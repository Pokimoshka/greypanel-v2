{% block content %}
<h1>Подтверждение покупки</h1>
<div class="card">
    <div class="card-body">
        <p><strong>Сервер:</strong> {{ server.server_name }}</p>
        <p><strong>Привилегия:</strong> {{ privilege.title }} (флаги: {{ privilege.flags }})</p>
        <p><strong>Количество дней:</strong> {{ days }}</p>
        <p><strong>Итого к оплате:</strong> {{ total_price }} ₽</p>
        <p><strong>Ваш баланс:</strong> {{ app.user.money }} ₽</p>

        {% if app.user.money >= total_price %}
            <form method="post" action="/vip/activate">
                <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                <input type="hidden" name="server_id" value="{{ server.id }}">
                <input type="hidden" name="privilege_id" value="{{ privilege.id }}">
                <input type="hidden" name="days" value="{{ days }}">
                
                {# ДОБАВЛЕНО ПОЛЕ ДЛЯ ПАРОЛЯ #}
                <div class="mb-3">
                    <label class="form-label">Ваш пароль (необходим для активации на сервере)</label>
                    <input type="password" name="password" class="form-control" required>
                    <div class="form-text">Пароль от вашего аккаунта на сайте. Не беспокойтесь, он передаётся в зашифрованном виде.</div>
                </div>
                
                <button type="submit" class="btn btn-success">Активировать</button>
                <a href="/vip/{{ server.id }}" class="btn btn-secondary">Назад</a>
            </form>
        {% else %}
            <div class="alert alert-danger">
                Недостаточно средств. Необходимо пополнить баланс на {{ total_price - app.user.money }} ₽.
                <a href="/balance" class="alert-link">Пополнить</a>
            </div>
            <a href="/vip/{{ server.id }}" class="btn btn-secondary">Назад</a>
        {% endif %}
    </div>
</div>
{% endblock %}