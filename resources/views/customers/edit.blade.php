@extends('layouts.app')

@section('title', 'Edit Pelanggan')

@section('content_header')
    <h1>Edit Pelanggan</h1>
@stop

@section('content')
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('customers.update', $customer) }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label for="name">Nama</label>
                <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $customer->name) }}" required maxlength="255">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="phone">Nomor Telepon</label>
                <input id="phone" type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $customer->phone) }}" required maxlength="30">
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="address">Alamat / Data kontak</label>
                <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" maxlength="255">{{ old('address', $customer->address) }}</textarea>
                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <a class="btn btn-light" href="{{ route('customers.show', $customer) }}">Batal</a>
            <button class="btn btn-primary" type="submit">Simpan</button>
        </form>
    </div></div>
@stop
