import { api } from '../utils/api.js';

export default () => ({
    servers: [],
    loading: true,

    async init() {
        await this.fetchServers();
        setInterval(() => this.fetchServers(), 30000);
    },

    async fetchServers() {
        const data = await api.get('/monitor/data');
        this.servers = data;
        this.loading = false;
    },
});
