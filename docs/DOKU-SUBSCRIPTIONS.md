# Upgrade Premium melalui DOKU Checkout

Implementasi pembayaran subscription dipisahkan dari Sale, payment POS Midtrans,
dan QR checkout. Tidak ada perubahan pada controller POS, Midtrans service, role,
scope business_id, atau middleware ResolveTenant pada pekerjaan ini.

## Database dan file

Migration aditif `2026_09_24_000001_add_doku_subscriptions.php` sudah diterapkan
dengan `php artisan migrate --force`, setelah pemeriksaan `--pretend`:

- `packages`: tambah `price_monthly`, `price_yearly`, `is_active`. Harga awal
  Premium Rp49.000/bulan dan Rp499.000/tahun; dapat diubah melalui Fitur & Paket.
  Menonaktifkan paket menghentikan pembelian baru, bukan mencabut periode berbayar.
- `businesses`: tambah `subscription_source`, `subscription_started_at`.
  Field paket/status/akhir langganan existing tetap digunakan.
- `subscription_orders`: riwayat pembayaran tenant, snapshot nama toko/paket/harga,
  billing cycle, ID unik, request ID, referensi DOKU, checkout URL, batas pembayaran,
  status pembayaran, metode, tanggal bayar, serta awal/akhir periode.
  Foreign key tidak menghapus riwayat secara cascade. Rollback tidak menghapus tabel.

Model baru: `app/Models/SubscriptionOrder.php`, memakai `BelongsToBusiness` existing.
Model diperluas: `Package.php`, `Business.php`.

Backend baru:

- `app/Services/DokuService.php`: signature/header, API Checkout, validasi response
  dan URL pembayaran, verifikasi notification dari raw request body.
- `app/Services/SubscriptionService.php`: order, pembayaran idempotent, renewal, expiry.
- `app/Http/Controllers/SubscriptionController.php`: page pemilik, checkout, hasil, invoice.
- `app/Http/Controllers/DokuSubscriptionNotificationController.php`: notification.
- `app/Http/Middleware/EnsureSubscriptionOwner.php`: Atasan/Pemilik saja; menolak
  Super Admin sekalipun memiliki role tambahan.
- `config/doku.php`: konfigurasi dari ENV, default Sandbox.

Backend existing diperluas: PackageController (harga), SuperAdminPageController
(monitoring seluruh pembayaran), PackageAccess (paket efektif), BusinessController
(override subscription oleh platform kembali ke aturan manual existing).

View baru `resources/views/subscriptions/{index,history,result,invoice}.blade.php`.
Menu PAKET ditambahkan hanya bagi Atasan/Pemilik pada layout existing. View paket
Super Admin mendapat harga; view langganan Super Admin mendapat riwayat DOKU.

## Route

| Method | URL | Named route |
| --- | --- | --- |
| GET | `/subscription` | `subscriptions.index` |
| POST | `/subscription/checkout` | `subscriptions.store` |
| GET | `/subscription/payment/{orderId}/result` | `subscriptions.result` |
| GET | `/subscription/orders/{orderId}/invoice` | `subscriptions.invoice` |
| POST | `/api/doku/subscription/notification` | `doku.subscription.notification` |

Monitoring tetap memakai `super-admin.subscriptions.index`; harga tetap memakai
`packages.index` / `packages.update`. Route pemilik memakai auth, credential,
tenant, role-owner check, serta CSRF existing; checkout dibatasi 10 request/menit.
Invoice dan hasil pembayaran harus milik business user login. Notification tidak
memerlukan login/CSRF browser, tetapi selalu memverifikasi HMAC dan Client-Id.

## Flow dan keamanan

1. Atasan memilih monthly/yearly. Server mengambil tenant dari user login dan
   Premium beserta harga dari database. Input amount/business_id/plan_id diabaikan.
2. Business row dikunci. Order pending existing yang belum lewat batas dipakai
   ulang; GET/refresh tidak membuat order. ID memakai `SUB-` + ULID (30 karakter,
   tetap unik dan sesuai batas invoice channel kartu); bukan ID Sale.
3. Backend memanggil DOKU dengan request ID unik. Channel tidak dipaksakan; metode
   yang tersedia mengikuti konfigurasi merchant DOKU. Checkout URL tersimpan.
4. User diarahkan ke DOKU. Return URL hanya menampilkan status database.
5. Notification memverifikasi signature atas raw body dan path aktual, invoice,
   amount/currency, serta original request ID bila disertakan. SUCCESS pembelian
   mengaktifkan Premium dalam transaksi DB, dengan lock business lalu order.
6. `paid_at` menjadi guard idempotensi. Notifikasi sama atau berbeda yang mengulang
   pembayaran order tersebut tidak memperpanjang periode lagi.

Tidak ada credential di frontend/log. Log aplikasi hanya berisi order ID, status
HTTP, dan keterangan umum. Signature tidak dicatat. URL checkout wajib HTTPS pada
domain DOKU; HTTP client tidak mengikuti redirect dan tidak melakukan retry buta.

## Status dan kegagalan

Status pembayaran lokal: pending, paid, failed, expired; tampilan/model juga dapat
merepresentasikan cancelled. Tidak diarang webhook CANCELLED/EXPIRED untuk API ini.
Meninggalkan checkout atau return URL cancel tidak dianggap bukti pembatalan.

- SUCCESS mengesahkan pembayaran. AUTHORIZE/REFUND bukan pembayaran pembelian.
- FAILED dari Checkout adalah kegagalan **percobaan**, sehingga disimpan sebagai
  `last_payment_status=FAILED`, order tetap pending agar pelanggan dapat mencoba
  metode lain. UI menjelaskan kegagalan percobaan tersebut.
- Status notification yang belum dikenal diakui tanpa mengaktifkan Premium.
- Penolakan create-payment yang jelas (400/401/403/422) menghasilkan failed dan
  user dapat membuat order baru.
- Timeout/5xx/response tidak valid berarti hasil remote belum pasti: order tetap
  pending, tidak diulang otomatis. Hubungi pengelola dengan Order ID atau tunggu
  jendela pembayaran berakhir; jangan membuat charge pengganti secara otomatis.
- Checkout yang melewati `payment_due_at` tidak lagi menawarkan tombol lanjut.
  Paid notification sah yang terlambat tetap dapat melunasi order lokal expired.
- Receipt hanya tersedia untuk paid. Status paid tetap dipertahankan pada invoice
  walaupun periode Premium akhirnya habis.

## Periode dan kompatibilitas

Carbon `addMonthNoOverflow()` / `addYearNoOverflow()` menangani tanggal akhir bulan
dan tahun kabisat. Bulanan berarti satu bulan kalender, tahunan satu tahun.
Pembelian selama Premium masih aktif menambah dari expiry existing. Jika habis,
periode baru mulai ketika notification sah diproses. Harga/nama pada order lama
tidak berubah ketika Super Admin mengubah paket.

Untuk membership DOKU, `Business::effectiveSubscriptionPlan()` memberikan Free
ketika status tidak active atau expiry tidak lagi di masa depan. `PackageAccess`
menggunakan nilai efektif tersebut pada pengecekan fitur/limit. `allowsOperations()`
mengizinkan toko DOKU expired tetap berjalan sebagai Free, tetapi is_active=false,
suspended, dan inactive tetap ditolak. Produk/user/meja tidak dihapus.

Command `php artisan subscriptions:expire` memperbarui status tersimpan dan
pending order yang habis. Terjadwal setiap menit melalui Laravel scheduler;
jalankan `php artisan schedule:run` tiap menit sesuai deployment. Keamanan fitur
tidak bergantung pada scheduler.

**Kompatibilitas data existing:** langganan manual yang sebelumnya diatur Super
Admin mempertahankan aturan lama; tidak diubah massal menjadi langganan DOKU.
Source DOKU baru ditetapkan setelah pembayaran sah. Tindakan override paket/status/
expiry oleh Super Admin mengembalikan kendali ke flow manual. Paket manual Premium
tanpa batas waktu tidak ditagih perpanjangan. Pembayaran yang masuk saat toko
disuspend/nonaktif tidak mengaktifkan kembali toko tersebut.

## ENV lokal dan uji Sandbox

Isi `.env` sendiri, jangan commit credential:

```dotenv
APP_URL=https://alamat-aplikasi-publik
DOKU_ENV=sandbox
DOKU_CLIENT_ID=isi_client_id_sandbox
DOKU_SECRET_KEY=isi_secret_key_sandbox
DOKU_BASE_URL=https://api-sandbox.doku.com
DOKU_NOTIFICATION_URL=https://alamat-aplikasi-publik/api/doku/subscription/notification
```

Jika deployment memakai subdirectory, sertakan prefix tersebut pada URL lengkap.
Notification URL harus dapat diakses DOKU melalui HTTPS publik; localhost tidak
dapat menerima webhook dari DOKU. DOKU_NOTIFICATION_URL boleh kosong jika route
yang dihasilkan aplikasi sudah sesuai URL publik. Konfigurasikan URL yang sama
di DOKU Back Office; request juga mengirim override_notification_url.

1. Isi ENV, jalankan `php artisan optimize:clear`, akses aplikasi melalui URL publik.
2. Pastikan DOKU Checkout Sandbox serta channel yang ingin diuji sudah aktif.
3. Login Atasan → PAKET → Paket & Langganan → Premium Bulanan → Aktifkan Premium.
4. Pastikan order pending berharga dari database, lalu diarahkan ke DOKU Sandbox.
5. Simulasikan pembayaran melalui [simulator resmi DOKU](https://sandbox.doku.com/integration/simulator/).
6. Pastikan POST notification memperoleh 200, order paid, Premium active, dan
   expiry satu bulan. Return URL tanpa notification harus tetap pending.
7. Buka invoice. Kirim ulang notification melalui fasilitas DOKU; expiry tidak
   boleh bertambah lagi.
8. Ulangi dengan tahunan pada tenant uji Free (expiry satu tahun), lalu renewal
   sebelum habis (ditambah dari expiry sebelumnya). Uji gagal/ditinggalkan juga.
9. Uji tenant uji lain tidak dapat membuka invoice/history toko pertama.

Pada pengerjaan ini credential DOKU di `.env` belum tersedia dan URL publik belum
diberikan. **Pembayaran Sandbox end-to-end nyata belum diverifikasi.** Tidak ada
request pembayaran nyata saat automated test maupun verifikasi browser.

## Hasil verifikasi

- `optimize:clear`, `route:list` (146 routes), `view:cache`, `test`: berhasil.
- **122 tests passed / 1009 assertions**: 100 test existing dan 22 test baru.
- Test baru `tests/Feature/DokuSubscriptionTest.php` memakai Http::fake dan melarang
  stray request; menguji harga/tenant server, ACL, signature, nominal, pending,
  receipt, monthly/yearly, renewal, expiry, data tetap dapat dibaca, idempotensi,
  error/timeout, cross-tenant, monitoring/harga, dan kompatibilitas manual.
- Semua test POS, Midtrans/QR, login, paket, dan tenant existing tetap lulus.
- Snapshot nilai kolom/baris lama di 20 tabel identik setelah migration aditif.
- Audit ulang di akhir sesi menemukan perubahan pada data POS yang berjalan:
  sales 16→17, sale_details 18→19, customers 5→6, tables 20→23, notifications
  9→11, serta checksum products berubah. Penyebab/perubahan tersebut tidak
  diverifikasi sebagai bagian tugas subscription dan tidak ditimpa/dirollback.
  Test otomatis/browser memakai database SQLite in-memory terpisah.
- Browser Edge memakai database in-memory: menu Atasan, harga, pilihan billing,
  desktop/mobile tanpa overflow dokumen, dan pesan konfigurasi kosong berhasil.

Screenshot data uji:
[desktop](screenshots/subscription-owner-desktop.png),
[mobile](screenshots/subscription-owner-mobile.png).

Referensi resmi:
[Checkout backend](https://developers.doku.com/accept-payments/doku-checkout/integration-guide/backend-integration),
[signature request](https://developers.doku.com/get-started-with-doku-api/signature-component/non-snap/signature-component-from-request-header),
[notification best practice](https://developers.doku.com/get-started-with-doku-api/notification/best-practice),
[simulation](https://developers.doku.com/accept-payments/doku-checkout/integration-guide/simulate-payment-and-notification).
