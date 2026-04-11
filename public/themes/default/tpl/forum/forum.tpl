{% extends "base.tpl" %}

{% block title %}{{ forum.title }}{% endblock %}

{% block content %}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>{{ forum.title }}</h1>
    <a href="/forum/forum/{{ forum.id }}/create" class="btn btn-success">Создать тему</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Тема</th>
            <th>Ответов</th>
            <th>Просмотров</th>
            <th>Последнее сообщение</th>
        </tr>
    </thead>
    <tbody>
        {% for thread in threads %}
        <tr>
            <td>
                <a href="/forum/thread/{{ thread.id }}" class="fw-bold">{{ thread.title }}</a>
                <div class="small text-muted">Автор: <a href="/profile/{{ thread.author.id }}">{{ thread.author.username }}</a> · {{ thread.created_at|date('d.m.Y H:i') }}</div>
            </td>
            <td>{{ thread.replies }}</td>
            <td>{{ thread.views }}</td>
            <td>
                {% if thread.last_post_user %}
                    <a href="/profile/{{ thread.last_post_user.id }}">{{ thread.last_post_user.username }}</a><br>
                    <small>{{ thread.last_post_at|date('d.m.Y H:i') }}</small>
                {% else %}
                    {{ thread.created_at|date('d.m.Y H:i') }}
                {% endif %}
            </td>
        </tr>
        {% else %}
        <tr><td colspan="4" class="text-center">Нет тем. Будьте первым!</td></tr>
        {% endfor %}
    </tbody>
</table>

{% include 'partials/pagination.tpl' with {
    'current': page,
    'total': total,
    'per_page': per_page,
    'url': '/forum/forum/' ~ forum.id,
    'params': {}
} %}
{% endblock %}