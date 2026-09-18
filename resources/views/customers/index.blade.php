@extends('layouts.app')

@section('title', 'Pelanggan')

@section('content_header')
    <h1>Pelanggan</h1>
    <p class="text-muted">Data pelanggan berasal dari pemesanan QR Meja.</p>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('customers.index') }}" class="mb-3">
                <div class="input-group">
                    <input name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama, nomor telepon, atau alamat">
                    <div class="input-group-append"><button class="btn btn-primary" type="submit">Cari</button></div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Nama</th><th>Nomor Telepon</th><th>Alamat</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->phone }}</td>
                                <td>{{ $customer->address ?: '-' }}</td>
                                <td class="text-nowrap">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('customers.show', $customer) }}">Detail</a>
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('customers.edit', $customer) }}">Edit</a>
                                    <form class="d-inline" method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('Hapus pelanggan ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Belum ada pelanggan dari order QR Meja.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $customers->links() }}</div>
        </div>
    </div>
@stop
