<?php
namespace App\Services;
use App\Models\User;
class StaffNotifications {
    public function query(User $user){
        $query=$user->notifications();
        if(!$user->hasRole('Super Admin')){
            $query->where(function($q)use($user){
                $q->where('data->business_id',$user->business_id)->orWhere(function($legacy)use($user){
                    $legacy->whereNull('data->business_id')->where(function($q)use($user){
                        $q->whereNull('data->sale_id')->orWhereIn('data->sale_id',\App\Models\Sale::withoutGlobalScopes()->select('id')->where('business_id',$user->business_id));
                    });
                });
            });
        }
        $query->where(function($q)use($user){
            $q->whereNull('data->conversation_id');
            if(app(CustomerChat::class)->canRead($user))$q->orWhereIn('data->conversation_id',\App\Models\Conversation::select('id')->where('business_id',$user->business_id));
        });
        $query->where(function($q)use($user){
            $q->whereNull('data->booking_id');
            if($user->hasAnyRole(['Atasan','Admin','Kasir']) && $user->can('sales.create'))$q->orWhereIn('data->booking_id',\App\Models\Booking::withoutGlobalScopes()->select('id')->where('business_id',$user->business_id));
        });
        return $query;
    }
}
