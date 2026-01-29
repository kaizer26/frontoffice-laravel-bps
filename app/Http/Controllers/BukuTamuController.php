<?php

namespace App\Http\Controllers;

use App\Models\BukuTamu;
use App\Models\PermintaanData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Helpers\ActivityLogger;

class BukuTamuController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_pengunjung' => 'required|string|max:255',
                'instansi' => 'required|string|max:255',
                'no_hp' => 'required|string|max:20',
                'email' => 'required|email|max:255',
                'jenis_layanan' => 'required|array',
                'keperluan' => 'required|string',
                'sarana_kunjungan' => 'required|string|in:Langsung,Online',
                'online_channel' => 'nullable|required_if:sarana_kunjungan,Online|string|in:Pegawai,Kontak Admin',
                'petugas_online_id' => 'nullable|required_if:online_channel,Pegawai|exists:users,id',
                'nomor_surat' => 'nullable|string|max:100',
                'tanggal_surat' => 'nullable|date',
                'file_surat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ]);

            // Check if Permintaan Data is selected and if nomor_surat already exists
            $jenisLayanan = $validated['jenis_layanan'] ?? [];
            if (in_array('Permintaan Data', $jenisLayanan) && !empty($validated['nomor_surat'])) {
                $exists = PermintaanData::where('nomor_surat', $validated['nomor_surat'])->exists();
                if ($exists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Nomor surat sudah ada dalam sistem. Silakan periksa kembali.',
                    ], 422);
                }
            }

            // Generate unique tokens
            $ratingToken = Str::random(32);
            $isPermintaanData = in_array('Permintaan Data', $validated['jenis_layanan'] ?? []);
            $skdToken = $isPermintaanData ? Str::random(32) : null;

            // Sanitize phone number - remove all non-digits
            $noHp = preg_replace('/\D/', '', $validated['no_hp']);
            // Convert 08xx to 628xx
            if (str_starts_with($noHp, '0')) {
                $noHp = '62' . substr($noHp, 1);
            } elseif (str_starts_with($noHp, '8')) {
                $noHp = '62' . $noHp;
            }

            // Create buku tamu entry
            $bukuTamu = BukuTamu::create([
                'waktu_kunjungan' => now(),
                'nama_konsumen' => $validated['nama_pengunjung'],
                'instansi' => $validated['instansi'],
                'no_hp' => $noHp,
                'email' => $validated['email'],
                'jenis_layanan' => implode(', ', $validated['jenis_layanan']),
                'keperluan' => $validated['keperluan'],
                'sarana_kunjungan' => $validated['sarana_kunjungan'],
                'online_channel' => $validated['online_channel'] ?? null,
                'petugas_online_id' => $validated['petugas_online_id'] ?? null,
                'user_id' => auth()->id(),
                'rating_token' => $ratingToken,
                'skd_token' => $skdToken,
            ]);

            // If there's a data request with Permintaan Data selected
            $jenisLayanan = $validated['jenis_layanan'] ?? [];
            $isPermintaanData = in_array('Permintaan Data', $jenisLayanan);
            
            if ($isPermintaanData && !empty($validated['nomor_surat'])) {
                $filePath = null;
                
                if ($request->hasFile('file_surat')) {
                    // Ensure directory exists
                    Storage::disk('public')->makeDirectory('surat');
                    $filePath = $request->file('file_surat')->store('surat', 'public');
                }

                PermintaanData::create([
                    'buku_tamu_id' => $bukuTamu->id,
                    'nomor_surat' => $validated['nomor_surat'],
                    'tanggal_surat' => $validated['tanggal_surat'] ?? now(),
                    'file_surat' => $filePath,
                    'status_layanan' => 'Diterima',
                    'tanggal_update' => now(),
                ]);
            }

            // Generate rating URL only if Langsung
            $ratingUrl = null;
            $remoteRatingUrl = null;
            
            if ($validated['sarana_kunjungan'] === 'Langsung') {
                $ratingUrl = route('rating.show', $ratingToken);
                
                // Cloud Rating Link (GAS)
                $gasUrl = config('services.gas.rating_url');
                if ($gasUrl) {
                    $remoteRatingUrl = $gasUrl . (str_contains($gasUrl, '?') ? '&' : '?') . "token=" . $ratingToken;
                }
            }

            ActivityLogger::log('CREATE_BUKUTAMU', 'BukuTamu', $bukuTamu->id, "Registrasi pengunjung baru: {$bukuTamu->nama_konsumen}");

            return response()->json([
                'success' => true,
                'message' => 'Buku tamu berhasil ditambahkan',
                'rating_url' => $ratingUrl,
                'remote_rating_url' => $remoteRatingUrl,
                'rating_token' => $ratingToken,
                'skd_token' => $skdToken,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all()),
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('BukuTamu store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function searchPengunjung(Request $request)
    {
        $query = $request->input('q');
        if (empty($query)) {
            return response()->json([]);
        }

        $visitors = BukuTamu::where('nama_konsumen', 'LIKE', "%$query%")
            ->orWhere('no_hp', 'LIKE', "%$query%")
            ->select('nama_konsumen', 'no_hp', 'email', 'instansi')
            ->orderBy('waktu_kunjungan', 'desc')
            ->get()
            ->unique('no_hp')
            ->take(10)
            ->values();

        return response()->json($visitors);
    }
}
