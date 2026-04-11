{% extends "base.tpl" %}

{% block title %}Профиль пользователя{% endblock %}

{% block content %}
<div class="row">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <img src="{{ user.avatar }}" alt="Avatar" class="img-thumbnail mb-3" style="max-width: 200px;">
                <h4>{{ user.username }}</h4>
                <p class="text-muted">
                    Группа:
                    {% if user.group == 4 %}Root Admin
                    {% elseif user.group == 3 %}Admin
                    {% elseif user.group == 2 %}Moderator
                    {% elseif user.group == 1 %}Меценат
                    {% else %}Пользователь
                    {% endif %}
                </p>
                <p>Зарегистрирован: {{ user.regData|date('d.m.Y H:i') }}</p>
                <p>Баланс: {{ user.money }} ₽</p>
                <p>Всего пополнено: {{ user.allMoney }} ₽</p>
                <a href="/settings" class="btn btn-primary">Редактировать профиль</a>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Статистика</div>
            <div class="card-body">
                <p>Тем на форуме: {{ user.countTheard }}</p>
                <p>Сообщений: {{ user.countPost }}</p>
                <p>Лайков: {{ user.countLike }}</p>
            </div>
        </div>
    </div>
</div>
{% endblock %}