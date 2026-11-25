# Penyelesaian portal pelanggan

Tanggal: 6 Oktober 2026.

## Fitur yang terhubung

- `/customer/dashboard`: ringkasan reservasi, pesanan, loyalty, kategori, dan rekomendasi toko, dengan tampilan responsif.
- `/customer/profile`: profil, foto privat, tanggal lahir opsional, dan beberapa alamat dengan satu alamat utama. Alamat hanya dapat diubah oleh pemilik akun.
- `/customer/stores`: pencarian toko/layanan, kategori, alamat, lokasi browser, radius 1/5/10/25 km, serta pilihan daftar/peta. Toko tanpa koordinat tetap muncul pada daftar tanpa filter radius; toko Free, expired, dan portal nonaktif dikecualikan.
- Pengaturan toko: Atasan dengan `settings.edit` dapat mengisi/menghapus pasangan koordinat toko sendiri.
- Chat mengambang tampil untuk pelanggan yang sudah masuk, kecuali di halaman chat penuh. Pada layar kecil tombol membuka halaman chat penuh. Polling berhenti ketika tab tersembunyi; respons lama saat berpindah percakapan diabaikan dan pengiriman ganda saat request berlangsung dicegah.
- Badge unread diperbarui setelah percakapan dibaca. Percakapan tertutup, membership nonaktif, dan portal expired tidak mengaktifkan input chat melalui API.

Dashboard sudah terhubung melalui `CustomerPortalController::history`; tidak diperlukan perubahan rute dashboard.

## Database

Migration `2026_10_04_000001_add_customer_profiles_and_store_locations.php` diterapkan pada database lokal pada tanggal di atas. Migration menambahkan kolom nullable pada `businesses` dan `customer_accounts`, serta tabel `customer_addresses`.

Hash seluruh kolom lama pada kedua tabel dibandingkan sebelum/sesudah migration: nilainya tetap sama. Tidak menjalankan fresh/refresh/reset/wipe.

## Verifikasi

- Seluruh suite `php artisan test`: **277 test lulus, 2.551 assertion**.
- `CustomerPortalExperienceTest`: 6 test, 65 assertion lulus, mencakup komponen halaman, pencarian radius/urutan, validasi koordinat, alamat utama dan kepemilikan alamat, identitas/foto privat, isolasi chat, unread, status percakapan, expiry, dan izin lokasi toko.
- Pemeriksaan sintaks Node untuk tiga skrip portal lulus.
- Kompilasi Blade dan daftar rute berhasil.
- Pemeriksaan visual interaktif browser belum dilakukan pada sesi ini.

Peta menggunakan Leaflet/OpenStreetMap dan membutuhkan koneksi internet. Lokasi browser memerlukan izin pengguna dan secure context (HTTPS atau localhost); pencarian alamat manual tetap tersedia. Radius menggunakan jarak garis lurus, bukan jarak perjalanan.

## Cek manual

1. Masuk sebagai customer, buka dashboard/profil, simpan foto dan dua alamat; pindahkan alamat utama.
2. Sebagai Atasan, isi koordinat toko pada Pengaturan. Cari toko tersebut dari direktori, izinkan lokasi, pilih radius, lalu ganti daftar/peta.
3. Mulai chat dari halaman toko. Dari dashboard desktop buka tombol chat, pilih toko, dan kirim pesan. Balas sebagai staff dan periksa badge unread.
4. Ulangi pada layar ponsel; tombol chat membuka halaman percakapan penuh.
