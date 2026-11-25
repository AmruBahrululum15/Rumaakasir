# Profil modul jenis usaha

Sumber pemetaan: `config/business_modules.php`. `BusinessModules` menentukan modul berdasarkan tipe bisnis, lalu `PackageAccess` menerapkan fitur paket existing. Permission role tetap berlaku.

Semua profil mempertahankan Dashboard, POS/transaksi, pelanggan, pengaturan, profil, dan langganan. Produk/kategori/stok existing dipertahankan sebagai katalog transaksi untuk profil layanan sampai modul khusus tersedia.

| Jenis usaha | Modul tambahan existing | Flag persiapan (tanpa menu baru) |
|---|---|---|
| retail, minimarket | Produk, kategori, stok, supplier, pembelian, laporan | Barcode |
| cafe, restaurant, kedai | Produk/menu, kategori, stok, supplier, pembelian, meja, QR, dine in/take away, laporan | — |
| fashion | Modul retail | Varian, ukuran, warna |
| laundry | Katalog POS, laporan | Layanan, berat, status, pengambilan |
| bengkel | Modul retail | Kendaraan, mekanik, service order |
| salon, barbershop | Katalog POS, laporan | Layanan, staff, booking |
| jasa | Katalog POS, laporan | Layanan, invoice jasa |
| lainnya | Produk, kategori, stok, laporan | — |

## Registrasi dan approval

Form menyimpan kode tipe usaha terpusat. Approval menyalin nilai dari pengajuan tersimpan, bukan request approval. Pilihan lama Elektronik/Apotek dinormalisasi ke retail saat approval; data pengajuan lama tidak ditulis ulang. Premium requested tetap mengikuti approval/payment existing.

## Keamanan dan kompatibilitas

- `ResolveTenant` memeriksa modul route setelah menentukan tenant dari autentikasi/session existing.
- `PackageAccess` menggabungkan profil bisnis dengan paket; public QR tetap menggunakan business dari tabel/QR token melalui middleware existing.
- Sidebar memakai directive `businessModule` di dalam permission existing. Tautan Supplier dashboard mengikuti profil yang sama.
- `business_type` tidak ditambahkan ke mass-assignment Business. Tidak ada endpoint pengubahan profil oleh tenant.
- Migration hanya menambahkan kolom nullable. Nilai null mempertahankan profil legacy, termasuk Toko Default. Tidak ada backfill atau penghapusan data.
- Modul persiapan tidak membuat route/menu operasional. Jika kelak diimplementasikan, tambahkan pemetaan route pada config dan pemeriksaan paket yang relevan.

## Verifikasi

`BusinessModuleProfileTest` mencakup penyimpanan/approval tipe, sidebar dan route Retail/Cafe, gabungan paket, public QR, manipulasi tenant, legacy/default, core profiles, dan validasi form.

Pemeriksaan browser form: 1366, 768, 390, dan 320 px; pilihan Cafe/Retail memperbarui preview tanpa submit atau email nyata.
