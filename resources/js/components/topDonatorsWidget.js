import { api } from '../utils/api.js';

export default () => ({
    donators: [],
    loading: true,

    async init() {
        await this.fetchDonators();
    },

    async fetchDonators() {
        try {
            const data = await api.get('/api/top-donators?limit=5');
            this.donators = data;
        } catch (e) {
            console.error('Ошибка загрузки донатеров:', e);
        } finally {
            this.loading = false;
        }
    },
});
