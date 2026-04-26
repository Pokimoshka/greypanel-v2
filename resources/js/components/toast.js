export default () => ({
    toasts: [],
    init() {
        // Слушаем глобальные события
        window.addEventListener('toast:success', (e) => this.success(e.detail));
        window.addEventListener('toast:error', (e) => this.error(e.detail));
        window.addEventListener('toast:info', (e) => this.info(e.detail));
        window.addEventListener('toast:warning', (e) => this.warning(e.detail));
    },
    add(type, message, duration = 5000) {
        const id = Date.now() + Math.random();
        this.toasts.push({ id, type, message });
        setTimeout(() => {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }, duration);
    },
    success(message) { this.add('success', message); },
    error(message) { this.add('danger', message); },
    info(message) { this.add('info', message); },
    warning(message) { this.add('warning', message); }
});