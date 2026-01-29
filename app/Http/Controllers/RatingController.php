<?php

namespace App\Http\Controllers;

use App\Models\BukuTamu;
use App\Models\PenilaianPetugas;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RatingController extends Controller
{
    /**
     * Show public rating form (hybrid)
     */
    public function publicForm()
    {
        return view('rating.public');
    }
    
    /**
     * Verify phone number and return visitor's buku tamu + petugas list
     */
    public function verifyPhone(Request $request)
    {
        $request->validate([
            'no_hp' => 'required|string|min:10|max:15'
        ]);
        
        $noHp = $request->no_hp;
        
        // Find buku tamu entry from today with this phone number
        $bukuTamu = BukuTamu::where('no_hp', $noHp)
            ->whereDate('waktu_kunjungan', Carbon::today())
            ->where('rated', false)
            ->orderBy('waktu_kunjungan', 'desc')
            ->first();
        
        if (!$bukuTamu) {
            // Also try without leading 0 or with different format
            $alternates = [];
            if (str_starts_with($noHp, '08')) {
                $alternates[] = '628' . substr($noHp, 2);
            } elseif (str_starts_with($noHp, '628')) {
                $alternates[] = '08' . substr($noHp, 3);
            }
            
            foreach ($alternates as $alt) {
                $bukuTamu = BukuTamu::where('no_hp', $alt)
                    ->whereDate('waktu_kunjungan', Carbon::today())
                    ->where('rated', false)
                    ->orderBy('waktu_kunjungan', 'desc')
                    ->first();
                
                if ($bukuTamu) break;
            }
        }
        
        if (!$bukuTamu) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor HP tidak ditemukan dalam kunjungan hari ini, atau Anda sudah memberikan penilaian.'
            ]);
        }
        
        // Get list of petugas for rating dropdown (Hybrid Logic)
        // 1. All officers scheduled for any shift today
        $scheduledIds = \App\Models\JadwalPetugas::where('tanggal', Carbon::today())
            ->where('status', 'aktif')
            ->pluck('user_id')
            ->toArray();
            
        // 2. Include specific handlers from the guest book entry (Even if not scheduled)
        $handlerIds = array_filter([$bukuTamu->user_id, $bukuTamu->petugas_online_id]);
        
        $allOfficerIds = array_unique(array_merge($scheduledIds, $handlerIds));
        
        $petugasList = User::whereIn('id', $allOfficerIds)
            ->where('status', 'aktif')
            ->select('id', 'name', 'foto', 'nip_bps')
            ->get()
            ->map(function($user) {
                $pegawai = \App\Models\Pegawai::where('nip_bps', $user->nip_bps)->first();
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'foto' => $user->foto ? asset('storage/' . $user->foto) : null,
                    'jabatan' => $pegawai ? $pegawai->jabatan : null
                ];
            });
        
        return response()->json([
            'success' => true,
            'buku_tamu' => [
                'id' => $bukuTamu->id,
                'nama_pengunjung' => $bukuTamu->nama_konsumen
            ],
            'petugas_list' => $petugasList
        ]);
    }
    
    /**
     * Submit public rating (with phone verification)
     */
    public function submitPublic(Request $request)
    {
        $validated = $request->validate([
            'buku_tamu_id' => 'required|exists:buku_tamu,id',
            'user_id' => 'required|exists:users,id',
            'rating_keramahan' => 'required|integer|min:1|max:5',
            'rating_kecepatan' => 'required|integer|min:1|max:5',
            'rating_pengetahuan' => 'required|integer|min:1|max:5',
            'rating_keseluruhan' => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:500',
        ]);
        
        $bukuTamu = BukuTamu::find($validated['buku_tamu_id']);
        
        if ($bukuTamu->rated) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memberikan penilaian untuk kunjungan ini.'
            ]);
        }
        
        // Create rating
        PenilaianPetugas::create([
            'buku_tamu_id' => $validated['buku_tamu_id'],
            'user_id' => $validated['user_id'],
            'rating_keramahan' => $validated['rating_keramahan'],
            'rating_kecepatan' => $validated['rating_kecepatan'],
            'rating_pengetahuan' => $validated['rating_pengetahuan'],
            'rating_keseluruhan' => $validated['rating_keseluruhan'],
            'komentar' => $validated['komentar'] ?? null,
        ]);
        
        // Mark buku tamu as rated
        $bukuTamu->update(['rated' => true]);
        
        return response()->json([
            'success' => true,
            'message' => 'Terima kasih atas penilaian Anda!'
        ]);
    }
    
    /**
     * Show rating form (token-based)
     */
    public function show($token)
    {
        $bukuTamu = BukuTamu::where('rating_token', $token)
            ->with(['user', 'petugasOnline'])
            ->first();
        
        if (!$bukuTamu) {
            return view('rating.invalid');
        }
        
        if ($bukuTamu->rated) {
            return view('rating.already_rated', compact('bukuTamu'));
        }

        // Determine which officer is being rated
        $officer = ($bukuTamu->sarana_kunjungan === 'Online' && $bukuTamu->online_channel === 'Pegawai' && $bukuTamu->petugasOnline) 
            ? $bukuTamu->petugasOnline 
            : $bukuTamu->user;
        
        return view('rating.form', compact('bukuTamu', 'officer'));
    }
    
    /**
     * Submit rating (token-based)
     */
    public function store(Request $request, $token)
    {
        $bukuTamu = BukuTamu::where('rating_token', $token)->first();
        
        if (!$bukuTamu) {
            return response()->json(['error' => 'Token tidak valid'], 404);
        }
        
        if ($bukuTamu->rated) {
            return response()->json(['error' => 'Anda sudah memberikan penilaian'], 400);
        }
        
        $validated = $request->validate([
            'rating_keramahan' => 'required|integer|min:1|max:5',
            'rating_kecepatan' => 'required|integer|min:1|max:5',
            'rating_pengetahuan' => 'required|integer|min:1|max:5',
            'rating_keseluruhan' => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:500',
        ]);
        
        // Determine which officer is being rated
        $officerId = ($bukuTamu->sarana_kunjungan === 'Online' && $bukuTamu->online_channel === 'Pegawai' && $bukuTamu->petugas_online_id) 
            ? $bukuTamu->petugas_online_id 
            : $bukuTamu->user_id;

        // Create rating
        PenilaianPetugas::create([
            'buku_tamu_id' => $bukuTamu->id,
            'user_id' => $officerId,
            'rating_keramahan' => $validated['rating_keramahan'],
            'rating_kecepatan' => $validated['rating_kecepatan'],
            'rating_pengetahuan' => $validated['rating_pengetahuan'],
            'rating_keseluruhan' => $validated['rating_keseluruhan'],
            'komentar' => $validated['komentar'] ?? null,
        ]);
        
        // Mark as rated
        $bukuTamu->update(['rated' => true]);
        
        return response()->json([
            'success' => true,
            'message' => 'Terima kasih atas penilaian Anda!'
        ]);
    }
}

