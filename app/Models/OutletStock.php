<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToBusiness;
class OutletStock extends Model {use BelongsToBusiness;public $timestamps=false;protected $guarded=['id','business_id'];public function product(){return $this->belongsTo(Product::class);}public function outlet(){return $this->belongsTo(Outlet::class);}}
