{% extends "base.tpl" %}

{% block title %}{{ trans('payment.redirecting') }}…{% endblock %}

{% block content %}
<div class="text-center">
    <h3>{{ trans('payment.redirecting') }}…</h3>
    <p>{{ trans('payment.redirect_manual') }}</p>
    <form method="post" action="https://yoomoney.ru/quickpay/confirm.xml" id="payment-form">
        {% for key, value in params %}
            <input type="hidden" name="{{ key }}" value="{{ value }}">
        {% endfor %}
        <button type="submit" class="btn btn-primary">{{ trans('payment.go_to_pay') }}</button>
    </form>
</div>
<script>document.getElementById('payment-form').submit();</script>
{% endblock %}