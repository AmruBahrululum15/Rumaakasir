<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionOrder extends Model
{
    use BelongsToBusiness;

    protected $guarded = ['id', 'business_id'];

    protected $casts = [
        'amount' => 'integer', 'payment_due_at' => 'datetime',
        'started_at' => 'datetime', 'expires_at' => 'datetime', 'paid_at' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'plan_id');
    }

    public function validPaymentUrl(): bool
    {
        return $this->payment_provider === 'midtrans'
            ? app(\App\Services\MidtransSubscription::class)->validCheckoutUrl($this->payment_url)
            : app(\App\Services\DokuService::class)->validCheckoutUrl($this->payment_url);
    }

    public function displayStatus(): string
    {
        return in_array($this->status, ['pending', 'creating', 'create_unknown'], true) && $this->payment_due_at->lte(now()) ? 'expired' : $this->status;
    }
}
