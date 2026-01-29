<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pegawai;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        // Sample pegawai data
        $pegawaiData = [
            [
                'nip_bps' => '123456789',
                'nip_pns' => '199001012015011001',
                'nama' => 'Petugas Satu',
                'jabatan' => 'Kepala Seksi Statistik Sosial',
                'golongan' => 'III/d',
                'email_bps' => 'petugas1@bps.go.id',
            ],
            [
                'nip_bps' => '987654321',
                'nip_pns' => '199002022015011002',
                'nama' => 'Petugas Dua',
                'jabatan' => 'Kepala Seksi Statistik Produksi',
                'golongan' => 'III/c',
                'email_bps' => 'petugas2@bps.go.id',
            ],
            [
                'nip_bps' => '111222333',
                'nip_pns' => '199003032015011003',
                'nama' => 'Petugas Tiga',
                'jabatan' => 'Kepala Seksi Statistik Distribusi',
                'golongan' => 'III/b',
                'email_bps' => 'petugas3@bps.go.id',
            ],
        ];

        foreach ($pegawaiData as $data) {
            Pegawai::create($data);
        }
    }
}
