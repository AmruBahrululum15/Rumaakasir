<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\InitialSupervisorSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ProvisionDemoAccount extends Command
{
    protected $signature = 'demo:provision
        {--username=demo : Username akun demo}
        {--email=demo@rumaakasir.test : Email akun demo}
        {--password= : Password akun demo (minimal 12 karakter)}
        {--business=Rumaakasir Demo : Nama toko demo}
        {--reset-password : Ganti password jika akun demo sudah ada}';

    protected $description = 'Create or update an isolated, shareable demo tenant and supervisor account';

    public function handle(): int
    {
        $username = trim((string) $this->option('username'));
        $email = Str::lower(trim((string) $this->option('email')));
        $password = (string) $this->option('password');
        $businessName = trim((string) $this->option('business'));

        if (! preg_match('/^[A-Za-z0-9_-]{3,100}$/', $username)) {
            $this->error('Username demo hanya boleh berisi huruf, angka, garis bawah, atau tanda hubung (3–100 karakter).');
            return self::FAILURE;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || $businessName === '') {
            $this->error('Email dan nama toko demo harus valid.');
            return self::FAILURE;
        }

        if ($password === '' || Str::length($password) < 12) {
            $this->error('Isi --password dengan minimal 12 karakter. Password tidak disimpan di source code.');
            return self::FAILURE;
        }

        // Ensure the standard role and its existing permission set are ready.
        (new InitialSupervisorSeeder())->ensureRolesAndPermissions();

        try {
            [$business, $user, $created] = DB::transaction(function () use ($username, $email, $password, $businessName): array {
                $business = Business::query()->firstOrCreate(
                    ['slug' => 'rumaakasir-demo'],
                    [
                        'name' => $businessName,
                        'is_active' => true,
                        'subscription_plan' => 'free',
                        'subscription_status' => 'active',
                    ],
                );

                $otherEmailOwner = User::withoutGlobalScopes()
                    ->where('email', $email)
                    ->where('username', '!=', $username)
                    ->first();
                if ($otherEmailOwner) {
                    throw new \RuntimeException('Email tersebut sudah digunakan akun lain. Gunakan email demo yang berbeda.');
                }

                $context = app(TenantContext::class);
                $context->set($business);

                try {
                    $user = User::withoutGlobalScopes()->firstOrNew(['username' => $username]);

                    if ($user->exists && (int) $user->business_id !== (int) $business->id) {
                        throw new \RuntimeException('Username tersebut sudah digunakan tenant lain. Gunakan username demo yang berbeda.');
                    }

                    $created = ! $user->exists;
                    $user->forceFill([
                        'name' => 'Pengguna Demo',
                        'email' => $email,
                        'is_active' => true,
                        'status' => 'approved',
                        'must_change_credentials' => false,
                    ]);

                    if ($created || $this->option('reset-password')) {
                        $user->password = Hash::make($password);
                    }

                    $user->save();
                } finally {
                    $context->clear();
                }

                $business->forceFill([
                    'name' => $businessName,
                    'owner_id' => $user->id,
                    'is_active' => true,
                    'subscription_plan' => 'free',
                    'subscription_status' => 'active',
                ])->save();

                $user->syncRoles([Role::findByName('Atasan', 'web')]);

                return [$business, $user, $created];
            });
        } catch (\Throwable $exception) {
            report($exception);
            $this->error($exception->getMessage());
            return self::FAILURE;
        }

        $this->components->info($created ? 'Akun dan tenant demo berhasil dibuat.' : 'Akun demo berhasil diperbarui.');
        $this->line('Username: '.$user->username);
        $this->line('Tenant: '.$business->name);
        $this->comment('Bagikan password melalui kanal yang aman. Gunakan --reset-password bila perlu menggantinya.');

        return self::SUCCESS;
    }
}
