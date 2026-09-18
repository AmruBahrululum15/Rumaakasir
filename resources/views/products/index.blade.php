@extends('layouts.app')

@section('title', 'Produk')

@section('content_header')

<div class="d-flex justify-content-between align-items-center flex-wrap">

    <div>

        <h2 class="font-weight-bold mb-1">
            <i class="fas fa-box-open text-primary mr-2"></i>
            Produk
        </h2>

        <p class="text-muted mb-0">
            Kelola katalog produk, harga, stok, kategori, dan gambar produk.
        </p>

    </div>


    <div class="mt-2 mt-md-0">

        <a
            href="{{ route('products.create') }}"
            class="btn btn-primary shadow-sm px-4"
        >

            <i class="fas fa-plus mr-1"></i>

            Tambah Produk

        </a>

    </div>

</div>

@stop


@section('content')

{{-- ========================================================= --}}
{{-- ALERT --}}
{{-- ========================================================= --}}

@if(session('success'))

<div class="alert alert-success alert-dismissible fade show shadow-sm">

    <i class="fas fa-check-circle mr-2"></i>

    {{ session('success') }}

    <button
        type="button"
        class="close"
        data-dismiss="alert"
    >
        <span>&times;</span>
    </button>

</div>

@endif


@if(session('error'))

<div class="alert alert-danger alert-dismissible fade show shadow-sm">

    <i class="fas fa-exclamation-circle mr-2"></i>

    {{ session('error') }}

    <button
        type="button"
        class="close"
        data-dismiss="alert"
    >
        <span>&times;</span>
    </button>

</div>

@endif


{{-- ========================================================= --}}
{{-- SEARCH --}}
{{-- ========================================================= --}}

<div class="card border-0 shadow-sm mb-4">

    <div class="card-body">

        <form
            action="{{ route('products.index') }}"
            method="GET"
        >

            <div class="row align-items-end">

                <div class="col-md-10">

                    <label class="font-weight-bold">

                        <i class="fas fa-search mr-1 text-primary"></i>

                        Cari Produk

                    </label>

                    <div class="input-group">

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            value="{{ $search }}"
                            placeholder="Cari nama produk atau kategori..."
                        >

                        <div class="input-group-append">

                            <button
                                type="submit"
                                class="btn btn-primary px-4"
                            >

                                <i class="fas fa-search mr-1"></i>

                                Cari

                            </button>

                        </div>

                    </div>

                </div>


                <div class="col-md-2 mt-3 mt-md-0">

                    @if($search)

                        <a
                            href="{{ route('products.index') }}"
                            class="btn btn-outline-secondary btn-block"
                        >

                            <i class="fas fa-times mr-1"></i>

                            Reset

                        </a>

                    @endif

                </div>

            </div>

        </form>

    </div>

</div>


{{-- ========================================================= --}}
{{-- HEADER KATALOG --}}
{{-- ========================================================= --}}

<div class="d-flex justify-content-between align-items-center mb-3">

    <div>

        <h5 class="font-weight-bold mb-1">

            Daftar Produk

        </h5>

        <small class="text-muted">

            Menampilkan
            <strong>
                {{ $products->total() }}
            </strong>
            produk

        </small>

    </div>


    <div>

        <span class="badge badge-light border px-3 py-2">

            <i class="fas fa-layer-group mr-1 text-primary"></i>

            {{ $products->count() }}
            Produk

        </span>

    </div>

</div>


{{-- ========================================================= --}}
{{-- PRODUCT GRID --}}
{{-- ========================================================= --}}

<div class="row">

    @forelse($products as $product)

        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">

            <div class="product-card">

                {{-- IMAGE --}}

                <div class="product-image-wrapper">

                    @if($product->image)

                        <img
                            src="{{ asset('storage/' . $product->image) }}"
                            alt="{{ $product->name }}"
                            class="product-image"
                        >

                    @else

                        <div class="product-placeholder">

                            <i class="fas fa-image"></i>

                            <span>
                                Belum ada gambar
                            </span>

                        </div>

                    @endif


                    {{-- STOCK BADGE --}}

                    @if($product->stock <= 0)

                        <span class="stock-badge stock-danger">

                            <i class="fas fa-times-circle mr-1"></i>

                            Habis

                        </span>

                    @elseif($product->stock <= 5)

                        <span class="stock-badge stock-warning">

                            <i class="fas fa-exclamation-circle mr-1"></i>

                            Menipis

                        </span>

                    @else

                        <span class="stock-badge stock-success">

                            <i class="fas fa-check-circle mr-1"></i>

                            Tersedia

                        </span>

                    @endif

                </div>


                {{-- CONTENT --}}

                <div class="product-content">

                    <div class="mb-2">

                        <span class="category-badge">

                            <i class="fas fa-tag mr-1"></i>

                            {{ $product->category->name ?? 'Tanpa Kategori' }}

                        </span>

                    </div>

                    <small class="text-muted d-block mb-2">
                        <i class="fas fa-truck mr-1"></i>
                        {{ $product->supplier->name ?? 'Tanpa Supplier' }}
                    </small>

                    @if($product->is_active)
                        <span class="badge badge-success mb-2">Aktif</span>
                    @else
                        <span class="badge badge-secondary mb-2">Nonaktif</span>
                    @endif


                    <h5
                        class="product-name"
                        title="{{ $product->name }}"
                    >

                        {{ $product->name }}

                    </h5>


                    <div class="product-price">

                        Rp {{ number_format(
                            $product->price,
                            0,
                            ',',
                            '.'
                        ) }}

                    </div>


                    <div class="product-stock">

                        <div>

                            <i class="fas fa-box mr-1"></i>

                            Stok

                        </div>

                        <strong>

                            {{ number_format(
                                $product->stock,
                                0,
                                ',',
                                '.'
                            ) }}

                            pcs

                        </strong>

                    </div>

                </div>


                {{-- ACTION --}}

                <div class="product-actions">

                    <a
                        href="{{ route(
                            'products.edit',
                            $product->id
                        ) }}"
                        class="btn btn-outline-primary btn-sm flex-fill"
                    >

                        <i class="fas fa-edit mr-1"></i>

                        Edit

                    </a>


                    <button
                        type="button"
                        class="btn btn-outline-danger btn-sm ml-2"
                        onclick="confirmDelete(
                            {{ $product->id }},
                            '{{ addslashes($product->name) }}'
                        )"
                        title="Hapus"
                    >

                        <i class="fas fa-trash"></i>

                    </button>


                    <form
                        id="delete-form-{{ $product->id }}"
                        action="{{ route(
                            'products.destroy',
                            $product->id
                        ) }}"
                        method="POST"
                        class="d-none"
                    >

                        @csrf

                        @method('DELETE')

                    </form>

                </div>

            </div>

        </div>

    @empty

        <div class="col-12">

            <div class="empty-state">

                <div class="empty-icon">

                    <i class="fas fa-box-open"></i>

                </div>

                <h4>
                    Belum Ada Produk
                </h4>

                <p class="text-muted">

                    @if($search)

                        Produk dengan kata
                        "<strong>{{ $search }}</strong>"
                        tidak ditemukan.

                    @else

                        Belum ada produk yang ditambahkan
                        ke dalam sistem.

                    @endif

                </p>


                @if(!$search)

                    <a
                        href="{{ route('products.create') }}"
                        class="btn btn-primary px-4"
                    >

                        <i class="fas fa-plus mr-1"></i>

                        Tambah Produk Pertama

                    </a>

                @else

                    <a
                        href="{{ route('products.index') }}"
                        class="btn btn-outline-primary"
                    >

                        <i class="fas fa-arrow-left mr-1"></i>

                        Kembali ke Produk

                    </a>

                @endif

            </div>

        </div>

    @endforelse

</div>


{{-- ========================================================= --}}
{{-- PAGINATION --}}
{{-- ========================================================= --}}

@if ($products->hasPages())
    <div class="pagination-wrapper">
        <div class="pagination-info">
            Menampilkan
            <strong>{{ $products->firstItem() }}</strong>
            -
            <strong>{{ $products->lastItem() }}</strong>
            dari
            <strong>{{ $products->total() }}</strong>
            produk
        </div>

        <div class="pagination">

            {{-- Previous --}}
            @if ($products->onFirstPage())
                <span class="page-item disabled">
                    <span class="page-link">
                        <i class="fas fa-chevron-left"></i>
                        <span>Sebelumnya</span>
                    </span>
                </span>
            @else
                <a href="{{ $products->previousPageUrl() }}"
                   class="page-item">
                    <span class="page-link">
                        <i class="fas fa-chevron-left"></i>
                        <span>Sebelumnya</span>
                    </span>
                </a>
            @endif


            {{-- Nomor halaman --}}
            @php
                $current = $products->currentPage();
                $last = $products->lastPage();

                $start = max(1, $current - 2);
                $end = min($last, $current + 2);
            @endphp

            @if ($start > 1)
                <a href="{{ $products->url(1) }}"
                   class="page-number">
                    1
                </a>

                @if ($start > 2)
                    <span class="page-dots">...</span>
                @endif
            @endif


            @for ($page = $start; $page <= $end; $page++)
                @if ($page == $current)
                    <span class="page-number active">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $products->url($page) }}"
                       class="page-number">
                        {{ $page }}
                    </a>
                @endif
            @endfor


            @if ($end < $last)
                @if ($end < $last - 1)
                    <span class="page-dots">...</span>
                @endif

                <a href="{{ $products->url($last) }}"
                   class="page-number">
                    {{ $last }}
                </a>
            @endif


            {{-- Next --}}
            @if ($products->hasMorePages())
                <a href="{{ $products->nextPageUrl() }}"
                   class="page-item">
                    <span class="page-link">
                        <span>Berikutnya</span>
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </a>
            @else
                <span class="page-item disabled">
                    <span class="page-link">
                        <span>Berikutnya</span>
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </span>
            @endif

        </div>
    </div>
@endif

@stop


@section('css')

<style>

    .product-card {
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #e9ecef;
        box-shadow: 0 4px 18px rgba(0,0,0,.05);
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: all .25s ease;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0,0,0,.12);
        border-color: #dce4f7;
    }

    .product-image-wrapper {
        height: 220px;
        background: #f6f8fb;
        position: relative;
        overflow: hidden;
    }

    .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .35s ease;
    }

    .product-card:hover .product-image {
        transform: scale(1.05);
    }

    .product-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #adb5bd;
    }

    .product-placeholder i {
        font-size: 48px;
        margin-bottom: 10px;
    }

    .product-placeholder span {
        font-size: 13px;
    }

    .stock-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        padding: 6px 10px;
        border-radius: 30px;
        font-size: 11px;
        font-weight: 700;
        background: rgba(255,255,255,.95);
        box-shadow: 0 3px 10px rgba(0,0,0,.08);
    }

    .stock-success {
        color: #198754;
    }

    .stock-warning {
        color: #d39e00;
    }

    .stock-danger {
        color: #dc3545;
    }

    .product-content {
        padding: 18px;
        flex: 1;
    }

    .category-badge {
        background: #eef4ff;
        color: #0d6efd;
        border-radius: 30px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 600;
    }

    .product-name {
        font-size: 17px;
        font-weight: 700;
        margin: 13px 0 8px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-price {
        font-size: 20px;
        font-weight: 800;
        color: #0d6efd;
        margin-bottom: 15px;
    }

    .product-stock {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8f9fa;
        padding: 9px 11px;
        border-radius: 9px;
        font-size: 13px;
        color: #6c757d;
    }

    .product-stock strong {
        color: #212529;
    }

    .product-actions {
        padding: 14px 18px 18px;
        display: flex;
    }

    .empty-state {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 16px;
        padding: 70px 20px;
        text-align: center;
        box-shadow: 0 4px 18px rgba(0,0,0,.04);
    }

    .empty-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: #eef4ff;
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 34px;
        margin: 0 auto 20px;
    }

    .empty-state h4 {
        font-weight: 700;
    }

    @media(max-width: 767px) {

        .product-image-wrapper {
            height: 200px;
        }

    }
.pagination-wrapper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 18px 4px 4px;
    flex-wrap: wrap;
}

.pagination-info {
    color: #6b7280;
    font-size: 13px;
    white-space: nowrap;
}

.pagination-info strong {
    color: #111827;
    font-weight: 600;
}

.pagination {
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 0;
}

.page-number,
.page-item .page-link {
    height: 38px;
    min-width: 38px;
    padding: 0 12px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border: 1px solid #e5e7eb;
    border-radius: 9px;

    background: #ffffff;
    color: #374151;

    font-size: 13px;
    font-weight: 500;

    text-decoration: none;

    transition:
        background .2s ease,
        color .2s ease,
        border-color .2s ease,
        transform .2s ease;
}

.page-number:hover,
.page-item:hover .page-link {
    background: #f3f4f6;
    border-color: #d1d5db;
    color: #111827;
    text-decoration: none;
}

.page-number.active {
    background: #111827;
    border-color: #111827;
    color: #ffffff;
    font-weight: 600;
}

.page-number.active:hover {
    background: #111827;
    color: #ffffff;
}

.page-item {
    display: inline-flex;
    text-decoration: none;
}

.page-item .page-link {
    gap: 7px;
}

.page-item .page-link i {
    font-size: 10px;
}

.page-item.disabled {
    pointer-events: none;
    opacity: .45;
}

.page-dots {
    width: 28px;
    height: 38px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    color: #9ca3af;
    font-size: 14px;
}


/* =========================================
   RESPONSIVE
========================================= */

@media (max-width: 768px) {

    .pagination-wrapper {
        flex-direction: column;
        align-items: stretch;
    }

    .pagination-info {
        text-align: center;
    }

    .pagination {
        justify-content: center;
        flex-wrap: wrap;
    }

    .page-item .page-link span {
        display: none;
    }

    .page-item .page-link {
        width: 38px;
        padding: 0;
    }
}

</style>

@stop


@section('js')

<script>

function confirmDelete(id, name)
{
    if (
        confirm(
            'Apakah Anda yakin ingin menghapus produk "' +
            name +
            '"?'
        )
    ) {

        document
            .getElementById(
                'delete-form-' + id
            )
            .submit();

    }
}

</script>

@stop
