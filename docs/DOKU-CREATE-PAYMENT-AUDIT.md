# Audit create DOKU Checkout — 25 September 2026

Lingkup: create payment saja. Tidak ada perubahan schema/database existing, scope
tenant, role, paket, sidebar, payment POS/Midtrans, atau aktivasi melalui webhook.
Tidak dilakukan simulasi callback/pembayaran Sandbox.

## Temuan request nyata

- Environment: sandbox.
- Endpoint: `POST https://api-sandbox.doku.com/checkout/v1/payment`.
- Client ID/Secret Key terisi; tidak ditemukan spasi tersembunyi atau format PEM.
- Callback/notification dikonfigurasi melalui URL HTTPS ngrok.
- Request penuh dengan harga tahunan dari database dan pelanggan uji ditolak:
  HTTP **400**, code **invalid_signature**, message **Invalid Header Signature**.
- Checkout URL belum diperoleh; redirect browser ke Checkout nyata belum berhasil.
- Probe tidak menulis database lokal atau mengubah paket toko.

Header Client-Id, Request-Id UUID, timestamp UTC, target `/checkout/v1/payment`,
SHA256/base64 digest atas body JSON yang sama dengan bytes terkirim, serta
HMAC-SHA256/base64 tanpa newline terakhir cocok dengan dokumentasi. Tidak diubah
menjadi algoritma alternatif untuk menyembunyikan penolakan autentikasi gateway.
Pasangan credential Sandbox Checkout Non-SNAP perlu dikonfirmasi oleh pemilik akun.

## Perbaikan

- `DokuService.php`: mencatat HTTP status, request/order ID, code/message dan body
  JSON respons yang disensor; credential, signature, header, token, URL pembayaran,
  serta data pelanggan tidak dicatat. Respons non-JSON tidak didump mentah.
- `DokuCheckoutException.php`: membedakan penolakan eksplisit dari hasil tidak pasti.
- `SubscriptionService.php`: status awal `creating`; hanya berubah menjadi pending
  setelah URL, invoice, amount dan expiry valid. Error terstruktur 4xx → failed;
  timeout/5xx/respons tidak valid → `create_unknown`, tanpa blind retry.
- `SubscriptionOrder.php`: hasil create yang tidak pasti tetap memiliki batas waktu.
- `SubscriptionController.php`: mencegah checkout duplikat saat create belum selesai.
- View `subscriptions/index` dan `subscriptions/result`: pesan create error yang
  dapat ditindaklanjuti, termasuk untuk order failed lama tanpa checkout URL.
- Tidak ada perubahan pada metode signature atau handler notification/activation.

Paid notification yang lebih cepat dari respons create tidak ditimpa oleh state
pending/failed. Respons error diperiksa sebelum menentukan status failed. Data
order lama tidak dimigrasikan atau ditulis ulang.

## Verifikasi

`optimize:clear` dan `view:cache` berhasil. Pengujian mencakup signature atas body
aktual, endpoint lengkap, status creating sebelum API merespons, error asli 400,
redaksi log, expiry tidak valid, timeout/deduplication, dan redirect saat respons
Checkout valid. Semua request automated test memakai Http::fake.

Referensi resmi:
[Backend Checkout](https://developers.doku.com/accept-payments/doku-checkout/integration-guide/backend-integration),
[Signature Non-SNAP](https://developers.doku.com/get-started-with-doku-api/signature-component/non-snap/signature-component-from-request-header).
