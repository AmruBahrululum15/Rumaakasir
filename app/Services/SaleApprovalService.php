<?php
namespace App\Services;
use App\Models\{BusinessAuditEntry,Customer,Product,Sale,SaleApproval,User};
use App\Notifications\SaleApprovalRequested;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class SaleApprovalService {
    private function eligible(Sale $sale,string $type): void {
        if(strtolower($sale->payment_method)!=='cash') throw ValidationException::withMessages(['sale_id'=>'Refund gateway belum didukung. Gunakan proses resmi penyedia pembayaran; sistem tidak mencatat refund online palsu.']);
        $allowed=$type==='refund' ? ['PAID'] : ['CASH_PENDING','PENDING'];
        if(!in_array(strtoupper($sale->payment_status),$allowed,true)) throw ValidationException::withMessages(['sale_id'=>'Status transaksi tidak sesuai. Refund untuk transaksi tunai dibayar; void untuk tunai belum dibayar.']);
    }
    public function request(User $user,int $saleId,string $type,string $reason): SaleApproval {
        app(PackageAccess::class)->ensureFeature('approval_transactions',$user->business);
        return DB::transaction(function() use($user,$saleId,$type,$reason) {
            $sale=Sale::where('business_id',$user->business_id)->lockForUpdate()->findOrFail($saleId);$this->eligible($sale,$type);
            abort_if(SaleApproval::where('sale_id',$sale->id)->where('status','pending_approval')->exists(),409,'Transaksi sudah memiliki pengajuan pending.');
            $approval=SaleApproval::create(['sale_id'=>$sale->id,'requested_by'=>$user->id,'type'=>$type,'reason'=>$reason]);
            $this->audit($user,$approval,'requested');
            DB::afterCommit(function() use($user,$approval,$sale) {
                foreach(User::withoutGlobalScopes()->where('business_id',$user->business_id)->whereHas('roles',fn($q)=>$q->whereIn('name',['Atasan','Admin']))->get() as $owner) if($owner->can('sales.approve-refunds')) {
                    try {$owner->notify(new SaleApprovalRequested($approval->id,$sale->code));}
                    catch(\Throwable $e){\Illuminate\Support\Facades\Log::warning('Approval notification failed',['approval_id'=>$approval->id,'exception_type'=>get_class($e)]);}
                }
            });
            return $approval;
        });
    }
    public function review(User $user,int $id,bool $approve,string $note): void {
        app(PackageAccess::class)->ensureFeature('approval_transactions',$user->business);
        abort_unless($user->hasAnyRole(['Atasan','Admin']) && $user->can('sales.approve-refunds'),403);
        DB::transaction(function() use($user,$id,$approve,$note) {
            $request=SaleApproval::where('business_id',$user->business_id)->lockForUpdate()->findOrFail($id);
            abort_unless($request->status==='pending_approval',409,'Pengajuan sudah diproses.');
            $sale=Sale::where('business_id',$user->business_id)->lockForUpdate()->findOrFail($request->sale_id);
            if($approve) {
                $this->eligible($sale,$request->type);
                if($sale->customer_id) {
                    Customer::where('business_id',$user->business_id)->lockForUpdate()->findOrFail($sale->customer_id);
                    $ledger=DB::table('customer_loyalty_points')->where('business_id',$user->business_id)->where('source_id',$sale->id)->whereIn('source_type',['earned','redeemed']);
                    $net=(int)(clone $ledger)->sum('points');
                    if($net>app(LoyaltyService::class)->balance($user->business_id,$sale->customer_id)) throw ValidationException::withMessages(['decision'=>'Poin dari transaksi ini sudah digunakan. Saldo harus cukup untuk pembalikan poin.']);
                    if((clone $ledger)->exists()) DB::table('customer_loyalty_points')->insert(['business_id'=>$user->business_id,'customer_id'=>$sale->customer_id,'points'=>-$net,'source_type'=>'reversal','source_id'=>$sale->id,'description'=>'Pembalikan '.$sale->code,'created_at'=>now()]);
                }
                if(!preg_match('/^STOCK_RESTORED=1$/m',(string)$sale->notes)) {
                    foreach($sale->saleDetails()->orderBy('product_id')->get() as $line) {
                        $product=Product::where('business_id',$user->business_id)->lockForUpdate()->findOrFail($line->product_id);$product->increment('stock',$line->quantity);
                    }
                }
                $sale->update(['payment_status'=>$request->type==='refund'?'REFUNDED':'VOIDED','notes'=>rtrim((string)$sale->notes)."\nSTOCK_RESTORED=1\nAPPROVAL_ID=".$request->id]);
            }
            $request->update(['status'=>$approve?'approved':'rejected','approved_by'=>$user->id,'reviewed_at'=>now(),'review_note'=>$note]);
            $this->audit($user,$request,$approve?'approved':'rejected');
        });
    }
    private function audit(User $user,SaleApproval $request,string $action): void {
        BusinessAuditEntry::create(['user_id'=>$user->id,'action'=>'sale.'.$request->type.'.'.$action,'entity'=>'sale','entity_id'=>$request->sale_id,'metadata'=>['roles'=>$user->getRoleNames()->all(),'approval_id'=>$request->id,'reason'=>$request->reason,'review_note'=>$request->review_note],'created_at'=>now()]);
    }
}
