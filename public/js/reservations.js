(() => {
    const form=document.getElementById('public-reservation');if(!form)return;
    const service=form.elements.service_id,date=form.elements.booking_date,staff=form.elements.staff_id,resource=form.elements.resource_id,units=form.elements.capacity_units,time=form.elements.start_time;
    const feedback=document.getElementById('rv-slot-feedback'),slots=document.getElementById('rv-slots');let controller,timer;
    const load=async()=>{
        if(controller)controller.abort();slots.replaceChildren();
        const option=service.selectedOptions[0],count=Number(units.value||1);
        document.getElementById('rv-service-summary').textContent=service.value?option.textContent:'Belum memilih layanan.';
        document.getElementById('rv-price').textContent='Rp '+new Intl.NumberFormat('id-ID').format(Number(option?.dataset.price||0)*count);
        if(!service.value||!date.value){feedback.textContent='Pilih layanan dan tanggal untuk melihat slot tersedia.';return;}
        controller=new AbortController();feedback.textContent='Memeriksa slot tersedia...';slots.setAttribute('aria-busy','true');
        try{
            const url=new URL(form.dataset.slotsUrl,location.origin);url.search=new URLSearchParams({service_id:service.value,date:date.value,staff_id:staff.value,resource_id:resource.value,capacity_units:units.value});
            const response=await fetch(url,{headers:{Accept:'application/json'},signal:controller.signal});if(!response.ok)throw new Error('unavailable');const data=await response.json();
            feedback.textContent=data.slots.length?'Pilih salah satu jam yang tersedia.':'Tidak ada slot tersedia. Coba jadwal, karyawan, atau fasilitas lain.';
            data.slots.forEach(slot=>{const button=document.createElement('button');button.type='button';button.textContent=slot;button.setAttribute('aria-pressed',String(time.value===slot));button.addEventListener('click',()=>{time.value=slot;slots.querySelectorAll('button').forEach(b=>b.setAttribute('aria-pressed',String(b===button)));});slots.append(button);});
        }catch(error){if(error.name!=='AbortError')feedback.textContent='Slot belum dapat dimuat. Anda dapat mengisi jam manual; ketersediaan tetap diperiksa saat dikirim.';}finally{slots.removeAttribute('aria-busy');}
    };
    [service,date,staff,resource,units].forEach(field=>field.addEventListener('change',()=>{time.value='';clearTimeout(timer);timer=setTimeout(load,150);}));
    form.addEventListener('submit',()=>{if(form.checkValidity()){const button=document.getElementById('rv-submit');button.disabled=true;button.textContent='Mengirim reservasi...';}});
    load();
})();
