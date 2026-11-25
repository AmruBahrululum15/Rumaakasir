(() => {
 document.querySelector('a[href="#manual-booking"]')?.addEventListener('click',()=>{const form=document.getElementById('manual-booking');if(form)form.open=true;});
 const dialog=document.getElementById('rv-detail-dialog');if(!dialog||!dialog.showModal)return;
 document.querySelectorAll('[data-rv-detail]').forEach(link=>link.addEventListener('click',event=>{
  event.preventDefault();dialog.querySelectorAll('[data-rv-value]').forEach(el=>el.textContent=link.dataset[el.dataset.rvValue]||'—');
  dialog.querySelector('[data-rv-full]').href=link.href;dialog.showModal();
 }));dialog.querySelector('[data-rv-close]').addEventListener('click',()=>dialog.close());
 dialog.addEventListener('click',event=>{if(event.target===dialog){const r=dialog.getBoundingClientRect();if(event.clientX<r.left||event.clientX>r.right||event.clientY<r.top||event.clientY>r.bottom)dialog.close();}});
})();
