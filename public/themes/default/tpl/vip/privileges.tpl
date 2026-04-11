{% extends "base.tpl" %}

{% block title %}Привилегии на {{ server.server_name }}{% endblock %}

{% block content %}
<h1>Сервер: {{ server.server_name }}</h1>

{% if user_active %}
<div class="alert alert-info">
    У вас уже есть активная привилегия на этом сервере до {{ user_active.expired_at|date('d.m.Y H:i') }}.
    Продление возможно.
</div>
{% endif %}

<h2>Доступные привилегии</h2>
<form method="post" action="/vip/confirm">
    <input type="hidden" name="server_id" value="{{ server.id }}">
    <table class="table">
        <thead>
            <tr>
                <th>Название</th>
                <th>Флаги</th>
                <th>Цена за день</th>
                <th>Выбрать</th>
            </tr>
        </thead>
        <tbody>
            {% for priv in privileges %}
            <tr>
                <td>{{ priv.title }}</td>
                <td><code>{{ priv.flags }}</code></td>
                <td>{{ priv.price_per_day }} ₽</td>
                <td>
                    <input type="radio" name="privilege_id" value="{{ priv.id }}" required>
                </td>
            </tr>
            {% else %}
            <tr><td colspan="4" class="text-center">Нет привилегий</td></tr>
            {% endfor %}
        </tbody>
    </table>
    <div class="mb-3">
        <label class="form-label">Количество дней</label>
        <select name="days" class="form-select">
            <option value="7">7 дней</option>
            <option value="14">14 дней</option>
            <option value="30">30 дней</option>
            <option value="60">60 дней</option>
            <option value="90">90 дней</option>
            <option value="180">180 дней</option>
            <option value="365">365 дней</option>
        </select>
    </div>
    <button type="submit" class="btn btn-success">Далее →</button>
</form>
{% endblock %}