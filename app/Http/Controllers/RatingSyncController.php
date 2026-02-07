<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\BukuTamu;
use App\Models\PenilaianPetugas;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RatingSyncController extends Controller
{
    public function sync()
    {
        $gasUrl = config('services.gas.rating_url');
        if (!$gasUrl) {
            return response()->json(['success' => false, 'message' => 'GAS_RATING_URL belum dikonfigurasi.'], 400);
        }

        try {
            // 1. Get pending ratings
            $response = Http::get($gasUrl, ['action' => 'getPendingRatings']);
            $pending = $response->json();

            if (empty($pending)) {
                return response()->json(['success' => true, 'message' => 'Tidak ada penilaian baru untuk disinkronkan.', 'synced_count' => 0]);
            }

            $syncedRows = [];
            $successCount = 0;

            foreach ($pending as $item) {
                // Find corresponding BukuTamu by token
                $bukuTamu = BukuTamu::where('rating_token', $item['token'])->first();
                
                if ($bukuTamu) {
                    // Check if rating already exists for this entry
                    $exists = PenilaianPetugas::where('buku_tamu_id', $bukuTamu->id)->exists();
                    
                    if (!$exists) {
                        // Determine which officer is being rated (Online vs Langsung)
                        $officerId = ($bukuTamu->sarana_kunjungan === 'Online' && $bukuTamu->online_channel === 'Pegawai' && $bukuTamu->petugas_online_id) 
                            ? $bukuTamu->petugas_online_id 
                            : $bukuTamu->user_id;

                        // Create rating
                        PenilaianPetugas::create([
                            'buku_tamu_id' => $bukuTamu->id,
                            'user_id' => $officerId,
                            'rating_keramahan' => $item['keramahan'],
                            'rating_kecepatan' => $item['kecepatan'],
                            'rating_pengetahuan' => $item['pengetahuan'],
                            'rating_keseluruhan' => $item['keseluruhan'],
                            'komentar' => $item['komentar'],
                        ]);

                        // Mark local buku tamu as rated
                        $bukuTamu->update(['rated' => true]);
                        $successCount++;
                    } else {
                        Log::info("Rating sync: Skip token {$item['token']} because rating already exists for buku_tamu_id {$bukuTamu->id}");
                    }
                    
                    $syncedRows[] = $item['row'];
                } else {
                    Log::warning("Rating sync: Token {$item['token']} not found in buku_tamu table.");
                }
            }

            // 2. Mark as synced in GAS if any were processed
            if (!empty($syncedRows)) {
                Http::post($gasUrl, [
                    'action' => 'markAsSynced',
                    'rows' => $syncedRows
                ]);
                
                ActivityLogger::log('SYNC_RATINGS', null, null, "Sinkronisasi berhasil: $successCount penilaian baru diimpor dari GAS.");
            }

            return response()->json([
                'success' => true, 
                'message' => "Berhasil menyinkronkan $successCount penilaian.",
                'synced_count' => $successCount
            ]);

        } catch (\Exception $e) {
            Log::error('Rating sync error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal sinkronisasi: ' . $e->getMessage()], 500);
        }
    }
}
