# Reservasi universal — tahap Cash

Modul memakai tabel `bookings` dan layanan `business_services` existing. Semua operasi toko tetap memakai `business_id` login. Resource, jadwal karyawan, dan penerimaan Cash ditambahkan melalui migration non-destruktif `2026_10_10_000001_extend_universal_reservations`.

## Mengaktifkan toko

1. Toko memakai paket yang mengizinkan fitur `booking` dan `customer_booking`.
2. Atasan/Admin dengan `settings.edit` membuka **Reservasi** dan menambahkan layanan appointment, harga, durasi, kapasitas, dan jeda.
3. Atur hari/jam buka, tanggal libur, interval slot, konfirmasi manual/otomatis, serta ketentuan tanpa pembayaran di muka, DP, atau lunas.
4. Tambahkan resource meja/lapangan/ruangan/kursi/fasilitas. Meja POS existing bisa ditautkan tanpa disalin atau dipindahkan.
5. Jadwal karyawan bersifat opsional; jika diisi, hanya waktu kerja yang diizinkan.

Kategori layanan berupa label per layanan. Jenis usaha `jasa`/`lainnya` dapat dipakai untuk spa, klinik kecantikan, olahraga, atau studio tanpa menambah profil bisnis duplikat.

## Alur pelanggan dan toko

- Halaman publik toko → **Reservasi Sekarang** → pilih layanan/resource/karyawan/tanggal/slot → kontak → kirim.
- Guest tidak perlu login. Tautan acak pribadi disimpan hash-nya di database; tautan harus disimpan pelanggan dan tidak dibagikan.
- Status publik dipoll setiap 15 detik. Customer terdaftar juga dapat membukanya dari Reservasi Saya.
- Atasan/Admin/Kasir aktif dan approved dengan `sales.create` menerima database notification tenant terkait. Navbar dan sidebar memiliki badge; polling 15 detik memberikan toast untuk reservasi baru.
- Toko dapat konfirmasi, ubah jadwal, check-in, tandai no-show/batal, dan menyelesaikan reservasi. DP/lunas wajib dipenuhi sebelum konfirmasi.

## Cash dan POS

- Kasir mencatat Cash/DP di detail reservasi. `request_key` unik mencegah penerimaan berulang akibat submit ulang.
- **Checkout Cash & Selesaikan** tersedia setelah konfirmasi dan waktu mulai. DP yang diterima dikreditkan, hanya sisa yang ditagih, dan kembalian dihitung server-side.
- Checkout membuat satu sale POS Cash lunas dengan sumber `reservation`, menautkannya ke booking, dan menyelesaikan booking dalam transaksi database. Retry mengembalikan sale yang sama.
- Layanan reservasi tidak memiliki baris produk stok. Struk POS menampilkan layanan dari booking. Alur penjualan produk, stok, Cash biasa, QRIS, Midtrans, dan QR Order tetap terpisah.
- Untuk transaksi yang sudah dicatat sebelumnya, tersedia tautan sale existing setelah reservasi selesai; kasir wajib memastikan pembayaran yang sama tidak ditagih ulang.
- Pembayaran DOKU reservasi belum diaktifkan, sesuai keputusan pengguna **Cash dahulu**. Credential langganan Premium platform tidak dipakai untuk reservasi pelanggan.

## Keamanan dan batas tahap ini

- Business ID, harga, total, status pembayaran, kode, dan token tidak diambil dari field frontend.
- Slot memakai overlap waktu, durasi, buffer, kapasitas resource/layanan, dan jadwal karyawan. Baris business dikunci selama booking/pembayaran sehingga permintaan tenant yang sama diserialkan pada database yang mendukung row lock (MySQL/InnoDB).
- Pengujian otomatis SQLite memverifikasi konflik dan idempotensi secara berurutan; bukan simulasi concurrency MySQL multi-proses.
- Daftar memakai pagination 20; kalender maksimal 500 entri dan dapat difilter. Laporan CSV tersedia untuk pengguna modul dengan otorisasi existing.
- Reservasi batal yang sudah dibayar tidak otomatis mengembalikan uang; pengembalian Cash perlu dicatat sesuai prosedur toko.
- Statistik Super Admin menggunakan agregat tenant; akses percakapan pribadi tidak ditambahkan.

## Audit data

Setelah migration tambahan, jumlah data existing tetap: sales 21, customers 7, tables 23, bookings 0, business_services 0. Tidak ada kategori/toko/transaksi lama dihapus atau dipindahkan.
