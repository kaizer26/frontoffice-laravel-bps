<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanData extends Model
{
    use HasFactory;

    protected $table = 'permintaan_data';

    protected $fillable = [
        'buku_tamu_id',
        'nomor_surat',
        'tanggal_surat',
        'file_surat',
        'status_layanan',
        'tanggal_update',
        'catatan',
        'link_skd',
        'skd_terisi',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'tanggal_update' => 'datetime',
        'skd_terisi' => 'boolean',
    ];

    // Relationships
    public function bukuTamu()
    {
        return $this->belongsTo(BukuTamu::class);
    }

    // Get petugas through bukuTamu
    public function petugas()
    {
        return $this->bukuTamu->user ?? null;
    }
}
