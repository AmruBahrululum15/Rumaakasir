# Pendaftaran toko dengan approval

Form publik: `/register-store`, named route `store-registration.create` / `.store`.
Halaman sukses dan ketentuan: `store-registration.success` / `.terms`.
Aktivasi: `store-registration.activate` (GET) dan `.activate-account` (POST),
memakai signed URL 7 hari dan CSRF. GET tidak login dan tidak memakai tautan.

Review Super Admin: `/super-admin/store-registrations`, dengan named route
`super-admin.store-registrations.index`, `.show`, `.approve`, `.reject`, `.resend`.
Seluruh route review memakai middleware auth, credentials.changed, role Super Admin.

## Struktur

- Migration `2026_09_25_000001_create_store_registrations_table.php` hanya menambah
  tabel `store_registrations`, tanpa mengubah atau menghapus data existing.
- Model `StoreRegistration` menyimpan informasi pengajuan, hasil review dan
  referensi toko/Atasan yang dibuat. Tidak memiliki kolom password.
- `StoreRegistrationRequest` memvalidasi form; submit dibatasi 5 per jam per IP.
- `StoreRegistrationController` mengelola submit dan aktivasi.
- `StoreRegistrationReviewController` dan `StoreRegistrationService` mengelola review,
  pembuatan Business/User/Setting dengan TenantContext existing, dan pengiriman email.
- `StoreRegistrationNotification` menggunakan database notification existing.
- `StoreRegistrationMail` memakai Laravel Mail existing untuk acknowledgment,
  review, approval dan rejection; konfigurasi SMTP tetap berasal dari `.env`.

## Keputusan implementasi

Opsi setup password sesudah approval dipilih sesuai preferensi task. Form tidak
meminta password; saat approval User memperoleh hash password acak yang tidak
pernah dikirim. Pemilik membuka tautan aktivasi, melakukan POST, lalu diarahkan
ke first-login existing dengan `must_change_credentials=true`. Dashboard tetap
terkunci sampai username/password disimpan melalui flow existing.

Approval menggunakan transaksi dan lock pengajuan. Approve ulang tidak menambah
toko/user. Keputusan yang berlawanan ditolak 409. Username/email diperiksa kembali
sebelum pembuatan akun, dengan constraint unik sebagai perlindungan race.
Username/email pengajuan unik termasuk pengajuan yang sudah ditolak; jika perlu
mengajukan ulang identitas yang sama, pemohon harus menghubungi pengelola.

Free disetujui menjadi toko aktif dengan paket Free. Premium disetujui menjadi
toko aktif dengan `subscription_plan=free`, `subscription_status=pending_payment`,
`subscription_source=store_registration`. Business::allowsOperations hanya
menambahkan pengecualian sempit untuk kombinasi ini; inactive/suspended tetap
terblokir. Fitur dan batas tetap memakai PackageAccess existing. Tidak ada
request gateway saat pendaftaran atau approval. DOKU/Midtrans tidak diubah.

Email/notifikasi diproses setelah transaksi review selesai. Kegagalan pengiriman
dicatat dengan ID pengajuan, tahap dan tipe exception, tanpa pesan exception
atau credential SMTP. Approval tetap tersimpan. Admin dapat mengirim ulang
tautan aktivasi yang belum digunakan dari halaman detail pengajuan.

Halaman ketentuan merupakan informasi awal/placeholder yang dinyatakan jelas;
pengelola perlu menerbitkan kebijakan lengkap sebelum peluncuran publik.

## Pengujian manual

1. Buka Login → Daftarkan Toko. Isi 4 langkah dengan email milik Anda, lalu submit.
2. Pastikan belum ada toko/user baru dan pengajuan berstatus Pending.
3. Login Super Admin → Pengajuan Toko; cek badge, notifikasi, filter dan detail.
4. Approve Free; cek satu Business baru dan satu Atasan. Klik approve lagi tidak
   membuat duplikat. Email berisi tautan aktivasi, tanpa password.
5. Buka email di browser yang logout, klik aktivasi, buat password melalui
   first-login existing. Pastikan dashboard toko baru kosong.
6. Ulangi dengan identitas berbeda untuk Premium; sesudah approval akses Free
   tetap berjalan dan Paket & Langganan menampilkan Premium Menunggu Pembayaran.
7. Untuk pengajuan lain, Reject wajib alasan dan tidak membuat toko/user.
8. Uji SMTP dengan alamat yang Anda miliki; automated tests memakai fake dan
   tidak membuktikan pengiriman email ke inbox sungguhan.

Pemeriksaan browser lokal mencakup validasi stepper, retensi pilihan, review,
desktop 1366px dan mobile 390px tanpa horizontal overflow. Browser tidak submit
ke database operasional. Screenshot berada di `docs/screenshots/store-registration-*`.

## Hasil verifikasi

- Migration baru sudah diterapkan pada database proyek melalui migrate --path.
- Fingerprint business_id=1 dan jumlah baris tabel existing sebelum/sesudah tetap sama.
- optimize:clear, route:list (157 route), view:cache berhasil.
- php artisan test: 150 test / 1268 assertion lulus, termasuk test existing.
- 17 test khusus pendaftaran ditambahkan; test navigasi diperluas untuk menu baru,
  termasuk pemeriksaan active state dan larangan akses role tenant.
- Mail transport existing adalah SMTP; credential tidak diubah. Pengiriman ke
  inbox nyata belum diuji. Tidak ada email nyata, pembayaran, atau tenant uji
  dibuat pada database operasional selama pengujian.
