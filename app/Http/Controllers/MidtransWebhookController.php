<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\Notification;
use Throwable;

class MidtransWebhookController extends Controller
{
    /**
     * ============================================================
     * HANDLE MIDTRANS NOTIFICATION
     * ============================================================
     */
    public function handle(Request $request)
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | MIDTRANS CONFIG
            |--------------------------------------------------------------------------
            */

            Config::$serverKey =
                config('midtrans.server_key');

            Config::$isProduction =
                config('midtrans.is_production');

            Config::$isSanitized =
                true;

            Config::$is3ds =
                true;


            /*
            |--------------------------------------------------------------------------
            | NOTIFICATION
            |--------------------------------------------------------------------------
            */

            $notification =
                new Notification();


            $orderId =
                $notification->order_id;


            $transactionStatus =
                $notification->transaction_status;


            $fraudStatus =
                $notification->fraud_status
                ?? null;


            $transactionId =
                $notification->transaction_id
                ?? null;


            $paymentType =
                $notification->payment_type
                ?? null;


            $grossAmount =
                $notification->gross_amount
                ?? null;


            /*
            |--------------------------------------------------------------------------
            | CARI SALE
            |--------------------------------------------------------------------------
            */

            $sale =
                Sale::withoutGlobalScopes()->where(
                    'code',
                    $orderId
                )->first();


            if (!$sale) {

                return response()->json(
                    [
                        'success' =>
                            false,

                        'message' =>
                            'Order tidak ditemukan.',
                    ],
                    404
                );
            }

            // Portal orders must be verified with their own merchant credentials.
            if ($sale->order_source === 'customer_portal') return response()->json(['message'=>'Use the store payment notification endpoint.'],403);
            app(TenantContext::class)->set($sale->business);


            /*
            |--------------------------------------------------------------------------
            | VALIDASI NOMINAL
            |--------------------------------------------------------------------------
            */

            if (
                $grossAmount !== null
                &&
                (float) $grossAmount
                    !==
                (float) $sale->total
            ) {

                report(
                    new \RuntimeException(
                        'Midtrans gross amount tidak sesuai.'
                    )
                );


                return response()->json(
                    [
                        'success' =>
                            false,

                        'message' =>
                            'Nominal transaksi tidak sesuai.',
                    ],
                    400
                );
            }


            /*
            |--------------------------------------------------------------------------
            | NORMALISASI PAYMENT METHOD
            |--------------------------------------------------------------------------
            */

            $paymentMethod =
                $this->normalizePaymentMethod(
                    $paymentType
                );


            /*
            |--------------------------------------------------------------------------
            | TRANSACTION
            |--------------------------------------------------------------------------
            */

            DB::transaction(
                function () use (
                    $sale,
                    $transactionStatus,
                    $fraudStatus,
                    $transactionId,
                    $paymentMethod
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | SETTLEMENT
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $transactionStatus ===
                        'settlement'
                    ) {

                        $this->markAsPaid(
                            $sale,
                            $transactionId,
                            $paymentMethod
                        );

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CREDIT CARD CAPTURE
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $transactionStatus ===
                        'capture'
                        &&
                        $fraudStatus ===
                        'accept'
                    ) {

                        $this->markAsPaid(
                            $sale,
                            $transactionId,
                            $paymentMethod
                        );

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | PENDING
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $transactionStatus ===
                        'pending'
                    ) {

                        $this->updatePaymentStatus(
                            $sale,
                            'PENDING'
                        );


                        $this->updateSaleNote(
                            $sale,
                            'PAYMENT_METHOD',
                            $paymentMethod
                        );


                        $this->updateSaleNote(
                            $sale,
                            'MIDTRANS_TRANSACTION_ID',
                            $transactionId ?? ''
                        );


                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | EXPIRED
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $transactionStatus ===
                        'expire'
                    ) {

                        $this->markAsFailed(
                            $sale,
                            'EXPIRED',
                            $transactionId,
                            $paymentMethod,
                            true
                        );

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CANCEL
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $transactionStatus ===
                        'cancel'
                    ) {

                        $this->markAsFailed(
                            $sale,
                            'CANCELLED',
                            $transactionId,
                            $paymentMethod,
                            true
                        );

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | DENY
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $transactionStatus ===
                        'deny'
                    ) {

                        $this->markAsFailed(
                            $sale,
                            'DENIED',
                            $transactionId,
                            $paymentMethod,
                            true
                        );

                        return;
                    }
                }
            );


            /*
            |--------------------------------------------------------------------------
            | RESPONSE
            |--------------------------------------------------------------------------
            */

            return response()->json(
                [
                    'success' =>
                        true,

                    'message' =>
                        'Notification berhasil diproses.',
                ],
                200
            );


        } catch (Throwable $e) {

            report($e);


            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Webhook gagal diproses.',
                ],
                500
            );
        }
    }


    /**
     * ============================================================
     * MARK AS PAID
     * ============================================================
     */
    private function markAsPaid(
        Sale $sale,
        ?string $transactionId,
        ?string $paymentMethod
    ): void {

        /*
        |--------------------------------------------------------------------------
        | IDEMPOTENCY
        |--------------------------------------------------------------------------
        */

        $currentStatus = strtoupper((string) ($sale->payment_status ?: $this->getSaleNote($sale, 'PAYMENT_STATUS')));


        if (
            $currentStatus ===
            'PAID'
        ) {

            /*
            | Tetap update metadata jika ada
            */

            if ($transactionId !== null) {

                $this->updateSaleNote(
                    $sale,
                    'MIDTRANS_TRANSACTION_ID',
                    $transactionId
                );
            }


            if ($paymentMethod !== null) {

                $this->updateSaleNote(
                    $sale,
                    'PAYMENT_METHOD',
                    $paymentMethod
                );
            }


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE PAYMENT
        |--------------------------------------------------------------------------
        */

        $sale->update([

            'paid' =>
                $sale->total,

            'change' =>
                0,
            'payment_status' => 'PAID',
            'payment_method' => strtolower((string) ($paymentMethod ?: $sale->payment_method)),

        ]);


        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        $this->updateSaleNote(
            $sale,
            'PAYMENT_STATUS',
            'PAID'
        );


        /*
        |--------------------------------------------------------------------------
        | METHOD
        |--------------------------------------------------------------------------
        */

        $this->updateSaleNote(
            $sale,
            'PAYMENT_METHOD',
            $paymentMethod ?? ''
        );


        /*
        |--------------------------------------------------------------------------
        | TRANSACTION ID
        |--------------------------------------------------------------------------
        */

        $this->updateSaleNote(
            $sale,
            'MIDTRANS_TRANSACTION_ID',
            $transactionId ?? ''
        );


        /*
        |--------------------------------------------------------------------------
        | JIKA SEBELUMNYA MASIH ADA PAYMENT_TYPE
        |--------------------------------------------------------------------------
        */

        $this->removeSaleNote(
            $sale,
            'PAYMENT_TYPE'
        );
    }


    /**
     * ============================================================
     * MARK AS FAILED
     * ============================================================
     */
    private function markAsFailed(
        Sale $sale,
        string $status,
        ?string $transactionId,
        ?string $paymentMethod,
        bool $restoreStock = false
    ): void {

        $currentStatus =
            $this->getSaleNote(
                $sale,
                'PAYMENT_STATUS'
            );


        /*
        |--------------------------------------------------------------------------
        | JANGAN PROSES ULANG
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $currentStatus,
                [
                    'PAID',
                    'EXPIRED',
                    'CANCELLED',
                    'DENIED',
                ],
                true
            )
        ) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | RESTORE STOCK
        |--------------------------------------------------------------------------
        */

        if ($restoreStock) {

            $this->restoreStockIfReserved(
                $sale
            );
        }


        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        $this->updatePaymentStatus(
            $sale,
            $status
        );


        /*
        |--------------------------------------------------------------------------
        | PAYMENT METHOD
        |--------------------------------------------------------------------------
        */

        $this->updateSaleNote(
            $sale,
            'PAYMENT_METHOD',
            $paymentMethod ?? ''
        );


        /*
        |--------------------------------------------------------------------------
        | TRANSACTION ID
        |--------------------------------------------------------------------------
        */

        $this->updateSaleNote(
            $sale,
            'MIDTRANS_TRANSACTION_ID',
            $transactionId ?? ''
        );


        /*
        |--------------------------------------------------------------------------
        | HAPUS FORMAT LAMA
        |--------------------------------------------------------------------------
        */

        $this->removeSaleNote(
            $sale,
            'PAYMENT_TYPE'
        );
    }


    /**
     * ============================================================
     * RESTORE STOCK
     * ============================================================
     */
    private function restoreStockIfReserved(
        Sale $sale
    ): void {

        /*
        |--------------------------------------------------------------------------
        | CEK FLAG STOCK RESERVED
        |--------------------------------------------------------------------------
        */

        $stockReserved =
            $this->getSaleNote(
                $sale,
                'STOCK_RESERVED'
            );


        /*
        |--------------------------------------------------------------------------
        | JIKA TIDAK ADA FLAG
        |--------------------------------------------------------------------------
        |
        | Untuk transaksi lama yang belum mempunyai flag,
        | kita tidak langsung mengembalikan stok.
        |
        | Ini mencegah stok terduplikasi.
        |
        */

        if ($stockReserved !== '1') {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | KEMBALIKAN STOK
        |--------------------------------------------------------------------------
        */

        $sale->load([
            'saleDetails'
        ]);


        foreach (
            $sale->saleDetails
            as $detail
        ) {

            Product::where(
                'id',
                $detail->product_id
            )->increment(
                'stock',
                $detail->quantity
            );
        }


        /*
        |--------------------------------------------------------------------------
        | UBAH FLAG
        |--------------------------------------------------------------------------
        */

        $this->updateSaleNote(
            $sale,
            'STOCK_RESERVED',
            '0'
        );
    }


    /**
     * ============================================================
     * UPDATE PAYMENT STATUS
     * ============================================================
     */
    private function updatePaymentStatus(
        Sale $sale,
        string $status
    ): void {

        $sale->update(['payment_status' => strtoupper($status)]);
        $this->updateSaleNote(
            $sale,
            'PAYMENT_STATUS',
            strtoupper($status)
        );
    }


    /**
     * ============================================================
     * NORMALIZE PAYMENT METHOD
     * ============================================================
     */
    private function normalizePaymentMethod(
        ?string $paymentType
    ): string {

        if (!$paymentType) {

            return '';
        }


        return match (
            strtolower(
                trim($paymentType)
            )
        ) {

            /*
            |--------------------------------------------------------------------------
            | QRIS / E-WALLET
            |--------------------------------------------------------------------------
            */

            'qris',
            'other_qris',
            'gopay',
            'shopeepay',
            'dana',
            'ovo',
            'linkaja' =>
                'QRIS',


            /*
            |--------------------------------------------------------------------------
            | VIRTUAL ACCOUNT
            |--------------------------------------------------------------------------
            */

            'bank_transfer',
            'bca_va',
            'bni_va',
            'bri_va',
            'cimb_va',
            'permata_va',
            'echannel' =>
                'VA',


            /*
            |--------------------------------------------------------------------------
            | DEFAULT
            |--------------------------------------------------------------------------
            */

            default =>
                strtoupper(
                    $paymentType
                ),
        };
    }


    /**
     * ============================================================
     * GET SALE NOTE
     * ============================================================
     */
    private function getSaleNote(
        Sale $sale,
        string $key
    ): ?string {

        $notes =
            (string) (
                $sale->notes ?? ''
            );


        if ($notes === '') {

            return null;
        }


        $lines =
            preg_split(
                '/\r\n|\r|\n/',
                $notes
            );


        foreach ($lines as $line) {

            $line =
                trim($line);


            if (
                str_starts_with(
                    $line,
                    $key . '='
                )
            ) {

                return trim(
                    substr(
                        $line,
                        strlen($key) + 1
                    )
                );
            }
        }


        return null;
    }


    /**
     * ============================================================
     * UPDATE SALE NOTE
     * ============================================================
     */
    private function updateSaleNote(
        Sale $sale,
        string $key,
        string $value
    ): void {

        $notes =
            (string) (
                $sale->notes ?? ''
            );


        $lines =
            $notes === ''
                ? []
                : preg_split(
                    '/\r\n|\r|\n/',
                    $notes
                );


        $updated = false;


        foreach ($lines as &$line) {

            $line =
                trim($line);


            if (
                str_starts_with(
                    $line,
                    $key . '='
                )
            ) {

                $line =
                    $key
                    . '='
                    . $value;


                $updated = true;


                break;
            }
        }


        unset($line);


        if (!$updated) {

            $lines[] =
                $key
                . '='
                . $value;
        }


        $sale->update([

            'notes' =>
                implode(
                    PHP_EOL,
                    $lines
                ),

        ]);
    }


    /**
     * ============================================================
     * REMOVE SALE NOTE
     * ============================================================
     */
    private function removeSaleNote(
        Sale $sale,
        string $key
    ): void {

        $notes =
            (string) (
                $sale->notes ?? ''
            );


        if ($notes === '') {

            return;
        }


        $lines =
            preg_split(
                '/\r\n|\r|\n/',
                $notes
            );


        $filtered = [];


        foreach ($lines as $line) {

            $line =
                trim($line);


            if (
                str_starts_with(
                    $line,
                    $key . '='
                )
            ) {

                continue;
            }


            if ($line !== '') {

                $filtered[] =
                    $line;
            }
        }


        $sale->update([

            'notes' =>
                implode(
                    PHP_EOL,
                    $filtered
                ),

        ]);
    }
}
