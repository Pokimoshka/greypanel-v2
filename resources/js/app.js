import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import 'bootstrap/dist/js/bootstrap.min.js';
import Sortable from 'sortablejs';
import EasyMDE from 'easymde';
import { marked } from 'marked';
import { markedHighlight } from 'marked-highlight';
import hljs from 'highlight.js';
import 'highlight.js/styles/github-dark.css';
import { markdownToBbcode } from './markdown-to-bbcode';

import chatWidget from './components/chat';
import likeButton from './components/like';
import modal from './components/modal';
import monitorWidget from './components/monitor';
import quote from './components/quote';
import sortableList from './components/sortable';

window.Alpine = Alpine;
window.Sortable = Sortable;

Alpine.data('chatWidget', chatWidget);
Alpine.data('likeButton', likeButton);
Alpine.data('modal', modal);
Alpine.data('monitorWidget', monitorWidget);
Alpine.data('quote', quote);
Alpine.data('sortableList', sortableList);

Alpine.plugin(collapse);

Alpine.start();

marked.use(markedHighlight({
    langPrefix: 'hljs language-',
    highlight(code, lang) {
        if (lang && hljs.getLanguage(lang)) {
            return hljs.highlight(code, { language: lang }).value;
        }
        return code;
    }
}));

const editorToolbar = [
    'bold', 'italic', 'heading', '|',
    'quote', 'unordered-list', 'ordered-list', 'table', 'horizontal-rule', '|',
    'link', 'image',
    {
        name: 'upload-image',
        action: function(editor) {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            input.onchange = async () => {
                const file = input.files[0];
                if (!file) return;
                const formData = new FormData();
                formData.append('image', file);
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);

                try {
                    const resp = await fetch('/admin/upload-image', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await resp.json();
                    if (data.url) {
                        const cm = editor.codemirror;
                        cm.replaceSelection(`![](${data.url})`);
                    } else {
                        alert('Ошибка загрузки');
                    }
                } catch (e) {
                    alert('Ошибка соединения');
                }
            };
            input.click();
        },
        className: 'fa fa-upload',
        title: 'Загрузить изображение'
    },
    '|', 'preview', 'side-by-side', 'fullscreen', '|',
    'guide'
];

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('textarea.editor').forEach(el => {
        const easyMDE = new EasyMDE({
            element: el,
            spellChecker: true,
            status: ['lines', 'words', 'cursor'],
            toolbar: editorToolbar,
            previewRender: (plainText) => marked.parse(plainText),
        });

        const originalToTextArea = easyMDE.toTextArea;
        easyMDE.toTextArea = function() {
            const md = this.value();
            el.value = markdownToBbcode(md);
            originalToTextArea.call(this);
        };

        el.easyMDE = easyMDE;
    });
});