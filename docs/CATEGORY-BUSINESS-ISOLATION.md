# Isolasi kategori per toko

Verifikasi: 6 Oktober 2026.

## Struktur dan audit

Tabel `categories` existing memiliki `id`, `business_id`, `name` (100 karakter), `slug`, `description`, `status`, `deleted_at`, `created_at`, dan `updated_at`. Relasi sudah tersedia: `Business::categories()` hasMany, `Category::business()` belongsTo melalui `BelongsToBusiness`, serta `Product::category()` belongsTo.

Audit database lokal sebelum perubahan: tidak ada nama kategori duplikat pada business yang sama (termasuk soft-deleted), tidak ada kategori dengan business_id null, dan tidak ada relasi produk-kategori lintas business. Sebelumnya hanya tersedia indeks unik `(business_id, slug)`; tidak ada indeks unik nama global.

Migration `2026_10_06_000001_scope_category_name_uniqueness_to_business.php` menambah `UNIQUE (business_id, name)`. Migration memeriksa ulang duplikat dan kepemilikan null sebelum melakukan DDL; jika ditemukan, proses berhenti tanpa menghapus, menggabungkan, atau mengganti nama data. Jika suatu instalasi memiliki indeks nama global, indeks itu dilepas setelah indeks composite berhasil dibuat.

Migration diterapkan pada database lokal. Hash seluruh baris/kolom kategori dan produk sebelum/sesudah identik. Tidak mengubah business_id, nama, atau produk existing.

## Alur aplikasi

- Controller create/update menggunakan FormRequest dengan `Rule::unique('categories', 'name')->where('business_id', TenantContext::id())`; update mengabaikan ID kategori hasil route binding, bukan ID dari form.
- `ResolveTenant` mengisi konteks dari business akun staff. Super Admin mempertahankan alur POS existing melalui toko yang dipilih server/session.
- `BelongsToBusiness` mengisi business_id saat create; business_id tidak fillable dan tidak disalin dari request. Trait juga menolak pemindahan business pada record existing.
- List, detail, edit, dan delete memakai scope business. Middleware tenant berjalan sebelum route binding sehingga ID kategori toko lain menghasilkan 404.
- Dropdown produk memakai scope yang sama. Validasi create/update produk mengharuskan category_id milik business aktif dan belum soft-deleted.
- Nama kategori soft-deleted tetap dicadangkan di toko tersebut sesuai indeks composite; data lama tidak dihapus. Pembuatan slug memperhitungkan slug soft-deleted agar tidak menabrak indeks existing.
- Portal customer mempertahankan scope toko dari slug URL melalui `CustomerPortal::within`; kategori dan produk dipilih berdasarkan business itu. Filter category_id toko lain tidak menghasilkan produk.
- QR menentukan business dari token meja existing. Produk difilter berdasarkan business meja dan kategori dimuat melalui relasi yang memiliki scope business; business_id input diabaikan. Checkout menolak produk toko lain.

## Pengujian

`CategoryBusinessIsolationTest` menambahkan sembilan test yang mencakup nama Makanan pada dua toko, duplikat toko sama, update/self-ignore, pemalsuan business_id, list/IDOR, dropdown dan validasi produk, portal, QR/checkout, constraint database, soft delete, serta toko pilihan Super Admin.

Pengujian terarah bersama `ProductModuleTest` dan `TenantIsolationTest`: 11 test lulus, 96 assertion. `php artisan optimize:clear` dan `php artisan route:list` berhasil (235 rute).

Seluruh suite `php artisan test`: **286 test lulus, 2.631 assertion**, termasuk pengujian kategori/produk, portal, QR, dan tenant existing.
