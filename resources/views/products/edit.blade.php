@extends('layouts.app')

@section('title', 'Edit Produk')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h2 class="font-weight-bold mb-1">

            <i class="fas fa-edit text-primary mr-2"></i>

            Edit Produk

        </h2>

        <p class="text-muted mb-0">

            Perbarui informasi dan foto produk.

        </p>

    </div>


    <a
        href="{{ route('products.index') }}"
        class="btn btn-outline-secondary"
    >

        <i class="fas fa-arrow-left mr-1"></i>

        Kembali

    </a>

</div>

@stop


@section('content')

@if($errors->any())

<div class="alert alert-danger shadow-sm">

    <div class="font-weight-bold mb-2">

        <i class="fas fa-exclamation-triangle mr-1"></i>

        Periksa kembali data berikut:

    </div>

    <ul class="mb-0">

        @foreach($errors->all() as $error)

            <li>
                {{ $error }}
            </li>

        @endforeach

    </ul>

</div>

@endif


<form
    action="{{ route(
        'products.update',
        $product->id
    ) }}"
    method="POST"
    enctype="multipart/form-data"
>

@csrf

@method('PUT')


<div class="row">

    {{-- ===================================================== --}}
    {{-- FOTO PRODUK --}}
    {{-- ===================================================== --}}

    <div class="col-lg-5">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white">

                <h5 class="mb-0 font-weight-bold">

                    <i class="fas fa-image text-primary mr-2"></i>

                    Foto Produk

                </h5>

            </div>


            <div class="card-body">

                <div
                    class="image-upload-area"
                    id="imageUploadArea"
                >

                    @if($product->image)

                        <img
                            id="imagePreview"
                            src="{{ asset(
                                'storage/' . $product->image
                            ) }}"
                            alt="{{ $product->name }}"
                            class="image-preview"
                        >

                    @else

                        <div
                            id="imagePlaceholder"
                            class="image-placeholder"
                        >

                            <div class="upload-icon">

                                <i class="fas fa-image"></i>

                            </div>

                            <h5 class="font-weight-bold">

                                Belum Ada Gambar

                            </h5>

                            <p class="text-muted">

                                Klik untuk memilih gambar baru.

                            </p>

                        </div>


                        <img
                            id="imagePreview"
                            src=""
                            alt="Preview"
                            class="image-preview d-none"
                        >

                    @endif

                </div>


                <input
                    type="file"
                    name="image"
                    id="image"
                    class="d-none"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                >


                <div class="text-center mt-3">

                    <button
                        type="button"
                        class="btn btn-outline-primary"
                        id="chooseImage"
                    >

                        <i class="fas fa-camera mr-1"></i>

                        Ganti Foto

                    </button>

                </div>


                <div class="alert alert-light border mt-4 mb-0">

                    <i class="fas fa-info-circle text-primary mr-1"></i>

                    <small class="text-muted">

                        Kosongkan pilihan gambar jika ingin
                        mempertahankan foto produk yang sekarang.

                    </small>

                </div>

            </div>

        </div>

    </div>


    {{-- ===================================================== --}}
    {{-- INFORMASI --}}
    {{-- ===================================================== --}}

    <div class="col-lg-7">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white">

                <h5 class="mb-0 font-weight-bold">

                    <i class="fas fa-box text-primary mr-2"></i>

                    Informasi Produk

                </h5>

            </div>


            <div class="card-body">

                {{-- NAMA --}}

                <div class="form-group">

                    <label class="font-weight-bold">

                        Nama Produk

                        <span class="text-danger">*</span>

                    </label>

                    <div class="input-group">

                        <div class="input-group-prepend">

                            <span class="input-group-text bg-white">

                                <i class="fas fa-box-open text-primary"></i>

                            </span>

                        </div>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="{{ old(
                                'name',
                                $product->name
                            ) }}"
                            maxlength="255"
                            required
                        >

                    </div>

                </div>


                {{-- KATEGORI --}}

                <div class="form-group">

                    <label class="font-weight-bold">

                        Kategori

                        <span class="text-danger">*</span>

                    </label>

                    <div class="input-group">

                        <div class="input-group-prepend">

                            <span class="input-group-text bg-white">

                                <i class="fas fa-tags text-primary"></i>

                            </span>

                        </div>

                        <select
                            name="category_id"
                            class="form-control"
                            required
                        >

                            <option value="">

                                -- Pilih Kategori --

                            </option>

                            @foreach($categories as $category)

                                <option
                                    value="{{ $category->id }}"
                                    {{ old(
                                        'category_id',
                                        $product->category_id
                                    ) == $category->id
                                        ? 'selected'
                                        : ''
                                    }}
                                >

                                    {{ $category->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>


                {{-- SUPPLIER --}}

                <div class="form-group">

                    <label class="font-weight-bold">Supplier <small class="text-muted">(opsional)</small></label>

                    <div class="input-group">

                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-truck text-primary"></i></span>
                        </div>

                        <select name="supplier_id" class="form-control">
                            <option value="">-- Tanpa Supplier --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id', $product->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>

                    </div>

                </div>


                <div class="row">

                    {{-- HARGA --}}

                    <div class="col-md-6">

                        <div class="form-group">

                            <label class="font-weight-bold">

                                Harga

                                <span class="text-danger">*</span>

                            </label>

                            <div class="input-group">

                                <div class="input-group-prepend">

                                    <span class="input-group-text">

                                        Rp

                                    </span>

                                </div>

                                <input
                                    type="number"
                                    name="price"
                                    class="form-control"
                                    value="{{ old(
                                        'price',
                                        $product->price
                                    ) }}"
                                    min="0"
                                    step="1"
                                    required
                                >

                            </div>

                        </div>

                    </div>


                    {{-- STOK --}}

                    <div class="col-md-6">

                        <div class="form-group">

                            <label class="font-weight-bold">

                                Stok

                                <span class="text-danger">*</span>

                            </label>

                            <div class="input-group">

                                <div class="input-group-prepend">

                                    <span class="input-group-text">

                                        <i class="fas fa-cubes"></i>

                                    </span>

                                </div>

                                <input
                                    type="number"
                                    name="stock"
                                    class="form-control"
                                    value="{{ old(
                                        'stock',
                                        $product->stock
                                    ) }}"
                                    min="0"
                                    step="1"
                                    required
                                >

                                <div class="input-group-append">

                                    <span class="input-group-text">

                                        pcs

                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <div class="form-group">
                    <label class="font-weight-bold">Status Produk <span class="text-danger">*</span></label>
                    <select name="status" class="form-control" required>
                        <option value="active" {{ old('status', $product->status ?? ($product->is_active ? 'active' : 'inactive')) === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status', $product->status ?? ($product->is_active ? 'active' : 'inactive')) === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>


                {{-- STATUS --}}

                <div class="product-status">

                    <div>

                        <small class="text-muted d-block">

                            Status stok

                        </small>

                        @if($product->stock <= 0)

                            <span class="badge badge-danger">

                                <i class="fas fa-times-circle mr-1"></i>

                                Stok Habis

                            </span>

                        @elseif($product->stock <= 5)

                            <span class="badge badge-warning">

                                <i class="fas fa-exclamation-circle mr-1"></i>

                                Stok Menipis

                            </span>

                        @else

                            <span class="badge badge-success">

                                <i class="fas fa-check-circle mr-1"></i>

                                Stok Tersedia

                            </span>

                        @endif

                    </div>


                    <div class="text-right">

                        <small class="text-muted d-block">

                            ID Produk

                        </small>

                        <strong>

                            #{{ $product->id }}

                        </strong>

                    </div>

                </div>


                {{-- BUTTON --}}

                <div class="d-flex justify-content-end mt-4">

                    <a
                        href="{{ route('products.index') }}"
                        class="btn btn-light border mr-2 px-4"
                    >

                        Batal

                    </a>


                    <button
                        type="submit"
                        class="btn btn-primary px-4"
                    >

                        <i class="fas fa-save mr-1"></i>

                        Simpan Perubahan

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>

</form>

@stop


@section('css')

<style>

    .card {
        border-radius: 16px;
        overflow: hidden;
    }

    .image-upload-area {
        height: 380px;
        border: 2px dashed #cfd8e3;
        border-radius: 16px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        cursor: pointer;
        transition: all .2s ease;
    }

    .image-upload-area:hover {
        border-color: #0d6efd;
        background: #f1f6ff;
    }

    .image-preview {
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: #f8fafc;
    }

    .image-placeholder {
        text-align: center;
        padding: 25px;
    }

    .upload-icon {
        width: 76px;
        height: 76px;
        margin: 0 auto 18px;
        border-radius: 50%;
        background: #eaf2ff;
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
    }

    .product-status {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 14px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

</style>

@stop


@section('js')

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const imageInput =
            document.getElementById('image');

        const imagePreview =
            document.getElementById('imagePreview');

        const imageUploadArea =
            document.getElementById('imageUploadArea');

        const chooseImage =
            document.getElementById('chooseImage');


        function openFilePicker()
        {
            imageInput.click();
        }


        imageUploadArea.addEventListener(
            'click',
            openFilePicker
        );


        chooseImage.addEventListener(
            'click',
            function (event) {

                event.stopPropagation();

                openFilePicker();

            }
        );


        imageInput.addEventListener(
            'change',
            function () {

                const file =
                    this.files[0];

                if (!file) {
                    return;
                }


                const maxSize =
                    2 * 1024 * 1024;


                if (file.size > maxSize) {

                    alert(
                        'Ukuran gambar maksimal 2 MB.'
                    );

                    this.value = '';

                    return;
                }


                const allowedTypes = [
                    'image/jpeg',
                    'image/png',
                    'image/webp'
                ];


                if (
                    !allowedTypes.includes(
                        file.type
                    )
                ) {

                    alert(
                        'Format gambar harus JPG, JPEG, PNG, atau WEBP.'
                    );

                    this.value = '';

                    return;
                }


                const reader =
                    new FileReader();


                reader.onload =
                    function (event) {

                        imagePreview.src =
                            event.target.result;

                        imagePreview.classList.remove(
                            'd-none'
                        );

                    };


                reader.readAsDataURL(file);

            }
        );

    }
);

</script>

@stop
