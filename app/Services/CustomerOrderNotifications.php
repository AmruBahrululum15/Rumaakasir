<?php
namespace App\Services;
use App\Models\{Sale,User};
use App\Notifications\CustomerOrderNotification;
class CustomerOrderNotifications {
    public function send(Sale $sale,string $event):void {
        $notification=new CustomerOrderNotification($sale,$event);
        foreach(User::withoutGlobalScopes()->where('business_id',$sale->business_id)->where('is_active',true)->where('status','approved')->whereHas('roles',fn($q)=>$q->where('guard_name','web')->whereIn('name',['Atasan','Admin','Kasir']))->get() as $user){
            if(!$user->can('sales.view'))continue;
            $hash=md5('customer-order:'.$sale->id.':'.$event.':'.$user->id);
            $id=substr($hash,0,8).'-'.substr($hash,8,4).'-'.substr($hash,12,4).'-'.substr($hash,16,4).'-'.substr($hash,20);
            $user->notifications()->firstOrCreate(['id'=>$id],['type'=>CustomerOrderNotification::class,'data'=>$notification->toDatabase($user)]);
        }
    }
}
