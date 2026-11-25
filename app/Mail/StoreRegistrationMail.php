<?php

namespace App\Mail;

use App\Models\StoreRegistration;
use Illuminate\Mail\Mailable;

class StoreRegistrationMail extends Mailable
{
    public function __construct(public StoreRegistration $registration, public string $kind, public ?string $actionUrl = null) {}
    public function build(): static
    {
        return $this->subject(match ($this->kind) {
            'received' => 'Pengajuan Toko Berhasil Diterima', 'review' => 'Pengajuan Toko Baru',
            'approved' => 'Pengajuan Toko Anda Disetujui', 'rejected' => 'Pengajuan Toko Belum Dapat Disetujui',
        })->view('emails.store-registration');
    }
}
