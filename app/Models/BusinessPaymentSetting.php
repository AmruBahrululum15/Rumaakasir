<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BusinessPaymentSetting extends Model {
    protected $guarded=['id'];
    protected $hidden=['midtrans_server_key'];
    protected $casts=['cash_enabled'=>'boolean','qris_enabled'=>'boolean','va_enabled'=>'boolean','midtrans_production'=>'boolean','midtrans_server_key'=>'encrypted','va_banks'=>'array'];
}
