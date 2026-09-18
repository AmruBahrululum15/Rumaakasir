<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class SaleController extends Controller
{
    /**
     * ============================================================
     * DAFTAR PENJUALAN
     * ============================================================
     */
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | QUERY DASAR
        |--------------------------------------------------------------------------
        */

        $query = Sale::with([
            'saleDetails.product', 'customer', 'user', 'table',
        ])->latest('id');

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim(
                $request->search
            );

            $query->where(function ($q) use ($search) {

                $q->where(
                    'code',
                    'like',
                    '%'.$search.'%'
                )
                    ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', '%'.$search.'%')->orWhere('username', 'like', '%'.$search.'%'))
                    ->orWhere(
                        'notes',
                        'like',
                        '%'.$search.'%'
                    );

            });
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('order_source')) {
            $query->where('order_source', $request->order_source);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        /*
        |--------------------------------------------------------------------------
        | PERIODE
        |--------------------------------------------------------------------------
        */

        $period = $request->get(
            'period',
            'all'
        );

        /*
        |--------------------------------------------------------------------------
        | HARIAN
        |--------------------------------------------------------------------------
        */

        if (
            $period === 'daily' &&
            $request->filled('date')
        ) {

            $query->whereDate(
                'sale_date',
                $request->date
            );
        }

        /*
        |--------------------------------------------------------------------------
        | BULANAN
        |--------------------------------------------------------------------------
        */

        if (
            $period === 'monthly' &&
            $request->filled('month')
        ) {

            $month = $request->month;

            $parts = explode(
                '-',
                $month
            );

            if (count($parts) === 2) {

                $year = (int) $parts[0];

                $monthNumber = (int) $parts[1];

                $query
                    ->whereYear(
                        'sale_date',
                        $year
                    )
                    ->whereMonth(
                        'sale_date',
                        $monthNumber
                    );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | TAHUNAN
        |--------------------------------------------------------------------------
        */

        if (
            $period === 'yearly' &&
            $request->filled('year')
        ) {

            $query->whereYear(
                'sale_date',
                $request->year
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER STATUS PEMBAYARAN
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('payment_status')
        ) {

            $paymentStatus =
                strtoupper(
                    trim(
                        $request->payment_status
                    )
                );

            $query->where(function ($statusQuery) use ($paymentStatus) {
                $statusQuery->where('payment_status', $paymentStatus)
                    ->orWhere('notes', 'like', '%PAYMENT_STATUS='.$paymentStatus.'%');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | STATISTIK
        |--------------------------------------------------------------------------
        */

        $statisticsSales =
            (clone $query)->get();

        /*
        |--------------------------------------------------------------------------
        | TOTAL TRANSAKSI
        |--------------------------------------------------------------------------
        */

        $totalTransactions =
            $statisticsSales->count();

        /*
        |--------------------------------------------------------------------------
        | TOTAL PENJUALAN
        |--------------------------------------------------------------------------
        */

        $totalSales =
            $statisticsSales->sum('total');

        /*
        |--------------------------------------------------------------------------
        | TOTAL PRODUK
        |--------------------------------------------------------------------------
        */

        $totalProducts =
            $statisticsSales->sum(
                function ($sale) {

                    return $sale
                        ->saleDetails
                        ->sum('quantity');

                }
            );

        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        $sales =
            $query
                ->paginate(10)
                ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | DAFTAR TAHUN
        |--------------------------------------------------------------------------
        */

        $years =
            Sale::query()
                ->selectRaw(
                    'YEAR(sale_date) as year'
                )
                ->distinct()
                ->orderByDesc('year')
                ->pluck('year');

        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'sales.index',
            compact(
                'sales',
                'totalTransactions',
                'totalSales',
                'totalProducts',
                'years',
                'period'
            )
        );
    }

    /**
     * ============================================================
     * FORM TAMBAH PENJUALAN
     * ============================================================
     */
    public function create()
    {
        $products =
            Product::with('category')
                ->orderBy('name')
                ->get();

        $code =
            $this->generateSaleCode();

        return view(
            'sales.create',
            compact(
                'products',
                'code'
            )
        );
    }

    /**
     * ============================================================
     * SIMPAN PENJUALAN CASH
     * ============================================================
     */
    public function store(Request $request, MidtransService $midtrans)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'sale_date' => [
                'required',
                'date',
            ],

            'paid' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'customer_name' => ['required', 'string', 'max:100'],

            'payment_method' => ['required', 'in:cash,qris,va'],

            'notes' => [
                'nullable',
                'string',
            ],

            'products' => [
                'required',
                'array',
                'min:1',
            ],

            'products.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'products.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

        ], [

            'sale_date.required' => 'Tanggal penjualan wajib diisi.',

            'sale_date.date' => 'Tanggal penjualan tidak valid.',

            'paid.required' => 'Uang pembayaran wajib diisi.',

            'paid.numeric' => 'Uang pembayaran harus berupa angka.',

            'paid.min' => 'Uang pembayaran tidak boleh kurang dari 0.',

            'customer_name.required' => 'Nama pelanggan wajib diisi.',

            'products.required' => 'Minimal pilih satu produk.',

            'products.array' => 'Data produk tidak valid.',

            'products.min' => 'Minimal pilih satu produk.',

            'products.*.product_id.required' => 'Produk wajib dipilih.',

            'products.*.product_id.exists' => 'Produk tidak ditemukan.',

            'products.*.quantity.required' => 'Jumlah produk wajib diisi.',

            'products.*.quantity.integer' => 'Jumlah produk harus berupa angka bulat.',

            'products.*.quantity.min' => 'Jumlah produk minimal 1.',

        ]);

        try {

            $sale = DB::transaction(
                function () use ($request) {
                    $user = $request->user();
                    if (! $user || ! $user->is_active) {
                        throw new \Exception('Akun kasir tidak aktif.');
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | GENERATE CODE
                    |--------------------------------------------------------------------------
                    */

                    $code =
                        $this->generateSaleCode();

                    /*
                    |--------------------------------------------------------------------------
                    | GABUNG PRODUK SAMA
                    |--------------------------------------------------------------------------
                    */

                    $groupedProducts = [];

                    foreach (
                        $request->products as $item
                    ) {

                        $productId =
                            (int) $item['product_id'];

                        $quantity =
                            (int) $item['quantity'];

                        if (
                            ! isset(
                                $groupedProducts[
                                    $productId
                                ]
                            )
                        ) {

                            $groupedProducts[
                                $productId
                            ] = 0;
                        }

                        $groupedProducts[
                            $productId
                        ] += $quantity;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | HITUNG TOTAL
                    |--------------------------------------------------------------------------
                    */

                    $total = 0;

                    $details = [];

                    /*
                    |--------------------------------------------------------------------------
                    | CEK STOK
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $groupedProducts as $productId => $quantity
                    ) {

                        $product =
                            Product::lockForUpdate()
                                ->findOrFail(
                                    $productId
                                );

                        if (
                            $product->stock <
                            $quantity
                        ) {

                            throw new \Exception(
                                'Stok produk '
                                .$product->name
                                .' tidak mencukupi. '
                                .'Stok tersedia: '
                                .$product->stock
                                .', jumlah diminta: '
                                .$quantity
                                .'.'

                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | HARGA
                        |--------------------------------------------------------------------------
                        */

                        $price =
                            (float) $product->price;

                        $subtotal =
                            $price * $quantity;

                        $total +=
                            $subtotal;

                        $details[] = [

                            'product' => $product,

                            'quantity' => $quantity,

                            'price' => $price,

                            'subtotal' => $subtotal,

                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | PEMBAYARAN CASH
                    |--------------------------------------------------------------------------
                    */

                    $paymentMethod = $request->string('payment_method')->lower()->value();
                    $paid = (float) ($request->paid ?? 0);

                    if ($paymentMethod === 'cash' && $paid < $total) {

                        $kurang =
                            $total - $paid;

                        throw new \Exception(
                            'Uang pembayaran kurang sebesar Rp '
                            .
                            number_format(
                                $kurang,
                                0,
                                ',',
                                '.'
                            )

                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | KEMBALIAN
                    |--------------------------------------------------------------------------
                    */

                    $change = $paymentMethod === 'cash' ? $paid - $total : 0;

                    $customerName = trim((string) $request->input('customer_name'));
                    // POS only collects a name: reuse an existing exact-name customer,
                    // otherwise create a minimal customer without inventing contact data.
                    $customer = Customer::query()->where('name', $customerName)->lockForUpdate()->first();
                    $customer ??= Customer::create(['name' => $customerName]);

                    /*
                    |--------------------------------------------------------------------------
                    | NOTES
                    |--------------------------------------------------------------------------
                    */

                    $notes =
                        $this->buildCashNotes(
                            $request->notes
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | SIMPAN SALE
                    |--------------------------------------------------------------------------
                    */

                    $sale =
                        Sale::create([

                            'code' => $code,

                            'sale_date' => $request->sale_date,

                            'total' => $total,

                            'paid' => $paid,

                            'change' => $change,

                            'customer_id' => $customer?->id,
                            // Never accept a user/cashier id from the request.
                            'user_id' => $user->id,
                            'payment_method' => $paymentMethod,
                            'payment_status' => $paymentMethod === 'cash' ? 'PAID' : 'PENDING',
                            // POS source is owned by this endpoint, not client input.
                            'order_source' => 'cashier',

                            'notes' => $notes,

                        ]);

                    /*
                    |--------------------------------------------------------------------------
                    | DETAIL + KURANGI STOK
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $details as $detail
                    ) {

                        $sale
                            ->saleDetails()
                            ->create([

                                'product_id' => $detail[
                                        'product'
                                    ]->id,

                                'quantity' => $detail[
                                        'quantity'
                                    ],

                                'price' => $detail[
                                        'price'
                                    ],

                                'subtotal' => $detail[
                                        'subtotal'
                                    ],

                            ]);

                        $detail['product']
                            ->decrement(
                                'stock',
                                $detail['quantity']
                            );
                    }

                    return $sale;
                }
            );

            if (in_array($sale->payment_method, ['qris', 'va'], true)) {
                $cashier = $sale->cashier_display_name;
                $snapToken = $midtrans->createSnapToken($sale, null, $sale->customer_display_name, '', '', $sale->payment_method);

                return view('sales.payment', compact('sale', 'snapToken', 'cashier'));
            }

            return redirect()
                ->route('sales.index')
                ->with(
                    'success',
                    "Penjualan {$sale->code} berhasil disimpan."
                );

        } catch (Throwable $e) {

            report($e);

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /**
     * ============================================================
     * DETAIL
     * ============================================================
     */
    public function show(Sale $sale)
    {
        $sale->load([
            'saleDetails.product', 'customer', 'user', 'table',
        ]);

        return view(
            'sales.show',
            compact('sale')
        );
    }

    /**
     * ============================================================
     * STRUK
     * ============================================================
     */
    public function receipt(Sale $sale)
    {
        $sale->load([
            'saleDetails.product', 'customer', 'user', 'table',
        ]);

        return view(
            'sales.receipt',
            compact('sale')
        );
    }

    /**
     * ============================================================
     * FORM EDIT
     * ============================================================
     */
    public function edit(Sale $sale)
    {
        $sale->load([
            'saleDetails.product',
        ]);

        $products =
            Product::with('category')
                ->orderBy('name')
                ->get();

        return view(
            'sales.edit',
            compact(
                'sale',
                'products'
            )
        );
    }

    /**
     * ============================================================
     * UPDATE
     * ============================================================
     */
    public function update(
        Request $request,
        Sale $sale
    ) {

        $request->validate([

            'sale_date' => [
                'required',
                'date',
            ],

            'paid' => [
                'required',
                'numeric',
                'min:0',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'products' => [
                'required',
                'array',
                'min:1',
            ],

            'products.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'products.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

        ], [

            'sale_date.required' => 'Tanggal penjualan wajib diisi.',

            'sale_date.date' => 'Tanggal penjualan tidak valid.',

            'paid.required' => 'Uang pembayaran wajib diisi.',

            'paid.numeric' => 'Uang pembayaran harus berupa angka.',

            'paid.min' => 'Uang pembayaran tidak boleh kurang dari 0.',

            'products.required' => 'Minimal pilih satu produk.',

            'products.min' => 'Minimal pilih satu produk.',

            'products.*.product_id.required' => 'Produk wajib dipilih.',

            'products.*.product_id.exists' => 'Produk tidak ditemukan.',

            'products.*.quantity.required' => 'Jumlah produk wajib diisi.',

            'products.*.quantity.integer' => 'Jumlah produk harus berupa angka bulat.',

            'products.*.quantity.min' => 'Jumlah produk minimal 1.',

        ]);

        try {

            DB::transaction(
                function () use (
                    $request,
                    $sale
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | LOCK SALE
                    |--------------------------------------------------------------------------
                    */

                    $sale =
                        Sale::lockForUpdate()
                            ->findOrFail(
                                $sale->id
                            );

                    /*
                    |--------------------------------------------------------------------------
                    | AMBIL METADATA PEMBAYARAN LAMA
                    |--------------------------------------------------------------------------
                    */

                    $oldPaymentStatus =
                        $this->getSaleNote(
                            $sale,
                            'PAYMENT_STATUS'
                        );

                    $oldPaymentMethod =
                        $this->getSaleNote(
                            $sale,
                            'PAYMENT_METHOD'
                        );

                    $oldTransactionId =
                        $this->getSaleNote(
                            $sale,
                            'MIDTRANS_TRANSACTION_ID'
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | LOAD DETAIL
                    |--------------------------------------------------------------------------
                    */

                    $sale->load([
                        'saleDetails',
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | KEMBALIKAN STOK LAMA
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $sale->saleDetails as $oldDetail
                    ) {

                        $oldProduct =
                            Product::lockForUpdate()
                                ->find(
                                    $oldDetail->product_id
                                );

                        if ($oldProduct) {

                            $oldProduct->increment(
                                'stock',
                                $oldDetail->quantity
                            );
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | GABUNG PRODUK
                    |--------------------------------------------------------------------------
                    */

                    $groupedProducts = [];

                    foreach (
                        $request->products as $item
                    ) {

                        $productId =
                            (int) $item['product_id'];

                        $quantity =
                            (int) $item['quantity'];

                        if (
                            ! isset(
                                $groupedProducts[
                                    $productId
                                ]
                            )
                        ) {

                            $groupedProducts[
                                $productId
                            ] = 0;
                        }

                        $groupedProducts[
                            $productId
                        ] += $quantity;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | HITUNG ULANG
                    |--------------------------------------------------------------------------
                    */

                    $total = 0;

                    $details = [];

                    foreach (
                        $groupedProducts as $productId => $quantity
                    ) {

                        $product =
                            Product::lockForUpdate()
                                ->findOrFail(
                                    $productId
                                );

                        if (
                            $product->stock <
                            $quantity
                        ) {

                            throw new \Exception(
                                'Stok produk '
                                .$product->name
                                .' tidak mencukupi. '
                                .'Stok tersedia: '
                                .$product->stock
                                .', jumlah diminta: '
                                .$quantity
                                .'.'

                            );
                        }

                        $price =
                            (float) $product->price;

                        $subtotal =
                            $price * $quantity;

                        $total +=
                            $subtotal;

                        $details[] = [

                            'product' => $product,

                            'quantity' => $quantity,

                            'price' => $price,

                            'subtotal' => $subtotal,

                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | PEMBAYARAN
                    |--------------------------------------------------------------------------
                    */

                    $paid =
                        (float) $request->paid;

                    if ($paid < $total) {

                        $kurang =
                            $total - $paid;

                        throw new \Exception(
                            'Uang pembayaran kurang sebesar Rp '
                            .
                            number_format(
                                $kurang,
                                0,
                                ',',
                                '.'
                            )

                        );
                    }

                    $change =
                        $paid - $total;

                    /*
                    |--------------------------------------------------------------------------
                    | NOTES
                    |--------------------------------------------------------------------------
                    */

                    $notes =
                        $this->buildUpdatedNotes(
                            $request->notes,
                            $oldPaymentStatus,
                            $oldPaymentMethod,
                            $oldTransactionId
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE SALE
                    |--------------------------------------------------------------------------
                    */

                    $sale->update([

                        'sale_date' => $request->sale_date,

                        'total' => $total,

                        'paid' => $paid,

                        'change' => $change,

                        'notes' => $notes,

                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | HAPUS DETAIL LAMA
                    |--------------------------------------------------------------------------
                    */

                    $sale
                        ->saleDetails()
                        ->delete();

                    /*
                    |--------------------------------------------------------------------------
                    | SIMPAN DETAIL BARU
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $details as $detail
                    ) {

                        $sale
                            ->saleDetails()
                            ->create([

                                'product_id' => $detail[
                                        'product'
                                    ]->id,

                                'quantity' => $detail[
                                        'quantity'
                                    ],

                                'price' => $detail[
                                        'price'
                                    ],

                                'subtotal' => $detail[
                                        'subtotal'
                                    ],

                            ]);

                        $detail['product']
                            ->decrement(
                                'stock',
                                $detail['quantity']
                            );
                    }
                }
            );

            return redirect()
                ->route('sales.index')
                ->with(
                    'success',
                    'Penjualan berhasil diperbarui.'
                );

        } catch (Throwable $e) {

            report($e);

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /**
     * ============================================================
     * HAPUS
     * ============================================================
     */
    public function destroy(Sale $sale)
    {
        try {

            DB::transaction(
                function () use ($sale) {

                    $sale =
                        Sale::lockForUpdate()
                            ->findOrFail(
                                $sale->id
                            );

                    $sale->load([
                        'saleDetails',
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | CEK APAKAH STOCK SUDAH DIRESTORE
                    |--------------------------------------------------------------------------
                    */

                    $stockRestored =
                        $this->getSaleNote(
                            $sale,
                            'STOCK_RESTORED'
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | KEMBALIKAN STOK
                    |--------------------------------------------------------------------------
                    */

                    if ($stockRestored !== '1') {

                        foreach (
                            $sale->saleDetails as $detail
                        ) {

                            $product =
                                Product::lockForUpdate()
                                    ->find(
                                        $detail->product_id
                                    );

                            if ($product) {

                                $product->increment(
                                    'stock',
                                    $detail->quantity
                                );
                            }
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | HAPUS DETAIL
                    |--------------------------------------------------------------------------
                    */

                    $sale
                        ->saleDetails()
                        ->delete();

                    /*
                    |--------------------------------------------------------------------------
                    | HAPUS SALE
                    |--------------------------------------------------------------------------
                    */

                    $sale->delete();
                }
            );

            return redirect()
                ->route('sales.index')
                ->with(
                    'success',
                    'Penjualan berhasil dihapus dan stok telah dikembalikan.'
                );

        } catch (Throwable $e) {

            report($e);

            return redirect()
                ->route('sales.index')
                ->with(
                    'error',
                    'Penjualan gagal dihapus: '
                    .$e->getMessage()
                );
        }
    }

    /**
     * ============================================================
     * GENERATE KODE
     * ============================================================
     */
    private function generateSaleCode(): string
    {
        $lastSale =
            Sale::orderByDesc('id')
                ->first();

        if (
            $lastSale &&
            preg_match(
                '/PJ-(\d+)/',
                $lastSale->code,
                $matches
            )
        ) {

            $lastNumber =
                (int) $matches[1];

        } else {

            $lastNumber = 0;
        }

        $nextNumber =
            $lastNumber + 1;

        $code =
            'PJ-'.
            str_pad(
                $nextNumber,
                4,
                '0',
                STR_PAD_LEFT
            );

        while (
            Sale::where(
                'code',
                $code
            )->exists()
        ) {

            $nextNumber++;

            $code =
                'PJ-'.
                str_pad(
                    $nextNumber,
                    4,
                    '0',
                    STR_PAD_LEFT
                );
        }

        return $code;
    }

    /**
     * ============================================================
     * BUILD CASH NOTES
     * ============================================================
     */
    private function buildCashNotes(
        ?string $notes
    ): string {

        $notes =
            trim(
                (string) $notes
            );

        $metadata = [

            'PAYMENT_STATUS=PAID',

            'PAYMENT_METHOD=CASH',

        ];

        if ($notes !== '') {

            return $notes
                .PHP_EOL
                .implode(
                    PHP_EOL,
                    $metadata
                );
        }

        return implode(
            PHP_EOL,
            $metadata
        );
    }

    /**
     * ============================================================
     * BUILD UPDATED NOTES
     * ============================================================
     */
    private function buildUpdatedNotes(
        ?string $notes,
        ?string $paymentStatus,
        ?string $paymentMethod,
        ?string $transactionId
    ): string {

        $notes =
            trim(
                (string) $notes
            );

        /*
        |--------------------------------------------------------------------------
        | HAPUS METADATA LAMA DARI CATATAN
        |--------------------------------------------------------------------------
        */

        $lines =
            preg_split(
                '/\r\n|\r|\n/',
                $notes
            );

        $cleanLines = [];

        foreach ($lines as $line) {

            $line =
                trim($line);

            if ($line === '') {
                continue;
            }

            if (
                str_starts_with(
                    $line,
                    'PAYMENT_STATUS='
                )
            ) {
                continue;
            }

            if (
                str_starts_with(
                    $line,
                    'PAYMENT_METHOD='
                )
            ) {
                continue;
            }

            if (
                str_starts_with(
                    $line,
                    'MIDTRANS_TRANSACTION_ID='
                )
            ) {
                continue;
            }

            if (
                str_starts_with(
                    $line,
                    'PAYMENT_TYPE='
                )
            ) {
                continue;
            }

            $cleanLines[] =
                $line;
        }

        /*
        |--------------------------------------------------------------------------
        | TAMBAHKAN PAYMENT STATUS
        |--------------------------------------------------------------------------
        */

        if ($paymentStatus !== null) {

            $cleanLines[] =
                'PAYMENT_STATUS='
                .$paymentStatus;
        }

        /*
        |--------------------------------------------------------------------------
        | TAMBAHKAN PAYMENT METHOD
        |--------------------------------------------------------------------------
        */

        if ($paymentMethod !== null) {

            $cleanLines[] =
                'PAYMENT_METHOD='
                .$paymentMethod;
        }

        /*
        |--------------------------------------------------------------------------
        | TRANSACTION ID
        |--------------------------------------------------------------------------
        */

        if ($transactionId !== null) {

            $cleanLines[] =
                'MIDTRANS_TRANSACTION_ID='
                .$transactionId;
        }

        return implode(
            PHP_EOL,
            $cleanLines
        );
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
                    $key.'='
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
}
