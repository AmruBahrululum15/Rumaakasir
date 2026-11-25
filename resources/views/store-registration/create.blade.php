@extends('layouts.guest')
@section('title', 'Daftarkan Toko')
@section('content')
<style>
.auth-shell{display:block}.auth-brand-panel,.auth-card-heading{display:none}.auth-login-panel{min-height:100vh;padding:36px 18px}.auth-card{width:min(100%,1000px)}
.registration-head{display:flex;justify-content:space-between;align-items:start;gap:20px;margin-bottom:26px}.registration-head a{color:var(--primary-dark)}
.registration-steps{display:flex;gap:8px;padding:0;list-style:none;margin:25px 0}.registration-steps li{flex:1;border-top:3px solid #e6e8ed;padding-top:12px;font-size:13px;color:var(--muted)}.registration-steps li.current{border-color:var(--primary);color:var(--primary-dark);font-weight:700}
.registration-grid{display:grid;grid-template-columns:1fr 1fr;gap:0 22px}.registration-grid .form-control{padding:10px 14px}.registration-grid textarea{height:100px}.registration-section h3{font-size:18px;margin:12px 0 22px}.registration-actions{display:flex;gap:15px;justify-content:space-between;margin-top:25px}.registration-actions button{width:auto;padding:12px 25px}.registration-actions .secondary{background:#edf0f4;color:#303747}.registration-plans{display:grid;grid-template-columns:1fr 1fr;gap:20px}.registration-plan{border:1px solid #e1e4ea;border-radius:16px;padding:22px}.registration-plan:has(input:checked){border-color:var(--primary);background:#fffaf5}.registration-plan ul{padding-left:20px;line-height:1.9}.registration-review{display:grid;grid-template-columns:1fr 1fr;gap:16px;background:#f7f8fb;padding:22px;border-radius:14px}.registration-review dt{font-size:12px;color:var(--muted)}.registration-review dd{margin:5px 0;overflow-wrap:anywhere}.registration-note{padding:15px;background:#fff4e7;border-radius:12px;margin:15px 0;line-height:1.6}.registration-section[hidden]{display:none}select.form-control{appearance:auto}.registration-section legend{font-weight:700}fieldset.registration-section{border:0;padding:0;margin:0}
@media(max-width:600px){.registration-grid,.registration-plans,.registration-review{grid-template-columns:1fr}.registration-head{flex-direction:column}.registration-steps li{font-size:11px}.registration-actions button{padding:12px}.auth-card{padding:24px 18px}}
.business-types-wrap{grid-column:1/-1}.business-types{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin:18px 0}.business-type-card{position:relative;display:flex;flex-direction:column;align-items:center;gap:9px;padding:18px 8px;border:1px solid #e1e4ea;border-radius:12px;cursor:pointer;font-size:13px}.business-type-card i{font-size:22px;color:#777}.business-type-card input{position:absolute;top:9px;left:9px;accent-color:var(--primary)}.business-type-card:has(input:checked){border-color:var(--primary);background:#fff4e7;color:#a34d00}.business-type-card:has(input:checked) i{color:var(--primary)}.business-type-card:focus-within{outline:2px solid var(--primary);outline-offset:2px}.business-module-preview{border:1px solid #ece9e3;border-radius:12px;padding:18px;margin-bottom:18px}.business-module-preview ul{display:flex;flex-wrap:wrap;gap:8px;list-style:none;padding:0}.business-module-preview li{padding:5px 9px;border-radius:6px;background:#f6f7f9;font-size:12px}@media(max-width:600px){.business-types{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>
<header class="registration-head"><div><h2>Daftarkan Toko Anda</h2><p class="muted">Lengkapi informasi berikut untuk mengajukan pembukaan akun toko.</p></div><a href="{{ route('login') }}">Kembali ke Login</a></header>
@if($errors->any())<div class="alert alert-danger" role="alert"><strong>Periksa kembali pengajuan Anda.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<ol class="registration-steps" aria-label="Tahap pendaftaran"><li>1 · Informasi Toko</li><li>2 · Pemilik</li><li>3 · Paket</li><li>4 · Review</li></ol>
<form id="store-registration" method="POST" action="{{ route('store-registration.store') }}">@csrf
<input type="hidden" name="submission_token" value="{{ session('store_registration_token') }}">
<fieldset class="registration-section"><legend>Informasi Toko</legend><p class="muted">Field bertanda * wajib diisi.</p><div class="registration-grid" style="margin-top:20px">
@include('store-registration.fields', ['fields' => [
['store_name','Nama Toko',true], ['legal_name','Nama Pemilik',false],
['store_phone','Nomor Telepon Toko',true,'tel'], ['store_email','Email Toko',true,'email'],
['website','Website',false,'url'], ['social_media','Instagram / Social Media',false],
]])
@include('store-registration.address')
<div class="business-types-wrap">
<h3>Jenis Usaha *</h3><p class="muted">Pilih yang paling sesuai dengan operasional toko Anda.</p>
<div class="business-types">@foreach($types as $code => $type)
<label class="business-type-card"><input type="radio" name="business_type" value="{{ $code }}" required @checked(app(\App\Services\BusinessModules::class)->normalize(is_string(old('business_type')) ? old('business_type') : null) === $code)><i class="fas {{ $type['icon'] }}" aria-hidden="true"></i><strong>{{ $type['label'] }}</strong></label>
@endforeach</div>
<div class="business-module-preview" aria-live="polite"><strong>Fitur yang akan tersedia</strong><ul id="business-module-list"><li>Pilih jenis usaha untuk melihat modul.</li></ul><p class="muted">Fitur tertentu dapat bergantung pada paket yang dipilih. Modul khusus yang belum tersedia hanya disiapkan sebagai flag.</p></div>
</div>
<div class="form-group" id="business-other"><label for="business_type_other">Jenis Usaha Lainnya</label><input class="form-control" id="business_type_other" name="business_type_other" value="{{ old('business_type_other') }}" maxlength="255"></div>
</div></fieldset>
<fieldset class="registration-section"><legend>Data Pemilik / Atasan Utama</legend><p class="registration-note">Password dibuat melalui tautan aktivasi yang dikirim ke email Anda setelah Super Admin menyetujui pengajuan. Jangan gunakan email milik orang lain.</p><p class="registration-note">Email yang sama dapat digunakan untuk beberapa toko. Username harus berbeda untuk setiap akun.</p><div class="registration-grid">
@include('store-registration.fields', ['fields' => [
['owner_name','Nama Lengkap',true], ['owner_username','Username',true], ['owner_email','Email Pemilik',true,'email'], ['owner_phone','Nomor HP',true,'tel'], ['owner_position','Jabatan',false],
]])
</div></fieldset>
<fieldset class="registration-section"><legend>Pilih Paket</legend><p class="muted" style="margin:12px 0 20px">Harga dan fitur mengikuti konfigurasi paket platform.</p><div class="registration-plans">
@forelse($packages as $package)<article class="registration-plan">
<label><input type="radio" name="requested_plan" value="{{ $package->code }}" @checked(old('requested_plan', 'free') === $package->code) required> <strong>{{ $package->name }}</strong></label>
<ul data-plan-features="{{ $package->code }}"><li>Pilih jenis usaha untuk melihat fitur paket.</li></ul>
@if($package->code === 'premium')
<p><strong data-plan-price="monthly">Rp{{ number_format($package->price_monthly, 0, ',', '.') }}</strong> / bulan</p><p><strong data-plan-price="yearly">Rp{{ number_format($package->price_yearly, 0, ',', '.') }}</strong> / tahun</p>
<label for="billing_cycle">Periode Premium</label><select class="form-control" style="padding:10px" name="billing_cycle" id="billing_cycle"><option value="monthly" @selected(old('billing_cycle') === 'monthly')>Premium Bulanan</option><option value="yearly" @selected(old('billing_cycle') === 'yearly')>Premium Tahunan</option></select>
@endif</article>@empty<p>Paket belum tersedia. Silakan hubungi pengelola.</p>@endforelse
</div><p class="registration-note">Tidak ada pembayaran saat pendaftaran. Jika memilih Premium, setelah approval toko memakai Free sementara sampai pembayaran Premium berhasil.</p></fieldset>
<fieldset class="registration-section"><legend>Review &amp; Submit</legend><p class="muted">Pastikan data dan email sudah benar. Anda dapat kembali untuk mengedit.</p><dl class="registration-review" id="registration-review"></dl>
<label class="checkbox"><input type="checkbox" name="terms" value="1" required @checked(old('terms'))><span>Saya menyetujui <a href="{{ route('store-registration.terms') }}" target="_blank" rel="noopener">Syarat & Ketentuan dan Kebijakan Privasi</a>.</span></label>
<p class="muted">Pengajuan akan ditinjau Super Admin. Mengirim form tidak langsung mengaktifkan toko.</p></fieldset>
<div class="registration-actions"><button type="button" class="auth-button secondary" id="registration-back" hidden>Kembali</button><button type="button" class="auth-button" id="registration-next" hidden>Lanjutkan</button><button type="submit" class="auth-button" id="registration-submit">Kirim Pengajuan</button></div>
</form>
@php
    $businessTypePreviews = [];
    foreach ($types as $code => $type) {
        foreach (['free','premium'] as $planCode) $businessTypePreviews[$code][$planCode] = app(\App\Services\BusinessModules::class)->offerPreview($code, $planCode);
    }
@endphp
<script>
(()=>{const form=document.getElementById('store-registration'),steps=[...form.querySelectorAll('fieldset')],labels=[...document.querySelectorAll('.registration-steps li')],back=document.getElementById('registration-back'),next=document.getElementById('registration-next'),submit=document.getElementById('registration-submit');let index=0;form.noValidate=true;
const review=()=>{const root=document.getElementById('registration-review');root.replaceChildren();Object.entries({store_name:'Nama Toko',owner_name:'Pemilik',owner_username:'Username',owner_email:'Email Pemilik',owner_phone:'Nomor HP',store_email:'Email Toko',store_phone:'Telepon Toko',business_type:'Jenis Usaha',business_type_other:'Jenis Usaha Lainnya',address:'Alamat',province:'Provinsi',city:'Kota / Kabupaten',district:'Kecamatan',village:'Kelurahan / Desa',postal_code:'Kode Pos',requested_plan:'Paket',billing_cycle:'Periode'}).forEach(([key,label])=>{if(key==='billing_cycle'&&form.elements.requested_plan.value!=='premium')return;const value=form.elements[key]?.value;if(!value)return;const box=document.createElement('div'),dt=document.createElement('dt'),dd=document.createElement('dd');dt.textContent=label;dd.textContent=value;box.append(dt,dd);root.append(box)})};
const show=()=>{steps.forEach((s,i)=>s.hidden=i!==index);labels.forEach((l,i)=>{l.classList.toggle('current',i===index);if(i===index)l.setAttribute('aria-current','step');else l.removeAttribute('aria-current')});back.hidden=index===0;next.hidden=index===3;submit.hidden=index!==3;if(index===3)review()};
const valid=(i)=>{for(const input of steps[i].querySelectorAll('input,select,textarea'))if(!input.checkValidity()){index=i;show();input.reportValidity();return false}return true};
const typeProfiles = @json($businessTypePreviews);
const other=()=>{const selected=form.elements.business_type.value;const enabled=selected==='lainnya';document.getElementById('business-other').hidden=!enabled;form.elements.business_type_other.required=enabled;const list=document.getElementById('business-module-list');list.replaceChildren();(typeProfiles[selected]?.[form.elements.requested_plan.value]?.features||['Pilih jenis usaha untuk melihat modul.']).forEach(label=>{const li=document.createElement('li');li.textContent=label;list.append(li)});for(const code of ['free','premium']){const offer=typeProfiles[selected]?.[code];if(!offer)continue;const target=form.querySelector('[data-plan-features='+code+']');target.replaceChildren();offer.features.forEach(label=>{const li=document.createElement('li');li.textContent=label;target.append(li)});const radio=form.querySelector('[name=requested_plan][value='+code+']');if(radio){radio.disabled=!offer.active;radio.closest('article').setAttribute('data-available',String(offer.active));if(!offer.active){const li=document.createElement('li');li.textContent='Paket sedang tidak tersedia untuk pengajuan baru.';target.append(li)}}if(code==='premium'){for(const cycle of ['monthly','yearly'])form.querySelector('[data-plan-price='+cycle+']').textContent='Rp'+new Intl.NumberFormat('id-ID').format(offer[cycle])}}};form.querySelectorAll('[name=business_type]').forEach(input=>input.addEventListener('change',other));form.querySelectorAll('[name=requested_plan]').forEach(input=>input.addEventListener('change',other));other();
back.addEventListener('click',()=>{index--;show()});next.addEventListener('click',()=>{if(valid(index)){index++;show()}});form.addEventListener('submit',e=>{for(let i=0;i<steps.length;i++)if(!valid(i)){e.preventDefault();return}submit.disabled=true;submit.textContent='Mengirim pengajuan...'});show();})();
</script>
@endsection
