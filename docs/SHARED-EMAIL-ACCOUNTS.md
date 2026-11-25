# Email bersama, akun terpisah

Email adalah alamat korespondensi, bukan identitas tenant. Username tetap unik global; toko berasal dari `users.business_id` akun yang login.

- Registrasi, approval, pembuatan toko oleh Super Admin, manajemen pengguna, dan edit profil menerima email yang sudah digunakan akun lain.
- Pengajuan dengan username yang telah digunakan akun/pengajuan lain ditolak. Submit ulang dengan submission token yang sama tetap idempotent.
- Migration `2026_09_28_000001_allow_shared_account_email.php` memeriksa duplikat username sebelum menghapus hanya indeks unik email pada `users` dan `store_registrations`. Indeks unik username tetap dipertahankan. Tidak ada perubahan nilai data. Rollback tidak mengembalikan uniqueness email karena bisa merusak akun yang telah berbagi email.
- Login toko menggunakan username. Kompatibilitas login email Super Admin berlaku hanya jika satu akun cocok dan akun tersebut Super Admin; pencocokan username selalu lebih dahulu.

## Recovery

Route existing `password.request`, `password.email`, `password.reset`, dan `password.store` tetap digunakan. Input sekarang username.

Laravel Password Broker tetap membuat token acak, menyimpan hash, memeriksa expiry dan cooldown, lalu menghapus token setelah penggunaan. `User::getEmailForPasswordReset()` mengembalikan `account:{id}` sebagai kunci repository. Kolom existing `password_reset_tokens.email` kini berisi identifier akun tersebut untuk token baru, bukan alamat pengiriman email. Notifikasi tetap dikirim melalui alamat `users.email`.

Reset mencari username, kemudian broker memvalidasi token terhadap ID akun tersebut. Username readonly di form hanya untuk UX, bukan lapisan keamanan. Transaksi dan row lock akun menyerialkan penerbitan/pemakaian token. Response permintaan reset selalu generik, termasuk akun tidak ditemukan, cooldown, dan kegagalan pengiriman. Route throttle existing tetap digunakan.

Tautan lama berbasis email tidak dipakai lagi. Pengguna perlu meminta tautan baru menggunakan username. Password, username, business_id, status first-login, dan akun lama tidak diubah atau dipaksa reset.

Email registrasi/approval dan reset menyebut username serta nama toko. Activation link dan pengaturan password pertama tetap menggunakan akun dari pengajuan/authenticated user, bukan email. Tidak ada perubahan integrasi pembayaran atau penambahan email pembayaran baru.

## Verifikasi

`SharedEmailAccountTest` memeriksa dua pengajuan/approval dengan email sama, tenant berbeda, login, kompatibilitas Super Admin, password pertama, password akun, token silang/hash/expiry/replay, email dan form reset, response generik/rate limit, manajemen staf, dan profil. Test lama yang mengirim email untuk login/reset disesuaikan ke kontrak username baru; cakupan pemeriksaannya dipertahankan.
