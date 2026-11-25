<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Sale;
use App\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Dashboard utama POS Enterprise
     */
    public function index(Request $request)
    {
        $businessId = app(TenantContext::class)->id();
        /*
        |--------------------------------------------------------------------------
        | STATISTIK MASTER DATA
        |--------------------------------------------------------------------------
        */

        $totalProducts = Product::count();

        $totalCategories = Category::count();

        $totalSuppliers = Supplier::count();


        /*
        |--------------------------------------------------------------------------
        | STATISTIK PENJUALAN
        |--------------------------------------------------------------------------
        */

        $totalTransactions = Sale::validForTotals()->count();

        $totalRevenue = Sale::validForTotals()->sum('total');


        /*
        |--------------------------------------------------------------------------
        | PRODUK TERJUAL
        |--------------------------------------------------------------------------
        */

        $totalProductsSold = DB::table('sale_details')->whereIn('sale_details.sale_id', \App\Models\Sale::validForTotals()->select('id'))
            ->where('business_id', $businessId)
            ->sum('quantity');


        /*
        |--------------------------------------------------------------------------
        | STOK
        |--------------------------------------------------------------------------
        */

        $availableStock = Product::where('stock', '>', 0)
            ->sum('stock');

        $lowStock = Product::where('stock', '>', 0)
            ->where('stock', '<=', 10)
            ->count();

        $outOfStock = Product::where('stock', '<=', 0)
            ->count();


        /*
        |--------------------------------------------------------------------------
        | PENJUALAN HARI INI
        |--------------------------------------------------------------------------
        */

        $todayTransactions = Sale::validForTotals()->whereDate(
            'sale_date',
            now()->toDateString()
        )->count();

        $todayRevenue = Sale::validForTotals()->whereDate(
            'sale_date',
            now()->toDateString()
        )->sum('total');


        /*
        |--------------------------------------------------------------------------
        | PENJUALAN BULAN INI
        |--------------------------------------------------------------------------
        */

        $monthlyRevenue = Sale::validForTotals()->whereYear(
            'sale_date',
            now()->year
        )
        ->whereMonth(
            'sale_date',
            now()->month
        )
        ->sum('total');


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI TERBARU
        |--------------------------------------------------------------------------
        */

        $recentSales = Sale::with(
            'saleDetails.product'
        )
        ->latest('id')
        ->take(8)
        ->get();


        /*
        |--------------------------------------------------------------------------
        | PRODUK TERLARIS
        |--------------------------------------------------------------------------
        */

        $bestProducts = DB::table('sale_details')->whereIn('sale_details.sale_id', \App\Models\Sale::validForTotals()->select('id'))
            ->join(
                'products',
                'products.id',
                '=',
                'sale_details.product_id'
            )
            ->where('sale_details.business_id', $businessId)
            ->select(
                'products.id',
                'products.name',
                DB::raw(
                    'SUM(sale_details.quantity) as total_sold'
                ),
                DB::raw(
                    'SUM(sale_details.subtotal) as total_revenue'
                )
            )
            ->groupBy(
                'products.id',
                'products.name'
            )
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | PRODUK STOK MENIPIS
        |--------------------------------------------------------------------------
        */

        $lowStockProducts = Product::where(
            'stock',
            '<=',
            10
        )
        ->orderBy('stock')
        ->take(8)
        ->get();


        /*
        |--------------------------------------------------------------------------
        | DATA GRAFIK PENJUALAN 7 HARI TERAKHIR
        |--------------------------------------------------------------------------
        */

        $salesChartLabels = [];

        $salesChartData = [];

        for ($i = 6; $i >= 0; $i--) {

            $date = now()->subDays($i);

            $salesChartLabels[] =
                $date->format('d M');

            $salesChartData[] =
                (float) Sale::validForTotals()->whereDate(
                    'sale_date',
                    $date->toDateString()
                )->sum('total');
        }


        /*
        |--------------------------------------------------------------------------
        | DATA GRAFIK 6 BULAN TERAKHIR
        |--------------------------------------------------------------------------
        */

        $monthlyChartLabels = [];

        $monthlyChartData = [];

        for ($i = 5; $i >= 0; $i--) {

            $date = now()->subMonths($i);

            $monthlyChartLabels[] =
                $date->translatedFormat('M Y');

            $monthlyChartData[] =
                (float) Sale::validForTotals()->whereYear(
                    'sale_date',
                    $date->year
                )
                ->whereMonth(
                    'sale_date',
                    $date->month
                )
                ->sum('total');
        }


        /*
        |--------------------------------------------------------------------------
        | PERSENTASE STOK
        |--------------------------------------------------------------------------
        */

        $totalStockProducts = Product::count();

        if ($totalStockProducts > 0) {

            $availableStockPercentage =
                round(
                    Product::where('stock', '>', 10)->count()
                    / $totalStockProducts
                    * 100
                );

            $lowStockPercentage =
                round(
                    Product::whereBetween(
                        'stock',
                        [1, 10]
                    )->count()
                    / $totalStockProducts
                    * 100
                );

            $outOfStockPercentage =
                round(
                    Product::where('stock', '<=', 0)->count()
                    / $totalStockProducts
                    * 100
                );

        } else {

            $availableStockPercentage = 0;

            $lowStockPercentage = 0;

            $outOfStockPercentage = 0;
        }


        /*
        |--------------------------------------------------------------------------
        | RETURN DASHBOARD
        |--------------------------------------------------------------------------
        */

        return view(
            'dashboard.index',
            compact(

                'totalProducts',

                'totalCategories',

                'totalSuppliers',

                'totalTransactions',

                'totalRevenue',

                'totalProductsSold',

                'availableStock',

                'lowStock',

                'outOfStock',

                'todayTransactions',

                'todayRevenue',

                'monthlyRevenue',

                'recentSales',

                'bestProducts',

                'lowStockProducts',

                'salesChartLabels',

                'salesChartData',

                'monthlyChartLabels',

                'monthlyChartData',

                'availableStockPercentage',

                'lowStockPercentage',

                'outOfStockPercentage'

            )
        );
    }
}
