# Audit signature Checkout Sandbox

Algoritma dibandingkan dengan contoh PHP/Python resmi:
https://developers.doku.com/get-started-with-doku-api/signature-component/non-snap/sample-code

Canonical memakai LF, tanpa newline terakhir. Digest adalah base64 SHA256 biner
dari raw JSON; signature adalah `HMACSHA256=` ditambah base64 HMAC-SHA256 biner
menggunakan secret mentah. Tidak ditemukan perbedaan algoritma dengan contoh resmi.
Body di-encode sekali dan string yang sama dikirim melalui `withBody`.
Client ID dan secret tidak di-trim atau di-decode.

`DOKU_SIGNATURE_DEBUG=true` mengaktifkan trace hanya pada APP_ENV local/testing
dan DOKU_ENV sandbox. Default mati. Trace berisi body mentah, digest, canonical
dengan Client ID dimasking, ID/waktu/target, dan signature; tidak berisi shared key.
Aktifkan hanya untuk investigasi lokal karena body dapat berisi data pelanggan.

Test deterministik memakai credential contoh publik, payload `{}`, ID tetap,
timestamp tetap, dan target Checkout. Nilai expected dihitung secara independen
dengan Python hashlib/hmac. Test request HTTP memeriksa body dan signature yang
benar-benar dikirim serta kesesuaian trace dengan request.

## Satu percobaan Sandbox pada 25 September 2026

Order sintetis tidak disimpan dalam database lokal. Tidak ada callback yang dicoba.
Endpoint: `POST https://api-sandbox.doku.com/checkout/v1/payment`.

```text
Client-Id:[MASKED]
Request-Id:611b45c6-0ed5-442d-b685-6ffc735761ec
Request-Timestamp:2026-09-25T13:04:03Z
Request-Target:/checkout/v1/payment
Digest:D56DvoWxc32qrJC/6GWw7wb+qEYwkze5t7tFsO+koyE=
```

Signature yang dihasilkan:
`HMACSHA256=7KNAEKWYn3MM1Qjny0758WSM1P9b0FWf4LSidejc76Q=`

Hasil: connection_error, HTTP status null, tidak ada response body/error code DOKU.
Checkout URL belum diperoleh dan redirect browser belum dapat diverifikasi.
Error HTTP 400 invalid_signature dari percobaan sebelumnya belum terbukti teratasi.
Validitas pasangan Client ID/Shared Key untuk merchant Checkout Sandbox tidak
dapat dibuktikan hanya dari konfigurasi lokal; harus dicocokkan dengan dashboard
DOKU oleh pemegang credential. Credential lokal ada dan tidak memiliki spasi
awal/akhir, karakter non-ASCII, atau tanda kutip literal.

## Pemeriksaan wire request lanjutan

Satu request create payment melalui service aplikasi diperiksa dengan middleware
HTTP pada request PSR-7 aktual. Hasil:

- Client ID dan Secret Key runtime identik dengan nilai hasil parsing `.env`.
- Signature header cocok dengan HMAC canonical yang dihitung dari header, path,
  dan body PSR-7 aktual. Content-Type application/json; body 863 byte.
- Request timestamp: `2026-09-25T13:34:05Z`.
- HTTP Date DOKU: `Fri, 25 Sep 2026 13:34:06 GMT`.
- Request ID: `441025d5-2bbb-47aa-a291-b2cf0e9b63f6`.
- Order ID: `SUB-01M3CCBVJRXKF4NG5QTMYFJ2VP`.
- HTTP 400: `invalid_signature` / `Invalid Header Signature`.

Cache konfigurasi dibersihkan sebelum pemeriksaan. Tidak ada perubahan kode
payment, database, atau percobaan callback dalam pemeriksaan lanjutan ini.
Test terarah: 5 test, 37 assertion lulus. Checkout URL tetap belum diperoleh.

Pesan untuk support DOKU (tidak menyertakan secret):

> Mohon periksa autentikasi merchant Sandbox untuk POST
> https://api-sandbox.doku.com/checkout/v1/payment. Request ID
> 441025d5-2bbb-47aa-a291-b2cf0e9b63f6, waktu 25 September 2026
> 13:34:05 UTC, mendapat HTTP 400 invalid_signature / Invalid Header Signature.
> Kami memakai HMACSHA256 non-SNAP sesuai contoh dokumentasi, dengan digest dari
> raw body yang dikirim, path /checkout/v1/payment, dan timestamp UTC. Mohon
> pastikan pasangan Client ID/Shared Key pada API Keys Sandbox valid untuk
> merchant ini dan endpoint Checkout tersebut.
