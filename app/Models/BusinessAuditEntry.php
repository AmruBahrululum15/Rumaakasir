<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToBusiness;
class BusinessAuditEntry extends Model {
    use BelongsToBusiness;
    public $timestamps=false;
    protected $guarded=['id','business_id'];
    protected $casts=['metadata'=>'array','created_at'=>'datetime'];
}
