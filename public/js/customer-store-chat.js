(() => {
    document.querySelectorAll('.ct-product img').forEach(image => {
        const fallback = () => {
            image.hidden = true;
            image.nextElementSibling.hidden = false;
        };
        image.addEventListener('error', fallback);
        if (image.complete && !image.naturalWidth) fallback();
    });
    const form = document.getElementById('ct-message-form');
    const selected = document.getElementById('ct-selected-product');
    if (!form || !selected) return;
    const radios = [...document.querySelectorAll('input[name="product_id"][form="ct-message-form"]')];
    const name = document.getElementById('ct-selected-name');
    const message = document.getElementById('message');
    let generatedTemplate = '';
    const sync = () => {
        const product = radios.find(radio => radio.checked);
        selected.hidden = !product;
        name.textContent = product ? product.dataset.productName : '';
        if (!message.value.trim() || message.value === generatedTemplate) {
            generatedTemplate = product ? `Halo, apakah ${product.dataset.productName} masih tersedia?` : '';
            message.value = generatedTemplate;
        }
    };
    radios.forEach(radio => radio.addEventListener('change', () => {
        sync();
        message.focus({ preventScroll: true });
    }));
    document.getElementById('ct-clear-product').addEventListener('click', () => {
        radios.forEach(radio => { radio.checked = false; });
        sync();
    });
    sync();
    const messages = document.getElementById('messages');
    if (messages) messages.scrollTop = messages.scrollHeight;
})();
