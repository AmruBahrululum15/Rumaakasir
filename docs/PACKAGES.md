# Paket Free / Premium

Aturan paket ditambahkan pada SaaS existing. Tidak ada perubahan autentikasi,
tenant, pembayaran Midtrans, atau data operasional existing.

## Default

| Aturan | Free | Premium |
| --- | --- | --- |
| Produk | 100 | Unlimited |
| Pengguna | 2 | Unlimited |
| Meja | 5 | Unlimited |
| Export Excel / PDF | Tidak | Ya |
| Custom Branding | Tidak | Ya |
| AI Agent | Tidak | Ya |
| QR Order | Ya | Ya |

POS, produk, kategori, pelanggan, supplier, dan pembelian tetap menggunakan
fitur serta permission existing. Paket Premium tidak menggantikan permission Spatie.

Super Admin membuka **Fitur & Paket** (`/super-admin/packages`) untuk mengubah nama,
batas, dan seluruh feature flag. Kosong pada batas berarti unlimited. Kode internal
`free` / `premium` tetap stabil meskipun nama paket diganti. Mengubah paket toko
tetap melalui **Manajemen Toko → Edit**, menggunakan `subscription_plan` existing.

## Cara pemeriksaan

`PackageAccess` menjadi sumber pemeriksaan terpusat. Paket disimpan pada tabel
`packages`; keanggotaan dibaca dari business yang ditetapkan server. Tidak ada
nilai `business_id`, `package`, atau kuota dari request yang dipercaya untuk
memberikan akses. Perubahan paket/flag berlaku pada request berikutnya tanpa login
ulang atau pembersihan cache.

`EnforceTenantCapacity` yang sudah ada diperluas untuk paket dan meja. Baris toko
dikunci sampai operasi selesai, termasuk generate meja sekaligus dan approval
pengguna. Konfigurasi paket memperoleh shared lock selama transaksi penambahan.
Kuota per-toko existing `product_limit` / `user_limit` tetap menjadi batas tambahan;
kuota tersebut dapat memperketat, tetapi tidak melampaui, batas paket. Nilai kosong
pada batas per toko kini berarti mengikuti paket; nilai kosong pada paket berarti
unlimited.

Produk dan meja dihitung termasuk yang nonaktif. Kuota pengguna paket menghitung
akun **approved**, termasuk Atasan dan akun approved yang nonaktif. Registrasi
publik existing tetap menjadi permohonan pending yang tidak boleh login; approval
wajib lolos pemeriksaan kuota. Batas `user_limit` eksplisit yang sudah ada tetap
menghitung semua record pengguna seperti sebelumnya. Pending tidak dihitung dua
kali pada saat approval.

Downgrade tidak menghapus atau menonaktifkan record existing. Pengguna tetap dapat
melihat data dengan permission existing; penambahan ditolak jika melampaui batas.
Generate meja menggunakan kembali meja nonaktif sebelum menambah record baru.

## Feature gates

- Excel/PDF: middleware pada route export existing, setelah autentikasi/tenant dan
  permission. Menu tetap tampil dengan badge Premium ketika tidak tersedia.
- AI: halaman, chat, dan reset dilindungi. Floating AI hanya tampil bila diizinkan.
- Branding: perubahan nama/identitas, header/footer struk, dan unggahan logo/favicon
  ditolak pada Free. Informasi kontak tetap dapat diedit. Branding existing tetap
  disimpan dan ditampilkan setelah downgrade; field branding pada form dikunci.
- QR: pembuatan order publik, menu, checkout, serta tampilan/cetak QR dilindungi.
  Request publik menggunakan toko pemilik meja dari parameter route, bukan tenant
  yang dikirim melalui body/query. Status pesanan existing tetap mengikuti alur
  sebelumnya agar hasil pembayaran tidak berubah.

Penolakan kuota berupa validasi 422 untuk JSON atau redirect dengan pesan form.
Fitur terkunci merespons 403 dengan pesan “Fitur ini tersedia pada paket Premium.”
Status langganan tetap memakai kolom existing: `active`, `inactive` (legacy),
`expired`, dan `suspended`, beserta `subscription_ends_at`. Tidak ditambahkan trial
karena sebelumnya belum tersedia. Tidak ada payment gateway subscription baru.

## Migrasi dan pengujian

Migrasi `2026_09_23_000001_create_packages_table` hanya membuat tabel paket dan dua
konfigurasi awal. Tidak memperbarui business/user/transaksi existing. Pengujian
memakai trait SQLite in-memory yang sudah ada, tanpa perintah migrasi destruktif.

```powershell
php artisan migrate --force
php artisan optimize:clear
php artisan test
```

Test tambahan: `tests/Feature/PackageFeatureLimitTest.php`, meliputi seluruh limit,
produk ke-101, generate meja, approval, export file nyata, branding/AI/QR, perubahan
paket, downgrade 500 produk, pemalsuan tenant, pengelolaan paket, dan langganan tidak
aktif. Semua test existing dipertahankan tanpa perubahan.

Hasil akhir: `php artisan optimize:clear` berhasil dan `php artisan test` menghasilkan
**80 test lulus, 638 assertion** (55 existing + 25 tambahan). Migrasi paket telah
diterapkan; hash seluruh baris dan kolom existing pada 18 tabel sebelum/sesudah
migrasi identik.

## File pada pekerjaan paket ini

Baru:

- `app/Models/Package.php`
- `app/Services/PackageAccess.php`
- `app/Http/Controllers/PackageController.php`
- `app/Http/Middleware/EnforcePackageFeature.php`
- `app/Http/Middleware/EnforceQrPackage.php`
- `app/Http/Middleware/EnforceBrandingPackage.php`
- `database/migrations/2026_09_23_000001_create_packages_table.php`
- `resources/views/packages/index.blade.php`
- `tests/Feature/PackageFeatureLimitTest.php`
- `docs/PACKAGES.md`

Diperluas:

- `app/Http/Middleware/EnforceTenantCapacity.php`
- `app/Http/Controllers/BusinessController.php`
- `bootstrap/app.php`
- `routes/web.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/partials/ai-floating.blade.php`
- `resources/views/settings/index.blade.php`
- `resources/views/businesses/form.blade.php`
- `resources/views/businesses/index.blade.php`
- `resources/views/users/approvals.blade.php`
- `docs/SAAS-MULTITENANT.md`
