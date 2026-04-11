{% extends "base.tpl" %}

{% block title %}{{ thread.title }}{% endblock %}

{% block content %}
<div class="mb-3">
    <a href="{{ url('forum/forum/' ~ thread.forum_id) }}" class="btn btn-secondary btn-sm">← Назад</a>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h3>{{ thread.title }}</h3>
        <div class="d-flex justify-content-between">
            <span>Автор: {{ thread.author.username }}</span>
            {% if app.user and (thread.author.id == app.user.id or app.user.group >= 3) %}
            <div>
                <a href="{{ url('forum/thread/edit/' ~ thread.id) }}" class="btn btn-sm btn-warning">Ред.</a>
                <a href="{{ url('forum/thread/delete/' ~ thread.id) }}" class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')">Удалить</a>
            </div>
            {% endif %}
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-2 text-center">
                <img src="{{ thread.author.avatar }}" class="rounded-circle mb-2" width="80">
                <div><strong>{{ thread.author.username }}</strong></div>
            </div>
            <div class="col-md-10">
                {{ thread.content_html|raw }}
                <div class="mt-3">
                    <div x-data="likeButton('thread', {{ thread.id }}, {{ thread.likes_count|default(0) }}, {{ thread.user_liked ? 'true' : 'false' }})">
                        <button @click="toggle" class="btn btn-sm btn-outline-primary">
                            👍 <span x-text="count"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<h4>Ответы ({{ thread.posts_count }})</h4>

{% for post in thread.posts %}
<div class="card mb-3" id="post-{{ post.id }}">
    <div class="card-body">
        <div class="row">
            <div class="col-md-2 text-center">
                <img src="{{ post.author.avatar }}" class="rounded-circle mb-2" width="60">
                <div><strong>{{ post.author.username }}</strong></div>
            </div>
            <div class="col-md-10">
                <div class="text-muted small mb-2">
                    #{{ loop.index }} · {{ post.created_at|date('d.m.Y H:i') }}
                </div>
                {{ post.content_html|raw }}
                <div class="mt-2">
                    {% if app.user and (post.author.id == app.user.id or app.user.group >= 3) %}
                        <a href="{{ url('forum/post/edit/' ~ post.id) }}" class="btn btn-sm btn-warning">Ред.</a>
                        <a href="{{ url('forum/post/delete/' ~ post.id) }}" class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')">Удалить</a>
                    {% endif %}
                    <div x-data="quote">
                        <button @click="insertQuote('{{ post.author.username|escape('js') }}', '{{ post.content|escape('js') }}')" class="btn btn-sm btn-outline-secondary">Цитировать</button>
                    </div>
                    <div x-data="likeButton('post', {{ post.id }}, {{ post.likes_count|default(0) }}, {{ post.user_liked ? 'true' : 'false' }})" class="d-inline">
                        <button @click="toggle" class="btn btn-sm btn-outline-primary">Нравится</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{% endfor %}

{% if not thread.is_closed and app.user %}
<div class="card mt-3">
    <div class="card-header">Ответить</div>
    <div class="card-body">
        <textarea id="reply-content" class="form-control" rows="6" placeholder="Ваше сообщение..."></textarea>
        <button id="submit-reply" class="btn btn-primary mt-2">Отправить</button>
    </div>
</div>
{% endif %}

{% include 'partials/pagination.tpl' %}
{% endblock %}

{% block scripts %}
<script>
document.addEventListener('alpine:init', () => {
    $('#reply-content').trumbowyg({
        btns: ['bold', 'italic', 'underline', 'link', 'quote'],
        plugins: { bbcode: true }
    });

    document.getElementById('submit-reply').addEventListener('click', async () => {
        const content = $('#reply-content').trumbowyg('html');
        const formData = new FormData();
        formData.append('thread_id', {{ thread.id }});
        formData.append('content', content);
        formData.append('csrf_token', '{{ csrf_token }}');
        const res = await fetch('/forum/post/create', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) location.reload();
        else alert(data.error);
    });
});
</script>
{% endblock %}