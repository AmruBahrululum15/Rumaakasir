<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CustomerAddress extends Model {
    protected $guarded=['id','customer_account_id'];
    protected $casts=['is_default'=>'boolean','latitude'=>'float','longitude'=>'float'];
}
