<?php

namespace App\Notifications;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationDecisionNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly bool $approved) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $settings = Setting::allAsArray();
        $business = ($settings['app_name'] ?? '') ?: 'POS Enterprise';

        return (new MailMessage)
            ->subject($business.' - Status Registrasi')
            ->view('emails.auth.registration-decision', [
                'approved' => $this->approved,
                'businessName' => $business,
                'businessLogo' => ($settings['sidebar_logo'] ?? null) ?: ($settings['app_logo'] ?? null),
                'name' => $notifiable->name,
                'role' => $notifiable->requested_role,
                'loginUrl' => route('login'),
            ]);
    }
}
