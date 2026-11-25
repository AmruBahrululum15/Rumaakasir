<?php
namespace App\Services;
use App\Models\{Business,BusinessCustomer,Sale};
use Illuminate\Support\Facades\DB;

class PortalPurchaseLink
{
    public function record(Sale $sale): void
    {
        if (!$sale->customer_id || strtoupper((string)$sale->payment_status)!=='PAID') return;
        $business=Business::find($sale->business_id);
        if (!$business || $business->business_type===null) return;
        $member=BusinessCustomer::where('business_id',$business->id)->where('customer_id',$sale->customer_id)->where('status','active')->first();
        if (!$member) return;
        // Staff explicitly selected this tenant's customer, or the customer already
        // authenticated during portal/QR checkout. Never claim guest orders by phone.
        if (!$sale->customer_account_id && $sale->user_id && app(CustomerPortal::class)->active($business)) {
            DB::table('sales')->where('id',$sale->id)->where('business_id',$business->id)->whereNull('customer_account_id')->update(['customer_account_id'=>$member->customer_account_id]);
            $sale->setAttribute('customer_account_id',$member->customer_account_id);
            $sale->syncOriginalAttribute('customer_account_id');
        }
        if ((int)$sale->customer_account_id===(int)$member->customer_account_id) $member->update(['last_transaction_at'=>now()]);
    }
}
