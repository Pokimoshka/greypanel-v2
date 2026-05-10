{% extends "base.tpl" %}

{% block title %}{{ trans('forum.create_thread') }} — {{ forum.title }}{% endblock %}

{% block content %}
<h1>{{ trans('forum.create_thread_in') }} "{{ forum.title }}"</h1>
<form method="post" action="/forum/thread/create">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <input type="hidden" name="forum_id" value="{{ forum.id }}">
    <div class="mb-3">
        <label class="form-label">{{ trans('forum.title_label') }}</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('forum.content_label') }}</label>
        <textarea name="content" x-data="markdownEditor" class="form-control editor" rows="10">{{ thread.content ?? '' }}</textarea>
        <div class="form-text">{{ trans('forum.markdown_hint') }}</div>
    </div>
    <button type="submit" class="btn btn-primary">{{ trans('forum.create.submit') }}</button>
    <a href="{{ url('/forum/forum/' ~ forum.id) }}" class="btn btn-secondary">{{ trans('admin.cancel') }}</a>
</form>
{% endblock %}