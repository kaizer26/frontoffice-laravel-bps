<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPetugas extends Model
{
    use HasFactory;

    protected $table = 'jadwal_petugas';

    protected $fillable = [
        'tanggal',
        'shift',
        'user_id',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
