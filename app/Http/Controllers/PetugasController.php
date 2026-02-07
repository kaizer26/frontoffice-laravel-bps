<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PetugasController extends Controller
{
    public function dashboard()
    {
        $userId = auth()->id();
        $today = \Carbon\Carbon::today();
        
        // 1. Check if on duty right now
        // Assuming shift 1: 08:00-12:00, shift 2: 12:00-16:00 (adjustable by settings)
        $now = \Carbon\Carbon::now();
        $currentShift = 0;
        $currentTime = $now->format('H:i');
        
        $s1_start = \App\Models\SystemSetting::get('shift1_start', '08:00');
        $s1_end = \App\Models\SystemSetting::get('shift1_end', '12:00');
        $s2_start = \App\Models\SystemSetting::get('shift2_start', '12:00');
        $s2_end = \App\Models\SystemSetting::get('shift2_end', '16:00');

        if ($currentTime >= $s1_start && $currentTime < $s1_end) $currentShift = 1;
        elseif ($currentTime >= $s2_start && $currentTime < $s2_end) $currentShift = 2;

        $isOnDuty = \App\Models\JadwalPetugas::where('user_id', $userId)
            ->whereDate('tanggal', $today)
            ->where('shift', $currentShift)
            ->exists();

        // 2. Get upcoming schedules (next 5)
        $upcomingSchedules = \App\Models\JadwalPetugas::where('user_id', $userId)
            ->where(function($q) use ($today, $currentShift) {
                $q->whereDate('tanggal', '>', $today)
                  ->orWhere(function($q2) use ($today, $currentShift) {
                      $q2->whereDate('tanggal', $today)
                         ->where('shift', '>', $currentShift);
                  });
            })
            ->orderBy('tanggal', 'asc')
            ->orderBy('shift', 'asc')
            ->take(5)
            ->get();

        // Get all active officers for selection dropdowns
        $petugas = \App\Models\User::where('role', 'petugas')
            ->where('status', 'aktif')
            ->orderBy('name')
            ->get();
            
        return view('petugas.dashboard', compact('petugas', 'isOnDuty', 'upcomingSchedules'));
    }
}
