@extends('layouts.guest')
@section('title', 'Ketentuan Pendaftaran & Privasi')
@section('content')
<h2>Ketentuan Pendaftaran & Privasi</h2>
<p class="muted">Informasi awal pendaftaran. Dokumen kebijakan lengkap platform belum diterbitkan; hubungi pengelola jika memerlukan penjelasan sebelum mengajukan toko.</p>
<ul style="padding-left:20px;line-height:1.8"><li>Gunakan informasi toko dan kontak pemilik yang benar serta email yang Anda kuasai.</li><li>Pengajuan diperiksa oleh Super Admin. Pendaftaran tidak menjamin persetujuan atau akses Premium.</li><li>Data yang dikirim digunakan untuk review, pembuatan akun, dan pemberitahuan status pengajuan.</li><li>Premium hanya aktif setelah pembayaran berhasil. Sebelum itu toko yang disetujui menggunakan Free.</li><li>Jangan mengirim password atau informasi pembayaran melalui form ini.</li></ul>
<div class="auth-links"><a href="{{ route('store-registration.create') }}">Kembali ke Pendaftaran</a></div>
@endsection
