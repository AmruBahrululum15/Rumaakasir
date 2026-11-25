@extends('layouts.app')
@section('title','Pelanggan')
@section('content_header')
<div class="customers-heading"><span>HUBUNGAN PELANGGAN</span><h1>Pelanggan</h1><p>Data pelanggan dari transaksi kasir, QR meja, portal, dan reservasi toko Anda.</p></div>
@stop
@section('content')
<link rel="stylesheet" href="{{ asset('css/customers.css') }}">
<div class="customers-page">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="cu-stats">@foreach(['total'=>['Total pelanggan','users'],'transacting'=>['Memiliki transaksi','receipt'],'with_contact'=>['Dengan nomor kontak','phone']] as $key=>$info)<article><div><span>{{ $info[0] }}</span><i class="fas fa-{{ $info[1] }}" aria-hidden="true"></i></div><strong>{{ number_format($summary[$key],0,',','.') }}</strong><small>Data toko Anda</small></article>@endforeach</div>
<section class="cu-card"><div class="cu-header"><div><h2>Daftar pelanggan</h2><p>{{ $customers->total() }} pelanggan{{ $search ? ' sesuai pencarian' : ' terdaftar' }}</p></div><form method="get" action="{{ route('customers.index') }}"><input aria-label="Cari pelanggan" name="search" value="{{ $search }}" placeholder="Cari nama, nomor HP, atau alamat"><button type="submit">Cari</button>@if($search)<a href="{{ route('customers.index') }}">Reset</a>@endif</form></div>
<div class="table-responsive"><table class="table"><thead><tr><th>Pelanggan</th><th>Kontak & alamat</th><th>Sumber aktivitas</th><th>Transaksi</th><th>Aksi</th></tr></thead><tbody>
@forelse($customers as $customer)
<tr><td><div class="cu-person"><span class="cu-avatar">{{ mb_strtoupper(mb_substr($customer->name,0,1)) }}</span><div><strong>{{ $customer->name }}</strong><small>Terdaftar {{ $customer->created_at?->locale('id')->translatedFormat('d M Y') }}</small></div></div></td><td><span>{{ $customer->phone ?: 'Nomor HP belum tersedia' }}</span><small class="cu-address">{{ $customer->address ?: 'Alamat belum dilengkapi' }}</small></td><td><div class="cu-sources">@if($customer->cashier_count)<span>Kasir</span>@endif @if($customer->qr_count)<span>QR Meja</span>@endif @if($customer->portal_count)<span>Portal</span>@endif @if($customer->bookings_count)<span>Reservasi</span>@endif @if(!$customer->sales_count && !$customer->bookings_count)<span class="cu-neutral">Terdaftar</span>@elseif(!$customer->cashier_count && !$customer->qr_count && !$customer->portal_count && !$customer->bookings_count)<span>Layanan lainnya</span>@endif</div></td><td><strong>{{ $customer->sales_count }}</strong><small class="cu-address">{{ $customer->bookings_count }} reservasi</small></td><td><div class="cu-actions"><a href="{{ route('customers.show',$customer) }}">Detail</a>@can('customers.edit')<a class="cu-secondary" href="{{ route('customers.edit',$customer) }}">Edit</a><form method="post" action="{{ route('customers.destroy',$customer) }}" onsubmit="return confirm('Hapus pelanggan ini?')">@csrf @method('DELETE')<button class="cu-delete" type="submit">Hapus</button></form>@endcan</div></td></tr>
@empty<tr><td colspan="5"><div class="cu-empty"><i class="fas fa-users" aria-hidden="true"></i><h3>{{ $search ? 'Pelanggan tidak ditemukan' : 'Belum ada pelanggan' }}</h3><p>{{ $search ? 'Coba cari dengan nama atau nomor HP lainnya.' : 'Pelanggan yang tercatat dari transaksi kasir, QR meja, portal, dan reservasi akan muncul di sini.' }}</p></div></td></tr>@endforelse
</tbody></table></div><div class="cu-pagination">{{ $customers->links() }}</div></section></div>
@stop
