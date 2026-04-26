import { EditorView, basicSetup } from 'codemirror';
import { html } from '@codemirror/lang-html';
import { css } from '@codemirror/lang-css';
import { javascript } from '@codemirror/lang-javascript';
import { oneDark } from '@codemirror/theme-one-dark';

const ThemeEditor = {
    currentFile: null,
    editor: null,
    previewVisible: false,

    initEditor(content, mode) {
        const textarea = document.getElementById('editor');
        textarea.value = content;
        textarea.style.display = 'none';
        const container = document.createElement('div');
        container.style.display = 'flex';
        textarea.parentNode.insertBefore(container, textarea.nextSibling);

        const resizeObserver = new ResizeObserver(() => {
            const rect = container.parentElement.getBoundingClientRect();
            container.style.height = rect.height + 'px';
        });
        resizeObserver.observe(container.parentElement);

        let language;
        switch (mode) {
            case 'css': language = css(); break;
            case 'js': language = javascript(); break;
            default: language = html();
        }

        const view = new EditorView({
            doc: content,
            extensions: [
                basicSetup,
                oneDark,
                language,
                EditorView.updateListener.of((update) => {
                    if (update.docChanged) {
                        textarea.value = view.state.doc.toString();
                        if (ThemeEditor.previewVisible) ThemeEditor.updatePreview();
                    }
                })
            ],
            parent: container
        });
        ThemeEditor.editor = view;
        return view;
    },

    loadFile(filePath) {
        ThemeEditor.currentFile = filePath;
        document.getElementById('currentFileLabel').innerHTML = `<i class="fas fa-file-code"></i> ${filePath}`;
        fetch(`/admin/theme-editor/get-file?file=${encodeURIComponent(filePath)}`)
            .then(res => res.json())
            .then(data => {
                if (data.content !== undefined) {
                    if (ThemeEditor.editor) {
                        ThemeEditor.editor.dispatch({
                            changes: { from: 0, to: ThemeEditor.editor.state.doc.length, insert: data.content }
                        });
                    } else {
                        ThemeEditor.initEditor(data.content, data.ext);
                    }
                } else {
                    window.dispatchEvent(new CustomEvent('toast:error', { detail: 'Ошибка загрузки файла' }));
                }
            });
    },

    saveCurrentFile() {
        if (!ThemeEditor.currentFile || !ThemeEditor.editor) return;
        const content = ThemeEditor.editor.state.doc.toString();
        const formData = new FormData();
        formData.append('file', ThemeEditor.currentFile);
        formData.append('content', content);
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);
        fetch('/admin/theme-editor/save-file', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) window.dispatchEvent(new CustomEvent('toast:success', { detail: 'Файл сохранён' }));
                else window.dispatchEvent(new CustomEvent('toast:error', { detail: 'Ошибка' }));
            });
    },

    togglePreview() {
        ThemeEditor.previewVisible = !ThemeEditor.previewVisible;
        const previewArea = document.getElementById('previewArea');
        if (ThemeEditor.previewVisible) {
            previewArea.style.display = 'block';
            ThemeEditor.updatePreview();
        } else {
            previewArea.style.display = 'none';
        }
    },

    updatePreview() {
        if (!ThemeEditor.editor || !ThemeEditor.previewVisible) return;
        const content = ThemeEditor.editor.state.doc.toString();
        const iframe = document.getElementById('previewFrame');
        const doc = iframe.contentDocument || iframe.contentWindow.document;
        doc.open();
        doc.write(content);
        doc.close();
    },

    loadDir(dir) {
        fetch(`/admin/theme-editor?dir=${encodeURIComponent(dir)}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTree = doc.querySelector('#dirContent').innerHTML;
                document.getElementById('dirContent').innerHTML = newTree;
                history.pushState({}, '', `/admin/theme-editor?dir=${dir}`);
            });
    },

    // Блоки
    blocksData: null,

    async loadBlocks() {
        const res = await fetch('/blocks.json');
        if (res.ok) {
            ThemeEditor.blocksData = await res.json();
            ThemeEditor.renderBlocksModal();
        } else {
            document.getElementById('blocksList').innerHTML = '<div class="alert alert-danger">Ошибка загрузки блоков</div>';
        }
    },

    renderBlocksModal() {
        if (!ThemeEditor.blocksData) return;
        let html = '';
        for (let category in ThemeEditor.blocksData) {
            html += `<h6 class="mt-3">${category}</h6><div class="row">`;
            for (let block of ThemeEditor.blocksData[category]) {
                html += `<div class="col-md-6 mb-2">
                            <button class="btn btn-outline-primary w-100 text-start" onclick="ThemeEditor.insertBlockCode(\`${ThemeEditor.escapeJs(block.code)}\`)">
                                <i class="fas fa-code me-2"></i>${ThemeEditor.escapeHtml(block.name)}
                            </button>
                         </div>`;
            }
            html += `</div>`;
        }
        document.getElementById('blocksList').innerHTML = html;
    },

    escapeJs(str) { return str.replace(/`/g, '\\`').replace(/\$/g, '\\$'); },
    escapeHtml(str) { return str.replace(/[&<>]/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' })[m] || m); },

    insertBlockCode(code) {
        if (!ThemeEditor.editor) return;
        const transaction = ThemeEditor.editor.state.update({
            changes: { from: ThemeEditor.editor.state.selection.main.head, insert: code }
        });
        ThemeEditor.editor.dispatch(transaction);
        ThemeEditor.closeModal();
    },

    openModal() {
        const modalEl = document.getElementById('blocksModal');
        modalEl.style.display = 'block';
        modalEl.classList.add('show');
        document.body.classList.add('modal-open');
        if (!document.querySelector('.modal-backdrop')) {
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }
    },

    closeModal() {
        const modalEl = document.getElementById('blocksModal');
        modalEl.style.display = 'none';
        modalEl.classList.remove('show');
        document.body.classList.remove('modal-open');
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) backdrop.remove();
    },

    insertBlock() {
        if (!ThemeEditor.blocksData) {
            ThemeEditor.loadBlocks().then(() => ThemeEditor.openModal());
        } else {
            ThemeEditor.openModal();
        }
    },

    getCurrentDir() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('dir') || '';
    },

    createFile() {
        let name = prompt('Имя файла (с расширением, например header.tpl):');
        if (!name) return;
        let formData = new FormData();
        formData.append('dir', ThemeEditor.getCurrentDir());
        formData.append('name', name);
        formData.append('type', 'file');
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);
        fetch('/admin/theme-editor/create', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => { if (data.success) location.reload(); else window.dispatchEvent(new CustomEvent('toast:error', { detail: 'Ошибка' })); });
    },

    createFolder() {
        let name = prompt('Имя папки:');
        if (!name) return;
        let formData = new FormData();
        formData.append('dir', ThemeEditor.getCurrentDir());
        formData.append('name', name);
        formData.append('type', 'folder');
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);
        fetch('/admin/theme-editor/create', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => { if (data.success) location.reload(); else window.dispatchEvent(new CustomEvent('toast:error', { detail: 'Ошибка' })); });
    },

    deleteItem(path, isDir) {
        if (!confirm('Удалить ' + (isDir ? 'папку' : 'файл') + ' ' + path + '?')) return;
        let formData = new FormData();
        formData.append('file', path);
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);
        fetch('/admin/theme-editor/delete', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => { if (data.success) location.reload(); else window.dispatchEvent(new CustomEvent('toast:error', { detail: 'Ошибка' })); });
    }
};

window.ThemeEditor = ThemeEditor;