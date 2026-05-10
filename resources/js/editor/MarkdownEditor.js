import { Toast } from '../utils/toast-global';
import EasyMDE from 'easymde';
import { marked } from 'marked';
import { markedHighlight } from 'marked-highlight';
import hljs from 'highlight.js';
import { Picker } from 'emoji-picker-element';
import { Modal } from 'bootstrap';
import { toolbarConfig } from './toolbar';
import { uploadImage } from './upload';

marked.use(
    markedHighlight({
        langPrefix: 'hljs language-',
        highlight(code, lang) {
            if (lang && hljs.getLanguage(lang)) {
                return hljs.highlight(code, { language: lang }).value;
            }
            return code;
        },
    })
);

export default class MarkdownEditor {
    constructor(textarea, options = {}) {
        this.textarea = textarea;
        this.options = {
            uploadEndpoint: '/upload-image',
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.content,
            draftKey: textarea.id || textarea.name || 'md-editor',
            ...options,
        };
        this.easyMDE = null;
        this._emojiPicker = null;
        this.init();
    }

    init() {
        this.easyMDE = new EasyMDE({
            element: this.textarea,
            spellChecker: false,
            autoDownloadFontAwesome: false,
            status: ['lines'],
            previewRenderDelay: 500,
            toolbar: toolbarConfig(this),
            uploadImage: false,
            previewRender: (plainText) => {
                let html = marked.parse(plainText);
                html = html.replace(
                    /<blockquote>\s*([\s\S]*?)\s*<\/blockquote>/g,
                    (match, inner) => {
                        const authorMatch = inner.match(
                            /^\s*<p>\s*<strong>(.+?)<\/strong>\s*\(#(\d+)\)\s*<\/p>\s*(.*)/s
                        );
                        if (authorMatch) {
                            const author = authorMatch[1];
                            const postId = authorMatch[2];
                            let body = authorMatch[3].trim().replace(/^(<br\s*\/?>)+/, '');
                            return `
                            <blockquote class="xen-quote">
                                <div class="quote-header">
                                    ${author} написал(а):
                                    <a href="/forum/thread/${postId}#post-${postId}">⤴</a>
                                </div>
                                <div class="quote-body">${body}</div>
                            </blockquote>`;
                        }
                        return `<blockquote class="xen-quote"><div class="quote-body">${inner.trim()}</div></blockquote>`;
                    }
                );
                return `<div class="post-content">${html}</div>`;
            },
        });

        let draftTimer;
        this.easyMDE.codemirror.on('change', () => {
            clearTimeout(draftTimer);
            draftTimer = setTimeout(() => this.saveDraft(), 500);
        });

        const cm = this.easyMDE.codemirror;
        const wrapper = cm.getWrapperElement();
        wrapper.addEventListener('dragover', (e) => {
            e.preventDefault();
            wrapper.classList.add('editor-dragover');
        });
        wrapper.addEventListener('dragleave', () => wrapper.classList.remove('editor-dragover'));
        wrapper.addEventListener('drop', async (e) => {
            e.preventDefault();
            wrapper.classList.remove('editor-dragover');
            const files = Array.from(e.dataTransfer.files).filter((f) =>
                f.type.startsWith('image/')
            );
            for (const file of files) {
                try {
                    const url = await uploadImage(
                        file,
                        this.options.uploadEndpoint,
                        this.options.csrfToken
                    );
                    cm.replaceSelection(`![](${url})`);
                } catch (err) {
                    Toast.error(window.__['editor.image_error'] || 'Image upload error');
                }
            }
        });

        const form = this.textarea.closest('form');
        if (form) {
            form.addEventListener('submit', () => {
                this.textarea.value = this.getContent();
            });
        }

        this.textarea._markdownEditor = this;
        this.loadDraft();
    }

    getContent() {
        return this.easyMDE.value();
    }
    setContent(md) {
        this.easyMDE.value(md);
    }

    saveDraft() {
        localStorage.setItem(`draft_${this.options.draftKey}`, this.getContent());
    }
    loadDraft() {
        const saved = localStorage.getItem(`draft_${this.options.draftKey}`);
        if (saved && !this.textarea.value) {
            this.setContent(saved);
            this.easyMDE.codemirror.clearHistory();
        }
    }
    clearDraft() {
        localStorage.removeItem(`draft_${this.options.draftKey}`);
    }

    uploadImageFile() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        input.onchange = async () => {
            const file = input.files[0];
            if (!file) return;
            try {
                const url = await uploadImage(
                    file,
                    this.options.uploadEndpoint,
                    this.options.csrfToken
                );
                this.easyMDE.codemirror.replaceSelection(`![](${url})`);
            } catch (err) {
                Toast.error(window.__['editor.image_error'] || 'Image upload error');
            }
        };
        input.click();
    }

    insertLink() {
        this.showLinkDialog('link');
    }
    insertImageByUrl() {
        this.showLinkDialog('image');
    }

    showLinkDialog(type) {
        const existing = document.getElementById('md-link-modal');
        if (existing) existing.remove();
        const modalHtml = `
        <div class="modal fade" id="md-link-modal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header"><h5>${type === 'link' ? window.__['editor.link_title'] || 'Insert Link' : window.__['editor.image_title'] || 'Insert Image'}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
              <div class="modal-body">
                ${type === 'link' ? '<input id="md-link-text" class="form-control mb-2" placeholder="Текст ссылки">' : ''}
                <input id="md-link-url" class="form-control" placeholder="URL">
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="md-link-insert">Вставить</button>
              </div>
            </div>
          </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modalEl = document.getElementById('md-link-modal');
        const modal = new Modal(modalEl);
        modal.show();

        document.getElementById('md-link-insert').addEventListener('click', () => {
            const url = document.getElementById('md-link-url').value.trim();
            if (!url) return;
            const cm = this.easyMDE.codemirror;
            if (type === 'link') {
                const text = document.getElementById('md-link-text')?.value.trim() || url;
                cm.replaceSelection(`[${text}](${url})`);
            } else {
                cm.replaceSelection(`![](${url})`);
            }
            modal.hide();
        });
        modalEl.addEventListener('hidden.bs.modal', () => modalEl.remove());
    }

    showEmojiPicker(button) {
        if (!button) {
            button = document.querySelector('.fa-smile-o');
        }
        if (!button) return;

        if (this._emojiPicker) {
            this.hideEmojiPicker();
            return;
        }

        const container = document.createElement('div');
        container.className = 'emoji-picker-container';
        container.style.position = 'absolute';
        container.style.zIndex = '2500';
        container.style.background = 'var(--card-bg, #fff)';
        container.style.border = '1px solid var(--border-color, #ccc)';
        container.style.borderRadius = '8px';
        container.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        container.style.maxHeight = '350px';
        container.style.overflowY = 'auto';

        const picker = new Picker({
            locale: 'ru',
            skinToneEmoji: '👍',
            theme:
                document.documentElement.getAttribute('data-bs-theme') === 'dark'
                    ? 'dark'
                    : 'light',
        });

        picker.addEventListener('emoji-click', (event) => {
            const emoji = event.detail.emoji.unicode;
            const cm = this.easyMDE.codemirror;
            cm.replaceSelection(emoji);
        });
        container.appendChild(picker);
        document.body.appendChild(container);
        this._emojiPicker = container;

        const rect = button.getBoundingClientRect();

        container.style.top = `${rect.bottom + window.scrollY}px`;
        container.style.left = `${rect.left + window.scrollX}px`;

        setTimeout(() => document.addEventListener('click', this._closeEmojiOnOutside), 10);
    }

    _closeEmojiOnOutside = (e) => {
        if (!this._emojiPicker?.contains(e.target) && !e.target.closest('.fa-smile-o')) {
            this.hideEmojiPicker();
        }
    };

    hideEmojiPicker() {
        if (this._emojiPicker) {
            this._emojiPicker.remove();
            this._emojiPicker = null;
            document.removeEventListener('click', this._closeEmojiOnOutside);
        }
    }
}
