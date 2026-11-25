<?php
namespace App\Http\Controllers;
use App\Models\{Business,Conversation,Product};
use App\Services\{CustomerChat,CustomerPortal};
use Illuminate\Http\Request;

class PortalChatController extends Controller
{
    private function actor(Request $r) { return $r->routeIs('customer.*')?auth('customer')->user():$r->user(); }
    public function start(Request $r, Business $business, CustomerPortal $portal)
    {
        $portal->requireActive($business,'customer_chat'); $portal->join($business,auth('customer')->user());
        $conversation=Conversation::firstOrCreate(['business_id'=>$business->id,'customer_account_id'=>auth('customer')->id()]);
        return redirect()->route('customer.chat.show',$conversation);
    }
    public function index(Request $r, ?Conversation $conversation=null)
    {
        $r->validate(['before'=>'nullable|integer|min:1']);
        $actor=$this->actor($r); $customer=$r->routeIs('customer.*');
        if (!$customer) abort_if($actor->hasRole('Super Admin'),403);
        if (!$customer) abort_unless(app(CustomerChat::class)->canRead($actor) && $actor->business->business_type!==null,403);
        if ($conversation) app(CustomerChat::class)->authorize($conversation,$actor);
        $messages=$conversation?$conversation->messages()->when($r->before,fn($q,$v)=>$q->where('id','<',$v))->latest('id')->limit(100)->get()->reverse():collect();
        if ($conversation) {
            $conversation->messages()->whereIn('id',$messages->pluck('id'))->where('sender_type',$customer?'staff':'customer')->whereNull('read_at')->update(['read_at'=>now()]);
            $actor->unreadNotifications()->where('data->conversation_id',$conversation->id)
                ->where(fn($q)=>$q->where('type',\App\Notifications\PortalMessage::class)->orWhere('data->type','customer_chat'))
                ->where(fn($q)=>$q->where('data->business_id',$conversation->business_id)->orWhereNull('data->business_id'))
                ->where(fn($q)=>$q->whereIn('data->message_id',$messages->pluck('id'))->orWhereNull('data->message_id'))
                ->update(['read_at'=>now()]);
        }
        $conversations=Conversation::with(['business','account','latestMessage'])->where($customer?'customer_account_id':'business_id',$customer?$actor->id:$actor->business_id)
            ->withCount(['messages as unread_count'=>fn($q)=>$q->where('sender_type',$customer?'staff':'customer')->whereNull('read_at')])->orderByDesc('last_message_at')->paginate(20);
        if ($r->expectsJson()) return response()->json(['unread_count'=>app(CustomerChat::class)->unreadCount($actor),'conversations'=>$customer?$conversations->map(fn($c)=>['id'=>$c->id,'name'=>$c->business->name,'logo'=>$c->business->logo?asset($c->business->logo):null,'unread'=>$c->unread_count,'url'=>route('customer.chat.show',$c),'send_url'=>route('customer.chat.send',$c),'writable'=>$c->status==='open' && app(CustomerPortal::class)->active($c->business,'customer_chat') && \App\Models\BusinessCustomer::where('business_id',$c->business_id)->where('customer_account_id',$c->customer_account_id)->where('status','active')->exists()]):[], 'messages'=>$messages->values()->map(fn($m)=>['id'=>$m->id,'message'=>$m->message,'sender_type'=>$m->sender_type,'created_at'=>$m->created_at->toIso8601String(),'read_at'=>$m->read_at?->toIso8601String()])])->header('Cache-Control','private, no-store');
        $products=null;
        if ($customer && $conversation && !$r->expectsJson()) {
            $r->validate(['menu_search'=>'nullable|string|max:100']);
            $products=Product::withoutGlobalScopes()->where('business_id',$conversation->business_id)->where('is_active',true)
                ->when($r->filled('menu_search'),fn($q)=>$q->where('name','like','%'.$r->input('menu_search').'%'))
                ->orderBy('name')->paginate(12,['id','name','price','image'],'menu_page')->withQueryString();
        }
        return view($customer?'portal.chat':'portal.staff-chat',compact('conversations','conversation','messages','customer','products'));
    }
    public function send(Request $r,Conversation $conversation,CustomerChat $chat)
    {
        $actor=$this->actor($r);
        $chat->authorize($conversation,$actor,true);
        $data=$r->validate(['message'=>'required|string|max:2000','product_id'=>'nullable|integer|min:1']);
        $text=$data['message'];
        if ($r->routeIs('customer.*') && !empty($data['product_id'])) {
            $product=Product::withoutGlobalScopes()->where('business_id',$conversation->business_id)->where('is_active',true)->find($data['product_id']);
            if (!$product) throw \Illuminate\Validation\ValidationException::withMessages(['product_id'=>'Produk tidak tersedia di toko ini.']);
            $text='Pertanyaan tentang produk: '.$product->name."\n".$text;
            if (mb_strlen($text)>2000) throw \Illuminate\Validation\ValidationException::withMessages(['message'=>'Pertanyaan beserta nama produk maksimal 2.000 karakter.']);
        }
        $chat->send($conversation,$actor,$text);
        if($r->expectsJson())return response()->json(['sent'=>true]);
        return back()->with('success','Pesan terkirim.');
    }
}
