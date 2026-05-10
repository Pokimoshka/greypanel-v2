{% extends "base.tpl" %}

{% block title %}{{ trans('admin.logs') }}{% endblock %}
{% block page_title %}{{ trans('admin.logs') }}{% endblock %}

{% block content %}
<div class="card p-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>ID</th><th>{{ trans('admin.user') }}</th><th>{{ trans('admin.action') }}</th><th>{{ trans('admin.details') }}</th><th>IP</th><th>{{ trans('admin.date') }}</th></tr>
            </thead>
            <tbody>
                {% for log in logs %}
                <tr>
                    <td>{{ log.id }}</td>
                    <td>{{ log.username ?? trans('admin.guest') }}</td>
                    <td>{{ log.action }}</td>
                    <td>{{ log.details }}</td>
                    <td>{{ log.ip }}</td>
                    <td>{{ log.created_at|format_datetime('medium', 'short', locale=locale) }}</td>
                </tr>
                {% endfor %}
            </tbody>
        </table>
    </div>
</div>
{% include 'partials/pagination.tpl' with {'current': page, 'total': total, 'per_page': per_page, 'url': '/admin/logs'} %}
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}