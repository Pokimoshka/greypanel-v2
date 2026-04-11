{% extends "base.tpl" %}

{% block title %}{{ server ? 'Редактировать' : 'Добавить' }} сервер{% endblock %}

{% block content %}
<div class="card">
    <div class="card-header">
        <h3>{{ server ? 'Редактирование сервера' : 'Новый сервер' }}</h3>
    </div>
    <div class="card-body">
        <form method="post">
            <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
            <div class="mb-3">
                <label class="form-label">Название сервера</label>
                <input type="text" name="server_name" class="form-control" value="{{ server.server_name ?? '' }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">IP адрес</label>
                <input type="text" name="server_ip" class="form-control" value="{{ server.server_ip ?? '' }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Порт</label>
                <input type="number" name="server_port" class="form-control" value="{{ server.server_port ?? '' }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Тип сервера</label>
                <select name="type" class="form-select">
                    <option value="0" {{ server.type == 0 ? 'selected' : '' }}>AMX Admin (MySQL)</option>
                    <option value="1" {{ server.type == 1 ? 'selected' : '' }}>FTP (users.ini)</option>
                    <option value="2" {{ server.type == 2 ? 'selected' : '' }}>SQL (users_sql)</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Хост (IP / FTP адрес)</label>
                <input type="text" name="host" class="form-control" value="{{ server.host ?? '' }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Пользователь</label>
                <input type="text" name="user" class="form-control" value="{{ server.user ?? '' }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Пароль</label>
                <input type="password" name="password" class="form-control">
                <div class="form-text">Оставьте пустым, чтобы не менять</div>
            </div>
            <div class="mb-3">
                <label class="form-label">База данных / путь к users.ini</label>
                <input type="text" name="database" class="form-control" value="{{ server.database ?? '' }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Префикс (для MySQL) / пусто</label>
                <input type="text" name="prefix" class="form-control" value="{{ server.prefix ?? '' }}">
            </div>
            <div class="mb-3">
                <label class="form-label">AMX ID (для AMX типа)</label>
                <input type="number" name="amx_id" class="form-control" value="{{ server.amx_id ?? 0 }}">
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
            <a href="/admin/vip/servers" class="btn btn-secondary">Отмена</a>
        </form>
    </div>
</div>
{% endblock %}