{% extends "base.tpl" %}

{% block title %}Настройки AMXBans{% endblock %}

{% block content %}
<h1>Настройки AMXBans</h1>
<form method="post">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <div class="mb-3 form-check">
        <input type="checkbox" name="amxbans_active" class="form-check-input" id="active" value="1" {{ settings.amxbans_active == '1' ? 'checked' : '' }}>
        <label class="form-check-label" for="active">Включить бан-лист</label>
    </div>
    <div class="mb-3">
        <label class="form-label">Хост MySQL (AMXBans)</label>
        <input type="text" name="amxbans_host" class="form-control" value="{{ settings.amxbans_host }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Имя базы данных</label>
        <input type="text" name="amxbans_db" class="form-control" value="{{ settings.amxbans_db }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Пользователь MySQL</label>
        <input type="text" name="amxbans_user" class="form-control" value="{{ settings.amxbans_user }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Пароль MySQL (оставьте пустым, чтобы не менять)</label>
        <input type="password" name="amxbans_pass" class="form-control" placeholder="******">
    </div>
    <div class="mb-3">
        <label class="form-label">Префикс таблиц (например, amx_)</label>
        <input type="text" name="amxbans_prefix" class="form-control" value="{{ settings.amxbans_prefix }}">
    </div>
    <div class="mb-3">
        <label class="form-label">ID форума для заявок на разбан</label>
        <select name="amxbans_forum" class="form-select">
            <option value="0">-- Выберите форум --</option>
            {% for forum in forums %}
            <option value="{{ forum.id }}" {{ forum.id == settings.amxbans_forum ? 'selected' : '' }}>{{ forum.title }}</option>
            {% endfor %}
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Стоимость платного разбана (руб.)</label>
        <input type="number" name="buy_razban" class="form-control" value="{{ settings.buy_razban }}">
    </div>
    <button type="submit" class="btn btn-primary">Сохранить</button>
</form>
{% endblock %}