<?php
namespace App\Services;
use App\Models\{Conversation, CustomerAccount, Message, User};
use App\Notifications\PortalMessage;
use Illuminate\Support\Facades\DB;

class CustomerChat
{
    public function canRead(User $staff):bool {
        return !$staff->hasRole('Super Admin') && ($staff->can('customer_chat.view') || $staff->can('customer_chat.reply'));
    }
    public function unreadCount(CustomerAccount|User $actor):int {
        if($actor instanceof User && !$this->canRead($actor))return 0;
        return Message::query()->join('conversations','conversations.id','=','messages.conversation_id')
            ->where($actor instanceof CustomerAccount?'conversations.customer_account_id':'conversations.business_id',$actor instanceof CustomerAccount?$actor->id:$actor->business_id)
            ->where('messages.sender_type',$actor instanceof CustomerAccount?'staff':'customer')->whereNull('messages.read_at')->count();
    }
    public function authorize(Conversation $conversation, CustomerAccount|User $actor, bool $reply=false): void
    {
        if ($actor instanceof CustomerAccount) {
            abort_unless($actor->status==='active' && (int)$conversation->customer_account_id===(int)$actor->id,404);
        } else {
            abort_if($actor->hasRole('Super Admin'),403);
            abort_unless((int)$conversation->business_id===(int)$actor->business_id,404);
            abort_unless($reply?$actor->can('customer_chat.reply'):$this->canRead($actor),403);
        }
        if ($reply) {
            app(CustomerPortal::class)->requireActive($conversation->business,'customer_chat');
            abort_unless($conversation->status==='open',403);
            abort_unless(\App\Models\BusinessCustomer::where('business_id',$conversation->business_id)->where('customer_account_id',$conversation->customer_account_id)->where('status','active')->exists(),403);
        }
    }
    public function send(Conversation $conversation, CustomerAccount|User $actor, string $text): Message
    {
        $this->authorize($conversation,$actor,true);
        $message=DB::transaction(function () use ($conversation,$actor,$text) {
            \App\Models\Business::whereKey($conversation->business_id)->lockForUpdate()->firstOrFail();
            $conversation=Conversation::whereKey($conversation->id)->lockForUpdate()->firstOrFail();
            $this->authorize($conversation,$actor,true);
            $message=$conversation->messages()->create(['sender_type'=>$actor instanceof CustomerAccount?'customer':'staff','sender_id'=>$actor->id,'message'=>$text]);
            $conversation->update(['last_message_at'=>now()]); return $message;
        });
        $notification=new PortalMessage($conversation->id, $actor instanceof CustomerAccount?$actor->name:$conversation->business->name,[
            'business_id'=>$conversation->business_id,'customer_account_id'=>$conversation->customer_account_id,
            'customer_name'=>$conversation->account->name,'message_preview'=>\Illuminate\Support\Str::limit($message->message,160),
            'message_id'=>$message->id,'created_at'=>$message->created_at->toIso8601String(),
        ]);
        try { if ($actor instanceof CustomerAccount) {
            foreach (User::withoutGlobalScopes()->where('business_id',$conversation->business_id)->where('is_active',true)->where('status','approved')->get() as $staff) {
                if ($this->canRead($staff) && ($staff->hasAnyRole(['Atasan','Admin']) || ($staff->hasRole('Kasir') && $staff->can('customer_chat.reply')))) $staff->notify($notification);
            }
        } else { $conversation->account->notify($notification); }
        } catch (\Throwable $error) { \Log::warning('Portal chat notification failed',['conversation_id'=>$conversation->id,'exception'=>get_class($error)]); }
        return $message;
    }
}
