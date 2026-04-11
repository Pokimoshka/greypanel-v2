{% extends "base.tpl" %}

{% block title %}{{ server ? 'Редактирование сервера' : 'Добавление сервера' }}{% endblock %}

{% block content %}
<h1>{{ server ? 'Редактирование сервера' : 'Добавление сервера' }}</h1>

<form method="post">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <div class="mb-3">
        <label class="form-label">Тип сервера</label>
        <select name="type" class="form-select" required>
            <option value="halflife" {{ server and server.type == 'halflife' ? 'selected' : '' }}>Counter-Strike 1.6 (halflife)</option>
            <option value="source" {{ server and server.type == 'source' ? 'selected' : '' }}>Counter-Strike: Source / GO (source)</option>
        </select>
        <div class="form-text">halflife – для CS 1.6, source – для CS:GO/CS2</div>
    </div>

    <div class="mb-3">
        <label class="form-label">IP адрес</label>
        <input type="text" name="ip" class="form-control" value="{{ server.ip|default('') }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Query порт (c_port)</label>
        <input type="number" name="c_port" class="form-control" value="{{ server.c_port|default('') }}" required>
        <div class="form-text">Порт для запросов SourceQuery (обычно 27015, 27016, или 22222 для CS 1.6)</div>
    </div>

    <div class="mb-3">
        <label class="form-label">q_port (опционально)</label>
        <input type="number" name="q_port" class="form-control" value="{{ server.q_port|default('') }}">
    </div>

    <div class="mb-3">
        <label class="form-label">s_port (опционально)</label>
        <input type="number" name="s_port" class="form-control" value="{{ server.s_port|default('') }}">
    </div>

    <div class="mb-3 form-check">
        <input type="checkbox" name="disabled" class="form-check-input" id="disabled" value="1" {{ server and server.disabled ? 'checked' : '' }}>
        <label class="form-check-label" for="disabled">Отключить (не показывать на сайте)</label>
    </div>

    <button type="submit" class="btn btn-primary">Сохранить</button>
    <a href="/admin/monitor/servers" class="btn btn-secondary">Отмена</a>
</form>
{% endblock %}