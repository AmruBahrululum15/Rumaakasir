<!doctype html>
<html lang="id"><body style="margin:0;background:#f8f9fa;padding:28px;font-family:Arial,sans-serif;color:#252525">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td align="center">
<table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px;background:#fff;border-radius:14px;overflow:hidden">
<tr><td style="background:#f57c00;padding:25px;text-align:center;color:#fff">
@if($businessLogo)<img src="{{ asset($businessLogo) }}" alt="Logo {{ $businessName }}" style="max-height:52px;max-width:130px;object-fit:contain;background:#fff;border-radius:8px;padding:4px"><br>@endif
<strong>{{ $businessName }}</strong></td></tr>
<tr><td style="padding:30px"><h2 style="margin-top:0">Atur ulang password</h2>
<p>Anda menerima email ini karena ada permintaan untuk mengatur ulang password akun Anda.</p>
<p><strong>Username:</strong> {{ $username }}<br><strong>Nama Toko:</strong> {{ $storeName }}</p>
<p style="margin:28px 0"><a href="{{ $url }}" style="background:#f57c00;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:bold">Reset Password</a></p>
<p>Tautan ini berlaku selama {{ $expiry }} menit. Jika Anda tidak meminta reset password, abaikan email ini.</p></td></tr>
</table></td></tr></table></body></html>
