@extends('layouts.app')
@section('title','Customer Portal')
@section('content')<div class="container-fluid"><h1>Customer Portal</h1><p>Ringkasan penggunaan platform tanpa akses isi percakapan pribadi.</p><div class="row">@foreach(['Portal aktif'=>$stores,'Akun customer'=>$accounts,'Relasi pelanggan toko'=>$memberships,'Percakapan'=>$conversations]+($reservationStats??[]) as $label=>$number)<div class="col-md-3"><div class="card p-4"><h2>{{ $number }}</h2><p>{{ $label }}</p></div></div>@endforeach</div></div>@endsection
