# Order pelanggan, notifikasi, dan pembayaran per toko

Penyelesaian: 7 Oktober 2026.

## Flow portal

Checkout `/customer/stores/{slug}/cart` menampilkan metode aktif milik business pada URL. Harga, diskon, stok, dan total dihitung ulang di server. Token checkout mencegah order dan notifikasi ganda. Input business_id, total, payment_status, dan identitas customer dari frontend tidak digunakan sebagai sumber otoritas.

Order menggunakan tabel `sales` existing dengan `order_source=customer_portal`. Karena sebelumnya tidak ada status pengerjaan order, ditambahkan kolom nullable `order_status`; order baru mengikuti `pending → confirmed → processing → completed`, atau `cancelled`. Penyelesaian membutuhkan pembayaran PAID. Data status pembayaran lama tetap menggunakan CASH_PENDING, PENDING, PAID, CANCELLED, EXPIRED, dan DENIED.

Detail customer: `/customer/orders/{order}`. Detail staff: `/portal-orders/{order}`. Staff memerlukan `sales.view` untuk membaca dan `sales.create` untuk memproses. Customer hanya dapat membaca order miliknya, termasuk gambar QRIS dan instruksi VA.

## Pembayaran

Atasan dengan `settings.edit` membuka **Pengaturan → Pengaturan Pembayaran Pelanggan**, URL `/settings/customer-payments`. Pengaturan tersimpan di `business_payment_settings`, satu baris per toko; tidak mengambil merchant key global atau konfigurasi DOKU.

- Default toko tanpa konfigurasi: Cash aktif; QRIS/VA tidak tampil.
- Cash: CASH_PENDING dan paid=0 sampai staff mengonfirmasi uang diterima. Tidak otomatis lunas saat order diterima/diproses.
- QRIS manual: upload PNG/JPG/WebP milik toko. Path gambar disalin ke `customer_order_payments` saat order dibuat; perubahan QRIS berikutnya tidak mengganti QRIS order lama. Gambar disimpan pada disk private dan hanya diakses pemilik order. Pembayaran PENDING sampai staff memverifikasi dana.
- QRIS Midtrans: memakai Snap merchant toko dengan `enabled_payments=[other_qris]`. Merchant harus memiliki layanan QRIS aktif.
- VA: tampil hanya jika diaktifkan, Server Key tersedia, dan bank dipilih. Daftar bank yang disediakan adalah BCA, BNI, BRI, Mandiri, dan Permata; aktifkan hanya yang tersedia pada merchant. Pemilihan bank, nomor VA asli, batas waktu, dan instruksi lengkap ditampilkan oleh halaman Snap yang dikembalikan provider. Halaman order juga menampilkan instruksi VA dari respons status gateway yang sudah diverifikasi.
- Server Key tersimpan terenkripsi dan tidak ditampilkan kembali. Konfigurasi merchant disalin secara terenkripsi untuk order sehingga perubahan key toko tidak mengubah verifikasi order lama. Key tidak disertakan pada flashed input saat validasi gagal.
- Setelah order dibuat, customer memulai pembayaran gateway melalui POST pada halaman detail, kemudian membuka halaman Midtrans. Pembukaan ulang memakai URL pembayaran yang sama. Return URL hanya menampilkan status database dan tidak mengubah pembayaran.
- Total gateway harus rupiah bulat positif; total pecahan tidak dibulatkan diam-diam menjadi jumlah charge berbeda.

Endpoint webhook: `/api/customer-payments/{business}/midtrans`. Request Snap menetapkan URL tersebut melalui `X-Override-Notification`; URL juga tersedia pada pengaturan untuk konfigurasi merchant. Verifikasi mencakup signature SHA-512 dengan key order, GET Status API merchant yang sama, nomor order, jumlah, mata uang, dan jenis pembayaran. PAID hanya dari status settlement yang terverifikasi. QRIS mendukung status acquirer GoPay/ShopeePay. Callback duplikat tidak mengulang pembayaran, notifikasi, atau pengembalian stok.

Request gateway yang hasilnya tidak pasti tidak dikirim ulang otomatis. Order tetap tersimpan dan belum lunas; staff perlu menelusuri merchant/gateway jika webhook tidak datang. Pembatalan lokal ditolak setelah request gateway dimulai untuk mencegah stok dilepas sementara pelanggan masih dapat membayar. Status expire/cancel/deny dari gateway mengembalikan stok satu kali. Refund transaksi lunas tidak ditambahkan pada task ini.

Alur Midtrans POS/QR meja existing tetap tersedia; endpoint global tidak boleh memproses order portal. DOKU subscription tidak digunakan untuk pembayaran pelanggan.

## Notifikasi navbar

Database notification dikirim ke staff aktif/approved di business order dengan relasi role Spatie Atasan/Admin/Kasir dan permission `sales.view`. Tidak membaca kolom role pada users. Order portal dan order QR meja memakai notifikasi pesanan; perubahan ke PAID menghasilkan notifikasi pembayaran diterima.

Data memuat order_id, order_number, customer_name, business_id, total, order_type, payment_method, dan created_at. ID notifikasi deterministik per order/event/user mencegah duplikasi.

Bell navbar menampilkan unread count dan delapan notifikasi terbaru, dengan polling 15 detik saat tab aktif. Klik melakukan POST untuk menandai dibaca lalu membuka detail order. Halaman notifikasi tetap memiliki tandai satu/semua dibaca. Semua akses membatasi penerima dan business; notifikasi legacy berisi sale_id juga dicocokkan dengan business sale. Super Admin mempertahankan halaman notifikasi platform existing.

## Migrasi dan audit

Migration `2026_10_06_000002_add_customer_order_payments.php` diterapkan pada database lokal. Menambahkan dua tabel baru dan kolom nullable order_status, tanpa backfill nilai status lama.

Hash kolom existing pada `businesses`, `sales`, `sale_details`, `products`, `settings`, dan `notifications` identik sebelum/sesudah migration. Tidak menggunakan fresh/refresh/wipe/truncate. Setelah migration terdapat nol konfigurasi merchant toko; aktivasi QRIS/VA memerlukan pengaturan pemilik toko.

## Verifikasi

`php artisan optimize:clear`, `php artisan route:list --json`, `php artisan view:cache`, dan pemeriksaan sintaks JavaScript navbar berhasil. Seluruh suite: **296 test lulus, 2.753 assertion**. Setelah perbaikan terakhir pada HTTP redirect dan pengujian mark-all-read, suite khusus pembayaran dijalankan ulang: **10 test lulus, 126 assertion**.

Test baru `CustomerOrderPaymentTest` menguji penerima role/permission, isolasi notifikasi, badge/read/open, cash dan status order, QRIS private, metode per toko, gateway memakai key toko, return URL tidak melunaskan, valid/invalid/duplicate callback, nominal salah, expiry, dan kegagalan gateway. Semua request provider pada test menggunakan Http fake; tidak ada charge nyata.

Pemeriksaan visual interaktif dan transaksi sandbox/live dengan akun merchant nyata belum dilakukan dalam sesi ini.

Referensi implementasi provider: [Midtrans Snap request](https://docs.midtrans.com/reference/request-body-json-parameter), [validasi dan override notifikasi](https://docs.midtrans.com/reference/handle-notifications), [GET Status API](https://docs.midtrans.com/reference/get-transaction-status), dan [Other QRIS](https://docs.midtrans.com/reference/other-qris).
