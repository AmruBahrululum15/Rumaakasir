<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CustomerOrderPayment extends Model {
    protected $guarded=['id'];
    protected $hidden=['server_key'];
    protected $casts=['server_key'=>'encrypted','production'=>'boolean','enabled_payments'=>'array','instructions'=>'array'];
}
