{% extends 'base.tpl' %}

{% block title %}{{ trans('balance.history') }} — {{ site_name }}{% endblock %}

{% block content %}
    <div class="widget-card p-0 overflow-hidden">
        <div class="p-3 border-bottom" style="border-color: var(--border-color);">
            <h4 class="mb-0"><i class="fas fa-history me-2" style="color: var(--accent);"></i>{{ trans('balance.history') }}</h4>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
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
                        <tr><td colspan="4" class="text-center text-muted py-3">{{ trans('balance.history_empty') }}</td></tr>
                    {% endfor %}
                </tbody>
            </table>
        </div>
    </div>
    {% include 'partials/pagination.tpl' with {'current': page, 'total': total, 'per_page': per_page, 'url': '/balance/history'} %}
{% endblock %}