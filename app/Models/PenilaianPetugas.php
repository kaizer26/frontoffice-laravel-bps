<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenilaianPetugas extends Model
{
    use HasFactory;

    protected $table = 'penilaian_petugas';

    protected $fillable = [
        'buku_tamu_id',
        'user_id',
        'rating_keramahan',
        'rating_kecepatan',
        'rating_pengetahuan',
        'rating_keseluruhan',
        'komentar',
    ];

    // Relationships
    public function bukuTamu()
    {
        return $this->belongsTo(BukuTamu::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
