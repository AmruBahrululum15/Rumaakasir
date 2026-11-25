<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Conversation extends Model
{
    protected $guarded = ['id'];
    protected $attributes = ['status' => 'open'];
    public function business() { return $this->belongsTo(Business::class); }
    public function account() { return $this->belongsTo(CustomerAccount::class, 'customer_account_id'); }
    public function messages() { return $this->hasMany(Message::class); }
    public function latestMessage() { return $this->hasOne(Message::class)->latestOfMany(); }
}
