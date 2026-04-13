{% extends "base.tpl" %}

{% block title %}Привилегии сервера {{ server.server_name }}{% endblock %}

{% block content %}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Привилегии: {{ server.server_name }}</h1>
    <a href="/admin/vip/servers/{{ server.id }}/privileges/add" class="btn btn-primary">
        <i class="fas fa-plus"></i> Добавить привилегию
    </a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Флаги</th>
            <th>Цена за день (₽)</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        {% for priv in privileges %}
        <tr>
            <td>{{ priv.id }}</td>
            <td>{{ priv.title }}</td>
            <td><code>{{ priv.flags }}</code></td>
            <td>{{ priv.price_per_day }} ₽</td>
            <td>
                <a href="/admin/vip/servers/{{ server.id }}/privileges/edit/{{ priv.id }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit"></i>
                </a>
                <form method="post" action="/admin/vip/servers/{{ server.id }}/privileges/delete/{{ priv.id }}" style="display:inline;" onsubmit="return confirm('Удалить привилегию?');">
                    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                </form>
            </td>
        </tr>
        {% else %}
        <tr>
            <td colspan="5" class="text-center text-muted">Нет привилегий</td>
        </tr>
        {% endfor %}
    </tbody>
</table>

<a href="/admin/vip/servers" class="btn btn-secondary mt-3">
    <i class="fas fa-arrow-left"></i> Назад к серверам
</a>
{% endblock %}