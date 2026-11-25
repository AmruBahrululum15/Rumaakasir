<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/** Read-only analytics. Every join includes its own tenant constraint. */
class PremiumAnalytics
{
    private function stockLevels(Business $business):Builder
    {
        $q=DB::table('products')->where('products.business_id',$business->id)->select('products.id','products.name');
        if($id=app(\App\Tenancy\OutletContext::class)->id()) return $q->leftJoin('outlet_stocks',fn($j)=>$j->on('outlet_stocks.product_id','=','products.id')->where('outlet_stocks.business_id',$business->id)->where('outlet_stocks.outlet_id',$id))->selectRaw('COALESCE(outlet_stocks.quantity,0) AS stock');
        return $q->addSelect('products.stock');
    }
    public function purchases(Business $business, string $start, string $end): Builder
    {
        return DB::table('purchases')->when(app(\App\Tenancy\OutletContext::class)->id(),fn($q,$id)=>$q->where('purchases.outlet_id',$id))->where('purchases.business_id',$business->id)
            ->whereBetween('purchase_date',[$start,$end.' 23:59:59'])
            ->leftJoin('suppliers',fn ($join)=>$join->on('suppliers.id','=','purchases.supplier_id')->where('suppliers.business_id',$business->id));
    }
    /** Recorded purchase receipts and paid sales, not an invented historical stock balance. */
    public function stock(Business $business, string $start, string $end): Builder
    {
        $incoming=$this->purchases($business,$start,$end)
            ->join('purchase_details',fn ($j)=>$j->on('purchase_details.purchase_id','=','purchases.id')->where('purchase_details.business_id',$business->id))
            ->select('purchase_details.product_id')->selectRaw('SUM(purchase_details.quantity) AS quantity')->groupBy('purchase_details.product_id');
        $outgoing=$this->details($business,$start,$end)->select('products.id')->selectRaw('SUM(sale_details.quantity) AS quantity')->groupBy('products.id');
        return $this->stockLevels($business)
            ->leftJoinSub($incoming,'incoming',fn ($j)=>$j->on('incoming.product_id','=','products.id'))
            ->leftJoinSub($outgoing,'outgoing',fn ($j)=>$j->on('outgoing.id','=','products.id'))
            ->selectRaw('COALESCE(incoming.quantity,0) AS incoming, COALESCE(outgoing.quantity,0) AS outgoing')->orderBy('products.id');
    }
    public function sales(Business $business, string $start, string $end): Builder
    {
        return DB::table('sales')->when(app(\App\Tenancy\OutletContext::class)->id(),fn($q,$id)=>$q->where('sales.outlet_id',$id))->where('sales.business_id', $business->id)
            ->whereBetween('sales.sale_date', [$start, $end.' 23:59:59'])->whereRaw('UPPER(sales.payment_status) = ?', ['PAID']);
    }
    private function details(Business $business, string $start, string $end): Builder
    {
        return $this->sales($business, $start, $end)
            ->join('sale_details', fn ($join) => $join->on('sale_details.sale_id','=','sales.id')->where('sale_details.business_id',$business->id))
            ->join('products', fn ($join) => $join->on('products.id','=','sale_details.product_id')->where('products.business_id',$business->id));
    }
    public function report(Business $business, string $start, string $end): array
    {
        $sales = $this->sales($business, $start, $end);
        $count = (clone $sales)->count();
        $revenue = (float) (clone $sales)->sum('sales.total');
        $products = $this->details($business,$start,$end)->select('products.id','products.name')
            ->selectRaw('SUM(sale_details.quantity) AS quantity, SUM(sale_details.subtotal) AS revenue')->groupBy('products.id','products.name');
        $customers = (clone $sales)->leftJoin('customers', fn ($join) => $join->on('customers.id','=','sales.customer_id')->where('customers.business_id',$business->id))
            ->select('customers.id','customers.name')->selectRaw('COUNT(sales.id) AS transactions, SUM(sales.total) AS revenue')->groupBy('customers.id','customers.name');
        $cashiers = (clone $sales)->leftJoin('users', fn ($join) => $join->on('users.id','=','sales.user_id')->where('users.business_id',$business->id))
            ->select('users.id','users.name')->selectRaw('COUNT(sales.id) AS transactions, SUM(sales.total) AS revenue')->groupBy('users.id','users.name');
        $categories = $this->details($business,$start,$end)->leftJoin('categories',fn ($join) => $join->on('categories.id','=','products.category_id')->where('categories.business_id',$business->id))
            ->select('categories.id','categories.name')->selectRaw('SUM(sale_details.quantity) AS quantity, SUM(sale_details.subtotal) AS revenue')->groupBy('categories.id','categories.name');
        $sold = $this->details($business,$start,$end)->select('products.id')->selectRaw('SUM(sale_details.quantity) AS quantity')->groupBy('products.id');
        $slow = DB::table('products')->where('products.business_id',$business->id)->leftJoinSub($sold,'sold',fn ($join)=>$join->on('sold.id','=','products.id'))
            ->select('products.id','products.name')->selectRaw('COALESCE(sold.quantity, 0) AS quantity')->orderBy('quantity')->orderBy('products.id')->limit(10)->get();
        $hour = DB::connection()->getDriverName() === 'sqlite' ? "strftime('%H', sales.created_at)" : 'HOUR(sales.created_at)';
        $suppliers = collect();
        if (app(BusinessModules::class)->permitsType($business,'purchases')) {
            $suppliers = DB::table('purchases')->when(app(\App\Tenancy\OutletContext::class)->id(),fn($q,$id)=>$q->where('purchases.outlet_id',$id))->where('purchases.business_id',$business->id)->whereBetween('purchases.purchase_date',[$start,$end.' 23:59:59'])
                ->leftJoin('suppliers',fn ($join) => $join->on('suppliers.id','=','purchases.supplier_id')->where('suppliers.business_id',$business->id))
                ->select('suppliers.id','suppliers.name')->selectRaw('COUNT(purchases.id) AS transactions, SUM(purchases.total) AS revenue')
                ->groupBy('suppliers.id','suppliers.name')->orderByDesc('revenue')->limit(50)->get();
        }
        return [
            'purchases'=>app(BusinessModules::class)->permitsType($business,'purchases') ? $this->purchases($business,$start,$end)->orderByDesc('purchase_date')->limit(100)->get(['purchases.code','purchase_date','purchases.total','suppliers.name']) : collect(),
            'stock'=>$this->stock($business,$start,$end)->limit(100)->get(),
            'lowStock'=>DB::query()->fromSub($this->stockLevels($business),'levels')->where('stock','<=',5)->orderBy('stock')->orderBy('id')->limit(50)->get(),
            'recentPurchases'=>app(BusinessModules::class)->permitsType($business,'purchases')
                ? DB::table('purchases')->when(app(\App\Tenancy\OutletContext::class)->id(),fn($q,$id)=>$q->where('purchases.outlet_id',$id))->where('purchases.business_id',$business->id)->whereBetween('purchase_date',[$start,$end.' 23:59:59'])
                    ->leftJoin('suppliers',fn ($join)=>$join->on('suppliers.id','=','purchases.supplier_id')->where('suppliers.business_id',$business->id))
                    ->orderByDesc('purchase_date')->orderByDesc('purchases.id')->limit(10)->get(['purchases.code','purchases.purchase_date','purchases.total','suppliers.name']) : collect(),
            'count'=>$count, 'revenue'=>$revenue, 'average'=>$count ? $revenue/$count : 0,
            'today'=>(float)$this->sales($business,now()->toDateString(),now()->toDateString())->sum('sales.total'),
            'month'=>(float)$this->sales($business,now()->startOfMonth()->toDateString(),now()->toDateString())->sum('sales.total'),
            'activeCustomers'=>(clone $sales)->join('customers', fn ($join) => $join->on('customers.id','=','sales.customer_id')->where('customers.business_id',$business->id))->distinct()->count('customers.id'),
            'products'=>(clone $products)->orderByDesc('quantity')->limit(50)->get(), 'slow'=>$slow,
            'customers'=>(clone $customers)->orderByDesc('revenue')->limit(50)->get(),
            'cashiers'=>$cashiers->orderByDesc('revenue')->limit(50)->get(),
            'categories'=>$categories->orderByDesc('revenue')->limit(50)->get(), 'suppliers'=>$suppliers,
            'trend'=>(clone $sales)->selectRaw('DATE(sales.sale_date) AS day, SUM(sales.total) AS revenue, COUNT(*) AS transactions')->groupByRaw('DATE(sales.sale_date)')->orderBy('day')->get(),
            'hours'=>(clone $sales)->whereNotNull('sales.created_at')->selectRaw($hour.' AS hour, COUNT(*) AS transactions')->groupByRaw($hour)->orderByDesc('transactions')->get(),
        ];
    }
}
