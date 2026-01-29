<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanLayanan extends Model
{
    use HasFactory;

    protected $table = 'laporan_layanan';

    protected $fillable = [
        'buku_tamu_id',
        'topik',
        'ringkasan',
        'foto_bukti',
        'feedback_final',
        'surat_balasan',
        'tags',
    ];

    protected $casts = [
        'foto_bukti' => 'array',
        'tags' => 'array',
    ];

    // Relationships
    public function bukuTamu()
    {
        return $this->belongsTo(BukuTamu::class);
    }
}
