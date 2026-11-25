# Navigasi Super Admin

Seluruh 11 menu memakai named route, layout `layouts.app`, sidebar/topbar khusus
Super Admin, header konsisten, dan active state `request()->routeIs()`.
Blok sidebar toko, middleware tenant, permission existing, paket, dan POS tidak diubah.

## Menu

| Menu | Named route |
| --- | --- |
| Dashboard | `super-admin.dashboard` |
| Kelola Toko | `businesses.index` |
| Paket & Langganan | `super-admin.subscriptions.index` |
| Semua Pengguna | `super-admin.users.index` |
| Atasan / Pemilik Toko | `super-admin.owners.index` |
| Fitur & Paket | `packages.index` |
| Pengaturan Platform | `super-admin.platform-settings.index` |
| Aktivitas | `super-admin.activities.index` |
| Notifikasi | `super-admin.notifications.index` |
| Profil | `super-admin.profile.edit` |
| Pengaturan akun | `super-admin.settings.index` |

Route `businesses.*` dan `packages.*` dipertahankan. Tidak dibuat duplikat modul
paket/langganan. Halaman langganan memakai field Business existing dan tautan edit
toko existing; tanggal mulai tidak ditampilkan karena belum ada field tersebut.
Detail toko memakai `businesses.show`; perubahan aktif/nonaktif memakai PATCH
`businesses.status`. Aksi Kelola POS existing tetap tersedia dan tidak diubah.

## Perbaikan

- Empat placeholder (pengguna, Atasan, platform settings, aktivitas) menjadi page aktif.
- Langganan mendapat daftar khusus; sebelumnya hanya query string pada daftar toko.
- Profil, pengaturan akun, notifikasi, dan tautan dropdown tidak lagi memerlukan
  pemilihan tenant atau menuju halaman operasional toko.
- Profil menggunakan validasi/upload/controller existing, dengan tujuan redirect
  platform ketika dipanggil melalui route Super Admin. Form toko tetap menggunakan
  route semula. Password tetap menggunakan `password.update` existing.
- Notifikasi memakai relasi pengguna login dan aksi read/read-all existing.
- Daftar Atasan menggunakan relasi Spatie roles, bukan kolom role.
- Aktivitas menampilkan empty state jujur; pencatatan audit platform belum tersedia.
- Platform settings menyimpan nama aplikasi, logo, email, dan nomor support dalam
  tabel terpisah. Branding ini hanya digunakan shell Super Admin; settings toko
  dan branding login tidak diubah.

## File implementasi

- `routes/web.php`: route baru di dalam grup auth/credentials/role Super Admin existing.
- `app/Http/Controllers/SuperAdminPageController.php`: daftar global dan page akun.
- `app/Http/Controllers/PlatformSettingController.php`: konfigurasi platform.
- `app/Models/PlatformSetting.php` dan migration
  `2026_09_24_000000_create_platform_settings_table.php`: tabel baru tanpa update data lama.
- `app/Http/Controllers/BusinessController.php`: owner, detail, status toko.
- `app/Http/Controllers/ProfileController.php`: redirect profil platform.
- `resources/views/super-admin/*.blade.php`: tujuh page platform baru.
- `resources/views/businesses/{index,show}.blade.php`: daftar dan detail tenant.
- `resources/views/layouts/app.blade.php` dan
  `resources/views/partials/super-admin/navigation.blade.php`: branding dan tautan.
- `resources/views/profile/partials/update-profile-information-form.blade.php`:
  form shared dengan parameter route opsional, default toko tetap sama.
- `public/css/super-admin-shell.css`: style profil hanya pada page platform.
- `tests/Feature/SuperAdminNavigationTest.php`: 20 kasus pengujian baru.

## Verifikasi 24 September 2026

Perintah akhir berhasil: `php artisan optimize:clear`, `php artisan route:list`
(141 routes), `php artisan view:cache`, dan `php artisan test`.
Hasil **100 tests passed / 820 assertions**. Semua 80 test existing dipertahankan
(tanpa edit), termasuk suite POS, paket, login, dan tenant isolation.

Test baru memeriksa semua menu 200 dan active state tunggal, seluruh menu/aksi
platform menolak Admin/Atasan/Kasir/Gudang, direktori lintas tenant, filter Spatie,
settings platform terpisah, notifikasi milik sendiri, profil/password tanpa tenant,
serta detail/status toko tanpa menghapus pengguna atau mengganti paket.

Browser Edge headless memakai login form existing dengan fixture SQLite in-memory,
bukan database proyek. Seluruh 11 tautan diklik dan diuji pada desktop 1366px dan
mobile 390px. Tablet 768px, collapse, drawer, Escape, dropdown, badge kosong,
logout, serta sidebar/branding Atasan juga lulus. Tidak ada overflow dokumen;
tabel lebar memakai container yang dapat digeser.

Migrasi biasa hanya menambahkan `platform_settings`. Snapshot 18 tabel existing
identik segera setelah migrasi. Pemeriksaan akhir menemukan checksum tabel users
berubah dengan jumlah baris tetap enam; akun Super Admin existing mempunyai
updated_at baru selama sesi pengerjaan. Tidak dilakukan rollback atau penimpaan
akun tersebut. 17 tabel lainnya tetap identik. Test otomatis/browser terisolasi
menggunakan SQLite in-memory; tidak ada perintah penghapusan tabel/data proyek.

Screenshot menggunakan data uji:

- [Desktop](screenshots/super-admin-desktop.png)
- [Collapsed](screenshots/super-admin-collapsed.png)
- [Mobile](screenshots/super-admin-mobile.png)
- [Drawer mobile](screenshots/super-admin-mobile-menu.png)
