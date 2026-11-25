<link rel="stylesheet" href="{{ asset('css/customer-register.css') }}">
<script src="{{ asset('js/customer-register.js') }}" defer></script>
<div class="customer-register">
<aside class="cr-intro"><span class="cr-eyebrow">SELAMAT DATANG DI RUMAAKASIR</span><h1>Satu akun, <br>banyak kemudahan.</h1><p>Temukan toko favorit dan nikmati pengalaman belanja yang lebih praktis.</p><div class="cr-benefits"><div><span>01</span><div><strong>Pesan & reservasi</strong><p>Kelola pesanan dan jadwal kunjungan Anda.</p></div></div><div><span>02</span><div><strong>Chat langsung dengan toko</strong><p>Tanyakan produk atau layanan sebelum membeli.</p></div></div><div><span>03</span><div><strong>Loyalty & voucher</strong><p>Lihat poin dan penawaran dari toko pilihan.</p></div></div></div><small>Sudah punya akun? <a href="{{ route('customer.login') }}">Masuk di sini →</a></small></aside>
<section class="cr-card"><div class="cr-card-heading"><span class="cr-eyebrow">AKUN PELANGGAN</span><h2>Buat akun Anda</h2><p>Isi data berikut untuk mulai menjelajahi toko.</p></div>
<form method="post" action="{{ route('customer.register.submit') }}" data-customer-register>@csrf
<fieldset><legend><span>1</span> Informasi pribadi</legend>
<div class="cr-field"><label for="name">Nama lengkap</label><input id="name" name="name" required maxlength="120" autocomplete="name" placeholder="Nama lengkap Anda" value="{{ old('name') }}" @error('name') aria-invalid="true" @enderror>@error('name')<small class="cr-error">{{ $message }}</small>@enderror</div>
<div class="cr-field"><label for="phone">Nomor HP / WhatsApp</label><input id="phone" name="phone" type="tel" inputmode="tel" required maxlength="30" autocomplete="tel" placeholder="Contoh: 081234567890" value="{{ old('phone') }}" aria-describedby="cr-phone-help" @error('phone') aria-invalid="true" @enderror><small id="cr-phone-help">Gunakan nomor aktif. Nomor ini juga bisa digunakan untuk masuk.</small>@error('phone')<small class="cr-error">{{ $message }}</small>@enderror</div>
<div class="cr-field"><label for="email">Alamat email</label><input id="email" name="email" type="email" required maxlength="190" autocomplete="email" placeholder="nama@email.com" value="{{ old('email') }}" aria-describedby="cr-email-help" @error('email') aria-invalid="true" @enderror><small id="cr-email-help">Untuk menerima informasi akun dan memulihkan password.</small>@error('email')<small class="cr-error">{{ $message }}</small>@enderror</div>
</fieldset>
<fieldset class="cr-address" data-customer-regions data-endpoint="{{ route('store-registration.regions') }}"><legend><span>2</span> Alamat pelanggan <small>Opsional</small></legend>
<p class="cr-address-note">Simpan alamat utama sekarang agar lebih praktis saat memesan. Anda juga bisa menambahkannya nanti di Profil.</p>
<div class="cr-field"><label for="address">Alamat lengkap</label><textarea id="address" name="address" rows="3" maxlength="1000" autocomplete="street-address" placeholder="Nama jalan, nomor rumah, RT/RW, atau patokan">{{ old('address') }}</textarea>@error('address')<small class="cr-error">{{ $message }}</small>@enderror</div>
<input type="hidden" name="address_mode" value="{{ old('address_mode','structured') }}">
<div class="cr-address-grid">
@foreach(['province'=>'Provinsi','city'=>'Kota / Kabupaten','district'=>'Kecamatan','village'=>'Kelurahan / Desa'] as $field=>$label)
<div class="cr-field"><label for="{{ $field }}_id">{{ $label }}</label><select id="{{ $field }}_id" name="{{ $field }}_id" data-region="{{ $field }}" data-old="{{ old($field.'_id') }}"><option value="">Pilih {{ strtolower($label) }}</option></select><input name="{{ $field }}" data-region-name="{{ $field }}" value="{{ old($field) }}" maxlength="100" placeholder="{{ $label }}" hidden>@error($field.'_id')<small class="cr-error">{{ $message }}</small>@enderror @error($field)<small class="cr-error">{{ $message }}</small>@enderror</div>
@endforeach
</div><div class="cr-field"><label for="postal_code">Kode pos</label><input id="postal_code" name="postal_code" inputmode="numeric" pattern="[0-9]{5}" maxlength="5" autocomplete="postal-code" placeholder="Terisi jika tersedia, atau isi 5 angka" value="{{ old('postal_code') }}" list="customer-postal-options"><datalist id="customer-postal-options"></datalist>@error('postal_code')<small class="cr-error">{{ $message }}</small>@enderror</div>
<p class="cr-address-note" data-region-feedback role="status">Memuat pilihan provinsi�</p><button class="cr-region-mode" type="button" data-region-manual>Isi wilayah manual</button>
<noscript><p>Aktifkan JavaScript untuk pilihan wilayah. Alamat dapat ditambahkan nanti melalui Profil.</p></noscript>
<small class="cr-address-note">Jika mengisi alamat, lengkapi juga provinsi dan kota/kabupaten.</small>
</fieldset>
<fieldset><legend><span>3</span> Keamanan akun</legend>
<div class="cr-field"><label for="password">Password</label><div class="cr-password"><input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" placeholder="Buat password Anda" aria-describedby="cr-password-help cr-caps" @error('password') aria-invalid="true" @enderror><button type="button" data-cr-toggle="password" aria-controls="password" aria-label="Tampilkan password" aria-pressed="false">Lihat</button></div><small id="cr-password-help">Minimal 8 karakter. Gunakan kombinasi yang mudah Anda ingat.</small><small id="cr-caps" class="cr-warning" hidden>Caps Lock sedang aktif.</small>@error('password')<small class="cr-error">{{ $message }}</small>@enderror</div>
<div class="cr-field"><label for="confirmation">Konfirmasi password</label><div class="cr-password"><input id="confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" placeholder="Ketik kembali password Anda" aria-describedby="cr-match"><button type="button" data-cr-toggle="confirmation" aria-controls="confirmation" aria-label="Tampilkan konfirmasi password" aria-pressed="false">Lihat</button></div><small id="cr-match" role="status" aria-live="polite">Masukkan password yang sama.</small></div>
</fieldset>
<button type="submit" class="cr-submit">Buat Akun Pelanggan <span aria-hidden="true">→</span></button>
<p class="cr-signin">Sudah terdaftar? <a href="{{ route('customer.login') }}">Masuk</a></p>
</form><div class="cr-business">Ingin mengelola bisnis? <a href="{{ route('login') }}">Masuk ke Rumaakasir Bisnis ↗</a></div></section>
</div>

<script src="{{ asset('js/customer-register-regions.js') }}" defer></script>
