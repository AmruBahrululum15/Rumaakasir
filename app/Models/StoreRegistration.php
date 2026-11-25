<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreRegistration extends Model
{
    public function businessTypeLabel(): string { return app(\App\Services\BusinessModules::class)->label($this->business_type); }
    protected $guarded = ['id'];
    protected $hidden = ['submission_token'];
    protected $casts = ['reviewed_at' => 'datetime', 'activated_at' => 'datetime', 'terms_accepted_at' => 'datetime', 'address_verified' => 'boolean'];
    public function business() { return $this->belongsTo(Business::class); }
    public function owner() { return $this->belongsTo(User::class, 'owner_id')->withoutGlobalScope('business'); }
}
