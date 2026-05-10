{% extends "base.tpl" %}

{% block title %}{{ trans('admin.theme_editor') }}: {{ active_theme }}{% endblock %}
{% block page_title %}{{ trans('admin.theme_editor') }}: {{ active_theme }}{% endblock %}

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
        min-height: 0;
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
    <div class="file-tree" id="fileTree">
        <div class="mb-2">
            <button class="btn btn-sm btn-success" onclick="ThemeEditor.createFile()"><i class="fas fa-file"></i> {{ trans('admin.new_file') }}</button>
            <button class="btn btn-sm btn-info" onclick="ThemeEditor.createFolder()"><i class="fas fa-folder"></i> {{ trans('admin.new_folder') }}</button>
        </div>
        <ul>
            <li><a class="folder" data-path="" onclick="ThemeEditor.loadDir('')"><i class="fas fa-home"></i> /</a>
                <ul id="dirContent">
                    {% include 'theme_editor/_file_tree_items.tpl' with {'files': files, 'current_dir': current_dir, 'parent_dir': parent_dir} %}
                </ul>
            </li>
        </ul>
    </div>

    <div class="editor-pane">
        <div class="editor-toolbar">
            <div>
                <a href="{{ url('admin/theme-editor') }}" class="btn btn-sm btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left"></i> {{ trans('admin.back') }}
                </a>
                <span id="currentFileLabel"><i class="fas fa-file-code"></i> {{ trans('admin.current_file') }}</span>
            </div>
            <div>
                <button class="btn btn-sm btn-secondary" onclick="ThemeEditor.insertBlock()"><i class="fas fa-puzzle-piece"></i> {{ trans('admin.insert_block') }}</button>
                <button class="btn btn-sm btn-primary" onclick="ThemeEditor.saveCurrentFile()"><i class="fas fa-save"></i> {{ trans('admin.save') }}</button>
                <button class="btn btn-sm btn-info" onclick="ThemeEditor.togglePreview()"><i class="fas fa-eye"></i> {{ trans('admin.preview') }}</button>
            </div>
        </div>
        <div class="editor-area" id="editorArea">
            <textarea id="editor" style="height: 100%; width: 100%;"></textarea>
        </div>
        <div class="preview-area" id="previewArea" style="display: none;">
            <h6>{{ trans('admin.preview') }}:</h6>
            <iframe id="previewFrame" style="width: 100%; height: 250px; border: none;"></iframe>
        </div>
    </div>
</div>

<div class="modal fade" id="blocksModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5>{{ trans('admin.blocks_modal') }}</h5><button type="button" class="btn-close" onclick="closeModal()"></button></div>
            <div class="modal-body" id="blocksList">{{ trans('admin.loading') }}...</div>
            <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal()">{{ trans('admin.close') }}</button></div>
        </div>
    </div>
</div>
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}