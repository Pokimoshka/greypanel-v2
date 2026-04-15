{% extends "base.tpl" %}

{% block title %}Категории форума{% endblock %}
{% block page_title %}Категории{% endblock %}

{% block content %}
<div class="d-flex justify-content-end mb-3">
    <a href="{{ url('admin/forum/categories/add') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Добавить</a>
</div>

<div class="card p-0" x-data="sortableList('/admin/forum/categories/sort', '{{ csrf_token }}')">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>ID</th><th>Название</th><th>Порядок</th><th></th></tr></thead>
            <tbody x-ref="sortableList">
                {% for cat in categories %}
                <tr data-id="{{ cat.id }}" style="cursor: grab;">
                    <td><a href="{{ url('admin/forum/categories/' ~ cat.id ~ '/forums') }}">{{ cat.id }}</a></td>
                    <td><a href="{{ url('admin/forum/categories/' ~ cat.id ~ '/forums') }}">{{ cat.title }}</a></td>
                    <td>{{ cat.sort_order }}</td>
                    <td>
                        <a href="{{ url('admin/forum/categories/edit/' ~ cat.id) }}" class="btn btn-sm btn-outline-primary">Ред.</a>
                        <form method="post" action="{{ url('admin/forum/categories/delete/' ~ cat.id) }}" style="display:inline;" onsubmit="return confirm('Удалить?');">
                            <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                            <button class="btn btn-sm btn-outline-danger">Удалить</button>
                        </form>
                    </td>
                </tr>
                {% endfor %}
            </tbody>
        </table>
    </div>
    <div class="card-footer text-secondary">
        <i class="fas fa-grip-vertical me-1"></i>Перетаскивайте строки для изменения порядка
    </div>
</div>
{% endblock %}