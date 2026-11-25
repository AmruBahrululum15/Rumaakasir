# Customer login dan akses platform

Verifikasi: 3 Oktober 2026.

## Akses

- `/customer/login`: email atau nomor HP dan password. Guard `customer`, provider `customers`, model `CustomerAccount`; terpisah dari staff `/login`.
- `/customer/register`, `/customer/forgot-password`, `/customer/dashboard`: menggunakan flow customer existing.
- `/super-admin/platform-customers`: menu **Pelanggan Platform**, pencarian/status, tanggal daftar/login terakhir, jumlah toko/order/booking/percakapan.
- `/super-admin/platform-customers/{account}`: detail akun, relasi toko, metadata percakapan. Tidak memuat pesan atau preview isi chat.
- `/portal-customers`: pelanggan terkait toko untuk Atasan/Admin dengan permission `customers.view` dan Customer Portal Premium aktif. Semua agregat dibatasi business staff.
- `/portal-customers/invite`: undangan pengaitan pelanggan QR lama, tambahan permission `customers.edit`, maksimal lima per jam per staff.
- `/customer/links/{token}`: konfirmasi pengaitan oleh akun customer yang dituju. GET hanya menampilkan konfirmasi; POST memerlukan CSRF dan persetujuan.

## Identitas dan data lama

`CustomerIdentity` menormalisasi nomor Indonesia/internasional untuk pencarian. Registrasi dan perubahan nomor menolak nomor terpakai. Nomor historis ambigu tidak dipilih sembarang: login melalui email tetap tersedia. Tidak ada backfill nomor atau perubahan akun lama.

Migration `2026_10_03_000001_extend_customer_platform_access.php` menambahkan `customer_accounts.login_phone`, `last_login_at`, dan tabel `customer_link_invitations`. Migration sudah diterapkan; tidak menjalankan fresh/refresh/reset/wipe/truncate.

Relasi tetap menggunakan `business_customers`: satu akun customer dapat terkait beberapa toko. Pengaitan QR memerlukan kontak cocok, undangan ke email akun, autentikasi akun yang sama, dan persetujuan eksplisit. Token random disimpan sebagai hash, berlaku satu jam, sekali pakai. Nama saja tidak cukup. Relasi yang sudah ada tidak digabung atau ditimpa; data pelanggan, transaksi, dan loyalty lama tidak diubah.

## Isolasi

- Loyalty tetap dihitung per business dan customer toko melalui membership.
- Conversation tetap memiliki business_id dan customer_account_id. Customer hanya membaca miliknya, staff hanya business sendiri dengan permission chat.
- Super Admin hanya melihat metadata; akses isi chat tetap ditolak.
- Portal penuh tetap membutuhkan tenant baru, Premium aktif, dan feature enabled. Tenant Free/expired tidak mendapatkan akses tulis Premium.
- Staff tidak otomatis menjadi customer, dan sesi customer tidak memberi akses dashboard staff.

## Verifikasi

- `php artisan optimize:clear`: berhasil.
- `php artisan route:list`: berhasil.
- `php artisan view:cache`: berhasil.
- `php artisan test`: **271 passed, 2486 assertions**; termasuk seluruh test existing.
- Sembilan test baru pada `CustomerPlatformAccessTest`, serta tambahan menu pada `SuperAdminNavigationTest`.
- Browser dengan database fixture terpisah: login melalui nomor HP berhasil; login staff dan halaman pelanggan Super Admin berhasil. Desktop 1366px/mobile 390px tidak menunjukkan overflow horizontal halaman.
- Screenshot: `screenshots/portal-customer-access-login-390.png`, `screenshots/portal-platform-customers-1366.png`, `screenshots/portal-platform-customers-390.png`, dan `screenshots/portal-platform-customer-metadata-1366.png`.
- Audit hash sepuluh tabel sebelum/sesudah migration: tidak ada nilai data lama berubah. Kolom baru nullable dikecualikan dari perbandingan. Toko Default tetap `business_type=null`.
- Automated test menggunakan database in-memory dan mail/notification fake. Pengiriman email nyata tetap bergantung konfigurasi mail existing.

Login staff, DOKU, Midtrans, dan flow POS tidak diubah dalam task ini.
