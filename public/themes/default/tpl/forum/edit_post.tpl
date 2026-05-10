{% extends "base.tpl" %}

{% block title %}{{ trans('forum.edit_post') }}{% endblock %}

{% block content %}
<h1>{{ trans('forum.edit_post') }}</h1>
<form method="post" action="/forum/post/edit/{{ post.id }}">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <div class="mb-3">
        <label class="form-label">{{ trans('forum.content_label') }}</label>
        <textarea name="content" class="form-control editor" rows="10">{{ post.content }}</textarea>
    </div>
    <button type="submit" class="btn btn-primary">{{ trans('settings.save') }}</button>
    <a href="/forum/thread/{{ thread.id }}" class="btn btn-secondary">{{ trans('admin.cancel') }}</a>
</form>
{% endblock %}