@extends('layouts.app')

@section('title', 'Pembelian')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>
        <h2 class="fw-bold mb-1">
            Pembelian
        </h2>

        <p class="text-muted mb-0">
            Kelola transaksi pembelian barang
        </p>
    </div>

    <a href="{{ route('purchases.create') }}"
       class="btn btn-primary">

        <i class="fas fa-plus mr-1"></i>

        Tambah Pembelian

    </a>

</div>

@stop


@section('content')

<div class="card">

    <div class="card-header bg-white">

        <div class="d-flex justify-content-between align-items-center">

            <h5 class="mb-0">

                <i class="fas fa-shopping-cart mr-2"></i>

                Daftar Pembelian

            </h5>

            <span class="badge badge-primary">

                {{ $purchases->total() }} Pembelian

            </span>

        </div>

    </div>


    <div class="card-body">

        {{-- Notifikasi berhasil --}}

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


        {{-- Notifikasi error --}}

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


        {{-- Notifikasi info --}}

        @if(session('info'))

            <div class="alert alert-info alert-dismissible fade show">

                <i class="fas fa-info-circle mr-2"></i>

                {{ session('info') }}

                <button type="button"
                        class="close"
                        data-dismiss="alert">

                    <span>&times;</span>

                </button>

            </div>

        @endif


        {{-- Search --}}

        <form method="GET"
              action="{{ route('purchases.index') }}"
              class="mb-4">

            <div class="input-group">

                <input type="text"
                       name="search"
                       class="form-control"
                       value="{{ request('search') }}"
                       placeholder="Cari kode pembelian atau supplier...">

                <div class="input-group-append">

                    <button class="btn btn-primary"
                            type="submit">

                        <i class="fas fa-search"></i>

                        Cari

                    </button>

                </div>

            </div>

        </form>


        {{-- Table --}}

        <div class="table-responsive">

            <table class="table table-hover table-bordered">

                <thead class="thead-light">

                    <tr>

                        <th width="60">
                            No
                        </th>

                        <th>
                            Kode Pembelian
                        </th>

                        <th>
                            Supplier
                        </th>

                        <th>
                            Tanggal
                        </th>

                        <th>
                            Total
                        </th>

                        <th width="150">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($purchases as $purchase)

                        <tr>

                            <td>

                                {{ $purchases->firstItem() + $loop->index }}

                            </td>


                            <td>

                                <strong>

                                    {{ $purchase->code }}

                                </strong>

                            </td>


                            <td>

                                @if($purchase->supplier)

                                    {{ $purchase->supplier->name }}

                                @else

                                    <span class="text-muted">

                                        -

                                    </span>

                                @endif

                            </td>


                            <td>

                                {{ $purchase->purchase_date->format('d/m/Y') }}

                            </td>


                            <td>

                                <strong>

                                    Rp
                                    {{ number_format(
                                        $purchase->total,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                </strong>

                            </td>


                            <td>

                                <a href="{{ route(
                                    'purchases.show',
                                    $purchase->id
                                ) }}"
                                   class="btn btn-sm btn-info"
                                   title="Detail">

                                    <i class="fas fa-eye"></i>

                                </a>


                                <a href="{{ route(
                                    'purchases.edit',
                                    $purchase->id
                                ) }}"
                                   class="btn btn-sm btn-warning"
                                   title="Edit">

                                    <i class="fas fa-edit"></i>

                                </a>


                                <form action="{{ route(
                                    'purchases.destroy',
                                    $purchase->id
                                ) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm(
                                          'Yakin ingin menghapus pembelian ini?'
                                      )">

                                    @csrf

                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-sm btn-danger"
                                            title="Hapus">

                                        <i class="fas fa-trash"></i>

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6"
                                class="text-center py-5">

                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>

                                <br>

                                <strong>
                                    Belum ada transaksi pembelian
                                </strong>

                                <p class="text-muted mb-3">

                                    Silakan tambahkan transaksi pembelian baru.

                                </p>

                                <a href="{{ route('purchases.create') }}"
                                   class="btn btn-primary">

                                    <i class="fas fa-plus mr-1"></i>

                                    Tambah Pembelian

                                </a>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Pagination --}}

        <div class="d-flex justify-content-end mt-3">

            {{ $purchases->links() }}

        </div>

    </div>

</div>

@stop