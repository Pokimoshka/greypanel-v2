{% extends "base.tpl" %}

{% block title %}Управление серверами VIP{% endblock %}

{% block content %}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Серверы привилегий</h1>
    <a href="/admin/vip/servers/add" class="btn btn-primary">
        <i class="fas fa-plus"></i> Добавить сервер
    </a>
</div>

<table class="table table-bordered table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th width="50">ID</th>
            <th>Название</th>
            <th>IP:Порт</th>
            <th>Тип</th>
            <th width="280">Действия</th>
        </tr>
    </thead>
    <tbody>
        {% for server in servers %}
        <tr>
            <td>{{ server.id }}</td>
            <td>{{ server.server_name }}</td>
            <td><code>{{ server.server_ip }}:{{ server.server_port }}</code></td>
            <td>
                {% if server.type == 0 %} AMX
                {% elseif server.type == 1 %} FTP
                {% else %} SQL {% endif %}
            </td>
            <td>
                <a href="/admin/vip/servers/{{ server.id }}/test" class="btn btn-sm btn-info" title="Тест подключения">
                    <i class="fas fa-plug"></i>
                </a>
                <a href="/admin/vip/servers/edit/{{ server.id }}" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit"></i>
                </a>
                <a href="/admin/vip/servers/{{ server.id }}/privileges" class="btn btn-sm btn-success">
                    <i class="fas fa-star"></i> Прив.
                </a>
                <a href="/admin/vip/servers/delete/{{ server.id }}" class="btn btn-sm btn-danger" onclick="return confirm('Удалить сервер?');">
                    <i class="fas fa-trash"></i>
                </a>
            </td>
        </tr>
        {% else %}
        <tr>
            <td colspan="5" class="text-center text-muted py-3">Серверы не добавлены</td>
        </tr>
        {% endfor %}
    </tbody>
</table>
{% endblock %}