<?php

namespace App\Models\Concerns;

use App\Models\Business;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use LogicException;

trait BelongsToBusiness
{
    public static function bootBelongsToBusiness(): void
    {
        static::addGlobalScope('business', function (Builder $builder): void {
            // Laravel loads the authenticated User from the session through an
            // Eloquent query. Calling Auth::user() while that query is being
            // built recursively starts another user query until PHP runs out
            // of memory. Before ResolveTenant has established a context, let
            // that one authentication lookup proceed unscoped.
            $businessId = $builder->getModel() instanceof \App\Models\User
                ? app(TenantContext::class)->id()
                : static::resolveBusinessId();

            if ($businessId !== null) {
                $builder->where($builder->getModel()->getTable().'.business_id', $businessId);
            }
        });

        static::creating(function ($model): void {
            $businessId = static::resolveBusinessId();

            if ($businessId === null) {
                throw new LogicException('Business context is required to create tenant data.');
            }

            if ($model->business_id !== null && (int) $model->business_id !== (int) $businessId) {
                throw new LogicException('Tenant data cannot be created for another business.');
            }

            $model->business_id = $businessId;
        });

        static::created(function ($model): void {
            if (! $model instanceof \App\Models\User) {
                return;
            }

            $business = Business::query()->find($model->business_id);
            if ($business && $business->owner_id === null) {
                $business->forceFill(['owner_id' => $model->id])->save();
            }
        });

        static::updating(function ($model): void {
            if ($model->isDirty('business_id')) {
                throw new LogicException('Existing tenant records cannot be moved to another business.');
            }
        });
    }

    /**
     * Legacy POS routes such as the login branding and public table QR flow do
     * not have an authenticated user from which to resolve a tenant.  Until a
     * request has an explicit tenant, keep those established single-store
     * flows on Toko Default (ID 1), never an arbitrary active store.
     */
    protected static function resolveBusinessId(): ?int
    {
        return app(TenantContext::class)->id()
            ?? Auth::user()?->business_id
            ?? 1;
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
