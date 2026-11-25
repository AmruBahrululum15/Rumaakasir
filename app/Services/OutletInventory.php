<?php
namespace App\Services;
use App\Models\{Outlet,OutletStock,OutletStockTransfer,Product,Sale,User};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class OutletInventory {
    public function central(Product $product):int {return (int)$product->stock-(int)DB::table('outlet_stocks')->where('business_id',$product->business_id)->where('product_id',$product->id)->sum('quantity');}
    public function consume(Product $product,?int $outlet,int $quantity):void {
        if($outlet){$this->adjust($product,$outlet,-$quantity);return;}
        if($this->central($product)<$quantity)throw ValidationException::withMessages(['products'=>'Stok pusat tidak mencukupi. Stok yang dialokasikan ke outlet tidak dapat dijual dari pusat.']);
    }
    private function adjust(Product $product,int $outlet,int $delta):void {
        Outlet::where('business_id',$product->business_id)->findOrFail($outlet);
        $row=DB::table('outlet_stocks')->where('business_id',$product->business_id)->where('outlet_id',$outlet)->where('product_id',$product->id)->lockForUpdate()->first();
        $next=($row?->quantity ?? 0)+$delta;
        if($next<0)throw ValidationException::withMessages(['products'=>'Stok outlet tidak mencukupi untuk '.$product->name.'.']);
        if($row)DB::table('outlet_stocks')->where('id',$row->id)->update(['quantity'=>$next]);
        else DB::table('outlet_stocks')->insert(['business_id'=>$product->business_id,'outlet_id'=>$outlet,'product_id'=>$product->id,'quantity'=>$next]);
    }
    public function saleLine(\App\Models\SaleDetail $line):void {
        $sale=Sale::withoutGlobalScopes()->where('business_id',$line->business_id)->findOrFail($line->sale_id);
        if(!$sale->outlet_id)return;
        $product=Product::withoutGlobalScopes()->where('business_id',$line->business_id)->lockForUpdate()->findOrFail($line->product_id);
        $this->consume($product,$sale->outlet_id,$line->quantity);$this->movement($product,$sale->outlet_id,-$line->quantity,'sale',$sale->id);
    }
    public function purchaseLine(\App\Models\PurchaseDetail $line):void {
        $purchase=\App\Models\Purchase::withoutGlobalScopes()->where('business_id',$line->business_id)->findOrFail($line->purchase_id);
        if(!$purchase->outlet_id)return;
        $product=Product::withoutGlobalScopes()->where('business_id',$line->business_id)->lockForUpdate()->findOrFail($line->product_id);
        $this->adjust($product,$purchase->outlet_id,$line->quantity);$this->movement($product,$purchase->outlet_id,$line->quantity,'purchase',$purchase->id);
    }
    public function restoreAllocation(Sale $sale):void {
        if(!$sale->outlet_id)return;
        $q=DB::table('outlet_stock_movements')->where('business_id',$sale->business_id)->where('source_type','sale')->where('source_id',$sale->id);
        foreach($q->get() as $movement){
            if(DB::table('outlet_stock_movements')->where('business_id',$sale->business_id)->where('source_type','sale_return')->where('source_id',$sale->id)->where('product_id',$movement->product_id)->exists())continue;
            $product=Product::withoutGlobalScopes()->where('business_id',$sale->business_id)->lockForUpdate()->findOrFail($movement->product_id);
            $this->adjust($product,$sale->outlet_id,-$movement->quantity);$this->movement($product,$sale->outlet_id,-$movement->quantity,'sale_return',$sale->id);
        }
    }
    public function transfer(User $user,array $data):void {
        app(PackageAccess::class)->ensureFeature('inter_outlet_stock_transfer',$user->business);
        DB::transaction(function()use($user,$data){
            $product=Product::where('business_id',$user->business_id)->lockForUpdate()->findOrFail($data['product_id']);
            $from=isset($data['from_outlet_id'])?(int)$data['from_outlet_id']:null;$to=isset($data['to_outlet_id'])?(int)$data['to_outlet_id']:null;
            if($from===$to)throw ValidationException::withMessages(['to_outlet_id'=>'Asal dan tujuan harus berbeda.']);
            foreach(array_filter([$from,$to]) as $id)Outlet::where('is_active',true)->where('business_id',$user->business_id)->findOrFail($id);
            $old=OutletStockTransfer::where('request_key',$data['request_key'])->first();
            if($old){abort_unless((int)$old->product_id===$product->id && $old->from_outlet_id==$from && $old->to_outlet_id==$to && (int)$old->quantity===(int)$data['quantity'],409);return;}
            $this->consume($product,$from,(int)$data['quantity']);if($to)$this->adjust($product,$to,(int)$data['quantity']);
            $transfer=OutletStockTransfer::create(['from_outlet_id'=>$from,'to_outlet_id'=>$to,'product_id'=>$product->id,'quantity'=>$data['quantity'],'request_key'=>$data['request_key'],'user_id'=>$user->id,'created_at'=>now()]);
            $this->movement($product,$from,-$data['quantity'],'transfer_out',$transfer->id);$this->movement($product,$to,$data['quantity'],'transfer_in',$transfer->id);
        });
    }
    private function movement(Product $product,?int $outlet,int $quantity,string $type,int $id):void {
        $query=DB::table('outlet_stock_movements')->where('business_id',$product->business_id)->where('outlet_id',$outlet)->where('product_id',$product->id)->where('source_type',$type)->where('source_id',$id);
        if($query->exists()) $query->increment('quantity',$quantity);
        else DB::table('outlet_stock_movements')->insert(['business_id'=>$product->business_id,'outlet_id'=>$outlet,'product_id'=>$product->id,'quantity'=>$quantity,'source_type'=>$type,'source_id'=>$id,'created_at'=>now()]);
    }
}
