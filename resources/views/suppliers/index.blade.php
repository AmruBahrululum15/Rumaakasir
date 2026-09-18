@extends('layouts.app')

@section('title', 'Supplier')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>
        <h2 class="fw-bold mb-1">Supplier</h2>

        <p class="text-muted mb-0">
            Kelola data supplier
        </p>
    </div>

    <a href="{{ route('suppliers.create') }}"
       class="btn btn-primary">

        <i class="fas fa-plus mr-1"></i>

        Tambah Supplier

    </a>

</div>

@stop


@section('content')

<div class="card">

    <div class="card-header bg-white">

        <div class="d-flex justify-content-between align-items-center">

            <h5 class="mb-0">

                <i class="fas fa-truck mr-2"></i>

                Daftar Supplier

            </h5>

            <span class="badge badge-primary">

                {{ $suppliers->total() }} Supplier

            </span>

        </div>

    </div>


    <div class="card-body">

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


        {{-- SEARCH --}}

        <form method="GET"
              action="{{ route('suppliers.index') }}"
              class="mb-3">

            <div class="input-group">

                <input type="text"
                       name="search"
                       class="form-control"
                       value="{{ request('search') }}"
                       placeholder="Cari kode, nama, telepon, atau email...">

                <div class="input-group-append">

                    <button type="submit"
                            class="btn btn-primary">

                        <i class="fas fa-search"></i>

                    </button>

                    @if(request('search'))

                        <a href="{{ route('suppliers.index') }}"
                           class="btn btn-secondary">

                            <i class="fas fa-times"></i>

                        </a>

                    @endif

                </div>

            </div>

        </form>


        <div class="table-responsive">

            <table class="table table-hover table-bordered">

                <thead class="thead-light">

                    <tr>

                        <th width="60">No</th>

                        <th>Kode</th>

                        <th>Nama Supplier</th>

                        <th>Telepon</th>

                        <th>Email</th>

                        <th>Produk</th>

                        <th width="170">Aksi</th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($suppliers as $supplier)

                        <tr>

                            <td>
                                {{ $suppliers->firstItem() + $loop->index }}
                            </td>

                            <td>

                                <span class="badge badge-secondary">

                                    {{ $supplier->code }}

                                </span>

                            </td>

                            <td>

                                <strong>

                                    {{ $supplier->name }}

                                </strong>

                            </td>

                            <td>

                                {{ $supplier->phone ?: '-' }}

                            </td>

                            <td>

                                {{ $supplier->email ?: '-' }}

                            </td>

                            <td>

                                <span class="badge badge-info">

                                    {{ $supplier->products_count }}

                                    Produk

                                </span>

                            </td>

                            <td>

                                <a href="{{ route('suppliers.show', $supplier->id) }}"
                                   class="btn btn-sm btn-info"
                                   title="Detail">

                                    <i class="fas fa-eye"></i>

                                </a>


                                <a href="{{ route('suppliers.edit', $supplier->id) }}"
                                   class="btn btn-sm btn-warning"
                                   title="Edit">

                                    <i class="fas fa-edit"></i>

                                </a>


                                <form action="{{ route('suppliers.destroy', $supplier->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus supplier ini?')">

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

                            <td colspan="7"
                                class="text-center py-5">

                                <i class="fas fa-truck fa-3x text-muted mb-3"></i>

                                <br>

                                <span class="text-muted">

                                    Belum ada data supplier.

                                </span>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        <div class="d-flex justify-content-end mt-3">

            {{ $suppliers->links() }}

        </div>

    </div>

</div>

@stop
