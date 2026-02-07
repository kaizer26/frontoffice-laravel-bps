<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiPetugas extends Model
{
    protected $table = 'absensi_petugas';

    protected $fillable = [
        'user_id',
        'jadwal_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'status_masuk',
        'ip_address',
        'keterangan'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime',
        'jam_pulang' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jadwal()
    {
        return $this->belongsTo(JadwalPetugas::class, 'jadwal_id');
    }
}
