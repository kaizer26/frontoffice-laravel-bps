<?php

namespace App\Http\Controllers;

use App\Models\PermintaanData;
use App\Models\BukuTamu;
use App\Models\LaporanLayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function myServices()
    {
        $services = BukuTamu::where(function($q) {
                $q->where('user_id', auth()->id())
                  ->orWhere('petugas_online_id', auth()->id());
            })
            ->with(['permintaanData', 'petugasOnline', 'laporanLayanan'])
            ->latest('tanggal_update')
            ->get()
            ->map(function ($bt) {
                $permintaan = $bt->permintaanData->first();
                return [
                    'id' => $bt->id,
                    'is_buku_tamu' => true,
                    'nama_pengunjung' => $bt->nama_pengunjung,
                    'instansi' => $bt->instansi,
                    'no_hp' => $bt->no_hp,
                    'email' => $bt->email,
                    'keperluan' => $bt->keperluan,
                    'jenis_layanan' => $bt->jenis_layanan,
                    'nomor_surat' => optional($permintaan)->nomor_surat ?? '-',
                    'permintaan_data_id' => optional($permintaan)->id,
                    'tanggal_surat' => optional($permintaan)->tanggal_surat ? $permintaan->tanggal_surat->format('Y-m-d') : null,
                    'file_surat' => ($permintaan && $permintaan->file_surat) ? asset('storage/' . $permintaan->file_surat) : null,
                    'surat_lengkap' => $permintaan && !empty($permintaan->nomor_surat) && $permintaan->tanggal_surat && $permintaan->file_surat,
                    'status_layanan' => $bt->status_layanan,
                    'tanggal_kunjungan' => $bt->waktu_kunjungan ? $bt->waktu_kunjungan->format('Y-m-d H:i') : null,
                    'tanggal_update' => $bt->tanggal_update ? $bt->tanggal_update->format('Y-m-d H:i') : $bt->created_at->format('Y-m-d H:i'),
                    'sarana_kunjungan' => $bt->sarana_kunjungan,
                    'online_channel' => $bt->online_channel,
                    'petugas_online_id' => $bt->petugas_online_id,
                    'nama_petugas_online' => $bt->petugasOnline->name ?? null,
                    'skd_token' => $bt->skd_token,
                    'rating_token' => $bt->rating_token,
                    'skd_filled' => (bool)$bt->skd_filled,
                    'rated' => (bool)$bt->rated,
                    // Short URL - only use stored shortlink, don't fallback to long URL
                    'remote_rating_url' => $bt->rating_short_url ?: null,
                    // Long URL - always generate fresh with officer info via Helper
                    'remote_rating_long_url' => \App\Helpers\GasSyncHelper::buildRatingUrl($bt->rating_token, $bt->petugasOnline ?: $bt->user),
                    // SKD Short URL - only use stored shortlink
                    'skd_short_url' => $bt->skd_short_url ?: null,
                    // SKD Long URL - always generate fresh
                    'skd_long_url' => $bt->skd_token ? (config('services.gas.skd_url') ?: "https://script.google.com/macros/s/AKfycbx6NAMQSZTBFuda4tpddVggCK87wr0pCLUxpCarjLJYH7OvbTXJ80j_fPLBAXtXWO0/exec") . (str_contains(config('services.gas.skd_url') ?: "exec", '?') ? '&' : '?') . "token=" . $bt->skd_token : null,
                    'link_monitor' => $bt->link_monitor,
                    'laporan' => $bt->laporanLayanan ? [
                        'topik' => $bt->laporanLayanan->topik,
                        'ringkasan' => $bt->laporanLayanan->ringkasan,
                        'feedback' => $bt->laporanLayanan->feedback_final,
                        'tags' => $bt->laporanLayanan->tags,
                        'foto_bukti' => $bt->laporanLayanan->foto_bukti ? array_map(fn($p) => asset('storage/'.$p), $bt->laporanLayanan->foto_bukti) : [],
                        'surat_balasan' => $bt->laporanLayanan->surat_balasan ? asset('storage/'.$bt->laporanLayanan->surat_balasan) : null,
                        'arsip_layanan' => $bt->laporanLayanan->arsip_layanan ? asset('storage/'.$bt->laporanLayanan->arsip_layanan) : null,
                    ] : null,
                ];
            });

        return response()->json($services);
    }

    public function allServices(Request $request)
    {
        $query = BukuTamu::with(['user', 'petugasOnline', 'permintaanData', 'laporanLayanan']);

        if ($request->status) {
            $query->where('status_layanan', $request->status);
        }

        if ($request->petugas_id) {
            $query->where(function($q) use ($request) {
                $q->where('user_id', $request->petugas_id)
                  ->orWhere('petugas_online_id', $request->petugas_id);
            });
        }

        $services = $query->latest('tanggal_update')
            ->get()
            ->map(function ($bt) {
                $permintaan = $bt->permintaanData->first();
                return [
                    'id' => $bt->id,
                    'is_buku_tamu' => true,
                    'nama_pengunjung' => $bt->nama_pengunjung,
                    'instansi' => $bt->instansi,
                    'no_hp' => $bt->no_hp,
                    'email' => $bt->email,
                    'keperluan' => $bt->keperluan,
                    'jenis_layanan' => $bt->jenis_layanan,
                    'nomor_surat' => optional($permintaan)->nomor_surat ?? '-',
                    'permintaan_data_id' => optional($permintaan)->id,
                    'tanggal_surat' => optional($permintaan)->tanggal_surat ? $permintaan->tanggal_surat->format('Y-m-d') : null,
                    'file_surat' => ($permintaan && $permintaan->file_surat) ? asset('storage/' . $permintaan->file_surat) : null,
                    'status_layanan' => $bt->status_layanan,
                    'tanggal_kunjungan' => $bt->waktu_kunjungan ? $bt->waktu_kunjungan->format('Y-m-d H:i') : null,
                    'tanggal_update' => $bt->tanggal_update ? $bt->tanggal_update->format('Y-m-d H:i') : $bt->created_at->format('Y-m-d H:i'),
                    'nama_petugas' => $bt->user->name ?? '',
                    'sarana_kunjungan' => $bt->sarana_kunjungan,
                    'online_channel' => $bt->online_channel,
                    'petugas_online_id' => $bt->petugas_online_id,
                    'nama_petugas_online' => $bt->petugasOnline->name ?? null,
                    'skd_token' => $bt->skd_token,
                    'rating_token' => $bt->rating_token,
                    'skd_filled' => (bool)$bt->skd_filled,
                    'rated' => (bool)$bt->rated,
                    'remote_rating_url' => $bt->rating_short_url ?: \App\Helpers\GasSyncHelper::buildRatingUrl($bt->rating_token, $bt->petugasOnline ?: $bt->user),
                    'remote_rating_long_url' => \App\Helpers\GasSyncHelper::buildRatingUrl($bt->rating_token, $bt->petugasOnline ?: $bt->user),
                    'skd_short_url' => $bt->skd_short_url,
                    'skd_long_url' => $bt->skd_token ? (config('services.gas.skd_url') ?: "https://script.google.com/macros/s/AKfycbx6NAMQSZTBFuda4tpddVggCK87wr0pCLUxpCarjLJYH7OvbTXJ80j_fPLBAXtXWO0/exec") . (str_contains(config('services.gas.skd_url') ?: "exec", '?') ? '&' : '?') . "token=" . $bt->skd_token : null,
                    'link_monitor' => $bt->link_monitor,
                    'laporan' => $bt->laporanLayanan ? [
                        'topik' => $bt->laporanLayanan->topik,
                        'ringkasan' => $bt->laporanLayanan->ringkasan,
                        'feedback' => $bt->laporanLayanan->feedback_final,
                        'tags' => $bt->laporanLayanan->tags,
                        'foto_bukti' => $bt->laporanLayanan->foto_bukti ? array_map(fn($p) => asset('storage/'.$p), $bt->laporanLayanan->foto_bukti) : [],
                        'surat_balasan' => $bt->laporanLayanan->surat_balasan ? asset('storage/'.$bt->laporanLayanan->surat_balasan) : null,
                        'arsip_layanan' => $bt->laporanLayanan->arsip_layanan ? asset('storage/'.$bt->laporanLayanan->arsip_layanan) : null,
                    ] : null,
                ];
            });

        return response()->json($services);
    }

    /**
     * Get handlers for a specific service
     */
    public function getHandlers($id)
    {
        $bt = BukuTamu::with('handlers')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'handlers' => $bt->handlers->map(fn($h) => [
                'user_id' => $h->user_id,
                'role' => $h->role
            ])
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status_layanan' => 'required|in:Diterima,Diproses,Menunggu Persetujuan,Siap Diambil,Selesai',
            'catatan' => 'nullable|string',
            'topik' => 'nullable|string|max:255',
            'ringkasan' => 'nullable|string',
            'feedback_final' => 'nullable|string|max:255',
            'tags' => 'nullable|string', // expectation: comma separated string to be converted to array
            'foto_bukti.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'surat_balasan' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $bt = BukuTamu::findOrFail($id);
        $bt->update([
            'status_layanan' => $validated['status_layanan'],
            'catatan' => $validated['catatan'] ?? $bt->catatan,
            'tanggal_update' => now(),
        ]);

        // Keep PermintaanData synced for compatibility
        $permintaan = $bt->permintaanData->first();
        if ($permintaan) {
            $permintaan->update([
                'status_layanan' => $validated['status_layanan'],
                'catatan' => $validated['catatan'] ?? $permintaan->catatan,
                'tanggal_update' => now(),
            ]);
        }

        // Handle LaporanLayanan
        $repoData = [
            'topik' => $request->topik,
            'ringkasan' => $request->ringkasan,
            'feedback_final' => $request->feedback_final,
        ];

        if ($request->tags) {
            $repoData['tags'] = array_map('trim', explode(',', $request->tags));
        }

        // Handle Multiple Photo Uploads
        if ($request->hasFile('foto_bukti')) {
            $photoPaths = [];
            foreach ($request->file('foto_bukti') as $photo) {
                $photoPaths[] = $photo->store('bukti', 'public');
            }
            $repoData['foto_bukti'] = $photoPaths;
        }

        // Handle PDF Uploads
        if ($request->hasFile('surat_balasan')) {
            $repoData['surat_balasan'] = $request->file('surat_balasan')->store('balasan', 'public');
        }

        // Create or update record
        $bt->laporanLayanan()->updateOrCreate(['buku_tamu_id' => $bt->id], $repoData);

        // Sync handlers (additional employees involved)
        if ($request->has('handlers')) {
            $handlersData = json_decode($request->handlers, true);
            if (is_array($handlersData)) {
                // Clear existing handlers and re-create
                $bt->handlers()->delete();
                foreach ($handlersData as $handler) {
                    if (!empty($handler['user_id'])) {
                        \App\Models\BukuTamuHandler::create([
                            'buku_tamu_id' => $bt->id,
                            'user_id' => $handler['user_id'],
                            'role' => $handler['role'] ?? 'Membantu',
                        ]);
                    }
                }
            }
        }

        $response = [
            'success' => true,
            'message' => 'Status dan Laporan berhasil diupdate',
            'skd_token' => $bt->skd_token,
            'whatsapp_group_link' => \App\Models\SystemSetting::get('whatsapp_group_link', 'https://chat.whatsapp.com/DPrCxwvtrX3DP6Gu84YOef'),
            'visitor_name' => $bt->nama_pengunjung,
            'visitor_instansi' => $bt->instansi,
            'visitor_service' => $bt->jenis_layanan,
            'visitor_purpose' => $bt->keperluan,
            'visitor_phone' => $bt->no_hp,
            'visitor_email' => $bt->email,
            'link_monitor' => $bt->link_monitor,
            'status' => $bt->status_layanan,
            'remote_rating_url' => $bt->rating_short_url ?: \App\Helpers\GasSyncHelper::buildRatingUrl($bt->rating_token, $bt->petugasOnline ?: $bt->user),
        ];

        return response()->json($response);
    }

    public function updateVisitor(Request $request, $id)
    {
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

        $bt = BukuTamu::findOrFail($id);

        // Sanitize phone number
        $noHp = preg_replace('/\D/', '', $validated['no_hp']);
        if (str_starts_with($noHp, '0')) {
            $noHp = '62' . substr($noHp, 1);
        } elseif (str_starts_with($noHp, '8')) {
            $noHp = '62' . $noHp;
        }

        $bt->update([
            'nama_pengunjung' => $validated['nama_pengunjung'],
            'instansi' => $validated['instansi'],
            'no_hp' => $noHp,
            'email' => $validated['email'],
            'jenis_layanan' => implode(', ', $validated['jenis_layanan']),
            'keperluan' => $validated['keperluan'],
            'sarana_kunjungan' => $validated['sarana_kunjungan'],
            'online_channel' => $validated['online_channel'] ?? null,
            'petugas_online_id' => $validated['petugas_online_id'] ?? null,
        ]);

        // Hande PermintaanData update if exists or if newly added
        $jenisLayanan = $validated['jenis_layanan'] ?? [];
        $isPermintaanData = in_array('Permintaan Data', $jenisLayanan);

        if ($isPermintaanData) {
            $permintaan = $bt->permintaanData->first();
            
            // Check for Nomor Surat uniqueness ONLY if it's changed or new
            $newNomorSurat = isset($validated['nomor_surat']) ? trim((string)$validated['nomor_surat']) : '';
            $oldNomorSurat = $permintaan ? trim((string)$permintaan->nomor_surat) : '';
            
            if ($newNomorSurat !== '' && $newNomorSurat !== $oldNomorSurat) {
                $existing = PermintaanData::where('nomor_surat', $newNomorSurat)
                    ->where('buku_tamu_id', '!=', $bt->id)
                    ->with('bukuTamu')
                    ->first();
                
                if ($existing) {
                    $namaLain = $existing->bukuTamu->nama_pengunjung ?? 'Data Lama/Tidak Diketahui';
                    return response()->json([
                        'success' => false,
                        'message' => "Nomor surat '{$newNomorSurat}' sudah digunakan oleh pengunjung: {$namaLain}. Silakan gunakan nomor lain atau periksa kembali data Anda.",
                    ], 422);
                }
            }
            $data = [
                'nomor_surat' => $validated['nomor_surat'],
                'tanggal_surat' => $validated['tanggal_surat'],
            ];

            if ($request->hasFile('file_surat')) {
                $data['file_surat'] = $request->file('file_surat')->store('surat', 'public');
            }

            if ($permintaan) {
                $permintaan->update($data);
            } else {
                $data['buku_tamu_id'] = $bt->id;
                $data['status_layanan'] = $bt->status_layanan;
                $data['tanggal_update'] = now();
                PermintaanData::create($data);
            }
        }

        // Sync handlers if provided
        if ($request->has('handlers')) {
            $handlersData = json_decode($request->handlers, true);
            if (is_array($handlersData)) {
                $bt->handlers()->delete();
                foreach ($handlersData as $handler) {
                    if (!empty($handler['user_id'])) {
                        \App\Models\BukuTamuHandler::create([
                            'buku_tamu_id' => $bt->id,
                            'user_id' => $handler['user_id'],
                            'role' => $handler['role'] ?? 'Membantu',
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Data pengunjung berhasil diperbarui',
        ]);
    }

    public function updateMonitorLink(Request $request, $id)
    {
        $validated = $request->validate([
            'link_monitor' => 'nullable|url|max:255',
        ]);

        $bt = BukuTamu::findOrFail($id);
        $bt->update([
            'link_monitor' => $validated['link_monitor'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Link monitoring berhasil disimpan',
        ]);
    }
}
