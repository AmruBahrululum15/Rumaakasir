@extends('layouts.app')

@section('title', 'Detail Pembelian')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>
        <h2 class="fw-bold mb-1">
            Detail Pembelian
        </h2>

        <p class="text-muted mb-0">
            Informasi lengkap transaksi pembelian
        </p>
    </div>

    <div>

        <a href="{{ route('purchases.index') }}"
           class="btn btn-secondary">

            <i class="fas fa-arrow-left mr-1"></i>

            Kembali

        </a>

        <a href="{{ route('purchases.edit', $purchase->id) }}"
           class="btn btn-warning">

            <i class="fas fa-edit mr-1"></i>

            Edit

        </a>

    </div>

</div>

@stop


@section('content')

<div class="card">

    <div class="card-header bg-white">

        <h5 class="mb-0">

            <i class="fas fa-shopping-cart mr-2"></i>

            {{ $purchase->code }}

        </h5>

    </div>


    <div class="card-body">

        {{-- INFORMASI PEMBELIAN --}}

        <div class="row">

            <div class="col-md-4">

                <div class="form-group">

                    <label class="text-muted">
                        Kode Pembelian
                    </label>

                    <div>

                        <strong>
                            {{ $purchase->code }}
                        </strong>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="form-group">

                    <label class="text-muted">
                        Supplier
                    </label>

                    <div>

                        @if($purchase->supplier)

                            <strong>
                                {{ $purchase->supplier->name }}
                            </strong>

                            <br>

                            <small class="text-muted">
                                {{ $purchase->supplier->code }}
                            </small>

                        @else

                            <span class="text-muted">
                                -
                            </span>

                        @endif

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="form-group">

                    <label class="text-muted">
                        Tanggal Pembelian
                    </label>

                    <div>

                        {{ $purchase->purchase_date->format('d/m/Y') }}

                    </div>

                </div>

            </div>

        </div>


        <hr>


        {{-- DETAIL PRODUK --}}

        <h5 class="mb-3">

            <i class="fas fa-box mr-2"></i>

            Detail Produk

        </h5>


        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="thead-light">

                    <tr>

                        <th width="60">
                            No
                        </th>

                        <th>
                            Produk
                        </th>

                        <th>
                            Harga Satuan
                        </th>

                        <th>
                            Jumlah
                        </th>

                        <th>
                            Subtotal
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($purchase->details as $detail)

                        <tr>

                            <td>
                                {{ $loop->iteration }}
                            </td>


                            <td>

                                @if($detail->product)

                                    <strong>
                                        {{ $detail->product->name }}
                                    </strong>

                                @else

                                    <span class="text-muted">
                                        Produk tidak ditemukan
                                    </span>

                                @endif

                            </td>


                            <td>

                                Rp
                                {{ number_format(
                                    $detail->price,
                                    0,
                                    ',',
                                    '.'
                                ) }}

                            </td>


                            <td>

                                {{ $detail->quantity }}

                            </td>


                            <td>

                                <strong>

                                    Rp
                                    {{ number_format(
                                        $detail->subtotal,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                </strong>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5"
                                class="text-center py-4">

                                <span class="text-muted">

                                    Belum ada detail produk.

                                </span>

                            </td>

                        </tr>

                    @endforelse

                </tbody>


                <tfoot>

                    <tr>

                        <td colspan="4"
                            class="text-right">

                            <strong>
                                TOTAL PEMBELIAN
                            </strong>

                        </td>

                        <td>

                            <strong class="text-primary">

                                Rp
                                {{ number_format(
                                    $purchase->total,
                                    0,
                                    ',',
                                    '.'
                                ) }}

                            </strong>

                        </td>

                    </tr>

                </tfoot>

            </table>

        </div>


        {{-- CATATAN --}}

        @if($purchase->notes)

            <div class="mt-4">

                <label class="text-muted">
                    Catatan
                </label>

                <div class="alert alert-light border">

                    <i class="fas fa-sticky-note mr-2"></i>

                    {{ $purchase->notes }}

                </div>

            </div>

        @endif

    </div>


    <div class="card-footer">

        <a href="{{ route('purchases.index') }}"
           class="btn btn-secondary">

            <i class="fas fa-arrow-left mr-1"></i>

            Kembali

        </a>

    </div>

</div>

@stop
