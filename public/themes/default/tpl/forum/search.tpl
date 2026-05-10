{% extends "base.tpl" %}

{% block title %}{{ trans('forum.search') }}{% endblock %}

{% block content %}
<h1>{{ trans('forum.search') }}</h1>

<form method="get" action="/forum/search" class="mb-4">
    <div class="input-group">
        <input type="text" name="q" class="form-control" placeholder="{{ trans('forum.search_placeholder') }}" value="{{ query }}">
        <button class="btn btn-primary" type="submit">{{ trans('forum.search') }}</button>
    </div>
</form>

{% if query and query|length < 3 %}
    <div class="alert alert-warning">{{ trans('forum.search_min') }}</div>
{% elseif query %}
    <p>{{ trans('forum.found') }}: {{ total }} {{ trans('forum.topics') }}</p>
    {% for thread in results %}
        <div class="card mb-2">
            <div class="card-body">
                <h5><a href="{{ thread.url }}">{{ thread.title }}</a></h5>
                <p class="text-muted small">
                    {{ trans('forum.section') }}: <a href="/forum/forum/{{ thread.forum_id }}">{{ thread.forum_title }}</a> |
                    {{ trans('forum.author') }}: {{ thread.author_name }} |
                    {{ thread.created_at|format_datetime('medium', 'short', locale=locale) }}
                </p>
                <p>{{ thread.content|slice(0, 200)|striptags }}</p>
            </div>
        </div>
    {% else %}
        <div class="alert alert-info">{{ trans('forum.not_found') }}</div>
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