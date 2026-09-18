<?php

namespace App\Notifications;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BusinessResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $settings = Setting::allAsArray();
        $businessName = ($settings['app_name'] ?? '') ?: 'POS Enterprise';
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject($businessName.' - Atur Ulang Password')
            ->view('emails.auth.password-reset', [
                'businessName' => $businessName,
                'businessLogo' => ($settings['sidebar_logo'] ?? null) ?: ($settings['app_logo'] ?? null),
                'url' => $url,
                'expiry' => config('auth.passwords.users.expire'),
            ]);
    }
}
