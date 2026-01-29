<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class RetryDownloadFoto extends Command
{
    protected $signature = 'pegawai:retry-foto';
    protected $description = 'Retry download foto untuk pegawai yang belum punya foto';

    public function handle()
    {
        // Create storage directory for photos
        Storage::disk('public')->makeDirectory('avatars');
        
        // Get users without photos
        $users = User::whereNull('foto')
            ->orWhere('foto', '')
            ->whereNotNull('nip_bps')
            ->get();
        
        $this->info("Ditemukan {$users->count()} user tanpa foto");
        
        $success = 0;
        $failed = 0;
        
        foreach ($users as $user) {
            $nipBps = $user->nip_bps;
            
            if (empty($nipBps)) {
                continue;
            }
            
            $this->info("Downloading foto: {$user->name} ({$nipBps})");
            
            // Try both formats: full NIP and last 5 digits
            $nipShort = substr($nipBps, -5); // last 5 digits
            $urls = [
                "https://community.bps.go.id/images/avatar/{$nipBps}.jpg",
                "https://community.bps.go.id/images/avatar/{$nipShort}.jpg",
            ];
            
            $downloaded = false;
            
            foreach ($urls as $fotoUrl) {
                try {
                    $this->line("  Trying: $fotoUrl");
                    $response = Http::timeout(15)->get($fotoUrl);
                    
                    if ($response->successful() && strlen($response->body()) > 1000) {
                        $fotoPath = "avatars/{$nipBps}.jpg";
                        Storage::disk('public')->put($fotoPath, $response->body());
                        
                        $user->update(['foto' => $fotoPath]);
                        
                        $this->info("  ✓ Foto downloaded: $fotoPath");
                        $success++;
                        $downloaded = true;
                        break;
                    }
                } catch (\Exception $e) {
                    $this->warn("  ✗ Error: " . $e->getMessage());
                }
            }
            
            if (!$downloaded) {
                // Fallback: use default BPS nofoto image
                $this->line("  Trying fallback: nofoto.JPG");
                try {
                    $response = Http::timeout(15)->get("https://community.bps.go.id/images/nofoto.JPG");
                    if ($response->successful() && strlen($response->body()) > 100) {
                        $fotoPath = "avatars/{$nipBps}.jpg";
                        Storage::disk('public')->put($fotoPath, $response->body());
                        $user->update(['foto' => $fotoPath]);
                        $this->info("  ✓ Using default nofoto.JPG");
                        $success++;
                        $downloaded = true;
                    }
                } catch (\Exception $e) {
                    $this->warn("  ✗ Fallback failed: " . $e->getMessage());
                }
            }
            
            if (!$downloaded) {
                $this->warn("  ✗ Semua format gagal");
                $failed++;
            }
            
            // Small delay to avoid rate limiting
            usleep(500000); // 0.5 second
        }
        
        $this->newLine();
        $this->info("=================================");
        $this->info("Retry selesai!");
        $this->info("Berhasil: $success");
        $this->info("Gagal: $failed");
        $this->info("=================================");
        
        return 0;
    }
}
