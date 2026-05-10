import { api } from '../utils/api.js';

export default () => ({
    count: 0,
    users: [],
    loading: true,
    open: true,

    async init() {
        await this.fetchOnline();
        setInterval(() => this.fetchOnline(), 30000);
    },

    async fetchOnline() {
        try {
            const data = await api.get('/online/data');
            this.count = data.count || 0;
            this.users = data.users || [];
        } catch (e) {
            console.error('Ошибка загрузки онлайн:', e);
        } finally {
            this.loading = false;
        }
    },

    formatActivity(timestamp) {
        if (!timestamp) return '';
        const seconds = Math.floor(Date.now() / 1000) - timestamp;
        if (seconds < 60) return window.__['online.just_now'];
        if (seconds < 3600)
            return (window.__['online.min_ago'] || '%count% мин. назад').replace(
                '%count%',
                Math.floor(seconds / 60)
            );
        if (seconds < 86400)
            return (window.__['online.hour_ago'] || '%count% ч. назад').replace(
                '%count%',
                Math.floor(seconds / 3600)
            );
        const date = new Date(timestamp * 1000);
        return date.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' });
    },
});
