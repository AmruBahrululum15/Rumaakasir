<form method="post" action="{{ route('customer.addresses.save',$address?->id) }}">@csrf
<label>Label alamat<select name="label" required>@foreach(['Rumah','Kantor','Lainnya'] as $label)<option @selected(old('label',$address?->label)===$label)>{{ $label }}</option>@endforeach</select></label>
<div class="address-fields">
@foreach(['recipient_name'=>'Nama penerima','phone'=>'Nomor HP','address'=>'Alamat lengkap','province'=>'Provinsi','city'=>'Kota / Kabupaten','district'=>'Kecamatan','village'=>'Kelurahan','postal_code'=>'Kode pos','notes'=>'Catatan alamat (opsional)'] as $field=>$label)
<label @if(in_array($field,['address','notes'])) class="address-field-wide" @endif>{{ $label }}<input name="{{ $field }}" value="{{ old($field,$address?->$field) }}" @required(in_array($field,['recipient_name','phone','address','province','city']))></label>
@endforeach
</div>
<details><summary>Koordinat alamat (opsional)</summary><p>Isi hanya jika diperlukan untuk lokasi alamat ini.</p><div class="grid">@foreach(['latitude'=>'Latitude','longitude'=>'Longitude'] as $field=>$label)<label>{{ $label }}<input type="number" step="any" name="{{ $field }}" min="{{ $field==='latitude'?-90:-180 }}" max="{{ $field==='latitude'?90:180 }}" value="{{ old($field,$address?->$field) }}"></label>@endforeach</div></details>
<label class="check-row"><input type="checkbox" name="is_default" value="1" @checked($address?->is_default)> Jadikan alamat utama</label><button>Simpan alamat</button>
</form>
