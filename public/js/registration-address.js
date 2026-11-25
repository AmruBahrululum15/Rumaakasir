(() => {
    const root = document.getElementById('registration-address');
    if (!root) return;
    const form = root.closest('form'), levels = ['province', 'city', 'district', 'village'];
    const fields = levels.map(level => ({level, input: root.querySelector('#' + level), id: root.querySelector('#' + level + '_id'), list: root.querySelector('#' + level + '-options'), rows: []}));
    const mode = root.querySelector('#address_mode'), toggle = root.querySelector('#region-mode-toggle');
    const feedback = root.querySelector('#region-feedback'), postal = root.querySelector('#postal_code'), postalList = root.querySelector('#postal-options'), postalFeedback = root.querySelector('#postal-feedback');
    const initialIds = fields.map(field => field.id.value), initialPostal = postal.value;
    let version = 0, controller, loading = false;
    const setPostal = (codes = [], value = '') => {
        postal.value = value; postal.readOnly = codes.length === 1; postal.required = codes.length > 0;
        postal.setCustomValidity(''); postalList.replaceChildren();
        codes.forEach(code => { const option = document.createElement('option'); option.value = code; postalList.append(option); });
        if (codes.length === 1) postal.value = codes[0];
        postalFeedback.textContent = codes.length ? 'Kode pos sesuai kelurahan/desa yang dipilih.' : 'Kode pos belum tersedia dari sumber wilayah. Isi manual jika diketahui.';
    };
    const manual = (message) => {
        version++; controller?.abort(); loading = false; mode.value = 'manual';
        fields.forEach((field, index) => { field.id.value = ''; field.input.disabled = false; field.input.required = index < 2; field.input.removeAttribute('list'); field.input.setCustomValidity(''); field.input.removeAttribute('aria-busy'); });
        setPostal([], postal.value);
        toggle.textContent = 'Gunakan pilihan wilayah';
        feedback.textContent = message || 'Mode manual: isi nama wilayah dengan lengkap. Alamat akan diperiksa saat pengajuan ditinjau.';
    };
    const clearBelow = index => {
        for (let i = index + 1; i < fields.length; i++) {
            const field = fields[i]; field.input.value = ''; field.id.value = ''; field.rows = []; field.list.replaceChildren(); field.input.disabled = true; field.input.setCustomValidity('');
        }
        setPostal();
    };
    const load = async (index, parent, selected = '') => {
        controller?.abort(); controller = new AbortController();
        const current = ++version, field = fields[index]; loading = true;
        feedback.textContent = 'Memuat pilihan wilayah…'; field.input.setAttribute('aria-busy', 'true');
        const url = new URL(root.dataset.endpoint, location.href); url.searchParams.set('level', field.level); if (parent) url.searchParams.set('parent', parent);
        const timer = setTimeout(() => controller?.abort(), 12000);
        try {
            const response = await fetch(url, {headers: {Accept: 'application/json'}, signal: controller.signal, credentials: 'same-origin'});
            if (!response.ok) throw new Error('unavailable');
            const result = await response.json();
            if (current !== version) return false;
            if (!Array.isArray(result.data) || !result.data.length) throw new Error('unavailable');
            field.rows = result.data; field.list.replaceChildren();
            field.rows.forEach(row => { const option = document.createElement('option'); option.value = row.name; field.list.append(option); });
            field.input.disabled = false;
            const saved = field.rows.find(row => row.id === selected);
            field.input.value = saved?.name || ''; field.id.value = saved?.id || '';
            field.input.setCustomValidity(saved ? '' : 'Pilih wilayah dari daftar yang tersedia.');
            feedback.textContent = 'Ketik untuk mencari, lalu pilih wilayah dari daftar. Kecamatan dan kelurahan/desa wajib dipilih.';
            return true;
        } catch (error) {
            if (current === version) manual('Data wilayah gagal dimuat. Anda dapat melanjutkan dengan input manual atau mencoba pilihan wilayah lagi.');
            return false;
        } finally {
            clearTimeout(timer);
            if (current === version) { loading = false; field.input.removeAttribute('aria-busy'); }
        }
    };
    const structured = async (restore = false) => {
        mode.value = 'structured'; toggle.textContent = 'Gunakan input manual';
        fields.forEach(field => { field.input.required = true; field.input.disabled = true; field.input.setAttribute('list', field.level + '-options'); field.id.value = ''; });
        for (let i = 0; i < fields.length; i++) {
            if (!await load(i, i ? fields[i-1].id.value : null, restore ? initialIds[i] : '')) return;
            if (!fields[i].id.value) { clearBelow(i); break; }
            if (i === fields.length-1) setPostal(fields[i].rows.find(row => row.id === fields[i].id.value).postal_codes, initialPostal);
        }
    };
    fields.forEach((field, index) => field.input.addEventListener('input', () => {
        if (mode.value !== 'structured') return;
        version++; controller?.abort(); loading = false;
        field.input.removeAttribute('aria-busy');
        const row = field.rows.find(row => row.name === field.input.value);
        field.id.value = row?.id || ''; clearBelow(index);
        field.input.setCustomValidity(row ? '' : 'Pilih wilayah dari daftar yang tersedia.');
        if (row && index < fields.length-1) load(index+1, row.id);
        if (row && index === fields.length-1) setPostal(row.postal_codes);
    }));
    toggle.hidden = false;
    toggle.addEventListener('click', () => mode.value === 'structured' ? manual() : structured());
    const guardLoading = event => {
        if (loading) { event.preventDefault(); event.stopImmediatePropagation(); feedback.textContent = 'Tunggu pilihan wilayah selesai dimuat, atau gunakan input manual.'; root.scrollIntoView({block:'center'}); }
    };
    document.getElementById('registration-next')?.addEventListener('click', guardLoading, true);
    form.addEventListener('submit', guardLoading, true);
    if (root.dataset.oldMode === 'manual') manual(); else structured(true);
})();
