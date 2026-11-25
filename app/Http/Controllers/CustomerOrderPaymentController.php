<?php
namespace App\Http\Controllers;
use App\Models\{Business,CustomerOrderPayment,Sale,Product};
use App\Services\{CustomerOrderGateway,CustomerPortal};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Storage};
class CustomerOrderPaymentController extends Controller {
    private function own(int $order):Sale {return Sale::withoutGlobalScopes()->where('customer_account_id',auth('customer')->id())->where('order_source','customer_portal')->findOrFail($order);}
    public function show(int $order){
        $sale=$this->own($order);$business=$sale->business;
        return app(CustomerPortal::class)->within($business,function()use($sale,$business){
            $sale->load('saleDetails.product');$payment=CustomerOrderPayment::where('business_id',$sale->business_id)->where('sale_id',$sale->id)->first();
            return response()->view('portal.order',compact('sale','business','payment'))->header('Cache-Control','private, no-store');
        });
    }
    public function pay(int $order,CustomerOrderGateway $gateway){$sale=$this->own($order);$gateway->start($sale);return redirect()->route('customer.orders.show',$sale->id);}
    public function qris(int $order){
        $sale=$this->own($order);$payment=CustomerOrderPayment::where('sale_id',$sale->id)->where('business_id',$sale->business_id)->where('provider','manual_qris')->firstOrFail();
        abort_unless($payment->qris_path&&Storage::disk('local')->exists($payment->qris_path),404);
        return response()->file(Storage::disk('local')->path($payment->qris_path),['Cache-Control'=>'private, no-store'])->setPrivate();
    }
    public function webhook(Request $r,Business $business,CustomerOrderGateway $gateway){
        $d=$r->validate(['order_id'=>'required|string|max:100','status_code'=>'required|string|max:10','gross_amount'=>'required|numeric','signature_key'=>'required|string|size:128']);
        $sale=Sale::withoutGlobalScopes()->where('business_id',$business->id)->where('order_source','customer_portal')->where('code',$d['order_id'])->firstOrFail();
        $payment=CustomerOrderPayment::where('business_id',$business->id)->where('sale_id',$sale->id)->where('provider','midtrans')->firstOrFail();
        $status=$gateway->verifiedStatus($payment,$sale,$d);
        app(CustomerPortal::class)->within($business,fn()=>DB::transaction(function()use($sale,$status,$payment){
            $sale=Sale::whereKey($sale->id)->lockForUpdate()->firstOrFail();
            if(in_array($sale->payment_status,['PAID','CANCELLED','EXPIRED','DENIED','FAILED'],true))return;
            $instructions=array_intersect_key($status,array_flip(['va_numbers','permata_va_number','bill_key','biller_code','expiry_time','payment_type']));
            $payment->update(['instructions'=>$instructions]);
            if(($status['transaction_status']??null)==='settlement' && in_array($status['fraud_status']??'accept',['accept'],true)){
                $sale->update(['paid'=>$sale->total,'payment_status'=>'PAID']);
            }elseif(in_array($status['transaction_status']??null,['expire','cancel','deny'],true)){
                foreach($sale->saleDetails as $detail)Product::where('business_id',$sale->business_id)->whereKey($detail->product_id)->increment('stock',$detail->quantity);
                $sale->update(['payment_status'=>['expire'=>'EXPIRED','cancel'=>'CANCELLED','deny'=>'DENIED'][$status['transaction_status']],
                    'order_status'=>'cancelled','notes'=>$sale->notes."\nSTOCK_RESTORED=1"]);
            }
        }));
        return response()->json(['received'=>true]);
    }
}
