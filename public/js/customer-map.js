document.querySelectorAll('[data-store-map]').forEach(root=>{
    if(!window.L)return;
    const markers=JSON.parse(root.dataset.markers),map=L.map(root).setView([-2.5,118],4);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>'}).addTo(map);
    const points=[];
    markers.forEach(store=>{
        const point=[store.latitude,store.longitude],box=document.createElement('div');points.push(point);
        for(const text of [store.name,store.type,store.address,store.distance!==null?store.distance+' km':'']){const p=document.createElement('p');p.textContent=text;box.append(p);}
        const a=document.createElement('a');a.textContent='Lihat Toko';a.href=store.url;box.append(a);
        L.circleMarker(point,{radius:9,color:'#c44c09',fillColor:'#f97316',fillOpacity:.9}).addTo(map).bindPopup(box);
    });
    const fit=()=>{map.invalidateSize();if(points.length)map.fitBounds(points,{padding:[30,30],maxZoom:15});};
    document.addEventListener('portal-map-visible',fit);fit();
    root.nextElementSibling.textContent=points.length?'Lokasi toko yang sudah dilengkapi oleh pemilik.':'Belum ada toko dengan koordinat pada pencarian ini.';
});
