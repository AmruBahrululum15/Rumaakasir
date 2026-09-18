@extends('layouts.app')

@section('title', 'Detail Produk')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h2 class="fw-bold mb-1">
            Detail Produk
        </h2>

        <p class="text-muted mb-0">
            Informasi lengkap produk
        </p>

    </div>

    <div>

        <a href="{{ route('products.index') }}"
           class="btn btn-secondary">

            <i class="fas fa-arrow-left mr-1"></i>

            Kembali

        </a>

        <a href="{{ route('products.edit', $product->id) }}"
           class="btn btn-warning">

            <i class="fas fa-edit mr-1"></i>

            Edit

        </a>

    </div>

</div>

@stop


@section('content')

{{-- ========================================================= --}}
{{-- ALERT --}}
{{-- ========================================================= --}}

@if(session('success'))

    <div class="alert alert-success alert-dismissible fade show">

        <i class="fas fa-check-circle mr-2"></i>

        {{ session('success') }}

        <button type="button"
                class="close"
                data-dismiss="alert">

            <span>&times;</span>

        </button>

    </div>

@endif


@if(session('error'))

    <div class="alert alert-danger alert-dismissible fade show">

        <i class="fas fa-exclamation-circle mr-2"></i>

        {{ session('error') }}

        <button type="button"
                class="close"
                data-dismiss="alert">

            <span>&times;</span>

        </button>

    </div>

@endif


<div class="row">


    {{-- ========================================================= --}}
    {{-- INFORMASI PRODUK --}}
    {{-- ========================================================= --}}

    <div class="col-md-8">

        <div class="card">

            <div class="card-header bg-white">

                <h5 class="mb-0">

                    <i class="fas fa-box mr-2"></i>

                    Informasi Produk

                </h5>

            </div>


            <div class="card-body">

                {{-- NAMA PRODUK --}}

                <div class="row mb-3">

                    <div class="col-md-4 text-muted">
                        Nama Produk
                    </div>

                    <div class="col-md-8">

                        <strong class="text-dark">

                            {{ $product->name }}

                        </strong>

                    </div>

                </div>


                <hr>


                {{-- KATEGORI --}}

                <div class="row mb-3">

                    <div class="col-md-4 text-muted">
                        Kategori
                    </div>

                    <div class="col-md-8">

                        @if($product->category)

                            <span class="badge badge-info">

                                <i class="fas fa-tags mr-1"></i>

                                {{ $product->category->name }}

                            </span>

                        @else

                            <span class="text-muted">
                                -
                            </span>

                        @endif

                    </div>

                </div>


                <hr>


                {{-- SUPPLIER --}}

                <div class="row mb-3">

                    <div class="col-md-4 text-muted">
                        Supplier
                    </div>

                    <div class="col-md-8">

                        @if($product->supplier)

                            <span class="badge badge-secondary">

                                <i class="fas fa-truck mr-1"></i>

                                {{ $product->supplier->name }}

                            </span>

                        @else

                            <span class="text-muted">
                                -
                            </span>

                        @endif

                    </div>

                </div>


                <hr>


                {{-- HARGA --}}

                <div class="row mb-3">

                    <div class="col-md-4 text-muted">
                        Harga
                    </div>

                    <div class="col-md-8">

                        <strong class="text-primary">

                            Rp {{ number_format(
                                $product->price,
                                0,
                                ',',
                                '.'
                            ) }}

                        </strong>

                        <small class="text-muted">
                            / barang
                        </small>

                    </div>

                </div>


                <hr>


                {{-- STOK --}}

                <div class="row mb-3">

                    <div class="col-md-4 text-muted">
                        Stok
                    </div>

                    <div class="col-md-8">

                        @if($product->stock <= 5)

                            <span class="badge badge-danger"
                                  style="font-size: 14px;">

                                {{ $product->stock }}

                            </span>

                            <small class="text-danger ml-2">

                                Stok rendah

                            </small>

                        @elseif($product->stock <= 10)

                            <span class="badge badge-warning"
                                  style="font-size: 14px;">

                                {{ $product->stock }}

                            </span>

                            <small class="text-warning ml-2">

                                Stok hampir habis

                            </small>

                        @else

                            <span class="badge badge-success"
                                  style="font-size: 14px;">

                                {{ $product->stock }}

                            </span>

                            <small class="text-success ml-2">

                                Stok aman

                            </small>

                        @endif

                    </div>

                </div>


                <hr>


                {{-- NILAI TOTAL STOK --}}

                <div class="row mb-3">

                    <div class="col-md-4 text-muted">
                        Nilai Persediaan
                    </div>

                    <div class="col-md-8">

                        <strong>

                            Rp {{ number_format(
                                $product->price * $product->stock,
                                0,
                                ',',
                                '.'
                            ) }}

                        </strong>

                    </div>

                </div>


                <hr>

                {{-- SUPPLIER --}}

                <div class="row mb-3">

                    <div class="col-md-4 text-muted">
                        Supplier
                    </div>

                    <div class="col-md-8">
                        {{ $product->supplier->name ?? 'Tanpa Supplier' }}
                    </div>

                </div>

                <hr>


                {{-- STATUS PRODUK --}}

                <div class="row mb-3">

                    <div class="col-md-4 text-muted">
                        Status Produk
                    </div>

                    <div class="col-md-8">

                        @if(isset($product->status))

                            @if($product->status === 'active')

                                <span class="badge badge-success">

                                    <i class="fas fa-check-circle mr-1"></i>

                                    Aktif

                                </span>

                            @else

                                <span class="badge badge-secondary">

                                    <i class="fas fa-ban mr-1"></i>

                                    Nonaktif

                                </span>

                            @endif

                        @else

                            <span class="badge badge-success">

                                <i class="fas fa-check-circle mr-1"></i>

                                Aktif

                            </span>

                        @endif

                    </div>

                </div>


                <hr>


                {{-- ID PRODUK --}}

                <div class="row mb-3">

                    <div class="col-md-4 text-muted">
                        ID Produk
                    </div>

                    <div class="col-md-8">

                        <span class="badge badge-light border">

                            #{{ $product->id }}

                        </span>

                    </div>

                </div>


                {{-- TANGGAL DIBUAT --}}

                @if($product->created_at)

                    <hr>

                    <div class="row mb-3">

                        <div class="col-md-4 text-muted">
                            Dibuat
                        </div>

                        <div class="col-md-8">

                            {{ $product->created_at->format('d-m-Y H:i') }}

                        </div>

                    </div>

                @endif


                {{-- TERAKHIR DIPERBARUI --}}

                @if($product->updated_at)

                    <hr>

                    <div class="row">

                        <div class="col-md-4 text-muted">
                            Terakhir diperbarui
                        </div>

                        <div class="col-md-8">

                            {{ $product->updated_at->format('d-m-Y H:i') }}

                        </div>

                    </div>

                @endif

            </div>

        </div>

    </div>



    {{-- ========================================================= --}}
    {{-- RINGKASAN --}}
    {{-- ========================================================= --}}

    <div class="col-md-4">


        {{-- STOK CARD --}}

        <div class="card">

            <div class="card-header bg-white">

                <h5 class="mb-0">

                    <i class="fas fa-warehouse mr-2"></i>

                    Ringkasan Stok

                </h5>

            </div>


            <div class="card-body text-center">

                <div class="mb-3">

                    <i class="fas fa-box-open fa-3x text-primary"></i>

                </div>


                <h2 class="font-weight-bold">

                    {{ $product->stock }}

                </h2>


                <p class="text-muted mb-0">

                    Unit tersedia

                </p>

            </div>

        </div>



        {{-- HARGA CARD --}}

        <div class="card">

            <div class="card-header bg-white">

                <h5 class="mb-0">

                    <i class="fas fa-money-bill-wave mr-2"></i>

                    Harga Produk

                </h5>

            </div>


            <div class="card-body text-center">

                <h3 class="font-weight-bold text-success">

                    Rp {{ number_format(
                        $product->price,
                        0,
                        ',',
                        '.'
                    ) }}

                </h3>


                <p class="text-muted mb-0">

                    Harga jual / unit

                </p>

            </div>

        </div>



        {{-- NILAI PERSEDIAAN --}}

        <div class="card">

            <div class="card-header bg-white">

                <h5 class="mb-0">

                    <i class="fas fa-calculator mr-2"></i>

                    Nilai Persediaan

                </h5>

            </div>


            <div class="card-body text-center">

                <h4 class="font-weight-bold text-primary">

                    Rp {{ number_format(
                        $product->price * $product->stock,
                        0,
                        ',',
                        '.'
                    ) }}

                </h4>


                <p class="text-muted mb-0">

                    Harga × Stok

                </p>

            </div>

        </div>



        {{-- AKSI --}}

        <div class="card">

            <div class="card-header bg-white">

                <h5 class="mb-0">

                    <i class="fas fa-cogs mr-2"></i>

                    Aksi

                </h5>

            </div>


            <div class="card-body">


                <a href="{{ route(
                    'products.edit',
                    $product->id
                ) }}"
                   class="btn btn-warning btn-block">

                    <i class="fas fa-edit mr-1"></i>

                    Edit Produk

                </a>


                <a href="{{ route(
                    'products.index'
                ) }}"
                   class="btn btn-secondary btn-block">

                    <i class="fas fa-arrow-left mr-1"></i>

                    Kembali ke Produk

                </a>


            </div>

        </div>

    </div>

</div>

@stop
