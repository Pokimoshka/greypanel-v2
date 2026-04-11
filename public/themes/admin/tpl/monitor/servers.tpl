{% extends "base.tpl" %}

{% block title %}Управление серверами мониторинга{% endblock %}

{% block content %}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Серверы мониторинга</h1>
    <a href="/admin/monitor/servers/add" class="btn btn-primary">Добавить сервер</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Тип</th>
            <th>IP адрес</th>
            <th>Порт</th>
            <th>Статус</th>
            <th>Последнее обновление</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        {% for server in servers %}
        <tr>
            <td>{{ server.id }}</td>
            <td>{{ server.type == 'halflife' ? 'CS 1.6' : 'Source' }}</td>
            <td>{{ server.ip }}</td>
            <td>{{ server.c_port }}</td>
            <td>
                {% if server.status == 0 %}
                    <span class="badge bg-success">Онлайн</span>
                {% else %}
                    <span class="badge bg-danger">Оффлайн</span>
                {% endif %}
            </td>
            <td>{{ server.cache_time ? server.cache_time|date('d.m.Y H:i:s') : 'никогда' }}</td>
            <td>
                <a href="/admin/monitor/servers/edit/{{ server.id }}" class="btn btn-sm btn-primary">Ред.</a>
                <a href="/admin/monitor/servers/refresh/{{ server.id }}" class="btn btn-sm btn-info">Обновить</a>
                <a href="/admin/monitor/servers/delete/{{ server.id }}" class="btn btn-sm btn-danger" onclick="return confirm('Удалить сервер?')">Удалить</a>
            </td>
        </tr>
        {% else %}
            <tr><td colspan="7" class="text-center">Нет добавленных серверов</td></tr>
        {% endfor %}
    </tbody>
</tr>
{% endblock %}