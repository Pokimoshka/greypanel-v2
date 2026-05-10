{% extends "base.tpl" %}

{% block title %}{{ group ? trans('admin.edit_group') : trans('admin.add_group') }}{% endblock %}

{% block content %}
<h1>{{ group ? trans('admin.edit_group') : trans('admin.add_group') }}</h1>

{% if error %}
    <div class="alert alert-danger">{{ error }}</div>
{% endif %}

<form method="post">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.group_name') }}</label>
        <input type="text" name="name" class="form-control" value="{{ group.name ?? '' }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.flags') }}</label>
        <input type="text" name="flags" class="form-control" value="{{ group.flags ?? '' }}" placeholder="abcdef..." @input="normalizeFlags($event)">
        <div class="form-text">{{ trans('admin.flags_hint') }}</div>
    </div>
    <div class="mb-3 form-check">
        <input type="checkbox" name="is_default" class="form-check-input" id="is_default" value="1" {{ group and group.isDefault ? 'checked' : '' }}>
        <label class="form-check-label" for="is_default">{{ trans('admin.is_default') }}</label>
    </div>
    <button type="submit" class="btn btn-primary">{{ trans('admin.save') }}</button>
    <a href="{{ url('admin/groups') }}" class="btn btn-secondary">{{ trans('admin.cancel') }}</a>
</form>
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}