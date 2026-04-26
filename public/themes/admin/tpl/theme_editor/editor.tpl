{% extends "base.tpl" %}

{% block title %}Редактор темы: {{ active_theme }}{% endblock %}
{% block page_title %}Редактор темы: {{ active_theme }}{% endblock %}

{% block head %}
{{ parent() }}
<style>
    .editor-container {
        display: flex;
        gap: 1.5rem;
        height: calc(100vh - 200px);
        min-height: 500px;
    }
    .file-tree {
        width: 280px;
        flex-shrink: 0;
        background: var(--admin-card-bg);
        border: 1px solid var(--admin-border);
        border-radius: 0.75rem;
        overflow-y: auto;
        padding: 1rem;
    }
    .file-tree ul {
        list-style: none;
        padding-left: 1.2rem;
        margin: 0;
    }
    .file-tree li {
        margin: 0.25rem 0;
    }
    .file-tree a {
        color: var(--admin-text);
        text-decoration: none;
        display: inline-block;
        padding: 0.2rem 0.5rem;
        border-radius: 0.375rem;
        cursor: pointer;
    }
    .file-tree a:hover {
        background: var(--admin-primary);
        color: white;
    }
    .file-tree .folder {
        font-weight: 600;
    }
    .file-tree .file {
        font-family: monospace;
    }
    .editor-pane {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        min-width: 0;
        height: 100%;
    }
    .editor-toolbar {
        flex-shrink: 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--admin-card-bg);
        border: 1px solid var(--admin-border);
        border-radius: 0.75rem;
        padding: 0.5rem 1rem;
    }
    .editor-area {
        flex: 1;
        min-height: 0;  /* позволяет скроллить внутренности */
        background: var(--admin-card-bg);
        border: 1px solid var(--admin-border);
        border-radius: 0.75rem;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .preview-area {
        flex-shrink: 0;
        background: var(--admin-card-bg);
        border: 1px solid var(--admin-border);
        border-radius: 0.75rem;
        padding: 1rem;
        max-height: 300px;
        overflow: auto;
    }
    .editor-area .cm-editor {
        flex: 1;
        height: auto;
        overflow: auto;
    }
    .resize-handle {
        cursor: row-resize;
        text-align: center;
        padding: 0.25rem;
        background: var(--admin-border);
        margin: 0.5rem 0;
        border-radius: 0.5rem;
    }
</style>
{% endblock %}

{% block content %}
<div class="editor-container">
    <!-- Левая панель: дерево файлов -->
    <div class="file-tree" id="fileTree">
        <div class="mb-2">
            <button class="btn btn-sm btn-success" onclick="ThemeEditor.createFile()"><i class="fas fa-file"></i> Файл</button>
            <button class="btn btn-sm btn-info" onclick="ThemeEditor.createFolder()"><i class="fas fa-folder"></i> Папка</button>
        </div>
        <ul>
            <li><a class="folder" data-path="" onclick="ThemeEditor.loadDir('')"><i class="fas fa-home"></i> /</a>
                <ul id="dirContent">
                    {% include 'theme_editor/_file_tree_items.tpl' with {'files': files, 'current_dir': current_dir, 'parent_dir': parent_dir} %}
                </ul>
            </li>
        </ul>
    </div>

    <!-- Правая панель: редактор и превью -->
    <div class="editor-pane">
        <div class="editor-toolbar">
            <span id="currentFileLabel"><i class="fas fa-file-code"></i> Не выбран файл</span>
            <div>
                <button class="btn btn-sm btn-secondary" onclick="ThemeEditor.insertBlock()"><i class="fas fa-puzzle-piece"></i> Блок</button>
                <button class="btn btn-sm btn-primary" onclick="ThemeEditor.saveCurrentFile()"><i class="fas fa-save"></i> Сохранить</button>
                <button class="btn btn-sm btn-info" onclick="ThemeEditor.togglePreview()"><i class="fas fa-eye"></i> Превью</button>
            </div>
        </div>
        <div class="editor-area" id="editorArea">
            <textarea id="editor" style="height: 100%; width: 100%;"></textarea>
        </div>
        <div class="preview-area" id="previewArea" style="display: none;">
            <h6>Предпросмотр:</h6>
            <iframe id="previewFrame" style="width: 100%; height: 250px; border: none;"></iframe>
        </div>
    </div>
</div>

<!-- Модальное окно блоков (как раньше) -->
<div class="modal fade" id="blocksModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5>Вставить блок</h5><button type="button" class="btn-close" onclick="closeModal()"></button></div>
            <div class="modal-body" id="blocksList">Загрузка...</div>
            <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal()">Закрыть</button></div>
        </div>
    </div>
</div>
{% endblock %}