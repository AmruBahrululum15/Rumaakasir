@extends('layouts.app')
@section('title', 'Paket & Langganan')
@section('description', 'Kelola paket dan status langganan setiap toko.')
@section('content')
<section class="content"><div class="container-fluid"><div class="card">
    <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
        <thead><tr><th>Toko</th><th>Paket</th><th>Status Langganan</th><th>Berakhir</th><th>Aksi</th></tr></thead>
        <tbody>@forelse($businesses as $business)<tr>
            <td>{{ $business->name }}</td><td>{{ $packageNames[$business->effectiveSubscriptionPlan()] ?? ucfirst($business->effectiveSubscriptionPlan()) }} <small class="text-muted">({{ ucfirst($business->effectiveSubscriptionPlan()) }})</small></td>
            <td>{{ $business->subscription_source === 'doku' && $business->subscription_status === 'active' && $business->subscription_ends_at?->lte(now()) ? 'Expired (Free aktif)' : ucfirst($business->subscription_status) }}</td><td>{{ $business->subscription_ends_at?->format('d M Y') ?? 'Tidak ditentukan' }}</td>
            <td><a class="btn btn-sm btn-outline-primary" href="{{ route('businesses.edit', $business) }}">Kelola langganan</a></td>
        </tr>@empty<tr><td colspan="5" class="text-center text-muted py-5">Belum ada langganan toko.</td></tr>@endforelse</tbody>
    </table></div></div><div class="card-footer">{{ $businesses->links() }}</div>
</div>
<div class="card"><div class="card-header"><h2 class="h5 mb-0">Pembayaran Langganan DOKU</h2></div>
    @include('subscriptions.history', ['platform' => true])
</div></div></section>
@endsection
