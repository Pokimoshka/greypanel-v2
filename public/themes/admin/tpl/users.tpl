{% extends "base.tpl" %}

{% block title %}Управление пользователями{% endblock %}

{% block content %}
<h1>Пользователи</h1>

<form method="get" class="mb-3">
    <div class="input-group">
        <input type="text" name="search" class="form-control" placeholder="Поиск по нику или email" value="{{ search }}">
        <button class="btn btn-primary" type="submit">Искать</button>
    </div>
</form>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Ник</th>
            <th>Email</th>
            <th>Группа</th>
            <th>Баланс</th>
            <th>Забанен</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        {% for user in users %}
        <tr>
            <td>{{ user.id }}</td>
            <td>{{ user.username }}</td>
            <td>{{ user.email }}</td>
            <td>{{ user.group }}</td>
            <td>{{ user.money }}</td>
            <td>{{ user.banned ? 'Да' : 'Нет' }}</td>
            <td>
                <a href="/admin/users/edit/{{ user.id }}" class="btn btn-sm btn-primary">Редактировать</a>
            </td>
        </tr>
        {% endfor %}
    </tbody>
</table>

{# Пагинация #}
{% include 'partials/pagination.tpl' with {
    'current': page,
    'total': total,
    'per_page': per_page,
    'url': '/admin/users',
    'params': {'search': search}
} %}
{% endblock %}