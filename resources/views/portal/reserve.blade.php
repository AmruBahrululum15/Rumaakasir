@extends('portal.layout')
@section('title','Reservasi Sekarang')
@section('content')
<link rel="stylesheet" href="{{ asset('css/reservations.css') }}">
<div class="reservation-page">
<div class="rv-header"><div><a href="{{ route('customer.store',$business->slug) }}" class="rv-muted">← Kembali ke toko</a><h1 style="margin-top:12px">Reservasi {{ $business->name }}</h1><p>Pilih layanan, temukan slot tersedia, lalu kirim reservasi Anda.</p></div></div>
@if($services->isEmpty())<div class="rv-card rv-empty">Toko belum menambahkan layanan reservasi. Hubungi toko untuk informasi lebih lanjut.</div>@else
<div class="rv-layout"><section class="rv-card">
<form method="post" action="{{ route('reservations.store',$business->slug) }}" id="public-reservation" data-slots-url="{{ route('reservations.slots',$business->slug) }}">@csrf<input type="hidden" name="request_token" value="{{ $requestToken }}">
<h2>1. Pilih layanan & jadwal</h2><div class="rv-fields">
<div><label for="rv-service">Layanan</label><select id="rv-service" name="service_id" required><option value="">Pilih layanan</option>@foreach($services as $s)<option value="{{ $s->id }}" data-price="{{ $s->price }}" data-duration="{{ $s->duration_minutes }}" @selected(old('service_id')==$s->id)>{{ $s->name }} · Rp {{ number_format($s->price,0,',','.') }} · {{ $s->duration_minutes }} menit</option>@endforeach</select></div>
<div><label for="rv-units">Jumlah peserta / unit</label><input id="rv-units" type="number" name="capacity_units" min="1" max="1000" value="{{ old('capacity_units',1) }}" required></div>
<div><label for="rv-staff">Karyawan</label><select id="rv-staff" name="staff_id"><option value="">Tanpa preferensi</option>@foreach($staff as $person)<option value="{{ $person->id }}" @selected(old('staff_id')==$person->id)>{{ $person->name }}</option>@endforeach</select></div>
<div><label for="rv-resource">Meja / fasilitas</label><select id="rv-resource" name="resource_id"><option value="">Tanpa fasilitas khusus</option>@foreach($resources as $resource)<option value="{{ $resource->id }}" @selected(old('resource_id')==$resource->id)>{{ $resource->name }} · kapasitas {{ $resource->capacity }}</option>@endforeach</select></div>
<div><label for="rv-date">Tanggal</label><input id="rv-date" type="date" name="booking_date" min="{{ now()->toDateString() }}" max="{{ now()->addYear()->toDateString() }}" value="{{ old('booking_date',now()->addDay()->toDateString()) }}" required></div>
<div><label for="rv-time">Jam mulai</label><input id="rv-time" type="time" name="start_time" value="{{ old('start_time') }}" required><small class="rv-muted">Pilih slot di bawah atau isi jam secara manual.</small></div>
</div><div id="rv-slot-feedback" class="rv-loading" role="status">Pilih layanan untuk melihat slot tersedia.</div><div id="rv-slots" class="rv-slots"></div>
<h2 style="margin-top:22px">2. Informasi pelanggan</h2><div class="rv-fields">
<div><label for="rv-name">Nama pelanggan</label><input id="rv-name" name="customer_name" required maxlength="120" value="{{ old('customer_name',auth('customer')->user()?->name) }}" autocomplete="name"></div>
<div><label for="rv-phone">Nomor WhatsApp</label><input id="rv-phone" name="customer_phone" required maxlength="30" value="{{ old('customer_phone',auth('customer')->user()?->phone) }}" autocomplete="tel" inputmode="tel"></div>
<div class="rv-wide"><label for="rv-email">Email (opsional)</label><input id="rv-email" name="customer_email" type="email" maxlength="190" value="{{ old('customer_email',auth('customer')->user()?->email) }}" autocomplete="email"></div>
<div class="rv-wide"><label for="rv-notes">Catatan khusus (opsional)</label><textarea id="rv-notes" name="notes" rows="3" maxlength="1000">{{ old('notes') }}</textarea></div>
</div><div class="rv-actions"><button type="submit" id="rv-submit">Kirim Reservasi</button></div>
</form></section><aside class="rv-card"><h2>Ringkasan reservasi</h2><p id="rv-service-summary">Belum memilih layanan.</p><div class="rv-total"><span>Estimasi total</span><strong id="rv-price">Rp 0</strong></div><div class="rv-info">{{ $settings['payment_mode']==='free'?'Reservasi tidak mewajibkan pembayaran di muka.':($settings['payment_mode']==='deposit'?'DP '.$settings['deposit_percent'].'% diperlukan untuk konfirmasi.':'Pembayaran lunas diperlukan untuk konfirmasi.') }}<br>Pembayaran dilakukan secara Cash dan dicatat oleh kasir toko.</div><p>Slot diperiksa kembali saat Anda mengirim reservasi. Simpan tautan status yang muncul setelah reservasi berhasil.</p><small class="rv-muted">Semua jadwal menggunakan zona waktu {{ config('app.timezone') }}.</small></aside></div>
@endif</div><script src="{{ asset('js/reservations.js') }}" defer></script>
@endsection
