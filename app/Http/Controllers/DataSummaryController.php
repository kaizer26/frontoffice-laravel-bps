<?php

namespace App\Http\Controllers;

use App\Models\DataRegistry;
use App\Models\DataEntry;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DataSummaryController extends Controller
{
    public function index()
    {
        $registries = DataRegistry::orderBy('judul')->get();
        return view('admin.data-summary.index', compact('registries'));
    }

    public function fetchPeriods(Request $request)
    {
        $registryIds = $request->input('registry_ids', []);
        
        if (empty($registryIds)) {
            return response()->json(['periods' => []]);
        }

        // Get common period types
        $registries = DataRegistry::whereIn('id', $registryIds)->get();
        $types = $registries->pluck('periode_tipe')->unique();

        if ($types->count() > 1) {
            return response()->json([
                'error' => 'Tipe periode tabel yang dipilih harus sama (contoh: semuanya Tahunan atau semuanya Bulanan).'
            ], 422);
        }

        // UNION of periods across all selected registries (show ALL available)
        $allPeriods = [];
        foreach ($registryIds as $id) {
            $periods = DataEntry::where('data_registry_id', $id)
                ->pluck('periode')
                ->toArray();
            
            $allPeriods = array_merge($allPeriods, $periods);
        }

        // Unique and Sort periods
        $allPeriods = array_unique($allPeriods);
        sort($allPeriods);

        return response()->json([
            'periods' => array_values($allPeriods),
            'type' => $types->first()
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'registry_ids' => 'required|array|min:1',
            'periods' => 'required|array|min:1',
            'mode' => 'nullable|string|in:compact,join'
        ]);

        $registryIds = $request->input('registry_ids');
        $periods = $request->input('periods'); // Array of periods
        $mode = $request->input('mode', 'compact');
        sort($periods);

        $registries = DataRegistry::whereIn('id', $registryIds)->get();
        
        $summary = [];
        $registryHeaders = []; // registryId -> [col1_label, col2_label...]
        $registryDataMap = []; // registryId_period -> [ [key, col1, col2...], ... ]
        $allKeys = [];

        // 1. Fetch all data and labels
        foreach ($registries as $registry) {
            $registryHeaders[$registry->id] = [];
            
            foreach ($periods as $periode) {
                $entry = DataEntry::where('data_registry_id', $registry->id)
                    ->where('periode', $periode)
                    ->first();
                
                if (!$entry) continue;

                $data = $entry->data_json['data'] ?? [];
                if (empty($data)) continue;

                $headerRowsCount = $this->getHeaderRowsCount($data);
                $rawHeaders = array_slice($data, 0, $headerRowsCount);
                $actualData = array_slice($data, $headerRowsCount);
                
                // Determine registry column labels (labels of columns 1..N)
                if (empty($registryHeaders[$registry->id])) {
                    $colsCount = count($data[0] ?? []);
                    for ($c = 1; $c < $colsCount; $c++) {
                        $hParts = [];
                        for ($h = 0; $h < $headerRowsCount; $h++) {
                            $val = $rawHeaders[$h][$c] ?? '';
                            if ($val) $hParts[] = $val;
                        }
                        $label = implode(' ', array_unique($hParts));
                        $registryHeaders[$registry->id][$c] = $label ?: "Data $c";
                    }
                }

                $dataKey = $registry->id . '_' . $periode;
                $registryDataMap[$dataKey] = $actualData;

                if ($mode === 'join') {
                    foreach ($actualData as $row) {
                        $k = $row[0] ?? null;
                        if ($k !== null && $k !== '' && !in_array($k, $allKeys)) {
                            $allKeys[] = $k;
                        }
                    }
                }
            }
        }

        if ($mode === 'compact') {
            // MODE COMPACT: Tahun as Row, Registry as Column
            // Headers: Tahun | Registry1_Col1 | Registry1_Col2 | Registry2_Col1 ...
            $headers = ['Tahun'];
            foreach ($registries as $registry) {
                $colLabels = $registryHeaders[$registry->id] ?? [];
                foreach ($colLabels as $colIdx => $label) {
                    $unitSuffix = $registry->satuan ? " ({$registry->satuan})" : "";
                    $finalLabel = $registry->judul;
                    if ($label && stripos($registry->judul, $label) === false) {
                        $finalLabel .= " - " . $label;
                    }
                    $headers[] = $finalLabel . $unitSuffix;
                }
            }

            foreach ($periods as $periode) {
                $row = [$periode];
                foreach ($registries as $registry) {
                    $dataKey = $registry->id . '_' . $periode;
                    $entryData = $registryDataMap[$dataKey] ?? [];
                    
                    // Find row where column 0 matches $periode (or just take the first row if only 1 row)
                    $matchingRow = null;
                    if (count($entryData) === 1) {
                        $matchingRow = $entryData[0];
                    } else {
                        foreach ($entryData as $r) {
                            if (isset($r[0]) && $r[0] == $periode) {
                                $matchingRow = $r;
                                break;
                            }
                        }
                        // Fallback: if not found by period name, try the first row anyway?
                        // No, let's be strict for now.
                    }

                    $colIndices = array_keys($registryHeaders[$registry->id] ?? []);
                    foreach ($colIndices as $cIdx) {
                        $row[] = $matchingRow[$cIdx] ?? null;
                    }
                }
                $summary[] = $row;
            }
        } else {
            // MODE JOIN: Keterangan (ex: Kecamatan) as Row, Registry & Period as Columns
            $headers = ['Keterangan'];
            foreach ($registries as $registry) {
                $colLabels = $registryHeaders[$registry->id] ?? [];
                foreach ($periods as $periode) {
                    foreach ($colLabels as $colIdx => $label) {
                        $periodSuffix = count($periods) > 1 ? " [$periode]" : "";
                        $unitSuffix = $registry->satuan ? " ({$registry->satuan})" : "";
                        $finalLabel = $registry->judul;
                        if ($label && stripos($registry->judul, $label) === false) {
                            $finalLabel .= " - " . $label;
                        }
                        $headers[] = $finalLabel . $periodSuffix . $unitSuffix;
                    }
                }
            }

            sort($allKeys);
            foreach ($allKeys as $key) {
                $row = [$key];
                foreach ($registries as $registry) {
                    $colIndices = array_keys($registryHeaders[$registry->id] ?? []);
                    foreach ($periods as $periode) {
                        $dataKey = $registry->id . '_' . $periode;
                        $entryData = $registryDataMap[$dataKey] ?? [];
                        
                        $matchingRow = null;
                        foreach ($entryData as $r) {
                            if (isset($r[0]) && $r[0] == $key) {
                                $matchingRow = $r;
                                break;
                            }
                        }

                        foreach ($colIndices as $cIdx) {
                            $row[] = $matchingRow[$cIdx] ?? null;
                        }
                    }
                }
                $summary[] = $row;
            }
        }

        return response()->json([
            'headers' => $headers,
            'summary' => $summary,
            'periods' => $periods,
            'mode' => $mode,
            'registries' => $registries->map(function($r) {
                return [
                    'id' => $r->id,
                    'judul' => $r->judul,
                    'link_spreadsheet' => $r->link_spreadsheet
                ];
            })
        ]);
    }

    private function getHeaderRowsCount($data)
    {
        if (empty($data)) return 0;
        // In our system, the first column of a data row usually has a value (e.g. Kecamatan or Year)
        // Header rows might have multiple merged cells or titles
        for ($i = 0; $i < count($data); $i++) {
            $val = $data[$i][0] ?? '';
            if ($val !== '' && $val !== null) {
                // Check if this looks like a data row
                // For now, assume any non-empty first column is the start of data rows
                // unless it's the very first row
                if ($i == 0) continue; 
                return $i;
            }
        }
        return 1;
    }
}
