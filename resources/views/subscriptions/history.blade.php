<div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead><tr>@if($platform)<th>Toko</th>@endif<th>Order ID</th><th>Paket</th><th>Periode</th><th>Nominal</th><th>Gateway / Metode</th><th>Status</th><th>Dibayar</th><th>Mulai</th><th>Berakhir</th>@unless($platform)<th>Aksi</th>@endunless</tr></thead>
    <tbody>@forelse($orders as $order)<tr>
        @if($platform)<td>{{ $order->business?->name ?? $order->business_name }}</td>@endif
        <td>{{ $order->order_id }}</td><td>{{ $order->plan_name }}</td><td>{{ $order->billing_cycle === 'monthly' ? 'Bulanan' : 'Tahunan' }}</td>
        <td class="text-nowrap">Rp{{ number_format($order->amount, 0, ',', '.') }}</td><td>{{ $order->gateway }} / {{ $order->payment_method ?: '-' }}</td>
        <td><span class="badge badge-{{ $order->displayStatus() === 'paid' ? 'success' : ($order->displayStatus() === 'pending' ? 'warning' : 'secondary') }}">{{ ($friendlyStatuses ?? false) ? (['paid'=>'Dibayar', 'pending'=>'Menunggu Pembayaran', 'creating'=>'Sedang Dibuat', 'create_unknown'=>'Sedang Diverifikasi', 'expired'=>'Berakhir', 'cancelled'=>'Dibatalkan', 'failed'=>'Gagal'][$order->displayStatus()] ?? 'Sedang Diverifikasi') : ucfirst($order->displayStatus()) }}</span></td>
        <td>{{ $order->paid_at?->format('d M Y H:i') ?? '-' }}</td><td>{{ $order->started_at?->format('d M Y H:i') ?? '-' }}</td><td>{{ $order->expires_at?->format('d M Y H:i') ?? '-' }}</td>
        @unless($platform)<td><a class="btn btn-sm btn-outline-secondary" href="{{ route('subscriptions.result', $order->order_id) }}">Detail</a>@if($order->status === 'paid') <a class="btn btn-sm btn-outline-primary" href="{{ route('subscriptions.invoice', $order->order_id) }}">Invoice</a>@endif</td>@endunless
    </tr>@empty<tr><td colspan="11" class="text-center text-muted py-4">Belum ada pembayaran langganan.</td></tr>@endforelse</tbody>
</table></div></div><div class="card-footer">{{ $orders->links() }}</div>
