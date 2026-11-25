@extends('layouts.app')
@section('title', 'Pengajuan Toko')
@section('description', 'Tinjau pendaftaran toko baru sebelum memberikan akses platform.')
@section('content')
<section class="content"><div class="container-fluid">
<div class="card"><div class="card-body"><form method="GET" class="row">
<div class="col-md-3 mb-2"><label for="search">Cari toko / pemilik / email</label><input class="form-control" id="search" name="search" value="{{ request('search') }}"></div>
<div class="col-md-2 mb-2"><label for="status">Status</label><select class="form-control" id="status" name="status"><option value="">Semua</option>@foreach(['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $value=>$label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></div>
<div class="col-md-2 mb-2"><label for="requested_plan">Paket</label><select class="form-control" id="requested_plan" name="requested_plan"><option value="">Semua</option>@foreach(['free','premium'] as $value)<option value="{{ $value }}" @selected(request('requested_plan') === $value)>{{ ucfirst($value) }}</option>@endforeach</select></div>
<div class="col-md-3 mb-2"><label for="business_type">Jenis usaha</label><select class="form-control" id="business_type" name="business_type"><option value="">Semua</option>@foreach(config('business_modules.types') as $value => $type)<option value="{{ $value }}" @selected(request('business_type') === $value)>{{ $type['label'] }}</option>@endforeach</select></div>
<div class="col-md-2 d-flex align-items-end mb-2"><button class="btn btn-primary">Terapkan</button></div></form></div></div>
<div class="card"><div class="table-responsive"><table class="table table-hover"><thead><tr><th>Toko</th><th>Pemilik</th><th>Email</th><th>Jenis Usaha</th><th>Paket</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
@forelse($registrations as $registration)<tr><td>{{ $registration->store_name }}</td><td>{{ $registration->owner_name }}</td><td>{{ $registration->owner_email }}</td><td>{{ $registration->businessTypeLabel() }}</td><td>{{ ucfirst($registration->requested_plan) }}</td><td>{{ $registration->created_at->format('d M Y') }}</td><td><span class="badge badge-{{ ['pending'=>'warning','approved'=>'success','rejected'=>'secondary'][$registration->status] }}">{{ ucfirst($registration->status) }}</span></td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('super-admin.store-registrations.show', $registration) }}">Detail</a></td></tr>
@empty<tr><td colspan="8" class="text-center text-muted py-4">Belum ada pengajuan toko.</td></tr>@endforelse
</tbody></table></div><div class="card-footer">{{ $registrations->links() }}</div></div></div></section>
@endsection
