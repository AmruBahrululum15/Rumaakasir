# Super Admin dan isolasi toko

Implementasi melanjutkan `Business`, `TenantContext`, scope `business_id`, autentikasi,
Spatie Permission, settings, notifikasi, dan layout existing. Tidak ada akun existing
yang dipindahkan ke toko lain atau diganti passwordnya.

## Penggunaan

1. Login menggunakan akun Super Admin existing. Buka `/super-admin/dashboard` atau
   `/businesses`.
2. Tambah toko beserta akun Atasan. Pembuatan toko, branding, dan akun berlangsung
   dalam satu transaksi. Atasan wajib mengganti kredensial awal melalui alur existing.
3. Atur paket Free/Premium, status langganan, akhir langganan, serta batas pengguna
   dan produk melalui Edit Toko. Waktu mengikuti timezone aplikasi.
4. Pilih **Kelola POS** untuk bekerja pada toko tersebut. Super Admin harus memilih
   toko; banner menampilkan toko aktif. **Ganti toko** kembali ke daftar platform.
5. Atasan menggunakan menu Pengguna existing untuk mengelola pengguna tokonya,
   sesuai permission Spatie. Atasan tidak dapat mengelola platform, mengubah akun
   Super Admin, atau memberikan role Super Admin.

Aturan Free/Premium kini diatur terpusat melalui **Fitur & Paket**. Free memiliki
100 produk, 2 pengguna approved, dan 5 meja; Premium secara default unlimited.
Batas per-toko existing tetap dipertahankan sebagai batas tambahan. Kuota per toko
yang kosong mengikuti paket. Penurunan kuota tidak menghapus data existing.
Lihat [panduan paket](PACKAGES.md) untuk feature flags, approval, dan perhitungan
batas yang berlaku sekarang.

Toko nonaktif, langganan nonaktif, atau kedaluwarsa tidak dapat menjalankan modul
POS/QR. Super Admin tetap dapat membuka pengelolaan platform untuk mengaktifkan
kembali toko. Akun yang dinonaktifkan juga ditolak meskipun masih memiliki sesi.

## Data dan keamanan

- Tenant ditentukan sebelum route model binding. Parameter `business_id` dari form
  tidak menentukan kepemilikan record. Validasi relasi produk, supplier, dan kategori
  dibatasi pada tenant aktif.
- Konteks tenant dibersihkan pada awal/akhir request web maupun API. Branding guest
  tetap menggunakan Toko Default ID 1, tidak bergeser ke toko aktif lainnya.
- Foto pengguna baru disimpan di disk privat `local`; foto lama tetap di lokasi
  existing. `/user-photos/{id}` memeriksa pemilik atau izin melihat pengguna pada
  toko yang sama. Foto produk dan logo tetap publik untuk kebutuhan menu QR.
- `.htaccess` melindungi URL foto lama pada Apache/XAMPP. `server.php` menerapkan
  perlindungan yang sama pada `php artisan serve`. Root `.htaccess` menolak akses
  langsung ke direktori storage jika project root terpapar melalui XAMPP.
- Pada web server lain, gunakan document root `public` dan blokir akses statis
  `/storage/users/`, `/storage/profiles/`, serta `/storage/profile/` sebelum aturan
  static-file serving. Jangan memublikasikan direktori storage privat.
- Alur Forgot Password dan pembayaran Midtrans tidak diubah oleh pekerjaan ini.
  Perubahan pada file Midtrans yang telah ada sebelum pekerjaan tetap dipertahankan.

## Migrasi dan pengujian

Migrasi tambahan `2026_09_23_000000_add_tenant_capacity_limits` hanya menambahkan
`user_limit` dan `product_limit` nullable. Telah diterapkan pada database lokal dengan
`php artisan migrate --force`. Snapshot hash membuktikan seluruh data pada kolom
existing di 18 tabel operasional/akses tetap sama sebelum dan sesudah migrasi.

Jalankan suite:

```powershell
php vendor/bin/phpunit
```

Trait `Tests\Concerns\RefreshDatabase` menjalankan `migrate` pada SQLite `:memory:`
dan transaksi test, bukan perintah migrasi destruktif. Trait menolak koneksi di luar
SQLite in-memory. Suite mencakup login existing, login toko kedua, first-login,
Forgot Password, transaksi kasir, checkout QR, akses platform, pemilihan tenant,
ID lintas tenant, kuota, sesi nonaktif, file privat, notifikasi, serta sumber ekspor.

Verifikasi terakhir: **55 test, 284 assertion lulus**; Blade berhasil dikompilasi.
Pemeriksaan HTTP lokal: login 200, URL foto statis 403 pada Artisan/Apache, endpoint
foto tanpa autentikasi 302 ke login, akses storage privat melalui Apache 403.
Pembayaran Midtrans eksternal dan pengiriman email nyata tidak dijalankan.
