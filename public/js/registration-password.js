document.querySelectorAll('[data-registration-passwords] input[type="password"]').forEach((input) => {
    const wrap = document.createElement('div');
    wrap.className = 'registration-password-wrap';
    input.before(wrap);
    wrap.append(input);
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'registration-password-toggle';
    button.setAttribute('aria-controls', input.id);
    button.setAttribute('aria-label', 'Tampilkan password');
    button.setAttribute('aria-pressed', 'false');
    button.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/><path class="password-eye-slash" d="m3 3 18 18"/></svg>';
    button.addEventListener('click', () => {
        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        button.setAttribute('aria-label', visible ? 'Sembunyikan password' : 'Tampilkan password');
        button.setAttribute('aria-pressed', String(visible));
    });
    wrap.append(button);
});
