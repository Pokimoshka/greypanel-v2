Alpine.data('chatWidget', () => ({
    messages: [],
    newMessage: '',
    lastId: 0,
    csrf: '',

    init() {
        this.csrf = document.querySelector('meta[name="csrf-token"]').content;
        this.fetchMessages();
        setInterval(() => this.fetchMessages(), 10000);
    },

    async fetchMessages() {
        const res = await fetch(`/chat/messages?last=${this.lastId}`);
        const data = await res.json();
        if (data.length) {
            this.messages = [...data.reverse(), ...this.messages];
            this.lastId = data[0].id;
            this.$nextTick(() => {
                const el = this.$refs.messagesContainer;
                if (el) el.scrollTop = el.scrollHeight;
            });
        }
    },

    async sendMessage() {
        if (!this.newMessage.trim()) return;
        const formData = new FormData();
        formData.append('message', this.newMessage);
        formData.append('csrf_token', this.csrf);
        await fetch('/chat/send', { method: 'POST', body: formData });
        this.newMessage = '';
        this.fetchMessages();
    }

}));

Alpine.data('monitorWidget', () => ({
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
}));

Alpine.data('likeButton', () => ({
    count: initialCount,
    liked: initialLiked,
    csrf: document.querySelector('meta[name="csrf-token"]').content,

    async toggle() {
        const formData = new FormData();
        formData.append('type', type);
        formData.append('target_id', targetId);
        formData.append('csrf_token', this.csrf);
        const res = await fetch('/forum/like', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            this.liked = !this.liked;
            this.count += this.liked ? 1 : -1;
        }
    }
}));
Alpine.data('modal', () => ({
    open: false,
    close() { this.open = false; }
}));
Alpine.data('sortableList', () =>({
    init() {
        const el = this.$refs.sortableList;
        new Sortable(el, {
            animation: 150,
            onEnd: async (evt) => {
                const items = [...el.children].map((item, index) => ({
                    id: item.dataset.id,
                    order: index
                }));
                await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order: items, csrf_token: csrf })
                });
            }
        });
    }
}));
Alpine.data('quote', () =>({
    insertQuote(author, content) {
        const textarea = document.getElementById('reply-content');
        if (textarea) {
            textarea.value += `[quote=${author}]${content}[/quote]\n\n`;
            textarea.focus();
        }
    }
}));