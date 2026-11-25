# Paket per jenis usaha

## Sumber aturan

- `config/business_modules.php`: jenis usaha, modul yang tersedia, flag modul rencana, label, dan fitur khusus Premium.
- `BusinessModules`: pemeriksaan modul, matrix perbandingan, preview registrasi/approval.
- `PackageAccess::forType()`: proyeksi paket global ditambah konfigurasi varian tipe bisnis. Proyeksi tidak disimpan ke tabel packages.
- `PackageAccess::package()`: memilih paket efektif dari business tersimpan, termasuk expiry untuk tenant bertipe usaha.

Semua profil mempertahankan core POS, pelanggan, profil, pengaturan, dan subscription. Permission pengguna tetap berlaku.

| Jenis usaha | Free / operasional existing | Tambahan Premium yang tersedia |
|---|---|---|
| Retail, minimarket | Katalog, kategori, stok, supplier, pembelian, transaksi, laporan | Kapasitas paket, export, branding, AI sesuai konfigurasi |
| Cafe, restaurant, kedai | Menu/produk, kategori, pelanggan, stok, supplier, pembelian, meja terbatas, laporan | Kapasitas termasuk meja, QR Order, dine in/take away melalui QR, export, branding, AI |
| Fashion | Operasional retail | Kapasitas, export, branding, AI |
| Bengkel | Katalog/sparepart, stok, supplier, pembelian, transaksi, laporan | Kapasitas, export, branding, AI |
| Laundry, salon, barbershop, jasa | Katalog transaksi POS, pelanggan, laporan | Core Premium, workflow laundry khusus Laundry; booking khusus Salon/Barbershop/Jasa |
| Lainnya | Core POS, katalog, kategori, pelanggan, laporan | Kapasitas, export, branding, AI |

Workflow laundry, booking, loyalty, promo, approval tunai, dan multi-outlet telah tersedia; lihat [implementasi Premium operasional](PREMIUM-CAPABILITIES.md). Varian produk, barcode sebagai modul tersendiri, stock opname, kendaraan/mekanik/service order, komisi staff, dan invoice jasa tetap disabled. Tidak ditawarkan sebagai fitur aktif atau diberi link operasional. Low-stock/dashboard existing tidak dipindahkan menjadi Premium. Ketersediaan fitur export/branding/AI mengikuti konfigurasi paket.

## Harga dan konfigurasi

Migration `2026_09_28_000002_create_package_variants.php` hanya membuat tabel baru:

- `package_id`: relasi paket Free/Premium existing.
- `business_type`: salah satu tipe dari config.
- `settings`: JSON tervalidasi berisi nama, harga bulanan/tahunan, status pengajuan/pembelian, limit, dan flag paket yang tersedia.
- Unique `(package_id, business_type)`.

Tidak ada backfill bisnis maupun penyalinan paket global. Varian yang belum dikonfigurasi mewarisi harga dan limit global. Super Admin membuka Fitur & Paket, memilih accordion jenis usaha, lalu menyimpan Free atau Premium. Accordion Global/Legacy tetap mengelola paket existing. Status nonaktif mencegah pengajuan/pembelian baru, tidak membatalkan durasi yang sudah dibayar.

Checkout memakai business milik user login dan harga varian dari server. Harga/tipe/business_id dari request tidak digunakan. Order menyimpan snapshot nama paket dan nominal existing; FK tetap paket global. Pending order yang sudah ada tetap menggunakan nominal order tersebut. Request, signature, notification, renewal, dan settlement DOKU tidak diubah. Tidak ada request DOKU nyata selama implementasi/pengujian.

## Legacy dan keamanan

`business_type = null` langsung memakai paket global dan halaman `subscriptions.legacy` (salinan halaman existing). Profil/menu/null legacy tidak diberi aturan khusus Premium yang baru. Toko Default tidak diberi business_type.

Untuk tenant bertipe usaha, QR Order/dine in/take away memerlukan Premium aktif dan flag QR paket. Premium yang berakhir dianggap Free tanpa mutasi data. Expiry tenant bertipe usaha tidak menonaktifkan toko. Premium manual tanpa tanggal akhir yang masih aktif tetap dihormati; status suspended/inactive tidak dilonggarkan.

Sidebar memakai service dan permission existing. Atasan dengan fitur belum tersedia diarahkan dari route fitur paket ke subscription untuk request GET HTML. Public QR dan API tetap menolak akses. Pemeriksaan tenant/model tetap berjalan; akses tabel tenant lain tidak dijadikan redirect upgrade. Route tetap memakai nama dan otorisasi existing.

## UI

- `subscriptions/index.blade.php` dan `public/css/subscription.css`: current plan, billing toggle, benefit relevan, matrix, pending payment, history.
- `subscriptions/legacy.blade.php`: halaman lama untuk null-type tenants.
- `packages/variants.blade.php`: konfigurasi per tipe usaha.
- Registrasi dan review menggunakan preview server dari matrix yang sama. Preview tidak memberikan Premium sebelum pembayaran.

Badge hemat hanya muncul jika 12 kali harga bulanan lebih besar daripada harga tahunan. Tidak ada pembatalan pembayaran baru atau fake success.

## Verifikasi

Test tambahan `BusinessPackageVariantTest`: seluruh 12 tipe, no fake features, pembatasan QR, cross-tenant, nominal checkout monthly/yearly dengan Http::fake, admin config, fallback legacy, expiry, status pembelian, limit, dan validasi registrasi.

Browser diuji menggunakan database SQLite terpisah: Cafe/Retail/Laundry, toggle bulanan/tahunan, preview registrasi, accordion Super Admin, lebar 320/390/768/1366 px. Screenshot tersedia di `docs/screenshots/subscription-cafe-desktop.png` dan `subscription-cafe-mobile.png`.
