<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'slug' => '1',
                'name' => 'Inspektur',
                'description' => 'Pimpinan yang melakukan investigasi dan pengambilan keputusan akhir',
            ],
            [
                'slug' => '2',
                'name' => 'Verifikator',
                'description' => 'Petugas yang memverifikasi dan memvalidasi laporan yang masuk',
            ],
            [
                'slug' => '3',
                'name' => 'Admin',
                'description' => 'Administrator sistem dengan akses penuh',
            ],
            [
                'slug' => '4',
                'name' => 'Pengadu',
                'description' => 'Pelapor internal (ASN/Pegawai)',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }
    }
}
