{% extends "base.tpl" %}

{% block title %}Разделы категории "{{ category.title }}"{% endblock %}

{% block content %}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Разделы: {{ category.title }}</h1>
    <a href="/admin/forum/categories/{{ category.id }}/forums/add" class="btn btn-primary">Добавить раздел</a>
</div>

<table class="table table-striped" id="sortable-table">
    <thead><tr><th>ID</th><th>Иконка</th><th>Название</th><th>Описание</th><th>Порядок</th><th>Действия</th></tr></thead>
    <tbody id="sortable-forums">
        {% for forum in forums %}
        <tr data-id="{{ forum.id }}">
            <td>{{ forum.id }}</td>
            <td><i class="{{ forum.icon }}"></i></td>
            <td>{{ forum.title }}</td>
            <td>{{ forum.description }}</td>
            <td class="sort-order">{{ forum.sort_order }}</td>
            <td>
                <a href="/admin/forum/categories/{{ category.id }}/forums/edit/{{ forum.id }}" class="btn btn-sm btn-primary">Ред.</a>
                <a href="/admin/forum/categories/{{ category.id }}/forums/delete/{{ forum.id }}" class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')">Удалить</a>
            </td>
        </tr>
        {% endfor %}
    </tbody>
</table>
<button id="save-order" class="btn btn-secondary">Сохранить порядок</button>
<a href="/admin/forum/categories" class="btn btn-link">← Назад к категориям</a>
{% endblock %}

{% block scripts %}
<script>
document.getElementById('save-order')?.addEventListener('click', function() {
    let rows = document.querySelectorAll('#sortable-forums tr');
    let order = {};
    rows.forEach((row, idx) => { order[row.dataset.id] = idx; });
    fetch('/admin/forum/forums/sort', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'order=' + JSON.stringify(order)
    }).then(() => location.reload());
});
</script>
{% endblock %}