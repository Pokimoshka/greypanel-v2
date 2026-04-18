{% extends "base.tpl" %}

{% block title %}Безопасность{% endblock %}
{% block page_title %}Настройки безопасности{% endblock %}

{% block content %}
<div class="card">
    <div class="card-header">
        <i class="fas fa-shield-alt me-2"></i>Google reCAPTCHA
    </div>
    <div class="card-body">
        <form method="post" action="{{ url('admin/security/save') }}">
            <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
            <div class="mb-3 form-check">
                <input type="checkbox" name="recaptcha_enabled" class="form-check-input" id="recaptcha_enabled" value="1" {{ recaptcha_enabled ? 'checked' }}>
                <label class="form-check-label" for="recaptcha_enabled">Включить reCAPTCHA на формах входа и регистрации</label>
            </div>
            <div class="mb-3">
                <label class="form-label">Site Key</label>
                <input type="text" name="recaptcha_site_key" class="form-control" value="{{ recaptcha_site_key }}">
                <div class="form-text">Получите ключи на <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA Admin</a></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Secret Key</label>
                <input type="password" name="recaptcha_secret_key" class="form-control" value="{{ recaptcha_secret_key }}" autocomplete="off">
                <div class="form-text">Оставьте пустым, чтобы не менять</div>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
    </div>
</div>
{% endblock %}