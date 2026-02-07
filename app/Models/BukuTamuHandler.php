<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BukuTamuHandler extends Model
{
    use HasFactory;

    protected $table = 'buku_tamu_handlers';

    protected $fillable = [
        'buku_tamu_id',
        'user_id',
        'role',
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
