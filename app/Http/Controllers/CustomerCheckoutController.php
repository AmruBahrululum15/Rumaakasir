<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Table;
use App\Models\User;
use App\Services\MidtransService;
use App\Notifications\NewTableOrderNotification;
use App\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;


class CustomerCheckoutController extends Controller
{
    /**
     * ============================================================
     * HALAMAN CHECKOUT
     * ============================================================
     */
    public function create($table)
    {
        $table = $this->resolveTenantTable($table);
        /*
        |--------------------------------------------------------------------------
        | CEK STATUS MEJA
        |--------------------------------------------------------------------------
        */

        if ((int) $table->is_active !== 1) {
            abort(404, 'Meja tidak aktif.');
        }

        return view('customer.checkout', [
            'table' => $table,
        ]);
    }


    /**
     * ============================================================
     * SIMPAN ORDER DAN PROSES PEMBAYARAN
     * ============================================================
     */
    public function store(
        Request $request,
        $table,
        MidtransService $midtrans
    ) {
        $table = $this->resolveTenantTable($table);

        /*
        |--------------------------------------------------------------------------
        | CEK STATUS MEJA
        |--------------------------------------------------------------------------
        */

        if ((int) $table->is_active !== 1) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Meja ini sedang tidak aktif.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'customer_name' => [
                'required',
                'string',
                'max:100',
            ],

            'customer_phone' => [
                'required',
                'string',
                'max:30',
            ],

            'customer_email' => [
                'required',
                'email',
                'max:255',
            ],

            'payment_method' => [
                'required',
                'in:qris,va,cash',
            ],

            'cart' => [
                'required',
                'string',
            ],
        ], [
            'customer_name.required' =>
                'Nama pelanggan wajib diisi.',

            'customer_phone.required' =>
                'Nomor HP wajib diisi.',

            'customer_email.required' =>
                'Email wajib diisi.',

            'customer_email.email' =>
                'Format email tidak valid.',

            'payment_method.required' =>
                'Silakan pilih metode pembayaran.',

            'payment_method.in' =>
                'Metode pembayaran tidak valid.',

            'cart.required' =>
                'Keranjang pesanan kosong.',
        ]);


        /*
        |--------------------------------------------------------------------------
        | DECODE CART
        |--------------------------------------------------------------------------
        */

        $cart = json_decode(
            $validated['cart'],
            true
        );


        if (
            !is_array($cart)
            || count($cart) === 0
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Keranjang pesanan masih kosong.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | PRODUCT IDS
        |--------------------------------------------------------------------------
        */

        $productIds = collect($cart)
            ->pluck('id')
            ->filter()
            ->map(
                fn ($id) => (int) $id
            )
            ->unique()
            ->values();


        if ($productIds->isEmpty()) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Produk pesanan tidak valid.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | AMBIL PRODUCT
        |--------------------------------------------------------------------------
        */

        $products = Product::query()
            ->whereIn(
                'id',
                $productIds
            )
            ->where(
                'is_active',
                1
            )
            ->lockForUpdate()
            ->get()
            ->keyBy('id');


        if (
            $products->count()
            !== $productIds->count()
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Ada produk yang sudah tidak tersedia.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDASI CART
        |--------------------------------------------------------------------------
        */

        $items = [];

        $total = 0;


        foreach ($cart as $cartItem) {

            $productId = (int) (
                $cartItem['id'] ?? 0
            );

            $quantity = (int) (
                $cartItem['quantity'] ?? 0
            );


            /*
            |--------------------------------------------------------------------------
            | VALIDASI ITEM
            |--------------------------------------------------------------------------
            */

            if (
                $productId <= 0
                || $quantity <= 0
            ) {
                continue;
            }


            if (!$products->has($productId)) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Produk tidak ditemukan.'
                    );
            }


            $product = $products->get(
                $productId
            );


            /*
            |--------------------------------------------------------------------------
            | CEK STOK
            |--------------------------------------------------------------------------
            */

            $stock = (int) (
                $product->stock ?? 0
            );


            if ($stock < $quantity) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        "Stok {$product->name} tidak mencukupi."
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | HITUNG HARGA
            |--------------------------------------------------------------------------
            */

            $price = (float) $product->price;

            $subtotal =
                $price * $quantity;


            /*
            |--------------------------------------------------------------------------
            | CATATAN ITEM
            |--------------------------------------------------------------------------
            |
            | Jika fitur catatan produk masih digunakan di checkout,
            | bagian ini tetap mendukungnya.
            |
            */

            $note = trim(
                (string) (
                    $cartItem['note'] ?? ''
                )
            );


            if (strlen($note) > 250) {
                $note = substr(
                    $note,
                    0,
                    250
                );
            }


            /*
            |--------------------------------------------------------------------------
            | MASUKKAN ITEM
            |--------------------------------------------------------------------------
            */

            $items[] = [
                'product' =>
                    $product,

                'quantity' =>
                    $quantity,

                'price' =>
                    $price,

                'subtotal' =>
                    $subtotal,

                'note' =>
                    $note !== ''
                        ? $note
                        : null,
            ];


            $total += $subtotal;
        }


        /*
        |--------------------------------------------------------------------------
        | CEK ITEM
        |--------------------------------------------------------------------------
        */

        if (count($items) === 0) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Tidak ada item valid di dalam keranjang.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | CREATE SALE
        |--------------------------------------------------------------------------
        */

        try {

            /*
            |--------------------------------------------------------------------------
            | DATABASE TRANSACTION
            |--------------------------------------------------------------------------
            */

            $sale = DB::transaction(
                function () use (
                    $items,
                    $total,
                    $validated,
                    $table
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | GENERATE ORDER CODE
                    |--------------------------------------------------------------------------
                    */

                    do {

                        $code =
                            'QR-'
                            . now()->format('Ymd')
                            . '-'
                            . strtoupper(
                                Str::random(6)
                            );

                    } while (
                        Sale::where(
                            'code',
                            $code
                        )->exists()
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | PAYMENT STATUS
                    |--------------------------------------------------------------------------
                    */

                    $paymentMethod =
                        $validated['payment_method'];

                    $customerPhone = trim($validated['customer_phone']);

                    $portalAccount = auth('customer')->user();
                    if ($portalAccount && $portalAccount->status === 'active' && app(\App\Services\CustomerPortal::class)->active($table->business)) {
                        $membership = app(\App\Services\CustomerPortal::class)->join($table->business, $portalAccount);
                        $customer = Customer::where('business_id', $table->business_id)->findOrFail($membership->customer_id);
                    } else {
                        $portalAccount = null;
                    $customer = Customer::query()
                        ->when($table->business->business_type !== null, fn ($query) => $query->whereNotIn('id',
                            DB::table('business_customers')->where('business_id', $table->business_id)->select('customer_id')))
                        ->where('phone', $customerPhone)
                        ->lockForUpdate()
                        ->first();

                    if (! $customer) {
                        $customer = new Customer();
                    }

                    $customer->fill([
                        'name' => trim($validated['customer_name']),
                        'phone' => $customerPhone,
                        // Database aktif memakai kolom address. Email tetap
                        // dipakai untuk kebutuhan Midtrans dan metadata order.
                        'address' => trim($validated['customer_email']),
                    ])->save();

                    }

                    [$promotion,$promotionDiscount]=app(\App\Services\PromotionService::class)->quote($table->business,$customer,$total,null);
                    $total-=$promotionDiscount;


                    if (
                        $paymentMethod === 'cash'
                    ) {

                        $paymentStatus =
                            'CASH_PENDING';

                    } else {

                        $paymentStatus =
                            'PENDING';
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | SALE NOTES / METADATA
                    |--------------------------------------------------------------------------
                    |
                    | Data ini hanya untuk kebutuhan internal sistem.
                    | Jangan tampilkan field notes ini pada struk.
                    |
                    */

                    $notes = [
                        'ORDER_TYPE=QR_TABLE',

                        'TABLE_ID='
                            . $table->id,

                        'TABLE_NAME='
                            . $table->name,

                        'CUSTOMER_NAME='
                            . $validated['customer_name'],

                        'CUSTOMER_PHONE='
                            . $validated['customer_phone'],

                        'CUSTOMER_EMAIL='
                            . $validated['customer_email'],

                        'PAYMENT_METHOD='
                            . strtoupper(
                                $paymentMethod
                            ),

                        'PAYMENT_STATUS='
                            . $paymentStatus,

                        'MIDTRANS_ORDER_ID=',

                        'MIDTRANS_TRANSACTION_ID=',

                        'PAYMENT_TYPE=',

                        'STOCK_RESERVED=1',
                    ];


                    /*
                    |--------------------------------------------------------------------------
                    | CREATE SALE
                    |--------------------------------------------------------------------------
                    */

                    $sale = Sale::create([
                        'code' =>
                            $code,

                        'sale_date' =>
                            now()->toDateString(),

                        'total' =>
                            $total,

                        'paid' =>
                            0,

                        'change' =>
                            0,

                        'customer_id' => $customer->id,
                        'promotion_discount' => $promotionDiscount,
                        // QR orders intentionally have no cashier creator.
                        'user_id' => null,
                        'payment_method' => $paymentMethod,
                        'payment_status' => $paymentStatus,
                        // Source/table are resolved from the route model, never request input.
                        'order_source' => 'table_qr',
                        'table_id' => $table->id,

                        'notes' =>
                            implode(
                                PHP_EOL,
                                $notes
                            ),
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | CREATE SALE DETAILS
                    |--------------------------------------------------------------------------
                    */

                    foreach ($items as $item) {

                        $product =
                            $item['product'];


                        /*
                        |--------------------------------------------------------------------------
                        | DETAIL TRANSAKSI
                        |--------------------------------------------------------------------------
                        */

                        SaleDetail::create([
                            'sale_id' =>
                                $sale->id,

                            'product_id' =>
                                $product->id,

                            'quantity' =>
                                $item['quantity'],

                            'price' =>
                                $item['price'],

                            'subtotal' =>
                                $item['subtotal'],

                            'note' =>
                                $item['note'],
                        ]);


                        /*
                        |--------------------------------------------------------------------------
                        | KURANGI STOK
                        |--------------------------------------------------------------------------
                        */

                        $product->decrement(
                            'stock',
                            $item['quantity']
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | RETURN SALE
                    |--------------------------------------------------------------------------
                    */

                    if ($portalAccount) $sale->forceFill(['customer_account_id'=>$portalAccount->id])->save();
                    app(\App\Services\PromotionService::class)->record($promotion,$sale,$promotionDiscount);
                    return $sale;
                }
            );

/*
|--------------------------------------------------------------------------
| NOTIFIKASI ADMIN & KASIR
|--------------------------------------------------------------------------
|
| Jika notifikasi gagal, pesanan tetap dianggap berhasil.
|
*/

try {

    app(\App\Services\CustomerOrderNotifications::class)->send($sale,'new_customer_order');
} catch (Throwable $notificationError) {

    // Jangan menggagalkan transaksi hanya karena notifikasi gagal.
    report($notificationError);
}

            /*
            |--------------------------------------------------------------------------
            | ============================================================
            | CASH
            | ============================================================
            |--------------------------------------------------------------------------
            */

            if (
                $validated['payment_method']
                === 'cash'
            ) {

                return redirect()->route(
                    'customer.checkout.success',
                    [
                        'table' =>
                            $table->id,

                        'sale' =>
                            $sale->id,
                    ]
                );
            }


            /*
            |--------------------------------------------------------------------------
            | ============================================================
            | ONLINE PAYMENT
            | ============================================================
            |--------------------------------------------------------------------------
            */

            $snapToken =
                $midtrans->createSnapToken(
                    $sale,
                    $table->id,
                    $validated['customer_name'],
                    $validated['customer_phone'],
                    $validated['customer_email'],
                    $validated['payment_method']
                );


            /*
            |--------------------------------------------------------------------------
            | SIMPAN MIDTRANS ORDER ID
            |--------------------------------------------------------------------------
            */

            $this->updateSaleNote(
                $sale,
                'MIDTRANS_ORDER_ID',
                $sale->code
            );


            /*
            |--------------------------------------------------------------------------
            | PAYMENT PAGE
            |--------------------------------------------------------------------------
            */

            return view(
                'customer.checkout-payment',
                [
                    'table' =>
                        $table,

                    'sale' =>
                        $sale->fresh(),

                    'snapToken' =>
                        $snapToken,

                    'paymentMethod' =>
                        $validated['payment_method'],

                    'customerName' =>
                        $validated['customer_name'],

                    'customerPhone' =>
                        $validated['customer_phone'],

                    'customerEmail' =>
                        $validated['customer_email'],
                ]
            );


        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | LOG ERROR
            |--------------------------------------------------------------------------
            */

            report($e);


            return back()
                ->withInput()
                ->with(
                    'error',
                    'Pesanan gagal diproses. Silakan coba lagi.'
                );
        }
    }


    /**
     * ============================================================
     * HALAMAN SUKSES / STATUS ORDER
     * ============================================================
     */
    public function success($table, $sale)
    {
        $table = $this->resolveTenantTable($table);
        $sale = Sale::findOrFail($sale);

        if ((int) $sale->table_id !== (int) $table->id) {
            abort(404);
        }

        // Load normalized context; legacy notes remain available for old orders.
        $sale->load(['customer', 'user', 'table', 'saleDetails.product']);

        return view(
            'customer.checkout-success',
            [
                'table' =>
                    $table,

                'sale' =>
                    $sale,
            ]
        );
    }

    private function resolveTenantTable($table): Table
    {
        $id = $table instanceof Table ? $table->getKey() : $table;
        $resolved = Table::withoutGlobalScopes()->findOrFail($id);

        if (! $resolved->business?->allowsOperations()) {
            abort(404);
        }

        app(TenantContext::class)->set($resolved->business);

        return $resolved;
    }


    /**
     * ============================================================
     * UPDATE METADATA SALE
     * ============================================================
     */
    private function updateSaleNote(
        Sale $sale,
        string $key,
        string $value
    ): void {

        $notes =
            $sale->notes ?? '';


        /*
        |--------------------------------------------------------------------------
        | PECAH NOTES
        |--------------------------------------------------------------------------
        */

        $lines = preg_split(
            '/\r\n|\r|\n/',
            $notes
        );


        $updated = false;


        /*
        |--------------------------------------------------------------------------
        | CARI KEY
        |--------------------------------------------------------------------------
        */

        foreach ($lines as &$line) {

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


        /*
        |--------------------------------------------------------------------------
        | JIKA BELUM ADA
        |--------------------------------------------------------------------------
        */

        if (!$updated) {

            $lines[] =
                $key
                . '='
                . $value;
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */

        $sale->update([
            'notes' =>
                implode(
                    PHP_EOL,
                    $lines
                ),
        ]);
    }
}
