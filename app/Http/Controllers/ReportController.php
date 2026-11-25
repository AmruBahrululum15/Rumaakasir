<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\ReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
class ReportController extends Controller
{
    /**
     * Menampilkan laporan penjualan dan pembelian.
     */
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | FILTER UTAMA
        |--------------------------------------------------------------------------
        */

        $reportType = $request->get('report_type', 'sales');

        if (!in_array($reportType, ['sales', 'purchases'])) {
            $reportType = 'sales';
        }

        $period = $request->get('period', 'monthly');

        if (!in_array($period, ['daily', 'monthly', 'yearly'])) {
            $period = 'monthly';
        }

        $selectedDate = $request->get(
            'date',
            Carbon::today()->format('Y-m-d')
        );

        $selectedMonth = $request->get(
            'month',
            Carbon::today()->format('Y-m')
        );

        $selectedYear = $request->get(
            'year',
            Carbon::today()->year
        );

        /*
        |--------------------------------------------------------------------------
        | VALIDASI TANGGAL
        |--------------------------------------------------------------------------
        */

        try {
            $dateObject = Carbon::createFromFormat(
                'Y-m-d',
                $selectedDate
            );

            if (!$dateObject || $dateObject->format('Y-m-d') !== $selectedDate) {
                throw new \Exception();
            }
        } catch (\Exception $e) {

            $selectedDate = Carbon::today()->format('Y-m-d');
            $dateObject = Carbon::today();
        }

        try {
            $monthDate = Carbon::createFromFormat(
                'Y-m',
                $selectedMonth
            );

            if (!$monthDate || $monthDate->format('Y-m') !== $selectedMonth) {
                throw new \Exception();
            }
        } catch (\Exception $e) {

            $selectedMonth = Carbon::today()->format('Y-m');
            $monthDate = Carbon::today();
        }

        $selectedYear = (int) $selectedYear;

        /*
        |--------------------------------------------------------------------------
        | QUERY PENJUALAN
        |--------------------------------------------------------------------------
        */

        $salesQuery = Sale::validForTotals();

        if ($period === 'daily') {

            $salesQuery->whereDate(
                'sale_date',
                $selectedDate
            );

        } elseif ($period === 'monthly') {

            $salesQuery
                ->whereYear(
                    'sale_date',
                    $monthDate->year
                )
                ->whereMonth(
                    'sale_date',
                    $monthDate->month
                );

        } else {

            $salesQuery->whereYear(
                'sale_date',
                $selectedYear
            );
        }

        /*
        |--------------------------------------------------------------------------
        | QUERY PEMBELIAN
        |--------------------------------------------------------------------------
        */

        $purchasesQuery = Purchase::query();

        if ($period === 'daily') {

            $purchasesQuery->whereDate(
                'purchase_date',
                $selectedDate
            );

        } elseif ($period === 'monthly') {

            $purchasesQuery
                ->whereYear(
                    'purchase_date',
                    $monthDate->year
                )
                ->whereMonth(
                    'purchase_date',
                    $monthDate->month
                );

        } else {

            $purchasesQuery->whereYear(
                'purchase_date',
                $selectedYear
            );
        }

        /*
        |--------------------------------------------------------------------------
        | STATISTIK PENJUALAN
        |--------------------------------------------------------------------------
        */

        $totalSalesTransactions = (clone $salesQuery)->count();

        $totalSales = (clone $salesQuery)->sum('total');

        $saleIds = (clone $salesQuery)->pluck('id');

        $totalProductsSold = 0;

        if ($saleIds->isNotEmpty()) {

            $totalProductsSold = DB::table('sale_details')->whereIn('sale_details.sale_id', \App\Models\Sale::validForTotals()->select('id'))->where('sale_details.business_id', app(\App\Tenancy\TenantContext::class)->id())
                ->whereIn(
                    'sale_id',
                    $saleIds
                )
                ->sum('quantity');
        }

        /*
        |--------------------------------------------------------------------------
        | STATISTIK PEMBELIAN
        |--------------------------------------------------------------------------
        */

        $totalPurchaseTransactions = (clone $purchasesQuery)->count();

        $totalPurchases = (clone $purchasesQuery)->sum('total');

        $purchaseIds = (clone $purchasesQuery)->pluck('id');

        $totalProductsPurchased = 0;

        if ($purchaseIds->isNotEmpty()) {

            $totalProductsPurchased = DB::table('purchase_details')->whereIn('purchase_details.purchase_id', \App\Models\Purchase::select('id'))->where('purchase_details.business_id', app(\App\Tenancy\TenantContext::class)->id())
                ->whereIn(
                    'purchase_id',
                    $purchaseIds
                )
                ->sum('quantity');
        }

        /*
        |--------------------------------------------------------------------------
        | DAFTAR TRANSAKSI PENJUALAN
        |--------------------------------------------------------------------------
        */

        $sales = (clone $salesQuery)
            ->with('saleDetails.product')
            ->latest('sale_date')
            ->latest('id')
            ->paginate(
                15,
                ['*'],
                'sales_page'
            )
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | DAFTAR TRANSAKSI PEMBELIAN
        |--------------------------------------------------------------------------
        */

        $purchases = (clone $purchasesQuery)
            ->with([
                'supplier',
                'details.product'
            ])
            ->latest('purchase_date')
            ->latest('id')
            ->paginate(
                15,
                ['*'],
                'purchases_page'
            )
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | PRODUK TERLARIS
        |--------------------------------------------------------------------------
        */

        $bestSalesProducts = collect();

        if ($saleIds->isNotEmpty()) {

            $bestSalesProducts = DB::table('sale_details')->whereIn('sale_details.sale_id', \App\Models\Sale::validForTotals()->select('id'))->where('sale_details.business_id', app(\App\Tenancy\TenantContext::class)->id())
                ->join(
                    'products',
                    'products.id',
                    '=',
                    'sale_details.product_id'
                )
                ->whereIn(
                    'sale_details.sale_id',
                    $saleIds
                )
                ->select(
                    'products.id',
                    'products.name',
                    DB::raw(
                        'SUM(sale_details.quantity) as total_quantity'
                    ),
                    DB::raw(
                        'SUM(sale_details.subtotal) as total_sales'
                    )
                )
                ->groupBy(
                    'products.id',
                    'products.name'
                )
                ->orderByDesc('total_quantity')
                ->limit(10)
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | PRODUK PALING BANYAK DIBELI
        |--------------------------------------------------------------------------
        */

        $bestPurchaseProducts = collect();

        if ($purchaseIds->isNotEmpty()) {

            $bestPurchaseProducts = DB::table('purchase_details')->whereIn('purchase_details.purchase_id', \App\Models\Purchase::select('id'))->where('purchase_details.business_id', app(\App\Tenancy\TenantContext::class)->id())
                ->join(
                    'products',
                    'products.id',
                    '=',
                    'purchase_details.product_id'
                )
                ->whereIn(
                    'purchase_details.purchase_id',
                    $purchaseIds
                )
                ->select(
                    'products.id',
                    'products.name',
                    DB::raw(
                        'SUM(purchase_details.quantity) as total_quantity'
                    ),
                    DB::raw(
                        'SUM(purchase_details.subtotal) as total_purchases'
                    )
                )
                ->groupBy(
                    'products.id',
                    'products.name'
                )
                ->orderByDesc('total_quantity')
                ->limit(10)
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | SUPPLIER TERBANYAK
        |--------------------------------------------------------------------------
        */

        $topSuppliers = collect();

        if ($purchaseIds->isNotEmpty()) {

            $topSuppliers = DB::table('purchases')->whereIn('purchases.id', \App\Models\Purchase::select('id'))->where('purchases.business_id', app(\App\Tenancy\TenantContext::class)->id())
                ->join(
                    'suppliers',
                    'suppliers.id',
                    '=',
                    'purchases.supplier_id'
                )
                ->whereIn(
                    'purchases.id',
                    $purchaseIds
                )
                ->select(
                    'suppliers.id',
                    'suppliers.name',
                    DB::raw(
                        'COUNT(purchases.id) as total_transactions'
                    ),
                    DB::raw(
                        'SUM(purchases.total) as total_purchases'
                    )
                )
                ->groupBy(
                    'suppliers.id',
                    'suppliers.name'
                )
                ->orderByDesc('total_purchases')
                ->limit(10)
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | DATA GRAFIK
        |--------------------------------------------------------------------------
        */

        $chartLabels = [];

        $chartSalesValues = [];

        $chartPurchaseValues = [];

        /*
        |--------------------------------------------------------------------------
        | GRAFIK HARIAN
        |--------------------------------------------------------------------------
        */

        if ($period === 'daily') {

            for ($hour = 0; $hour < 24; $hour++) {

                $chartLabels[] = sprintf(
                    '%02d:00',
                    $hour
                );
            }

            /*
            |--------------------------------------------------------------------------
            | PENJUALAN PER JAM
            |--------------------------------------------------------------------------
            */

            $salesByHour = (clone $salesQuery)
                ->select(
                    DB::raw('HOUR(created_at) as hour'),
                    DB::raw('SUM(total) as total')
                )
                ->groupBy(
                    DB::raw('HOUR(created_at)')
                )
                ->pluck(
                    'total',
                    'hour'
                );

            /*
            |--------------------------------------------------------------------------
            | PEMBELIAN PER JAM
            |--------------------------------------------------------------------------
            */

            $purchasesByHour = (clone $purchasesQuery)
                ->select(
                    DB::raw('HOUR(created_at) as hour'),
                    DB::raw('SUM(total) as total')
                )
                ->groupBy(
                    DB::raw('HOUR(created_at)')
                )
                ->pluck(
                    'total',
                    'hour'
                );

            for ($hour = 0; $hour < 24; $hour++) {

                $chartSalesValues[] =
                    (float) ($salesByHour[$hour] ?? 0);

                $chartPurchaseValues[] =
                    (float) ($purchasesByHour[$hour] ?? 0);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | GRAFIK BULANAN
        |--------------------------------------------------------------------------
        */

        elseif ($period === 'monthly') {

            $daysInMonth = $monthDate->daysInMonth;

            for ($day = 1; $day <= $daysInMonth; $day++) {

                $chartLabels[] = (string) $day;
            }

            /*
            |--------------------------------------------------------------------------
            | PENJUALAN PER HARI
            |--------------------------------------------------------------------------
            */

            $salesByDay = (clone $salesQuery)
                ->select(
                    DB::raw('DAY(sale_date) as day'),
                    DB::raw('SUM(total) as total')
                )
                ->groupBy(
                    DB::raw('DAY(sale_date)')
                )
                ->pluck(
                    'total',
                    'day'
                );

            /*
            |--------------------------------------------------------------------------
            | PEMBELIAN PER HARI
            |--------------------------------------------------------------------------
            */

            $purchasesByDay = (clone $purchasesQuery)
                ->select(
                    DB::raw('DAY(purchase_date) as day'),
                    DB::raw('SUM(total) as total')
                )
                ->groupBy(
                    DB::raw('DAY(purchase_date)')
                )
                ->pluck(
                    'total',
                    'day'
                );

            for ($day = 1; $day <= $daysInMonth; $day++) {

                $chartSalesValues[] =
                    (float) ($salesByDay[$day] ?? 0);

                $chartPurchaseValues[] =
                    (float) ($purchasesByDay[$day] ?? 0);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | GRAFIK TAHUNAN
        |--------------------------------------------------------------------------
        */

        else {

            $namaBulan = [
                'Januari',
                'Februari',
                'Maret',
                'April',
                'Mei',
                'Juni',
                'Juli',
                'Agustus',
                'September',
                'Oktober',
                'November',
                'Desember',
            ];

            $chartLabels = $namaBulan;

            /*
            |--------------------------------------------------------------------------
            | PENJUALAN PER BULAN
            |--------------------------------------------------------------------------
            */

            $salesByMonth = (clone $salesQuery)
                ->select(
                    DB::raw('MONTH(sale_date) as month'),
                    DB::raw('SUM(total) as total')
                )
                ->groupBy(
                    DB::raw('MONTH(sale_date)')
                )
                ->pluck(
                    'total',
                    'month'
                );

            /*
            |--------------------------------------------------------------------------
            | PEMBELIAN PER BULAN
            |--------------------------------------------------------------------------
            */

            $purchasesByMonth = (clone $purchasesQuery)
                ->select(
                    DB::raw('MONTH(purchase_date) as month'),
                    DB::raw('SUM(total) as total')
                )
                ->groupBy(
                    DB::raw('MONTH(purchase_date)')
                )
                ->pluck(
                    'total',
                    'month'
                );

            for ($month = 1; $month <= 12; $month++) {

                $chartSalesValues[] =
                    (float) ($salesByMonth[$month] ?? 0);

                $chartPurchaseValues[] =
                    (float) ($purchasesByMonth[$month] ?? 0);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | JUDUL PERIODE
        |--------------------------------------------------------------------------
        */

        if ($period === 'daily') {

            $periodTitle = 'Harian';

            $periodDescription = $dateObject
                ->translatedFormat('d F Y');

        } elseif ($period === 'monthly') {

            $periodTitle = 'Bulanan';

            $periodDescription = $monthDate
                ->translatedFormat('F Y');

        } else {

            $periodTitle = 'Tahunan';

            $periodDescription =
                'Tahun ' . $selectedYear;
        }

        /*
        |--------------------------------------------------------------------------
        | JUDUL JENIS LAPORAN
        |--------------------------------------------------------------------------
        */

        if ($reportType === 'sales') {

            $reportTitle = 'Laporan Penjualan';

        } else {

            $reportTitle = 'Laporan Pembelian';
        }

        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'reports.index',
            compact(
                'reportType',
                'period',
                'selectedDate',
                'selectedMonth',
                'selectedYear',
                'periodTitle',
                'periodDescription',
                'reportTitle',

                'totalSalesTransactions',
                'totalSales',
                'totalProductsSold',

                'totalPurchaseTransactions',
                'totalPurchases',
                'totalProductsPurchased',

                'bestSalesProducts',
                'bestPurchaseProducts',
                'topSuppliers',

                'sales',
                'purchases',

                'chartLabels',
                'chartSalesValues',
                'chartPurchaseValues'
            )
        );
    }


    /**
     * Export laporan ke Excel.
     */
    public function exportExcel(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | FILTER
        |--------------------------------------------------------------------------
        */

        $reportType = $request->get(
            'report_type',
            'sales'
        );

        if (!in_array(
            $reportType,
            ['sales', 'purchases']
        )) {
            $reportType = 'sales';
        }


        $period = $request->get(
            'period',
            'monthly'
        );

        if (!in_array(
            $period,
            ['daily', 'monthly', 'yearly']
        )) {
            $period = 'monthly';
        }


        $selectedDate = $request->get(
            'date',
            Carbon::today()->format('Y-m-d')
        );


        $selectedMonth = $request->get(
            'month',
            Carbon::today()->format('Y-m')
        );


        $selectedYear = (int) $request->get(
            'year',
            Carbon::today()->year
        );


        /*
        |--------------------------------------------------------------------------
        | VALIDASI TANGGAL EXPORT
        |--------------------------------------------------------------------------
        */

        try {

            Carbon::createFromFormat(
                'Y-m-d',
                $selectedDate
            );

        } catch (\Exception $e) {

            $selectedDate =
                Carbon::today()->format('Y-m-d');
        }


        try {

            Carbon::createFromFormat(
                'Y-m',
                $selectedMonth
            );

        } catch (\Exception $e) {

            $selectedMonth =
                Carbon::today()->format('Y-m');
        }


        /*
        |--------------------------------------------------------------------------
        | NAMA FILE
        |--------------------------------------------------------------------------
        */

        if ($reportType === 'sales') {

            $prefix = 'Laporan-Penjualan';

        } else {

            $prefix = 'Laporan-Pembelian';
        }


        if ($period === 'daily') {

            $suffix = Carbon::parse(
                $selectedDate
            )->format('d-m-Y');

        } elseif ($period === 'monthly') {

            $suffix = Carbon::parse(
                $selectedMonth . '-01'
            )->translatedFormat('F-Y');

        } else {

            $suffix = $selectedYear;
        }


        $filename =
            $prefix
            . '-'
            . $suffix
            . '.xlsx';


        /*
        |--------------------------------------------------------------------------
        | DOWNLOAD EXCEL
        |--------------------------------------------------------------------------
        */

        return Excel::download(
            new ReportExport(
                $reportType,
                $period,
                $selectedDate,
                $selectedMonth,
                $selectedYear
            ),
            $filename
        );
    }
    /**
 * Export laporan ke PDF.
 */
public function pdf(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | FILTER UTAMA
    |--------------------------------------------------------------------------
    */

    $reportType = $request->get('report_type', 'sales');

    if (!in_array($reportType, ['sales', 'purchases'])) {
        $reportType = 'sales';
    }

    $period = $request->get('period', 'monthly');

    if (!in_array($period, ['daily', 'monthly', 'yearly'])) {
        $period = 'monthly';
    }

    $selectedDate = $request->get(
        'date',
        Carbon::today()->format('Y-m-d')
    );

    $selectedMonth = $request->get(
        'month',
        Carbon::today()->format('Y-m')
    );

    $selectedYear = $request->get(
        'year',
        Carbon::today()->year
    );


    /*
    |--------------------------------------------------------------------------
    | VALIDASI TANGGAL
    |--------------------------------------------------------------------------
    */

    try {

        $dateObject = Carbon::createFromFormat(
            'Y-m-d',
            $selectedDate
        );

    } catch (\Exception $e) {

        $selectedDate = Carbon::today()->format('Y-m-d');

        $dateObject = Carbon::today();
    }


    try {

        $monthDate = Carbon::createFromFormat(
            'Y-m',
            $selectedMonth
        );

    } catch (\Exception $e) {

        $selectedMonth = Carbon::today()->format('Y-m');

        $monthDate = Carbon::today();
    }


    $selectedYear = (int) $selectedYear;


    /*
    |--------------------------------------------------------------------------
    | QUERY PENJUALAN
    |--------------------------------------------------------------------------
    */

    $salesQuery = Sale::validForTotals();

    if ($period === 'daily') {

        $salesQuery->whereDate(
            'sale_date',
            $selectedDate
        );

    } elseif ($period === 'monthly') {

        $salesQuery
            ->whereYear(
                'sale_date',
                $monthDate->year
            )
            ->whereMonth(
                'sale_date',
                $monthDate->month
            );

    } else {

        $salesQuery->whereYear(
            'sale_date',
            $selectedYear
        );
    }


    /*
    |--------------------------------------------------------------------------
    | QUERY PEMBELIAN
    |--------------------------------------------------------------------------
    */

    $purchasesQuery = Purchase::query();

    if ($period === 'daily') {

        $purchasesQuery->whereDate(
            'purchase_date',
            $selectedDate
        );

    } elseif ($period === 'monthly') {

        $purchasesQuery
            ->whereYear(
                'purchase_date',
                $monthDate->year
            )
            ->whereMonth(
                'purchase_date',
                $monthDate->month
            );

    } else {

        $purchasesQuery->whereYear(
            'purchase_date',
            $selectedYear
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STATISTIK PENJUALAN
    |--------------------------------------------------------------------------
    */

    $totalSalesTransactions =
        (clone $salesQuery)->count();

    $totalSales =
        (clone $salesQuery)->sum('total');

    $saleIds =
        (clone $salesQuery)->pluck('id');


    $totalProductsSold = 0;

    if ($saleIds->count() > 0) {

        $totalProductsSold = DB::table('sale_details')->whereIn('sale_details.sale_id', \App\Models\Sale::validForTotals()->select('id'))->where('sale_details.business_id', app(\App\Tenancy\TenantContext::class)->id())
            ->whereIn(
                'sale_id',
                $saleIds
            )
            ->sum('quantity');
    }


    /*
    |--------------------------------------------------------------------------
    | STATISTIK PEMBELIAN
    |--------------------------------------------------------------------------
    */

    $totalPurchaseTransactions =
        (clone $purchasesQuery)->count();

    $totalPurchases =
        (clone $purchasesQuery)->sum('total');

    $purchaseIds =
        (clone $purchasesQuery)->pluck('id');


    $totalProductsPurchased = 0;

    if ($purchaseIds->count() > 0) {

        $totalProductsPurchased = DB::table('purchase_details')->whereIn('purchase_details.purchase_id', \App\Models\Purchase::select('id'))->where('purchase_details.business_id', app(\App\Tenancy\TenantContext::class)->id())
            ->whereIn(
                'purchase_id',
                $purchaseIds
            )
            ->sum('quantity');
    }


    /*
    |--------------------------------------------------------------------------
    | PRODUK TERLARIS
    |--------------------------------------------------------------------------
    */

    $bestSalesProducts = collect();

    if ($saleIds->count() > 0) {

        $bestSalesProducts = DB::table('sale_details')->whereIn('sale_details.sale_id', \App\Models\Sale::validForTotals()->select('id'))->where('sale_details.business_id', app(\App\Tenancy\TenantContext::class)->id())
            ->join(
                'products',
                'products.id',
                '=',
                'sale_details.product_id'
            )
            ->whereIn(
                'sale_details.sale_id',
                $saleIds
            )
            ->select(
                'products.id',
                'products.name',
                DB::raw(
                    'SUM(sale_details.quantity) as total_quantity'
                ),
                DB::raw(
                    'SUM(sale_details.subtotal) as total_sales'
                )
            )
            ->groupBy(
                'products.id',
                'products.name'
            )
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();
    }


    /*
    |--------------------------------------------------------------------------
    | PRODUK PALING BANYAK DIBELI
    |--------------------------------------------------------------------------
    */

    $bestPurchaseProducts = collect();

    if ($purchaseIds->count() > 0) {

        $bestPurchaseProducts = DB::table('purchase_details')->whereIn('purchase_details.purchase_id', \App\Models\Purchase::select('id'))->where('purchase_details.business_id', app(\App\Tenancy\TenantContext::class)->id())
            ->join(
                'products',
                'products.id',
                '=',
                'purchase_details.product_id'
            )
            ->whereIn(
                'purchase_details.purchase_id',
                $purchaseIds
            )
            ->select(
                'products.id',
                'products.name',
                DB::raw(
                    'SUM(purchase_details.quantity) as total_quantity'
                ),
                DB::raw(
                    'SUM(purchase_details.subtotal) as total_purchases'
                )
            )
            ->groupBy(
                'products.id',
                'products.name'
            )
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();
    }


    /*
    |--------------------------------------------------------------------------
    | SUPPLIER TERBESAR
    |--------------------------------------------------------------------------
    */

    $topSuppliers = collect();

    if ($purchaseIds->count() > 0) {

        $topSuppliers = DB::table('purchases')->whereIn('purchases.id', \App\Models\Purchase::select('id'))->where('purchases.business_id', app(\App\Tenancy\TenantContext::class)->id())
            ->join(
                'suppliers',
                'suppliers.id',
                '=',
                'purchases.supplier_id'
            )
            ->whereIn(
                'purchases.id',
                $purchaseIds
            )
            ->select(
                'suppliers.id',
                'suppliers.name',
                DB::raw(
                    'COUNT(purchases.id) as total_transactions'
                ),
                DB::raw(
                    'SUM(purchases.total) as total_purchases'
                )
            )
            ->groupBy(
                'suppliers.id',
                'suppliers.name'
            )
            ->orderByDesc('total_purchases')
            ->limit(10)
            ->get();
    }


    /*
    |--------------------------------------------------------------------------
    | DAFTAR TRANSAKSI
    |--------------------------------------------------------------------------
    */

    $sales = (clone $salesQuery)
        ->with('saleDetails.product')
        ->latest('sale_date')
        ->latest('id')
        ->get();


    $purchases = (clone $purchasesQuery)
        ->with([
            'supplier',
            'details.product'
        ])
        ->latest('purchase_date')
        ->latest('id')
        ->get();


    /*
    |--------------------------------------------------------------------------
    | JUDUL PERIODE
    |--------------------------------------------------------------------------
    */

    if ($period === 'daily') {

        $periodTitle = 'Harian';

        $periodDescription =
            $dateObject->translatedFormat('d F Y');

    } elseif ($period === 'monthly') {

        $periodTitle = 'Bulanan';

        $periodDescription =
            $monthDate->translatedFormat('F Y');

    } else {

        $periodTitle = 'Tahunan';

        $periodDescription =
            'Tahun ' . $selectedYear;
    }


    /*
    |--------------------------------------------------------------------------
    | JUDUL LAPORAN
    |--------------------------------------------------------------------------
    */

    if ($reportType === 'sales') {

        $reportTitle = 'Laporan Penjualan';

    } else {

        $reportTitle = 'Laporan Pembelian';
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE PDF
    |--------------------------------------------------------------------------
    */

    $pdf = Pdf::loadView(
        'reports.pdf',
        compact(
            'reportType',
            'period',
            'selectedDate',
            'selectedMonth',
            'selectedYear',

            'periodTitle',
            'periodDescription',
            'reportTitle',

            'totalSalesTransactions',
            'totalSales',
            'totalProductsSold',

            'totalPurchaseTransactions',
            'totalPurchases',
            'totalProductsPurchased',

            'bestSalesProducts',
            'bestPurchaseProducts',
            'topSuppliers',

            'sales',
            'purchases'
        )
    );


    /*
    |--------------------------------------------------------------------------
    | UKURAN & ORIENTASI
    |--------------------------------------------------------------------------
    */

    $pdf->setPaper(
        'A4',
        'portrait'
    );


    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD
    |--------------------------------------------------------------------------
    */

    $fileName =
        'laporan-' .
        $reportType .
        '-' .
        now()->format('Ymd-His') .
        '.pdf';


    return $pdf->download($fileName);
}
}