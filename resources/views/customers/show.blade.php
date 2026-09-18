@extends('layouts.app')

@section('title', 'Detail Pelanggan')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Detail Pelanggan</h1>
        <a class="btn btn-outline-secondary" href="{{ route('customers.edit', $customer) }}">Edit</a>
    </div>
@stop

@section('content')
    <div class="card mb-3"><div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Nama</dt><dd class="col-sm-9">{{ $customer->name }}</dd>
            <dt class="col-sm-3">Nomor Telepon</dt><dd class="col-sm-9">{{ $customer->phone }}</dd>
            <dt class="col-sm-3">Alamat / Data kontak</dt><dd class="col-sm-9">{{ $customer->address ?: '-' }}</dd>
        </dl>
    </div></div>
    <a class="btn btn-link mt-2" href="{{ route('customers.index') }}">Kembali ke daftar pelanggan</a>
@stop
