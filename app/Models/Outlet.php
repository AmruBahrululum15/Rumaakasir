<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToBusiness;
class Outlet extends Model {use BelongsToBusiness;protected $guarded=['id','business_id'];protected $casts=['is_active'=>'boolean'];}
