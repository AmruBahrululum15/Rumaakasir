@extends('layouts.app')
@section('title','Riwayat Promo')
@section('content')
<h1 class="h3">Riwayat: {{ $promotion->name }}</h1><div class="card card-body table-responsive"><table class="table"><thead><tr><th>Transaksi</th><th>Customer ID</th><th>Diskon</th><th>Waktu</th></tr></thead><tbody>@forelse($usages as $usage)<tr><td>{{ $usage->sale_id }}</td><td>{{ $usage->customer_id }}</td><td>Rp{{ number_format($usage->discount,0,',','.') }}</td><td>{{ $usage->created_at }}</td></tr>@empty<tr><td colspan="4">Belum ada penggunaan.</td></tr>@endforelse</tbody></table>{{ $usages->links() }}</div>
@endsection
