<?php

namespace App\Services;

use App\Exceptions\DokuCheckoutException;
use App\Models\Business;
use App\Models\Package;
use App\Models\SubscriptionOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class SubscriptionService
{
    public function __construct(private DokuService $doku) {}

    public function checkout(User $user, string $cycle, ?string $retryFrom = null): SubscriptionOrder
    {
        $provider = config('doku.subscription_gateway', 'doku');
        $gateway = $provider === 'midtrans' ? app(MidtransSubscription::class) : $this->doku;
        $gateway->ensureConfigured();
        [$order, $created] = DB::transaction(function () use ($user, $cycle, $retryFrom, $provider) {
            // The business lock serializes concurrent double-clicks and renewals.
            $business = Business::lockForUpdate()->findOrFail($user->business_id);
            abort_unless($business->allowsOperations(), 403);
            if ($retryFrom !== null) {
                $source = SubscriptionOrder::where('business_id', $business->id)->where('order_id', $retryFrom)->lockForUpdate()->firstOrFail();
                if (!in_array($source->displayStatus(), ['failed', 'expired'], true)) return [$source, false];
                // A repeated POST from the same failed page reuses its replacement,
                // even when that replacement also failed. Retry from the new page.
                $replacement = SubscriptionOrder::where('business_id', $business->id)->where('id', '>', $source->id)
                    ->where('billing_cycle', $source->billing_cycle)->oldest('id')->first();
                if ($replacement) return [$replacement, false];
                $cycle = $source->billing_cycle;
            }
            if ($business->subscription_plan === 'premium' && ! $business->subscription_ends_at
                && !in_array($business->subscription_source, ['doku','midtrans'], true)) {
                throw ValidationException::withMessages(['payment' => 'Toko sudah memiliki Premium tanpa batas waktu. Perpanjangan tidak diperlukan.']);
            }
            SubscriptionOrder::where('business_id', $business->id)->whereIn('status', ['pending', 'creating', 'create_unknown'])
                ->where('payment_due_at', '<=', now())->update(['status' => 'expired']);
            $pending = SubscriptionOrder::where('business_id', $business->id)->whereIn('status', ['pending', 'creating', 'create_unknown'])->first();
            if ($pending) {
                return [$pending, false];
            }
            $plan = app(PackageAccess::class)->forType($business->business_type, 'premium');
            $amount = $plan->getAttribute('price_'.$cycle);
            if (! $plan->is_active || ! is_int($amount) || $amount < 1) {
                throw ValidationException::withMessages(['payment' => 'Paket Premium belum tersedia untuk pembelian.']);
            }
            $order = new SubscriptionOrder([
                'plan_id' => $plan->id, 'plan_name' => $plan->name, 'business_name' => $business->name,
                'payment_provider' => $provider, 'billing_cycle' => $cycle, 'amount' => $amount, 'status' => 'creating',
                // 30 characters even for credit card channels; separate from Sale IDs.
                'order_id' => 'SUB-'.Str::ulid(), 'request_id' => (string) Str::uuid(),
                'payment_due_at' => now()->addMinutes(config('doku.payment_due_minutes')),
            ]);
            $order->business_id = $business->id;
            $order->save();
            return [$order, true];
        });

        if ($created) {
            try {
                $data = $gateway->createPayment($order, $user);
                // A fast webhook may already have settled this order. Do not
                // overwrite its status or grant here, even on a creation error.
                DB::transaction(function () use ($order, $data) {
                    $current = SubscriptionOrder::lockForUpdate()->findOrFail($order->id);
                    $current->fill($data);
                    if (! $current->paid_at && in_array($current->status, ['creating', 'create_unknown'], true)) {
                        $current->status = 'pending';
                    }
                    $current->save();
                });
            } catch (DokuCheckoutException $exception) {
                SubscriptionOrder::whereKey($order->id)->where('status', 'creating')->whereNull('paid_at')->update([
                    'status' => $exception->rejected ? 'failed' : 'create_unknown',
                    'last_payment_status' => 'CREATE_ERROR',
                ]);
            } catch (Throwable $exception) {
                // Exception messages can contain credentials/URLs; log class only.
                Log::error('DOKU subscription create processing error', [
                    'order_id' => $order->order_id, 'request_id' => $order->request_id,
                    'exception_type' => get_class($exception),
                ]);
                SubscriptionOrder::whereKey($order->id)->where('status', 'creating')->whereNull('paid_at')->update([
                    'status' => 'create_unknown', 'last_payment_status' => 'CREATE_ERROR',
                ]);
            }
        }
        return $order->fresh();
    }

    public function notify(array $data, string $provider = 'doku'): void
    {
        $invoice = data_get($data, 'order.invoice_number');
        $found = SubscriptionOrder::withoutGlobalScope('business')->where('order_id', $invoice)->firstOrFail();
        abort_unless($found->payment_provider === $provider, 422);
        DB::transaction(function () use ($found, $data, $provider) {
            $business = Business::lockForUpdate()->findOrFail($found->business_id);
            $order = SubscriptionOrder::withoutGlobalScope('business')->lockForUpdate()->findOrFail($found->id);
            if (! DokuService::amountMatches(data_get($data, 'order.amount'), $order->amount)
                || data_get($data, 'order.currency', 'IDR') !== 'IDR') {
                Log::warning('DOKU subscription amount mismatch', ['order_id' => $order->order_id]);
                abort(422, 'Amount mismatch.');
            }
            $originalId = data_get($data, 'transaction.original_request_id');
            abort_if($originalId !== null && $originalId !== $order->request_id, 422, 'Request mismatch.');
            if ($order->paid_at) {
                return;
            }
            $status = data_get($data, 'transaction.status');
            $type = data_get($data, 'transaction.type');
            if ($type !== null && ! in_array($type, ['SALE', 'CAPTURE', 'INSTALLMENT', 'MOTO'], true)) {
                // Successful authorization/refund is not a settled purchase.
                return;
            }
            // DOKU Checkout FAILED is an attempt failure, not a terminal order.
            // Unknown/new statuses are acknowledged but never grant Premium.
            if ($status !== 'SUCCESS') {
                if ($status === 'FAILED') {
                    $order->update(['last_payment_status' => 'FAILED']);
                }
                return;
            }
            $paidAt = now();
            $continuing = $business->subscription_plan === 'premium'
                && $business->subscription_status === 'active'
                && $business->subscription_ends_at?->isFuture();
            $start = $continuing ? $business->subscription_ends_at->copy() : $paidAt->copy();
            $end = $order->billing_cycle === 'yearly' ? $start->copy()->addYearNoOverflow() : $start->copy()->addMonthNoOverflow();
            $order->update([
                'status' => 'paid', 'last_payment_status' => 'SUCCESS', 'paid_at' => $paidAt,
                'started_at' => $start, 'expires_at' => $end,
                'payment_method' => data_get($data, 'channel.id'),
            ]);
            $business->forceFill([
                'subscription_plan' => 'premium', 'subscription_source' => $provider,
                // Never clear a platform suspension imposed while payment was pending.
                'subscription_status' => in_array($business->subscription_status, ['suspended', 'inactive'], true) ? $business->subscription_status : 'active',
                'subscription_started_at' => $continuing ? ($business->subscription_started_at ?? $start) : $start,
                'subscription_ends_at' => $end,
            ])->save();
        });
    }

    public function expire(): int
    {
        SubscriptionOrder::withoutGlobalScope('business')->where('status', 'pending')->where('payment_due_at', '<=', now())->update(['status' => 'expired']);
        return Business::whereIn('subscription_source', ['doku','midtrans'])->where('subscription_status', 'active')
            ->where('subscription_ends_at', '<=', now())
            ->update(['subscription_plan' => 'free', 'subscription_status' => 'expired']);
    }
}
