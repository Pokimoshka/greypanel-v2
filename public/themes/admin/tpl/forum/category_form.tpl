{% extends "base.tpl" %}

{% block title %}{{ category ? trans('admin.edit_category') : trans('admin.add_category') }}{% endblock %}

{% block content %}
<h1>{{ category ? trans('admin.edit_category') : trans('admin.add_category') }}</h1>
<form method="post">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    {% if category %}<input type="hidden" name="id" value="{{ category.id }}">{% endif %}
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.heading') }}</label>
        <input type="text" name="title" class="form-control" value="{{ category.title|default('') }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.description') }}</label>
        <textarea name="description" class="form-control" rows="3">{{ category.description|default('') }}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.sort_order') }}</label>
        <input type="number" name="sort_order" class="form-control" value="{{ category.sort_order|default(0) }}">
    </div>
    <button type="submit" class="btn btn-primary">{{ trans('admin.save') }}</button>
    <a href="/admin/forum/categories" class="btn btn-secondary">{{ trans('admin.cancel') }}</a>
</form>
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}