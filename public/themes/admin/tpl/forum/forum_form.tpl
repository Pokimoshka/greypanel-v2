{% extends "base.tpl" %}

{% block title %}{{ forum ? trans('admin.edit_section') : trans('admin.add_section') }}{% endblock %}

{% block content %}
<h1>{{ forum ? trans('admin.edit_section') : trans('admin.add_section') }} {{ trans('admin.in_category') }} "{{ category.title }}"</h1>
<form method="post">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    {% if forum %}<input type="hidden" name="id" value="{{ forum.id }}">{% endif %}
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.heading') }}</label>
        <input type="text" name="title" class="form-control" value="{{ forum.title|default('') }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.description') }}</label>
        <textarea name="description" class="form-control" rows="3">{{ forum.description|default('') }}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.icon') }}</label>
        <input type="text" name="icon" class="form-control" value="{{ forum.icon|default('fa fa-comments') }}" placeholder="fa fa-comments">
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.sort_order') }}</label>
        <input type="number" name="sort_order" class="form-control" value="{{ forum.sort_order|default(0) }}">
    </div>
    <button type="submit" class="btn btn-primary">{{ trans('admin.save') }}</button>
    <a href="/admin/forum/categories/{{ category.id }}/forums" class="btn btn-secondary">{{ trans('admin.cancel') }}</a>
</form>
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}