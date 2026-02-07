<?php

namespace App\Http\Controllers;

use App\Models\DataRegistry;
use App\Models\DataEntry;
use Illuminate\Http\Request;
use App\Helpers\ActivityLogger;

class DataEntryController extends Controller
{
    public function index(DataRegistry $registry)
    {
        $entries = $registry->entries()->latest()->paginate(20);
        return view('admin.data-entry.index', compact('registry', 'entries'));
    }

    public function create(DataRegistry $registry)
    {
        return view('admin.data-entry.create', compact('registry'));
    }

    public function store(Request $request, DataRegistry $registry)
    {
        $validated = $request->validate([
            'periode' => 'required|string|max:50',
            'data_json' => 'required|json',
        ]);

        // Detect if periode is a range (e.g., "2010-2035", "2024-01--2024-12", "2024-Q1--2024-Q4")
        $periodeInput = $validated['periode'];
        $dataJson = $validated['data_json'];
        
        $periods = [];
        $isRange = false;

        // 1. Check for Annual Range (YYYY-YYYY)
        if (preg_match('/^(\d{4})-(\d{4})$/', $periodeInput, $matches)) {
            $start = (int)$matches[1];
            $end = (int)$matches[2];
            for ($i = $start; $i <= $end; $i++) $periods[] = (string)$i;
            $isRange = true;
        } 
        // 2. Check for Monthly Range (YYYY-MM--YYYY-MM)
        elseif (preg_match('/^(\d{4}-\d{2})--(\d{4}-\d{2})$/', $periodeInput, $matches)) {
            $periods = $this->generateDateRange($matches[1], $matches[2], 'bulanan');
            $isRange = true;
        }
        // 3. Check for Quarterly/Semesterly Range (YYYY-QX--YYYY-QX or YYYY-SX--YYYY-SX)
        elseif (preg_match('/^(\d{4}-[QS][1-4])--(\d{4}-[QS][1-4])$/', $periodeInput, $matches)) {
            $periods = $this->generateDateRange($matches[1], $matches[2], 'custom');
            $isRange = true;
        }

        if ($isRange) {
            if (empty($periods)) {
                return redirect()->back()->with('error', 'Rentang periode tidak valid atau tahun awal lebih besar dari tahun akhir!');
            }

            // Decode data_json to get the data array
            $dataArray = json_decode($dataJson, true);
            
            if (!isset($dataArray['data']) || !is_array($dataArray['data'])) {
                return redirect()->back()->with('error', 'Format data tidak valid!');
            }
            
            $createdCount = 0;
            $skipped = [];
            
            // Create entry for each single periode in the range
            foreach ($periods as $p) {
                // Check if this single periode already exists
                $exists = $registry->entries()->where('periode', $p)->exists();
                
                if ($exists) {
                    $skipped[] = $p;
                    continue;
                }
                
                // Extract data for this specific period from the table
                $extractedData = $this->extractDataForPeriode($dataArray['data'], $p, $periods);
                
                if ($extractedData !== null) {
                    $entryDataJson = json_encode([
                        'data' => $extractedData,
                        'mergeCells' => $dataArray['mergeCells'] ?? []
                    ]);
                    
                    $registry->entries()->create([
                        'periode' => $p,
                        'data_json' => $entryDataJson
                    ]);
                    
                    $createdCount++;
                }
            }
            
            ActivityLogger::log('CREATE_DATA_ENTRY_BULK', 'DataEntry', null, "Menambahkan {$createdCount} periode data untuk tabel: {$registry->judul}");
            
            $message = "Berhasil membuat {$createdCount} periode data!";
            if (count($skipped) > 0) {
                $message .= " (Yang dilewati karena sudah ada: " . implode(', ', $skipped) . ")";
            }
            
            return redirect()->route('admin.data-entry.index', $registry)->with('success', $message);
            
        } else {
            // Single periode (not a range)
            $exists = $registry->entries()->where('periode', $periodeInput)->exists();
            if ($exists) {
                return redirect()->back()->with('error', "Data untuk periode {$periodeInput} sudah ada!");
            }

            $entry = $registry->entries()->create($validated);
            ActivityLogger::log('CREATE_DATA_ENTRY', 'DataEntry', $entry->id, "Menambahkan data periode {$entry->periode} untuk tabel: {$registry->judul}");

            return redirect()->route('admin.data-entry.index', $registry)->with('success', "Data periode {$entry->periode} berhasil ditambahkan!");
        }
    }

    /**
     * Generate list of periods between two values
     */
    private function generateDateRange($start, $end, $type)
    {
        $results = [];
        if ($type === 'bulanan') {
            try {
                $current = \Carbon\Carbon::createFromFormat('Y-m', $start)->startOfMonth();
                $target = \Carbon\Carbon::createFromFormat('Y-m', $end)->startOfMonth();
                
                if ($current->gt($target)) return [];

                while ($current->lte($target)) {
                    $results[] = $current->format('Y-m');
                    $current->addMonth();
                }
            } catch (\Exception $e) { return []; }
        } else {
            // Quarterly or Semesterly logic: YYYY-QX or YYYY-SX
            preg_match('/^(\d{4})-([QS])(\d)$/', $start, $m1);
            preg_match('/^(\d{4})-([QS])(\d)$/', $end, $m2);
            
            if (!$m1 || !$m2 || $m1[2] !== $m2[2]) return []; // Must be same type (Q or S)

            $startYear = (int)$m1[1];
            $startVal = (int)$m1[3];
            $endYear = (int)$m2[1];
            $endVal = (int)$m2[3];
            $prefix = $m1[2];
            $maxVal = ($prefix === 'Q') ? 4 : 2;

            for ($y = $startYear; $y <= $endYear; $y++) {
                $vStart = ($y === $startYear) ? $startVal : 1;
                $vEnd = ($y === $endYear) ? $endVal : $maxVal;
                
                if ($vStart > $vEnd && $startYear === $endYear) return [];

                for ($v = $vStart; $v <= $vEnd; $v++) {
                    $results[] = "{$y}-{$prefix}{$v}";
                }
            }
        }
        return $results;
    }
    
    /**
     * Extract data for a specific period from the full dataset
     * It looks for a match in the first column (Case-insensitive)
     */
    private function extractDataForPeriode($allData, $targetPeriode, $allPeriodsInRange = [])
    {
        if (empty($allData)) return null;
        
        // 1. Identify static headers
        // These are rows at the top that DO NOT match ANY period label in our range.
        $staticHeaders = [];
        $dataRowsStartAt = -1;

        foreach ($allData as $index => $row) {
            $firstCol = isset($row[0]) ? trim((string)$row[0]) : '';
            $isAnyPeriodLabel = false;
            
            // If allPeriodsInRange is empty, we fallback to finding the first row that matches target
            if (empty($allPeriodsInRange)) {
                if (strcasecmp($firstCol, $targetPeriode) === 0) {
                    $isAnyPeriodLabel = true;
                }
            } else {
                foreach ($allPeriodsInRange as $p) {
                    if (strcasecmp($firstCol, (string)$p) === 0) {
                        $isAnyPeriodLabel = true;
                        break;
                    }
                }
            }

            if ($isAnyPeriodLabel) {
                $dataRowsStartAt = $index;
                break;
            } else {
                $staticHeaders[] = $row;
            }
        }

        // 2. Find the specific row for our targetPeriode
        $targetRow = null;
        if ($dataRowsStartAt !== -1) {
            for ($i = $dataRowsStartAt; $i < count($allData); $i++) {
                $firstCol = isset($allData[$i][0]) ? trim((string)$allData[$i][0]) : '';
                if (strcasecmp($firstCol, $targetPeriode) === 0) {
                    $targetRow = $allData[$i];
                    break;
                }
            }
        }
        
        if ($targetRow === null) return null;
        
        return array_merge($staticHeaders, [$targetRow]);
    }

    public function show(DataRegistry $registry, DataEntry $entry)
    {
        // Ensure entry belongs to registry
        if ($entry->data_registry_id !== $registry->id) {
            abort(404);
        }

        return view('admin.data-entry.show', compact('registry', 'entry'));
    }

    public function edit(DataRegistry $registry, DataEntry $entry)
    {
        // Ensure entry belongs to registry
        if ($entry->data_registry_id !== $registry->id) {
            abort(404);
        }

        return view('admin.data-entry.edit', compact('registry', 'entry'));
    }

    public function update(Request $request, DataRegistry $registry, DataEntry $entry)
    {
        // Ensure entry belongs to registry
        if ($entry->data_registry_id !== $registry->id) {
            abort(404);
        }

        $validated = $request->validate([
            'periode' => 'required|string|max:50',
            'data_json' => 'required|json',
        ]);

        // Check if periode already exists for this registry (excluding current entry)
        $exists = $registry->entries()
            ->where('periode', $validated['periode'])
            ->where('id', '!=', $entry->id)
            ->exists();
            
        if ($exists) {
            return redirect()->back()
                ->with('error', "Data untuk periode {$validated['periode']} sudah ada!");
        }

        $entry->update($validated);

        ActivityLogger::log('UPDATE_DATA_ENTRY', 'DataEntry', $entry->id, "Mengupdate data periode {$entry->periode} untuk tabel: {$registry->judul}");

        return redirect()->route('admin.data-entry.index', $registry)
            ->with('success', "Data periode {$entry->periode} berhasil diupdate!");
    }

    public function destroy(DataRegistry $registry, DataEntry $entry)
    {
        // Ensure entry belongs to registry
        if ($entry->data_registry_id !== $registry->id) {
            abort(404);
        }

        $periode = $entry->periode;
        $entry->delete();

        ActivityLogger::log('DELETE_DATA_ENTRY', 'DataEntry', null, "Menghapus data periode {$periode} dari tabel: {$registry->judul}");

        return redirect()->route('admin.data-entry.index', $registry)
            ->with('success', "Data periode {$periode} berhasil dihapus!");
    }

    public function export(DataRegistry $registry, DataEntry $entry)
    {
        // Ensure entry belongs to registry
        if ($entry->data_registry_id !== $registry->id) {
            abort(404);
        }

        // Export functionality will be implemented with PhpSpreadsheet
        // For now, return JSON for testing
        return response()->json([
            'registry' => $registry,
            'entry' => $entry,
            'data' => $entry->data_json
        ]);
    }
}
