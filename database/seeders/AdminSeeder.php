<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'mpn@pulaupinang'],
            [
                'name' => 'MPN Pulau Pinang',
                'password' => 'Mpn@pp2026!',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $role = Role::where('slug', 'admin')->first();
        if ($role) {
            $admin->roles()->syncWithoutDetaching([$role->id]);
        }
    }
}
