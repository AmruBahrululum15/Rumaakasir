@extends('layouts.app')

@section('title', 'Tambah Kategori')

@section('content_header')

<div>

    <h2 class="fw-bold mb-1">
        Tambah Kategori
    </h2>

    <p class="text-muted mb-0">
        Tambahkan kategori produk baru
    </p>

</div>

@stop


@section('content')

<div class="card">

    <div class="card-header">

        <h5 class="mb-0">

            <i class="fas fa-plus-circle mr-2"></i>

            Form Kategori

        </h5>

    </div>


    <form action="{{ route('categories.store') }}"
          method="POST">

        @csrf

        <div class="card-body">

            {{-- NAMA --}}

            <div class="form-group">

                <label>
                    Nama Kategori
                    <span class="text-danger">*</span>
                </label>

                <input type="text"
                       name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}"
                       placeholder="Contoh: Makanan"
                       required>

                @error('name')

                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>

                @enderror

            </div>


            {{-- DESKRIPSI --}}

            <div class="form-group">

                <label>
                    Deskripsi
                </label>

                <textarea name="description"
                          rows="4"
                          class="form-control @error('description') is-invalid @enderror"
                          placeholder="Deskripsi kategori...">{{ old('description') }}</textarea>

                @error('description')

                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>

                @enderror

            </div>


            {{-- STATUS --}}

            <div class="form-group">

                <label>
                    Status
                    <span class="text-danger">*</span>
                </label>

                <select name="status"
                        class="form-control @error('status') is-invalid @enderror"
                        required>

                    <option value="1"
                        {{ old('status', '1') == '1' ? 'selected' : '' }}>

                        Aktif

                    </option>

                    <option value="0"
                        {{ old('status') === '0' ? 'selected' : '' }}>

                        Nonaktif

                    </option>

                </select>

                @error('status')

                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>

                @enderror

            </div>

        </div>


        <div class="card-footer">

            <a href="{{ route('categories.index') }}"
               class="btn btn-secondary">

                <i class="fas fa-arrow-left mr-1"></i>

                Kembali

            </a>


            <button type="submit"
                    class="btn btn-primary">

                <i class="fas fa-save mr-1"></i>

                Simpan

            </button>

        </div>

    </form>

</div>

@stop
