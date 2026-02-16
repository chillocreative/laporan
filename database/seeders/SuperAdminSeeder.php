<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@sistem-pelaporan.my'],
            [
                'name' => 'Super Admin',
                'password' => 'password123',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $role = Role::where('slug', 'super-admin')->first();
        if ($role) {
            $superAdmin->roles()->syncWithoutDetaching([$role->id]);
        }
    }
}
