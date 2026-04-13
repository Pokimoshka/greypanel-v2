{% extends "base.tpl" %}

{% block title %}{{ news.title }}{% endblock %}

{% block content %}
<div class="card">
    <div class="card-header">
        <h1>{{ news.title }}</h1>
        <div class="text-muted">
            {{ news.created_at|date('d.m.Y H:i') }} · Автор: {{ news.author_name }} · Просмотров: {{ news.views }}
        </div>
    </div>
    <div class="card-body">
        {{ news.content|raw }}
    </div>
</div>
<a href="{{ url('news') }}" class="btn btn-secondary mt-3">← Все новости</a>
{% endblock %}