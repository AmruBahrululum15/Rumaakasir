@extends('layouts.app')

@section('title', 'Kasir / Tambah Penjualan')

@section('content_header')
@endsection

@section('content')

<style>
/* ============================================================
   POS SALES PAGE
   ============================================================ */

:root {
    --pos-orange: #ff7a18;
    --pos-orange-dark: #f36b0a;
    --pos-orange-light: #ff9b4a;
    --pos-orange-soft: #fff1e7;
    --pos-orange-bg: #fff8f1;

    --pos-cream: #fffaf4;
    --pos-bg: #f6f3ef;

    --pos-text: #172033;
    --pos-muted: #8b8f99;

    --pos-border: #eee5dc;

    --pos-green: #20b66f;
    --pos-green-soft: #ebfaf2;

    --pos-red: #e85d4a;
    --pos-red-soft: #fff0ec;

    --pos-purple: #7957e8;
    --pos-purple-soft: #f1edff;
}


/* ============================================================
   WRAPPER
   ============================================================ */

.pos-sales-page {
    width: 100%;
    padding: 0 0 22px;
    color: var(--pos-text);
}


/* ============================================================
   PAGE HEADER
   ============================================================ */

.sales-page-header {
    position: relative;

    min-height: 74px;

    margin-bottom: 10px;

    padding: 14px 18px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    overflow: hidden;

    border-radius: 11px;

    background:
        linear-gradient(
            110deg,
            #ffb45e 0%,
            #ffc87f 45%,
            #ffe0ae 100%
        );

    box-shadow:
        0 5px 18px rgba(255, 122, 24, .08);
}


/* dekorasi header */

.sales-page-header::before {
    content: "";

    position: absolute;

    width: 170px;
    height: 170px;

    right: 90px;
    top: -85px;

    border-radius: 50%;

    background:
        rgba(255,255,255,.12);
}


.sales-page-header::after {
    content: "";

    position: absolute;

    width: 120px;
    height: 120px;

    right: 300px;
    bottom: -75px;

    border-radius: 50%;

    background:
        rgba(255,255,255,.12);
}


/* ============================================================
   HEADER LEFT
   ============================================================ */

.sales-header-left {
    position: relative;
    z-index: 2;

    display: flex;
    align-items: center;
    gap: 12px;
}


.sales-header-icon {
    width: 48px;
    height: 48px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 12px;

    background:
        rgba(255,255,255,.78);

    color:
        var(--pos-orange);

    font-size: 23px;

    box-shadow:
        0 4px 12px rgba(255,255,255,.20);
}


.sales-header-title {
    margin: 0;

    color:
        #242a35;

    font-size: 20px;

    font-weight: 800;

    line-height: 1.1;
}


.sales-header-subtitle {
    margin-top: 4px;

    color:
        #8d735c;

    font-size: 10px;

    font-weight: 500;
}


/* ============================================================
   HEADER BUTTON
   ============================================================ */

.sales-history-btn {
    position: relative;
    z-index: 3;

    display: inline-flex;
    align-items: center;
    gap: 7px;

    padding: 9px 14px;

    border: none;

    border-radius: 9px;

    background: rgba(255,255,255,.82);

    color: var(--pos-orange-dark);

    font-size: 11px;

    font-weight: 700;

    text-decoration: none;

    transition: .2s ease;
}


.sales-history-btn:hover {
    background: #ffffff;

    color:
        var(--pos-orange-dark);

    transform:
        translateY(-1px);

    text-decoration: none;
}


/* ============================================================
   TRANSACTION INFO
   ============================================================ */

.sale-info-card {
    margin-bottom: 10px;

    padding: 13px 18px;

    border:
        1px solid var(--pos-border);

    border-radius: 11px;

    background: #ffffff;

    box-shadow:
        0 4px 14px rgba(30,20,10,.035);
}


.sale-info-grid {
    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 16px;
}


.sale-info-field label {
    display: block;

    margin-bottom: 5px;

    color:
        #536071;

    font-size: 10px;

    font-weight: 700;
}


.sale-info-field label i {
    margin-right: 4px;

    color:
        var(--pos-orange);
}


.sale-info-field .form-control {
    height: 35px;

    padding:
        6px 10px;

    border:
        1px solid #e8e0d7 !important;

    border-radius: 7px !important;

    background:
        #ffffff !important;

    color:
        #6d7580 !important;

    font-size: 11px !important;
}


.sale-info-field .form-control:focus {
    border-color:
        var(--pos-orange) !important;

    box-shadow:
        0 0 0 3px
        rgba(255,122,24,.08) !important;
}


/* ============================================================
   MAIN POS GRID
   ============================================================ */

.pos-main-grid {
    display: grid;

    grid-template-columns:
        minmax(0, 1fr) 335px;

    gap: 12px;

    align-items: start;
}


/* ============================================================
   PRODUCT PANEL
   ============================================================ */

.product-panel {
    background: #ffffff;

    border:
        1px solid var(--pos-border);

    border-radius: 11px;

    overflow: hidden;

    box-shadow:
        0 4px 15px rgba(30,20,10,.035);
}


/* ============================================================
   PRODUCT HEADER
   ============================================================ */

.product-panel-header {
    padding: 13px 16px 10px;

    border-bottom:
        1px solid #f1eae3;
}


.product-header-row {
    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 15px;
}


.product-heading {
    display: flex;
    align-items: center;

    gap: 8px;
}


.product-heading-icon {
    width: 31px;
    height: 31px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 8px;

    background:
        var(--pos-orange-soft);

    color:
        var(--pos-orange);

    font-size: 13px;
}


.product-heading h5 {
    margin: 0;

    color:
        #252c38;

    font-size: 14px;

    font-weight: 800;
}


.product-heading small {
    display: block;

    margin-top: 2px;

    color:
        #a0a5ad;

    font-size: 9px;
}


/* ============================================================
   SEARCH
   ============================================================ */

.product-search {
    position: relative;

    width: 305px;
}


.product-search i {
    position: absolute;

    left: 11px;
    top: 10px;

    z-index: 2;

    color:
        #b2b4ba;

    font-size: 11px;
}


.product-search input {
    width: 100%;

    height: 31px;

    padding:
        5px 10px 5px 31px;

    border:
        1px solid #eadfd5 !important;

    border-radius: 7px !important;

    background:
        #fffdfb !important;

    color:
        #3c4652 !important;

    font-size: 10px !important;
}


.product-search input:focus {
    border-color:
        var(--pos-orange) !important;

    box-shadow:
        0 0 0 3px
        rgba(255,122,24,.08) !important;
}


/* ============================================================
   CATEGORY
   ============================================================ */

.category-container {
    display: flex;

    gap: 7px;

    margin-top: 10px;

    overflow-x: auto;

    padding-bottom: 1px;
}


.category-container::-webkit-scrollbar {
    height: 0;
}


.category-btn {
    flex: 0 0 auto;

    padding:
        6px 12px;

    border:
        1px solid #eee3d9;

    border-radius: 20px;

    background:
        #ffffff;

    color:
        #7c746c;

    font-size: 9px;

    font-weight: 600;

    cursor: pointer;

    transition:
        all .18s ease;
}


.category-btn:hover {
    border-color:
        #ffbf91;

    background:
        var(--pos-orange-soft);

    color:
        var(--pos-orange-dark);
}


.category-btn.active {
    border-color:
        var(--pos-orange);

    background:
        linear-gradient(
            135deg,
            #ff8b32,
            #ff7414
        );

    color:
        #ffffff;

    box-shadow:
        0 3px 8px
        rgba(255,122,24,.15);
}


/* ============================================================
   PRODUCT GRID
   ============================================================ */

.product-grid {
    display: grid;

    grid-template-columns:
        repeat(5, minmax(0, 1fr));

    gap: 9px;

    padding: 11px;

    max-height:
        calc(100vh - 305px);

    min-height:
        420px;

    overflow-y: auto;

    align-content: start;
}


.product-grid::-webkit-scrollbar {
    width: 5px;
}


.product-grid::-webkit-scrollbar-track {
    background:
        transparent;
}


.product-grid::-webkit-scrollbar-thumb {
    background:
        #e4d9cf;

    border-radius:
        20px;
}


/* ============================================================
   PRODUCT CARD
   ============================================================ */

.product-card {
    position: relative;

    min-width: 0;

    border:
        1px solid #ece5de;

    border-radius: 9px;

    background:
        #ffffff;

    overflow:
        hidden;

    cursor:
        pointer;

    transition:
        all .18s ease;

    box-shadow:
        0 2px 5px
        rgba(30,20,10,.025);
}


.product-card:hover {
    transform:
        translateY(-2px);

    border-color:
        #ffc18e;

    box-shadow:
        0 7px 17px
        rgba(255,122,24,.10);
}


.product-card:active {
    transform:
        scale(.98);
}


.product-card.out-of-stock {
    cursor:
        not-allowed;

    opacity:
        .58;
}


.product-card.out-of-stock:hover {
    transform:
        none;

    border-color:
        #ece5de;

    box-shadow:
        0 2px 5px
        rgba(30,20,10,.025);
}


/* ============================================================
   PRODUCT IMAGE
   ============================================================ */

.product-image-wrapper {
    position: relative;

    width: 100%;

    height: 92px;

    display: flex;
    align-items: center;
    justify-content: center;

    overflow: hidden;

    background:
        #f8f5f1;
}


.product-image {
    width: 100%;
    height: 100%;

    object-fit: cover;

    display: block;
}


.product-image-placeholder {
    color:
        #d7cec5;

    font-size:
        30px;
}


/* ============================================================
   PRODUCT INFO
   ============================================================ */

.product-info {
    position: relative;

    padding:
        7px 8px 8px;
}


.product-name {
    height: 28px;

    overflow: hidden;

    color:
        #252c35;

    font-size:
        9px;

    font-weight:
        700;

    line-height:
        1.45;

    margin-bottom:
        3px;
}


.product-price {
    color:
        var(--pos-orange-dark);

    font-size:
        10px;

    font-weight:
        800;

    line-height:
        1.2;
}


.product-stock {
    margin-top:
        4px;

    padding-right:
        24px;

    font-size:
        8px;

    font-weight:
        500;
}


.stock-available {
    color:
        #22a765;
}


.stock-low {
    color:
        #e88920;
}


.stock-empty {
    color:
        #e85d4a;
}


/* ============================================================
   PLUS BUTTON
   ============================================================ */

.product-add-icon {
    position: absolute;

    right: 6px;
    bottom: 6px;

    width: 22px;
    height: 22px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background:
        linear-gradient(
            135deg,
            #ff913c,
            #ff7414
        );

    color:
        #ffffff;

    font-size:
        9px;

    box-shadow:
        0 3px 7px
        rgba(255,122,24,.18);
}


/* ============================================================
   CART PANEL
   ============================================================ */

.cart-panel {
    position: sticky;

    top: 10px;

    background:
        #ffffff;

    border:
        1px solid var(--pos-border);

    border-radius:
        11px;

    overflow:
        hidden;

    box-shadow:
        0 4px 15px
        rgba(30,20,10,.035);
}


/* ============================================================
   CART HEADER
   ============================================================ */

.cart-header {
    padding:
        13px 15px;

    border-bottom:
        1px solid #f1eae3;
}


.cart-title {
    display:
        flex;

    align-items:
        center;

    gap:
        8px;
}


.cart-title-icon {
    color:
        var(--pos-orange);

    font-size:
        14px;
}


.cart-title h5 {
    margin:
        0;

    color:
        #252c35;

    font-size:
        14px;

    font-weight:
        800;
}


.cart-item-info {
    margin-top:
        2px;

    color:
        #a0a5ad;

    font-size:
        9px;
}


/* ============================================================
   CLEAR BUTTON
   ============================================================ */

.clear-cart-btn {
    border:
        none;

    background:
        var(--pos-red-soft);

    color:
        var(--pos-red);

    border-radius:
        6px;

    padding:
        5px 7px;

    font-size:
        8px;

    cursor:
        pointer;
}


/* ============================================================
   CART ITEMS
   ============================================================ */

.cart-items {
    min-height:
        165px;

    max-height:
        270px;

    overflow-y:
        auto;
}


.cart-items::-webkit-scrollbar {
    width:
        4px;
}


.cart-items::-webkit-scrollbar-thumb {
    background:
        #e3d9cf;

    border-radius:
        20px;
}


/* ============================================================
   EMPTY CART
   ============================================================ */

.empty-cart {
    min-height:
        165px;

    padding:
        30px 15px;

    display:
        flex;

    flex-direction:
        column;

    align-items:
        center;

    justify-content:
        center;

    text-align:
        center;

    color:
        #aaa9a7;
}


.empty-cart i {
    margin-bottom:
        10px;

    color:
        #f2c59d;

    font-size:
        42px;
}


.empty-cart h6 {
    margin:
        0 0 4px;

    color:
        #8c8c8c;

    font-size:
        11px;

    font-weight:
        700;
}


.empty-cart small {
    color:
        #b2b0ad;

    font-size:
        8px;
}


/* ============================================================
   CART ITEM
   ============================================================ */

.cart-item {
    padding:
        9px 12px;

    border-bottom:
        1px solid #f3eee9;
}


.cart-item:last-child {
    border-bottom:
        none;
}


.cart-item-name {
    max-width:
        70%;

    margin-bottom:
        2px;

    color:
        #303641;

    font-size:
        10px;

    font-weight:
        700;

    line-height:
        1.35;
}


.cart-item-price {
    color:
        #999dA5;

    font-size:
        8px;
}


.cart-item-subtotal {
    color:
        var(--pos-orange-dark);

    font-size:
        10px;

    font-weight:
        800;
}


.cart-item small {
    color:
        #aaa;

    font-size:
        7px;
}


/* ============================================================
   QUANTITY
   ============================================================ */

.quantity-control {
    display:
        flex;

    align-items:
        center;

    margin-top:
        5px;
}


.quantity-btn {
    width:
        22px;

    height:
        22px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border:
        1px solid #eadfd5;

    border-radius:
        6px;

    background:
        #ffffff;

    color:
        #777;

    font-size:
        8px;

    cursor:
        pointer;
}


.quantity-btn:hover {
    border-color:
        var(--pos-orange);

    color:
        var(--pos-orange);

    background:
        var(--pos-orange-soft);
}


.quantity-value {
    width:
        27px;

    text-align:
        center;

    color:
        #333b46;

    font-size:
        9px;

    font-weight:
        700;
}


.remove-item {
    padding:
        3px;

    border:
        none;

    background:
        transparent;

    color:
        #c4a69a;

    font-size:
        9px;

    cursor:
        pointer;
}


.remove-item:hover {
    color:
        var(--pos-red);
}


/* ============================================================
   CART SUMMARY
   ============================================================ */

.cart-summary {
    padding:
        11px 15px;

    border-top:
        1px solid #f1eae3;
}


.summary-row {
    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    margin-bottom:
        8px;
}


.summary-row:last-child {
    margin-bottom:
        0;
}


.summary-label {
    color:
        #8d9199;

    font-size:
        9px;
}


.summary-total-label {
    color:
        #323945;

    font-size:
        11px;

    font-weight:
        700;
}


.summary-total {
    color:
        var(--pos-orange-dark);

    font-size:
        16px;

    font-weight:
        800;
}


/* ============================================================
   PAYMENT
   ============================================================ */

.payment-section {
    padding:
        13px 15px;

    border-top:
        1px solid #f1eae3;

    background:
        #fffaf6;
}


.payment-label {
    display:
        block;

    margin-bottom:
        5px;

    color:
        #59616d;

    font-size:
        10px;

    font-weight:
        700;
}


.payment-label i {
    margin-right:
        4px;

    color:
        var(--pos-orange);
}


.pos-sales-page .payment-method-options {
    gap: 6px;
    padding: 4px;
    border: 1px solid var(--pos-border);
    border-radius: 12px;
    background: #f7f5f2;
}

.pos-sales-page .payment-method-options > .payment-method-option {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 1 1 0;
    min-width: 0;
    min-height: 42px;
    margin: 0;
    padding: 9px 6px;
    border: 1px solid transparent;
    border-radius: 8px;
    background: transparent;
    color: #59616d;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.4;
    box-shadow: none;
    transition: background-color .15s ease, border-color .15s ease, color .15s ease;
}

.pos-sales-page .payment-method-option:hover {
    background: #ffffff;
    color: var(--pos-text);
}

.pos-sales-page .payment-method-option.active,
.pos-sales-page .payment-method-option:has(input:checked) {
    border-color: #f4bd95;
    background: var(--pos-orange-soft);
    color: #a64408;
    box-shadow: 0 1px 3px rgba(23, 32, 51, .04);
}

.pos-sales-page .payment-method-option.focus,
.pos-sales-page .payment-method-option:focus-within {
    outline: 2px solid var(--pos-orange);
    outline-offset: 2px;
    box-shadow: none;
}

.payment-input {
    height:
        38px !important;

    padding:
        7px 10px !important;

    border:
        1px solid #eadfd5 !important;

    border-radius:
        7px !important;

    background:
        #ffffff !important;

    color:
        #27313e !important;

    font-size:
        13px !important;

    font-weight:
        700 !important;
}


.payment-input:focus {
    border-color:
        var(--pos-orange) !important;

    box-shadow:
        0 0 0 3px
        rgba(255,122,24,.08) !important;
}


/* ============================================================
   QUICK PAYMENT
   ============================================================ */

.quick-payment {
    display:
        flex;

    flex-wrap:
        wrap;

    gap:
        5px;

    margin-top:
        6px;
}


.quick-pay {
    padding:
        4px 7px !important;

    border:
        1px solid #f2c6a3 !important;

    border-radius:
        5px !important;

    background:
        #ffffff !important;

    color:
        var(--pos-orange-dark) !important;

    font-size:
        8px !important;

    font-weight:
        600 !important;
}


.quick-pay:hover {
    background:
        var(--pos-orange-soft) !important;

    border-color:
        var(--pos-orange) !important;
}


/* ============================================================
   CHANGE
   ============================================================ */

.change-value {
    color:
        var(--pos-orange-dark);

    font-size:
        13px;

    font-weight:
        800;
}


.change-value.text-danger {
    color:
        var(--pos-red) !important;
}


.change-value.text-success {
    color:
        var(--pos-green) !important;
}


/* ============================================================
   PAYMENT STATUS
   ============================================================ */

#paymentStatus .alert {
    margin:
        8px 0 0 !important;

    padding:
        7px 9px !important;

    border:
        none !important;

    border-radius:
        6px !important;

    font-size:
        8px !important;
}


#paymentStatus .alert-warning {
    background:
        #fff6e8 !important;

    color:
        #bd7b20 !important;
}


#paymentStatus .alert-danger {
    background:
        var(--pos-red-soft) !important;

    color:
        #c84d3d !important;
}


#paymentStatus .alert-success {
    background:
        var(--pos-green-soft) !important;

    color:
        #168f59 !important;
}


/* ============================================================
   SAVE BUTTON
   ============================================================ */

#btnSimpan {
    height:
        36px;

    border:
        none !important;

    border-radius:
        7px !important;

    background:
        linear-gradient(
            135deg,
            #ff8b32,
            #ff7414
        ) !important;

    color:
        #ffffff !important;

    font-size:
        10px;

    font-weight:
        700;

    box-shadow:
        0 5px 12px
        rgba(255,122,24,.16);
}


#btnSimpan:hover:not(:disabled) {
    background:
        linear-gradient(
            135deg,
            #ff7414,
            #f3680a
        ) !important;
}


#btnSimpan:disabled {
    opacity:
        .45;

    cursor:
        not-allowed;
}


/* ============================================================
   CANCEL
   ============================================================ */

.cancel-sale-btn {
    height:
        34px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border:
        1px solid #e9dfd6 !important;

    border-radius:
        7px !important;

    background:
        #ffffff !important;

    color:
        #858585 !important;

    font-size:
        9px;

    font-weight:
        600;

    text-decoration:
        none;
}


.cancel-sale-btn:hover {
    background:
        #fff6f0 !important;

    color:
        var(--pos-orange-dark) !important;

    text-decoration:
        none;
}


/* ============================================================
   NOTES
   ============================================================ */

.notes-card {
    margin-top:
        10px;

    padding:
        11px 15px;

    border:
        1px solid var(--pos-border);

    border-radius:
        10px;

    background:
        #ffffff;

    box-shadow:
        0 3px 12px rgba(30,20,10,.025);
}


.notes-card label {
    margin-bottom:
        5px;

    color:
        #59616d;

    font-size:
        10px;

    font-weight:
        700;
}


.notes-card label i {
    color:
        var(--pos-orange);

    margin-right:
        4px;
}


.notes-card textarea {
    min-height:
        52px;

    resize:
        vertical;

    border:
        1px solid #e8ded5 !important;

    border-radius:
        7px !important;

    font-size:
        10px !important;
}


/* ============================================================
   ALERT
   ============================================================ */

.pos-sales-page > .alert {
    border:
        none;

    border-radius:
        8px;

    font-size:
        10px;
}


/* ============================================================
   EMPTY PRODUCT
   ============================================================ */

.empty-products {
    grid-column:
        1 / -1;

    padding:
        50px 20px;

    text-align:
        center;

    color:
        #aaa;
}


.empty-products i {
    color:
        #dfd5cc;

    font-size:
        40px;
}


.empty-products h5 {
    margin-top:
        10px;

    color:
        #8e8e8e;

    font-size:
        13px;
}


.empty-products p {
    font-size:
        9px;
}


/* ============================================================
   NO SEARCH RESULT
   ============================================================ */

#noProductResult {
    padding:
        35px 15px !important;

    color:
        #aaa;
}


#noProductResult i {
    color:
        #dfd5cc !important;

    font-size:
        32px !important;
}


#noProductResult h5 {
    margin:
        8px 0 3px;

    color:
        #8c8c8c;

    font-size:
        12px;
}


#noProductResult p {
    font-size:
        9px;
}


/* ============================================================
   RESPONSIVE
   ============================================================ */

@media (max-width: 1200px) {

    .product-grid {
        grid-template-columns:
            repeat(4, minmax(0, 1fr));
    }

}


@media (max-width: 992px) {

    .pos-main-grid {
        grid-template-columns:
            1fr;
    }


    .cart-panel {
        position:
            relative;

        top:
            0;
    }


    .product-grid {
        max-height:
            none;

        grid-template-columns:
            repeat(4, minmax(0, 1fr));
    }

}


@media (max-width: 768px) {

    .sales-page-header {
        min-height:
            auto;

        padding:
            12px;
    }


    .sales-header-title {
        font-size:
            17px;
    }


    .sales-header-subtitle {
        font-size:
            9px;
    }


    .sales-header-icon {
        width:
            42px;

        height:
            42px;
    }


    .sales-history-btn {
        padding:
            8px 9px;

        font-size:
            9px;
    }


    .sale-info-grid {
        grid-template-columns:
            1fr;
    }


    .product-header-row {
        flex-direction:
            column;

        align-items:
            stretch;
    }


    .product-search {
        width:
            100%;
    }


    .product-grid {
        grid-template-columns:
            repeat(3, minmax(0, 1fr));
    }

}


@media (max-width: 576px) {

    .sales-page-header {
        align-items:
            flex-start;

        gap:
            10px;
    }


    .sales-history-btn span {
        display:
            none;
    }


    .product-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap:
            8px;

        padding:
            9px;
    }


    .product-image-wrapper {
        height:
            105px;
    }


    .cart-panel {
        border-radius:
            9px;
    }

}
</style>


<div class="pos-sales-page">

    {{-- =========================================================
         ALERT
    ========================================================== --}}

    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show">

            <i class="fas fa-exclamation-circle mr-1"></i>

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


    @if($errors->any())

        <div class="alert alert-danger">

            <strong>
                <i class="fas fa-exclamation-triangle mr-1"></i>
                Terdapat kesalahan:
            </strong>

            <ul class="mb-0 mt-1 pl-3">

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- =========================================================
         HEADER KASIR
    ========================================================== --}}

    <div class="sales-page-header">

        <div class="sales-header-left">

            <div class="sales-header-icon">

                <i class="fas fa-cash-register"></i>

            </div>

            <div>

                <h1 class="sales-header-title">
                    Kasir
                </h1>

                <div class="sales-header-subtitle">
                    Buat transaksi penjualan baru
                </div>

            </div>

        </div>


        <a
            href="{{ route('sales.index') }}"
            class="sales-history-btn"
        >

            <i class="fas fa-receipt"></i>

            <span>
                Riwayat Penjualan
            </span>

        </a>

    </div>


    {{-- =========================================================
         FORM PENJUALAN
    ========================================================== --}}

    <form
        action="{{ route('sales.store') }}"
        method="POST"
        id="formPenjualan"
    >

        @csrf


        {{-- =====================================================
             INFO TRANSAKSI
        ====================================================== --}}

        <div class="sale-info-card">

            <div class="sale-info-grid">

                <div class="sale-info-field">
                    <label for="customer_name"><i class="fas fa-user"></i> Nama Pelanggan <span class="text-danger">*</span></label>
                    <input type="text" name="customer_name" id="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name') }}" maxlength="100" placeholder="Masukkan nama pelanggan" required autofocus>
                    @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="sale-info-field">
                    <label><i class="fas fa-cash-register"></i> Kasir</label>
                    <input type="text" class="form-control" value="{{ auth()->user()->name ?: (auth()->user()->username ?: auth()->user()->email) }}" readonly>
                </div>


                {{-- KODE --}}

                <div class="sale-info-field">

                    <label>

                        <i class="fas fa-barcode"></i>

                        Kode Penjualan

                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="{{ $code }}"
                        readonly
                    >

                </div>


                {{-- TANGGAL --}}

                <div class="sale-info-field">

                    <label for="sale_date">

                        <i class="fas fa-calendar"></i>

                        Tanggal Penjualan

                        <span class="text-danger">*</span>

                    </label>

                    <input
                        type="date"
                        name="sale_date"
                        id="sale_date"
                        class="form-control @error('sale_date') is-invalid @enderror"
                        value="{{ old('sale_date', date('Y-m-d')) }}"
                        required
                    >

                    @error('sale_date')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>

            </div>

        </div>


        {{-- =====================================================
             MAIN POS
        ====================================================== --}}

        <div class="pos-main-grid">


            {{-- =================================================
                 PRODUK
            ================================================== --}}

            <div class="product-panel">


                {{-- HEADER PRODUK --}}

                <div class="product-panel-header">

                    <div class="product-header-row">


                        <div class="product-heading">

                            <div class="product-heading-icon">

                                <i class="fas fa-boxes"></i>

                            </div>

                            <div>

                                <h5>
                                    Daftar Produk
                                </h5>

                                <small>
                                    Klik produk untuk menambahkannya ke keranjang.
                                </small>

                            </div>

                        </div>


                        {{-- SEARCH --}}

                        <div class="product-search">

                            <i class="fas fa-search"></i>

                            <input
                                type="text"
                                id="searchProduct"
                                placeholder="Cari nama produk..."
                                autocomplete="off"
                            >

                        </div>


                    </div>


                    {{-- =================================================
                         KATEGORI
                    ================================================== --}}

                    <div class="category-container">


                        <button
                            type="button"
                            class="category-btn active"
                            data-category="all"
                        >
                            Semua
                        </button>


                        @php

                            $categories = $products
                                ->filter(function ($product) {
                                    return $product->category;
                                })
                                ->map(function ($product) {
                                    return [
                                        'id' => $product->category->id,
                                        'name' => $product->category->name
                                    ];
                                })
                                ->unique('id')
                                ->values();

                        @endphp


                        @foreach($categories as $category)

                            <button
                                type="button"
                                class="category-btn"
                                data-category="{{ $category['id'] }}"
                            >

                                {{ $category['name'] }}

                            </button>

                        @endforeach


                    </div>

                </div>


                {{-- =================================================
                     GRID PRODUK
                ================================================== --}}

                <div
                    class="product-grid"
                    id="productGrid"
                >


                    @forelse($products as $product)

                        @php

                            $stock = (int) $product->stock;

                            $price = (float) $product->price;

                            $categoryId =
                                optional($product->category)->id;

                            $imageUrl =
                                $product->image_url;

                        @endphp


                        <div
                            class="product-card {{ $stock <= 0 ? 'out-of-stock' : '' }}"

                            data-id="{{ $product->id }}"

                            data-name="{{ strtolower($product->name) }}"

                            data-price="{{ $price }}"

                            data-stock="{{ $stock }}"

                            data-category="{{ $categoryId }}"
                        >


                            {{-- IMAGE --}}

                            <div class="product-image-wrapper">

                                @if($product->image)

                                    <img
                                        src="{{ $imageUrl }}"
                                        alt="{{ $product->name }}"
                                        class="product-image"
                                        loading="lazy"
                                    >

                                @else

                                    <i class="fas fa-box product-image-placeholder"></i>

                                @endif

                            </div>


                            {{-- INFO --}}

                            <div class="product-info">


                                <div class="product-name">

                                    {{ $product->name }}

                                </div>


                                <div class="product-price">

                                    Rp {{ number_format($price, 0, ',', '.') }}

                                </div>


                                <div
                                    class="
                                        product-stock
                                        @if($stock <= 0)
                                            stock-empty
                                        @elseif($stock <= 5)
                                            stock-low
                                        @else
                                            stock-available
                                        @endif
                                    "
                                >

                                    @if($stock <= 0)

                                        <i class="fas fa-times-circle mr-1"></i>

                                        Stok Habis

                                    @elseif($stock <= 5)

                                        <i class="fas fa-exclamation-circle mr-1"></i>

                                        Stok {{ $stock }}

                                    @else

                                        <i class="fas fa-check-circle mr-1"></i>

                                        Stok {{ $stock }}

                                    @endif

                                </div>


                            </div>


                            {{-- PLUS --}}

                            @if($stock > 0)

                                <div class="product-add-icon">

                                    <i class="fas fa-plus"></i>

                                </div>

                            @endif


                        </div>


                    @empty

                        <div class="empty-products">

                            <i class="fas fa-box-open"></i>

                            <h5>
                                Belum ada produk
                            </h5>

                            <p class="mb-0">
                                Tambahkan produk terlebih dahulu.
                            </p>

                        </div>

                    @endforelse


                </div>


                {{-- NO RESULT --}}

                <div
                    id="noProductResult"
                    style="display:none;"
                    class="text-center"
                >

                    <i class="fas fa-search"></i>

                    <h5>
                        Produk tidak ditemukan
                    </h5>

                    <p class="mb-0">
                        Coba gunakan kata pencarian lain.
                    </p>

                </div>


            </div>


            {{-- =================================================
                 CART
            ================================================== --}}

            <div class="cart-panel">


                {{-- CART HEADER --}}

                <div class="cart-header">

                    <div class="d-flex justify-content-between align-items-center">


                        <div>

                            <div class="cart-title">

                                <i class="fas fa-shopping-cart cart-title-icon"></i>

                                <h5>
                                    Keranjang
                                </h5>

                            </div>

                            <div
                                class="cart-item-info"
                                id="cartItemInfo"
                            >
                                0 item
                            </div>

                        </div>


                        <button
                            type="button"
                            class="clear-cart-btn"
                            id="btnClearCart"
                            style="display:none;"
                        >

                            <i class="fas fa-trash-alt"></i>

                        </button>


                    </div>

                </div>


                {{-- =================================================
                     CART ITEMS
                ================================================== --}}

                <div
                    class="cart-items"
                    id="cartItems"
                >

                    <div
                        class="empty-cart"
                        id="emptyCart"
                    >

                        <i class="fas fa-shopping-basket"></i>

                        <h6>
                            Keranjang masih kosong
                        </h6>

                        <small>
                            Klik produk untuk mulai transaksi.
                        </small>

                    </div>

                </div>


                {{-- =================================================
                     SUMMARY
                ================================================== --}}

                <div class="cart-summary">


                    <div class="summary-row">

                        <span class="summary-label">
                            Jumlah Item
                        </span>

                        <strong id="summaryQuantity">
                            0
                        </strong>

                    </div>


                    <div class="summary-row">

                        <span class="summary-total-label">
                            Total
                        </span>

                        <span
                            class="summary-total"
                            id="grandTotal"
                        >
                            Rp 0
                        </span>

                    </div>


                </div>


                {{-- =================================================
                     PAYMENT
                ================================================== --}}

                <div class="payment-section">

                    <div class="form-group mb-3">
                        <label class="payment-label"><i class="fas fa-credit-card"></i> Jenis Pembayaran <span class="text-danger">*</span></label>
                        <div class="btn-group btn-group-toggle d-flex payment-method-options" data-toggle="buttons" role="group" aria-label="Jenis Pembayaran">
                            @foreach(['cash' => 'Cash', 'qris' => 'QRIS', 'va' => 'VA'] as $value => $label)
                                <label class="btn payment-method-option {{ old('payment_method', 'cash') === $value ? 'active' : '' }}">
                                    <input type="radio" name="payment_method" value="{{ $value }}" {{ old('payment_method', 'cash') === $value ? 'checked' : '' }}> {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        @error('payment_method')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>


                    <div class="form-group mb-2">

                        <label
                            for="paid"
                            class="payment-label"
                        >

                            <i class="fas fa-money-bill-wave"></i>

                            Uang Bayar

                            <span class="text-danger cash-required">*</span>

                        </label>


                        <input
                            type="number"
                            name="paid"
                            id="paid"
                            class="form-control payment-input @error('paid') is-invalid @enderror"
                            min="0"
                            step="1"
                            value="{{ old('paid', 0) }}"
                            placeholder="0"
                        >


                        @error('paid')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror


                        {{-- QUICK PAYMENT --}}

                        <div
                            class="quick-payment"
                            id="quickPayment"
                        >

                            <button
                                type="button"
                                class="quick-pay"
                                data-value="exact"
                            >
                                Uang Pas
                            </button>

                            <button
                                type="button"
                                class="quick-pay"
                                data-value="50000"
                            >
                                50K
                            </button>

                            <button
                                type="button"
                                class="quick-pay"
                                data-value="100000"
                            >
                                100K
                            </button>

                            <button
                                type="button"
                                class="quick-pay"
                                data-value="200000"
                            >
                                200K
                            </button>

                            <button
                                type="button"
                                class="quick-pay"
                                data-value="500000"
                            >
                                500K
                            </button>

                        </div>

                    </div>


                    {{-- KEMBALIAN --}}

                    <div class="summary-row mb-0">

                        <span class="summary-total-label">
                            Kembalian
                        </span>

                        <span
                            id="changeDisplay"
                            class="change-value"
                        >
                            Rp 0
                        </span>

                    </div>


                    {{-- STATUS --}}

                    <div
                        id="paymentStatus"
                        class="mt-2"
                    ></div>


                    {{-- SIMPAN --}}

                    <button
                        type="submit"
                        class="btn btn-primary btn-block mt-2"
                        id="btnSimpan"
                        disabled
                    >

                        <i class="fas fa-check-circle mr-1"></i>

                        Bayar & Simpan

                    </button>


                    {{-- BATAL --}}

                    <a
                        href="{{ route('sales.index') }}"
                        class="cancel-sale-btn mt-2"
                    >

                        <i class="fas fa-times mr-1"></i>

                        Batal

                    </a>


                </div>


            </div>


        </div>


        {{-- =====================================================
             CATATAN
        ====================================================== --}}

        <div class="notes-card">

            <label for="notes">

                <i class="fas fa-sticky-note"></i>

                Catatan Transaksi

            </label>

            <textarea
                name="notes"
                id="notes"
                rows="2"
                class="form-control"
                placeholder="Tambahkan catatan jika diperlukan..."
            >{{ old('notes') }}</textarea>

        </div>


        {{-- =====================================================
             HIDDEN CART INPUTS
        ====================================================== --}}

        <div id="hiddenCartInputs"></div>


        @if(app(\App\Services\PackageAccess::class)->allows('customer_loyalty'))
        <div class="card card-body mt-3"><label for="loyalty_customer_id">Akun customer loyalty (wajib untuk redeem)</label><select id="loyalty_customer_id" name="loyalty_customer_id" class="form-control" onchange="if(this.selectedOptions[0].dataset.name) document.getElementById('customer_name').value=this.selectedOptions[0].dataset.name"><option value="">Gunakan nama customer pada form</option>@foreach(\App\Models\Customer::orderBy('name')->get(['id','name','phone']) as $loyaltyCustomer)<option value="{{ $loyaltyCustomer->id }}" data-name="{{ $loyaltyCustomer->name }}" @selected(old('loyalty_customer_id')==$loyaltyCustomer->id)>{{ $loyaltyCustomer->name }} · {{ $loyaltyCustomer->phone ?? 'Tanpa telepon' }} · #{{ $loyaltyCustomer->id }}</option>@endforeach</select></div>
        <div class="card card-body mt-3"><label for="redeem_points">Redeem poin customer (tunai)</label><input class="form-control" id="redeem_points" name="redeem_points" type="number" min="0" value="{{ old('redeem_points',0) }}"><small>Saldo mengikuti akun customer yang dipilih. Diskon dan kembalian dihitung ulang oleh server. <a href="{{ route('loyalty.index') }}">Lihat saldo / pengaturan loyalty</a></small></div>
        @endif
        @if(app(\App\Services\PackageAccess::class)->allows('promotions'))
        <div class="card card-body mt-3" id="premium_pricing"><label for="voucher_code">Kode voucher (opsional)</label><input class="form-control" id="voucher_code" name="voucher_code" maxlength="50" value="{{ old('voucher_code') }}"><small>Kosongkan untuk promo otomatis terbaik. Total akhir dan kembalian dihitung server setelah validasi promo.</small></div>
        @endif
    </form>

</div>


@endsection


@section('js')

<script>

/* ============================================================
   DATA PRODUK
   ============================================================ */

const products = @json($products);


/* ============================================================
   CART STATE
   ============================================================ */

let cart = {};
const premiumPricingEnabled = @json(app(\App\Services\PackageAccess::class)->allows('customer_loyalty') || app(\App\Services\PackageAccess::class)->allows('promotions'));
let premiumQuote = null, quoteSequence = 0, quoteTimer = null;
function refreshPremiumQuote() {
    if (!premiumPricingEnabled) return;
    premiumQuote = null;
    const sequence = ++quoteSequence;
    clearTimeout(quoteTimer);
    updatePayment();
    if (!Object.keys(cart).length) return;
    quoteTimer = setTimeout(async function() {
        try {
            const response = await fetch(@json(route('premium.checkout.quote')), {method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('#formPenjualan [name="_token"]').value},body:JSON.stringify({products:Object.values(cart).map(item=>({product_id:item.id,quantity:item.quantity})),customer_name:$('#customer_name').val(),loyalty_customer_id:$('#loyalty_customer_id').val() || null,redeem_points:$('#redeem_points').val() || 0,voucher_code:$('#voucher_code').val() || null,payment_method:$('input[name="payment_method"]:checked').val()})});
            const result = await response.json();
            if (sequence!==quoteSequence) return;
            if (!response.ok) throw new Error(Object.values(result.errors || {}).flat()[0] || 'Perhitungan diskon tidak tersedia.');
            premiumQuote=result;
            $('#grandTotal').text(formatRupiah(result.total));
            $('#premiumQuoteDetails').remove();
            $('<p id="premiumQuoteDetails" class="small text-muted"></p>').text('Promo: '+formatRupiah(result.promotion_discount)+' ? Redeem: '+formatRupiah(result.loyalty_discount)+'. Harga divalidasi ulang saat disimpan.').insertAfter('#grandTotal');
            updatePayment();
        } catch(error) {
            if(sequence!==quoteSequence) return;
            $('#paymentStatus').empty().append($('<div class="alert alert-warning mb-0"></div>').text(error.message));
            $('#btnSimpan').prop('disabled',true);
        }
    },300);
}
$(document).on('input change','#voucher_code,#redeem_points,#loyalty_customer_id,#customer_name,input[name="payment_method"]',refreshPremiumQuote);


let selectedCategory = 'all';


/* ============================================================
   FORMAT RUPIAH
   ============================================================ */

function formatRupiah(value)
{
    value = Number(value) || 0;

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(value);
}


/* ============================================================
   ESCAPE HTML
   ============================================================ */

function escapeHtml(text)
{
    return $('<div>')
        .text(text ?? '')
        .html();
}


/* ============================================================
   GET PRODUCT
   ============================================================ */

function getProduct(productId)
{
    return products.find(function(product) {

        return String(product.id) === String(productId);

    });
}


/* ============================================================
   ADD TO CART
   ============================================================ */

function addToCart(productId)
{
    const product = getProduct(productId);

    if (!product) {
        return;
    }


    const stock =
        parseInt(product.stock) || 0;


    if (stock <= 0) {

        alert(
            'Stok produk tersebut habis.'
        );

        return;
    }


    if (cart[productId]) {

        if (
            cart[productId].quantity >= stock
        ) {

            alert(
                'Jumlah tidak dapat melebihi stok tersedia.'
            );

            return;
        }


        cart[productId].quantity++;

    }

    else {

        cart[productId] = {

            id:
                product.id,

            name:
                product.name,

            price:
                parseFloat(product.price) || 0,

            stock:
                stock,

            quantity:
                1

        };

    }


    renderCart();

    showProductAdded(productId);
}


/* ============================================================
   PRODUCT ADDED EFFECT
   ============================================================ */

function showProductAdded(productId)
{
    const card =
        $('.product-card[data-id="' + productId + '"]');


    card.css(
        'transform',
        'scale(.96)'
    );


    setTimeout(function() {

        card.css(
            'transform',
            ''
        );

    }, 120);
}


/* ============================================================
   CHANGE QUANTITY
   ============================================================ */

function changeQuantity(productId, amount)
{
    if (!cart[productId]) {
        return;
    }


    const newQuantity =
        cart[productId].quantity + amount;


    if (newQuantity <= 0) {

        delete cart[productId];

        renderCart();

        return;
    }


    if (
        newQuantity > cart[productId].stock
    ) {

        alert(
            'Jumlah melebihi stok tersedia.'
        );

        return;
    }


    cart[productId].quantity =
        newQuantity;


    renderCart();
}


/* ============================================================
   REMOVE
   ============================================================ */

function removeFromCart(productId)
{
    if (!cart[productId]) {
        return;
    }


    delete cart[productId];

    renderCart();
}


/* ============================================================
   CLEAR CART
   ============================================================ */

function clearCart()
{
    if (
        Object.keys(cart).length === 0
    ) {
        return;
    }


    if (
        !confirm(
            'Yakin ingin mengosongkan keranjang?'
        )
    ) {
        return;
    }


    cart = {};

    renderCart();
}


/* ============================================================
   CALCULATE TOTAL
   ============================================================ */

function calculateTotal()
{
    let total = 0;

    let quantity = 0;


    Object.values(cart).forEach(function(item) {

        total +=
            item.price *
            item.quantity;


        quantity +=
            item.quantity;

    });


    return {

        total:
            total,

        quantity:
            quantity

    };
}


/* ============================================================
   RENDER CART
   ============================================================ */

function renderCart()
{
    const container =
        $('#cartItems');


    container.empty();


    const cartKeys =
        Object.keys(cart);


    if (
        cartKeys.length === 0
    ) {

        container.html(`

            <div class="empty-cart">

                <i class="fas fa-shopping-basket"></i>

                <h6>
                    Keranjang masih kosong
                </h6>

                <small>
                    Klik produk untuk mulai transaksi.
                </small>

            </div>

        `);


        $('#btnClearCart')
            .hide();


        $('#cartItemInfo')
            .text('0 item');


        updateSummary();

        return;
    }


    $('#btnClearCart')
        .show();


    cartKeys.forEach(function(productId) {

        const item =
            cart[productId];


        const subtotal =
            item.price *
            item.quantity;


        const html = `

            <div
                class="cart-item"
                data-cart-id="${item.id}"
            >

                <div class="d-flex justify-content-between">

                    <div class="cart-item-name">

                        ${escapeHtml(item.name)}

                    </div>


                    <button
                        type="button"
                        class="remove-item"
                        data-id="${item.id}"
                        title="Hapus"
                    >

                        <i class="fas fa-trash-alt"></i>

                    </button>

                </div>


                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <div class="cart-item-price">

                            ${formatRupiah(item.price)}

                        </div>


                        <div class="quantity-control">

                            <button
                                type="button"
                                class="quantity-btn btn-minus"
                                data-id="${item.id}"
                            >

                                <i class="fas fa-minus"></i>

                            </button>


                            <div class="quantity-value">

                                ${item.quantity}

                            </div>


                            <button
                                type="button"
                                class="quantity-btn btn-plus"
                                data-id="${item.id}"
                            >

                                <i class="fas fa-plus"></i>

                            </button>

                        </div>

                    </div>


                    <div class="cart-item-subtotal">

                        ${formatRupiah(subtotal)}

                    </div>

                </div>


                <small>

                    Stok tersedia: ${item.stock}

                </small>

            </div>

        `;


        container.append(html);

    });


    updateSummary();
}


/* ============================================================
   UPDATE SUMMARY
   ============================================================ */

function updateSummary()
{
    const summary =
        calculateTotal();


    $('#summaryQuantity')
        .text(summary.quantity);


    $('#cartItemInfo')
        .text(
            summary.quantity +
            ' item'
        );


    $('#grandTotal')
        .text(
            formatRupiah(summary.total)
        );


    if (premiumPricingEnabled) refreshPremiumQuote(); else updatePayment();
}


/* ============================================================
   UPDATE PAYMENT
   ============================================================ */

function updatePayment()
{
    const summary =
        calculateTotal();


    const total =
        premiumQuote ? premiumQuote.total : summary.total;


    const paid =
        parseFloat(
            $('#paid').val()
        ) || 0;


    const change =
        paid - total;


    /* EMPTY */

    if (
        summary.quantity <= 0
    ) {

        $('#changeDisplay')
            .removeClass(
                'text-success text-danger'
            )
            .text('Rp 0');


        $('#paymentStatus')
            .html(`

                <div class="alert alert-warning mb-0">

                    <i class="fas fa-shopping-cart mr-1"></i>

                    Tambahkan produk terlebih dahulu.

                </div>

            `);


        $('#btnSimpan')
            .prop(
                'disabled',
                true
            );


        return;
    }


    if (premiumPricingEnabled && !premiumQuote) {
        $('#changeDisplay').text('Menunggu perhitungan');
        $('#paymentStatus').text('Menghitung harga dan benefit dari server...');
        $('#btnSimpan').prop('disabled',true);
        return;
    }
    if (premiumPricingEnabled && $('input[name="payment_method"]:checked').val() !== 'cash') {
        $('#changeDisplay').text('Dibayar melalui gateway');
        $('#paymentStatus').text('Total pembayaran telah dihitung server.');
        $('#btnSimpan').prop('disabled',false);
        return;
    }
    /* PAYMENT LESS */

    if (
        paid < total
    ) {

        const kurang =
            total - paid;


        $('#changeDisplay')
            .removeClass(
                'text-success'
            )
            .addClass(
                'text-danger'
            )
            .text(
                '- ' +
                formatRupiah(kurang)
            );


        $('#paymentStatus')
            .html(`

                <div class="alert alert-danger mb-0">

                    <i class="fas fa-exclamation-circle mr-1"></i>

                    Pembayaran kurang

                    <strong>
                        ${formatRupiah(kurang)}
                    </strong>

                </div>

            `);


        $('#btnSimpan')
            .prop(
                'disabled',
                true
            );


        return;
    }


    /* PAYMENT ENOUGH */

    $('#changeDisplay')
        .removeClass(
            'text-danger text-muted'
        )
        .addClass(
            'text-success'
        )
        .text(
            formatRupiah(change)
        );


    $('#paymentStatus')
        .html(`

            <div class="alert alert-success mb-0">

                <i class="fas fa-check-circle mr-1"></i>

                Pembayaran cukup.

            </div>

        `);


    $('#btnSimpan')
        .prop(
            'disabled',
            false
        );
}


/* ============================================================
   CLICK PRODUCT
   ============================================================ */

$(document).on(
    'click',
    '.product-card',
    function()
    {
        const card =
            $(this);


        if (
            card.hasClass('out-of-stock')
        ) {
            return;
        }


        const productId =
            card.data('id');


        addToCart(productId);
    }
);


/* ============================================================
   PLUS
   ============================================================ */

$(document).on(
    'click',
    '.btn-plus',
    function()
    {
        const productId =
            $(this).data('id');


        changeQuantity(
            productId,
            1
        );
    }
);


/* ============================================================
   MINUS
   ============================================================ */

$(document).on(
    'click',
    '.btn-minus',
    function()
    {
        const productId =
            $(this).data('id');


        changeQuantity(
            productId,
            -1
        );
    }
);


/* ============================================================
   REMOVE
   ============================================================ */

$(document).on(
    'click',
    '.remove-item',
    function()
    {
        const productId =
            $(this).data('id');


        removeFromCart(productId);
    }
);


/* ============================================================
   CLEAR
   ============================================================ */

$(document).on(
    'click',
    '#btnClearCart',
    function()
    {
        clearCart();
    }
);


/* ============================================================
   SEARCH
   ============================================================ */

$('#searchProduct').on(
    'input',
    function()
    {
        filterProducts();
    }
);


/* ============================================================
   FILTER PRODUCTS
   ============================================================ */

function filterProducts()
{
    const search =
        (
            $('#searchProduct').val()
            || ''
        )
        .toLowerCase()
        .trim();


    let visibleCount = 0;


    $('.product-card').each(function() {

        const card =
            $(this);


        const name =
            String(
                card.data('name')
                || ''
            )
            .toLowerCase();


        const category =
            String(
                card.data('category')
                || ''
            );


        const matchSearch =
            name.includes(search);


        const matchCategory =
            selectedCategory === 'all'
            ||
            category ===
                String(selectedCategory);


        if (
            matchSearch &&
            matchCategory
        ) {

            card.show();

            visibleCount++;

        }

        else {

            card.hide();

        }

    });


    if (
        visibleCount === 0
    ) {

        $('#noProductResult')
            .show();

    }

    else {

        $('#noProductResult')
            .hide();

    }
}


/* ============================================================
   CATEGORY
   ============================================================ */

$(document).on(
    'click',
    '.category-btn',
    function()
    {

        $('.category-btn')
            .removeClass(
                'active'
            );


        $(this)
            .addClass(
                'active'
            );


        selectedCategory =
            $(this).data(
                'category'
            );


        filterProducts();

    }
);


/* ============================================================
   PAYMENT INPUT
   ============================================================ */

$(document).on(
    'input',
    '#paid',
    function()
    {
        updatePayment();
    }
);


/* ============================================================
   QUICK PAYMENT
   ============================================================ */

$(document).on(
    'click',
    '.quick-pay',
    function()
    {

        const value =
            $(this).data(
                'value'
            );


        const summary =
            calculateTotal();


        if (
            value === 'exact'
        ) {

            $('#paid')
                .val(
                    premiumQuote ? premiumQuote.total : summary.total
                );

        }

        else {

            $('#paid')
                .val(value);

        }


        updatePayment();

    }
);


/* ============================================================
   GENERATE HIDDEN INPUTS
   ============================================================ */

function generateHiddenInputs()
{
    const container =
        $('#hiddenCartInputs');


    container.empty();


    let index = 0;


    Object.values(cart)
        .forEach(function(item) {

            container.append(`

                <input
                    type="hidden"
                    name="products[${index}][product_id]"
                    value="${item.id}"
                >

                <input
                    type="hidden"
                    name="products[${index}][price]"
                    value="${item.price}"
                >

                <input
                    type="hidden"
                    name="products[${index}][quantity]"
                    value="${item.quantity}"
                >

            `);


            index++;

        });
}


/* ============================================================
   SUBMIT
   ============================================================ */

$('#formPenjualan').on(
    'submit',
    function(event)
    {

        const summary =
            calculateTotal();


        let valid =
            true;


        /* PRODUCT */

        if (
            summary.quantity <= 0
        ) {

            valid =
                false;


            alert(
                'Minimal pilih satu produk.'
            );

        }


        /* PAYMENT */

        const paid =
            parseFloat(
                $('#paid').val()
            ) || 0;


        if ($('input[name="payment_method"]:checked').val() === 'cash' && paid < summary.total && !(parseInt($('#redeem_points').val(),10)>0) && !$('#premium_pricing').length) {

            valid =
                false;


            alert(
                'Uang pembayaran masih kurang.'
            );

        }


        /* INPUT */

        if (valid) {

            generateHiddenInputs();


            $('#btnSimpan')
                .prop(
                    'disabled',
                    true
                )
                .html(`

                    <span
                        class="spinner-border spinner-border-sm mr-1"
                    ></span>

                    Menyimpan...

                `);

        }


        if (!valid) {

            event.preventDefault();

            return false;

        }

    }
);


/* ============================================================
   DOCUMENT READY
   ============================================================ */

$(document).ready(function()
{

    function syncPaymentMethod() {
        const isCash = $('input[name="payment_method"]:checked').val() === 'cash';
        $('#paid').prop('required', isCash).prop('disabled', !isCash);
        $('#quickPayment').toggle(isCash);
        $('.cash-required').toggle(isCash);
        if (!isCash) $('#paid').val(0);
    }
    $('input[name="payment_method"]').on('change', syncPaymentMethod);
    syncPaymentMethod();

    filterProducts();

    updatePayment();

    $('#searchProduct')
        .trigger('focus');

});

</script>

@endsection
