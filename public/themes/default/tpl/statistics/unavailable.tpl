{% extends 'base.tpl' %}
{% block title %}Статистика недоступна — {{ site_name }}{% endblock %}

{% block content %}
<div class="widget-card p-5 text-center text-muted">
    <i class="fas fa-chart-bar fa-4x mb-3" style="color: var(--accent);"></i>
    <h4>Статистика временно недоступна</h4>
    <p>На серверах ещё не настроена интеграция со статистикой.</p>
    <p>Пожалуйста, зайдите позже или свяжитесь с администратором.</p>
</div>
{% endblock %}