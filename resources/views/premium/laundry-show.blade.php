@extends('layouts.app')
@section('title','Detail Laundry')
@section('content')
<h1 class="h3">LND-{{ $order->id }} · {{ $order->customer?->name }}</h1><a href="{{ route('laundry.index',['customer_id'=>$order->customer_id]) }}">Riwayat customer</a>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card card-body mt-3"><h2 class="h5">{{ $order->service_name }}</h2><p>{{ $order->quantity }} {{ $order->unit }} × Rp{{ number_format($order->price,0,',','.') }}<br>Total: Rp{{ number_format($order->total,0,',','.') }}<br>Dibayar: Rp{{ number_format($order->paid,0,',','.') }}<br>Estimasi: {{ $order->estimated_at->format('d/m/Y H:i') }}<br>{{ $order->notes }}</p>
@php($next = array_keys($statuses)[array_search($order->status,array_keys($statuses),true)+1] ?? null)
@if($next)<form method="POST" action="{{ route('laundry.status',$order) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $next }}"><button class="btn btn-warning">Lanjut: {{ $statuses[$next] }}</button></form>@endif
</div><div class="row"><div class="col-md-6"><section class="card card-body"><h2 class="h5">Timeline</h2>@foreach($history as $entry)<p>{{ $statuses[$entry->status] }} · {{ $entry->created_at }} · User #{{ $entry->user_id }}</p>@endforeach</section></div><div class="col-md-6"><section class="card card-body"><h2 class="h5">Pembayaran tunai</h2>
@if((float)$order->paid<(float)$order->total)<form method="POST" action="{{ route('laundry.pay',$order) }}">@csrf<input type="hidden" name="request_key" value="{{ (string)\Illuminate\Support\Str::uuid() }}"><label>Uang diterima<input type="number" class="form-control" name="amount" min="0.01" step="0.01" max="{{ (float)$order->total-(float)$order->paid }}" required></label><button class="btn btn-warning">Catat pembayaran</button></form>@endif
@foreach($payments as $payment)<p class="mt-2">Rp{{ number_format($payment->amount,0,',','.') }} · Tunai · {{ $payment->created_at }}</p>@endforeach</section></div></div>
@endsection
