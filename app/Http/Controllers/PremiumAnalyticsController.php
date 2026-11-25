<?php

namespace App\Http\Controllers;

use App\Services\{PackageAccess, PremiumAnalytics};
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PremiumAnalyticsController extends Controller
{
    private function period(Request $request): array
    {
        $data = $request->validate(['period'=>['sometimes','in:daily,weekly,monthly,custom'],
            'start'=>['required_if:period,custom','nullable','date_format:Y-m-d'],
            'end'=>['required_if:period,custom','nullable','date_format:Y-m-d','after_or_equal:start']]);
        $period=$data['period'] ?? 'monthly';
        $today=CarbonImmutable::today();
        [$start,$end]=match($period) {
            'daily'=>[$today,$today], 'weekly'=>[$today->startOfWeek(),$today],
            'custom'=>[CarbonImmutable::parse($data['start']),CarbonImmutable::parse($data['end'])],
            default=>[$today->startOfMonth(),$today],
        };
        if ($start->diffInDays($end)>=366) throw ValidationException::withMessages(['end'=>'Rentang laporan maksimal 366 hari.']);
        return [$period,$start->toDateString(),$end->toDateString()];
    }
    public function index(Request $request, PremiumAnalytics $analytics)
    {
        [$period,$start,$end]=$this->period($request);
        $business=app(PackageAccess::class)->currentBusiness();
        return view('premium.analytics', ['business'=>$business,'period'=>$period,'start'=>$start,'end'=>$end,
            'report'=>$analytics->report($business,$start,$end),'detailed'=>$request->routeIs('premium.reports')]);
    }
    public function csv(Request $request, PremiumAnalytics $analytics)
    {
        [$period,$start,$end]=$this->period($request);
        $business=app(PackageAccess::class)->currentBusiness();
        $kind=$request->validate(['report'=>['sometimes','in:sales,purchases,stock']])['report'] ?? 'sales';
        if ($kind==='purchases') abort_unless(app(\App\Services\BusinessModules::class)->permitsType($business,'purchases'),403);
        // Explicit scope survives middleware cleanup when the response is streamed.
        [$query,$headers,$fields]=match($kind) {
            'purchases'=>[$analytics->purchases($business,$start,$end)->select('purchases.code','purchase_date','purchases.total','suppliers.name')->orderBy('purchases.id'),['Nomor Pembelian','Tanggal','Total','Supplier'],['code','purchase_date','total','name']],
            'stock'=>[$analytics->stock($business,$start,$end),['Produk','Masuk Pembelian','Keluar Penjualan Dibayar','Stok Saat Ini'],['name','incoming','outgoing','stock']],
            default=>[$analytics->sales($business,$start,$end)->select('sales.code','sales.sale_date','sales.total','sales.payment_method')->orderBy('sales.id'),['Nomor Transaksi','Tanggal','Total Dibayar','Metode Pembayaran'],['code','sale_date','total','payment_method']],
        };
        return response()->streamDownload(function () use ($query,$headers,$fields) {
            $out=fopen('php://output','w');
            fwrite($out,"\xEF\xBB\xBF");
            fputcsv($out,$headers, ',', '"', '');
            foreach($query->cursor() as $row) {
                $values=array_map(fn ($field)=>$row->$field,$fields);
                $values=array_map(fn ($value) => is_string($value) && preg_match('/^[\s]*[=+@\-]/u',$value) ? "'".$value : $value, $values);
                fputcsv($out,$values, ',', '"', '');
            }
            fclose($out);
        }, $kind.'-'.$start.'-'.$end.'.csv', ['Content-Type'=>'text/csv; charset=UTF-8','Cache-Control'=>'private, no-store']);
    }
}
