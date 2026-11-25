<?php
namespace App\Services;
use App\Models\{Booking,CustomerAccount,User};
use App\Notifications\ReservationNotification;
class BookingNotifications {
    public function send(int $id,string $event):void {
        $booking=Booking::withoutGlobalScopes()->with('customer')->findOrFail($id);
        $data=['type'=>'reservation','business_id'=>$booking->business_id,'booking_id'=>$id,'booking_code'=>$booking->code??'Booking #'.$id,'customer_name'=>$booking->customer_name??$booking->customer?->name,'service_name'=>$booking->service_name,'starts_at'=>$booking->starts_at->toIso8601String(),'status'=>$booking->status,'event'=>$event,'title'=>$event==='created'?'Reservasi baru dari '.($booking->customer_name??$booking->customer?->name):'Reservasi '.(BookingService::STATUSES[$booking->status]??$booking->status),'message'=>$booking->service_name.' · '.$booking->starts_at->format('d M Y H:i')];
        try {
            if(in_array($event,['created','rescheduled','payment_received']))foreach(User::withoutGlobalScopes()->where('business_id',$booking->business_id)->where('is_active',true)->where('status','approved')->get() as $user){if($user->hasAnyRole(['Atasan','Admin','Kasir']) && $user->can('sales.create'))$user->notify(new ReservationNotification($data));}
            if($booking->customer_account_id && $event!=='created')CustomerAccount::find($booking->customer_account_id)?->notify(new ReservationNotification($data));
        }catch(\Throwable $e){\Log::warning('Reservation notification failed',['booking_id'=>$id,'exception'=>get_class($e)]);}
    }
}
