<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Menu Pesanan</title>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <style>

        /* =========================================================
           RESET
        ========================================================= */

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f7f8fc;
            color: #111827;
            min-height: 100vh;
        }

        button,
        input {
            font-family: inherit;
        }

        button {
            border: none;
        }

        a {
            text-decoration: none;
        }


        /* =========================================================
           HEADER
        ========================================================= */

        .hero {
            position: relative;
            overflow: hidden;

            background:
                radial-gradient(
                    circle at 85% 15%,
                    rgba(245, 158, 11, .18),
                    transparent 32%
                ),
                radial-gradient(
                    circle at 10% 80%,
                    rgba(251, 146, 60, .12),
                    transparent 30%
                ),
                linear-gradient(
                    135deg,
                    #fffdf8 0%,
                    #fff8ed 48%,
                    #ffffff 100%
                );

            border-bottom-left-radius: 32px;
            border-bottom-right-radius: 32px;

            box-shadow:
                0 12px 40px rgba(15, 23, 42, .07);
        }

        .hero::before {
            content: "";
            position: absolute;

            width: 280px;
            height: 280px;

            border-radius: 50%;

            background: rgba(245, 158, 11, .08);

            top: -160px;
            right: -90px;
        }

        .hero::after {
            content: "";

            position: absolute;

            width: 180px;
            height: 180px;

            border-radius: 50%;

            background: rgba(251, 146, 60, .06);

            bottom: -110px;
            left: -70px;
        }

        .hero-inner {
            position: relative;
            z-index: 2;

            max-width: 1180px;

            margin: auto;

            padding:
                42px 24px
                46px;
        }

        .brand-label {
            display: inline-flex;

            align-items: center;
            gap: 8px;

            font-size: 12px;

            font-weight: 800;

            letter-spacing: 2px;

            color: #ea580c;

            margin-bottom: 10px;
        }

        .brand-label::before {
            content: "";

            width: 7px;
            height: 7px;

            border-radius: 50%;

            background: #f97316;

            box-shadow:
                0 0 0 5px rgba(249, 115, 22, .10);
        }

        .hero-title {
            font-family: 'Poppins', sans-serif;

            font-size: clamp(34px, 5vw, 54px);

            line-height: 1.08;

            font-weight: 800;

            color: #111827;

            letter-spacing: -.8px;

            margin-bottom: 12px;
        }

        .hero-description {
            max-width: 650px;

            font-size: 15px;

            line-height: 1.7;

            color: #64748b;

            margin-bottom: 22px;
        }

        .table-badge {
            display: inline-flex;

            align-items: center;

            gap: 10px;

            padding: 10px 16px;

            border-radius: 999px;

            background: #ffffff;

            color: #1f2937;

            border: 1px solid #e5e7eb;

            box-shadow:
                0 6px 20px rgba(15, 23, 42, .07);

            font-size: 14px;

            font-weight: 700;
        }

        .table-icon {
            width: 32px;
            height: 32px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background: #fff1df;

            color: #f97316;
        }


        /* =========================================================
           MAIN
        ========================================================= */

        .container {
            max-width: 1180px;

            margin: auto;

            padding:
                28px 24px
                110px;
        }


        /* =========================================================
           SEARCH
        ========================================================= */

        .search-wrapper {
            position: relative;

            margin-bottom: 26px;
        }

        .search-wrapper i {
            position: absolute;

            left: 18px;
            top: 50%;

            transform: translateY(-50%);

            color: #94a3b8;

            font-size: 17px;
        }

        #searchMenu {
            width: 100%;

            height: 56px;

            border: 1px solid #e5e7eb;

            border-radius: 18px;

            background: #ffffff;

            padding:
                0 20px
                0 50px;

            outline: none;

            color: #111827;

            font-size: 14px;

            box-shadow:
                0 6px 25px rgba(15, 23, 42, .05);

            transition: .2s;
        }

        #searchMenu:focus {
            border-color: #f59e0b;

            box-shadow:
                0 0 0 4px rgba(245, 158, 11, .10);
        }


        /* =========================================================
           SECTION TITLE
        ========================================================= */

        .section-heading {
            display: flex;

            align-items: flex-end;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 14px;
        }

        .section-title {
            font-family: 'Poppins', sans-serif;

            font-size: 22px;

            font-weight: 800;

            color: #111827;
        }

        .section-subtitle {
            margin-top: 4px;

            color: #94a3b8;

            font-size: 12px;
        }

        .menu-count {
            color: #64748b;

            font-size: 12px;

            white-space: nowrap;
        }


        /* =========================================================
           BEST SELLER
        ========================================================= */

        .best-section {
            margin-bottom: 32px;
        }

        .best-header {
            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 14px;
        }

        .best-title {
            display: flex;

            align-items: center;

            gap: 9px;

            font-family: 'Poppins', sans-serif;

            font-size: 21px;

            font-weight: 800;
        }

        .best-title-icon {
            width: 38px;
            height: 38px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 12px;

            background: #fff7ed;

            color: #f97316;
        }

        .best-scroll {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 16px;
        }

        .best-card {
            position: relative;

            overflow: hidden;

            background: #ffffff;

            border: 1px solid #edf0f4;

            border-radius: 20px;

            box-shadow:
                0 8px 25px rgba(15, 23, 42, .05);

            transition:
                transform .2s,
                box-shadow .2s;
        }

        .best-card:hover {
            transform: translateY(-3px);

            box-shadow:
                0 15px 35px rgba(15, 23, 42, .09);
        }

        .best-image {
            width: 100%;

            height: 150px;

            object-fit: cover;

            display: block;
        }

        .best-content {
            padding: 13px;
        }

        .best-badge {
            position: absolute;

            top: 10px;
            left: 10px;

            background: #f97316;

            color: #ffffff;

            padding: 6px 9px;

            border-radius: 999px;

            font-size: 9px;

            font-weight: 800;

            letter-spacing: .4px;
        }

        .best-name {
            font-family: 'Poppins', sans-serif;

            font-size: 14px;

            font-weight: 700;

            color: #111827;

            display: -webkit-box;

            -webkit-line-clamp: 2;

            -webkit-box-orient: vertical;

            overflow: hidden;

            min-height: 40px;

            margin-bottom: 8px;
        }

        .best-price {
            font-size: 15px;

            font-weight: 800;

            color: #111827;
        }


        /* =========================================================
           CATEGORY
        ========================================================= */

        .category-wrapper {
            margin-bottom: 28px;
        }

        .category-list {
            display: flex;

            gap: 9px;

            overflow-x: auto;

            padding:
                3px 2px
                8px;

            scrollbar-width: none;
        }

        .category-list::-webkit-scrollbar {
            display: none;
        }

        .category-btn {
            flex: 0 0 auto;

            display: inline-flex;

            align-items: center;

            gap: 7px;

            padding: 10px 16px;

            border-radius: 999px;

            background: #ffffff;

            color: #64748b;

            border: 1px solid #e5e7eb;

            cursor: pointer;

            font-size: 12px;

            font-weight: 700;

            transition: .2s;
        }

        .category-btn:hover {
            border-color: #fbbf24;

            color: #ea580c;

            background: #fffbeb;
        }

        .category-btn.active {
            background: #111827;

            color: #ffffff;

            border-color: #111827;

            box-shadow:
                0 7px 18px rgba(17, 24, 39, .15);
        }


        /* =========================================================
           PRODUCT GRID
        ========================================================= */

        .product-grid {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 18px;
        }

        .product-card {
            position: relative;

            background: #ffffff;

            border-radius: 20px;

            overflow: hidden;

            border: 1px solid #edf0f4;

            box-shadow:
                0 7px 25px rgba(15, 23, 42, .045);

            transition:
                transform .2s,
                box-shadow .2s;

            display: flex;

            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-4px);

            box-shadow:
                0 15px 35px rgba(15, 23, 42, .09);
        }

        .product-card.hidden {
            display: none;
        }

        .product-image-wrapper {
            position: relative;

            width: 100%;

            height: 190px;

            overflow: hidden;

            background:
                linear-gradient(
                    135deg,
                    #f8fafc,
                    #eef2f7
                );
        }

        .product-image {
            width: 100%;
            height: 100%;

            object-fit: cover;

            display: block;

            transition:
                transform .35s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.04);
        }

        .product-image-placeholder {
            width: 100%;
            height: 100%;

            display: flex;

            align-items: center;
            justify-content: center;

            color: #94a3b8;

            font-size: 38px;
        }

        .status-badge {
            position: absolute;

            top: 12px;
            left: 12px;

            padding: 6px 10px;

            border-radius: 999px;

            font-size: 9px;

            font-weight: 800;

            letter-spacing: .3px;

            z-index: 2;
        }

        .status-available {
            background: #16a34a;

            color: #ffffff;

            box-shadow:
                0 5px 15px rgba(22, 163, 74, .20);
        }

        .status-empty {
            background: #dc2626;

            color: #ffffff;

            box-shadow:
                0 5px 15px rgba(220, 38, 38, .18);
        }

        .best-ribbon {
            position: absolute;

            top: 12px;
            right: 12px;

            z-index: 2;

            display: inline-flex;

            align-items: center;

            gap: 4px;

            padding: 6px 9px;

            border-radius: 999px;

            background: #fff7ed;

            color: #ea580c;

            font-size: 9px;

            font-weight: 800;

            border: 1px solid #fed7aa;
        }

        .product-body {
            padding: 15px;

            display: flex;

            flex-direction: column;

            flex: 1;
        }

        .product-category {
            color: #f97316;

            font-size: 9px;

            font-weight: 800;

            text-transform: uppercase;

            letter-spacing: .7px;

            margin-bottom: 5px;
        }

        .product-name {
            font-family: 'Poppins', sans-serif;

            font-size: 15px;

            line-height: 1.35;

            font-weight: 700;

            color: #111827;

            min-height: 41px;

            margin-bottom: 10px;
        }

        .product-price {
            font-size: 17px;

            font-weight: 800;

            color: #111827;

            margin-bottom: 4px;
        }

        .product-action {
            margin-top: auto;

            padding-top: 12px;
        }

        .add-button {
            width: 100%;

            height: 43px;

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 7px;

            border-radius: 12px;

            background: #111827;

            color: #ffffff;

            cursor: pointer;

            font-size: 12px;

            font-weight: 800;

            transition: .2s;
        }

        .add-button:hover {
            background: #f97316;

            transform: translateY(-1px);
        }

        .add-button:active {
            transform: scale(.98);
        }

        .add-button.disabled {
            background: #e5e7eb;

            color: #9ca3af;

            cursor: not-allowed;
        }

        .add-button.disabled:hover {
            background: #e5e7eb;

            transform: none;
        }


        /* =========================================================
           EMPTY SEARCH
        ========================================================= */

        .empty-search {
            display: none;

            text-align: center;

            padding: 60px 20px;

            color: #94a3b8;
        }

        .empty-search i {
            font-size: 42px;

            margin-bottom: 14px;
        }

        .empty-search strong {
            display: block;

            color: #475569;

            font-size: 16px;

            margin-bottom: 5px;
        }

        .empty-search span {
            font-size: 12px;
        }


        /* =========================================================
           FLOATING CART
        ========================================================= */

        .cart-floating {
            position: fixed;

            z-index: 1000;

            left: 50%;

            bottom: 20px;

            transform: translateX(-50%);

            width: min(
                calc(100% - 32px),
                620px
            );

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            padding: 10px 12px;

            border-radius: 18px;

            background:
                rgba(17, 24, 39, .96);

            color: #ffffff;

            box-shadow:
                0 18px 50px rgba(15, 23, 42, .25);

            backdrop-filter: blur(12px);
        }

        .cart-left {
            display: flex;

            align-items: center;

            gap: 11px;

            min-width: 0;
        }

        .cart-icon {
            width: 44px;
            height: 44px;

            flex: 0 0 auto;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 13px;

            background: #f59e0b;

            color: #111827;

            font-size: 18px;
        }

        .cart-text {
            min-width: 0;
        }

        .cart-title {
            display: block;

            font-size: 13px;

            font-weight: 800;

            color: #ffffff;
        }

        .cart-subtitle {
            display: block;

            font-size: 10px;

            color: #94a3b8;

            margin-top: 2px;
        }

        .cart-count {
            min-width: 30px;
            height: 30px;

            display: flex;

            align-items: center;
            justify-content: center;

            padding: 0 8px;

            border-radius: 999px;

            background: #ffffff;

            color: #111827;

            font-size: 12px;

            font-weight: 800;
        }

        .cart-button {
            display: flex;

            align-items: center;

            gap: 8px;

            padding: 11px 16px;

            border-radius: 12px;

            background: #ffffff;

            color: #111827;

            cursor: pointer;

            font-size: 11px;

            font-weight: 800;

            white-space: nowrap;

            transition: .2s;
        }

        .cart-button:hover {
            background: #fff7ed;

            color: #ea580c;
        }


        /* =========================================================
           CART MODAL
        ========================================================= */

        .cart-overlay {
            position: fixed;

            inset: 0;

            z-index: 2000;

            display: none;

            align-items: flex-end;

            justify-content: center;

            background:
                rgba(15, 23, 42, .48);

            backdrop-filter: blur(4px);
        }

        .cart-overlay.show {
            display: flex;
        }

        .cart-modal {
            width: min(
                100%,
                600px
            );

            max-height: 85vh;

            background: #ffffff;

            border-radius:
                26px 26px
                0 0;

            overflow: hidden;

            box-shadow:
                0 -10px 50px rgba(15, 23, 42, .20);

            animation:
                cartUp .22s ease;
        }

        @keyframes cartUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .cart-modal-header {
            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 20px;

            border-bottom: 1px solid #eef2f7;
        }

        .cart-modal-title {
            font-family: 'Poppins', sans-serif;

            font-size: 18px;

            font-weight: 800;
        }

        .close-cart {
            width: 36px;
            height: 36px;

            border-radius: 50%;

            background: #f1f5f9;

            color: #475569;

            cursor: pointer;
        }

        .cart-items {
            max-height: 48vh;

            overflow-y: auto;

            padding: 8px 20px;
        }

        .cart-item {
            display: flex;

            align-items: center;

            gap: 12px;

            padding: 13px 0;

            border-bottom: 1px solid #f1f5f9;
        }

        .cart-item-image {
            width: 56px;
            height: 56px;

            flex: 0 0 auto;

            border-radius: 13px;

            object-fit: cover;

            background: #f1f5f9;
        }

        .cart-item-info {
            flex: 1;

            min-width: 0;
        }

        .cart-item-name {
            font-size: 13px;

            font-weight: 700;

            color: #111827;

            margin-bottom: 3px;
        }

        .cart-item-price {
            font-size: 11px;

            color: #64748b;
        }

        .cart-item-note {
            width: 100%;

            margin-top: 8px;

            padding: 9px 11px;

            border: 1px solid #e2e8f0;

            border-radius: 10px;

            background: #f8fafc;

            color: #334155;

            font-family: inherit;

            font-size: 11px;

            line-height: 1.4;

            resize: vertical;

            min-height: 38px;

            max-height: 90px;

            outline: none;

            transition: .2s;
        }

        .cart-item-note::placeholder {
            color: #94a3b8;
        }

        .cart-item-note:focus {
            border-color: #f97316;

            background: #ffffff;

            box-shadow:
                0 0 0 3px rgba(249, 115, 22, .10);
        }

        .quantity-control {
            display: flex;

            align-items: center;

            gap: 8px;
        }

        .quantity-button {
            width: 29px;
            height: 29px;

            border-radius: 9px;

            background: #f1f5f9;

            color: #111827;

            cursor: pointer;

            font-weight: 800;
        }

        .quantity-number {
            min-width: 20px;

            text-align: center;

            font-size: 12px;

            font-weight: 800;
        }

        .cart-empty {
            text-align: center;

            padding: 50px 20px;

            color: #94a3b8;
        }

        .cart-empty i {
            font-size: 40px;

            margin-bottom: 12px;
        }

        .cart-footer {
            padding: 18px 20px;

            border-top: 1px solid #eef2f7;

            background: #ffffff;
        }

        .cart-total {
            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 13px;
        }

        .cart-total span {
            color: #64748b;

            font-size: 12px;
        }

        .cart-total strong {
            font-size: 19px;

            font-weight: 800;
        }

        .checkout-button {
            width: 100%;

            height: 48px;

            border-radius: 14px;

            background: #f97316;

            color: #ffffff;

            cursor: pointer;

            font-size: 13px;

            font-weight: 800;
        }

        .checkout-button:hover {
            background: #ea580c;
        }


        /* =========================================================
           RESPONSIVE
        ========================================================= */

        @media (max-width: 1000px) {

            .product-grid,
            .best-scroll {
                grid-template-columns:
                    repeat(3, minmax(0, 1fr));
            }

        }

        @media (max-width: 720px) {

            .hero-inner {
                padding:
                    32px 18px
                    35px;
            }

            .container {
                padding:
                    20px 16px
                    110px;
            }

            .hero-title {
                font-size: 35px;
            }

            .hero-description {
                font-size: 13px;
            }

            .product-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));

                gap: 12px;
            }

            .best-scroll {
                display: flex;

                overflow-x: auto;

                scrollbar-width: none;
            }

            .best-scroll::-webkit-scrollbar {
                display: none;
            }

            .best-card {
                flex:
                    0 0
                    190px;
            }

            .best-image {
                height: 125px;
            }

            .product-image-wrapper {
                height: 150px;
            }

            .product-body {
                padding: 12px;
            }

            .product-name {
                font-size: 13px;

                min-height: 36px;
            }

            .product-price {
                font-size: 15px;
            }

            .add-button {
                height: 40px;
            }

        }

        @media (max-width: 430px) {

            .product-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));

                gap: 10px;
            }

            .product-image-wrapper {
                height: 135px;
            }

            .product-body {
                padding: 10px;
            }

            .product-category {
                font-size: 8px;
            }

            .product-name {
                font-size: 12px;

                min-height: 34px;
            }

            .product-price {
                font-size: 14px;
            }

            .status-badge,
            .best-ribbon {
                font-size: 7px;

                padding:
                    5px 7px;
            }

            .cart-floating {
                bottom: 12px;

                width:
                    calc(100% - 20px);
            }

            .cart-button {
                padding:
                    10px 12px;
            }

            .cart-button span {
                display: none;
            }

        }

    </style>
</head>


<body>

@php

    /*
    |--------------------------------------------------------------------------
    | DATA DASAR
    |--------------------------------------------------------------------------
    */

    $allProducts = collect($products ?? []);

    /*
    |--------------------------------------------------------------------------
    | KATEGORI
    |--------------------------------------------------------------------------
    |
    | Diambil langsung dari relasi category milik produk.
    |
    */

    $categories = $allProducts
        ->map(function ($product) {
            return $product->category ?? null;
        })
        ->filter()
        ->unique('id')
        ->sortBy('name')
        ->values();


    /*
    |--------------------------------------------------------------------------
    | BEST SELLER
    |--------------------------------------------------------------------------
    |
    | Mengambil produk berdasarkan jumlah quantity
    | yang pernah terjual.
    |
    */

    try {

        $bestProductIds = \App\Models\SaleDetail::query()
            ->selectRaw('product_id, SUM(quantity) as total_sold')
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->pluck('product_id')
            ->toArray();

        $bestProducts = $allProducts
            ->filter(function ($product) use ($bestProductIds) {
                return in_array(
                    $product->id,
                    $bestProductIds
                );
            })
            ->sortBy(function ($product) use ($bestProductIds) {
                $position = array_search(
                    $product->id,
                    $bestProductIds
                );

                return $position === false
                    ? 999
                    : $position;
            })
            ->values();

    } catch (\Throwable $e) {

        $bestProducts = collect();

    }


    /*
    |--------------------------------------------------------------------------
    | URL FOTO PRODUK
    |--------------------------------------------------------------------------
    */

    $productImage = function ($product) {

        $image =
            $product->image
            ?? $product->photo
            ?? $product->image_path
            ?? $product->gambar
            ?? null;

        if (!$image) {
            return null;
        }

        if (
            str_starts_with($image, 'http://')
            ||
            str_starts_with($image, 'https://')
        ) {
            return $image;
        }

        if (
            str_starts_with($image, 'storage/')
        ) {
            return asset($image);
        }

        return asset(
            'storage/' . ltrim($image, '/')
        );
    };

@endphp


<!-- =========================================================
     HEADER
========================================================= -->

<header class="hero">

    <div class="hero-inner">

        <div class="brand-label">
            ONLINE ORDER
        </div>

        <h1 class="hero-title">
            Menu Pesanan
        </h1>

        <p class="hero-description">
            Pilih makanan dan minuman favorit kamu,
            lalu lakukan pemesanan dengan mudah.
        </p>

        <div class="table-badge">

            <span class="table-icon">
                <i class="fas fa-chair"></i>
            </span>

            <span>
                {{ $table->name ?? 'Meja' }}
            </span>

        </div>

    </div>

</header>


<!-- =========================================================
     MAIN
========================================================= -->

<main class="container">


    <!-- =====================================================
         SEARCH
    ====================================================== -->

    <div class="search-wrapper">

        <i class="fas fa-search"></i>

        <input
            type="text"
            id="searchMenu"
            placeholder="Cari makanan atau minuman..."
            autocomplete="off"
        >

    </div>


    <!-- =====================================================
         BEST SELLER
    ====================================================== -->

    @if($bestProducts->count() > 0)

        <section
            class="best-section"
            id="bestSellerSection"
        >

            <div class="best-header">

                <div>

                    <div class="best-title">

                        <span class="best-title-icon">
                            <i class="fas fa-fire"></i>
                        </span>

                        Best Seller

                    </div>

                    <p class="section-subtitle">
                        Menu yang paling banyak dipesan
                    </p>

                </div>

            </div>


            <div class="best-scroll">

                @foreach($bestProducts as $product)

                    @php
                        $image = $productImage($product);
                    @endphp

                    <div
                        class="best-card"
                        data-best-product
                        data-product-name="{{ strtolower($product->name) }}"
                    >

                        <span class="best-badge">
                            <i class="fas fa-star"></i>
                            BEST SELLER
                        </span>


                        @if($image)

                            <img
                                src="{{ $image }}"
                                alt="{{ $product->name }}"
                                class="best-image"
                                loading="lazy"
                                onerror="this.style.display='none';"
                            >

                        @else

                            <div class="best-image product-image-placeholder">
                                <i class="fas fa-utensils"></i>
                            </div>

                        @endif


                        <div class="best-content">

                            <div class="best-name">
                                {{ $product->name }}
                            </div>

                            <div class="best-price">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

        </section>

    @endif


    <!-- =====================================================
         CATEGORY
    ====================================================== -->

    <section class="category-wrapper">

        <div class="section-heading">

            <div>

                <h2 class="section-title">
                    Pilihan Menu
                </h2>

                <p class="section-subtitle">
                    Pilih berdasarkan kategori
                </p>

            </div>

            <span
                class="menu-count"
                id="menuCount"
            >
                {{ $allProducts->count() }} menu
            </span>

        </div>


        <div
            class="category-list"
            id="categoryList"
        >

            <button
                type="button"
                class="category-btn active"
                data-category="all"
            >

                <i class="fas fa-layer-group"></i>

                Semua

            </button>


            @foreach($categories as $category)

                <button
                    type="button"
                    class="category-btn"
                    data-category="{{ $category->id }}"
                >

                    <i class="fas fa-tag"></i>

                    {{ $category->name }}

                </button>

            @endforeach

        </div>

    </section>


    <!-- =====================================================
         PRODUCT GRID
    ====================================================== -->

    <div
        class="product-grid"
        id="productGrid"
    >

        @foreach($allProducts as $product)

            @php

                $stock = (int) ($product->stock ?? 0);

                $available = $stock > 0;

                $image = $productImage($product);

                $categoryId =
                    $product->category_id
                    ?? optional($product->category)->id
                    ?? '';

                $categoryName =
                    optional($product->category)->name
                    ?? 'Menu';

                $isBestSeller =
                    $bestProducts
                        ->contains('id', $product->id);

            @endphp


            <article
                class="product-card"
                data-product-card
                data-product-id="{{ $product->id }}"
                data-product-name="{{ strtolower($product->name) }}"
                data-category-id="{{ $categoryId }}"
                data-category-name="{{ strtolower($categoryName) }}"
                data-price="{{ $product->price }}"
                data-stock="{{ $stock }}"
                data-image="{{ $image }}"
            >


                <!-- FOTO -->

                <div class="product-image-wrapper">

                    @if($image)

                        <img
                            src="{{ $image }}"
                            alt="{{ $product->name }}"
                            class="product-image"
                            loading="lazy"
                            onerror="
                                this.style.display='none';
                                this.nextElementSibling.style.display='flex';
                            "
                        >

                        <div
                            class="product-image-placeholder"
                            style="display:none;"
                        >
                            <i class="fas fa-utensils"></i>
                        </div>

                    @else

                        <div class="product-image-placeholder">

                            <i class="fas fa-utensils"></i>

                        </div>

                    @endif


                    <!-- STATUS -->

                    @if($available)

                        <span class="status-badge status-available">
                            TERSEDIA
                        </span>

                    @else

                        <span class="status-badge status-empty">
                            HABIS
                        </span>

                    @endif


                    @if($isBestSeller)

                        <span class="best-ribbon">

                            <i class="fas fa-star"></i>

                            BEST

                        </span>

                    @endif

                </div>


                <!-- BODY -->

                <div class="product-body">


                    <div class="product-category">

                        {{ $categoryName }}

                    </div>


                    <div class="product-name">

                        {{ $product->name }}

                    </div>


                    <div class="product-price">

                        Rp {{ number_format(
                            $product->price,
                            0,
                            ',',
                            '.'
                        ) }}

                    </div>


                    <div class="product-action">

                        @if($available)

                            <button
                                type="button"
                                class="add-button"
                                data-add-cart
                                data-id="{{ $product->id }}"
                            >

                                <i class="fas fa-plus"></i>

                                Tambah

                            </button>

                        @else

                            <button
                                type="button"
                                class="add-button disabled"
                                disabled
                            >

                                <i class="fas fa-ban"></i>

                                Habis

                            </button>

                        @endif

                    </div>

                </div>

            </article>

        @endforeach

    </div>


    <!-- =====================================================
         EMPTY SEARCH
    ====================================================== -->

    <div
        class="empty-search"
        id="emptySearch"
    >

        <i class="fas fa-search"></i>

        <strong>
            Menu tidak ditemukan
        </strong>

        <span>
            Coba gunakan kata kunci atau kategori lain.
        </span>

    </div>


</main>


<!-- =========================================================
     FLOATING CART
========================================================= -->

<div class="cart-floating">

    <div class="cart-left">

        <div class="cart-icon">

            <i class="fas fa-shopping-cart"></i>

        </div>

        <div class="cart-text">

            <span class="cart-title">
                Lihat Keranjang
            </span>

            <span class="cart-subtitle">
                Pesanan Kamu
            </span>

        </div>

        <div
            class="cart-count"
            id="cartCount"
        >
            0
        </div>

    </div>


    <button
        type="button"
        class="cart-button"
        id="openCart"
    >

        <i class="fas fa-arrow-right"></i>

        <span>
            Buka Keranjang
        </span>

    </button>

</div>


<!-- =========================================================
     CART MODAL
========================================================= -->

<div
    class="cart-overlay"
    id="cartOverlay"
>

    <div class="cart-modal">


        <div class="cart-modal-header">

            <div class="cart-modal-title">

                <i
                    class="fas fa-shopping-bag"
                    style="color:#f97316;"
                ></i>

                Keranjang Kamu

            </div>


            <button
                type="button"
                class="close-cart"
                id="closeCart"
            >

                <i class="fas fa-times"></i>

            </button>

        </div>


        <div
            class="cart-items"
            id="cartItems"
        ></div>


        <div class="cart-footer">

            <div class="cart-total">

                <span>
                    Total
                </span>

                <strong id="cartTotal">
                    Rp 0
                </strong>

            </div>


            <button
                type="button"
                class="checkout-button"
                id="checkoutButton"
            >

                Lanjutkan Pesanan

            </button>

        </div>

    </div>

</div>


<script>

    /*
    |--------------------------------------------------------------------------
    | DATA PRODUK
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | DATA PRODUK UNTUK JAVASCRIPT
  
    */

    @php
        $productsForJs = $allProducts
            ->map(function ($product) use ($productImage) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'stock' => (int) ($product->stock ?? 0),
                    'image' => $productImage($product),
                    'category_id' =>
                        $product->category_id
                        ?? optional($product->category)->id
                        ?? '',
                    'category_name' =>
                        optional($product->category)->name
                        ?? 'Menu',
                ];
            })
            ->values();

        $productsJson = json_encode(
            $productsForJs,
            JSON_HEX_TAG
                | JSON_HEX_APOS
                | JSON_HEX_AMP
                | JSON_HEX_QUOT
                | JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
        );
    @endphp

    const products = {!! $productsJson !!};


    /*
    |--------------------------------------------------------------------------
    | CART STORAGE KEY
    |--------------------------------------------------------------------------
    */

    const cartKey =
        'customer_cart_table_{{ $table->id ?? "default" }}';


    /*
    |--------------------------------------------------------------------------
    | CART
    |--------------------------------------------------------------------------
    */

    let cart = [];


    /*
    |--------------------------------------------------------------------------
    | LOAD CART
    |--------------------------------------------------------------------------
    */

    function loadCart()
    {

        try {

            const saved =
                localStorage.getItem(cartKey);

            cart =
                saved
                    ? JSON.parse(saved)
                    : [];

        } catch (error) {

            cart = [];

        }

        /*
        |--------------------------------------------------------------------------
        | COMPATIBILITY CART LAMA
        |--------------------------------------------------------------------------
        |
        | Jika sebelumnya sudah ada cart di localStorage, tambahkan field
        | note agar tidak merusak data keranjang yang lama.
        |
        */

        cart = Array.isArray(cart)
            ? cart.map(function(item) {
                return {
                    ...item,
                    note: item.note ?? ''
                };
            })
            : [];

        updateCart();

    }


    /*
    |--------------------------------------------------------------------------
    | SAVE CART
    |--------------------------------------------------------------------------
    */

    function saveCart()
    {

        localStorage.setItem(
            cartKey,
            JSON.stringify(cart)
        );

    }


    /*
    |--------------------------------------------------------------------------
    | FORMAT RUPIAH
    |--------------------------------------------------------------------------
    */

    function formatRupiah(value)
    {

        return new Intl.NumberFormat(
            'id-ID',
            {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            }
        ).format(value || 0);

    }


    /*
    |--------------------------------------------------------------------------
    | ESCAPE HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value)
    {

        const div =
            document.createElement('div');

        div.textContent =
            value ?? '';

        return div.innerHTML;

    }


    /*
    |--------------------------------------------------------------------------
    | ADD TO CART
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        function(event)
        {

            const button =
                event.target.closest(
                    '[data-add-cart]'
                );

            if (!button) {
                return;
            }


            const card =
                button.closest(
                    '[data-product-card]'
                );

            if (!card) {
                return;
            }


            const id =
                String(
                    card.dataset.productId
                );


            const product =
                products.find(
                    item =>
                        String(item.id) === id
                );


            if (!product) {
                return;
            }


            if (
                parseInt(product.stock) <= 0
            ) {

                return;

            }


            const existing =
                cart.find(
                    item =>
                        String(item.id) === id
                );


            if (existing) {

                if (
                    existing.quantity <
                    product.stock
                ) {

                    existing.quantity++;

                } else {

                    alert(
                        'Jumlah pesanan sudah mencapai stok yang tersedia.'
                    );

                    return;

                }

            } else {

                cart.push({

                    id: product.id,

                    name: product.name,

                    price: product.price,

                    image: product.image,

                    stock: product.stock,

                    quantity: 1,

                    note: ''

                });

            }


            saveCart();

            updateCart();


            button.innerHTML =
                '<i class="fas fa-check"></i> Ditambahkan';


            button.style.background =
                '#16a34a';


            setTimeout(
                function()
                {

                    button.innerHTML =
                        '<i class="fas fa-plus"></i> Tambah';

                    button.style.background =
                        '';

                },
                700
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | UPDATE CART
    |--------------------------------------------------------------------------
    */

    function updateCart()
    {

        const count =
            cart.reduce(
                (total, item) =>
                    total + item.quantity,
                0
            );


        document
            .getElementById('cartCount')
            .textContent =
            count;


        renderCart();

    }


    /*
    |--------------------------------------------------------------------------
    | RENDER CART
    |--------------------------------------------------------------------------
    */

    function renderCart()
    {

        const container =
            document.getElementById(
                'cartItems'
            );


        if (!cart.length) {

            container.innerHTML = `

                <div class="cart-empty">

                    <i class="fas fa-shopping-basket"></i>

                    <div>
                        Keranjang masih kosong
                    </div>

                </div>

            `;

            document
                .getElementById('cartTotal')
                .textContent =
                formatRupiah(0);

            return;

        }


        let total = 0;


        container.innerHTML =
            cart.map(
                function(item)
                {

                    const subtotal =
                        item.price *
                        item.quantity;


                    total += subtotal;


                    const image =
                        item.image
                        ? `
                            <img
                                src="${escapeHtml(item.image)}"
                                class="cart-item-image"
                                alt="${escapeHtml(item.name)}"
                            >
                        `
                        : `
                            <div
                                class="cart-item-image"
                                style="
                                    display:flex;
                                    align-items:center;
                                    justify-content:center;
                                    color:#94a3b8;
                                "
                            >
                                <i class="fas fa-utensils"></i>
                            </div>
                        `;


                    return `

                        <div class="cart-item">

                            ${image}

                            <div class="cart-item-info">

                                <div class="cart-item-name">

                                    ${escapeHtml(item.name)}

                                </div>

                                <div class="cart-item-price">

                                    ${formatRupiah(item.price)}

                                </div>

                                <textarea
                                    class="cart-item-note"
                                    placeholder="Catatan / request untuk menu ini..."
                                    maxlength="250"
                                    data-cart-note
                                    data-id="${item.id}"
                                >${escapeHtml(item.note || '')}</textarea>

                            </div>


                            <div class="quantity-control">

                                <button
                                    type="button"
                                    class="quantity-button"
                                    onclick="changeQuantity(
                                        '${item.id}',
                                        -1
                                    )"
                                >
                                    −
                                </button>


                                <span class="quantity-number">

                                    ${item.quantity}

                                </span>


                                <button
                                    type="button"
                                    class="quantity-button"
                                    onclick="changeQuantity(
                                        '${item.id}',
                                        1
                                    )"
                                >
                                    +
                                </button>

                            </div>

                        </div>

                    `;

                }
            ).join('');


        document
            .getElementById('cartTotal')
            .textContent =
            formatRupiah(total);

    }


    /*
    |--------------------------------------------------------------------------
    | CATATAN / REQUEST MENU
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'input',
        function(event)
        {

            const noteInput =
                event.target.closest(
                    '[data-cart-note]'
                );

            if (!noteInput) {
                return;
            }

            const id =
                String(
                    noteInput.dataset.id
                );

            const item =
                cart.find(
                    product =>
                        String(product.id) === id
                );

            if (!item) {
                return;
            }

            item.note =
                noteInput.value
                    .trimStart()
                    .slice(0, 250);

            saveCart();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | CHANGE QUANTITY
    |--------------------------------------------------------------------------
    */

    function changeQuantity(
        id,
        change
    )
    {

        const item =
            cart.find(
                product =>
                    String(product.id) ===
                    String(id)
            );


        if (!item) {
            return;
        }


        item.quantity += change;


        if (
            item.quantity <= 0
        ) {

            cart =
                cart.filter(
                    product =>
                        String(product.id) !==
                        String(id)
                );

        }


        if (
            item.quantity >
            item.stock
        ) {

            item.quantity =
                item.stock;

        }


        saveCart();

        updateCart();

    }


    /*
    |--------------------------------------------------------------------------
    | OPEN CART
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('openCart')
        .addEventListener(
            'click',
            function()
            {

                document
                    .getElementById(
                        'cartOverlay'
                    )
                    .classList
                    .add('show');

                document.body.style.overflow =
                    'hidden';

            }
        );


    /*
    |--------------------------------------------------------------------------
    | CLOSE CART
    |--------------------------------------------------------------------------
    */

    function closeCart()
    {

        document
            .getElementById(
                'cartOverlay'
            )
            .classList
            .remove('show');

        document.body.style.overflow =
            '';

    }


    document
        .getElementById('closeCart')
        .addEventListener(
            'click',
            closeCart
        );


    document
        .getElementById('cartOverlay')
        .addEventListener(
            'click',
            function(event)
            {

                if (
                    event.target ===
                    this
                ) {

                    closeCart();

                }

            }
        );


    /*
    |--------------------------------------------------------------------------
    | CATEGORY FILTER + SEARCH
    |--------------------------------------------------------------------------
    */

    let activeCategory = 'all';


    const searchInput =
        document.getElementById(
            'searchMenu'
        );


    const categoryButtons =
        document.querySelectorAll(
            '.category-btn'
        );


    const productCards =
        document.querySelectorAll(
            '[data-product-card]'
        );


    function filterProducts()
    {

        const keyword =
            searchInput
                .value
                .toLowerCase()
                .trim();


        let visibleCount = 0;


        productCards.forEach(
            function(card)
            {

                const name =
                    card.dataset
                        .productName
                        .toLowerCase();


                const categoryId =
                    String(
                        card.dataset
                            .categoryId
                    );


                const categoryName =
                    card.dataset
                        .categoryName
                        .toLowerCase();


                const matchesSearch =
                    !keyword ||
                    name.includes(keyword) ||
                    categoryName.includes(keyword);


                const matchesCategory =
                    activeCategory ===
                    'all'
                    ||
                    categoryId ===
                    String(activeCategory);


                if (
                    matchesSearch &&
                    matchesCategory
                ) {

                    card.classList
                        .remove('hidden');

                    visibleCount++;

                } else {

                    card.classList
                        .add('hidden');

                }

            }
        );


        document
            .getElementById(
                'menuCount'
            )
            .textContent =
            visibleCount +
            ' menu';


        document
            .getElementById(
                'emptySearch'
            )
            .style.display =
            visibleCount === 0
                ? 'block'
                : 'none';

    }


    /*
    |--------------------------------------------------------------------------
    | CATEGORY BUTTON
    |--------------------------------------------------------------------------
    */

    categoryButtons.forEach(
        function(button)
        {

            button.addEventListener(
                'click',
                function()
                {

                    categoryButtons
                        .forEach(
                            btn =>
                                btn.classList
                                    .remove(
                                        'active'
                                    )
                        );


                    this.classList
                        .add('active');


                    activeCategory =
                        this.dataset
                            .category;


                    filterProducts();

                }
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    searchInput.addEventListener(
        'input',
        filterProducts
    );


    /*
|--------------------------------------------------------------------------
| CHECKOUT
|--------------------------------------------------------------------------
*/

document
    .getElementById('checkoutButton')
    .addEventListener('click', function () {

        if (!cart.length) {
            alert('Keranjang masih kosong.');
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | SIMPAN CART KE SESSION STORAGE
        |--------------------------------------------------------------------------
        */

        sessionStorage.setItem(
            'customer_checkout_cart',
            JSON.stringify(cart)
        );

        /*
        |--------------------------------------------------------------------------
        | REDIRECT KE HALAMAN CHECKOUT
        |--------------------------------------------------------------------------
        */

        window.location.href =
            "{{ route('customer.checkout', ['table' => $table->id]) }}";
    });
    /*
    |--------------------------------------------------------------------------
    | INITIAL LOAD
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'DOMContentLoaded',
        function()
        {

            loadCart();

            filterProducts();

        }
    );

</script>

</body>

</html>