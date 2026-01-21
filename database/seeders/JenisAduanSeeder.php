<?php

namespace Database\Seeders;

use App\Models\JenisAduan;
use Illuminate\Database\Seeder;

class JenisAduanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenisAduans = [
            [
                'slug' => '1',
                'name' => 'Pelanggaran Disiplin Pegawai',
                'description' => 'Laporan terkait pelanggaran disiplin oleh pegawai',
            ],
            [
                'slug' => '2',
                'name' => 'Penyalahgunaan Wewenang',
                'description' => 'Laporan terkait penyalahgunaan jabatan atau wewenang',
            ],
            [
                'slug' => '3',
                'name' => 'Mal Administrasi dan Pemerasan/Penganiayaan',
                'description' => 'Laporan terkait mal administrasi, pemerasan, atau penganiayaan',
            ],
            [
                'slug' => '4',
                'name' => 'Perlakuan Amoral/Perselingkuhan',
                'description' => 'Laporan terkait perilaku amoral atau perselingkuhan',
            ],
            [
                'slug' => '5',
                'name' => 'Korupsi',
                'description' => 'Laporan terkait tindak pidana korupsi',
            ],
            [
                'slug' => '6',
                'name' => 'Pelanggaran dalam Pengadaan Barang dan Jasa',
                'description' => 'Laporan terkait kecurangan dalam proses pengadaan',
            ],
            [
                'slug' => '7',
                'name' => 'Pungutan Liar/Percaloan/Suap',
                'description' => 'Laporan terkait pungli, percaloan, atau suap',
            ],
            [
                'slug' => '8',
                'name' => 'Narkoba',
                'description' => 'Laporan terkait penyalahgunaan narkoba',
            ],
        ];

        foreach ($jenisAduans as $jenis) {
            JenisAduan::updateOrCreate(
                ['slug' => $jenis['slug']],
                $jenis
            );
        }
    }
}
