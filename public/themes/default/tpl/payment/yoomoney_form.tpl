{% extends "base.tpl" %}

{% block title %}Перенаправление на ЮMoney...{% endblock %}

{% block content %}
<div class="text-center">
    <h3>Перенаправление на платёжную систему...</h3>
    <p>Если автоматический переход не произошёл, нажмите кнопку ниже.</p>
    <form method="post" action="https://yoomoney.ru/quickpay/confirm.xml" id="payment-form">
        {% for key, value in params %}
            <input type="hidden" name="{{ key }}" value="{{ value }}">
        {% endfor %}
        <button type="submit" class="btn btn-primary">Перейти к оплате</button>
    </form>
</div>
<script>document.getElementById('payment-form').submit();</script>
{% endblock %}