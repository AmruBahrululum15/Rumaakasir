(() => {
    const header=document.querySelector('.portal-header');
    if(!header)return;
    const toggle=header.querySelector('.portal-menu-toggle'),nav=header.querySelector('.portal-navigation');
    const mobile=matchMedia('(max-width:1100px)');
    const setOpen=open=>{
        header.toggleAttribute('data-menu-open',open);
        toggle.setAttribute('aria-expanded',String(open));
        toggle.setAttribute('aria-label',open?'Tutup menu navigasi':'Buka menu navigasi');
    };
    header.setAttribute('data-nav-ready','');
    toggle.addEventListener('click',()=>setOpen(toggle.getAttribute('aria-expanded')!=='true'));
    header.addEventListener('keydown',event=>{if(event.key==='Escape'&&header.hasAttribute('data-menu-open')){setOpen(false);toggle.focus();}});
    document.addEventListener('click',event=>{if(!header.contains(event.target))setOpen(false);});
    nav.addEventListener('click',event=>{if(event.target.closest('a')&&mobile.matches)setOpen(false);});
    mobile.addEventListener('change',()=>setOpen(false));
    const category=nav.querySelector('[data-category-nav]'),initialActive=nav.querySelector('[aria-current]');
    const updateCategory=()=>{
        if(!category)return;
        const active=location.hash==='#type'&&new URL(category.href).pathname===location.pathname;
        category.removeAttribute('aria-current');
        if(initialActive)initialActive.setAttribute('aria-current','page');
        if(active){initialActive?.removeAttribute('aria-current');category.setAttribute('aria-current','location');}
    };
    updateCategory();window.addEventListener('hashchange',updateCategory);
})();

document.querySelectorAll('[data-nearby-link]').forEach(button=>button.addEventListener('click',e=>{
    e.preventDefault();const feedback=document.querySelector('.location-feedback');
    if(!navigator.geolocation){feedback.textContent='Lokasi browser tidak tersedia. Silakan cari kota/alamat secara manual.';return;}
    feedback.textContent='Meminta izin lokasi Anda...';
    navigator.geolocation.getCurrentPosition(p=>{const url=new URL(button.href||button.dataset.url,location.origin);if(button.dataset.url)url.search=location.search;url.searchParams.delete('city');url.searchParams.delete('page');url.searchParams.set('latitude',p.coords.latitude);url.searchParams.set('longitude',p.coords.longitude);location.assign(url);},()=>{feedback.textContent='Lokasi tidak tersedia atau izin ditolak. Anda tetap dapat mencari toko berdasarkan kota/alamat.';},{timeout:10000,maximumAge:60000});
}));
document.querySelectorAll('[data-view]').forEach(button=>button.addEventListener('click',()=>{
    const map=button.dataset.view==='map';document.getElementById('store-map-view').hidden=!map;document.getElementById('store-list-view').hidden=map;
    document.querySelectorAll('[data-view]').forEach(b=>b.setAttribute('aria-pressed',String(b===button)));
    if(map)document.dispatchEvent(new Event('portal-map-visible'));
}));

document.querySelectorAll('[data-portal-product-image]').forEach(image => {
    const fallback = () => { image.hidden = true; image.nextElementSibling.hidden = false; };
    image.addEventListener('error', fallback);
    if (image.complete && !image.naturalWidth) fallback();
});
