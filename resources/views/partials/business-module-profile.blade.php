@php
    $moduleService = app(\App\Services\BusinessModules::class);
    $modulePreview = $moduleService->preview($businessType);
    $plannedModules = $moduleService->planned($businessType);
@endphp
<div class="card"><div class="card-body"><h3 class="h5">{{ $moduleBusiness ? 'Modul Aktif' : 'Modul yang akan tersedia' }}</h3>
<p class="text-muted">Jenis usaha: {{ $moduleService->label($businessType) }}. Fitur tertentu bergantung pada paket dan permission pengguna.</p>
@if(!$moduleBusiness && isset($requestedPlan))
@php($offerPreview = app(\App\Services\BusinessModules::class)->offerPreview($businessType, $requestedPlan))
<p>Paket diminta: {{ $offerPreview['name'] }}. @if($requestedPlan === 'premium')Toko menggunakan Free sampai pembayaran Premium berhasil.@endif</p>
<ul>@foreach($offerPreview['features'] as $featureLabel)<li>{{ $featureLabel }}</li>@endforeach</ul>
@else
<div class="d-flex flex-wrap" style="gap:8px">@foreach($modulePreview as $key => $label)
    @if(!$moduleBusiness || $moduleService->allows($moduleBusiness, $key))<span class="badge badge-light border p-2">{{ $label }}</span>@endif
@endforeach</div>
@endif
@if($plannedModules)<p class="small text-muted mt-3 mb-0">Flag persiapan (modul belum tersedia): {{ implode(', ', $plannedModules) }}. Belum memiliki menu operasional.</p>@endif
@if($moduleBusiness)
    @php($modulePackage = app(\App\Services\PackageAccess::class)->package($moduleBusiness))
    <h4 class="h6 mt-4">Paket {{ $modulePackage->name }} & Fitur Paket</h4>
    <ul class="mb-0">@foreach(['export_excel'=>'Export Excel','export_pdf'=>'Export PDF','custom_branding'=>'Custom Branding','ai_agent'=>'AI Agent','qr_order'=>'QR Order'] as $feature=>$label)<li>{{ $label }}: {{ app(\App\Services\PackageAccess::class)->allows($feature, $moduleBusiness) ? 'Aktif' : 'Tidak aktif' }}</li>@endforeach</ul>
@endif
</div></div>
