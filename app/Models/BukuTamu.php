<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BukuTamu extends Model
{
    use HasFactory;

    protected $table = 'buku_tamu';

    protected $fillable = [
        'waktu_kunjungan',
        'nama_pengunjung',
        'instansi',
        'no_hp',
        'email',
        'jenis_layanan',
        'keperluan',
        'sarana_kunjungan',
        'online_channel',
        'petugas_online_id',
        'status_layanan',
        'tanggal_update',
        'catatan',
        'link_monitor',
        'user_id',
        'rating_token',
        'rating_short_url',
        'rated',
        'skd_token',
        'skd_short_url',
        'skd_filled',
    ];

    protected $casts = [
        'waktu_kunjungan' => 'datetime',
        'tanggal_update' => 'datetime',
        'rated' => 'boolean',
        'skd_filled' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function petugasOnline()
    {
        return $this->belongsTo(User::class, 'petugas_online_id');
    }

    public function permintaanData()
    {
        return $this->hasMany(PermintaanData::class);
    }

    public function penilaian()
    {
        return $this->hasOne(PenilaianPetugas::class);
    }

    public function laporanLayanan()
    {
        return $this->hasOne(LaporanLayanan::class);
    }

    public function handlers()
    {
        return $this->hasMany(BukuTamuHandler::class);
    }
}
