<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class BluehostSuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::updateOrCreate(
            ['email' => 'bluehostmedia@gmail.com'],
            [
                'name' => 'Super Admin',
                'organization' => 'MBSP',
                'password' => 'Temp@1234',
                'is_active' => true,
                'must_change_password' => true,
                'email_verified_at' => now(),
            ]
        );

        $role = Role::where('slug', 'super-admin')->first();
        if ($role) {
            $superAdmin->roles()->syncWithoutDetaching([$role->id]);
        }
    }
}
