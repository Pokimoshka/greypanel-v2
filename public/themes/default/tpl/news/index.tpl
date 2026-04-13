{% extends "base.tpl" %}

{% block title %}Новости{% endblock %}

{% block content %}
<h1>Новости</h1>

{% for item in news %}
<div class="card mb-4">
    <div class="card-header">
        <h3><a href="{{ url('news/' ~ item.slug) }}">{{ item.title }}</a></h3>
        <div class="text-muted small">
            {{ item.created_at|date('d.m.Y H:i') }} · Автор: {{ item.author_name }} · Просмотров: {{ item.views }}
        </div>
    </div>
    <div class="card-body">
        {{ item.content|raw }}
    </div>
</div>
{% else %}
<div class="alert alert-info">Новостей пока нет.</div>
{% endfor %}

{% include 'partials/pagination.tpl' with {
    'current': page,
    'total': total,
    'per_page': per_page,
    'url': '/news'
} %}
{% endblock %}