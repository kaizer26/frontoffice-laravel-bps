<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AbsensiPetugas;
use App\Models\JadwalPetugas;
use App\Models\SystemSetting;
use Carbon\Carbon;
use App\Helpers\ActivityLogger;

class AbsensiController extends Controller
{
    public function status()
    {
        $user = auth()->user();
        $today = Carbon::today();
        
        $absensi = AbsensiPetugas::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();
            
        $schedule = JadwalPetugas::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();
            
        $shiftSettings = [
            's1_start' => SystemSetting::get('shift1_start', '08:00'),
            's1_end' => SystemSetting::get('shift1_end', '12:00'),
            's2_start' => SystemSetting::get('shift2_start', '12:00'),
            's2_end' => SystemSetting::get('shift2_end', '16:00'),
        ];
            
        return response()->json([
            'success' => true,
            'is_clocked_in' => (bool)$absensi,
            'has_clocked_out' => $absensi ? (bool)$absensi->jam_pulang : false,
            'absensi' => $absensi,
            'schedule' => $schedule,
            'shift_settings' => $shiftSettings,
            'ip' => request()->ip()
        ]);
    }

    public function clockIn(Request $request)
    {
        $user = auth()->user();
        $now = Carbon::now();
        $today = $now->toDateString();
        $ip = $request->ip();

        // 1. Check IP
        if (!$this->isIpAllowed($ip)) {
            return response()->json([
                'success' => false,
                'message' => "Akses ditolak. IP Anda ($ip) tidak terdaftar dalam jaringan kantor."
            ], 403);
        }

        // 2. Check if already clocked in
        $exists = AbsensiPetugas::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->exists();
            
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen masuk hari ini.'
            ]);
        }

        // 3. Get schedule and check shift
        $schedule = JadwalPetugas::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        $statusMasuk = 'Tepat Waktu';
        if ($schedule) {
            $shift = $schedule->shift;
            $shiftStartKey = "shift{$shift}_start";
            $shiftStart = SystemSetting::get($shiftStartKey, ($shift == 1 ? '08:00' : '12:00'));
            
            try {
                $startTime = Carbon::createFromFormat('H:i', $shiftStart)->setDateFrom($now);
                if ($now->greaterThan($startTime->addMinutes(1))) { // 1 min buffer
                    $statusMasuk = 'Terlambat';
                }
            } catch (\Exception $e) {
                // Ignore parsing errors
            }
        }

        $absensi = AbsensiPetugas::create([
            'user_id' => $user->id,
            'jadwal_id' => $schedule ? $schedule->id : null,
            'tanggal' => $today,
            'jam_masuk' => $now,
            'status_masuk' => $statusMasuk,
            'ip_address' => $ip,
            'keterangan' => $request->keterangan
        ]);

        ActivityLogger::log('CLOCK_IN', 'AbsensiPetugas', $absensi->id, "Petugas {$user->name} absen masuk ($statusMasuk)");

        return response()->json([
            'success' => true,
            'message' => 'Berhasil absen masuk. Semangat bekerja!',
            'absensi' => $absensi
        ]);
    }

    public function clockOut(Request $request)
    {
        $user = auth()->user();
        $now = Carbon::now();
        $today = $now->toDateString();

        $absensi = AbsensiPetugas::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->whereNull('jam_pulang')
            ->first();

        if (!$absensi) {
            return response()->json([
                'success' => false,
                'message' => 'Data absen masuk tidak ditemukan atau Anda sudah absen pulang.'
            ]);
        }

        $absensi->update([
            'jam_pulang' => $now
        ]);

        ActivityLogger::log('CLOCK_OUT', 'AbsensiPetugas', $absensi->id, "Petugas {$user->name} absen pulang");

        return response()->json([
            'success' => true,
            'message' => 'Berhasil absen pulang. Selamat beristirahat!',
            'absensi' => $absensi
        ]);
    }

    public function todaySummary()
    {
        $today = Carbon::today();
        $logs = AbsensiPetugas::with('user', 'jadwal')
            ->whereDate('tanggal', $today)
            ->orderBy('jam_masuk', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'logs' => $logs
        ]);
    }

    private function isIpAllowed($ip)
    {
        $allowed = SystemSetting::get('attendance_allowed_ips', '');
        if (empty($allowed)) return true; // Default allow all if not set
        
        $allowedList = array_map('trim', explode(',', $allowed));
        foreach ($allowedList as $pattern) {
            if ($pattern === '*' || $pattern === $ip) return true;
            
            // Support 192.168.1.* style
            $regex = str_replace(['.', '*'], ['\.', '.*'], $pattern);
            if (preg_match('/^' . $regex . '$/', $ip)) return true;
        }
        return false;
    }
}
