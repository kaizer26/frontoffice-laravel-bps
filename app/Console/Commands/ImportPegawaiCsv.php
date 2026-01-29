<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImportPegawaiCsv extends Command
{
    protected $signature = 'pegawai:import-csv {file?}';
    protected $description = 'Import pegawai dari file CSV dan download foto dari BPS';

    public function handle()
    {
        $file = $this->argument('file') ?? base_path('../data_pegawai.csv');
        
        if (!file_exists($file)) {
            $this->error("File tidak ditemukan: $file");
            $this->info("Cara membuat CSV:");
            $this->info("1. Buka file Excel di Microsoft Excel / LibreOffice");
            $this->info("2. Save As → CSV (Comma delimited) (*.csv)");
            $this->info("3. Simpan sebagai: data_pegawai.csv");
            return 1;
        }

        $this->info("Membaca file: $file");
        
        // Create storage directory for photos
        Storage::disk('public')->makeDirectory('avatars');
        
        $imported = 0;
        $skipped = 0;
        
        // Read CSV file
        $handle = fopen($file, 'r');
        
        // Skip header row - use semicolon as delimiter (Indonesian Excel format)
        $header = fgetcsv($handle, 0, ';');
        $this->info("Header columns: " . count($header));
        $this->info("Columns: " . implode(' | ', $header));
        
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            // Assuming order: NIP_BPS, NIP_PNS, Nama, Jabatan, Golongan, Email_BPS
            $nipBps = trim($row[0] ?? '');
            $nipPns = trim($row[1] ?? '');
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
                if ($response->successful() && strlen($response->body()) > 1000) {
                    $fotoPath = "avatars/{$nipBps}.jpg";
                    Storage::disk('public')->put($fotoPath, $response->body());
                    $this->info("  ✓ Foto downloaded: $fotoPath");
                } else {
                    $this->warn("  ✗ Foto tidak ditemukan atau terlalu kecil");
                }
            } catch (\Exception $e) {
                $this->warn("  ✗ Gagal download foto: " . $e->getMessage());
            }
            
            // Create or update Pegawai
            Pegawai::updateOrCreate(
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
            
            User::updateOrCreate(
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
        
        fclose($handle);
        
        $this->newLine();
        $this->info("=================================");
        $this->info("Import selesai!");
        $this->info("Berhasil: $imported");
        $this->info("Dilewati: $skipped");
        $this->info("=================================");
        $this->newLine();
        $this->info("Login dengan email pegawai, password: password");
        
        return 0;
    }
}
