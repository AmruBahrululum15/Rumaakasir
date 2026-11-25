<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CustomerLinkInvitation extends Model
{
    protected $guarded=['id'];
    protected $hidden=['token_hash'];
    protected $casts=['expires_at'=>'datetime','accepted_at'=>'datetime'];
    public function business(){return $this->belongsTo(Business::class);}
}
