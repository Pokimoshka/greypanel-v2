{% extends "base.tpl" %}

{% block title %}Редактировать сообщение{% endblock %}

{% block content %}
<h1>Редактировать сообщение</h1>
<form method="post" action="/forum/post/edit/{{ post.id }}">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <div class="mb-3">
        <label class="form-label">Сообщение</label>
        <textarea name="content" class="form-control" rows="10" required>{{ post.content }}</textarea>
    </div>
    <button type="submit" class="btn btn-primary">Сохранить</button>
    <a href="/forum/thread/{{ thread.id }}" class="btn btn-secondary">Отмена</a>
</form>
{% endblock %}