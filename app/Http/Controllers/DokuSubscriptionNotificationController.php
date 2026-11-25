<?php

namespace App\Http\Controllers;

use App\Services\DokuService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DokuSubscriptionNotificationController extends Controller
{
    public function __invoke(Request $request, DokuService $doku, SubscriptionService $subscriptions)
    {
        abort_unless($doku->verifyNotification($request), 401, 'Invalid signature.');
        // Parse the signed raw body; request input normalization must not alter it.
        $data = json_decode($request->getContent(), true);
        abort_unless(is_array($data), 422);
        Validator::make($data, [
            'order.invoice_number' => ['required', 'string', 'max:30'],
            'order.amount' => ['required'],
            'order.currency' => ['sometimes', 'string', 'in:IDR'],
            'transaction.status' => ['required', 'string', 'max:30'],
            'transaction.type' => ['sometimes', 'nullable', 'string', 'max:30'],
            'transaction.original_request_id' => ['sometimes', 'nullable', 'string', 'max:128'],
            'channel.id' => ['sometimes', 'nullable', 'string', 'max:100'],
        ])->validate();
        $subscriptions->notify($data);
        return response()->json(['message' => 'OK']);
    }
}
