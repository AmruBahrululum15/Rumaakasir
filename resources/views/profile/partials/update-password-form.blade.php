<form method="POST" action="{{ route('password.update') }}">
    @csrf
    @method('PUT')
    <div class="form-group"><label class="profile-form-label" for="current_password">Password Saat Ini</label><input id="current_password" name="current_password" type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" required autocomplete="current-password">@error('current_password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="form-group"><label class="profile-form-label" for="password">Password Baru</label><input id="password" name="password" type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" required autocomplete="new-password">@error('password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror<small class="form-text text-muted">Minimal 8 karakter.</small></div>
    <div class="form-group mb-4"><label class="profile-form-label" for="password_confirmation">Konfirmasi Password Baru</label><input id="password_confirmation" name="password_confirmation" type="password" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" required autocomplete="new-password">@error('password_confirmation', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <button type="submit" class="btn btn-profile-save btn-block"><i class="fas fa-key mr-1"></i>Ubah Password</button>
</form>
