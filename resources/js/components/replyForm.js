import { api } from '../utils/api.js';
import { Toast } from '../utils/toast-global.js';

export default (threadId) => ({
    async submit() {
        const textarea = document.getElementById('reply-editor');
        if (!textarea) {
            Toast.error(window.__['editor.textarea_not_found'] || 'Поле ввода не найдено');
            return;
        }
        const editor = textarea._markdownEditor;
        if (!editor) {
            Toast.error(window.__['editor.not_loaded'] || 'Редактор не загружен');
            return;
        }
        const content = editor.getContent().trim();
        if (!content) {
            Toast.error(window.__['forum.reply_empty'] || 'Введите сообщение');
            return;
        }

        const formData = new FormData();
        formData.append('thread_id', threadId);
        formData.append('content', content);

        try {
            const data = await api.post('/forum/post/create', formData);
            if (data.success) {
                editor.setContent('');
                editor.clearDraft();
                location.reload();
            } else {
                Toast.error(data.error || 'Ошибка');
            }
        } catch (e) {
            Toast.error(e.message || 'Ошибка сети');
        }
    },
});
