@extends('layouts.app')
@section('title','Riwayat Poin')
@section('content')
<h1 class="h3">{{ $customer->name }}</h1><p class="lead">Saldo: {{ number_format($balance) }} poin</p><a href="{{ route('loyalty.index') }}">Loyalty Customer</a>
<div class="card card-body mt-3 table-responsive"><table class="table"><thead><tr><th>Tanggal</th><th>Poin</th><th>Keterangan</th></tr></thead><tbody>@forelse($entries as $entry)<tr><td>{{ $entry->created_at->format('d/m/Y H:i') }}</td><td>{{ $entry->points }}</td><td>{{ $entry->description }}</td></tr>@empty<tr><td colspan="3">Belum ada riwayat poin.</td></tr>@endforelse</tbody></table>{{ $entries->links() }}</div>
@endsection
