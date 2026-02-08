<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GasSyncHelper
{
    /**
     * Build rating URL with officer_id and trigger sync if needed.
     * 
     * @param string $ratingToken
     * @param User|null $officer
     * @return string|null
     */
    public static function buildRatingUrl($ratingToken, $officer = null)
    {
        $gasUrl = config('services.gas.rating_url');
        if (!$gasUrl) return null;

        $params = ['token' => $ratingToken];

        if ($officer) {
            $params['officer_id'] = $officer->id;
            
            // Trigger background sync if not already synced
            if (!$officer->is_synced_to_gas) {
                self::syncOfficerToGas($officer);
            }
        }

        $query = http_build_query($params);
        return $gasUrl . (str_contains($gasUrl, '?') ? '&' : '?') . $query;
    }

    /**
     * Sync single officer to GAS.
     * 
     * @param User $officer
     * @return bool
     */
    public static function syncOfficerToGas(User $officer)
    {
        $gasUrl = config('services.gas.rating_url');
        if (!$gasUrl) return false;

        $payload = [
            'action' => 'syncOfficer',
            'officer_id' => $officer->id,
            'officer_name' => $officer->name,
        ];

        if ($officer->foto && \Illuminate\Support\Facades\Storage::disk('public')->exists($officer->foto)) {
            $path = storage_path('app/public/' . $officer->foto);
            $payload['photo_base64'] = base64_encode(file_get_contents($path));
            $payload['photo_name'] = basename($officer->foto);
        }

        try {
            $response = Http::timeout(30)->post($gasUrl, $payload);

            if ($response->successful()) {
                $officer->update(['is_synced_to_gas' => true]);
                return true;
            }

            Log::warning("GAS Sync failed for officer {$officer->id}: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("GAS Sync error for officer {$officer->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Batch sync all unsynced officers (role: petugas).
     * 
     * @return array
     */
    public static function syncAllUnsyncedOfficers()
    {
        $gasUrl = config('services.gas.rating_url');
        if (!$gasUrl) {
            return ['success' => false, 'message' => 'GAS Rating URL not configured'];
        }

        $officers = User::where('role', 'petugas')
            ->where('is_synced_to_gas', false)
            ->get();

        if ($officers->isEmpty()) {
            return ['success' => true, 'message' => 'All officers already synced', 'count' => 0];
        }

        $data = $officers->map(function ($o) {
            $item = [
                'officer_id' => $o->id,
                'officer_name' => $o->name,
            ];

            if ($o->foto && \Illuminate\Support\Facades\Storage::disk('public')->exists($o->foto)) {
                $path = storage_path('app/public/' . $o->foto);
                $item['photo_base64'] = base64_encode(file_get_contents($path));
                $item['photo_name'] = basename($o->foto);
            }

            return $item;
        })->toArray();

        try {
            $response = Http::timeout(60)->post($gasUrl, [
                'action' => 'syncOfficersBatch',
                'data' => $data
            ]);

            if ($response->successful()) {
                $ids = $officers->pluck('id')->toArray();
                User::whereIn('id', $ids)->update(['is_synced_to_gas' => true]);
                
                return [
                    'success' => true,
                    'message' => "Successfully synced " . count($data) . " officers",
                    'count' => count($data)
                ];
            }

            return ['success' => false, 'message' => 'GAS server error: ' . $response->status()];
        } catch (\Exception $e) {
            Log::error("GAS Batch Sync error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
