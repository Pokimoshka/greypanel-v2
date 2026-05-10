import { api } from '../utils/api.js';
import { Toast } from '../utils/toast-global.js';

export default () => ({
    messages: [],
    newMessage: '',
    lastId: 0,
    sending: false,

    async init() {
        await this.fetchMessages();
        setInterval(() => this.fetchMessages(), 10000);
    },

    async fetchMessages() {
        const data = await api.get('/chat/messages', { last: this.lastId });
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
        if (this.sending) return;
        const message = this.newMessage.trim();
        if (!message) return;

        this.sending = true;
        const formData = new FormData();
        formData.append('message', message);
        try {
            const newMsg = await api.post('/chat/send', formData);
            this.messages = [...this.messages, newMsg];
            this.newMessage = '';
            this.$nextTick(() => {
                const el = this.$refs.messagesContainer;
                if (el) el.scrollTop = el.scrollHeight;
            });
        } catch (e) {
            Toast.error(window.__['chat.send_error'] || 'Ошибка отправки сообщения');
        } finally {
            this.sending = false;
        }
    }
});
