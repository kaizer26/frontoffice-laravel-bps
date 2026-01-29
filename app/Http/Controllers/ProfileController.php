<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

use App\Helpers\ActivityLogger;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = auth()->user();
        
        $noHp = preg_replace('/\D/', '', $request->no_hp);
        if (str_starts_with($noHp, '0')) {
            $noHp = '62' . substr($noHp, 1);
        } elseif (str_starts_with($noHp, '8')) {
            $noHp = '62' . $noHp;
        }

        $data = [
            'no_hp' => $noHp,
        ];

        if ($request->hasFile('foto')) {
            // Delete old photo if it exists and not default
            if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }
            
            $path = $request->file('foto')->store('profiles', 'public');
            $data['foto'] = $path;
        }

        // Handle password change if enabled and provided
        if (\App\Models\SystemSetting::get('allow_user_password_change', 'false') === 'true' && $request->filled('password')) {
            $request->validate([
                'password' => 'required|min:6|confirmed',
            ]);
            $data['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
            ActivityLogger::log('UPDATE_PASSWORD', 'User', $user->id, "User {$user->name} memperbarui password sendiri");
        }

        $user->update($data);
        
        ActivityLogger::log('UPDATE_PROFILE', 'User', $user->id, "User {$user->name} memperbarui data profil/foto");

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'user' => [
                'name' => $user->name,
                'no_hp' => $user->no_hp,
                'foto' => $user->foto ? asset('storage/' . $user->foto) : null,
            ]
        ]);
    }

    public function resetToDefault()
    {
        $user = auth()->user();
        $nipBps = $user->nip_bps;

        // Delete old custom photo if exists
        if ($user->foto && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->foto)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->foto);
        }

        if (empty($nipBps)) {
            $user->update(['foto' => null]);
            ActivityLogger::log('RESET_PROFILE_PHOTO', 'User', $user->id, "User {$user->name} mencoba mereset foto tapi NIP BPS kosong");
            return response()->json(['success' => true, 'message' => 'Profil direset (NIP BPS tidak ditemukan)', 'user' => ['foto' => null]]);
        }

        \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('avatars');
        
        $downloaded = false;
        $finalPath = null;

        // 1. Try to get photo URL from hover_profile
        try {
            $hoverUrl = "https://community.bps.go.id/portal/components/organisasi/hover_profile.php?user_pegid={$nipBps}";
            $htmlResponse = \Illuminate\Support\Facades\Http::timeout(10)->get($hoverUrl);
            
            if ($htmlResponse->successful()) {
                $html = $htmlResponse->body();
                // Match src="../../../images/avatar/XXXXX.jpg"
                if (preg_match('/src="([^"]+)"/', $html, $matches)) {
                    $relativePath = $matches[1];
                    // Clean ../ parts and build absolute URL
                    // $relativePath might look like "../../../images/avatar/53106.jpg"
                    $cleanedPath = str_replace('../', '', $relativePath);
                    $fotoUrl = "https://community.bps.go.id/" . ltrim($cleanedPath, '/');
                    
                    $imgResponse = \Illuminate\Support\Facades\Http::timeout(15)->get($fotoUrl);
                    if ($imgResponse->successful() && strlen($imgResponse->body()) > 1000) {
                        $finalPath = "avatars/{$nipBps}.jpg";
                        \Illuminate\Support\Facades\Storage::disk('public')->put($finalPath, $imgResponse->body());
                        $downloaded = true;
                    }
                }
            }
        } catch (\Exception $e) {
            // Log if needed
        }

        // 2. Legacy fallback if hover_profile failed
        if (!$downloaded) {
            $nipShort = substr($nipBps, -5);
            $urls = [
                "https://community.bps.go.id/images/avatar/{$nipBps}.jpg",
                "https://community.bps.go.id/images/avatar/{$nipShort}.jpg",
            ];

            foreach ($urls as $fotoUrl) {
                try {
                    $response = \Illuminate\Support\Facades\Http::timeout(15)->get($fotoUrl);
                    if ($response->successful() && strlen($response->body()) > 1000) {
                        $finalPath = "avatars/{$nipBps}.jpg";
                        \Illuminate\Support\Facades\Storage::disk('public')->put($finalPath, $response->body());
                        $downloaded = true;
                        break;
                    }
                } catch (\Exception $e) {}
            }
        }

        // 3. Last fallback: Default nofoto
        if (!$downloaded) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(10)->get("https://community.bps.go.id/images/nofoto.JPG");
                if ($response->successful()) {
                    $finalPath = "avatars/{$nipBps}.jpg";
                    \Illuminate\Support\Facades\Storage::disk('public')->put($finalPath, $response->body());
                    $downloaded = true;
                }
            } catch (\Exception $e) {}
        }

        $user->update(['foto' => $downloaded ? $finalPath : null]);
        
        ActivityLogger::log('RESET_PROFILE_PHOTO', 'User', $user->id, "User {$user->name} mereset foto ke official BPS (" . ($downloaded ? 'Berhasil' : 'Gagal') . ")");

        return response()->json([
            'success' => true,
            'message' => $downloaded ? 'Foto profil telah diperbarui dari official BPS' : 'Foto direset ke default (Gagal mengunduh foto official)',
            'user' => [
                'foto' => $user->foto ? asset('storage/' . $user->foto) : null
            ]
        ]);
    }
}
