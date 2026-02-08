<?php

namespace App\Http\Controllers;

use App\Models\BukuTamu;
use App\Models\PermintaanData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Helpers\ActivityLogger;
use App\Helpers\UrlHelper;

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
                'jenis_layanan_lainnya' => 'nullable|string|max:255',
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

            $gasUrl = config('services.gas.rating_url');
            $remoteRatingUrl = null;
            $longRemoteRatingUrl = null;
            
            // Build rating URL with token and officer info via Helper
            $officer = !empty($validated['petugas_online_id']) 
                ? \App\Models\User::find($validated['petugas_online_id']) 
                : auth()->user();

            $longRemoteRatingUrl = \App\Helpers\GasSyncHelper::buildRatingUrl($ratingToken, $officer);
            
            if ($longRemoteRatingUrl) {
                $remoteRatingUrl = UrlHelper::shorten($longRemoteRatingUrl);
            }

            // Generate SKD Short URL if applicable
            $skdShortUrl = null;
            if ($skdToken) {
                $gasSkdUrl = config('services.gas.skd_url') ?? "https://script.google.com/macros/s/AKfycbx6NAMQSZTBFuda4tpddVggCK87wr0pCLUxpCarjLJYH7OvbTXJ80j_fPLBAXtXWO0/exec";
                $longSkdUrl = $gasSkdUrl . (str_contains($gasSkdUrl, '?') ? '&' : '?') . "token=" . $skdToken;
                $skdShortUrl = UrlHelper::shorten($longSkdUrl);
            }

            // Create buku tamu entry
            // Handle Lainnya custom text - replace "Lainnya" with actual custom text if provided
            $jenisLayananArray = $validated['jenis_layanan'];
            if (in_array('Lainnya', $jenisLayananArray) && !empty($validated['jenis_layanan_lainnya'])) {
                $jenisLayananArray = array_map(function($jenis) use ($validated) {
                    return $jenis === 'Lainnya' ? $validated['jenis_layanan_lainnya'] : $jenis;
                }, $jenisLayananArray);
            }
            
            $bukuTamu = BukuTamu::create([
                'waktu_kunjungan' => now(),
                'nama_pengunjung' => $validated['nama_pengunjung'],
                'instansi' => $validated['instansi'],
                'no_hp' => $noHp,
                'email' => $validated['email'],
                'jenis_layanan' => implode(', ', $jenisLayananArray),
                'keperluan' => $validated['keperluan'],
                'sarana_kunjungan' => $validated['sarana_kunjungan'],
                'online_channel' => $validated['online_channel'] ?? null,
                'petugas_online_id' => $validated['petugas_online_id'] ?? null,
                'user_id' => auth()->id(),
                'rating_token' => $ratingToken,
                'rating_short_url' => $remoteRatingUrl,
                'skd_token' => $skdToken,
                'skd_short_url' => $skdShortUrl,
            ]);

            // If there's a data request with Permintaan Data selected
            if ($isPermintaanData) {
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

            ActivityLogger::log('CREATE_BUKUTAMU', 'BukuTamu', $bukuTamu->id, "Registrasi pengunjung baru: {$bukuTamu->nama_pengunjung}");

            // Generate links for response
            $ratingUrl = ($validated['sarana_kunjungan'] === 'Langsung') ? route('rating.show', $ratingToken) : null;

            return response()->json([
                'success' => true,
                'message' => 'Buku tamu berhasil ditambahkan',
                'rating_url' => $ratingUrl,
                'remote_rating_url' => $remoteRatingUrl,
                'remote_rating_long_url' => $longRemoteRatingUrl ?? null,
                'rating_token' => $ratingToken,
                'skd_token' => $skdToken,
                'skd_short_url' => $skdShortUrl,
                'skd_long_url' => $longSkdUrl ?? null,
                'whatsapp_group_link' => \App\Models\SystemSetting::get('whatsapp_group_link', 'https://chat.whatsapp.com/DPrCxwvtrX3DP6Gu84YOef'),
                'visitor_name' => $bukuTamu->nama_pengunjung,
                'visitor_instansi' => $bukuTamu->instansi,
                'visitor_service' => $bukuTamu->jenis_layanan,
                'visitor_purpose' => $bukuTamu->keperluan,
                'visitor_phone' => $bukuTamu->no_hp,
                'visitor_email' => $bukuTamu->email,
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

        $visitors = BukuTamu::where('nama_pengunjung', 'LIKE', "%$query%")
            ->orWhere('no_hp', 'LIKE', "%$query%")
            ->select('nama_pengunjung', 'no_hp', 'email', 'instansi')
            ->orderBy('waktu_kunjungan', 'desc')
            ->get()
            ->unique('no_hp')
            ->take(10)
            ->values();

        return response()->json($visitors);
    }

    /**
     * Send confirmation email notification to visitor
     */
    public function sendVisitorNotification(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string',
            'instansi' => 'nullable|string',
            'layanan' => 'nullable|string',
            'keperluan' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $status = $validated['status'] ?? 'Diterima';

        try {
            $emailContent = "Yth. {$validated['name']},\n\n";
            $emailContent .= "Terima kasih telah mengunjungi Pelayanan Statistik Terpadu (PST) Badan Pusat Statistik.\n\n";
            $emailContent .= "Berikut adalah data kunjungan Anda:\n";
            $emailContent .= "• Layanan: {$validated['layanan']}\n";
            $emailContent .= "• Instansi: " . ($validated['instansi'] ?: '-') . "\n";
            $emailContent .= "• Keperluan: " . ($validated['keperluan'] ?: '-') . "\n";
            $emailContent .= "• Status: {$status}\n\n";
            $emailContent .= "Petugas kami akan segera memproses permintaan Anda. Mohon menunggu informasi selanjutnya.\n\n";
            $emailContent .= "Salam,\nTim PST BPS";

            \Illuminate\Support\Facades\Mail::raw($emailContent, function ($message) use ($validated) {
                $message->to($validated['email'])
                    ->subject('Konfirmasi Pendaftaran PST BPS');
            });

            ActivityLogger::log('SEND_VISITOR_EMAIL', 'BukuTamu', null, "Email konfirmasi dikirim ke {$validated['email']}");

            return response()->json([
                'success' => true,
                'message' => 'Email berhasil dikirim'
            ]);
        } catch (\Exception $e) {
            Log::error('Send visitor notification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email: ' . $e->getMessage()
            ], 500);
        }
    }
}
