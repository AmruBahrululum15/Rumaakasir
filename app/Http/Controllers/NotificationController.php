<?php

namespace App\Http\Controllers;

class NotificationController extends Controller
{
    private function notifications(){return app(\App\Services\StaffNotifications::class)->query(auth()->user());}
    public function feed(){
        $query=$this->notifications();$unreadNotifications=(clone $query)->whereNull('read_at')->count();$navbarNotifications=$query->latest()->limit(8)->get();
        $reservationCount=(clone $query)->whereNull('read_at')->whereNotNull('data->booking_id')->count();
        $reservations=$navbarNotifications->filter(fn($n)=>!$n->read_at && isset($n->data['booking_id']))->map(fn($n)=>['id'=>$n->id,'title'=>$n->data['title'],'message'=>$n->data['message'],'url'=>route('notifications.open',$n->id)])->values();
        return response()->json(['unread_count'=>$unreadNotifications,'reservation_unread_count'=>$reservationCount,'reservations'=>$reservations,'chat_unread_count'=>app(\App\Services\CustomerChat::class)->unreadCount(auth()->user()),'html'=>view('partials.order-notifications',compact('unreadNotifications','navbarNotifications'))->render()])->header('Cache-Control','private, no-store');
    }
    public function open($id){
        $notification=$this->notifications()->where('id',$id)->firstOrFail();
        $data=$notification->data;
        if(isset($data['booking_id'])){
            abort_unless(auth()->user()->hasAnyRole(['Atasan','Admin','Kasir']) && auth()->user()->can('sales.create'),403);
            $booking=\App\Models\Booking::where('business_id',auth()->user()->business_id)->findOrFail($data['booking_id']);
            $notification->markAsRead();return redirect()->route('bookings.show',$booking->id);
        }
        if(isset($data['conversation_id'])){
            $conversation=\App\Models\Conversation::where('business_id',auth()->user()->business_id)->findOrFail($data['conversation_id']);
            app(\App\Services\CustomerChat::class)->authorize($conversation,auth()->user());
            $notification->markAsRead();
            return redirect()->route('customer-chat.show',$conversation->id);
        }
        if(isset($data['order_id'])){
            abort_unless(auth()->user()->can('sales.view'),403);
            $sale=\App\Models\Sale::where('business_id',auth()->user()->business_id)->findOrFail($data['order_id']);
            $notification->markAsRead();
            return redirect()->route($sale->order_source==='customer_portal'?'portal-orders.show':'sales.show',$sale->id);
        }
        $notification->markAsRead();return redirect()->route('notifications.index');
    }
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $user = auth()->user();

        $notifications = $this->notifications()
            ->latest()
            ->paginate(15);

        $unreadCount = $this->notifications()->whereNull('read_at')->count();

        return view(
            'notifications.index',
            compact('notifications', 'unreadCount')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | READ
    |--------------------------------------------------------------------------
    |
    | Tandai satu notifikasi sebagai sudah dibaca.
    |
    */

    public function read($id)
    {
        $user = auth()->user();

        $notification = $this->notifications()
            ->where('id', $id)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Tandai sudah dibaca
        |--------------------------------------------------------------------------
        */

        if (is_null($notification->read_at)) {

            $notification->markAsRead();

        }

        return back()->with(
            'success',
            'Notifikasi berhasil ditandai sebagai sudah dibaca.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | READ ALL
    |--------------------------------------------------------------------------
    |
    | Tandai seluruh notifikasi sebagai sudah dibaca.
    |
    */

    public function readAll()
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Ambil notifikasi yang belum dibaca
        |--------------------------------------------------------------------------
        */

        $count = $this->notifications()->whereNull('read_at')->update([
            'read_at' => now(),
        ]);

        return back()->with(
            'success',
            $count > 0
                ? "{$count} notifikasi berhasil ditandai sebagai sudah dibaca."
                : 'Tidak ada notifikasi baru untuk ditandai.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    |
    | Hapus satu notifikasi.
    |
    */

    public function destroy($id)
    {
        $user = auth()->user();

        $notification = $this->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->delete();

        return back()->with(
            'success',
            'Notifikasi berhasil dihapus.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY ALL
    |--------------------------------------------------------------------------
    |
    | Hapus seluruh notifikasi milik user yang sedang login.
    |
    */

    public function destroyAll()
    {
        $user = auth()->user();

        $this->notifications()
            ->delete();

        return back()->with(
            'success',
            'Semua notifikasi berhasil dihapus.'
        );
    }
}
