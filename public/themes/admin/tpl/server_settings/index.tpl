{% extends "base.tpl" %}

{% block title %}Настройка серверов{% endblock %}

{% block content %}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Серверы</h1>
    <a href="{{ url('admin/server-settings/add') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Добавить сервер
    </a>
</div>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>IP:Порт</th>
            <th>Привилегии</th>
            <th>Статистика</th>
            <th>Статус</th>
            <th style="width: 200px;">Действия</th>
        </tr>
    </thead>
    <tbody>
        {% for server in servers %}
        <tr>
            <td>{{ server.id }}</td>
            <td><code>{{ server.ip }}:{{ server.c_port }}</code></td>
            <td>
                {% if server.privilege_storage == 1 %}
                    <span class="badge bg-secondary">users.ini</span>
                {% elseif server.privilege_storage == 2 %}
                    <span class="badge bg-info">AmxBans</span>
                {% elseif server.privilege_storage == 3 %}
                    <span class="badge bg-primary">AmxBans + users.ini</span>
                {% else %}
                    <span class="badge bg-light text-dark">Не задано</span>
                {% endif %}
            </td>
            <td>
                {% if server.stats_engine == 1 %}
                    <span class="badge bg-secondary">CSStats</span>
                {% elseif server.stats_engine == 2 %}
                    <span class="badge bg-info">AES</span>
                {% elseif server.stats_engine == 3 %}
                    <span class="badge bg-primary">CSStats + AES</span>
                {% else %}
                    <span class="badge bg-light text-dark">Не задано</span>
                {% endif %}
            </td>
            <td>
                {% if server.status == 0 %}
                    <span class="badge bg-success">Онлайн</span>
                {% else %}
                    <span class="badge bg-danger">Оффлайн</span>
                {% endif %}
            </td>
            <td>
                <a href="{{ url('admin/server-settings/edit/' ~ server.id) }}" class="btn btn-sm btn-primary" title="Редактировать">
                    <i class="fas fa-edit"></i>
                </a>
                <a href="{{ url('admin/server-settings/test/' ~ server.id) }}" class="btn btn-sm btn-info" title="Проверить соединение">
                    <i class="fas fa-sync-alt"></i>
                </a>
                <form method="post" action="{{ url('admin/server-settings/delete/' ~ server.id) }}" style="display:inline;" onsubmit="return confirm('Удалить сервер?');">
                    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                    <button type="submit" class="btn btn-sm btn-danger" title="Удалить">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </td>
        </tr>
        {% else %}
        <tr>
            <td colspan="6" class="text-center text-muted py-3">Нет добавленных серверов</td>
        </tr>
        {% endfor %}
    </tbody>
</table>
{% endblock %}