@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>
        <h2 class="font-weight-bold mb-1">
            Edit Supplier
        </h2>

        <p class="text-muted mb-0">
            Perbarui informasi supplier
        </p>
    </div>

    <a href="{{ route('suppliers.index') }}"
       class="btn btn-secondary">

        <i class="fas fa-arrow-left mr-1"></i>
        Kembali

    </a>

</div>

@stop


@section('content')

<div class="row">

    <div class="col-lg-8">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white border-0">

                <h5 class="font-weight-bold mb-0">

                    <i class="fas fa-edit text-primary mr-2"></i>

                    Informasi Supplier

                </h5>

            </div>


            <form action="{{ route('suppliers.update', $supplier) }}"
                  method="POST">

                @csrf
                @method('PUT')

                <div class="card-body">

                    {{-- Kode Supplier --}}

                    <div class="form-group">

                        <label>
                            Kode Supplier
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="code"
                            value="{{ old('code', $supplier->code) }}"
                            class="form-control @error('code') is-invalid @enderror"
                            placeholder="Contoh: SUP-001"
                        >

                        @error('code')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>


                    {{-- Nama Supplier --}}

                    <div class="form-group">

                        <label>
                            Nama Supplier
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $supplier->name) }}"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="Contoh: PT Maju Jaya"
                        >

                        @error('name')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>


                    {{-- Nomor Telepon --}}

                    <div class="form-group">

                        <label>
                            Nomor Telepon
                        </label>

                        <input
                            type="text"
                            name="phone"
                            value="{{ old('phone', $supplier->phone) }}"
                            class="form-control @error('phone') is-invalid @enderror"
                            placeholder="Contoh: 081234567890"
                        >

                        @error('phone')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>


                    {{-- Alamat --}}

                    <div class="form-group">

                        <label>
                            Alamat
                        </label>

                        <textarea
                            name="address"
                            rows="4"
                            class="form-control @error('address') is-invalid @enderror"
                            placeholder="Masukkan alamat supplier"
                        >{{ old('address', $supplier->address) }}</textarea>

                        @error('address')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                </div>


                <div class="card-footer bg-white border-0">

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="fas fa-save mr-1"></i>

                        Update Supplier

                    </button>

                    <a
                        href="{{ route('suppliers.index') }}"
                        class="btn btn-light">

                        Batal

                    </a>

                </div>

            </form>

        </div>

    </div>


    <div class="col-lg-4">

        <div class="card border-0 shadow-sm">

            <div class="card-body text-center py-5">

                <i class="fas fa-truck fa-3x text-primary mb-3"></i>

                <h5 class="font-weight-bold">
                    Edit Supplier
                </h5>

                <p class="text-muted mb-0">
                    Perbarui kode, nama, nomor telepon,
                    atau alamat supplier.
                </p>

            </div>

        </div>

    </div>

</div>

@stop
