(() => {
    const form = document.getElementById('login-form');
    if (!form) return;
    const toggle = form.querySelector('[data-password-toggle]');
    const password = document.getElementById(toggle.dataset.passwordToggle);
    toggle.addEventListener('click', () => {
        const show = password.type === 'password';
        password.type = show ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', String(show));
        toggle.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
    });
    const button = form.querySelector('button[type="submit"]');
    const label = button.querySelector('[data-login-label]');
    form.addEventListener('submit', () => {
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        label.textContent = 'Memproses...';
    });
    window.addEventListener('pageshow', () => {
        button.disabled = false;
        button.removeAttribute('aria-busy');
        label.textContent = 'Masuk';
    });
})();
