<?php

namespace App\Http\Controllers;

use App\Models\DataRequestReply;
use App\Models\PermintaanData;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Helpers\ActivityLogger;

class DataRequestReplyController extends Controller
{
    /**
     * Generate the next letter number based on settings and last used number.
     */
    public function generateNumber(Request $request)
    {
        $year = Carbon::now()->year;
        $kodeSurat = $request->input('kode_surat') ?: SystemSetting::get('reply_letter_default_code', '02.04');
        
        // Find the last sequence number for the current year
        $lastReply = DataRequestReply::whereYear('tanggal_surat', $year)
            ->orderBy('nomor_urut', 'desc')
            ->first();
            
        $nextUrut = $lastReply ? $lastReply->nomor_urut + 1 : 1;
        
        $format = SystemSetting::get('reply_letter_format', 'B-{nomor_urut}/63101/{kode_surat}/{tahun}');
        
        $nomorSurat = str_replace(
            ['{nomor_urut}', '{kode_surat}', '{tahun}'],
            [$nextUrut, $kodeSurat, $year],
            $format
        );
        
        return response()->json([
            'success' => true,
            'nomor_surat' => $nomorSurat,
            'nomor_urut' => $nextUrut,
            'kode_surat' => $kodeSurat,
            'tahun' => $year
        ]);
    }

    /**
     * Store a new reply.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'permintaan_data_id' => 'required|exists:permintaan_data,id',
            'nomor_surat' => 'required|string|unique:permintaan_data_replies,nomor_surat',
            'nomor_urut' => 'required|integer',
            'tujuan' => 'required|string|max:255',
            'perihal' => 'nullable|string|max:255',
            'tanggal_surat' => 'required|date',
            'kode_surat' => 'required|string|max:50',
            'catatan' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();
            
            $reply = DataRequestReply::create($validated);
            
            // Link back to PermintaanData (if needed, but already linked via permintaan_data_id)
            // We might want to update the status of the request automatically?
            // For now, let's just create the reply.

            ActivityLogger::log('CREATE_REPLY_LETTER', 'DataRequestReply', $reply->id, "Membuat surat balasan: {$reply->nomor_surat}");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Surat balasan berhasil disimpan',
                'reply' => $reply
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan surat balasan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reply details for a specific request.
     */
    public function show($requestId)
    {
        $reply = DataRequestReply::where('permintaan_data_id', $requestId)->first();
        
        if (!$reply) {
            return response()->json([
                'success' => true,
                'reply' => null,
                'message' => 'Belum ada surat balasan.'
            ]);
        }
        
        return response()->json([
            'success' => true,
            'reply' => $reply
        ]);
    }
}
