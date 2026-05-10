import { api } from '../utils/api.js';

export function initSeoRegenerate() {
    const btn = document.getElementById('regenerateBtn');
    const resultSpan = document.getElementById('regenerateResult');
    if (!btn || !resultSpan) return;

    btn.addEventListener('click', async () => {
        try {
            await api.post('/admin/seo/regenerate');
            resultSpan.textContent = window.__['admin.done'] || 'Готово';
        } catch (e) {
            resultSpan.textContent = window.__['admin.error'] || 'Ошибка';
        }
    });
}
