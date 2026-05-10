{% extends "base.tpl" %}

{% block title %}{{ trans('admin.payments') }}{% endblock %}
{% block page_title %}{{ trans('admin.yoomoney_settings') }}{% endblock %}

{% block content %}
<div class="card">
    <div class="card-header"><i class="fas fa-ruble-sign me-2"></i>{{ trans('admin.yoomoney') }}</div>
    <div class="card-body">
        <form method="post">
            <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
            <div class="mb-3">
                <label class="form-label">{{ trans('admin.wallet') }}</label>
                <input type="text" name="yoomoney_wallet" class="form-control" value="{{ wallet }}">
            </div>
            <div class="mb-3">
                <label class="form-label">{{ trans('admin.secret_key') }}</label>
                <input type="password" name="yoomoney_secret" class="form-control" placeholder="******">
                <div class="form-text">{{ trans('admin.secret_key_hint') }}</div>
            </div>

            <div class="alert alert-info mt-3">
                <h5><i class="fas fa-link me-2"></i>{{ trans('admin.url_notifications') }}</h5>
                <p>{{ trans('admin.url_notifications_hint') }}</p>
                <div class="input-group">
                    <input type="text" class="form-control" 
                           value="{{ site_url }}/payment/yoomoney/notify" 
                           readonly>
                    <button class="btn btn-outline-secondary" type="button" 
                            onclick="navigator.clipboard.writeText(this.previousElementSibling.value)">
                        <i class="fas fa-copy"></i> {{ trans('admin.copy') }}
                    </button>
                </div>
                <small class="text-muted mt-2 d-block">
                    {{ trans('admin.yoomoney_notify_hint') }}
                    <a href="https://yoomoney.ru/transfer/myservices/http-notification" target="_blank">yoomoney.ru/myservices</a>.
                </small>
            </div>

            <button class="btn btn-primary">{{ trans('admin.save') }}</button>
        </form>
    </div>
</div>
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}