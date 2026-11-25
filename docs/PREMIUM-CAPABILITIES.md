# Premium operasional Rumaakasir

Implementasi mengikuti urutan dashboard ? reports ? CSV ? loyalty ? promo ? approval ? laundry ? booking ? outlet. Setiap tahap diuji sebelum status implementasinya diaktifkan.

## Modul dan route

| Modul | Named route utama | Backend |
|---|---|---|
| Advanced Dashboard | `premium.dashboard` | `PremiumAnalytics` |
| Advanced Reports | `premium.reports` | `PremiumAnalytics` |
| CSV penjualan/pembelian/stok | `premium.csv` | `PremiumAnalyticsController` |
| Loyalty | `loyalty.index`, `loyalty.update`, `loyalty.show` | `LoyaltyService`, `LoyaltyController` |
| Promo / voucher | `promotions.index/store/edit/update/history` | `PromotionService`, `PromotionController` |
| Preview diskon checkout | `premium.checkout.quote` | `PremiumCheckoutQuoteController` |
| Approval refund / void | `sale-approvals.index/store/review` | `SaleApprovalService`, `SaleApprovalController` |
| Laundry | `laundry.index/store/show/status/pay`, `laundry.services.store` | `LaundryService`, `LaundryController` |
| Booking | `bookings.index/store/edit/update/status`, `bookings.services.store` | `BookingService`, `BookingController` |
| Outlet | `outlets.index/store/update/select/assign` | `OutletContext`, `OutletController` |
| Stok / transfer outlet | `outlets.inventory.index/store` | `OutletInventory`, `OutletInventoryController` |

Semua route baru berada di `/premium/*`, menggunakan auth, tenant, first-login middleware, pemeriksaan permission dan fitur server. `premium.checkout.quote` memakai pemeriksaan salah satu fitur loyalty/promo di controller, bukan hanya sidebar.

## Migration dan model

Semua migration baru bersifat tambahan, tidak melakukan backfill atau mengubah kolom/data operasional lama. Migration dijalankan dengan path spesifik. `down()` sengaja tidak menghapus jurnal keuangan/operasional.

| Migration tanggal 2026-09-28 | Tabel / perubahan | Model baru |
|---|---|---|
| `000003_create_customer_loyalty_points` | ledger poin; kolom diskon/poin pada sales | `CustomerLoyaltyPoint` |
| `000004_create_promotions` | promotions, promotion_usages; diskon sales | `Promotion`, `PromotionUsage` |
| `000005_create_sale_approvals` | sale_approvals, business_audit_entries | `SaleApproval`, `BusinessAuditEntry` |
| `000006_create_laundry_workflow` | business_services, laundry_orders, laundry_status_histories, laundry_payments | `BusinessService`, `LaundryOrder`, `LaundryStatusHistory`, `LaundryPayment` |
| `000007_create_bookings` | bookings | `Booking` |
| `000008_create_outlets` | outlets; nullable outlet_id pada users/sales/purchases | `Outlet` |
| `000009_create_outlet_inventory` | outlet_stocks, outlet_stock_transfers, outlet_stock_movements | `OutletStock`, `OutletStockTransfer`, `OutletStockMovement` |

Model existing yang diperluas: Sale, SaleDetail, Purchase, PurchaseDetail, Product. Transaksi outlet tidak dapat dipindahkan ke outlet lain. Tidak ada perubahan business_id existing.

## Mapping Premium

`config/premium_features.php` dan `PremiumFeatures` menjadi sumber tunggal readiness, business type, status, dan navigasi. Hanya `available` / `disabled`; fitur belum diimplementasikan tidak dipromosikan kepada pengguna dan tidak dapat diaktifkan Super Admin.

- Semua 12 tipe terdaftar: dashboard, reports, CSV, loyalty, promo/voucher, approval tunai, multi-outlet/transfer, ditambah export/branding existing sesuai konfigurasi paket.
- Cafe/restoran/kedai: tetap memiliki QR Order, dine-in/take-away existing.
- Laundry: layanan kiloan/satuan, status, timeline, estimasi, pengambilan. Delivery tidak ditandai tersedia.
- Salon/barbershop/jasa: booking dengan staff opsional, durasi layanan dan deteksi konflik.
- Bengkel: fitur core tersebut; service order/kendaraan/mekanik bukan bagian implementasi sembilan modul ini dan tetap disabled.
- Fitur lain yang belum dibuat (misalnya stock opname, WhatsApp, API publik) tetap disabled dan tidak ditampilkan sebagai benefit aktif.

Free/expired tidak mendapat akses fitur baru. PackageSnapshot tetap memakai cache per request dan invalidasi saat paket/variant berubah. Pengaturan status induk berlaku untuk fitur turunannya (voucher/promo terjadwal, subfitur laundry, transfer outlet).

## Permission

Permission baru: `sales.approve-refunds`, diberikan kepada role Atasan/Admin yang sudah ada melalui migration; bootstrap Atasan juga menyertakannya. Tidak membuat role baru.

- Loyalty: `customers.view`; pengaturan hanya Atasan + `settings.edit`.
- Promo: Atasan/Admin + `settings.edit`.
- Pengajuan refund/void: Atasan/Admin/Kasir + `sales.create`; review juga `sales.approve-refunds` dan role Atasan/Admin.
- Laundry/booking: Atasan/Admin/Kasir + `sales.create`; konfigurasi layanan juga `settings.edit`.
- Outlet/transfer/penugasan: Atasan + `settings.edit`.
- Dashboard/reports/CSV: permission laporan existing.

## Perhitungan dan konsistensi

- Ringkasan/dashboard/laporan tenant baru mengecualikan REFUNDED/VOIDED dari total; histori tetap tersedia. Ringkasan legacy tidak diubah.
- Analitik POS hanya menghitung penjualan PAID; produk/kategori memakai subtotal bruto sebelum diskon. Tidak menampilkan laba karena biaya historis penjualan belum tersedia.
- Stok masuk/keluar laporan berasal dari detail pembelian/penjualan dibayar. Ini bukan rekonstruksi mutasi manual historis; batasan disebutkan di halaman. Stok saat ini mengikuti outlet yang dipilih, atau stok total jika tidak memilih outlet.
- CSV memakai scope tenant/outlet dan tanggal, streaming, UTF-8 BOM, dan pencegahan formula spreadsheet.
- Loyalty harus diaktifkan pemilik terlebih dahulu. Poin berasal dari transaksi POS dibayar, berdasarkan nominal setelah diskon. Ledger unik, lock customer, dan transaksi DB mencegah double award/redeem. Redeem checkout POS tunai wajib memilih ID customer tenant sendiri. Transaksi berledger tidak bisa diedit/dihapus sembarangan.
- Promo: persentase/nominal, periode, batas diskon, minimum, kuota total/per customer. Satu promo terbaik otomatis atau satu voucher; kuota mencakup pembayaran pending. Harga preview dihitung server tanpa membuat transaksi; checkout memvalidasi ulang. POS/QR memakai perhitungan promo server; voucher dimasukkan di POS.
- Refund tunai penuh memerlukan konfirmasi uang dikembalikan; void hanya tunai belum dibayar. Review memakai lock, status final tidak dapat disetujui dua kali, stok/poin dibalik dengan audit dan notifikasi database. Refund gateway tidak dibuat seolah berhasil: request online ditolak dengan petunjuk proses penyedia pembayaran. Tidak mengubah integrasi gateway.
- Laundry: snapshot harga layanan, berat desimal atau jumlah bulat, timeline berurutan, pembayaran tunai/cicilan dengan request key unik, wajib lunas sebelum diambil. Pembayaran laundry dicatat dalam ledger laundry terpisah; loyalty/promo di tahap ini terintegrasi checkout POS.
- Booking: lock business menserialisasi perubahan jadwal; tidak boleh overlap staff. Tanpa staff memakai satu slot bersama per layanan. Jadwal akhir dihitung dari durasi server. Cancel membuka slot, status terminal tidak dapat diedit kembali.
- Outlet: business tetap tenant. Staff assigned memakai outlet dari akun, tidak dari form/session pilihan owner. Owner dapat memilih outlet atau melihat semua. Scope Eloquent, query agregat dan CSV mengikuti outlet. Data lama tetap nullable. Tidak ada pemindahan otomatis.
- Stok outlet: alokasi terpisah; stok pusat = stok total - alokasi. Penjualan/pembelian outlet memperbarui alokasi dan jurnal. Transfer atomik/idempotent tidak mengubah stok total. Stok pusat tidak boleh memakai alokasi outlet. Refund mengembalikan alokasi satu kali. Transaksi outlet berledger tidak dapat diedit/dihapus; refund/void digunakan untuk koreksi penjualan.

## Legacy dan keamanan

Business dengan business_type null tidak mendapat fitur/menu baru, mapping, alokasi, atau perubahan limit. Default business_id=1 tidak di-backfill. Route binding, validasi relasi dan query selalu memeriksa tenant; request business_id/harga/outlet_id checkout tidak dipercaya. Tidak menjalankan fresh/refresh/reset/wipe/truncate atau penghapusan data.

## Verifikasi

Test khusus: PremiumCapabilitiesTest, LoyaltyModuleTest, PromotionModuleTest, SaleApprovalModuleTest, LaundryModuleTest, BookingModuleTest, OutletModuleTest. Ditambah seluruh test existing, termasuk POS/QR, DOKU, auth dan tenant isolation. Mail/notification/HTTP eksternal difake. Database PHPUnit wajib SQLite in-memory.

Browser memakai SQLite fixture terpisah, bukan database operasional. Halaman modul dicek pada 1366/768/390/320px. Preview checkout diverifikasi menurunkan harga Rp18.000 menjadi Rp16.200 dan mengaktifkan tombol bayar pada nominal setelah diskon. Screenshot tersedia di `docs/screenshots/modules-*.png`.

Hasil test final dan audit tersimpan pada laporan akhir implementasi. Audit original-field hash tidak menemukan perubahan data operasional; remember_token satu akun berubah selama sesi, tidak dikembalikan otomatis.

## Uji manual singkat

1. Super Admin ? Fitur & Paket ? jenis usaha: pastikan fitur yang ingin diuji berstatus Tersedia sekarang. Akun toko harus Premium aktif dan memiliki permission sesuai tabel.
2. Atasan ? Loyalty Customer: aktifkan, atur nilai poin; lakukan penjualan tunai, lalu pilih akun customer dan redeem pada transaksi berikutnya.
3. Promo & Voucher: buat promo periode aktif, masukkan kode di POS; periksa preview server, struk dan riwayat penggunaan.
4. Kasir ajukan refund tunai; Atasan review setelah pengembalian uang. Periksa status, stok, pembalikan poin dan audit.
5. Laundry: buat layanan/kg atau satuan, buat order, catat pembayaran dan jalankan status hingga Diambil.
6. Salon/barbershop/jasa: buat layanan booking, pilih staff/waktu; coba jadwal bertabrakan lalu cancel untuk membuka slot.
7. Multi Outlet: buat outlet, alokasikan stok dari pusat melalui transfer, tugaskan kasir, lalu buat penjualan/pembelian. Periksa saldo, ringkasan dan isolasi cabang.
8. Buka dashboard/reports dan CSV dengan rentang tanggal; data tenant lain tidak ikut muncul. Toko legacy tetap memakai jalur lama.

## Hasil akhir 28 September 2026

- `php artisan optimize:clear`: berhasil.
- `php artisan route:list`: berhasil, 191 route.
- `php artisan view:cache`: berhasil.
- `php artisan test`: **243 passed, 2204 assertions**, termasuk semua test existing.
- Baseline audit: 207 test; tambahan 36 test.
- Suite modul: PremiumCapabilities 11, Loyalty 6, Promotion 6, SaleApproval 4, Laundry 4, Booking 4, Outlet 11; seluruhnya lulus.
- Browser: 10 halaman modul pada 1366/768/390/320px, ditambah preview checkout desktop/mobile; 22 screenshot.
- Audit hash original fields: data operasional tidak berubah, business_id=1 tetap tanpa business_type. Satu `remember_token` akun berubah selama sesi audit; tidak di-restore secara otomatis.
- Database visual dan PHPUnit terpisah dari database operasional. Server visual lokal sudah dihentikan. Tidak mengirim pembayaran atau email nyata selama pengujian.
