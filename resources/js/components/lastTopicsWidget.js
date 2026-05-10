import { api } from '../utils/api.js';

export default () => ({
    topics: [],
    loading: true,

    async init() {
        await this.fetchTopics();
    },

    async fetchTopics() {
        try {
            const data = await api.get('/api/forum/last-topics?limit=5');
            this.topics = data;
        } catch (e) {
            console.error('Ошибка загрузки тем:', e);
        } finally {
            this.loading = false;
        }
    },
});
