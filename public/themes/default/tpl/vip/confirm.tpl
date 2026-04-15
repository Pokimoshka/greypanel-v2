{% extends 'base.tpl' %}

{% block title %}Подтверждение покупки — {{ site_name }}{% endblock %}

{% block content %}
    <div class="widget-card p-4">
        <h4 class="mb-4">Подтверждение покупки</h4>
        <dl class="row">
            <dt class="col-sm-4 text-secondary">Сервер:</dt><dd class="col-sm-8">{{ server.server_name }}</dd>
            <dt class="col-sm-4 text-secondary">Привилегия:</dt><dd class="col-sm-8">{{ privilege.title }} ({{ privilege.flags }})</dd>
            <dt class="col-sm-4 text-secondary">Дней:</dt><dd class="col-sm-8">{{ days }}</dd>
            <dt class="col-sm-4 text-secondary">К оплате:</dt><dd class="col-sm-8 fw-bold">{{ total_price }} ₽</dd>
        </dl>
        <hr style="border-color: var(--border-color);">
        
        {% if app.user.money >= total_price %}
            <form method="post" action="{{ url('/vip/activate') }}">
                <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                <input type="hidden" name="server_id" value="{{ server.id }}">
                <input type="hidden" name="privilege_id" value="{{ privilege.id }}">
                <input type="hidden" name="days" value="{{ days }}">
                <div class="mb-3">
                    <label class="form-label">Пароль для активации на сервере</label>
                    <input type="password" name="password" class="form-control" required>
                    <div class="form-text text-secondary">Необходим для записи привилегии на сервер.</div>
                </div>
                <button type="submit" class="btn btn-success btn-lg">Активировать</button>
                <a href="{{ url('/vip/' ~ server.id) }}" class="btn btn-outline-secondary">Назад</a>
            </form>
        {% else %}
            <div class="alert alert-warning">
                Недостаточно средств. Необходимо пополнить баланс на {{ total_price - app.user.money }} ₽.
                <a href="{{ url('/balance') }}" class="alert-link">Пополнить</a>
            </div>
            <a href="{{ url('/vip/' ~ server.id) }}" class="btn btn-outline-secondary">Назад</a>
        {% endif %}
    </div>
{% endblock %}