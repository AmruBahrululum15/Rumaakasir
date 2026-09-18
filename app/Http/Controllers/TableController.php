<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableController extends Controller
{
    /**
     * ============================================================
     * MENAMPILKAN DAFTAR MEJA
     * ============================================================
     */
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | AMBIL DATA MEJA
        |--------------------------------------------------------------------------
        */

        $tables = Table::query()
            ->orderBy('id', 'asc')
            ->paginate(12);

        /*
        |--------------------------------------------------------------------------
        | STATISTIK MEJA
        |--------------------------------------------------------------------------
        */

        $totalTables = Table::count();

        $activeTables = Table::where(
            'is_active',
            1
        )->count();

        $inactiveTables = Table::where(
            'is_active',
            0
        )->count();

        /*
        |--------------------------------------------------------------------------
        | PESANAN HARI INI
        |--------------------------------------------------------------------------
        |
        | Kita ambil transaksi hari ini.
        |
        | Informasi meja disimpan pada kolom notes Sale
        | dengan format:
        |
        | TABLE_ID=1
        | TABLE_NAME=Meja 01
        |
        */

        $todaySales = Sale::query()
            ->whereDate(
                'sale_date',
                today()
            )
            ->orderByDesc('id')
            ->get([
                'id',
                'code',
                'total',
                'paid',
                'notes',
                'created_at',
            ]);

        /*
        |--------------------------------------------------------------------------
        | SIAPKAN DATA ORDER PER MEJA
        |--------------------------------------------------------------------------
        */

        $tableOrders = [];

        foreach ($todaySales as $sale) {

            $tableId = $this->getSaleNote(
                $sale->notes,
                'TABLE_ID'
            );

            /*
            |--------------------------------------------------------------------------
            | BUKAN ORDER MEJA
            |--------------------------------------------------------------------------
            */

            if (
                ! $tableId
                || ! is_numeric($tableId)
            ) {
                continue;
            }

            $tableId = (int) $tableId;

            /*
            |--------------------------------------------------------------------------
            | PAYMENT STATUS
            |--------------------------------------------------------------------------
            */

            $paymentStatus = strtoupper(
                (string) (
                    $this->getSaleNote(
                        $sale->notes,
                        'PAYMENT_STATUS'
                    ) ?? ''
                )
            );

            /*
            |--------------------------------------------------------------------------
            | PAYMENT METHOD
            |--------------------------------------------------------------------------
            */

            $paymentMethod = strtoupper(
                (string) (
                    $this->getSaleNote(
                        $sale->notes,
                        'PAYMENT_METHOD'
                    ) ?? ''
                )
            );

            /*
            |--------------------------------------------------------------------------
            | TABLE NAME
            |--------------------------------------------------------------------------
            */

            $tableName =
                $this->getSaleNote(
                    $sale->notes,
                    'TABLE_NAME'
                );

            /*
            |--------------------------------------------------------------------------
            | CUSTOMER
            |--------------------------------------------------------------------------
            */

            $customerName =
                $this->getSaleNote(
                    $sale->notes,
                    'CUSTOMER_NAME'
                );

            /*
            |--------------------------------------------------------------------------
            | STATUS ORDER
            |--------------------------------------------------------------------------
            */

            $orderStatus = $this->getOrderStatus(
                $paymentStatus,
                $paymentMethod,
                $sale
            );

            /*
            |--------------------------------------------------------------------------
            | SIMPAN ORDER TERBARU
            |--------------------------------------------------------------------------
            |
            | Karena query diurutkan dari terbaru,
            | order pertama adalah order terbaru.
            |
            */

            if (! isset($tableOrders[$tableId])) {

                $tableOrders[$tableId] = [
                    'latest_order' => [
                        'id' => $sale->id,

                        'code' => $sale->code,

                        'total' => (float) $sale->total,

                        'paid' => (float) ($sale->paid ?? 0),

                        'table_name' => $tableName,

                        'customer_name' => $customerName,

                        'payment_method' => $paymentMethod,

                        'payment_status' => $paymentStatus,

                        'order_status' => $orderStatus,

                        'created_at' => $sale->created_at,
                    ],

                    'order_count' => 1,

                    'total_amount' => (float) $sale->total,
                ];

            } else {

                /*
                |--------------------------------------------------------------------------
                | TAMBAH JUMLAH ORDER
                |--------------------------------------------------------------------------
                */

                $tableOrders[$tableId]['order_count']++;

                /*
                |--------------------------------------------------------------------------
                | TOTAL NILAI ORDER HARI INI
                |--------------------------------------------------------------------------
                */

                $tableOrders[$tableId]['total_amount']
                    += (float) $sale->total;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | GABUNGKAN DATA ORDER KE DATA TABLE
        |--------------------------------------------------------------------------
        */

        $tables->getCollection()
            ->transform(function ($table) use (
                $tableOrders
            ) {

                $order =
                    $tableOrders[$table->id]
                    ?? null;

                /*
                |--------------------------------------------------------------------------
                | DEFAULT
                |--------------------------------------------------------------------------
                */

                $table->order_count = 0;

                $table->total_order_amount = 0;

                $table->latest_order = null;

                $table->has_order = false;

                /*
                |--------------------------------------------------------------------------
                | JIKA ADA ORDER
                |--------------------------------------------------------------------------
                */

                if ($order) {

                    $table->order_count =
                        $order['order_count'];

                    $table->total_order_amount =
                        $order['total_amount'];

                    $table->latest_order =
                        $order['latest_order'];

                    $table->has_order = true;
                }

                return $table;
            });

        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'tables.index',
            compact(
                'tables',
                'totalTables',
                'activeTables',
                'inactiveTables'
            )
        );
    }

    /**
     * ============================================================
     * FORM TAMBAH MEJA
     * ============================================================
     */
    public function create()
    {
        $totalTables = Table::count();
        $activeTables = Table::where('is_active', true)->count();
        $inactiveTables = $totalTables - $activeTables;

        return view('tables.create', compact(
            'totalTables',
            'activeTables',
            'inactiveTables'
        ));
    }

    /**
     * ============================================================
     * TAMBAH SATU MEJA
     * ============================================================
     */
    public function store(
        Request $request
    ) {

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        Table::create([
            'name' => $validated['name'],

            'qr_token' => Str::uuid()->toString(),

            'is_active' => 1,
        ]);

        return redirect()
            ->route('tables.index')
            ->with(
                'success',
                'Meja berhasil ditambahkan.'
            );
    }

    /**
     * ============================================================
     * DETAIL MEJA
     * ============================================================
     */
    public function show(
        Table $table
    ) {

        $url = url(
            '/order/table/'
            .$table->qr_token
        );

        return view(
            'tables.show',
            compact(
                'table',
                'url'
            )
        );
    }

    /**
     * ============================================================
     * FORM EDIT MEJA
     * ============================================================
     */
    public function edit(
        Table $table
    ) {

        return view(
            'tables.edit',
            compact('table')
        );
    }

    /**
     * ============================================================
     * UPDATE NAMA MEJA
     * ============================================================
     */
    public function update(
        Request $request,
        Table $table
    ) {

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        $table->update([
            'name' => $validated['name'],
        ]);

        return redirect()
            ->route('tables.index')
            ->with(
                'success',
                'Meja berhasil diperbarui.'
            );
    }

    /**
     * ============================================================
     * HAPUS MEJA
     * ============================================================
     */
    public function destroy(
        Table $table
    ) {

        $table->delete();

        return redirect()
            ->route('tables.index')
            ->with(
                'success',
                'Meja berhasil dihapus.'
            );
    }

    /**
     * ============================================================
     * AKTIF / NONAKTIFKAN MEJA
     * ============================================================
     */
    public function toggleStatus(
        Table $table
    ) {

        $newStatus =
            $table->is_active
                ? 0
                : 1;

        $table->update([
            'is_active' => $newStatus,
        ]);

        $status =
            $newStatus
                ? 'diaktifkan'
                : 'dinonaktifkan';

        return back()->with(
            'success',
            "QR {$table->name} berhasil {$status}."
        );
    }

    /**
     * ============================================================
     * MENAMPILKAN QR CODE
     * ============================================================
     */
    public function qr(
        Table $table
    ) {

        $url = url(
            '/order/table/'
            .$table->qr_token
        );

        return view(
            'tables.qr',
            compact(
                'table',
                'url'
            )
        );
    }

    /**
     * SVG QR image used by the table cards and printable QR page.
     *
     * SVG is deliberately used here so it works on servers without the GD or
     * Imagick extensions and remains sharp when printed.
     */
    public function qrImage(Table $table)
    {
        if (! $table->is_active) {
            abort(404, 'Meja tidak aktif.');
        }

        $url = route('customer.menu', [
            'qr_token' => $table->qr_token,
        ]);

        return response(
            QrCode::format('svg')
                ->size(320)
                ->margin(1)
                ->generate($url),
            200,
            [
                'Content-Type' => 'image/svg+xml; charset=UTF-8',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ]
        );
    }

    /**
     * ============================================================
     * GENERATE BEBERAPA MEJA SEKALIGUS
     * ============================================================
     */
    public function generate(
        Request $request
    ) {

        $validated = $request->validate([
            'jumlah' => [
                'required',
                'integer',
                'min:1',
                'max:500',
            ],
        ]);

        $jumlahTarget =
            (int) $validated['jumlah'];

        $jumlahSekarang = Table::where('is_active', true)->count();

        /*
        |--------------------------------------------------------------------------
        | JUMLAH SUDAH SESUAI
        |--------------------------------------------------------------------------
        */

        if (
            $jumlahTarget ===
            $jumlahSekarang
        ) {

            return back()->with(
                'info',
                "Jumlah meja sudah {$jumlahTarget}."
            );
        }

        /*
        |--------------------------------------------------------------------------
        | TAMBAH / AKTIFKAN MEJA
        |--------------------------------------------------------------------------
        */

        if ($jumlahTarget > $jumlahSekarang) {
            $jumlahYangDibutuhkan = $jumlahTarget - $jumlahSekarang;

            // Reuse inactive tables first so their existing QR can be used again.
            $tablesToActivate = Table::query()
                ->where('is_active', false)
                ->orderBy('id')
                ->limit($jumlahYangDibutuhkan)
                ->get();

            foreach ($tablesToActivate as $table) {
                $table->update(['is_active' => true]);
            }

            $jumlahBaru = $jumlahYangDibutuhkan - $tablesToActivate->count();
            $nextNumber = (int) Table::max('id') + 1;

            for ($i = 0; $i < $jumlahBaru; $i++) {

                Table::create([
                    'name' => 'Meja '
                        .str_pad(
                            $nextNumber + $i,
                            2,
                            '0',
                            STR_PAD_LEFT
                        ),

                    'qr_token' => Str::uuid()
                        ->toString(),

                    'is_active' => 1,
                ]);
            }

            return redirect()
                ->route('tables.index')
                ->with(
                    'success',
                    "Jumlah meja aktif disesuaikan menjadi {$jumlahTarget}."
                );
        }

        /*
        |--------------------------------------------------------------------------
        | TARGET LEBIH KECIL
        |--------------------------------------------------------------------------
        |
        | Jangan hapus meja.
        |
        | Meja paling belakang dinonaktifkan
        | supaya riwayat transaksi tetap aman.
        |
        */

        $tablesToDisable =
            Table::query()
                ->where('is_active', true)
                ->orderByDesc('id')
                ->limit($jumlahSekarang - $jumlahTarget)
                ->get();

        foreach (
            $tablesToDisable as $table
        ) {

            $table->update([
                'is_active' => 0,
            ]);
        }

        return redirect()
            ->route('tables.index')
            ->with(
                'success',
                "Jumlah aktif meja disesuaikan menjadi {$jumlahTarget}. "
                .'Meja yang dikurangi dinonaktifkan agar riwayat transaksi tetap aman.'
            );
    }

    /**
     * ============================================================
     * CETAK SEMUA QR
     * ============================================================
     */
    public function printAllQr()
    {

        $tables = Table::query()
            ->where(
                'is_active',
                1
            )
            ->orderBy(
                'id',
                'asc'
            )
            ->get();

        $totalTables =
            $tables->count();

        return view(
            'tables.print-all-qr',
            compact(
                'tables',
                'totalTables'
            )
        );
    }

    /**
     * ============================================================
     * AMBIL VALUE DARI SALE NOTES
     * ============================================================
     */
    private function getSaleNote(
        ?string $notes,
        string $key
    ): ?string {

        if (
            ! $notes
            || trim($notes) === ''
        ) {
            return null;
        }

        $lines = preg_split(
            '/\r\n|\r|\n/',
            $notes
        );

        foreach (
            $lines as $line
        ) {

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

    /**
     * ============================================================
     * TENTUKAN STATUS ORDER
     * ============================================================
     */
    private function getOrderStatus(
        string $paymentStatus,
        string $paymentMethod,
        Sale $sale
    ): string {

        /*
        |--------------------------------------------------------------------------
        | CASH
        |--------------------------------------------------------------------------
        */

        if (
            $paymentMethod === 'CASH'
            && $paymentStatus === 'CASH_PENDING'
        ) {

            return 'Menunggu Pembayaran';
        }

        /*
        |--------------------------------------------------------------------------
        | PAYMENT PENDING
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $paymentStatus,
                [
                    'PENDING',
                    'CASH_PENDING',
                ],
                true
            )
        ) {

            return 'Menunggu Pembayaran';
        }

        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $paymentStatus,
                [
                    'SETTLEMENT',
                    'PAID',
                    'SUCCESS',
                    'CAPTURE',
                ],
                true
            )
        ) {

            return 'Sudah Dibayar';
        }

        /*
        |--------------------------------------------------------------------------
        | EXPIRED
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $paymentStatus,
                [
                    'EXPIRE',
                    'EXPIRED',
                ],
                true
            )
        ) {

            return 'Kadaluarsa';
        }

        /*
        |--------------------------------------------------------------------------
        | CANCEL
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $paymentStatus,
                [
                    'CANCEL',
                    'CANCELLED',
                ],
                true
            )
        ) {

            return 'Dibatalkan';
        }

        /*
        |--------------------------------------------------------------------------
        | DEFAULT
        |--------------------------------------------------------------------------
        */

        if (
            (float) $sale->paid
            >=
            (float) $sale->total
            && (float) $sale->total > 0
        ) {

            return 'Sudah Dibayar';
        }

        return 'Pesanan Baru';
    }
}
