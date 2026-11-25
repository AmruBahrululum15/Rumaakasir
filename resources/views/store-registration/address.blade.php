<div class="registration-address" id="registration-address" data-endpoint="{{ route('store-registration.regions') }}" data-old-mode="{{ old('address_mode', '') }}">
    <div class="region-heading"><h3>Alamat Toko</h3><button type="button" id="region-mode-toggle" hidden>Gunakan input manual</button></div>
    <p id="region-feedback" role="status" aria-live="polite">Lengkapi wilayah dan alamat lengkap toko Anda.</p>
    <input type="hidden" name="address_mode" id="address_mode" value="manual">
    <div class="registration-grid">
        @foreach(['province'=>'Provinsi', 'city'=>'Kota / Kabupaten', 'district'=>'Kecamatan', 'village'=>'Kelurahan / Desa'] as $field=>$label)
            <div class="form-group">
                <label for="{{ $field }}">{{ $label }}{{ in_array($field, ['province','city']) ? ' *' : '' }}</label>
                <input class="form-control" id="{{ $field }}" name="{{ $field }}" value="{{ old($field) }}" maxlength="255" placeholder="Ketik {{ strtolower($label) }}" autocomplete="off" @required(in_array($field, ['province','city'])) aria-describedby="region-feedback">
                <input type="hidden" name="{{ $field }}_id" id="{{ $field }}_id" value="{{ old($field.'_id') }}">
                <datalist id="{{ $field }}-options"></datalist>
                @error($field.'_id')<div class="field-error">{{ $message }}</div>@enderror
                @error($field)<div class="field-error">{{ $message }}</div>@enderror
            </div>
        @endforeach
        <div class="form-group"><label for="postal_code">Kode Pos</label><input class="form-control" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" inputmode="numeric" maxlength="5" pattern="[0-9]{5}" placeholder="5 digit kode pos" list="postal-options"><datalist id="postal-options"></datalist><small id="postal-feedback">Isi kode pos jika diketahui.</small>@error('postal_code')<div class="field-error">{{ $message }}</div>@enderror</div>
        <div class="form-group"><label for="address">Alamat Lengkap *</label><textarea class="form-control" id="address" name="address" required maxlength="2000" placeholder="Nama jalan, nomor bangunan, RT/RW, dan detail alamat lainnya">{{ old('address') }}</textarea>@error('address')<div class="field-error">{{ $message }}</div>@enderror</div>
    </div>
    @error('address_mode')<div class="field-error">{{ $message }}</div>@enderror
    <noscript><p>JavaScript tidak aktif. Isi wilayah secara manual; alamat akan diperiksa saat pengajuan ditinjau.</p></noscript>
</div>
<style>
.registration-address{grid-column:1/-1;border-top:1px solid #e8ebef;margin:8px 0 20px;padding-top:20px;min-width:0}
.region-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.registration-address .region-heading h3{margin:0}
.region-heading button{padding:8px 12px;border:1px solid #f4d6bd;border-radius:8px;background:#fff6ee;color:#a84a12;font-size:12px;cursor:pointer;min-height:40px}
.registration-address p,.registration-address small{font-size:12px;line-height:1.6;color:#697589}
.registration-address input:disabled{background:#f2f4f7;color:#7b8795}
.registration-address [hidden]{display:none!important}
</style>
<script src="{{ asset('js/registration-address.js') }}" defer></script>
