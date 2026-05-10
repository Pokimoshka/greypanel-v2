{% extends 'base.tpl' %}

{% block title %}{{ news.title }} — {{ site_name }}{% endblock %}

{% block content %}
    <a href="{{ url('/news') }}" class="link-accent small mb-3 d-inline-block">
        <i class="fas fa-arrow-left me-1"></i>{{ trans('news.back') }}
    </a>

    <article class="widget-card p-4">
        <h1 class="mb-3" style="color: var(--accent-bright);">{{ news.title }}</h1>
        <div class="text-secondary small mb-4 pb-3 border-bottom" style="border-color: var(--border-color) !important;">
            <i class="fas fa-user me-1"></i>{{ news.author_name }} ·
            <i class="fas fa-calendar me-1"></i>{{ news.created_at|format_datetime('medium', 'short', locale=locale) }} ·
            <i class="fas fa-eye me-1"></i>{{ news.views }}
        </div>
        <div class="news-content">
            {{ news.content|raw }}
        </div>
    </article>

    {% if app.user and has_permission('c') %}
        <div class="mt-3 text-end">
            <a href="{{ url('/admin/news/edit/' ~ news.id) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-edit me-1"></i>{{ trans('news.edit') }}
            </a>
        </div>
    {% endif %}
{% endblock %}