@extends('layouts.app')
@section('title', 'Chat Customer')
@section('content')
<link rel="stylesheet" href="{{ asset('css/staff-chat.css') }}">
<div class="staff-chat-page">
    <header class="sc-page-header">
        <div class="sc-page-icon" aria-hidden="true"><i class="fas fa-comments"></i></div>
        <div><h1>Chat Customer</h1><p>Kelola percakapan pelanggan toko Anda.</p></div>
    </header>
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @include('portal.staff-chat-content')
</div>
@endsection
