<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Full system control and configuration',
                'is_system' => true,
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Report and user management',
                'is_system' => true,
            ],
            [
                'name' => 'User',
                'slug' => 'user',
                'description' => 'Self-service reporting',
                'is_system' => true,
            ],
            [
                'name' => 'MPKK',
                'slug' => 'mpkk',
                'description' => 'Majlis Pengurusan Kampung/Komuniti — pengurusan penyata kewangan dan minit mesyuarat',
                'is_system' => false,
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
