<?php

namespace App\Http\Controllers;

use App\Models\BukuTamu;
use App\Models\PermintaanData;
use App\Models\PenilaianPetugas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatsController extends Controller
{
    public function petugas()
    {
        $userId = auth()->id();
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();

        // Visitor stats
        $visitorsToday = BukuTamu::where('user_id', $userId)
            ->where('waktu_kunjungan', 'like', $today->toDateString() . '%')
            ->count();

        $visitorsWeek = BukuTamu::where('user_id', $userId)
            ->whereBetween('waktu_kunjungan', [$weekStart, now()])
            ->count();

        $visitorsTotal = BukuTamu::where('user_id', $userId)->count();

        // Rating
        $avgRating = PenilaianPetugas::where('user_id', $userId)
            ->avg('rating_keseluruhan') ?? 0;

        return response()->json([
            'visitors' => [
                'today' => $visitorsToday,
                'week' => $visitorsWeek,
                'total' => $visitorsTotal,
            ],
            'rating' => [
                'average' => round($avgRating, 1),
            ],
        ]);
    }

    public function admin()
    {
        $today = Carbon::today();

        // Visitor stats
        $visitorsToday = BukuTamu::where('waktu_kunjungan', 'like', $today->toDateString() . '%')->count();

        // Service stats
        $servicesTotal = PermintaanData::count();
        $servicesDiterima = PermintaanData::where('status_layanan', 'Diterima')->count();
        $servicesDiproses = PermintaanData::where('status_layanan', 'Diproses')->count();
        $servicesPending = $servicesDiterima + $servicesDiproses;

        // Officer count
        $officersActive = User::where('role', 'petugas')
            ->where('status', 'aktif')
            ->count();

        // Visitor trends (last 7 days)
        $visitorTrends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->toDateString();
            $visitorTrends[] = [
                'date' => $date->translatedFormat('d M'),
                'count' => BukuTamu::where('waktu_kunjungan', 'like', $dateStr . '%')->count()
            ];
        }

        // Overall rating
        $avgRating = PenilaianPetugas::avg('rating_keseluruhan') ?? 0;

        return response()->json([
            'visitors' => [
                'today' => $visitorsToday,
                'trends' => $visitorTrends,
            ],
            'services' => [
                'total' => $servicesTotal,
                'pending' => $servicesPending,
            ],
            'officers' => [
                'active' => $officersActive,
            ],
            'rating' => [
                'average' => round($avgRating, 1),
            ]
        ]);
    }
}
