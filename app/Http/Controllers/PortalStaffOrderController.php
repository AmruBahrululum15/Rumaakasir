<?php
namespace App\Http\Controllers;
use App\Models\{Sale,Product};
use App\Services\CustomerPortal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PortalStaffOrderController extends Controller
{
    public function index(Request $r)
    {
        abort_unless($r->user()->can('sales.view'),403);app(CustomerPortal::class)->requireActive($r->user()->business);
        $filters = $r->validate([
            'period' => 'sometimes|in:today,week,month,year',
            'status' => 'sometimes|in:all,unfinished,completed,cancelled',
            'search' => 'nullable|string|max:100',
        ]);
        $period = $filters['period'] ?? 'today';
        $status = $filters['status'] ?? 'all';
        $search = trim($filters['search'] ?? '');
        $now = now();
        [$start, $end] = match ($period) {
            'week' => [$now->copy()->startOfWeek(), $now->copy()->startOfWeek()->addWeek()],
            'month' => [$now->copy()->startOfMonth(), $now->copy()->startOfMonth()->addMonth()],
            'year' => [$now->copy()->startOfYear(), $now->copy()->startOfYear()->addYear()],
            default => [$now->copy()->startOfDay(), $now->copy()->startOfDay()->addDay()],
        };
        $base = Sale::where('business_id', $r->user()->business_id)->where('order_source', 'customer_portal');
        $inPeriod = (clone $base)->where('created_at', '>=', $start)->where('created_at', '<', $end);
        $unfinished = fn($q) => $q->whereNull('order_status')->orWhereIn('order_status', ['pending', 'confirmed', 'processing']);
        $today = (clone $base)->where('created_at', '>=', $now->copy()->startOfDay())->where('created_at', '<', $now->copy()->startOfDay()->addDay());
        // Daily totals remain daily; status counters describe the selected period before search.
        $summary = [
            'today' => (clone $today)->count(),
            'unfinished' => (clone $inPeriod)->where($unfinished)->count(),
            'completed' => (clone $inPeriod)->where('order_status', 'completed')->count(),
            'sales_today' => (clone $today)->where('payment_status', 'PAID')->sum('total'),
        ];
        $orders = $inPeriod
            ->when($status === 'unfinished', fn($q) => $q->where($unfinished))
            ->when($status === 'completed', fn($q) => $q->where('order_status', 'completed'))
            ->when($status === 'cancelled', fn($q) => $q->where('order_status', 'cancelled'))
            ->when($search !== '', fn($q) => $q->where(function ($query) use ($search) {
                $query->where('code', 'like', '%'.$search.'%')
                    ->orWhereHas('customer', fn($customer) => $customer->where('name', 'like', '%'.$search.'%'));
            }))
            ->with(['customer', 'saleDetails.product'])
            ->orderByRaw("CASE WHEN order_status IS NULL OR order_status IN ('pending', 'confirmed', 'processing') THEN 0 WHEN order_status = 'completed' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')->orderByDesc('id')->paginate(20)->withQueryString();
        return view('portal.staff-orders', compact('orders', 'summary', 'period', 'status', 'search'));
    }
    public function show(Request $r,int $order){
        abort_unless($r->user()->can('sales.view'),403);
        $order=Sale::where('business_id',$r->user()->business_id)->where('order_source','customer_portal')->with(['customer','saleDetails.product'])->findOrFail($order);
        $payment=\App\Models\CustomerOrderPayment::where('business_id',$order->business_id)->where('sale_id',$order->id)->first();
        return view('portal.staff-order',compact('order','payment'));
    }
    public function update(Request $r,int $order)
    {
        abort_unless($r->user()->can('sales.create'),403);
        abort_unless($r->user()->business->business_type!==null,403);
        $d=$r->validate(['action'=>'required|in:paid,cancelled,confirmed,processing,completed']);
        DB::transaction(function()use($r,$order,$d){
            $sale=Sale::where('business_id',$r->user()->business_id)->where('order_source','customer_portal')->lockForUpdate()->findOrFail($order);
            $payment=\App\Models\CustomerOrderPayment::where('business_id',$sale->business_id)->where('sale_id',$sale->id)->first();
            $status=$sale->order_status??'pending';
            abort_if(in_array($sale->payment_status,['CANCELLED','EXPIRED','DENIED','FAILED','VOIDED','REFUNDED'],true),422,'Pesanan sudah ditutup.');
            abort_if($status==='cancelled',422,'Pesanan sudah dibatalkan.');
            if($d['action']==='paid'){
                abort_unless(in_array($sale->payment_status,['CASH_PENDING','PENDING'],true) && ($sale->payment_method==='cash'||$payment?->provider==='manual_qris'),422,'Pembayaran gateway hanya dikonfirmasi oleh webhook.');
                $sale->update(['paid'=>$sale->total,'payment_status'=>'PAID']);
                DB::table('business_customers')->where('business_id',$sale->business_id)->where('customer_account_id',$sale->customer_account_id)->update(['last_transaction_at'=>now()]);
            }elseif($d['action']==='cancelled'){
                abort_unless(in_array($sale->payment_status,['CASH_PENDING','PENDING'],true),422,'Pembayaran sudah diproses.');
                abort_if($payment?->provider==='midtrans' && $payment->gateway_state!=='new',422,'Pembayaran gateway sudah dimulai. Tunggu status batal atau kedaluwarsa dari gateway.');
                foreach($sale->saleDetails as $detail)Product::where('business_id',$sale->business_id)->whereKey($detail->product_id)->increment('stock',$detail->quantity);
                $sale->update(['payment_status'=>'CANCELLED','order_status'=>'cancelled','notes'=>$sale->notes."\nSTOCK_RESTORED=1"]);
            }else{
                $next=['pending'=>'confirmed','confirmed'=>'processing','processing'=>'completed'];
                abort_unless(($next[$status]??null)===$d['action'],422,'Urutan status pesanan tidak valid.');
                if($d['action']==='completed')abort_unless($sale->payment_status==='PAID',422,'Konfirmasi pembayaran sebelum menyelesaikan pesanan.');
                $sale->update(['order_status'=>$d['action']]);
            }
            \App\Models\BusinessAuditEntry::create(['user_id'=>$r->user()->id,'action'=>'portal_order.'.$d['action'],'entity'=>'sale','entity_id'=>$sale->id,'metadata'=>['status'=>$sale->payment_status],'created_at'=>now()]);
        });return back()->with('success','Pesanan diperbarui.');
    }
}
