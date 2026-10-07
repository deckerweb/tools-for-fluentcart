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
    if (typeof dialog.showModal !== 'function') return;
    link.addEventListener('click', event => { event.preventDefault(); dialog.showModal(); document.getElementById('tffc-history-close').focus(); });
    document.getElementById('tffc-history-close').addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => link.focus());
})();
