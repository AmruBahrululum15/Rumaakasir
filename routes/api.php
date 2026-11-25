<?php

use App\Http\Controllers\MidtransWebhookController;
use Illuminate\Support\Facades\Route;
Route::post('/customer-payments/{business}/midtrans', [\App\Http\Controllers\CustomerOrderPaymentController::class,'webhook'])->name('customer-payment.webhook');

Route::post('/doku/subscription/notification', \App\Http\Controllers\DokuSubscriptionNotificationController::class)
    ->name('doku.subscription.notification');

Route::post(
    '/midtrans/notification',
    [MidtransWebhookController::class, 'handle']
)->name('midtrans.notification');
Route::post('/midtrans/subscription/notification', \App\Http\Controllers\MidtransSubscriptionNotificationController::class)->name('midtrans.subscription.notification');
