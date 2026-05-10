{% extends "base.tpl" %}

{% block title %}{{ trans('forum.edit_thread') }}{% endblock %}

{% block content %}
<h1>{{ trans('forum.edit_thread') }}</h1>
<form method="post" action="/forum/thread/edit/{{ thread.id }}">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <input type="hidden" name="thread_id" value="{{ thread.id }}">
    <div class="mb-3">
        <label class="form-label">{{ trans('forum.title_label') }}</label>
        <input type="text" name="title" class="form-control" value="{{ thread.title }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('forum.content_label') }}</label>
        <textarea name="content" class="form-control editor" rows="10">{{ thread.content }}</textarea>
    </div>
    <button type="submit" class="btn btn-primary">{{ trans('settings.save') }}</button>
    <a href="/forum/thread/{{ thread.id }}" class="btn btn-secondary">{{ trans('admin.cancel') }}</a>
</form>
{% endblock %}