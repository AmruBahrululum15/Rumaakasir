@extends('layouts.app')

@section('title', 'Kategori')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>
        <h2 class="fw-bold mb-1">Kategori</h2>

        <p class="text-muted mb-0">
            Kelola kategori produk
        </p>
    </div>

    <a href="{{ route('categories.create') }}"
       class="btn btn-primary">

        <i class="fas fa-plus mr-1"></i>

        Tambah Kategori

    </a>

</div>

@stop


@section('content')

<div class="card">

    <div class="card-header bg-white">

        <div class="d-flex justify-content-between align-items-center">

            <h5 class="mb-0">

                <i class="fas fa-tags mr-2"></i>

                Daftar Kategori

            </h5>

            <span class="badge badge-primary">
                {{ $categories->total() }} Kategori
            </span>

        </div>

    </div>


    <div class="card-body">

        {{-- SUCCESS --}}
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


        {{-- ERROR --}}
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
              action="{{ route('categories.index') }}"
              class="mb-3">

            <div class="input-group">

                <input type="text"
                       name="search"
                       class="form-control"
                       placeholder="Cari kategori..."
                       value="{{ request('search') }}">

                <div class="input-group-append">

                    <button class="btn btn-primary"
                            type="submit">

                        <i class="fas fa-search"></i>

                    </button>

                    @if(request('search'))

                        <a href="{{ route('categories.index') }}"
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

                        <th>Nama Kategori</th>

                        <th>Slug</th>

                        <th>Deskripsi</th>

                        <th width="120">Produk</th>

                        <th width="100">Status</th>

                        <th width="170">Aksi</th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($categories as $category)

                        <tr>

                            <td>
                                {{ $categories->firstItem() + $loop->index }}
                            </td>


                            <td>

                                <strong>
                                    {{ $category->name }}
                                </strong>

                            </td>


                            <td>

                                <span class="text-muted">
                                    {{ $category->slug }}
                                </span>

                            </td>


                            <td>

                                @if($category->description)

                                    {{ Str::limit($category->description, 60) }}

                                @else

                                    <span class="text-muted">
                                        -
                                    </span>

                                @endif

                            </td>


                            <td>

                                <span class="badge badge-info">

                                    {{ $category->products_count }}

                                    Produk

                                </span>

                            </td>


                            <td>

                                @if($category->status)

                                    <span class="badge badge-success">
                                        Aktif
                                    </span>

                                @else

                                    <span class="badge badge-secondary">
                                        Nonaktif
                                    </span>

                                @endif

                            </td>


                            <td>

                                {{-- DETAIL --}}

                                <a href="{{ route('categories.show', $category->id) }}"
                                   class="btn btn-sm btn-info"
                                   title="Detail">

                                    <i class="fas fa-eye"></i>

                                </a>


                                {{-- EDIT --}}

                                <a href="{{ route('categories.edit', $category->id) }}"
                                   class="btn btn-sm btn-warning"
                                   title="Edit">

                                    <i class="fas fa-edit"></i>

                                </a>


                                {{-- DELETE --}}

                                <form action="{{ route('categories.destroy', $category->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">

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

                                <i class="fas fa-tags fa-3x text-muted mb-3"></i>

                                <br>

                                <span class="text-muted">

                                    Belum ada data kategori.

                                </span>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- PAGINATION --}}

        <div class="d-flex justify-content-end mt-3">

            {{ $categories->links() }}

        </div>

    </div>

</div>

@stop
