{% extends "base.tpl" %}

{% block title %}Поиск по форуму{% endblock %}

{% block content %}
<h1>Поиск</h1>

<form method="get" action="/forum/search" class="mb-4">
    <div class="input-group">
        <input type="text" name="q" class="form-control" placeholder="Введите поисковый запрос (минимум 3 символа)" value="{{ query }}">
        <button class="btn btn-primary" type="submit">Искать</button>
    </div>
</form>

{% if query and query|length < 3 %}
    <div class="alert alert-warning">Введите минимум 3 символа.</div>
{% elseif query %}
    <p>Найдено: {{ total }} тем</p>
    {% for thread in results %}
        <div class="card mb-2">
            <div class="card-body">
                <h5><a href="{{ thread.url }}">{{ thread.title }}</a></h5>
                <p class="text-muted small">
                    Раздел: <a href="/forum/forum/{{ thread.forum_id }}">{{ thread.forum_title }}</a> |
                    Автор: {{ thread.author_name }} |
                    {{ thread.created_at|date('d.m.Y H:i') }}
                </p>
                <p>{{ thread.content|slice(0, 200)|striptags }}</p>
            </div>
        </div>
    {% else %}
        <div class="alert alert-info">Ничего не найдено.</div>
    {% endfor %}

    {% include 'partials/pagination.tpl' with {
        'current': page,
        'total': total,
        'per_page': per_page,
        'url': '/forum/search',
        'params': {'q': query}
    } %}
{% endif %}
{% endblock %}