<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportPegawai extends Command
{
    protected $signature = 'pegawai:import {file?}';
    protected $description = 'Import pegawai dari file Excel dan download foto dari BPS';

    public function handle()
    {
        if (!class_exists('ZipArchive')) {
            $this->error('Error: Class "ZipArchive" tidak ditemukan.');
            $this->info('Silakan aktifkan ekstensi "zip" di php.ini Anda.');
            return 1;
        }

        $file = $this->argument('file') ?? base_path('../data pegawai.xlsx');
        
        if (!file_exists($file)) {
            $this->error("File tidak ditemukan: $file");
            $this->info("Pastikan file ada di: " . realpath(base_path('..')) . "/data pegawai.xlsx");
            return 1;
        }

        $this->info("Membaca file: $file");
        
        try {
            $spreadsheet = IOFactory::load($file);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Skip header row
            $header = array_shift($rows);
            $this->info("Header: " . implode(', ', $header));
            
            // Create storage directory for photos
            Storage::disk('public')->makeDirectory('avatars');
            
            $imported = 0;
            $skipped = 0;
            
            foreach ($rows as $row) {
                $nipBps = trim((string)($row[0] ?? ''));
                if (is_numeric($nipBps) && (strpos(strtolower($nipBps), 'e+') !== false)) {
                    $nipBps = number_format((float)$nipBps, 0, '', '');
                }

                $nipPns = trim((string)($row[1] ?? ''));
                if (is_numeric($nipPns) && (strpos(strtolower($nipPns), 'e+') !== false)) {
                    $nipPns = number_format((float)$nipPns, 0, '', '');
                }
                $nama = trim($row[2] ?? '');
                $jabatan = trim($row[3] ?? '');
                $golongan = trim($row[4] ?? '');
                $emailBps = trim($row[5] ?? '');
                
                if (empty($nipBps) || empty($nama)) {
                    $this->warn("Skip: Data tidak lengkap - NIP: $nipBps, Nama: $nama");
                    $skipped++;
                    continue;
                }
                
                $this->info("Processing: $nama ($nipBps)");
                
                // Download foto dari BPS Community
                $fotoPath = null;
                $fotoUrl = "https://community.bps.go.id/images/avatar/{$nipBps}.jpg";
                
                try {
                    $response = Http::timeout(10)->get($fotoUrl);
                    if ($response->successful()) {
                        $fotoPath = "avatars/{$nipBps}.jpg";
                        Storage::disk('public')->put($fotoPath, $response->body());
                        $this->info("  ✓ Foto downloaded: $fotoPath");
                    } else {
                        $this->warn("  ✗ Foto tidak ditemukan: $fotoUrl");
                    }
                } catch (\Exception $e) {
                    $this->warn("  ✗ Gagal download foto: " . $e->getMessage());
                }
                
                // Create or update Pegawai
                $pegawai = Pegawai::updateOrCreate(
                    ['nip_bps' => $nipBps],
                    [
                        'nip_pns' => $nipPns,
                        'nama' => $nama,
                        'jabatan' => $jabatan,
                        'golongan' => $golongan,
                        'email_bps' => $emailBps,
                    ]
                );
                
                // Create User account if not exists
                $email = $emailBps ?: "{$nipBps}@bps.go.id";
                
                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'nip_bps' => $nipBps,
                        'nip_pns' => $nipPns,
                        'name' => $nama,
                        'password' => Hash::make('password'), // Default password
                        'role' => 'petugas',
                        'status' => 'aktif',
                        'foto' => $fotoPath,
                    ]
                );
                
                $this->info("  ✓ User & Pegawai created/updated");
                $imported++;
            }
            
            $this->newLine();
            $this->info("=================================");
            $this->info("Import selesai!");
            $this->info("Berhasil: $imported");
            $this->info("Dilewati: $skipped");
            $this->info("=================================");
            $this->newLine();
            $this->info("Login dengan email pegawai, password: password");
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
