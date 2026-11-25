<?php
namespace App\Notifications;
use Illuminate\Notifications\Notification;
class ReservationNotification extends Notification {
    public function __construct(private array $data){}
    public function via($notifiable):array{return ['database'];}
    public function toArray($notifiable):array{return $this->data;}
}
