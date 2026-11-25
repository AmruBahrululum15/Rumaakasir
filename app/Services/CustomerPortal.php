<?php
namespace App\Services;

use App\Models\{Business, BusinessCustomer, Customer, CustomerAccount};
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

class CustomerPortal
{
    public function active(Business $business, string $feature = 'customer_portal'): bool
    {
        $dependency=['customer_booking'=>'booking','customer_vouchers'=>'promotions'][$feature]??null;
        return $business->business_type !== null && $business->is_active
            && app(PremiumFeatures::class)->allows($business, 'customer_portal')
            && app(PremiumFeatures::class)->allows($business, $feature)
            && (!$dependency || app(PremiumFeatures::class)->allows($business,$dependency))
            && app(PackageAccess::class)->forType($business->business_type,'premium')->is_active;
    }
    public function requireActive(Business $business, string $feature = 'customer_portal'): void
    {
        abort_unless($this->active($business, $feature), 403, 'Toko sedang tidak menerima layanan online.');
    }
    public function within(Business $business, callable $callback): mixed
    {
        $context=app(TenantContext::class); $previous=$context->business();
        $context->set($business);
        try { return $callback(); } finally { $previous ? $context->set($previous) : $context->clear(); }
    }
    public function join(Business $business, CustomerAccount $account): BusinessCustomer
    {
        return DB::transaction(function () use ($business, $account) {
            $business=Business::whereKey($business->id)->lockForUpdate()->firstOrFail();
            $this->requireActive($business); abort_unless($account->status==='active',403);
            $existing=BusinessCustomer::where('business_id',$business->id)->where('customer_account_id',$account->id)->first();
            if ($existing) { abort_unless($existing->status==='active',403); return $existing; }
            // Never claim an existing customer's purchases/points by matching unverified contact details.
            $customer=$this->within($business, fn()=>Customer::create(['name'=>$account->name,'phone'=>$account->phone]));
            return BusinessCustomer::create(['business_id'=>$business->id,'customer_account_id'=>$account->id,'customer_id'=>$customer->id,'joined_at'=>now()]);
        });
    }
}
