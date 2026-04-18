{% extends 'base.tpl' %}

{% block title %}Слишком много запросов{% endblock %}

{% block content %}
<div class="widget-card p-4 text-center">
    <i class="fas fa-exclamation-triangle fa-3x mb-3" style="color: var(--accent);"></i>
    <h3>Слишком много запросов</h3>
    <p>{{ message }}</p>
    <a href="javascript:history.back()" class="btn btn-primary">Вернуться назад</a>
</div>
{% endblock %}