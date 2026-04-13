{% extends "base.tpl" %}

{% block title %}Категории форума{% endblock %}

{% block content %}
<div class="d-flex justify-content-between mb-3">
    <h1>Категории</h1>
    <a href="{{ url('admin/forum/categories/add') }}" class="btn btn-primary">Добавить</a>
</div>

<div x-data="sortableList('/admin/forum/categories/sort', '{{ csrf_token }}')">
    <table class="table">
        <thead><tr><th>ID</th><th>Название</th><th>Порядок</th><th></th></tr></thead>
        <tbody x-ref="sortableList">
            {% for cat in categories %}
            <tr data-id="{{ cat.id }}">
                <td><a href="{{ url('admin/forum/categories/' ~ cat.id ~ '/forums') }}">{{ cat.id }}</a></td>
                <td><a href="{{ url('admin/forum/categories/' ~ cat.id ~ '/forums') }}">{{ cat.title }}</a></td>
                <td>{{ cat.sort_order }}</td>
                <td>
                    <a href="{{ url('admin/forum/categories/edit/' ~ cat.id) }}" class="btn btn-sm btn-primary">Ред.</a>
                    <form method="post" action="{{ url('admin/forum/categories/delete/' ~ cat.id) }}" style="display:inline;" onsubmit="return confirm('Удалить?');">
                        <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                        <button type="submit" class="btn btn-sm btn-danger">Удалить</button>
                    </form>
            </tr>
            {% endfor %}
        </tbody>
    </table>
    <p class="text-muted"><i class="fas fa-grip-vertical"></i> Перетаскивайте строки для изменения порядка</p>
</div>
{% endblock %}