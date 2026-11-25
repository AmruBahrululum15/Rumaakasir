<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToBusiness;
class BookingStaffSchedule extends Model {use BelongsToBusiness;protected $guarded=['id','business_id'];}
