<?php

namespace App\Http\Controllers;

use App\Models\JadwalPetugas;
use App\Models\PenilaianPetugas;
use App\Models\User;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;
use App\Helpers\ActivityLogger;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function jadwal(Request $request)
    {
        $query = JadwalPetugas::with('user');

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        $jadwal = $query->orderBy('tanggal', 'desc')
            ->orderBy('shift')
            ->paginate(20)
            ->withQueryString();

        $petugas = User::where('role', 'petugas')
            ->where('status', 'aktif')
            ->get();

        return view('admin.jadwal', compact('jadwal', 'petugas'));
    }

    public function storeJadwal(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'shift' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:aktif,libur,cuti',
        ]);

        JadwalPetugas::create($validated);

        ActivityLogger::log('CREATE_JADWAL', 'JadwalPetugas', null, "Menambahkan jadwal baru untuk Petugas ID: {$validated['user_id']} pada tanggal {$validated['tanggal']}");

        return redirect()->back()->with('success', 'Jadwal berhasil ditambahkan');
    }

    public function updateJadwal(Request $request, \App\Models\JadwalPetugas $jadwal)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'shift' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:aktif,libur,cuti',
        ]);

        $jadwal->update($validated);

        ActivityLogger::log('UPDATE_JADWAL', 'JadwalPetugas', $jadwal->id, "Mengupdate jadwal Petugas ID: {$jadwal->user_id} tanggal {$jadwal->tanggal}");

        return redirect()->back()->with('success', 'Jadwal berhasil diupdate');
    }

    public function destroyJadwal(\App\Models\JadwalPetugas $jadwal)
    {
        $userId = $jadwal->user_id;
        $tanggal = $jadwal->tanggal;
        $jadwal->delete();
        
        ActivityLogger::log('DELETE_JADWAL', 'JadwalPetugas', null, "Menghapus jadwal Petugas ID: $userId tanggal $tanggal");
        
        return redirect()->back()->with('success', 'Jadwal berhasil dihapus');
    }

    public function penilaian()
    {
        $ratings = PenilaianPetugas::with(['user', 'bukuTamu'])
            ->latest()
            ->paginate(20);

        // Officer ratings summary
        $officerRatings = PenilaianPetugas::select('user_id', 
                DB::raw('AVG(rating_keseluruhan) as avg_rating'),
                DB::raw('COUNT(*) as total_ratings'))
            ->groupBy('user_id')
            ->with('user')
            ->get()
            ->sortByDesc('avg_rating');

        // Prepare data for charts to avoid Blade parsing issues
        $officerChartData = $officerRatings->take(10)->map(function($o) {
            return [
                'name' => $o->user->name ?? 'Unknown',
                'rating' => round($o->avg_rating, 1),
                'count' => $o->total_ratings
            ];
        })->values();

        $distributionChartData = [
            '5' => PenilaianPetugas::where('rating_keseluruhan', 5)->count(),
            '4' => PenilaianPetugas::where('rating_keseluruhan', 4)->count(),
            '3' => PenilaianPetugas::where('rating_keseluruhan', 3)->count(),
            '2' => PenilaianPetugas::where('rating_keseluruhan', 2)->count(),
            '1' => PenilaianPetugas::where('rating_keseluruhan', 1)->count(),
        ];

        return view('admin.penilaian', compact('ratings', 'officerRatings', 'officerChartData', 'distributionChartData'));
    }

    public function rekapLayanan(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $quarter = $request->input('quarter', ceil(date('n') / 3));

        $months = [];
        switch ($quarter) {
            case 1: $months = [1, 2, 3]; break;
            case 2: $months = [4, 5, 6]; break;
            case 3: $months = [7, 8, 9]; break;
            case 4: $months = [10, 11, 12]; break;
        }

        $petugas = User::where('role', 'petugas')->get();
        
        $rekap = $petugas->map(function($user) use ($year, $months) {
            // Stats handled as Main Officer (PST or Entry)
            $pstStats = DB::table('buku_tamu')
                ->where('user_id', $user->id)
                ->where('sarana_kunjungan', 'Langsung')
                ->where(DB::raw("strftime('%Y', waktu_kunjungan)"), (string)$year)
                ->whereIn(DB::raw("CAST(strftime('%m', waktu_kunjungan) AS INTEGER)"), $months)
                ->count();

            // Stats handled as Online Serving Officer
            $onlineStats = DB::table('buku_tamu')
                ->where('petugas_online_id', $user->id)
                ->where('sarana_kunjungan', 'Online')
                ->where(DB::raw("strftime('%Y', waktu_kunjungan)"), (string)$year)
                ->whereIn(DB::raw("CAST(strftime('%m', waktu_kunjungan) AS INTEGER)"), $months)
                ->count();

            // Aggregate metrics (Overall for this user)
            $generalStats = DB::table('buku_tamu')
                ->leftJoin('permintaan_data', 'buku_tamu.id', '=', 'permintaan_data.buku_tamu_id')
                ->leftJoin('penilaian_petugas', 'buku_tamu.id', '=', 'penilaian_petugas.buku_tamu_id')
                ->where(function($q) use ($user) {
                    $q->where('buku_tamu.user_id', $user->id)
                      ->orWhere('buku_tamu.petugas_online_id', $user->id);
                })
                ->where(DB::raw("strftime('%Y', waktu_kunjungan)"), (string)$year)
                ->whereIn(DB::raw("CAST(strftime('%m', waktu_kunjungan) AS INTEGER)"), $months)
                ->select([
                    DB::raw('COUNT(DISTINCT buku_tamu.id) as total_involved'),
                    DB::raw('SUM(CASE WHEN permintaan_data.status_layanan = "Selesai" THEN 1 ELSE 0 END) as selesai'),
                    DB::raw('AVG(penilaian_petugas.rating_keseluruhan) as avg_rating')
                ])
                ->first();

            return [
                'user' => $user,
                'total_pst' => $pstStats,
                'total_online' => $onlineStats,
                'total' => $pstStats + $onlineStats,
                'selesai' => $generalStats->selesai ?? 0,
                'rating' => round($generalStats->avg_rating ?? 0, 1)
            ];
        });

    // Sort by total desc, then selesai desc
    $rekap = $rekap->sortBy([
        ['total', 'desc'],
        ['selesai', 'desc'],
    ]);

    $years = DB::table('buku_tamu')
            ->select(DB::raw("strftime('%Y', waktu_kunjungan) as year"))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
            
        if ($years->isEmpty()) $years = [date('Y')];

        return view('admin.rekap', compact('rekap', 'year', 'quarter', 'years'));
    }

    public function exportRekap(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $quarter = $request->input('quarter', ceil(date('n') / 3));

        $months = match((int)$quarter) {
            1 => [1, 2, 3],
            2 => [4, 5, 6],
            3 => [7, 8, 9],
            4 => [10, 11, 12],
            default => [1, 2, 3],
        };

        $officers = \App\Models\User::where('role', 'petugas')->get();
        $data = [];

        foreach ($officers as $officer) {
            $baseQuery = \App\Models\BukuTamu::where('user_id', $officer->id)
                ->whereYear('waktu_kunjungan', $year)
                ->whereIn(\DB::raw('strftime("%m", waktu_kunjungan)'), array_map(fn($m) => str_pad($m, 2, '0', STR_PAD_LEFT), $months));

            $total = (clone $baseQuery)->count();
            $pst = (clone $baseQuery)->where('sarana_kunjungan', 'Langsung')->count();
            $online = (clone $baseQuery)->where('sarana_kunjungan', 'Online')->count();
            $selesai = (clone $baseQuery)->whereHas('permintaanData', function($q) {
                $q->where('status_layanan', 'Selesai');
            })->count();

            $rating = \App\Models\PenilaianPetugas::where('user_id', $officer->id)
                ->whereYear('created_at', $year)
                ->whereIn(\DB::raw('strftime("%m", created_at)'), array_map(fn($m) => str_pad($m, 2, '0', STR_PAD_LEFT), $months))
                ->avg('rating_keseluruhan') ?? 0;

            $data[] = [
                'Nama Petugas' => $officer->name,
                'Email' => $officer->email,
                'Layanan PST' => $pst,
                'Layanan Online' => $online,
                'Total Layanan' => $total,
                'Total Selesai' => $selesai,
                'Rata-rata Rating' => number_format($rating, 2),
                'Persentase Selesai' => ($total > 0 ? round(($selesai / $total) * 100, 1) : 0) . '%'
            ];
        }

        $filename = "Rekap_Layanan_{$year}_Q{$quarter}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            if (!empty($data)) {
                fputcsv($file, array_keys($data[0]));
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function pegawai()
    {
        $pegawai = Pegawai::paginate(20);
        return view('admin.pegawai', compact('pegawai'));
    }

    public function users(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $users = User::latest()->paginate($perPage)->appends(['per_page' => $perPage]);
        $pegawai = Pegawai::all();
        return view('admin.users', compact('users', 'pegawai', 'perPage'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'nip_bps' => 'nullable|string|max:9',
            'nip_pns' => 'nullable|string|max:18',
            'no_hp' => 'nullable|string|max:20',
            'role' => 'required|in:admin,petugas',
            'status' => 'required|in:aktif,nonaktif',
            'password' => 'required|min:6',
        ]);

        $noHp = preg_replace('/\D/', '', $request->no_hp);
        if ($noHp) {
            if (str_starts_with($noHp, '0')) {
                $noHp = '62' . substr($noHp, 1);
            } elseif (str_starts_with($noHp, '8')) {
                $noHp = '62' . $noHp;
            }
        }

        // Auto append @bps.go.id
        $email = $validated['email'];
        if (!str_contains($email, '@')) {
            $email = $email . '@bps.go.id';
        }

        // Check if email exists
        if (User::where('email', $email)->exists()) {
            return redirect()->back()->with('error', 'Email sudah terdaftar!');
        }

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $email,
            'nip_bps' => $validated['nip_bps'],
            'nip_pns' => $validated['nip_pns'],
            'no_hp' => $noHp,
            'role' => $validated['role'],
            'status' => $validated['status'],
            'password' => Hash::make($validated['password']),
        ]);

        ActivityLogger::log('CREATE_USER', 'User', $newUser->id, "Menambahkan user baru: {$newUser->name} ({$newUser->email})");

        return redirect()->back()->with('success', 'User berhasil ditambahkan');
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nip_bps' => 'nullable|string|max:9',
            'nip_pns' => 'nullable|string|max:18',
            'no_hp' => 'nullable|string|max:20',
            'role' => 'required|in:admin,petugas',
            'status' => 'required|in:aktif,nonaktif',
            'password' => 'nullable|min:6',
        ]);

        $noHp = preg_replace('/\D/', '', $request->no_hp);
        if ($noHp) {
            if (str_starts_with($noHp, '0')) {
                $noHp = '62' . substr($noHp, 1);
            } elseif (str_starts_with($noHp, '8')) {
                $noHp = '62' . $noHp;
            }
        }

        $user->update([
            'name' => $validated['name'],
            'nip_bps' => $validated['nip_bps'],
            'nip_pns' => $validated['nip_pns'],
            'no_hp' => $noHp,
            'role' => $validated['role'],
            'status' => $validated['status'],
        ]);

        if ($validated['password']) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        ActivityLogger::log('UPDATE_USER', 'User', $user->id, "Mengupdate data user: {$user->name} ({$user->email})");

        return redirect()->back()->with('success', 'User berhasil diupdate');
    }

    public function syncAllOfficers()
    {
        $result = \App\Helpers\GasSyncHelper::syncAllUnsyncedOfficers();
        
        if ($result['success']) {
            if ($result['count'] > 0) {
                ActivityLogger::log('SYNC_OFFICERS_BATCH', 'User', null, "Sinkronisasi massal berhasil: {$result['count']} petugas didaftarkan ke GAS.");
                return response()->json(['success' => true, 'message' => "Berhasil menyinkronkan {$result['count']} petugas."]);
            }
            return response()->json(['success' => true, 'message' => 'Semua petugas sudah sinkron.']);
        }
        
        return response()->json(['success' => false, 'message' => 'Gagal sinkronisasi: ' . $result['message']], 500);
    }


    public function downloadJadwalTemplate()
    {
        if (!class_exists('ZipArchive')) {
            return redirect()->back()->with('error', 'Error: Class "ZipArchive" tidak ditemukan. Silakan aktifkan ekstensi "zip" di php.ini Anda.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header
        $sheet->setCellValue('A1', 'Tanggal (YYYY-MM-DD)');
        $sheet->setCellValue('B1', 'Shift (Pagi/Siang/Sore)');
        $sheet->setCellValue('C1', 'NIP (BPS atau PNS)');
        $sheet->setCellValue('D1', 'Status (aktif/libur/cuti)');
        
        // Sample Data
        $sheet->setCellValue('A2', date('Y-m-d'));
        $sheet->setCellValue('B2', 'Pagi');
        $sheet->setCellValue('C2', '340012345');
        $sheet->setCellValue('D2', 'aktif');

        $writer = new Xlsx($spreadsheet);
        $fileName = 'template_jadwal.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }

    public function importJadwal(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        $file = $request->file('file');
        
        // Check if ZipArchive is enabled (required for .xlsx)
        if (!class_exists('ZipArchive') && $file->getClientOriginalExtension() === 'xlsx') {
            return redirect()->back()->with('error', 'Error: Class "ZipArchive" tidak ditemukan. Silakan aktifkan ekstensi "zip" di php.ini Anda dan restart server.');
        }
        
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            
            // Remove header
            array_shift($rows);
            
            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            DB::beginTransaction();
            foreach ($rows as $index => $row) {
                if (empty($row[0]) || empty($row[2])) continue;

                $tanggal = $row[0];
                $shift = $row[1] ?? 'Pagi';
                $nip = trim((string)($row[2] ?? ''));
                
                // Handle scientific notation from Excel for 18-digit NIPs
                if (is_numeric($nip) && (strpos(strtolower($nip), 'e+') !== false)) {
                    $nip = number_format((float)$nip, 0, '', '');
                }
                $status = strtolower($row[3] ?? 'aktif');

                // Find user by NIP BPS or NIP PNS
                $user = User::where('nip_bps', $nip)
                            ->orWhere('nip_pns', $nip)
                            ->first();
                
                if ($user) {
                    JadwalPetugas::create([
                        'tanggal' => $tanggal,
                        'shift' => $shift,
                        'user_id' => $user->id,
                        'status' => $status
                    ]);
                    $successCount++;
                } else {
                    $errorCount++;
                    $errors[] = "Baris " . ($index + 2) . ": Petugas dengan NIP $nip tidak ditemukan.";
                }
            }
            DB::commit();

            ActivityLogger::log('IMPORT_JADWAL', 'JadwalPetugas', null, "Berhasil mengimport $successCount jadwal dari Excel.");

            if ($errorCount > 0) {
                return redirect()->back()->with('success', "Berhasil mengimport $successCount jadwal.")
                    ->with('error', "Gagal mengimport $errorCount jadwal: " . implode(', ', $errors));
            }

            return redirect()->back()->with('success', "Berhasil mengimport $successCount jadwal.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimport Excel: ' . $e->getMessage());
        }
    }

    public function updateSetting(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string',
            'value' => 'required|string',
        ]);

        \App\Models\SystemSetting::set($validated['key'], $validated['value']);

        ActivityLogger::log('UPDATE_SETTING', 'SystemSetting', null, "Mengubah setting '{$validated['key']}' menjadi '{$validated['value']}'");

        return response()->json(['success' => true]);
    }

    public function activityLogs(Request $request)
    {
        $query = \App\Models\ActivityLog::with('user');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('target_type', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Date filter
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->latest()->paginate(50)->withQueryString();

        return view('admin.logs', compact('logs'));
    }

    /**
     * Upload reply letter Word template.
     */
    public function uploadReplyTemplate(Request $request)
    {
        $request->validate([
            'template' => 'required|mimes:docx|max:5120'
        ]);

        try {
            $file = $request->file('template');
            $filename = 'reply_letter_' . time() . '.docx';
            
            $path = $file->storeAs('templates', $filename, 'public');
            
            \App\Models\SystemSetting::set('reply_letter_template', $path);
            
            ActivityLogger::log('UPLOAD_REPLY_TEMPLATE', 'SystemSetting', null, "Upload template surat balasan: $filename");
            
            return response()->json([
                'success' => true,
                'message' => 'Template berhasil diupload',
                'path' => $filename
            ]);
        } catch (\Exception $e) {
            Log::error('Template upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate reply letter document from template.
     */
    public function generateReplyDocument(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|integer',
            'nomor_surat' => 'required|string',
            'tanggal_surat' => 'required|date',
            'tujuan' => 'required|string',
            'perihal' => 'nullable|string',
            'catatan' => 'nullable|string'
        ]);

        try {
            $templatePath = \App\Models\SystemSetting::get('reply_letter_template');
            if (!$templatePath) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template surat belum diupload oleh admin'
                ], 400);
            }

            $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($templatePath);
            if (!file_exists($fullPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File template tidak ditemukan'
                ], 404);
            }

            // Get service data
            $permintaan = \App\Models\PermintaanData::with('bukuTamu')->find($validated['service_id']);
            if (!$permintaan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data layanan tidak ditemukan'
                ], 404);
            }

            // Load template
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($fullPath);
            
            // Replace placeholders
            $replacements = [
                'NOMOR_SURAT' => $validated['nomor_surat'],
                'TANGGAL_SURAT' => Carbon::parse($validated['tanggal_surat'])->translatedFormat('d F Y'),
                'TUJUAN' => $validated['tujuan'],
                'NOMOR_SURAT_PERMINTAAN_DATA' => $permintaan->nomor_surat ?? '-',
                'KEPERLUAN' => $permintaan->bukuTamu->keperluan ?? '-'
            ];
            
            foreach ($replacements as $key => $value) {
                $templateProcessor->setValue('{{' . $key . '}}', $value);
            }

            // Ensure the generated number is not already used by another record
            $existingWithNumber = \App\Models\DataRequestReply::where('nomor_surat', $validated['nomor_surat'])
                ->where('permintaan_data_id', '!=', $permintaan->id)
                ->first();
            
            if ($existingWithNumber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor surat balasan ' . $validated['nomor_surat'] . ' sudah digunakan oleh data lain.'
                ], 422);
            }

            // Save generated document
            $cleanName = Str::slug($permintaan->bukuTamu->nama_pengunjung);
            $cleanInstansi = Str::slug($permintaan->bukuTamu->instansi);
            $dateSuffix = date('Ymd');
            
            $outputFilename = "replies/surat_balasan_{$permintaan->id}_{$cleanName}_{$cleanInstansi}_{$dateSuffix}.docx";
            $outputPath = storage_path('app/public/' . $outputFilename);
            
            // Ensure directory exists
            if (!is_dir(dirname($outputPath))) {
                mkdir(dirname($outputPath), 0755, true);
            }
            
            $templateProcessor->saveAs($outputPath);

            // Update the reply record
            \App\Models\DataRequestReply::updateOrCreate(
                ['permintaan_data_id' => $permintaan->id],
                [
                    'nomor_surat' => $validated['nomor_surat'],
                    'tanggal_surat' => $validated['tanggal_surat'],
                    'tujuan' => $validated['tujuan'],
                    'perihal' => $request->input('perihal'),
                    'catatan' => $request->input('catatan'),
                    'file_surat' => $outputFilename,
                    'nomor_urut' => $request->input('nomor_urut', 1),
                    'kode_surat' => $request->input('kode_surat', '02.04')
                ]
            );

            ActivityLogger::log('GENERATE_REPLY_LETTER', 'DataRequestReply', $permintaan->id, "Membuat surat balasan: {$validated['nomor_surat']}");

            return response()->json([
                'success' => true,
                'message' => 'Surat balasan berhasil dibuat',
                'download_url' => '/storage/' . $outputFilename
            ]);

        } catch (\Exception $e) {
            Log::error('Generate reply document error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat surat: ' . $e->getMessage()
            ], 500);
        }
    }
}
