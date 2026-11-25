@extends('portal.layout')
@section('content')<section class="card"><h1>{{ $business->name }}</h1><p>Toko sedang tidak menerima layanan online.</p><p>Riwayat pesanan, reservasi, dan poin Anda tetap tersimpan.</p><a class="btn" href="{{ route('customer.stores') }}">Cari toko lain</a></section>@endsection
