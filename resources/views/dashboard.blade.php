@extends('layouts.app')

@section('title', 'Dashboard')

@section('content_header')
    <h1>POS Enterprise Dashboard</h1>
@stop

@section('content')
<div class="row">

    <div class="col-lg-3 col-6">
        <x-adminlte-small-box
            title="0"
            text="Produk"
            icon="fas fa-box"
            theme="info"/>
    </div>

    <div class="col-lg-3 col-6">
        <x-adminlte-small-box
            title="0"
            text="Kategori"
            icon="fas fa-tags"
            theme="success"/>
    </div>

    <div class="col-lg-3 col-6">
        <x-adminlte-small-box
            title="0"
            text="Supplier"
            icon="fas fa-truck"
            theme="warning"/>
    </div>

    <div class="col-lg-3 col-6">
        <x-adminlte-small-box
            title="0"
            text="Penjualan"
            icon="fas fa-cash-register"
            theme="danger"/>
    </div>

</div>
@stop
