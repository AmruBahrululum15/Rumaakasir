<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToBusiness;
class Booking extends Model {
    use BelongsToBusiness;protected $guarded=['id','business_id'];protected $hidden=['public_token_hash'];protected $casts=['starts_at'=>'datetime','ends_at'=>'datetime','reserved_until'=>'datetime','checked_in_at'=>'datetime','total'=>'decimal:2','paid'=>'decimal:2'];
    public function customer(){return $this->belongsTo(Customer::class);}
    public function staff(){return $this->belongsTo(User::class,'staff_id');}
    public function resource(){return $this->belongsTo(BookingResource::class);}
    public function payments(){return $this->hasMany(BookingPayment::class);}
}
