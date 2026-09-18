<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use RuntimeException;

class MidtransService
{
    public function __construct()
    {
        if (blank(config('midtrans.server_key')) || blank(config('midtrans.client_key'))) {
            throw new RuntimeException(
                'Konfigurasi Midtrans belum lengkap. Isi MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY di file .env.'
            );
        }

        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createSnapToken(
        Sale $sale,
        ?int $tableId,
        string $customerName,
        string $customerPhone,
        string $customerEmail,
        ?string $paymentMethod = null
    ): string {
        $items = [];

        foreach ($sale->saleDetails()->with('product')->get() as $detail) {
            $items[] = [
                'id' => (string) $detail->product_id,
                'price' => (int) round($detail->price),
                'quantity' => (int) $detail->quantity,
                'name' => Str::limit(
                    $detail->product?->name ?? 'Produk',
                    50,
                    ''
                ),
            ];
        }

        $enabledPayments = config('midtrans.enabled_payments');

        if ($paymentMethod === 'qris') {
            $enabledPayments = [
                'other_qris',
                'gopay',
            ];
        }

        if ($paymentMethod === 'va') {
            $enabledPayments = [
                'bank_transfer',
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id' => $sale->code,
                'gross_amount' => (int) round($sale->total),
            ],

            'item_details' => $items,

            'customer_details' => array_filter([
                'first_name' => $customerName,
                'email' => $customerEmail,
                'phone' => $customerPhone,
            ]),

            'enabled_payments' => $enabledPayments,

            'callbacks' => [
                'finish' => $tableId
                    ? route('customer.checkout.success', ['table' => $tableId, 'sale' => $sale->id])
                    : route('sales.receipt', $sale),
            ],
        ];

        return Snap::getSnapToken($params);
    }
}
