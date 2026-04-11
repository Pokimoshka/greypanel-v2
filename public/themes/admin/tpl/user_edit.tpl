{% extends "base.tpl" %}

{% block title %}Редактирование пользователя{% endblock %}

{% block content %}
<h1>Редактирование пользователя: {{ user.username }}</h1>

<form method="post">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <div class="mb-3">
        <label class="form-label">Группа</label>
        <select name="group" class="form-select">
            <option value="0" {% if user.group == 0 %}selected{% endif %}>Пользователь</option>
            <option value="1" {% if user.group == 1 %}selected{% endif %}>Меценат</option>
            <option value="2" {% if user.group == 2 %}selected{% endif %}>Модератор</option>
            <option value="3" {% if user.group == 3 %}selected{% endif %}>Администратор</option>
            <option value="4" {% if user.group == 4 %}selected{% endif %}>Root Админ</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Баланс (руб.)</label>
        <input type="number" name="money" class="form-control" value="{{ user.money }}">
    </div>
    <div class="mb-3 form-check">
        <input type="checkbox" name="banned" class="form-check-input" id="banned" value="1" {% if user.banned %}checked{% endif %}>
        <label class="form-check-label" for="banned">Забанен</label>
    </div>
    <div class="mb-3">
        <label class="form-label">Новый пароль (оставьте пустым, чтобы не менять)</label>
        <input type="password" name="password" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Сохранить</button>
    <a href="/admin/users" class="btn btn-secondary">Отмена</a>
</form>
{% endblock %}