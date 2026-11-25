<!doctype html><html lang="id"><head><meta charset="utf-8"></head><body style="font-family:Arial,sans-serif;color:#252b38;line-height:1.7"><main style="max-width:600px;margin:auto;padding:30px;border:1px solid #eee;border-radius:12px">
<h1 style="font-size:22px">{{ match($kind) { 'received'=>'Pengajuan Toko Berhasil Diterima', 'review'=>'Pengajuan Toko Baru', 'approved'=>'Pengajuan Toko Anda Disetujui', 'rejected'=>'Pengajuan Toko Belum Dapat Disetujui' } }}</h1>
<p>Halo {{ $kind === 'review' ? 'Super Admin' : $registration->owner_name }},</p>
<p>{{ match($kind) { 'received'=>'Pengajuan pembukaan toko Anda telah kami terima. Kami akan mengirimkan email berikutnya setelah pengajuan ditinjau.', 'review'=>'Ada pengajuan toko baru yang memerlukan review Anda.', 'approved'=>'Pengajuan toko Anda telah disetujui.', 'rejected'=>'Pengajuan toko Anda belum dapat disetujui.' } }}</p>
<p><strong>Toko:</strong> {{ $registration->store_name }}<br><strong>Username Login:</strong> {{ $registration->owner_username }}<br><strong>Pemilik:</strong> {{ $registration->owner_name }}<br><strong>Paket:</strong> {{ ucfirst($registration->requested_plan) }}{{ $registration->requested_plan === 'premium' && $kind === 'approved' ? ' Requested' : '' }}
@if($registration->billing_cycle)<br><strong>Periode:</strong> {{ $registration->billing_cycle === 'monthly' ? 'Bulanan' : 'Tahunan' }}@endif
<br><strong>Status:</strong> {{ ['pending'=>'Menunggu Persetujuan','approved'=>'Disetujui','rejected'=>'Ditolak'][$registration->status] }}</p>
@if($kind === 'review')<p>Email: {{ $registration->owner_email }}<br>Nomor HP: {{ $registration->owner_phone }}<br>Jenis usaha: {{ $registration->businessTypeLabel() }}<br>Diajukan: {{ $registration->created_at->format('d M Y H:i') }}</p>@endif
@if($kind === 'approved')<p>{{ $registration->requested_plan === 'premium' ? 'Toko Anda sudah aktif menggunakan Free. Paket Premium akan aktif setelah pembayaran berhasil.' : 'Toko Anda sudah dapat digunakan setelah pengaturan akun.' }}</p><p>Tautan aktivasi berlaku 7 hari dan hanya dapat digunakan sekali. Anda wajib membuat password sebelum masuk dashboard.</p>@endif
@if($kind === 'approved')<p>Gunakan username tersebut untuk masuk ke akun toko Anda setelah aktivasi.</p><p><a href="{{ route('login') }}">Login ke {{ (\App\Models\PlatformSetting::first() ?? new \App\Models\PlatformSetting)->brandingName() }}</a></p>@endif
@if($kind === 'rejected')<p><strong>Alasan:</strong><br>{{ $registration->rejection_reason }}</p>@endif
@if($actionUrl)<p><a href="{{ $actionUrl }}" style="display:inline-block;background:#e76f00;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none">{{ $kind === 'review' ? 'Review Pengajuan' : 'Aktifkan Akun' }}</a></p>@endif
@php($support = \App\Models\PlatformSetting::query()->first())
@if($support?->support_email)<p>Hubungi pengelola: {{ $support->support_email }}</p>@endif
</main></body></html>
