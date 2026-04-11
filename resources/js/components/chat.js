export default () => ({
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
});