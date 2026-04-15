{% extends 'base.tpl' %}

{% block title %}{{ thread.title }} — {{ site_name }}{% endblock %}

{% block content %}
    <div class="mb-4">
        <a href="{{ url('/forum/forum/' ~ thread.forum_id) }}" class="link-accent small">
            <i class="fas fa-arrow-left me-1"></i>К списку тем
        </a>
        <h2 class="mt-2 mb-0" style="color: var(--accent-bright);">{{ thread.title }}</h2>
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-secondary small">
                <i class="fas fa-user me-1"></i>{{ thread.author.username }} · 
                <i class="fas fa-calendar me-1"></i>{{ thread.created_at|date('d.m.Y H:i') }}
            </span>
            <span class="text-secondary small">
                <i class="fas fa-eye me-1"></i>{{ thread.views }} · 
                <i class="fas fa-reply me-1"></i>{{ thread.replies }}
            </span>
        </div>
    </div>

    {# Сообщения темы #}
    {% for post in thread.posts %}
        <div class="widget-card p-3 mb-4" id="post-{{ post.id }}">
            <div class="d-flex">
                <div class="text-center me-3" style="min-width: 60px;">
                    <img src="{{ post.author.avatar }}" width="48" height="48" class="rounded-circle mb-1">
                    <div class="fw-bold small">{{ post.author.username }}</div>
                    <span class="badge mt-1" style="background: var(--accent); font-size: 0.7rem;">{{ post.author.group_name }}</span>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small">
                            <i class="fas fa-clock me-1"></i>{{ post.created_at|date('d.m.Y H:i') }}
                            {% if post.updated_at != post.created_at %}
                                <span class="ms-2">(отредактировано)</span>
                            {% endif %}
                        </span>
                        <div>
                            <span class="text-secondary me-2">#{{ loop.index }}</span>
                            {% if app.user and (post.author.id == app.user.id or app.user.group >= 3) %}
                                <a href="{{ url('/forum/post/edit/' ~ post.id) }}" class="btn btn-sm btn-link text-secondary" title="Редактировать">
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
                            <button @click="insertQuote('{{ post.author.username|escape('js') }}', '{{ post.content|escape('js') }}')" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-quote-right me-1"></i>Цитировать
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {% endfor %}

    {# Пагинация #}
    {% include 'partials/pagination.tpl' with {
        'current': page,
        'total': thread.posts_count,
        'per_page': per_page,
        'url': '/forum/thread/' ~ thread.id
    } %}

    {# Форма ответа #}
    {% if not thread.is_closed and app.user %}
        <div class="widget-card p-3 mt-4">
            <h5 class="mb-3">Ответить</h5>
            <textarea id="reply-content" class="form-control editor" rows="6" placeholder="Ваше сообщение..."></textarea>
            <div class="mt-3">
                <button id="submit-reply" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-1"></i>Отправить
                </button>
            </div>
        </div>
    {% elseif thread.is_closed %}
        <div class="alert alert-warning mt-4">
            <i class="fas fa-lock me-1"></i>Тема закрыта.
        </div>
    {% else %}
        <div class="alert alert-info mt-4">
            <a href="{{ url('/login') }}">Войдите</a>, чтобы ответить.
        </div>
    {% endif %}
{% endblock %}

{% block scripts %}
    {{ parent() }}
    <script>
        document.addEventListener('alpine:init', () => {
            const submitBtn = document.getElementById('submit-reply');
            if (submitBtn) {
                submitBtn.addEventListener('click', async () => {
                    const textarea = document.getElementById('reply-content');
                    const easyMDE = textarea.easyMDE;
                    if (easyMDE) easyMDE.toTextArea();
                    const content = textarea.value.trim();
                    if (!content) return alert('Введите сообщение');
                    
                    const formData = new FormData();
                    formData.append('thread_id', {{ thread.id }});
                    formData.append('content', content);
                    formData.append('csrf_token', '{{ csrf_token }}');
                    
                    try {
                        const res = await fetch('/forum/post/create', { method: 'POST', body: formData });
                        const data = await res.json();
                        if (data.success) location.reload();
                        else alert(data.error || 'Ошибка');
                    } catch (e) {
                        alert('Ошибка соединения');
                    }
                });
            }
        });
    </script>
{% endblock %}