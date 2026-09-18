@extends('layouts.app')

@section('title', 'Detail Kategori')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h2 class="fw-bold mb-1">
            Detail Kategori
        </h2>

        <p class="text-muted mb-0">
            Informasi kategori
        </p>

    </div>

    <a href="{{ route('categories.index') }}"
       class="btn btn-secondary">

        <i class="fas fa-arrow-left mr-1"></i>

        Kembali

    </a>

</div>

@stop


@section('content')

<div class="card">

    <div class="card-header">

        <h5 class="mb-0">

            <i class="fas fa-tags mr-2"></i>

            {{ $category->name }}

        </h5>

    </div>


    <div class="card-body">

        <div class="row">

            <div class="col-md-6">

                <strong>Nama Kategori</strong>

                <p>
                    {{ $category->name }}
                </p>

            </div>


            <div class="col-md-6">

                <strong>Slug</strong>

                <p>
                    {{ $category->slug }}
                </p>

            </div>


            <div class="col-md-6">

                <strong>Status</strong>

                <p>

                    @if($category->status)

                        <span class="badge badge-success">
                            Aktif
                        </span>

                    @else

                        <span class="badge badge-secondary">
                            Nonaktif
                        </span>

                    @endif

                </p>

            </div>


            <div class="col-md-6">

                <strong>Jumlah Produk</strong>

                <p>

                    <span class="badge badge-info">
                        {{ $category->products->count() }} Produk
                    </span>

                </p>

            </div>


            <div class="col-md-12">

                <strong>Deskripsi</strong>

                <p>

                    {{ $category->description ?: '-' }}

                </p>

            </div>

        </div>

    </div>

</div>


{{-- PRODUK DALAM KATEGORI --}}

<div class="card">

    <div class="card-header">

        <h5 class="mb-0">

            <i class="fas fa-box mr-2"></i>

            Produk dalam Kategori

        </h5>

    </div>


    <div class="card-body">

        @if($category->products->count())

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="thead-light">

                        <tr>

                            <th>No</th>

                            <th>Nama Produk</th>

                            <th>Harga</th>

                            <th>Stok</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($category->products as $product)

                            <tr>

                                <td>
                                    {{ $loop->iteration }}
                                </td>

                                <td>
                                    <strong>
                                        {{ $product->name }}
                                    </strong>
                                </td>

                                <td>
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </td>

                                <td>
                                    {{ $product->stock }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="text-center py-4">

                <i class="fas fa-box-open fa-2x text-muted mb-2"></i>

                <p class="text-muted mb-0">
                    Belum ada produk dalam kategori ini.
                </p>

            </div>

        @endif

    </div>

</div>

@stop
