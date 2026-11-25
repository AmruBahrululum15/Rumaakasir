<span class="sa-avatar">
    @if($saUser->foto)
        <img src="{{ route('users.photo', $saUser->id) }}" alt="Avatar Super Admin">
    @else
        <span aria-hidden="true">SA</span>
    @endif
</span>
