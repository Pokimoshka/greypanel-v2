{% extends "base.tpl" %}

{% block title %}Логи действий{% endblock %}
{% block page_title %}Логи действий{% endblock %}

{% block content %}
<div class="card p-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>ID</th><th>Пользователь</th><th>Действие</th><th>Детали</th><th>IP</th><th>Дата</th></tr>
            </thead>
            <tbody>
                {% for log in logs %}
                <tr>
                    <td>{{ log.id }}</td>
                    <td>{{ log.username ?? 'Гость' }}</td>
                    <td>{{ log.action }}</td>
                    <td>{{ log.details }}</td>
                    <td>{{ log.ip }}</td>
                    <td>{{ log.created_at|date('d.m.Y H:i') }}</td>
                </tr>
                {% endfor %}
            </tbody>
        </table>
    </div>
</div>
{% include 'partials/pagination.tpl' with {'current': page, 'total': total, 'per_page': per_page, 'url': '/admin/logs'} %}
{% endblock %}