{% extends "base.tpl" %}

{% block title %}{{ trans('admin.security') }}{% endblock %}
{% block page_title %}{{ trans('admin.security') }}{% endblock %}

{% block content %}
<div class="card">
    <div class="card-header">
        <i class="fas fa-shield-alt me-2"></i>{{ trans('admin.recaptcha_settings') }}
    </div>
    <div class="card-body">
        <form method="post" action="{{ url('admin/security/save') }}">
            <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
            <div class="mb-3 form-check">
                <input type="checkbox" name="recaptcha_enabled" class="form-check-input" id="recaptcha_enabled" value="1" {{ recaptcha_enabled ? 'checked' }}>
                <label class="form-check-label" for="recaptcha_enabled">{{ trans('admin.enable_recaptcha') }}</label>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ trans('admin.site_key') }}</label>
                <input type="text" name="recaptcha_site_key" class="form-control" value="{{ recaptcha_site_key }}">
                <div class="form-text">{{ trans('admin.get_keys_hint') }} <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA Admin</a></div>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ trans('admin.secret_key_field') }}</label>
                <input type="password" name="recaptcha_secret_key" class="form-control" placeholder="******" autocomplete="off">
                <div class="form-text">{{ trans('admin.leave_empty') }}</div>
            </div>
            <button type="submit" class="btn btn-primary">{{ trans('admin.save') }}</button>
        </form>
    </div>
</div>
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}