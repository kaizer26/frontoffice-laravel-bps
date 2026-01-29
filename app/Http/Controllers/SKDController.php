<?php

namespace App\Http\Controllers;

use App\Models\BukuTamu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SKDController extends Controller
{
    /**
     * Mark SKD as filled for a given token.
     * This endpoint is called by the frontend after confirming with GAS.
     */
    public function markAsFilled(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string|exists:buku_tamu,skd_token',
        ]);

        try {
            $bukuTamu = BukuTamu::where('skd_token', $validated['token'])->firstOrFail();
            
            if (!$bukuTamu->skd_filled) {
                $bukuTamu->update(['skd_filled' => true]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Status SKD berhasil diperbarui',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'SKD sudah ditandai sebagai diisi sebelumnya',
            ]);

        } catch (\Exception $e) {
            Log::error('SKD markAsFilled error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status SKD',
            ], 500);
        }
    }
}
