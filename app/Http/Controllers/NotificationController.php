<?php

namespace App\Http\Controllers;

class NotificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $user = auth()->user();

        $notifications = $user
            ->notifications()
            ->latest()
            ->paginate(15);

        $unreadCount = $user->unreadNotifications()->count();

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

        $notification = $user
            ->notifications()
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

        $count = $user->unreadNotifications()->update([
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

        $notification = $user
            ->notifications()
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

        $user
            ->notifications()
            ->delete();

        return back()->with(
            'success',
            'Semua notifikasi berhasil dihapus.'
        );
    }
}
