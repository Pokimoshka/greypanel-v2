{% extends 'base.tpl' %}

{% block title %}{{ trans('profile.referrals') }} — {{ site_name }}{% endblock %}

{% block content %}
    <div class="widget-card p-4">
        <h4 class="mb-3"><i class="fas fa-users me-2" style="color: var(--accent);"></i>{{ trans('profile.referral') }}</h4>
        <p class="text-secondary">{{ trans('profile.referral_desc') }}</p>
        
        <div class="alert" style="background: var(--bg-surface); border-color: var(--border-color);">
            <strong>{{ trans('profile.referral_link') }}:</strong>
            <div class="input-group mt-2">
                <input type="text" class="form-control" value="{{ ref_link }}" readonly id="refLink">
                <button class="btn btn-primary" onclick="copyRefLink()">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
        </div>
        
        <div class="d-flex justify-content-between mb-3">
            <h5 class="mb-0">{{ trans('profile.referrals') }} ({{ referrals|length }})</h5>
            <span class="text-secondary">{{ trans('profile.earnings') }}: <strong>{{ earnings }} ₽</strong></span>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr><th>{{ trans('auth.username') }}</th><th>{{ trans('auth.email') }}</th><th>{{ trans('profile.registration_date') }}</th></tr>
                </thead>
                <tbody>
                    {% for ref in referrals %}
                        <tr>
                            <td>{{ ref.username }}</td>
                            <td>{{ ref.email }}</td>
                            <td>{{ ref.reg_data|format_datetime('medium', 'short', locale=locale) }}</td>
                        </tr>
                    {% else %}
                        <tr><td colspan="3" class="text-center text-muted py-3">{{ trans('profile.no_referrals') }}</td></tr>
                    {% endfor %}
                </tbody>
            </table>
        </div>
    </div>
{% endblock %}