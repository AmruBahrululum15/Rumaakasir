<form method="POST" action="{{ route($profileUpdateRoute ?? 'profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PATCH')
    <div class="row">
        <div class="col-md-4 mb-4"><div class="profile-photo-box">
            @if ($user->foto)
                <img id="photo-preview" class="profile-photo" src="{{ route('users.photo', $user->id) }}" alt="Foto profil {{ $user->name }}">
            @else
                <div id="photo-placeholder" class="profile-photo-placeholder"><i class="fas fa-user"></i></div><img id="photo-preview" class="profile-photo d-none" src="" alt="Pratinjau foto profil">
            @endif
            <label for="foto" class="btn btn-outline-warning btn-sm mt-3 mb-1"><i class="fas fa-camera mr-1"></i>Pilih Foto</label>
            <input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp" class="d-none">
            <small class="d-block text-muted">JPG, PNG, atau WEBP. Maks. 2 MB.</small>
            @error('foto')<small class="d-block text-danger mt-2">{{ $message }}</small>@enderror
        </div></div>
        <div class="col-md-8">
            <div class="form-group"><label class="profile-form-label" for="name">Nama Lengkap <span class="text-danger">*</span></label><input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autocomplete="name">@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label class="profile-form-label" for="email">Email <span class="text-danger">*</span></label><input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="email">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="row"><div class="col-md-6"><div class="form-group"><label class="profile-form-label" for="nik">NIK</label><input id="nik" name="nik" type="text" inputmode="numeric" maxlength="30" class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik', $user->nik) }}" autocomplete="off">@error('nik')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div><div class="col-md-6"><div class="form-group"><label class="profile-form-label" for="no_telepon">Nomor HP</label><input id="no_telepon" name="no_telepon" type="tel" maxlength="20" class="form-control @error('no_telepon') is-invalid @enderror" value="{{ old('no_telepon', $user->no_telepon) }}" autocomplete="tel">@error('no_telepon')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div></div>
            <div class="form-group mb-4"><label class="profile-form-label" for="alamat">Alamat</label><textarea id="alamat" name="alamat" class="form-control @error('alamat') is-invalid @enderror" maxlength="1000" autocomplete="street-address">{{ old('alamat', $user->alamat) }}</textarea>@error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        </div>
    </div>
    <button type="submit" class="btn btn-profile-save"><i class="fas fa-save mr-1"></i>Simpan Profil</button>
</form>

@push('scripts')
<script>
document.getElementById('foto')?.addEventListener('change', function (event) {
    const file = event.target.files[0]; if (!file) return;
    const preview = document.getElementById('photo-preview');
    preview.src = URL.createObjectURL(file); preview.classList.remove('d-none');
    document.getElementById('photo-placeholder')?.classList.add('d-none');
});
</script>
@endpush
