import { api } from '../utils/api.js';

export function initModuleToggles() {
    document.querySelectorAll('.module-toggle').forEach((toggle) => {
        toggle.addEventListener('change', async function () {
            const formData = new FormData();
            formData.append('module', this.dataset.module);
            formData.append('enabled', this.checked ? '1' : '0');
            try {
                await api.post('/admin/modules/toggle', formData);
            } catch (error) {
                console.error('Module toggle error:', error);
            }
        });
    });
}
