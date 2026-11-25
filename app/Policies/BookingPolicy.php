<?php
namespace App\Policies;
use App\Models\{Booking,User};
class BookingPolicy {
    public function view(User $user,Booking $booking):bool{return (int)$user->business_id===(int)$booking->business_id && $user->hasAnyRole(['Atasan','Admin','Kasir']) && $user->can('sales.create');}
    public function update(User $user,Booking $booking):bool{return $this->view($user,$booking);}
}
