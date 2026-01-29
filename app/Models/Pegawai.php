<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawai';

    protected $fillable = [
        'nip_bps',
        'nip_pns',
        'nama',
        'jabatan',
        'golongan',
        'email_bps',
    ];
}
