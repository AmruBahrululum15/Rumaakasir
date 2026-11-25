<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToBusiness;
class PromotionUsage extends Model {
    use BelongsToBusiness;
    public $timestamps=false;
    protected $guarded=['id','business_id'];
}
