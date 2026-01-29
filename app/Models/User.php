<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nip_bps',
        'nip_pns',
        'name',
        'email',
        'no_hp',
        'password',
        'role',
        'status',
        'foto',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function bukuTamu()
    {
        return $this->hasMany(BukuTamu::class);
    }

    public function jadwalPetugas()
    {
        return $this->hasMany(JadwalPetugas::class);
    }

    public function penilaian()
    {
        return $this->hasMany(PenilaianPetugas::class);
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isPetugas()
    {
        return $this->role === 'petugas';
    }

    public function isAktif()
    {
        return $this->status === 'aktif';
    }

    /**
     * Get officers on duty based on current shifts (WITA)
     * Dynamic shifts from SystemSetting
     * Advance preview: 1 hour before next shift
     */
    public static function getDutyOfficers()
    {
        $now = now(); // Already Asia/Makassar
        $currentTime = $now->format('H:i');
        $today = $now->toDateString();
        $isFriday = $now->isFriday();

        // Base setting keys
        $prefix = $isFriday ? 'shift_friday_' : 'shift_';

        // Get limits and active status from SystemSetting
        $pagiStart = \App\Models\SystemSetting::get($prefix . 'pagi_start', '07:30');
        $pagiEnd = \App\Models\SystemSetting::get($prefix . 'pagi_end', $isFriday ? '11:30' : '12:00');
        $pagiActive = \App\Models\SystemSetting::get('shift_pagi_active', 'true') === 'true';

        $siangStart = \App\Models\SystemSetting::get($prefix . 'siang_start', $isFriday ? '13:30' : '12:00');
        $siangEnd = \App\Models\SystemSetting::get($prefix . 'siang_end', '14:30');
        $siangActive = \App\Models\SystemSetting::get('shift_siang_active', 'true') === 'true';

        $soreStart = \App\Models\SystemSetting::get($prefix . 'sore_start', '14:30');
        $soreEnd = \App\Models\SystemSetting::get($prefix . 'sore_end', $isFriday ? '16:30' : '16:00');
        $soreActive = \App\Models\SystemSetting::get('shift_sore_active', 'true') === 'true';

        $shiftsToShow = [];
        
        // Logic for Pagi
        if ($pagiActive && $currentTime >= $pagiStart && $currentTime < $pagiEnd) {
            $shiftsToShow[] = 'Pagi';
            // Preview Siang if Pagi ends in < 1 hour and Siang is active
            if ($siangActive) {
                $pagiEndObj = \Carbon\Carbon::createFromFormat('H:i', $pagiEnd);
                if ($now->diffInMinutes($pagiEndObj, false) <= 60) $shiftsToShow[] = 'Siang';
            }
        } 
        // Logic for Siang
        elseif ($siangActive && $currentTime >= $siangStart && $currentTime < $siangEnd) {
            $shiftsToShow[] = 'Siang';
            // Preview Sore if Siang ends in < 1 hour and Sore is active
            if ($soreActive) {
                $siangEndObj = \Carbon\Carbon::createFromFormat('H:i', $siangEnd);
                if ($now->diffInMinutes($siangEndObj, false) <= 60) $shiftsToShow[] = 'Sore';
            }
        }
        // Logic for Sore
        elseif ($soreActive && $currentTime >= $soreStart && $currentTime < $soreEnd) {
            $shiftsToShow[] = 'Sore';
            // Preview Tomorrow's Pagi if Sore ends in < 1 hour and Pagi is active
            $soreEndObj = \Carbon\Carbon::createFromFormat('H:i', $soreEnd);
            if ($now->diffInMinutes($soreEndObj, false) <= 60) {
                return self::whereHas('jadwalPetugas', function($q) use ($today) {
                    $q->where('tanggal', date('Y-m-d', strtotime($today . ' +1 day')))
                      ->where('shift', 'Pagi')
                      ->where('status', 'aktif');
                })->orWhere(function($q) use ($today, $shiftsToShow) {
                    $q->whereHas('jadwalPetugas', function($sq) use ($today, $shiftsToShow) {
                        $sq->where('tanggal', $today)->whereIn('shift', $shiftsToShow)->where('status', 'aktif');
                    });
                })->where('status', 'aktif')->get();
            }
        }

        // Fallback for outside hours (e.g. night)
        if (empty($shiftsToShow)) {
             // If night, show tomorrow's Pagi (if active)
             if ($currentTime >= $soreEnd) {
                 return self::whereHas('jadwalPetugas', function($q) use ($today) {
                    $q->where('tanggal', date('Y-m-d', strtotime($today . ' +1 day')))
                      ->where('shift', 'Pagi')
                      ->where('status', 'aktif');
                })->where('status', 'aktif')->get();
             }
             // If early morning, show today's Pagi (if active)
             return self::whereHas('jadwalPetugas', function($q) use ($today) {
                $q->where('tanggal', $today)
                  ->where('shift', 'Pagi')
                  ->where('status', 'aktif');
            })->where('status', 'aktif')->get();
        }

        return self::whereHas('jadwalPetugas', function($q) use ($today, $shiftsToShow) {
            $q->where('tanggal', $today)
              ->whereIn('shift', $shiftsToShow)
              ->where('status', 'aktif');
        })->where('status', 'aktif')->get();
    }


    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip_bps', 'nip_bps');
    }
}
