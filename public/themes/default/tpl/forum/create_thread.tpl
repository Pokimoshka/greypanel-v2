{% extends "base.tpl" %}

{% block title %}Создать тему в {{ forum.title }}{% endblock %}

{% block content %}
<h1>Создать тему в разделе "{{ forum.title }}"</h1>
<form method="post" action="/forum/thread/create">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <input type="hidden" name="forum_id" value="{{ forum.id }}">
    <div class="mb-3">
        <label class="form-label">Заголовок</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Сообщение</label>
        <textarea name="content" class="form-control editor" rows="10">{{ thread.content ?? '' }}</textarea>
        <div class="form-text">Доступные BB-коды: [b], [i], [u], [url], [img], [quote], [code]</div>
    </div>
    <button type="submit" class="btn btn-primary">Создать тему</button>
</form>
{% endblock %}