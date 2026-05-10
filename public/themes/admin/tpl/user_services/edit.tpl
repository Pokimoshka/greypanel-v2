{% extends "base.tpl" %}
{% block title %}{{ trans('admin.edit_service') }}{% endblock %}
{% block page_title %}{{ trans('admin.edit_service') }} {{ trans('admin.of_user') }} {{ user.username }}{% endblock %}
{% block content %}
<a href="{{ url('admin/users/' ~ user.id ~ '/services') }}" class="btn btn-outline-secondary mb-3">
    <i class="fas fa-arrow-left"></i> {{ trans('admin.back_to_services') }}
</a>

<form method="post">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.expiry_date') }}</label>
        <input type="datetime-local" name="expires_at" class="form-control"
               value="{{ userService.getExpiresAt() ? userService.getExpiresAt()|date('Y-m-d\TH:i') : '' }}">
        <div class="form-text">{{ trans('admin.expiry_hint') }}</div>
    </div>
    <button type="submit" class="btn btn-primary">{{ trans('admin.save') }}</button>
    <a href="{{ url('admin/users/' ~ user.id ~ '/services') }}" class="btn btn-secondary">{{ trans('admin.cancel') }}</a>
</form>
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}