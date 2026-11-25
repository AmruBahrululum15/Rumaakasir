<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class InitialSupervisorSeeder extends Seeder
{
    public function run(): void
    {
        // Registration may request any of these established business roles.
        // Creating missing records is idempotent and lets approval assign the
        // approved role without a ModelNotFoundException / 404.
        collect(['Admin', 'Gudang', 'Kasir', 'Atasan', 'Super Admin'])
            ->each(fn (string $name) => Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']));

        $role = Role::findByName('Atasan', 'web');
        // A first supervisor must be able to operate the existing POS after
        // completing first-login. These are exactly the permissions enforced
        // by current module routes, not a separate authorization system.
        $permissions = collect([
            'dashboard.view',
            'categories.create', 'categories.delete', 'categories.edit', 'categories.view',
            'customers.delete', 'customers.edit', 'customers.view',
            'products.create', 'products.delete', 'products.edit', 'products.view',
            'purchases.create', 'purchases.delete', 'purchases.edit', 'purchases.view',
            'reports.view',
            'sales.create', 'sales.delete', 'sales.edit', 'sales.view', 'sales.approve-refunds',
            'settings.edit', 'settings.view',
            'suppliers.create', 'suppliers.delete', 'suppliers.edit', 'suppliers.view',
            'tables.create', 'tables.delete', 'tables.edit', 'tables.view',
            'users.create', 'users.delete', 'users.edit', 'users.view',
            'users.approve', 'users.reject',
        ])
            ->map(fn (string $name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']));
        $role->givePermissionTo($permissions);
        Role::findByName('Super Admin', 'web')->syncPermissions($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // The role/status check remains true after the setup username is changed.
        if (User::role('Atasan')->where('status', 'approved')->where('is_active', true)->exists()) {
            return;
        }

        $email = env('INITIAL_SUPERVISOR_EMAIL', 'initial-supervisor@example.invalid');

        // Never repurpose an existing account that happens to use the configured
        // address. An operator can choose another INITIAL_SUPERVISOR_EMAIL and
        // run this idempotent seeder again if that collision ever occurs.
        if (User::where('email', $email)->exists()) {
            return;
        }

        $user = User::create([
            'email' => $email,
            'name' => 'Atasan Utama',
            'username' => 'atasan',
            'password' => Hash::make('atasan123'),
            'status' => 'approved',
            'is_active' => true,
            'must_change_credentials' => true,
        ]);
        $user->assignRole($role);
    }
}
