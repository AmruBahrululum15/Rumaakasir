<?php
namespace App\Notifications;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
class CustomerPasswordReset extends Notification
{
    public function __construct(public string $token) {}
    public function via($notifiable): array { return ['mail']; }
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)->subject('Reset Password Customer Rumaakasir')
            ->line('Gunakan tautan ini untuk mengganti password akun pelanggan Anda.')
            ->action('Reset Password', route('customer.reset', ['token'=>$this->token, 'email'=>$notifiable->email]))
            ->line('Tautan berlaku 60 menit. Abaikan jika Anda tidak meminta reset.');
    }
}
