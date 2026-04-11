{% extends "base.tpl" %}

{% block title %}Логи действий{% endblock %}

{% block content %}
<h1>Логи действий</h1>
<table class="table table-striped">
    <thead>
        <tr><th>ID</th><th>Пользователь</th><th>Действие</th><th>Детали</th><th>IP</th><th>Дата</th></tr>
    </thead>
    <tbody>
        {% for log in logs %}
        <tr>
            <td>{{ log.id }}</td>
            <td>{{ log.username ?? 'Гость' }} ({# log.user_id #})</td>
            <td>{{ log.action }}</td>
            <td>{{ log.details }}</td>
            <td>{{ log.ip }}</td>
            <td>{{ log.created_at|date('d.m.Y H:i:s') }}</td>
        </tr>
        {% endfor %}
    </tbody>
</table>
{% include 'partials/pagination.tpl' with {
    'current': page,
    'total': total,
    'per_page': per_page,
    'url': '/admin/logs',
    'params': {}
} %}
{% endblock %}