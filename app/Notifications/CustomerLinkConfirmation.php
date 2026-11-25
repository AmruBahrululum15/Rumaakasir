<?php
namespace App\Notifications;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
class CustomerLinkConfirmation extends Notification
{
    public function __construct(public string $token,public string $businessName){}
    public function via($notifiable):array{return ['mail'];}
    public function toMail($notifiable):MailMessage
    {
        return (new MailMessage)->subject('Konfirmasi pelanggan toko '.$this->businessName)
            ->line('Toko '.$this->businessName.' mengajukan pengaitan data pelanggan Anda ke akun customer ini.')
            ->line('Konfirmasikan hanya jika data pelanggan toko tersebut memang milik Anda. Tautan berlaku 60 menit.')
            ->action('Periksa & konfirmasi',route('customer.links.show',$this->token));
    }
}
