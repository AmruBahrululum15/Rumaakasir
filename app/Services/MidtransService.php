<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MidtransService
{
    /**
     * ============================================================
     * KONSTRUKTOR
     * ============================================================
     */
    public function __construct()
    {
        $serverKey = config('midtrans.server_key');
        $clientKey = config('midtrans.client_key');

        if (
            blank($serverKey)
            ||
            blank($clientKey)
        ) {
            throw new RuntimeException(
                'Konfigurasi Midtrans belum lengkap. Isi MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY di file .env.'
            );
        }
    }


    /**
     * ============================================================
     * CREATE SNAP TOKEN
     * ============================================================
     */
    public function createSnapToken(
        Sale $sale,
        ?int $tableId,
        string $customerName,
        string $customerPhone,
        string $customerEmail,
        ?string $paymentMethod = null
    ): string {

        $serverKey =
            config('midtrans.server_key');


        $isProduction =
            (bool) config(
                'midtrans.is_production',
                false
            );


        /*
        |--------------------------------------------------------------------------
        | MIDTRANS SNAP ENDPOINT
        |--------------------------------------------------------------------------
        */

        $endpoint = $isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';


        /*
        |--------------------------------------------------------------------------
        | ITEM DETAILS
        |--------------------------------------------------------------------------
        */

        $items = [];


        foreach (
            $sale
                ->saleDetails()
                ->with('product')
                ->get()
            as $detail
        ) {

            $items[] = [

                'id' =>
                    (string) $detail->product_id,

                'price' =>
                    (int) round(
                        $detail->price
                    ),

                'quantity' =>
                    (int) $detail->quantity,

                'name' =>
                    Str::limit(
                        $detail->product?->name
                            ?? 'Produk',
                        50,
                        ''
                    ),

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | PAYMENT METHOD
        |--------------------------------------------------------------------------
        */

        $enabledPayments =
            config(
                'midtrans.enabled_payments',
                []
            );


        /*
        |--------------------------------------------------------------------------
        | QRIS
        |--------------------------------------------------------------------------
        */

        if (
            $paymentMethod === 'qris'
        ) {

            $enabledPayments = [

                'other_qris',

            ];

        }


        /*
        |--------------------------------------------------------------------------
        | VIRTUAL ACCOUNT
        |--------------------------------------------------------------------------
        */

        if (
            $paymentMethod === 'va'
        ) {

            $enabledPayments = [

                'bank_transfer',

            ];

        }


        /*
        |--------------------------------------------------------------------------
        | PARAMETER SNAP
        |--------------------------------------------------------------------------
        */

        $params = [

            'transaction_details' => [

                'order_id' =>
                    (string) $sale->code,

                'gross_amount' =>
                    (int) round(
                        $sale->total
                    ),

            ],


            'item_details' =>
                $items,


            'customer_details' =>
                array_filter(

                    [

                        'first_name' =>
                            trim($customerName),

                        'email' =>
                            trim($customerEmail),

                        'phone' =>
                            trim($customerPhone),

                    ],

                    function ($value) {

                        return
                            $value !== null
                            &&
                            $value !== '';

                    }

                ),


            'enabled_payments' =>
                array_values(
                    array_filter(
                        $enabledPayments
                    )
                ),


            /*
            |--------------------------------------------------------------------------
            | FINISH CALLBACK
            |--------------------------------------------------------------------------
            */

            'callbacks' => [

                'finish' =>

                    $tableId

                        ? route(
                            'customer.checkout.success',
                            [

                                'table' =>
                                    $tableId,

                                'sale' =>
                                    $sale->id,

                            ]
                        )

                        : route(
                            'sales.receipt',
                            $sale
                        ),

            ],

        ];


        /*
        |--------------------------------------------------------------------------
        | REQUEST MIDTRANS
        |--------------------------------------------------------------------------
        |
        | Tidak menggunakan proxy option.
        |
        | Karena environment PHP kamu sudah terbukti tidak memiliki
        | HTTP_PROXY / HTTPS_PROXY / ALL_PROXY.
        |
        */

        $response = Http::acceptJson()
            ->asJson()

            ->connectTimeout(15)

            ->timeout(60)

            ->withBasicAuth(
                $serverKey,
                ''
            )

            ->post(
                $endpoint,
                $params
            );


        /*
        |--------------------------------------------------------------------------
        | HTTP ERROR
        |--------------------------------------------------------------------------
        */

        if (
            $response->failed()
        ) {

            $body =
                $response->json();


            $statusMessage =
                is_array($body)
                ? (
                    $body['status_message']
                    ?? null
                )
                : null;


            if (
                is_array($body)
                &&
                !empty($body['error_messages'])
                &&
                is_array($body['error_messages'])
            ) {

                $statusMessage =
                    implode(
                        '; ',
                        $body['error_messages']
                    );

            }


            throw new RuntimeException(

                'Midtrans API error. ' .
                'HTTP ' .
                $response->status() .
                '. ' .
                (
                    $statusMessage
                    ?: $response->body()
                )

            );

        }


        /*
        |--------------------------------------------------------------------------
        | SNAP TOKEN
        |--------------------------------------------------------------------------
        */

        $token =
            $response->json('token');


        /*
        |--------------------------------------------------------------------------
        | VALIDATE SNAP TOKEN
        |--------------------------------------------------------------------------
        */

        if (
            blank($token)
        ) {

            throw new RuntimeException(

                'Midtrans tidak mengembalikan Snap Token. Response: ' .
                $response->body()

            );

        }


        return (string) $token;
    }
}