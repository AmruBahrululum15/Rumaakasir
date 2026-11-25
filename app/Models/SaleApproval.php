<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToBusiness;
class SaleApproval extends Model {
    use BelongsToBusiness;
    protected $guarded=['id','business_id'];
    protected $casts=['reviewed_at'=>'datetime'];
    public function sale(){return $this->belongsTo(Sale::class);}
    public function requester(){return $this->belongsTo(User::class,'requested_by');}
}
