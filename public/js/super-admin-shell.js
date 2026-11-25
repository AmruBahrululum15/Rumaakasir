(() => {
    const body = document.body;
    if (!body.classList.contains('sa-shell')) return;
    const toggle = document.querySelector('[data-sa-toggle]');
    const sidebar = document.getElementById('sa-sidebar');
    const mobile = window.matchMedia('(max-width: 991.98px)');
    if (!toggle || !sidebar) return;

    const sync = () => {
        const expanded = mobile.matches ? body.classList.contains('sa-mobile-open') : !body.classList.contains('sa-collapsed');
        toggle.setAttribute('aria-expanded', String(expanded));
        toggle.setAttribute('aria-label', expanded ? 'Tutup sidebar' : 'Buka sidebar');
        sidebar.inert = mobile.matches && !expanded;
    };
    const close = () => {
        body.classList.remove('sa-mobile-open');
        sync();
        toggle.focus();
    };
    toggle.addEventListener('click', () => {
        body.classList.toggle(mobile.matches ? 'sa-mobile-open' : 'sa-collapsed');
        sync();
        if (mobile.matches && body.classList.contains('sa-mobile-open')) sidebar.querySelector('a')?.focus();
    });
    document.querySelector('[data-sa-close]')?.addEventListener('click', close);
    document.addEventListener('keydown', event => {
        if (!mobile.matches || !body.classList.contains('sa-mobile-open')) return;
        if (event.key === 'Escape') close();
        if (event.key !== 'Tab') return;
        const links = [...sidebar.querySelectorAll('a[href], button:not([disabled])')];
        const first = links[0], last = links[links.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    });
    mobile.addEventListener('change', () => { body.classList.remove('sa-mobile-open'); sync(); });
    sync();
})();
