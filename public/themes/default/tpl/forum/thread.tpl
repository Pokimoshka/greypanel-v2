{% extends 'base.tpl' %}

{% block title %}{{ thread.title }} — {{ site_name }}{% endblock %}

{% block content %}
    <div class="mb-4">
        <a href="{{ url('/forum/forum/' ~ thread.forum_id) }}" class="link-accent small">
            <i class="fas fa-arrow-left me-1"></i>{{ trans('forum.back_to_list') }}
        </a>
        <div class="d-flex justify-content-between align-items-center mt-2">
            <h2 class="mb-0" style="color: var(--accent-bright);">{{ thread.title }}</h2>
            {% if app.user and (thread.user_id == app.user.id or has_permission('c')) %}
                <form method="post" action="/forum/thread/delete/{{ thread.id }}" onsubmit="return confirm('{{ trans('forum.delete_thread_confirm') }}');">
                    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash"></i> {{ trans('forum.delete') }}
                    </button>
                </form>
            {% endif %}
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-secondary small">
                <i class="fas fa-user me-1"></i>{{ thread.author.username }} · 
                <i class="fas fa-calendar me-1"></i>{{ thread.created_at|format_datetime('medium', 'short', locale=locale) }}
            </span>
            <span class="text-secondary small">
                <i class="fas fa-eye me-1"></i>{{ thread.views }} · 
                <i class="fas fa-reply me-1"></i>{{ thread.replies }}
            </span>
        </div>
    </div>

    {% for post in thread.posts %}
        <div class="widget-card p-3 mb-4" id="post-{{ post.id }}">
            <div class="d-flex">
                <div class="text-center me-3" style="min-width: 60px;">
                    <img src="{{ post.author.avatar|e('html_attr') }}" width="48" height="48" class="rounded-circle mb-1">
                    <div class="fw-bold small"><a href="{{ url('/profile/' ~ post.author.id) }}">{{ post.author.username }}</a></div>
                    <span class="badge mt-1" style="background: var(--accent); font-size: 0.7rem;">{{ post.author.group_name }}</span>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small">
                            <i class="fas fa-clock me-1"></i>{{ post.created_at|format_datetime('medium', 'short', locale=locale) }}
                            {% if post.updated_at != post.created_at %}
                                <span class="ms-2">({{ trans('forum.edited') }})</span>
                            {% endif %}
                        </span>
                        <div>
                            <span class="text-secondary me-2">#{{ loop.index }}</span>
                            {% if app.user and (post.author.id == app.user.id or has_permission('c')) %}
                                <a href="{{ url('/forum/post/edit/' ~ post.id) }}" ...>
                                    <i class="fas fa-edit"></i>
                                </a>
                            {% endif %}
                        </div>
                    </div>
                    <div class="post-content">
                        {{ post.content_html|raw }}
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <div x-data="likeButton('post', {{ post.id }}, {{ post.likes_count|default(0) }}, {{ post.user_liked ? 'true' : 'false' }})">
                            <button @click="toggle" class="btn btn-sm" :class="liked ? 'btn-primary' : 'btn-outline-secondary'">
                                <i class="far fa-heart me-1"></i>
                                <span x-text="count"></span>
                            </button>
                        </div>
                        <div x-data="quote">
                            <button @click="insertQuote('{{ post.author.username|escape('js') }}', '{{ post.content|escape('js') }}', {{ post.id }})" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-quote-right me-1"></i>{{ trans('forum.quote') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {% endfor %}

    {% include 'partials/pagination.tpl' with {
        'current': page,
        'total': thread.posts_count,
        'per_page': per_page,
        'url': '/forum/thread/' ~ thread.id
    } %}

    {% if not thread.is_closed and app.user %}
    <div class="widget-card p-3 mt-4">
        <h5 class="mb-3">{{ trans('forum.reply.title') }}</h5>
        <textarea id="reply-editor" x-data="markdownEditor" class="form-control editor" rows="6"
                  placeholder="{{ trans('forum.reply_placeholder') }}"></textarea>
        <div class="mt-3">
            <button class="btn btn-primary" x-data="replyForm({{ thread.id }})" @click="submit">
                <i class="fas fa-paper-plane me-1"></i>{{ trans('forum.reply.send') }}
            </button>
        </div>
    </div>
    {% elseif thread.is_closed %}
        <div class="alert alert-warning mt-4">
            <i class="fas fa-lock me-1"></i>{{ trans('forum.closed') }}
        </div>
    {% else %}
        <div class="alert alert-info mt-4">
            <a href="{{ url('/login') }}">{{ trans('forum.reply.login_required') }}</a>.
        </div>
    {% endif %}
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}