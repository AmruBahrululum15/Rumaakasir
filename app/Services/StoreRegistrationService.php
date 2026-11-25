<?php

namespace App\Services;

use App\Mail\StoreRegistrationMail;
use App\Models\{Business, StoreRegistration, User, Setting};
use App\Notifications\StoreRegistrationNotification;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\{DB, Log, Mail, URL, Validator};
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Throwable;

class StoreRegistrationService
{
    public function received(StoreRegistration $registration): void
    {
        $this->safely($registration, 'acknowledgment', fn () => Mail::to($registration->owner_email)->send(new StoreRegistrationMail($registration, 'received')));
        $admins = User::withoutGlobalScope('business')->whereHas('roles', fn ($q) => $q->where('name', 'Super Admin')->where('guard_name', 'web'))->get();
        foreach ($admins as $admin) {
            $this->safely($registration, 'admin_notification', fn () => $admin->notify(new StoreRegistrationNotification($registration)));
            if ($admin->email) $this->safely($registration, 'admin_email', fn () => Mail::to($admin->email)->send(new StoreRegistrationMail($registration, 'review', route('super-admin.store-registrations.show', $registration))));
        }
    }

    public function review(StoreRegistration $registration, User $reviewer, bool $approve, ?string $reason = null): StoreRegistration
    {
        [$current, $changed] = DB::transaction(function () use ($registration, $reviewer, $approve, $reason) {
            $current = StoreRegistration::lockForUpdate()->findOrFail($registration->id);
            if ($current->status !== 'pending') {
                abort_unless($current->status === ($approve ? 'approved' : 'rejected'), 409, 'Pengajuan sudah diproses.');
                return [$current, false];
            }
            if ($approve) {
                Validator::make($current->toArray(), [
                    'owner_username' => ['required', Rule::unique('users', 'username')],
                    'owner_email' => ['required', 'email'],
                ])->validate();
                $business = Business::create(['name' => $current->store_name, 'phone' => $current->store_phone,
                    'address' => $current->address, 'is_active' => true, 'subscription_plan' => 'free',
                    'subscription_status' => $current->requested_plan === 'premium' ? 'pending_payment' : 'active']);
                $business->forceFill(['subscription_source' => 'store_registration',
                    'business_type' => app(BusinessModules::class)->normalize($current->business_type)])->save();
                $context = app(TenantContext::class);
                $previous = $context->business();
                $context->set($business);
                try {
                    Setting::setValue('app_name', $business->name);
                    $owner = User::create(['name' => $current->owner_name, 'username' => $current->owner_username,
                        'email' => $current->owner_email, 'no_telepon' => $current->owner_phone,
                        'password' => Str::random(64), 'status' => 'approved', 'is_active' => true,
                        'must_change_credentials' => true]);
                    $owner->assignRole(Role::findOrCreate('Atasan', 'web'));
                } finally {
                    $previous ? $context->set($previous) : $context->clear();
                }
                $business->forceFill(['owner_id' => $owner->id])->save();
                $current->business_id = $business->id;
                $current->owner_id = $owner->id;
            }
            $current->fill(['status' => $approve ? 'approved' : 'rejected', 'rejection_reason' => $approve ? null : $reason,
                'reviewed_by' => $reviewer->id, 'reviewed_at' => now()])->save();
            return [$current, true];
        });
        if ($changed) {
            if ($approve) {
                $this->safely($current, 'owner_notification', fn () => $current->owner->notify(new StoreRegistrationNotification($current, true)));
                $this->approvalMail($current);
            } else {
                $this->safely($current, 'rejection_email', fn () => Mail::to($current->owner_email)->send(new StoreRegistrationMail($current, 'rejected')));
            }
        }
        return $current;
    }

    public function approvalMail(StoreRegistration $registration): void
    {
        $this->safely($registration, 'approval_email', fn () => Mail::to($registration->owner_email)->send(new StoreRegistrationMail($registration, 'approved',
            URL::temporarySignedRoute('store-registration.activate', now()->addDays(7), ['registration' => $registration->id]))));
    }

    private function safely(StoreRegistration $registration, string $stage, callable $send): void
    {
        try { $send(); } catch (Throwable $exception) {
            Log::warning('Store registration delivery failed', ['registration_id' => $registration->id, 'stage' => $stage, 'exception_type' => get_class($exception)]);
        }
    }
}
