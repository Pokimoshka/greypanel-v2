export default () => ({
    servers: [],
    loading: true,

    async init() {
        await this.fetchServers();
        setInterval(() => this.fetchServers(), 30000);
    },

    async fetchServers() {
        const res = await fetch('/monitor/data');
        this.servers = await res.json();
        this.loading = false;
    }
});