<?php
namespace App\Models;
use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
class CustomerLoyaltyPoint extends Model {
    use BelongsToBusiness;
    public $timestamps=false;
    protected $guarded=['id'];
    protected $casts=['created_at'=>'datetime','points'=>'integer'];
}
