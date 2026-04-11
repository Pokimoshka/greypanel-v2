{% extends "base.tpl" %}

{% block title %}Платёж выполнен{% endblock %}

{% block content %}
<div class="alert alert-success">
    <h3>Платёж успешно выполнен!</h3>
    <p>Деньги зачислены на ваш баланс.</p>
    <a href="/balance" class="btn btn-primary">Перейти к балансу</a>
</div>
{% endblock %}