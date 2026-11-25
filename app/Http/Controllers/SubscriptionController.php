<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\SubscriptionOrder;
use App\Services\DokuService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        return view($request->user()->business->business_type === null ? 'subscriptions.legacy' : 'subscriptions.index', [
            'business' => $request->user()->business->fresh(),
            'premium' => app(\App\Services\PackageAccess::class)->forType($request->user()->business->business_type, 'premium'),
            'featureCatalog' => app(\App\Services\PremiumFeatures::class)->catalog($request->user()->business->business_type),
            'comparison' => app(\App\Services\BusinessModules::class)->comparison($request->user()->business->business_type),
            'latestPaid' => SubscriptionOrder::where('business_id', $request->user()->business_id)->where('status', 'paid')->latest('id')->first(),
            'orders' => SubscriptionOrder::where('business_id', $request->user()->business_id)->latest('id')->paginate(15),
            'pending' => SubscriptionOrder::where('business_id', $request->user()->business_id)->whereIn('status', ['pending', 'creating', 'create_unknown'])->where('payment_due_at', '>', now())->first(),
        ]);
    }

    public function paymentOptions(Request $request)
    {
        $data = $request->validate(['billing_cycle' => ['required', Rule::in(['monthly', 'yearly'])]]);
        $business = $request->user()->business;
        $premium = app(\App\Services\PackageAccess::class)->forType($business->business_type, 'premium');
        abort_unless($premium && $premium->is_active, 404);
        $cycle = $data['billing_cycle'];
        $amount = $cycle === 'yearly' ? $premium->price_yearly : $premium->price_monthly;
        return view('subscriptions.payment-options', compact('business', 'premium', 'cycle', 'amount'));
    }

    public function store(Request $request, SubscriptionService $subscriptions, DokuService $doku)
    {
        $data = $request->validate(['billing_cycle' => ['required', Rule::in(['monthly', 'yearly'])]]);
        $order = $subscriptions->checkout($request->user(), $data['billing_cycle']);
        if ($order->displayStatus() === 'pending' && $order->validPaymentUrl()) {
            return redirect()->away($order->payment_url);
        }
        return to_route('subscriptions.result', $order->order_id);
    }

    public function result(Request $request, string $orderId)
    {
        // Never bind an unscoped order or accept a business identifier from input.
        $order = SubscriptionOrder::where('business_id', $request->user()->business_id)->where('order_id', $orderId)->firstOrFail();
        return view('subscriptions.result', compact('order'));
    }

    public function retry(Request $request, string $orderId, SubscriptionService $subscriptions, DokuService $doku)
    {
        $source = SubscriptionOrder::where('business_id', $request->user()->business_id)->where('order_id', $orderId)->firstOrFail();
        $order = $subscriptions->checkout($request->user(), $source->billing_cycle, $source->order_id);
        if ($order->displayStatus() === 'pending' && $order->validPaymentUrl()) return redirect()->away($order->payment_url);
        return to_route('subscriptions.result', $order->order_id);
    }

    public function invoice(Request $request, string $orderId)
    {
        $order = SubscriptionOrder::where('business_id', $request->user()->business_id)->where('order_id', $orderId)->where('status', 'paid')->firstOrFail();
        return view('subscriptions.invoice', compact('order'));
    }
}
