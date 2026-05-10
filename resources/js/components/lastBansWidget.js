import { api } from '../utils/api.js';

export default () => ({
    bans: [],
    loading: true,

    async init() {
        await this.fetchBans();
    },

    async fetchBans() {
        try {
            const data = await api.get('/api/bans/last-bans?limit=5');
            this.bans = data;
        } catch (e) {
            console.error('Ошибка загрузки банов:', e);
        } finally {
            this.loading = false;
        }
    },
});
