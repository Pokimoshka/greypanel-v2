{% extends "base.tpl" %}

{% block title %}{{ trans('admin.theme_editor') }}{% endblock %}
{% block page_title %}{{ trans('admin.theme_editor') }}: {{ active_theme }}{% endblock %}

{% block content %}
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span><i class="fas fa-folder-open me-2"></i>{{ trans('admin.current_folder') }}: /{{ current_dir }}</span>
        <div>
            <button class="btn btn-sm btn-success" onclick="createFile()"><i class="fas fa-file"></i> {{ trans('admin.new_file') }}</button>
            <button class="btn btn-sm btn-info" onclick="createFolder()"><i class="fas fa-folder"></i> {{ trans('admin.new_folder') }}</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>{{ trans('admin.name') }}</th><th>{{ trans('admin.size') }}</th><th>{{ trans('admin.modified') }}</th><th>{{ trans('admin.actions') }}</th></tr>
            </thead>
            <tbody>
                {% if current_dir %}
                <tr>
                    <td><a href="?dir={{ parent_dir }}"><i class="fas fa-level-up-alt me-2"></i>..</a></td>
                    <td></td><td></td><td></td>
                </tr>
                {% endif %}
                {% for item in files %}
                <tr>
                    <td>
                        {% if item.is_dir %}
                            <a href="?dir={{ item.path }}"><i class="fas fa-folder me-2"></i>{{ item.name }}</a>
                        {% else %}
                            <a href="/admin/theme-editor/edit?file={{ item.path }}"><i class="fas fa-file-code me-2"></i>{{ item.name }}</a>
                        {% endif %}
                    </td>
                    <td>{% if not item.is_dir %}{{ (item.size / 1024)|round(1) }} KB{% endif %}</td>
                    <td>{{ item.modified }}</td>
                    <td>
                        <button class="btn btn-sm btn-danger" onclick="ThemeEditor.deleteItem('{{ item.path }}', {{ item.is_dir ? 'true' : 'false' }})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                {% endfor %}
            </tbody>
        </table>
    </div>
</div>
{% endblock %}

{% block scripts %}
    {{ parent() }}
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function createFile() {
    let name = prompt('{{ trans("admin.enter_file_name") }}');
    if (!name) return;
    let form = new FormData();
    form.append('dir', '{{ current_dir }}');
    form.append('name', name);
    form.append('type', 'file');
    form.append('csrf_token', csrfToken);
    fetch('/admin/theme-editor/create', { method: 'POST', body: form })
        .then(r => r.json())
        .then(data => { if(data.success) location.reload(); else Toast.error('{{ trans("admin.error_occurred") }}'); });
}
function createFolder() {
    let name = prompt('{{ trans("admin.enter_folder_name") }}');
    if (!name) return;
    let form = new FormData();
    form.append('dir', '{{ current_dir }}');
    form.append('name', name);
    form.append('type', 'folder');
    form.append('csrf_token', csrfToken);
    fetch('/admin/theme-editor/create', { method: 'POST', body: form })
        .then(r => r.json())
        .then(data => { if(data.success) location.reload(); else Toast.error('{{ trans("admin.error_occurred") }}'); });
}
function deleteItem(path, isDir) {
    if (!confirm('{{ trans("admin.delete_item_confirm") }}'.replace('{path}', path))) return;
    let form = new FormData();
    form.append('file', path);
    form.append('csrf_token', csrfToken);
    fetch('/admin/theme-editor/delete', { method: 'POST', body: form })
        .then(r => r.json())
        .then(data => { if(data.success) location.reload(); else Toast.error('{{ trans("admin.error_occurred") }}'); });
}
</script>
{% endblock %}