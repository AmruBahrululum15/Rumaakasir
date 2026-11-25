# Customer Portal Premium

## Struktur dan akses

- `customer_accounts`: akun pelanggan global, guard `customer`, terpisah dari staff `users`/guard `web`. Email unik, password hashed, register/login/logout/profile dan password reset.
- `customer_password_reset_tokens`: broker `customers` terpisah; token hashed, kedaluwarsa 60 menit, sekali pakai, respons forgot password generik.
- `business_customers`: satu relasi unik `(business_id, customer_account_id)` dengan pelanggan POS `customer_id` yang dibuat khusus. Tidak mengambil alih histori/poin pelanggan lama berdasarkan email/telepon.
- `conversations` dan `messages`: customer ↔ business saja. Pesan teks di-escape; AJAX memakai `textContent`; polling 15 detik saat tab aktif; pagination pesan lama.
- `portal_checkouts`: token unik untuk mencegah order ganda.
- Kolom nullable `customer_account_id` ditambahkan pada `sales` dan `bookings`. Tidak ada backfill atau perubahan identitas tenant.

Migration: `database/migrations/2026_09_28_200000_create_customer_portal_tables.php`. Migration sudah diterapkan secara additive. Tidak menjalankan fresh/refresh/reset/wipe/truncate.

## Route

Definisi di `routes/customer.php`:

| Area | URL / named route |
| --- | --- |
| Auth customer | `/customer/login`, `/customer/register`, `/customer/forgot`, `/customer/reset` — `customer.*` |
| Dashboard dan histori | `/customer/dashboard`, `/customer/orders`, `/customer/bookings`, `/customer/loyalty`, `/customer/vouchers` |
| Direktori | `/customer/stores` — `customer.stores` |
| Toko | `/store/{slug}` — `customer.store` |
| Produk | `/store/{slug}/products/{id}` — `customer.product` |
| Keranjang | `/customer/stores/{slug}/cart` — `customer.cart`, `customer.cart.add` |
| Checkout | POST `/customer/stores/{slug}/checkout` — `customer.cart.checkout` |
| Booking | POST `/customer/stores/{slug}/booking` — `customer.book` |
| Chat customer | `/customer/chat`, `/customer/chat/{id}` — `customer.chat.*` |
| Chat staff | `/customer-chat` — `customer-chat.*` |
| Order staff | `/portal-orders` — `portal-orders.*` |
| Ringkasan Super Admin | `/super-admin/customer-portal` — `super-admin.customer-portal` |

Semua POST/PATCH menggunakan CSRF. Auth, chat, dan aksi customer memakai rate limiter terpisah.

## Premium dan legacy

`CustomerPortal` memeriksa business aktif, business_type non-null, Premium yang masih berlaku, paket aktif, dan feature enabled dari `PremiumFeatures`/`PackageAccess` existing. Flag `customer_portal`, `customer_chat`, `customer_booking`, `customer_vouchers` tersedia pada pengaturan fitur paket; loyalty memakai `customer_loyalty` existing.

- Free/expired/legacy tidak muncul sebagai portal aktif.
- Portal expired memberi pesan “Toko sedang tidak menerima layanan online.”
- Chat lama read-only saat Premium berakhir; histori dan loyalty tetap tersimpan.
- Customer booking membutuhkan juga modul `booking`; voucher membutuhkan `promotions`.
- Sidebar legacy tetap menggunakan perilaku sebelumnya. Menu Order Customer/Chat Customer hanya untuk tenant yang memenuhi feature dan permission.
- Super Admin melihat statistik akun, toko, relasi, percakapan tanpa isi chat. Service chat menolak Super Admin secara eksplisit.

## Integrasi operasional

**Retail/minimarket/fashion/F&B/lainnya:** katalog, detail produk, keranjang, voucher, dan order ambil/bayar di toko. Harga berasal dari database; stok dikunci, alokasi outlet dilindungi, voucher divalidasi service existing. Status awal `CASH_PENDING`, bukan paid. Staff dengan `sales.create` mengonfirmasi uang diterima atau membatalkan dan mengembalikan stok satu kali. Tidak ada integrasi gateway baru.

**Salon/barbershop/jasa/bengkel:** booking memakai jadwal dan penguncian `BookingService` existing. Layanan, staff, dan customer dibatasi business yang dituju. Bentrok jadwal ditolak; customer hanya dapat membatalkan booking sendiri yang belum dimulai. Informasi kendaraan dapat dicatat pada catatan booking; portal ini tidak membuat modul kendaraan/mekanik baru.

**Laundry:** menampilkan layanan/harga dan histori/status laundry milik customer yang terhubung. Pickup/delivery baru dan reservasi meja tidak ditampilkan karena modul terkait belum tersedia. Pengambilan laundry existing tetap digunakan oleh toko.

**QR existing:** guest tetap dapat order. Jika customer login dan portal toko aktif, customer/order dikaitkan ke akun itu, mengabaikan `customer_account_id` dari input. Tidak mengubah Midtrans ataupun DOKU. Poin mengikuti settlement existing, bukan klik checkout. Transaksi staff yang memilih pelanggan terhubung juga muncul di histori akun setelah paid; guest tidak diklaim berdasarkan telepon.

Pada tenant bertipe usaha, pencarian pelanggan guest QR mengecualikan pelanggan yang sudah terhubung ke akun portal. Nomor telepon yang sama tidak dapat digunakan guest untuk mengubah profil pelanggan tersebut atau mencampur riwayat/poinnya. Perilaku pencarian guest QR legacy tetap dipertahankan.

## Chat dan notifikasi

Permission `customer_chat.view` / `customer_chat.reply` diberikan ke role Atasan/Admin oleh migration. Staff tambahan memerlukan permission eksplisit. Seluruh percakapan staff harus cocok dengan business_id staff, customer harus cocok dengan customer_account_id. Role Super Admin tidak mempunyai akses isi chat secara default.

Pesan customer mengirim database notification kepada staff aktif yang memiliki permission view. Balasan staff mengirim database notification ke akun customer. Membaca percakapan menandai pesan dan notifikasi terkait sebagai read. Tidak mengirim email pada setiap pesan.

## File utama

- Models: `CustomerAccount`, `BusinessCustomer`, `Conversation`, `Message`.
- Services: `CustomerPortal`, `CustomerChat`, `PortalOrderService`, `PortalPurchaseLink`; reuse `BookingService`, `LoyaltyService`, `PromotionService`.
- Controllers: `CustomerAuthController`, `CustomerPortalController`, `PortalCartController`, `PortalChatController`, `PortalStaffOrderController`.
- Middleware: `CustomerAuthenticated`.
- Notifications: `CustomerPasswordReset`, `PortalMessage`.
- Views: `resources/views/portal/*`.
- Config/integrasi: `config/auth.php`, `config/premium_features.php`, `AppServiceProvider`, `CustomerCheckoutController`, `Sale`, sidebar Super Admin.
- Tests: `tests/Feature/CustomerPortalTest.php`. Ekspektasi matrix Bengkel dan menu Super Admin diperluas pada test existing tanpa menghapus assertion keamanan.

## Uji manual

1. Pilih tenant bertipe usaha dengan Premium aktif, pastikan feature portal enabled di Fitur & Paket.
2. Buka `/customer/register`, buat akun pelanggan, cari toko di `/customer/stores`.
3. Ikuti dua toko; pastikan loyalty dan riwayat masing-masing terpisah.
4. Untuk retail/F&B, masukkan produk ke keranjang lalu pesan. Refresh tidak membuat order baru. Staff buka Order Customer dan konfirmasi setelah menerima pembayaran.
5. Untuk layanan appointment, pilih layanan/staff/tanggal/jam. Coba jam bentrok; harus ditolak.
6. Kirim chat customer, masuk sebagai staff di browser terpisah, balas dari Chat Customer. Periksa unread/read dan polling.
7. Akhiri masa Premium pada tenant uji: order/booking/chat baru ditolak, histori dan chat lama tetap dapat dibaca.

Screenshot desktop/mobile: `docs/screenshots/portal-*.png`. Pemeriksaan browser memakai database SQLite fixture terpisah, bukan data toko aktual.

## Hasil verifikasi

- `php artisan optimize:clear`, `php artisan route:list`, dan `php artisan view:cache` berhasil.
- `php artisan test`: **261 test lulus, 2.366 assertion** (108,50 detik pada run terakhir).
- Test khusus Customer Portal: **17 test, 153 assertion**, mencakup auth/reset terpisah, tenant isolation, expiry, booking bentrok, idempotency order, loyalty, voucher, notifikasi/read state, CSRF, rate limit, larangan akses chat Super Admin, dan proteksi akun terdaftar dari pencocokan telepon guest QR.
- Browser desktop 1366px dan mobile 390px: direktori, login, dashboard, toko salon/booking, dan chat tidak mengalami horizontal overflow.
- Audit sebelum/sesudah migration pada 11 tabel operasional existing tidak menemukan perubahan nilai data lama. Kolom baru nullable dikecualikan dari perbandingan hash. `business_id=1` tetap memiliki `business_type=null`.
- Permission chat baru merupakan penambahan yang disengaja untuk Atasan/Admin. Tidak ada perubahan DOKU atau Midtrans; tidak ada request pembayaran atau email nyata dalam automated test.
