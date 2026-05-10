{% extends 'base.tpl' %}

{% block title %}{{ trans('news.title') }} — {{ site_name }}{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0" style="color: var(--accent-bright);">
            <i class="fas fa-newspaper me-2"></i>{{ trans('news.title') }}
        </h2>
        {% if app.user and has_permission('c') %}
            <a href="{{ url('/admin/news/create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>{{ trans('admin.add') }}
            </a>
        {% endif %}
    </div>

    {% for item in news %}
        <article class="widget-card p-3 mb-4">
            <h3 class="mb-2">
                <a href="{{ url('/news/' ~ item.slug) }}" style="color: var(--text-primary); text-decoration: none;">
                    {{ item.title }}
                </a>
            </h3>
            <div class="text-secondary small mb-3">
                <i class="fas fa-user me-1"></i>{{ item.author_name }} ·
                <i class="fas fa-calendar me-1"></i>{{ item.created_at|format_datetime('medium', 'short', locale=locale) }} ·
                <i class="fas fa-eye me-1"></i>{{ item.views }}
            </div>
            <div class="news-preview">
                {{ item.content|striptags|u.truncate(300, '...') }}
            </div>
            <div class="mt-3">
                <a href="{{ url('/news/' ~ item.slug) }}" class="link-accent">
                    {{ trans('news.read_more') }} <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </article>
    {% else %}
        <div class="widget-card p-4 text-center text-muted">
            <i class="fas fa-newspaper fa-3x mb-3" style="color: var(--accent);"></i>
            <h5>{{ trans('news.no_news') }}</h5>
            <p>{{ trans('news.no_news_hint') }}</p>
        </div>
    {% endfor %}

    {% include 'partials/pagination.tpl' with {
        'current': page,
        'total': total,
        'per_page': per_page,
        'url': '/news'
    } %}
{% endblock %}