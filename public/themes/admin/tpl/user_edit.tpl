{% extends "base.tpl" %}

{% block title %}Редактирование {{ user.username }}{% endblock %}
{% block page_title %}Редактирование: {{ user.username }}{% endblock %}

{% block content %}
<div class="card">
    <div class="card-body">
        <form method="post">
            <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
            <div class="mb-3">
                <label class="form-label">Группа</label>
                <select name="group" class="form-select">
                    <option value="0" {{ user.group == 0 ? 'selected' }}>Пользователь</option>
                    <option value="1" {{ user.group == 1 ? 'selected' }}>Меценат</option>
                    <option value="2" {{ user.group == 2 ? 'selected' }}>Модератор</option>
                    <option value="3" {{ user.group == 3 ? 'selected' }}>Администратор</option>
                    <option value="4" {{ user.group == 4 ? 'selected' }}>Root Admin</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Баланс (₽)</label>
                <input type="number" name="money" class="form-control" value="{{ user.money }}">
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="banned" class="form-check-input" id="banned" value="1" {{ user.banned ? 'checked' }}>
                <label class="form-check-label" for="banned">Забанен</label>
            </div>
            <div class="mb-3">
                <label class="form-label">Новый пароль (оставьте пустым)</label>
                <input type="password" name="password" class="form-control">
            </div>
            <button class="btn btn-primary">Сохранить</button>
            <a href="{{ url('admin/users') }}" class="btn btn-outline-secondary">Отмена</a>
        </form>
    </div>
</div>
{% endblock %}