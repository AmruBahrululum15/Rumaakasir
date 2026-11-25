(() => {
    const form = document.querySelector('[data-customer-register]');
    if (!form) return;
    form.querySelectorAll('[data-cr-toggle]').forEach(button => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.crToggle);
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.textContent = show ? 'Tutup' : 'Lihat';
            button.setAttribute('aria-pressed', String(show));
            button.setAttribute('aria-label', (show ? 'Sembunyikan' : 'Tampilkan') + (input.name === 'password_confirmation' ? ' konfirmasi password' : ' password'));
            input.focus();
        });
    });
    const password = form.elements.password, confirmation = form.elements.password_confirmation;
    const status = document.getElementById('cr-match'), caps = document.getElementById('cr-caps');
    const match = () => {
        const mismatch = confirmation.value !== '' && password.value !== confirmation.value;
        confirmation.setCustomValidity(mismatch ? 'Konfirmasi password belum sama.' : '');
        status.textContent = confirmation.value === '' ? 'Masukkan password yang sama.' : mismatch ? 'Password belum sama. Periksa kembali.' : 'Password sudah sama.';
        status.dataset.match = confirmation.value === '' ? '' : mismatch ? 'mismatch' : 'match';
    };
    password.addEventListener('input', match);
    confirmation.addEventListener('input', match);
    ['keyup', 'keydown'].forEach(event => password.addEventListener(event, e => { caps.hidden = !e.getModifierState('CapsLock'); }));
    password.addEventListener('blur', () => { caps.hidden = true; });
})();
