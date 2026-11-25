<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToBusiness;
class Promotion extends Model {
    use BelongsToBusiness;
    protected $guarded=['id','business_id'];
    protected $casts=['start_at'=>'datetime','end_at'=>'datetime','is_active'=>'boolean'];
}
