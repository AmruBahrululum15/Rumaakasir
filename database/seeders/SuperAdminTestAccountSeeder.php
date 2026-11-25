<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminTestAccountSeeder extends Seeder
{
    public function run(): void
    {
        (new SuperAdminRoleSeeder())->run();

        // Do not overwrite an existing account that happens to use either
        // identifier. This seed only creates the requested test account once.
        if (User::query()->where('username', 'superadmin')->orWhere('email', 'superadmin@pos-enterprise.local')->exists()) {
            return;
        }

        $user = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'email' => 'superadmin@pos-enterprise.local',
            'password' => Hash::make('superadmin123'),
            'status' => 'approved',
            'is_active' => true,
            'must_change_credentials' => true,
        ]);

        $user->assignRole(Role::findByName('Super Admin', 'web'));
    }
}
