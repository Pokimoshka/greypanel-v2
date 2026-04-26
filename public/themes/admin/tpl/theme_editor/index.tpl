{% extends "base.tpl" %}

{% block title %}Редактор шаблона{% endblock %}
{% block page_title %}Редактор темы: {{ active_theme }}{% endblock %}

{% block content %}
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span><i class="fas fa-folder-open me-2"></i>Текущая папка: /{{ current_dir }}</span>
        <div>
            <button class="btn btn-sm btn-success" onclick="createFile()"><i class="fas fa-file"></i> Новый файл</button>
            <button class="btn btn-sm btn-info" onclick="createFolder()"><i class="fas fa-folder"></i> Новая папка</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Имя</th><th>Размер</th><th>Изменён</th><th></th></tr>
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

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function createFile() {
    let name = prompt('Имя файла (с расширением, например header.tpl):');
    if (!name) return;
    let form = new FormData();
    form.append('dir', '{{ current_dir }}');
    form.append('name', name);
    form.append('type', 'file');
    form.append('csrf_token', csrfToken);
    fetch('/admin/theme-editor/create', { method: 'POST', body: form })
        .then(r => r.json())
        .then(data => { if(data.success) location.reload(); else window.dispatchEvent(new CustomEvent('toast:error', { detail: 'Ошибка' })); });
}
function createFolder() {
    let name = prompt('Имя папки:');
    if (!name) return;
    let form = new FormData();
    form.append('dir', '{{ current_dir }}');
    form.append('name', name);
    form.append('type', 'folder');
    form.append('csrf_token', csrfToken);
    fetch('/admin/theme-editor/create', { method: 'POST', body: form })
        .then(r => r.json())
        .then(data => { if(data.success) location.reload(); else window.dispatchEvent(new CustomEvent('toast:error', { detail: 'Ошибка' })); });
}
function deleteItem(path, isDir) {
    if (!confirm('Удалить ' + (isDir ? 'папку' : 'файл') + ' ' + path + '?')) return;
    let form = new FormData();
    form.append('file', path);
    form.append('csrf_token', csrfToken);
    fetch('/admin/theme-editor/delete', { method: 'POST', body: form })
        .then(r => r.json())
        .then(data => { if(data.success) location.reload(); else window.dispatchEvent(new CustomEvent('toast:error', { detail: 'Ошибка' })); });
}
</script>
{% endblock %}