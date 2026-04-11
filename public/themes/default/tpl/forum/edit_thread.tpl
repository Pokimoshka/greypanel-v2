{% extends "base.tpl" %}

{% block title %}Редактировать тему{% endblock %}

{% block content %}
<h1>Редактировать тему</h1>
<form method="post" action="/forum/thread/edit/{{ thread.id }}">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <input type="hidden" name="thread_id" value="{{ thread.id }}">
    <div class="mb-3">
        <label class="form-label">Заголовок</label>
        <input type="text" name="title" class="form-control" value="{{ thread.title }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Сообщение</label>
        <textarea name="content" id="content" class="form-control" rows="10" required>{{ thread.content }}</textarea>
    </div>
    <button type="submit" class="btn btn-primary">Сохранить</button>
    <a href="/forum/thread/{{ thread.id }}" class="btn btn-secondary">Отмена</a>
</form>
{% endblock %}

{% block scripts %}
<script>
$(document).ready(function() {
    $('#content').trumbowyg({
        btns: ['bold', 'italic', 'underline', '|', 'link', 'insertImage', '|', 'unorderedList', 'orderedList', '|', 'quote', 'code', '|', 'removeformat'],
        btnsDef: {
            quote: {
                title: 'Цитата',
                fn: function() { this.execCmd('formatBlock', 'blockquote'); }
            }
        },
        plugins: { bbcode: true },
        autogrow: true,
        removeformatPasted: true
    });
});
</script>
{% endblock %}