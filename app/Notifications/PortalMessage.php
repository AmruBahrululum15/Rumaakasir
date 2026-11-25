<?php
namespace App\Notifications;
use Illuminate\Notifications\Notification;
class PortalMessage extends Notification
{
    public function __construct(public int $conversationId, public string $sender, public array $context=[]) {}
    public function via($notifiable): array { return ['database']; }
    public function toArray($notifiable): array
    {
        return array_merge($this->context,['type'=>'customer_chat','title'=>'Pesan baru dari '.$this->sender,
            'message'=>$this->context['message_preview']??('Pesan baru dari '.$this->sender),'conversation_id'=>$this->conversationId]);
    }
}
