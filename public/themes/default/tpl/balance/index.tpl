{% extends 'base.tpl' %}

{% block title %}{{ trans('balance.title') }} — {{ site_name }}{% endblock %}

{% block content %}
    <div class="row g-4">
        <div class="col-md-6">
            <div class="widget-card p-4 text-center">
                <i class="fas fa-wallet fa-3x mb-3" style="color: var(--accent);"></i>
                <h3>{{ trans('balance.your_balance') }}</h3>
                <div class="display-4 fw-bold mb-3">{{ user.getMoney() }} ₽</div>
                <p class="text-secondary">{{ trans('balance.total_recharge') }}: {{ total_recharge }} ₽</p>
                <a href="{{ url('/balance/history') }}" class="link-accent">{{ trans('balance.history') }} →</a>
            </div>
        </div>
        <div class="col-md-6">
            <div class="widget-card p-4">
                <h5 class="mb-3">{{ trans('balance.top_up') }}</h5>
                <p class="text-secondary">{{ trans('balance.choose_method') }}</p>
                <div class="list-group">
                    <a href="{{ url('/payment') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" style="background: var(--bg-surface); color: var(--text-primary); border-color: var(--border-color);">
                        <span><i class="fab fa-yandex me-2"></i>{{ trans('balance.yoomoney') }}</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <div class="list-group-item text-secondary" style="background: var(--bg-surface); border-color: var(--border-color);">
                        {{ trans('balance.other_methods') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="widget-card p-3 mt-4">
        <h5 class="mb-3">{{ trans('balance.recent') }}</h5>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr><th>{{ trans('balance.date') }}</th><th>{{ trans('balance.type') }}</th><th>{{ trans('balance.amount') }}</th><th>{{ trans('balance.description') }}</th></tr>
                </thead>
                <tbody>
                    {% for log in logs %}
                        <tr>
                            <td>{{ log.created_at|format_datetime('medium', 'short', locale=locale) }}</td>
                            <td>{{ log.type == 0 ? trans('balance.replenishment') : trans('balance.write_off') }}</td>
                            <td class="{{ log.type == 0 ? 'text-success' : 'text-danger' }}">
                                {{ log.type == 0 ? '+' : '-' }}{{ log.amount }} ₽
                            </td>
                            <td>{{ log.title }}</td>
                        </tr>
                    {% else %}
                        <tr><td colspan="4" class="text-center text-muted">{{ trans('balance.history_empty') }}</td></tr>
                    {% endfor %}
                </tbody>
            </table>
        </div>
    </div>
{% endblock %}