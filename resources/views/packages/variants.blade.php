<p class="text-muted">Konfigurasi per jenis usaha hanya berlaku untuk toko bertipe usaha. Jika belum disimpan, harga dan limit mengikuti paket global. QR Order khusus Premium untuk toko bertipe usaha. Status pembelian tidak mencabut masa aktif yang sudah dibayar.</p>
@foreach($types as $typeCode => $type)
<details class="card"><summary class="card-header" style="cursor:pointer"><strong>{{ $type['label'] }}</strong><span class="text-muted ml-2">Free / Premium</span></summary>
<div class="card-body"><div class="row">
@foreach($packages as $base)
@php
    $offer = app(\App\Services\PackageAccess::class)->forType($typeCode, $base->code);
    $formKey = $typeCode.'-'.$base->id;
    $failedVariant = old('_variant') === $formKey;
    $profile = app(\App\Services\BusinessModules::class)->profile($typeCode);
@endphp
<div class="col-lg-6"><form class="border rounded p-3 mb-3" method="POST" action="{{ route('packages.update', $base) }}">
@csrf @method('PUT')<input type="hidden" name="business_type" value="{{ $typeCode }}"><input type="hidden" name="_variant" value="{{ $formKey }}">
<h2 class="h5">{{ ucfirst($base->code) }} · {{ $type['label'] }}</h2>
<label for="name-{{ $formKey }}">Nama paket</label><input id="name-{{ $formKey }}" class="form-control mb-3" name="name" required maxlength="100" value="{{ $failedVariant ? old('name') : $offer->name }}">
@if($base->code === 'premium')
@foreach(['price_monthly'=>'Harga bulanan (Rp)', 'price_yearly'=>'Harga tahunan (Rp)'] as $key=>$label)
<label for="{{ $key }}-{{ $formKey }}">{{ $label }}</label><input id="{{ $key }}-{{ $formKey }}" type="number" min="1" max="999999999999" class="form-control mb-3" name="{{ $key }}" required value="{{ $failedVariant ? old($key) : $offer->$key }}">
@endforeach
@endif
<input type="hidden" name="is_active" value="0"><label><input type="checkbox" name="is_active" value="1" @checked($failedVariant ? old('is_active') : $offer->is_active)> Tersedia untuk pengajuan/pembelian baru</label>
@foreach(['max_products'=>'Batas produk', 'max_users'=>'Batas pengguna', 'max_tables'=>'Batas meja'] as $key=>$label)
@if($key !== 'max_tables' || in_array('tables', $profile))
<label class="d-block mt-2" for="{{ $key }}-{{ $formKey }}">{{ $label }}</label><input id="{{ $key }}-{{ $formKey }}" class="form-control mb-3" type="number" min="1" max="1000000" name="{{ $key }}" placeholder="Unlimited" value="{{ $failedVariant ? old($key) : $offer->$key }}">
@else<input type="hidden" name="{{ $key }}" value="{{ $offer->$key }}">@endif
@endforeach
@foreach(config('business_modules.feature_labels') as $key=>$label)
<input type="hidden" name="{{ $key }}" value="{{ $offer->$key ? 1 : 0 }}">
@endforeach
@if($base->code === 'premium')
<details class="mt-3 mb-3"><summary>Atur status fitur Premium</summary><p class="small text-muted mt-2">Available hanya untuk modul yang sudah diimplementasikan. AI memerlukan konfigurasi provider. Hanya modul dengan implementasi lengkap yang dapat diaktifkan.</p>
@foreach(collect(app(\App\Services\PremiumFeatures::class)->catalog($typeCode))->where('implemented',true)->groupBy('group', true) as $group=>$catalog)
<h3 class="h6 mt-3">{{ $group }}</h3>
@foreach($catalog as $featureKey=>$definition)
@if(!isset($definition['inherits']))
<div class="form-group"><label for="feature-{{ $formKey }}-{{ $featureKey }}">{{ $definition['label'] }} @if(!$definition['implemented'])<small class="text-muted">(belum diimplementasikan)</small>@endif</label><select id="feature-{{ $formKey }}-{{ $featureKey }}" class="form-control" name="feature_statuses[{{ $featureKey }}]">
@foreach(['available'=>'Tersedia sekarang','disabled'=>'Nonaktif'] as $state=>$stateLabel)
@if($state !== 'available' || $definition['implemented'])<option value="{{ $state }}" @selected(($failedVariant ? old('feature_statuses.'.$featureKey, $definition['status']) : $definition['status']) === $state)>{{ $stateLabel }}</option>@endif
@endforeach</select></div>
@endif
@endforeach
@endforeach
</details>
@else<p class="small text-muted">Fitur Core POS tetap tersedia. Fitur Premium tidak diaktifkan pada paket Free.</p>@endif
<button class="btn btn-primary mt-2">Simpan {{ ucfirst($base->code) }} {{ $type['label'] }}</button>
</form></div>
@endforeach
</div></div></details>
@endforeach
