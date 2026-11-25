<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetCodeNotification extends Notification
{
    public function __construct(
        private readonly string $code,
        private readonly string $userName
    ) {}

    public function via(
        object $notifiable
    ): array {

        return [
            'mail',
        ];
    }

    public function toMail(
        object $notifiable
    ): MailMessage {

        return (new MailMessage)

            ->subject(
                'Kode Reset Password - POS Enterprise'
            )

            ->view(
                'emails.auth.password-reset-code',
                [
                    'code' =>
                        $this->code,

                    'userName' =>
                        $this->userName,

                    'expiresIn' =>
                        10,

                    'appName' =>
                        config(
                            'app.name',
                            'POS Enterprise'
                        ),
                ]
            );
    }
}