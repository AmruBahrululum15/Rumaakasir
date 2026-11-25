<?php
namespace App\Services;
use App\Models\{Booking,BookingPayment,Business,Sale,User};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class ReservationPayments {
    public function checkout(User $actor,int $id,string $key,float $cash):Sale {
        app(PackageAccess::class)->ensureFeature('booking',$actor->business);
        abort_unless($actor->hasAnyRole(['Atasan','Admin','Kasir']) && $actor->can('sales.create'),403);
        return DB::transaction(function()use($actor,$id,$key,$cash){
            Business::whereKey($actor->business_id)->lockForUpdate()->firstOrFail();
            $b=Booking::where('business_id',$actor->business_id)->lockForUpdate()->findOrFail($id);
            if($b->sale_id)return Sale::where('business_id',$actor->business_id)->findOrFail($b->sale_id);
            $remaining=round(max(0,(float)$b->total-(float)$b->paid),2);
            if(!in_array($b->status,['confirmed','in_progress','completed']) || $b->starts_at->isFuture() || !is_finite($cash) || $cash<$remaining)throw ValidationException::withMessages(['cash_received'=>'Reservasi harus sudah dimulai dan dikonfirmasi. Cash harus mencukupi sisa tagihan.']);
            $deposit=(float)$b->paid;
            if($remaining>0)$this->cash($actor,$id,$key,$remaining);
            $change=round($cash-$remaining,2);
            // A service reservation has no stock-bearing product lines. Record its
            // total once in POS; previously received deposits are credited here.
            $sale=Sale::create(['code'=>'RSV-POS-'.\Illuminate\Support\Str::ulid(),'sale_date'=>now()->toDateString(),'customer_id'=>$b->customer_id,'user_id'=>$actor->id,'total'=>$b->total,'paid'=>(float)$b->total+$change,'change'=>$change,'payment_method'=>'cash','payment_status'=>'PAID','order_source'=>'reservation','order_status'=>'completed','notes'=>'Reservasi '.$b->code."\nDP sebelumnya: Rp ".number_format($deposit,0,',','.')."\nCash saat checkout: Rp ".number_format($cash,0,',','.')]);
            $b->refresh()->update(['sale_id'=>$sale->id,'status'=>'completed','paid'=>$b->total,'payment_status'=>'paid']);
            DB::afterCommit(fn()=>app(BookingNotifications::class)->send($id,'completed'));
            return $sale;
        });
    }
    public function cash(User $actor,int $id,string $key,float $amount):void {
        app(PackageAccess::class)->ensureFeature('booking',$actor->business);
        abort_unless($actor->hasAnyRole(['Atasan','Admin','Kasir']) && $actor->can('sales.create'),403);
        DB::transaction(function()use($actor,$id,$key,$amount){
            Business::whereKey($actor->business_id)->lockForUpdate()->firstOrFail();$booking=Booking::where('business_id',$actor->business_id)->lockForUpdate()->findOrFail($id);
            $existing=BookingPayment::withoutGlobalScopes()->where('request_key',$key)->first();
            if($existing){abort_unless((int)$existing->booking_id===$id && (int)$existing->business_id===(int)$actor->business_id && (float)$existing->amount===$amount,409);return;}
            if(in_array($booking->status,['cancelled','no_show']) || $booking->sale_id || $amount<=0 || round((float)$booking->paid+$amount,2)>(float)$booking->total)throw ValidationException::withMessages(['amount'=>'Pembayaran tidak valid atau melebihi sisa tagihan.']);
            BookingPayment::create(['booking_id'=>$id,'user_id'=>$actor->id,'request_key'=>$key,'amount'=>$amount]);
            $paid=round((float)$booking->paid+$amount,2);$booking->update(['paid'=>$paid,'payment_status'=>$paid>=(float)$booking->total?'paid':'partial']);
            if($booking->status==='awaiting_payment' && $paid>=(float)$booking->required_payment)$booking->update(['status'=>'confirmed']);
            DB::afterCommit(fn()=>app(BookingNotifications::class)->send($id,'payment_received'));
        });
    }
    public function link(User $actor,int $id,int $saleId):void {
        app(PackageAccess::class)->ensureFeature('booking',$actor->business);
        abort_unless($actor->hasAnyRole(['Atasan','Admin','Kasir']) && $actor->can('sales.create'),403);
        DB::transaction(function()use($actor,$id,$saleId){
            Business::whereKey($actor->business_id)->lockForUpdate()->firstOrFail();$b=Booking::where('business_id',$actor->business_id)->lockForUpdate()->findOrFail($id);
            $sale=Sale::where('business_id',$actor->business_id)->lockForUpdate()->findOrFail($saleId);
            if($b->sale_id){abort_unless((int)$b->sale_id===$saleId,409);return;}
            if($b->status!=='completed' || (int)$sale->customer_id!==(int)$b->customer_id || $sale->payment_status!=='PAID' || $sale->payment_method!=='cash' || (float)$sale->total<(float)$b->total || Booking::where('sale_id',$saleId)->exists())throw ValidationException::withMessages(['sale_id'=>'Pilih transaksi POS Cash lunas milik pelanggan ini, dengan total minimal nilai reservasi. Reservasi harus selesai.']);
            $b->update(['sale_id'=>$saleId]);
        });
    }
}
