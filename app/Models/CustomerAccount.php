<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class CustomerAccount extends Authenticatable
{
    use Notifiable;
    protected $fillable = ['name', 'email', 'phone', 'password'];
    protected $hidden = ['password', 'remember_token'];
    protected $attributes = ['status' => 'active'];
    protected $casts = ['password' => 'hashed', 'email_verified_at' => 'datetime', 'last_login_at'=>'datetime'];
    public function setPhoneAttribute($value):void
    {
        $this->attributes['phone']=$value;
        $this->attributes['login_phone']=\App\Services\CustomerIdentity::phone($value);
    }
    public function memberships() { return $this->hasMany(BusinessCustomer::class); }
    public function addresses() { return $this->hasMany(CustomerAddress::class); }
    public function getInitialsAttribute():string { return collect(preg_split('/\s+/u',trim($this->name)))->take(2)->map(fn($s)=>mb_strtoupper(mb_substr($s,0,1)))->implode(''); }
    public function conversations() { return $this->hasMany(Conversation::class); }
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\CustomerPasswordReset($token));
    }
}
