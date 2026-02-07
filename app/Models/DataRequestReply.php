<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataRequestReply extends Model
{
    use HasFactory;

    protected $table = 'permintaan_data_replies';

    protected $fillable = [
        'permintaan_data_id',
        'nomor_surat',
        'nomor_urut',
        'tujuan',
        'perihal',
        'tanggal_surat',
        'kode_surat',
        'catatan',
        'file_surat',
    ];

    public function request()
    {
        return $this->belongsTo(PermintaanData::class, 'permintaan_data_id');
    }
}
