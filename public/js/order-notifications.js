(() => {
    const root=document.getElementById('order-notification-navbar');if(!root)return;
    let busy=false;
    const seen=new Set([...root.querySelectorAll('[data-reservation-note]')].map(item=>item.dataset.reservationNote));
    const announce=note=>{
        if(seen.has(note.id))return;seen.add(note.id);
        const toast=document.createElement('div');toast.setAttribute('role','status');
        Object.assign(toast.style,{position:'fixed',right:'20px',bottom:'90px',maxWidth:'min(360px,calc(100vw - 40px))',padding:'18px',background:'#fff',border:'1px solid #f0d5bd',borderRadius:'14px',boxShadow:'0 8px 30px #24304220',zIndex:1100});
        const title=document.createElement('strong');title.textContent=note.title;toast.append(title);
        const detail=document.createElement('p');detail.textContent=note.message;detail.style.fontSize='12px';detail.style.margin='8px 0';toast.append(detail);
        const form=document.createElement('form');form.method='POST';form.action=note.url;
        const token=document.createElement('input');token.type='hidden';token.name='_token';token.value=root.querySelector('input[name="_token"]')?.value||'';form.append(token);
        const open=document.createElement('button');open.type='submit';open.className='btn btn-sm btn-warning';open.textContent='Lihat Reservasi';form.append(open);toast.append(form);
        const close=document.createElement('button');close.type='button';close.textContent='Tutup';close.className='btn btn-sm btn-light mt-2';close.addEventListener('click',()=>toast.remove());toast.append(close);
        document.querySelectorAll('[data-reservation-toast]').forEach(item=>item.remove());toast.dataset.reservationToast='';document.body.append(toast);setTimeout(()=>toast.remove(),15000);
    };
    setInterval(async()=>{
        if(document.hidden||busy||root.classList.contains('show'))return;busy=true;
        try{const response=await fetch(root.dataset.feed,{headers:{Accept:'application/json'},credentials:'same-origin'});if(!response.ok)return;const data=await response.json();(data.reservations||[]).forEach(announce);document.querySelectorAll('[data-reservation-unread]').forEach(badge=>{badge.textContent=data.reservation_unread_count||0;badge.hidden=!data.reservation_unread_count;});if(!root.classList.contains('show'))root.innerHTML=data.html;document.querySelectorAll('[data-chat-unread]').forEach(badge=>{badge.textContent=data.chat_unread_count;badge.hidden=!data.chat_unread_count;});}catch{}finally{busy=false;}
    },15000);
})();
