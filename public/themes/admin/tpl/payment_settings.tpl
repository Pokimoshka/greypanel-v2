{% extends "base.tpl" %}

{% block title %}Платежи{% endblock %}
{% block page_title %}Настройки ЮMoney{% endblock %}

{% block content %}
<div class="card">
    <div class="card-body">
        <form method="post">
            <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
            <div class="mb-3">
                <label class="form-label">Номер кошелька</label>
                <input type="text" name="yoomoney_wallet" class="form-control" value="{{ wallet }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Секретный ключ (оставьте пустым, чтобы не менять)</label>
                <input type="password" name="yoomoney_secret" class="form-control" placeholder="******">
                <div class="form-text">Текущий ключ скрыт. Введите новый, только если хотите изменить.</div>
            </div>
            <button class="btn btn-primary">Сохранить</button>
        </form>
    </div>
</div>
{% endblock %}