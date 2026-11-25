<?php

namespace App\Notifications;

use App\Models\PlatformSetting;
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
        $platform = PlatformSetting::find(1) ?? new PlatformSetting;
        $businessName = $platform->brandingName();
        $url = url(route('password.reset', [
            'token' => $this->token,
            'username' => $notifiable->username,
        ], false));

        return (new MailMessage)
            ->subject($businessName.' - Atur Ulang Password')
            ->view('emails.auth.password-reset', [
                'businessName' => $businessName,
                'businessLogo' => $platform->brandingLogo(),
                'url' => $url,
                'expiry' => config('auth.passwords.users.expire'),
                'username' => $notifiable->username,
                'storeName' => $notifiable->business?->name ?? 'Platform',
            ]);
    }
}
