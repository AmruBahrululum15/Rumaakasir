<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToBusiness;
class LaundryOrder extends Model {
    use BelongsToBusiness;protected $guarded=['id','business_id'];protected $casts=['estimated_at'=>'datetime','total'=>'decimal:2','paid'=>'decimal:2'];
    public function customer(){return $this->belongsTo(Customer::class);}
}
