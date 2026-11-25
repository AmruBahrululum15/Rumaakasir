<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BusinessCustomer extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];
    public function business() { return $this->belongsTo(Business::class); }
    public function account() { return $this->belongsTo(CustomerAccount::class, 'customer_account_id'); }
}
