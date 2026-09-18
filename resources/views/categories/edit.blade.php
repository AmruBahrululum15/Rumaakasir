@extends('layouts.app')

@section('title', 'Edit Kategori')

@section('content_header')

<div>

    <h2 class="fw-bold mb-1">
        Edit Kategori
    </h2>

    <p class="text-muted mb-0">
        Perbarui data kategori
    </p>

</div>

@stop


@section('content')

<div class="card">

    <div class="card-header">

        <h5 class="mb-0">

            <i class="fas fa-edit mr-2"></i>

            Edit Kategori

        </h5>

    </div>


    <form action="{{ route('categories.update', $category->id) }}"
          method="POST">

        @csrf

        @method('PUT')


        <div class="card-body">

            <div class="form-group">

                <label>
                    Nama Kategori
                    <span class="text-danger">*</span>
                </label>

                <input type="text"
                       name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $category->name) }}"
                       required>

                @error('name')

                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>

                @enderror

            </div>


            <div class="form-group">

                <label>
                    Slug
                </label>

                <input type="text"
                       class="form-control"
                       value="{{ $category->slug }}"
                       readonly>

                <small class="text-muted">
                    Slug dibuat otomatis berdasarkan nama kategori.
                </small>

            </div>


            <div class="form-group">

                <label>
                    Deskripsi
                </label>

                <textarea name="description"
                          rows="4"
                          class="form-control">{{ old('description', $category->description) }}</textarea>

            </div>


            <div class="form-group">

                <label>
                    Status
                    <span class="text-danger">*</span>
                </label>

                <select name="status"
                        class="form-control"
                        required>

                    <option value="1"
                        {{ old('status', $category->status) == 1 ? 'selected' : '' }}>

                        Aktif

                    </option>

                    <option value="0"
                        {{ old('status', $category->status) == 0 ? 'selected' : '' }}>

                        Nonaktif

                    </option>

                </select>

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

                Simpan Perubahan

            </button>

        </div>

    </form>

</div>

@stop
