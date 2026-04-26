{% extends "base.tpl" %}

{% block title %}Управление темами{% endblock %}

{% block content %}
<h1>Темы оформления</h1>
<div class="row">
    {% for theme in themes %}
    <div class="col-md-4 mb-4">
        <div class="card h-100 {% if theme.name == active %}border-primary{% endif %}">
            {% if theme.screenshot %}
            <img src="{{ theme.screenshot }}" class="card-img-top" alt="{{ theme.title }}">
            {% else %}
            <div class="card-img-top bg-secondary text-white text-center py-5">No preview</div>
            {% endif %}
            <div class="card-body">
                <h5 class="card-title">{{ theme.title }}</h5>
                <p class="card-text">{{ theme.description }}</p>
                <p class="card-text"><small class="text-muted">Версия: {{ theme.version }}, автор: {{ theme.author }}</small></p>
            </div>
            <div class="card-footer">
            {% if theme.name == active %}
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-success">Активна</span>
                    <a href="{{ url('admin/theme-editor') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Редактировать
                    </a>
                </div>
            {% else %}
                <form method="post" action="/admin/themes">
                    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                    <input type="hidden" name="theme" value="{{ theme.name }}">
                    <button type="submit" class="btn btn-primary btn-sm">Активировать</button>
                </form>
            {% endif %}
            </div>
        </div>
    </div>
    {% endfor %}
</div>
{% endblock %}