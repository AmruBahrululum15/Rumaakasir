# Notifikasi chat customer pada navbar toko

Tanggal: 7 Oktober 2026.

- `PortalMessage` kini menyimpan type `customer_chat`, business_id, conversation_id, customer_account_id, customer_name, message_preview, message_id, dan created_at. Judul dan preview ditampilkan dengan escaping Blade.
- Pesan customer mengirim database notification kepada staff aktif/approved di toko percakapan: Atasan/Admin yang memiliki akses chat, serta Kasir dengan `customer_chat.reply`. Permission balas memberi akses baca percakapan; role/permission memakai Spatie.
- Notifikasi chat tergabung pada feed navbar existing, diurutkan terbaru bersama notifikasi lain. POST klik notifikasi memverifikasi penerima, business, dan permission sebelum redirect ke percakapan.
- Menu Chat Customer menampilkan unread pesan pada toko. Lonceng menampilkan unread notifikasi pribadi penerima. Pesan yang dibaca oleh staff menggunakan read_at percakapan existing; notifikasi staff lain tetap pribadi dan tidak ikut ditandai dibaca.
- Membuka percakapan menandai pesan yang dimuat serta notifikasi chat terkait sebagai dibaca. Filter membatasi conversation, business, jenis chat, dan message_id; notifikasi percakapan lain maupun order tetap utuh. Notifikasi lama tanpa metadata baru tetap didukung dan dicek kepemilikan percakapannya.
- Balasan staff mengirim notifikasi hanya ke customer pemilik percakapan. Floating chat menggunakan total unread dari server untuk seluruh percakapan, bukan hanya halaman pertama.
- Tidak ada Reverb/Echo/WebSocket terpasang. Polling existing 15 detik dipakai kembali dan berhenti saat tab tersembunyi. Rate limiter chat existing tetap 20 kiriman/menit per aktor/IP. Notifikasi tetap per pesan, tanpa grouping database atau penghapusan notifikasi lama.
- Tidak ada migration, perubahan DOKU, atau perubahan pembayaran/POS pada task ini.

## Verifikasi

`CustomerChatNotificationTest`: 5 test, 68 assertion lulus. Mencakup target role/permission, spoofed business/customer input, preview yang aman, unread navbar/menu, klik conversation, read spesifik conversation, isolasi legacy notification, permission dicabut, reply customer, serta unread di luar halaman pertama.

Pengujian portal/order existing terarah: 33 test, 342 assertion lulus. `php artisan optimize:clear` dan pemeriksaan sintaks kedua skrip polling berhasil.

Seluruh suite `php artisan test`: **301 test lulus, 2.823 assertion**, termasuk notifikasi order, pembayaran, portal, dan isolasi tenant existing.
