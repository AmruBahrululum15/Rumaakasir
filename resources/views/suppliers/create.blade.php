@extends('layouts.app')

@section('title', 'Tambah Supplier')

@section('content_header')

<div>

    <h2 class="fw-bold mb-1">
        Tambah Supplier
    </h2>

    <p class="text-muted mb-0">
        Tambahkan supplier baru
    </p>

</div>

@stop


@section('content')

<div class="card">

    <div class="card-header">

        <h5 class="mb-0">

            <i class="fas fa-truck mr-2"></i>

            Form Supplier

        </h5>

    </div>


    <form action="{{ route('suppliers.store') }}"
          method="POST">

        @csrf

        <div class="card-body">


            {{-- KODE --}}

            <div class="form-group">

                <label>
                    Kode Supplier
                </label>

                <input type="text"
                       class="form-control"
                       value="{{ $code }}"
                       readonly>

                <small class="text-muted">
                    Kode supplier dibuat otomatis oleh sistem.
                </small>

            </div>


            {{-- NAMA --}}

            <div class="form-group">

                <label>

                    Nama Supplier

                    <span class="text-danger">*</span>

                </label>

                <input type="text"
                       name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}"
                       placeholder="Nama perusahaan supplier"
                       required>

                @error('name')

                    <div class="invalid-feedback">

                        {{ $message }}

                    </div>

                @enderror

            </div>


            {{-- TELEPON --}}

            <div class="form-group">

                <label>
                    Nomor Telepon
                </label>

                <input type="text"
                       name="phone"
                       class="form-control @error('phone') is-invalid @enderror"
                       value="{{ old('phone') }}"
                       placeholder="08xxxxxxxxxx">

                @error('phone')

                    <div class="invalid-feedback">

                        {{ $message }}

                    </div>

                @enderror

            </div>


            {{-- EMAIL --}}

            <div class="form-group">

                <label>
                    Email
                </label>

                <input type="email"
                       name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}"
                       placeholder="supplier@email.com">

                @error('email')

                    <div class="invalid-feedback">

                        {{ $message }}

                    </div>

                @enderror

            </div>


            {{-- ALAMAT --}}

            <div class="form-group">

                <label>
                    Alamat
                </label>

                <textarea name="address"
                          rows="4"
                          class="form-control @error('address') is-invalid @enderror"
                          placeholder="Alamat supplier...">{{ old('address') }}</textarea>

                @error('address')

                    <div class="invalid-feedback">

                        {{ $message }}

                    </div>

                @enderror

            </div>

        </div>


        <div class="card-footer">

            <a href="{{ route('suppliers.index') }}"
               class="btn btn-secondary">

                <i class="fas fa-arrow-left mr-1"></i>

                Kembali

            </a>


            <button type="submit"
                    class="btn btn-primary">

                <i class="fas fa-save mr-1"></i>

                Simpan Supplier

            </button>

        </div>

    </form>

</div>

@stop
