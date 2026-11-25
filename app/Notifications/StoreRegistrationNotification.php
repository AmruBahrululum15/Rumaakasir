<?php

namespace App\Notifications;

use App\Models\StoreRegistration;
use Illuminate\Notifications\Notification;

class StoreRegistrationNotification extends Notification
{
    public function __construct(public StoreRegistration $registration, public bool $forOwner = false) {}
    public function via(object $notifiable): array { return ['database']; }
    public function toArray(object $notifiable): array
    {
        return ['title' => $this->forOwner ? 'Toko disetujui' : 'Pengajuan toko baru',
            'message' => $this->forOwner ? 'Toko Anda telah disetujui dan siap digunakan.'.($this->registration->requested_plan === 'premium' ? ' Premium menunggu pembayaran.' : '') : 'Pengajuan toko baru: '.$this->registration->store_name,
            'registration_id' => $this->registration->id, 'store_name' => $this->registration->store_name,
            'owner_name' => $this->registration->owner_name, 'requested_plan' => $this->registration->requested_plan];
    }
}
