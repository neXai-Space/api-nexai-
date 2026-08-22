// =========================================================
// NEXAI SEARCH API — main.js
// =========================================================

function copyKey() {
    const el = document.getElementById('apiKeyText');
    if (!el) return;

    const text = el.textContent;
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.querySelector('.btn-copy');
        const original = btn.textContent;
        btn.textContent = 'Copied!';
        setTimeout(() => { btn.textContent = original; }, 1500);
    }).catch(() => {
        alert('Gagal copy, silakan copy manual: ' + text);
    });
}
