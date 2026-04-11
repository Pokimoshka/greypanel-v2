{% extends "base.tpl" %}

{% block title %}История операций{% endblock %}

{% block content %}
<h1>История операций</h1>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Дата</th>
            <th>Тип</th>
            <th>Сумма</th>
            <th>Описание</th>
        </tr>
    </thead>
    <tbody>
        {% for log in logs %}
        <tr>
            <td>{{ log.created_at|date('d.m.Y H:i') }}</td>
            <td>{{ log.type == 0 ? 'Пополнение' : 'Списание' }}</td>
            <td>{{ log.type == 0 ? '+' : '-' }} {{ log.amount }} ₽</td>
            <td>{{ log.title }}</td>
        </tr>
        {% else %}
        <tr><td colspan="4" class="text-center">Нет операций</td></tr>
        {% endfor %}
    </tbody>
</table>
{% include 'partials/pagination.tpl' with {
    'current': page,
    'total': total,
    'per_page': per_page,
    'url': '/balance/history'
} %}
{% endblock %}