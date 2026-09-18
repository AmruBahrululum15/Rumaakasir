<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Checkout Pesanan - {{ $table->name }}</title>

    <style>
        :root{
            --primary:#f97316;
            --primary-dark:#ea580c;
            --primary-soft:#fff7ed;
            --text:#1c1917;
            --heading:#172033;
            --muted:#78716c;
            --muted-2:#a8a29e;
            --border:#e7e5e4;
            --border-soft:#f1efed;
            --surface:#ffffff;
            --page:#fafaf9;
            --success:#16a34a;
            --danger:#dc2626;
            --radius-xl:24px;
            --radius-lg:18px;
            --radius-md:14px;
        }

        *{box-sizing:border-box}
        html{scroll-behavior:smooth}
        body{
            margin:0;
            min-height:100vh;
            color:var(--text);
            background:linear-gradient(180deg,#fffaf5 0,#fafaf9 300px,#fafaf9 100%);
            font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
            -webkit-font-smoothing:antialiased;
        }
        button,input{font:inherit}
        button{cursor:pointer}
        a{color:inherit}

        /* ---------- Header ---------- */
        .topbar{
            position:sticky;
            top:0;
            z-index:50;
            background:rgba(255,255,255,.94);
            border-bottom:1px solid rgba(231,229,228,.9);
            backdrop-filter:blur(16px);
            -webkit-backdrop-filter:blur(16px);
        }
        .topbar-inner{
            width:min(1180px,calc(100% - 48px));
            min-height:78px;
            margin:auto;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:20px;
        }
        .brand{
            display:flex;
            align-items:center;
            gap:14px;
            min-width:0;
        }
        .brand-icon{
            width:46px;
            height:46px;
            flex:0 0 46px;
            border-radius:14px;
            display:grid;
            place-items:center;
            background:linear-gradient(135deg,#fb923c,#ea580c);
            color:#fff;
            font-size:21px;
            box-shadow:0 8px 20px rgba(249,115,22,.18);
        }
        .brand-copy{min-width:0}
        .brand-title{
            color:var(--heading);
            font-size:17px;
            line-height:1.2;
            font-weight:850;
            letter-spacing:-.25px;
        }
        .brand-subtitle{
            margin-top:4px;
            color:var(--muted);
            font-size:12px;
            line-height:1.4;
        }
        .table-badge{
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:9px 13px;
            border:1px solid var(--border);
            border-radius:999px;
            background:#fff;
            color:var(--heading);
            font-size:13px;
            font-weight:800;
            white-space:nowrap;
            box-shadow:0 4px 14px rgba(28,25,23,.035);
        }
        .table-dot{
            width:8px;height:8px;border-radius:50%;
            background:#16a34a;
            box-shadow:0 0 0 4px #dcfce7;
        }

        /* ---------- Page ---------- */
        .page{
            width:min(1180px,calc(100% - 48px));
            margin:0 auto;
            padding:34px 0 54px;
        }
        .page-heading{margin-bottom:24px}
        .page-heading h1{
            margin:0;
            color:var(--heading);
            font-size:clamp(26px,4vw,34px);
            line-height:1.15;
            font-weight:900;
            letter-spacing:-.8px;
        }
        .page-heading p{
            margin:8px 0 0;
            color:var(--muted);
            font-size:14px;
            line-height:1.65;
        }

        /* ---------- Alerts ---------- */
        .alert{
            display:none;
            margin:0 0 18px;
            padding:14px 16px;
            border:1px solid #fecaca;
            border-radius:14px;
            background:#fff7f7;
            color:#991b1b;
            font-size:13px;
            line-height:1.55;
        }
        .alert.show{display:block}

        /* ---------- Main layout ---------- */
        .checkout-grid{
            display:grid;
            grid-template-columns:minmax(0,1.32fr) minmax(330px,.68fr);
            gap:24px;
            align-items:start;
        }
        .left-column{
            display:flex;
            flex-direction:column;
            gap:20px;
            min-width:0;
        }

        /* ---------- Cards ---------- */
        .card{
            min-width:0;
            overflow:hidden;
            background:var(--surface);
            border:1px solid var(--border);
            border-radius:var(--radius-xl);
            box-shadow:0 10px 35px rgba(28,25,23,.045);
        }
        .card-header{
            padding:22px 24px 17px;
            display:flex;
            align-items:flex-start;
            gap:13px;
            border-bottom:1px solid var(--border-soft);
        }
        .card-header-icon{
            width:38px;
            height:38px;
            flex:0 0 38px;
            display:grid;
            place-items:center;
            border-radius:12px;
            background:var(--primary-soft);
            color:var(--primary-dark);
            font-size:18px;
        }
        .card-header-copy{min-width:0}
        .card-header h2{
            margin:0;
            color:var(--heading);
            font-size:17px;
            line-height:1.35;
            font-weight:850;
            letter-spacing:-.15px;
        }
        .card-header p{
            margin:4px 0 0;
            color:var(--muted);
            font-size:12px;
            line-height:1.55;
        }
        .card-body{padding:24px}

        /* ---------- Order items ---------- */
        .order-items{display:flex;flex-direction:column}
        .order-item{
            display:grid;
            grid-template-columns:64px minmax(0,1fr) auto;
            gap:14px;
            align-items:center;
            padding:14px 0;
            border-bottom:1px solid var(--border-soft);
        }
        .order-item:first-child{padding-top:0}
        .order-item:last-child{padding-bottom:0;border-bottom:0}
        .product-image{
            width:64px;height:64px;
            overflow:hidden;
            border-radius:15px;
            background:#fff7ed;
        }
        .product-image img{
            display:block;
            width:100%;height:100%;
            object-fit:cover;
        }
        .product-placeholder{
            width:100%;height:100%;
            display:grid;place-items:center;
            font-size:21px;
            color:#c2410c;
        }
        .product-info{min-width:0}
        .product-name{
            color:var(--heading);
            font-size:14px;
            font-weight:800;
            line-height:1.4;
            overflow:hidden;
            text-overflow:ellipsis;
            white-space:nowrap;
        }
        .product-price{
            margin-top:4px;
            color:var(--muted);
            font-size:12px;
        }
        .product-quantity{
            display:inline-flex;
            margin-top:6px;
            padding:3px 7px;
            border-radius:7px;
            background:#f5f5f4;
            color:#57534e;
            font-size:11px;
            font-weight:700;
        }
        .item-subtotal{
            color:var(--heading);
            font-size:13px;
            font-weight:850;
            white-space:nowrap;
            text-align:right;
        }
        .empty-cart{
            padding:30px 12px;
            text-align:center;
            color:var(--muted);
        }
        .empty-icon{
            width:50px;height:50px;
            margin:0 auto 10px;
            display:grid;place-items:center;
            border-radius:16px;
            background:var(--primary-soft);
            font-size:22px;
        }
        .empty-cart h3{
            margin:0;
            color:var(--heading);
            font-size:15px;
        }
        .empty-cart p{
            margin:6px 0 0;
            font-size:12px;
        }

        /* ---------- Customer form ---------- */
        .customer-form{
            display:flex;
            flex-direction:column;
            gap:20px;
        }
        .form-group{margin:0}
        .form-label{
            display:block;
            margin:0 0 9px;
            color:var(--heading);
            font-size:13px;
            line-height:1.35;
            font-weight:800;
        }
        .required{color:var(--danger)}
        .input-wrap{position:relative}
        .input-icon{
            position:absolute;
            left:15px;
            top:50%;
            transform:translateY(-50%);
            width:22px;height:22px;
            display:grid;place-items:center;
            color:#a8a29e;
            font-size:15px;
            pointer-events:none;
        }
        .form-input{
            width:100%;
            min-height:50px;
            padding:13px 15px 13px 46px;
            border:1px solid #d6d3d1;
            border-radius:15px;
            outline:0;
            background:#fff;
            color:var(--heading);
            font-size:14px;
            line-height:1.4;
            transition:border-color .18s ease,box-shadow .18s ease,background .18s ease;
        }
        .form-input::placeholder{color:#b7b2ae}
        .form-input:hover{border-color:#c9c5c1}
        .form-input:focus{
            border-color:var(--primary);
            background:#fff;
            box-shadow:0 0 0 4px rgba(249,115,22,.09);
        }
        .form-input.is-invalid{
            border-color:#ef4444;
            background:#fffafa;
        }
        .field-error{
            margin-top:7px;
            color:#dc2626;
            font-size:12px;
            line-height:1.45;
        }

        /* ---------- Payment ---------- */
        .payment-section{
            padding-top:23px;
            border-top:1px solid var(--border-soft);
        }
        .section-heading{margin-bottom:14px}
        .section-heading h3{
            margin:0;
            color:var(--heading);
            font-size:16px;
            font-weight:850;
        }
        .section-heading p{
            margin:4px 0 0;
            color:var(--muted);
            font-size:12px;
            line-height:1.5;
        }
        .payment-options{
            display:grid;
            gap:12px;
        }
        .payment-option{
            position:relative;
            display:grid;
            grid-template-columns:46px minmax(0,1fr) auto;
            align-items:center;
            gap:13px;
            min-height:82px;
            padding:15px 16px;
            border:1px solid var(--border);
            border-radius:17px;
            background:#fff;
            cursor:pointer;
            user-select:none;
            transition:border-color .18s ease,background .18s ease,box-shadow .18s ease,transform .18s ease;
        }
        .payment-option:hover{
            border-color:#fdba74;
            background:#fffaf5;
            transform:translateY(-1px);
        }
        .payment-option.selected{
            border-color:var(--primary);
            background:var(--primary-soft);
            box-shadow:0 7px 22px rgba(249,115,22,.08);
        }
        .payment-option input{
            position:absolute;
            opacity:0;
            pointer-events:none;
        }
        .payment-icon{
            width:46px;height:46px;
            display:grid;place-items:center;
            border-radius:14px;
            background:#f5f5f4;
            font-size:20px;
        }
        .payment-option.selected .payment-icon{background:#ffedd5}
        .payment-content{min-width:0}
        .payment-title{
            display:block;
            color:var(--heading);
            font-size:14px;
            line-height:1.35;
            font-weight:850;
        }
        .payment-description{
            display:block;
            margin-top:4px;
            max-width:520px;
            color:var(--muted);
            font-size:11.5px;
            line-height:1.5;
        }
        .payment-tag{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            min-width:54px;
            padding:5px 8px;
            border-radius:8px;
            background:#f5f5f4;
            color:#78716c;
            font-size:9px;
            line-height:1;
            font-weight:850;
            text-transform:uppercase;
            letter-spacing:.3px;
        }
        .payment-option.selected .payment-tag{
            background:#fff;
            color:var(--primary-dark);
            border:1px solid #fed7aa;
        }
        .payment-check{
            position:absolute;
            right:13px;
            bottom:11px;
            width:20px;height:20px;
            display:none;
            place-items:center;
            border-radius:50%;
            background:var(--primary);
            color:#fff;
            font-size:12px;
            font-weight:900;
        }
        .payment-option.selected .payment-check{display:grid}
        .payment-helper{
            margin-top:11px;
            padding:11px 12px;
            border:1px solid var(--border-soft);
            border-radius:12px;
            background:#fafaf9;
            color:var(--muted);
            font-size:11px;
            line-height:1.5;
        }

        /* ---------- Summary ---------- */
        .summary-card{
            position:sticky;
            top:98px;
        }
        .summary-list{
            display:flex;
            flex-direction:column;
            gap:0;
        }
        .summary-row{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:15px;
            padding:9px 0;
            color:var(--muted);
            font-size:13px;
        }
        .summary-value{
            color:var(--heading);
            font-weight:750;
            white-space:nowrap;
        }
        .summary-total{
            margin-top:12px;
            padding:16px;
            border-radius:15px;
            background:var(--primary-soft);
        }
        .summary-total-label{
            display:block;
            color:var(--primary-dark);
            font-size:12px;
            font-weight:800;
        }
        .summary-total-value{
            display:block;
            margin-top:4px;
            color:var(--primary);
            font-size:28px;
            line-height:1.15;
            font-weight:900;
            letter-spacing:-.6px;
            word-break:break-word;
        }
        .summary-hint{
            margin-top:13px;
            padding:12px 13px;
            border:1px solid #e7eef8;
            border-radius:13px;
            background:#f8fafc;
            color:#64748b;
            font-size:11px;
            line-height:1.55;
        }
        .secure-info{
            display:flex;
            align-items:flex-start;
            gap:9px;
            margin-top:14px;
            padding-top:14px;
            border-top:1px solid var(--border-soft);
            color:var(--muted);
            font-size:11px;
            line-height:1.5;
        }
        .secure-icon{
            color:#64748b;
            flex:0 0 auto;
        }

        /* ---------- Actions ---------- */
        .actions{
            display:flex;
            flex-direction:column;
            gap:10px;
            margin-top:18px;
        }
        .submit-button{
            width:100%;
            min-height:52px;
            border:0;
            border-radius:14px;
            background:linear-gradient(135deg,#f97316,#ea580c);
            color:#fff;
            font-size:14px;
            font-weight:850;
            box-shadow:0 10px 24px rgba(249,115,22,.18);
            transition:transform .18s ease,box-shadow .18s ease,opacity .18s ease;
        }
        .submit-button:hover:not(:disabled){
            transform:translateY(-1px);
            box-shadow:0 13px 28px rgba(249,115,22,.22);
        }
        .submit-button:active:not(:disabled){transform:translateY(0)}
        .submit-button:disabled{
            cursor:not-allowed;
            opacity:.55;
            box-shadow:none;
        }
        .button-content{
            display:flex;
            align-items:center;
            justify-content:center;
            gap:9px;
        }
        .spinner{
            width:17px;height:17px;
            border:2px solid rgba(255,255,255,.35);
            border-top-color:#fff;
            border-radius:50%;
            animation:spin .7s linear infinite;
        }
        @keyframes spin{to{transform:rotate(360deg)}}
        .back-button{
            display:flex;
            align-items:center;
            justify-content:center;
            min-height:44px;
            border:1px solid var(--border);
            border-radius:13px;
            background:#fff;
            color:#57534e;
            text-decoration:none;
            font-size:12px;
            font-weight:750;
            transition:background .18s ease,border-color .18s ease;
        }
        .back-button:hover{background:#fafaf9;border-color:#d6d3d1}

        .mobile-action{
            display:none;
        }

        /* ---------- Responsive ---------- */
        @media (max-width: 920px){
            .checkout-grid{
                grid-template-columns:1fr;
            }
            .summary-card{
                position:static;
            }
        }

        @media (max-width: 640px){
            body{
                background:#fafaf9;
            }
            .topbar-inner,
            .page{
                width:100%;
            }
            .topbar-inner{
                min-height:66px;
                padding:0 16px;
                gap:12px;
            }
            .brand{gap:10px}
            .brand-icon{
                width:40px;height:40px;flex-basis:40px;
                border-radius:12px;
                font-size:18px;
            }
            .brand-title{font-size:15px}
            .brand-subtitle{display:none}
            .table-badge{
                padding:8px 10px;
                font-size:11px;
            }
            .page{
                padding:22px 14px 105px;
            }
            .page-heading{
                margin-bottom:18px;
                padding:0 2px;
            }
            .page-heading h1{
                font-size:24px;
                letter-spacing:-.5px;
            }
            .page-heading p{
                margin-top:6px;
                font-size:12px;
            }
            .checkout-grid{
                gap:16px;
            }
            .left-column{
                gap:16px;
            }
            .card{
                border-radius:19px;
                box-shadow:0 7px 24px rgba(28,25,23,.035);
            }
            .card-header{
                padding:18px 17px 15px;
                gap:10px;
            }
            .card-header-icon{
                width:34px;height:34px;flex-basis:34px;
                border-radius:10px;
                font-size:16px;
            }
            .card-header h2{font-size:15px}
            .card-header p{font-size:11px}
            .card-body{padding:18px 17px}
            .order-item{
                grid-template-columns:54px minmax(0,1fr) auto;
                gap:11px;
                padding:12px 0;
            }
            .product-image{
                width:54px;height:54px;
                border-radius:13px;
            }
            .product-name{font-size:13px}
            .product-price{font-size:11px}
            .product-quantity{font-size:10px}
            .item-subtotal{font-size:12px}
            .customer-form{gap:22px}
            .form-label{
                margin-bottom:8px;
                font-size:12px;
            }
            .form-input{
                min-height:50px;
                padding:13px 13px 13px 43px;
                border-radius:14px;
                font-size:13px;
            }
            .payment-section{
                margin-top:2px;
                padding-top:20px;
            }
            .section-heading h3{font-size:15px}
            .section-heading p{font-size:11px}
            .payment-options{gap:10px}
            .payment-option{
                grid-template-columns:42px minmax(0,1fr);
                min-height:76px;
                padding:13px;
                gap:11px;
                border-radius:15px;
            }
            .payment-icon{
                width:42px;height:42px;
                border-radius:12px;
                font-size:18px;
            }
            .payment-title{font-size:13px}
            .payment-description{
                margin-top:3px;
                font-size:10.5px;
                line-height:1.45;
                padding-right:6px;
            }
            .payment-tag{
                position:absolute;
                top:12px;
                right:12px;
                min-width:48px;
                padding:4px 6px;
                font-size:8px;
            }
            .payment-check{
                right:11px;
                bottom:10px;
            }
            .payment-helper{
                font-size:10.5px;
                padding:10px 11px;
            }
            .summary-card .card-body{padding-top:17px}
            .summary-row{font-size:12px}
            .summary-total{padding:14px}
            .summary-total-value{font-size:24px}
            .summary-hint{font-size:10.5px}
            .secure-info{font-size:10.5px}
            .summary-card .actions{
                display:none;
            }
            .mobile-action{
                display:block;
                position:fixed;
                left:0;
                right:0;
                bottom:0;
                z-index:60;
                padding:10px 14px calc(10px + env(safe-area-inset-bottom));
                background:rgba(255,255,255,.96);
                border-top:1px solid var(--border);
                box-shadow:0 -10px 30px rgba(28,25,23,.08);
                backdrop-filter:blur(14px);
                -webkit-backdrop-filter:blur(14px);
            }
            .mobile-action .submit-button{
                min-height:50px;
            }
            .mobile-action .mobile-action-row{
                display:grid;
                grid-template-columns:minmax(0,1fr) auto;
                align-items:center;
                gap:10px;
            }
            .mobile-total{
                min-width:0;
            }
            .mobile-total-label{
                display:block;
                color:var(--muted);
                font-size:9px;
                line-height:1.2;
            }
            .mobile-total-value{
                display:block;
                margin-top:2px;
                color:var(--heading);
                font-size:15px;
                font-weight:900;
                white-space:nowrap;
                overflow:hidden;
                text-overflow:ellipsis;
            }
            .mobile-action .submit-button{
                width:auto;
                min-width:170px;
                padding:0 18px;
                font-size:12px;
            }
            .mobile-action .back-mobile{
                display:block;
                margin-top:7px;
                color:var(--muted);
                text-align:center;
                font-size:10px;
                text-decoration:none;
            }
        }

        @media (max-width: 370px){
            .table-badge{font-size:10px;padding:7px 9px}
            .brand-title{font-size:14px}
            .page{padding-left:10px;padding-right:10px}
            .card-body{padding-left:14px;padding-right:14px}
            .mobile-action{padding-left:10px;padding-right:10px}
            .mobile-action .submit-button{min-width:150px;padding:0 12px}
        }

        @media (prefers-reduced-motion: reduce){
            *,*::before,*::after{
                scroll-behavior:auto!important;
                transition:none!important;
                animation:none!important;
            }
        }
    </style>
</head>

<body>
<header class="topbar">
    <div class="topbar-inner">
        <div class="brand">
            <div class="brand-icon">🛒</div>
            <div class="brand-copy">
                <div class="brand-title">Checkout Pesanan</div>
                <div class="brand-subtitle">Lengkapi data pelanggan dan pilih metode pembayaran</div>
            </div>
        </div>

        <div class="table-badge">
            <span class="table-dot"></span>
            {{ $table->name }}
        </div>
    </div>
</header>

<main class="page">
    <div class="page-heading">
        <h1>Konfirmasi Pesanan</h1>
        <p>Periksa pesanan Anda, isi data pelanggan, lalu pilih metode pembayaran.</p>
    </div>

    <div id="errorAlert" class="alert alert-error"></div>

    @if($errors->any())
        <div class="alert alert-error show">
            <strong>Pesanan belum dapat diproses.</strong>
            <ul style="margin:8px 0 0;padding-left:20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error show">{{ session('error') }}</div>
    @endif

    <form method="POST"
          action="{{ route('customer.checkout.store', $table->id) }}"
          id="checkoutForm">
        @csrf

        <input type="hidden" name="cart" id="cartInput" value="{{ old('cart') }}">

        <div class="checkout-grid">
            <div class="left-column">

                <section class="card">
                    <div class="card-header">
                        <div class="card-header-icon">🧾</div>
                        <div class="card-header-copy">
                            <h2>Detail Pesanan</h2>
                            <p>Pesanan dari {{ $table->name }}</p>
                        </div>
                    </div>
                    <div class="card-body" id="orderItemsContainer">
                        <div class="empty-cart">
                            <div class="empty-icon">🛒</div>
                            <h3>Memuat pesanan...</h3>
                            <p>Mohon tunggu.</p>
                        </div>
                    </div>
                </section>

                <section class="card">
                    <div class="card-header">
                        <div class="card-header-icon">👤</div>
                        <div class="card-header-copy">
                            <h2>Data Pelanggan</h2>
                            <p>Isi data dengan benar untuk informasi pesanan dan pembayaran.</p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="customer-form">

                            <div class="form-group">
                                <label for="customer_name" class="form-label">
                                    Nama Lengkap <span class="required">*</span>
                                </label>
                                <div class="input-wrap">
                                    <span class="input-icon">👤</span>
                                    <input type="text"
                                           id="customer_name"
                                           name="customer_name"
                                           class="form-input @error('customer_name') is-invalid @enderror"
                                           placeholder="Masukkan nama pelanggan"
                                           value="{{ old('customer_name') }}"
                                           maxlength="100"
                                           autocomplete="name"
                                           required>
                                </div>
                                @error('customer_name')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="customer_phone" class="form-label">
                                    Nomor WhatsApp / HP <span class="required">*</span>
                                </label>
                                <div class="input-wrap">
                                    <span class="input-icon">☎</span>
                                    <input type="tel"
                                           id="customer_phone"
                                           name="customer_phone"
                                           class="form-input @error('customer_phone') is-invalid @enderror"
                                           placeholder="Contoh: 08123456789"
                                           value="{{ old('customer_phone') }}"
                                           maxlength="30"
                                           autocomplete="tel"
                                           inputmode="tel"
                                           required>
                                </div>
                                @error('customer_phone')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="customer_email" class="form-label">
                                    Email <span class="required">*</span>
                                </label>
                                <div class="input-wrap">
                                    <span class="input-icon">✉</span>
                                    <input type="email"
                                           id="customer_email"
                                           name="customer_email"
                                           class="form-input @error('customer_email') is-invalid @enderror"
                                           placeholder="contoh@email.com"
                                           value="{{ old('customer_email') }}"
                                           maxlength="150"
                                           autocomplete="email"
                                           inputmode="email"
                                           required>
                                </div>
                                @error('customer_email')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="payment-section">
                                <div class="section-heading">
                                    <h3>Metode Pembayaran</h3>
                                    <p>Pilih cara pembayaran yang paling nyaman untuk Anda.</p>
                                </div>

                                <div class="payment-options">

                                    <label class="payment-option {{ old('payment_method','qris') === 'qris' ? 'selected' : '' }}"
                                           data-payment-option>
                                        <input type="radio"
                                               name="payment_method"
                                               value="qris"
                                               {{ old('payment_method','qris') === 'qris' ? 'checked' : '' }}>
                                        <div class="payment-icon">📱</div>
                                        <div class="payment-content">
                                            <span class="payment-title">QRIS</span>
                                            <span class="payment-description">Scan QR menggunakan mobile banking atau e-wallet.</span>
                                        </div>
                                        <span class="payment-tag">Online</span>
                                        <span class="payment-check">✓</span>
                                    </label>

                                    <label class="payment-option {{ old('payment_method') === 'va' ? 'selected' : '' }}"
                                           data-payment-option>
                                        <input type="radio"
                                               name="payment_method"
                                               value="va"
                                               {{ old('payment_method') === 'va' ? 'checked' : '' }}>
                                        <div class="payment-icon">🏦</div>
                                        <div class="payment-content">
                                            <span class="payment-title">Virtual Account</span>
                                            <span class="payment-description">BCA, BNI, BRI, Permata, dan bank yang tersedia.</span>
                                        </div>
                                        <span class="payment-tag">Online</span>
                                        <span class="payment-check">✓</span>
                                    </label>

                                    <label class="payment-option {{ old('payment_method') === 'cash' ? 'selected' : '' }}"
                                           data-payment-option>
                                        <input type="radio"
                                               name="payment_method"
                                               value="cash"
                                               {{ old('payment_method') === 'cash' ? 'checked' : '' }}>
                                        <div class="payment-icon">💵</div>
                                        <div class="payment-content">
                                            <span class="payment-title">Bayar di Kasir</span>
                                            <span class="payment-description">Bayar langsung kepada kasir setelah melakukan pesanan.</span>
                                        </div>
                                        <span class="payment-check">✓</span>
                                    </label>

                                </div>

                                <div class="payment-helper" id="paymentHelper">
                                    Pembayaran online akan dilanjutkan melalui halaman pembayaran yang aman.
                                </div>
                            </div>

                        </div>
                    </div>
                </section>
            </div>

            <aside>
                <section class="card summary-card">
                    <div class="card-header">
                        <div class="card-header-icon">💰</div>
                        <div class="card-header-copy">
                            <h2>Ringkasan Pesanan</h2>
                            <p>Periksa kembali total pesanan Anda.</p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="summary-list">
                            <div class="summary-row">
                                <span>Jumlah item</span>
                                <span class="summary-value" id="summaryItemCount">0 item</span>
                            </div>
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span class="summary-value" id="summarySubtotal">Rp 0</span>
                            </div>
                        </div>

                        <div class="summary-total">
                            <span class="summary-total-label">Total Pembayaran</span>
                            <span class="summary-total-value" id="summaryTotal">Rp 0</span>
                        </div>

                        <div class="summary-hint">
                            Pilih metode pembayaran di sebelah kiri untuk melanjutkan proses transaksi.
                        </div>

                        <div class="actions">
                            <button type="submit" class="submit-button" id="submitButton" disabled>
                                <span class="button-content" id="submitButtonContent">
                                    <span> Lanjutkan Pembayaran </span>
                                    <span>→</span>
                                </span>
                            </button>

                            <a href="{{ url('/order/table/' . $table->qr_token) }}" class="back-button">
                                ← Kembali ke Menu
                            </a>
                        </div>

                        <div class="secure-info">
                            <span class="secure-icon">🔒</span>
                            <span>Data pesanan Anda diproses secara aman dan hanya digunakan untuk kebutuhan transaksi.</span>
                        </div>
                    </div>
                </section>
            </aside>
        </div>

        <div class="mobile-action">
            <div class="mobile-action-row">
                <div class="mobile-total">
                    <span class="mobile-total-label">Total Pembayaran</span>
                    <span class="mobile-total-value" id="mobileTotal">Rp 0</span>
                </div>
                <button type="submit" class="submit-button" id="mobileSubmitButton" disabled>
                    <span class="button-content">
                        <span>Lanjutkan</span>
                        <span>→</span>
                    </span>
                </button>
            </div>
            <a href="{{ url('/order/table/' . $table->qr_token) }}" class="back-mobile">← Kembali ke Menu</a>
        </div>
    </form>
</main>

<script>

    /*
    |--------------------------------------------------------------------------
    | CART KEY
    |--------------------------------------------------------------------------
    |
    | Pastikan menu.blade.php menggunakan key yang sama:
    |
    | customer_cart_table_ID
    |
    */

    const cartKey =
        'customer_cart_table_{{ $table->id }}';


    /*
    |--------------------------------------------------------------------------
    | ELEMENT
    |--------------------------------------------------------------------------
    */

    const checkoutForm =
        document.getElementById(
            'checkoutForm'
        );


    const cartInput =
        document.getElementById(
            'cartInput'
        );


    const orderItemsContainer =
        document.getElementById(
            'orderItemsContainer'
        );


    const summaryItemCount =
        document.getElementById(
            'summaryItemCount'
        );


    const summarySubtotal =
        document.getElementById(
            'summarySubtotal'
        );


    const summaryTotal =
        document.getElementById(
            'summaryTotal'
        );


    const submitButton =
        document.getElementById(
            'submitButton'
        );


    const submitButtonContent =
        document.getElementById(
            'submitButtonContent'
        );

    const mobileSubmitButton =
        document.getElementById(
            'mobileSubmitButton'
        );

    const mobileTotal =
        document.getElementById(
            'mobileTotal'
        );

    const paymentHelper =
        document.getElementById(
            'paymentHelper'
        );


    const errorAlert =
        document.getElementById(
            'errorAlert'
        );


    /*
    |--------------------------------------------------------------------------
    | FORMAT RUPIAH
    |--------------------------------------------------------------------------
    */

    function formatRupiah(value)
    {

        const number =
            Number(value) || 0;


        return new Intl.NumberFormat(
            'id-ID',
            {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }
        ).format(number);

    }


    /*
    |--------------------------------------------------------------------------
    | ESCAPE HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value)
    {

        return String(value ?? '')
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;')
            .replace(/'/g,'&#039;');

    }


    /*
    |--------------------------------------------------------------------------
    | NORMALIZE IMAGE URL
    |--------------------------------------------------------------------------
    */

    function normalizeImageUrl(image)
    {

        if (!image) {

            return '';

        }


        let url =
            String(image).trim();


        if (!url) {

            return '';

        }


        /*
        |--------------------------------------------------------------------------
        | JIKA SUDAH URL
        |--------------------------------------------------------------------------
        */

        if (
            url.startsWith('http://')
            ||
            url.startsWith('https://')
            ||
            url.startsWith('/')
        ) {

            return url;

        }


        /*
        |--------------------------------------------------------------------------
        | JIKA PATH STORAGE
        |--------------------------------------------------------------------------
        */

        if (
            url.startsWith('storage/')
        ) {

            return '/' + url;

        }


        return '/storage/' + url;

    }


    /*
    |--------------------------------------------------------------------------
    | GET CART
    |--------------------------------------------------------------------------
    */

    function getCart()
    {

        try {

            const savedCart =
                localStorage.getItem(
                    cartKey
                );


            if (!savedCart) {

                return [];

            }


            const parsed =
                JSON.parse(
                    savedCart
                );


            if (!Array.isArray(parsed)) {

                return [];

            }


            return parsed

                .map(function(item)
                {

                    return {

                        id:
                            Number(
                                item.id || 0
                            ),

                        name:
                            String(
                                item.name || 'Produk'
                            ),

                        price:
                            Number(
                                item.price || 0
                            ),

                        image:
                            String(
                                item.image || ''
                            ),

                        quantity:
                            Number(
                                item.quantity || 0
                            )

                    };

                })

                .filter(function(item)
                {

                    return (
                        item.id > 0
                        &&
                        item.quantity > 0
                    );

                });

        }

        catch(error) {

            console.error(
                'Gagal membaca cart:',
                error
            );

            return [];

        }

    }


    /*
    |--------------------------------------------------------------------------
    | SHOW ERROR
    |--------------------------------------------------------------------------
    */

    function showError(message)
    {

        errorAlert.textContent =
            message;


        errorAlert.classList.add(
            'show'
        );


        window.scrollTo({

            top: 0,

            behavior: 'smooth'

        });

    }


    /*
    |--------------------------------------------------------------------------
    | HIDE ERROR
    |--------------------------------------------------------------------------
    */

    function hideError()
    {

        errorAlert.textContent =
            '';


        errorAlert.classList.remove(
            'show'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | RENDER CART
    |--------------------------------------------------------------------------
    */

    function renderCart()
    {

        const cart =
            getCart();


        /*
        |--------------------------------------------------------------------------
        | EMPTY
        |--------------------------------------------------------------------------
        */

        if (cart.length === 0)
        {

            orderItemsContainer.innerHTML = `

                <div class="empty-cart">

                    <div class="empty-icon">
                        🛒
                    </div>

                    <h3>
                        Keranjang masih kosong
                    </h3>

                    <p>
                        Silakan kembali ke menu
                        dan pilih produk terlebih dahulu.
                    </p>

                </div>

            `;


            summaryItemCount.textContent =
                '0 item';


            summarySubtotal.textContent =
                formatRupiah(0);


            summaryTotal.textContent =
                formatRupiah(0);

            if (mobileTotal) {
                mobileTotal.textContent = formatRupiah(0);
            }

            submitButton.disabled =
                true;

            if (mobileSubmitButton) {
                mobileSubmitButton.disabled = true;
            }


            return;

        }


        /*
        |--------------------------------------------------------------------------
        | ENABLE
        |--------------------------------------------------------------------------
        */

        submitButton.disabled =
            false;


        let total =
            0;


        let totalQuantity =
            0;


        /*
        |--------------------------------------------------------------------------
        | ITEMS
        |--------------------------------------------------------------------------
        */

        const itemsHtml =
            cart.map(function(item)
            {

                const subtotal =
                    item.price *
                    item.quantity;


                total +=
                    subtotal;


                totalQuantity +=
                    item.quantity;


                const imageUrl =
                    normalizeImageUrl(
                        item.image
                    );


                const imageHtml =
                    imageUrl

                    ?

                    `

                        <img
                            src="${escapeHtml(imageUrl)}"
                            alt="${escapeHtml(item.name)}"
                            loading="lazy"
                            onerror="
                                this.style.display='none';
                                this.parentElement.innerHTML='<div class=&quot;product-placeholder&quot;>🍽️</div>';
                            "
                        >

                    `

                    :

                    `

                        <div class="product-placeholder">
                            🍽️
                        </div>

                    `;


                /*
                |--------------------------------------------------------------------------
                | PENTING
                |--------------------------------------------------------------------------
                |
                | Tidak ada lagi item.note.
                | Catatan produk sudah dihapus dari checkout.
                |
                */

                return `

                    <div class="order-item">

                        <div class="product-image">

                            ${imageHtml}

                        </div>


                        <div class="product-info">

                            <div class="product-name">

                                ${escapeHtml(item.name)}

                            </div>


                            <div class="product-price">

                                ${formatRupiah(item.price)}

                            </div>


                            <div class="product-quantity">

                                ${item.quantity} ×

                            </div>

                        </div>


                        <div class="item-subtotal">

                            ${formatRupiah(subtotal)}

                        </div>

                    </div>

                `;

            }).join('');


        orderItemsContainer.innerHTML = `

            <div class="order-items">

                ${itemsHtml}

            </div>

        `;


        /*
        |--------------------------------------------------------------------------
        | SUMMARY
        |--------------------------------------------------------------------------
        */

        summaryItemCount.textContent =
            totalQuantity + ' item';


        summarySubtotal.textContent =
            formatRupiah(total);


        summaryTotal.textContent =
            formatRupiah(total);

        if (mobileTotal) {
            mobileTotal.textContent = formatRupiah(total);
        }

        if (mobileSubmitButton) {
            mobileSubmitButton.disabled = false;
        }

    }


    /*
    |--------------------------------------------------------------------------
    | PAYMENT OPTION
    |--------------------------------------------------------------------------
    */

    function updatePaymentOptions()
    {

        let selectedMethod = 'qris';

        document
            .querySelectorAll(
                '[data-payment-option]'
            )
            .forEach(function(option)
            {

                const radio =
                    option.querySelector(
                        'input[type="radio"]'
                    );

                option.classList.toggle(
                    'selected',
                    radio.checked
                );

                if (radio.checked) {
                    selectedMethod = radio.value;
                }

            });

        if (paymentHelper) {

            if (selectedMethod === 'qris') {
                paymentHelper.textContent =
                    'QRIS diproses secara online. Setelah melanjutkan, Anda akan diarahkan ke halaman pembayaran untuk menyelesaikan transaksi.';
            } else if (selectedMethod === 'va') {
                paymentHelper.textContent =
                    'Virtual Account diproses secara online. Detail pembayaran akan ditampilkan setelah Anda melanjutkan.';
            } else {
                paymentHelper.textContent =
                    'Pilih Bayar di Kasir jika Anda ingin melakukan pembayaran langsung kepada kasir setelah pesanan dibuat.';
            }

        }

    }


    document
        .querySelectorAll(
            '[data-payment-option]'
        )
        .forEach(function(option)
        {

            const radio =
                option.querySelector(
                    'input[type="radio"]'
                );


            option.addEventListener(
                'click',
                function()
                {

                    radio.checked =
                        true;


                    updatePaymentOptions();

                }
            );


            radio.addEventListener(
                'change',
                function()
                {

                    updatePaymentOptions();

                }
            );

        });


    /*
    |--------------------------------------------------------------------------
    | FORM SUBMIT
    |--------------------------------------------------------------------------
    */

    checkoutForm.addEventListener(
        'submit',
        function(event)
        {

            hideError();


            /*
            |--------------------------------------------------------------------------
            | CART
            |--------------------------------------------------------------------------
            */

            const cart =
                getCart();


            if (cart.length === 0)
            {

                event.preventDefault();


                showError(
                    'Keranjang pesanan masih kosong. Silakan pilih produk terlebih dahulu.'
                );


                return;

            }


            /*
            |--------------------------------------------------------------------------
            | CUSTOMER NAME
            |--------------------------------------------------------------------------
            */

            const customerName =
                document
                    .getElementById(
                        'customer_name'
                    )
                    .value
                    .trim();


            if (!customerName)
            {

                event.preventDefault();


                showError(
                    'Nama pelanggan wajib diisi.'
                );


                document
                    .getElementById(
                        'customer_name'
                    )
                    .focus();


                return;

            }


            /*
            |--------------------------------------------------------------------------
            | PHONE
            |--------------------------------------------------------------------------
            */

            const customerPhone =
                document
                    .getElementById(
                        'customer_phone'
                    )
                    .value
                    .trim();


            if (!customerPhone)
            {

                event.preventDefault();


                showError(
                    'Nomor HP wajib diisi.'
                );


                document
                    .getElementById(
                        'customer_phone'
                    )
                    .focus();


                return;

            }


            /*
            |--------------------------------------------------------------------------
            | EMAIL
            |--------------------------------------------------------------------------
            */

            const customerEmail =
                document
                    .getElementById(
                        'customer_email'
                    )
                    .value
                    .trim();


            if (!customerEmail)
            {

                event.preventDefault();


                showError(
                    'Email wajib diisi.'
                );


                document
                    .getElementById(
                        'customer_email'
                    )
                    .focus();


                return;

            }


            /*
            |--------------------------------------------------------------------------
            | PAYMENT
            |--------------------------------------------------------------------------
            */

            const paymentMethod =
                document.querySelector(
                    'input[name="payment_method"]:checked'
                );


            if (!paymentMethod)
            {

                event.preventDefault();


                showError(
                    'Silakan pilih metode pembayaran.'
                );


                return;

            }


            /*
            |--------------------------------------------------------------------------
            | CART JSON
            |--------------------------------------------------------------------------
            |
            | Ini bagian penting.
            |
            | Controller membutuhkan:
            |
            | cart = JSON string
            |
            */

            cartInput.value =
                JSON.stringify(
                    cart.map(function(item)
                    {

                        return {

                            id:
                                Number(
                                    item.id
                                ),

                            quantity:
                                Number(
                                    item.quantity
                                )

                        };

                    })
                );


            /*
            |--------------------------------------------------------------------------
            | DEBUG
            |--------------------------------------------------------------------------
            |
            | Bisa dihapus setelah sistem sudah normal.
            |
            */

            console.log(
                'Checkout cart:',
                cartInput.value
            );


            /*
            |--------------------------------------------------------------------------
            | LOADING
            |--------------------------------------------------------------------------
            */

            submitButton.disabled =
                true;

            if (mobileSubmitButton) {
                mobileSubmitButton.disabled = true;
                mobileSubmitButton.classList.add('loading');
            }

            submitButton.classList.add(
                'loading'
            );


            submitButtonContent.innerHTML = `

                <span class="spinner"></span>

                <span>
                    Memproses pesanan...
                </span>

            `;

        }
    );


    /*
    |--------------------------------------------------------------------------
    | INITIALIZE
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'DOMContentLoaded',
        function()
        {

            renderCart();

            updatePaymentOptions();

            if (mobileSubmitButton) {
                mobileSubmitButton.disabled = getCart().length === 0;
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | STORAGE CHANGE
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        'storage',
        function(event)
        {

            if (
                event.key === cartKey
            )
            {

                renderCart();

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | PAGE SHOW
    |--------------------------------------------------------------------------
    |
    | Ketika user kembali ke halaman checkout.
    |
    */

    window.addEventListener(
        'pageshow',
        function()
        {

            renderCart();

            updatePaymentOptions();

            submitButton.disabled =
                getCart().length === 0;

            submitButton.classList.remove(
                'loading'
            );

            submitButtonContent.innerHTML = `

                <span>
                    Lanjutkan Pesanan
                </span>

                <span>
                    →
                </span>

            `;

        }
    );

</script>
<script>
    if (typeof mobileSubmitButton !== 'undefined' && mobileSubmitButton) {
        mobileSubmitButton.addEventListener('click', function () {
            if (checkoutForm && typeof checkoutForm.requestSubmit === 'function') {
                // The mobile button is inside the same form; normal submit behavior is retained.
            }
        });
    }
</script>
