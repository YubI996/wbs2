<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@wbs.bontangkota.go.id',
                'password' => Hash::make('password'),
                'role_id' => Role::ADMIN,
                'nip' => '199001012020011001',
                'phone' => '08123456789',
            ],
            [
                'name' => 'User Verifikator',
                'email' => 'verifikator@wbs.bontangkota.go.id',
                'password' => Hash::make('password'),
                'role_id' => Role::VERIFIKATOR,
                'nip' => '199001012020011002',
                'phone' => '08123456790',
            ],
            [
                'name' => 'User Inspektur',
                'email' => 'inspektur@wbs.bontangkota.go.id',
                'password' => Hash::make('password'),
                'role_id' => Role::INSPEKTUR,
                'nip' => '199001012020011003',
                'phone' => '08123456791',
            ],
            [
                'name' => 'Pengadu Internal',
                'email' => 'pengadu@wbs.bontangkota.go.id',
                'password' => Hash::make('password'),
                'role_id' => Role::PENGADU,
                'nip' => '199001012020011004',
                'nik' => '6474010101900001',
                'phone' => '08123456792',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
