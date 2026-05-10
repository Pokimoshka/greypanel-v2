import { api } from '../utils/api.js';
import { Toast } from '../utils/toast-global.js';

export default (settings) => ({
    async requestUnban(banId) {
        const demoUrl = prompt(settings.demoPrompt || 'Ссылка на демо (если есть):');
        if (demoUrl === null) return;

        const formData = new FormData();
        formData.append('ban_id', banId);
        if (demoUrl) formData.append('demo_url', demoUrl);

        try {
            const data = await api.post('/bans/request', formData);
            if (data.success) {
                if (confirm(data.message || 'Заявка создана. Перейти к теме?')) {
                    window.location.href = '/forum/thread/' + data.thread_id;
                }
            } else {
                Toast.error(data.error || 'Ошибка');
            }
        } catch (e) {
            Toast.error(e.message || window.__['errors.network'] || 'Ошибка сети');
        }
    },

    async paidUnban(banId) {
        const price = settings.buy_razban;
        if (!confirm(`${settings.paidConfirmPrefix || 'Списать'} ${price} ₽?`)) return;

        const formData = new FormData();
        formData.append('ban_id', banId);

        try {
            const data = await api.post('/bans/paid', formData);
            if (data.success) {
                Toast.success(data.message || 'Бан успешно снят');
                setTimeout(() => location.reload(), 1000);
            } else {
                Toast.error(data.error || 'Ошибка');
            }
        } catch (e) {
            Toast.error(e.message || window.__['errors.network'] || 'Ошибка сети');
        }
    },
});
