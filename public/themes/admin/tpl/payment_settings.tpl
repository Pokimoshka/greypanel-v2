{% extends "base.tpl" %}

{% block title %}Настройки платежей{% endblock %}

{% block content %}
<h1>Настройки платежей (ЮMoney)</h1>
<form method="post">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <div class="mb-3">
        <label class="form-label">Номер кошелька ЮMoney</label>
        <input type="text" name="yoomoney_wallet" class="form-control" value="{{ wallet }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Секретный ключ (для уведомлений)</label>
        <input type="text" name="yoomoney_secret" class="form-control" value="{{ secret }}" required>
        <div class="form-text">Придумайте и укажите этот же ключ в настройках магазина ЮMoney.</div>
    </div>
    <button type="submit" class="btn btn-primary">Сохранить</button>
</form>
{% endblock %}