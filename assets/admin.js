/* Local preset editor and accessible native release-history dialog. */
(() => {
    'use strict';
    const form = document.getElementById('tffc-form');
    if (!form) return;
    const preset = document.getElementById('tffc-preset');
    const single = document.getElementById('tffc-single');
    const keys = ['min_total', 'min_item', 'min_value', 'max_total', 'max_item', 'step'];
    const sync = () => {
        for (const key of keys) document.getElementById(`tffc-${key}`).disabled = single.checked;
        document.getElementById('tffc-custom').classList.toggle('tffc-muted', single.checked);
    };
    preset.addEventListener('change', () => {
        if (preset.value === 'custom') { single.checked = false; sync(); return; }
        for (const key of keys) document.getElementById(`tffc-${key}`).value = key === 'step' ? 1 : 0;
        single.checked = preset.value === 'single';
        const presets = { minimum: ['min_total', 6], value: ['min_value', 30], maximum: ['max_total', 5], steps: ['step', 6] };
        const choice = presets[preset.value];
        if (choice) document.getElementById(`tffc-${choice[0]}`).value = choice[1];
        sync();
    });
    single.addEventListener('change', () => { preset.value = single.checked ? 'single' : 'custom'; sync(); });
    for (const key of keys) document.getElementById(`tffc-${key}`).addEventListener('input', () => { preset.value = 'custom'; });
    sync();
    const dialog = document.getElementById('tffc-history');
    const link = document.getElementById('tffc-history-link');
    if (!dialog || !link || !window.HTMLDialogElement || dialog.dataset.tffcInitialized) return;
    const native = window.HTMLDialogElement.prototype;
    if (typeof native.showModal !== 'function' || typeof native.close !== 'function') return;
    dialog.dataset.tffcInitialized = '1';
    link.addEventListener('click', event => {
        // Keep the real documentation link usable if native dialog opening fails.
        try {
            dialog.hidden = false;
            native.showModal.call(dialog);
            event.preventDefault();
            document.getElementById('tffc-history-close').focus();
        } catch (error) {
            if (dialog.open) native.close.call(dialog);
            dialog.hidden = true;
        }
    });
    dialog.addEventListener('close', () => { dialog.hidden = true; link.focus(); });
    dialog.addEventListener('cancel', () => native.close.call(dialog));
    dialog.addEventListener('click', event => {
        if (event.target !== dialog) return;
        const rect = dialog.getBoundingClientRect();
        if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) native.close.call(dialog);
    });
})();
