{% extends "base.tpl" %}

{% block title %}{{ news.id ? trans('admin.edit_news') : trans('admin.add_news') }}{% endblock %}

{% block content %}
<h1>{{ news.id ? trans('admin.edit_news') : trans('admin.add_news') }}</h1>

<form method="post">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.heading') }}</label>
        <input type="text" name="title" class="form-control" value="{{ news.title|default('') }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.slug') }}</label>
        <input type="text" name="slug" class="form-control" value="{{ news.slug|default('') }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.content') }}</label>
        <textarea name="content" x-data="markdownEditor" class="form-control" rows="15">{{ news.content|default('') }}</textarea>
    </div>
    <div class="mb-3 form-check">
        <input type="checkbox" name="is_published" class="form-check-input" id="is_published" value="1" {{ news.is_published|default(true) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_published">{{ trans('admin.published') }}</label>
    </div>
    <button type="submit" class="btn btn-primary">{{ trans('admin.save') }}</button>
    <a href="{{ url('admin/news') }}" class="btn btn-secondary">{{ trans('admin.cancel') }}</a>
</form>
{% endblock %}

{% block scripts %}
{{ parent() }}
{% endblock %}