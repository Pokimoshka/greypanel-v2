{% extends "base.tpl" %}

{% block title %}Форум{% endblock %}

{% block content %}
<h1>Форум</h1>
{% for category in categories %}
<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
        <h4 class="mb-0">{{ category.title }}</h4>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width: 5%"></th>
                    <th>Раздел</th>
                    <th style="width: 10%">Темы</th>
                    <th style="width: 30%">Последнее сообщение</th>
                </tr>
            </thead>
            <tbody>
                {% for forum in category.forums %}
                <tr>
                    <td class="text-center"><i class="{{ forum.icon }} fa-2x"></i></td>
                    <td>
                        <a href="/forum/forum/{{ forum.id }}" class="fw-bold">{{ forum.title }}</a>
                        <div class="small text-muted">{{ forum.description }}</div>
                    </td>
                    <td>{{ forum.threads_count }}</td>
                    <td>
                        {% if forum.last_post %}
                            <a href="/forum/thread/{{ forum.last_thread.id }}">{{ forum.last_thread.title|slice(0, 30) }}</a><br>
                            <small>{{ forum.last_post.created_at|date('d.m.Y H:i') }}<br>
                            <a href="/profile/{{ forum.last_post.user_id }}">{{ forum.last_post.author.username ?? 'Guest' }}</a></small>
                        {% else %}
                            <span class="text-muted">Нет сообщений</span>
                        {% endif %}
                    </td>
                </tr>
                {% endfor %}
            </tbody>
        </table>
    </div>
</div>
{% else %}
<div class="alert alert-info">Нет категорий.</div>
{% endfor %}
{% endblock %}