{% extends "base.tpl" %}

{% block title %}{{ trans('admin.user_services') }} {{ user.username }}{% endblock %}
{% block page_title %}{{ trans('admin.user_services') }}: {{ user.username }}{% endblock %}

{% block content %}
<div class="d-flex justify-content-between mb-3">
    <a href="{{ url('admin/users') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> {{ trans('admin.back_to_users') }}</a>
    <a href="{{ url('admin/users/' ~ user.id ~ '/services/add') }}" class="btn btn-primary"><i class="fas fa-plus"></i> {{ trans('admin.issue_service') }}</a>
</div>

<div class="card p-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>{{ trans('admin.service') }}</th><th>{{ trans('admin.tariff_days') }}</th><th>{{ trans('admin.expiry_date') }}</th><th>{{ trans('admin.status') }}</th><th>{{ trans('admin.actions') }}</th></tr></thead>
            <tbody>
            {% for srv in services %}
                <tr>
                    <td>{{ srv.service_name }}</td>
                    <td>{{ srv.tariff_days }}</td>
                    <td>{{ srv.expires_at ? srv.expires_at|format_datetime('medium', 'short', locale=locale) : trans('admin.forever') }}</td>
                    <td><span class="badge {{ srv.is_active ? 'bg-success' : 'bg-danger' }}">{{ srv.is_active ? trans('admin.active') : trans('admin.expired') }}</span></td>
                    <td>
                        <a href="{{ url('admin/users/' ~ user.id ~ '/services/edit/' ~ srv.id) }}" class="btn btn-sm btn-outline-primary">{{ trans('admin.edit') }}</a>
                        <form method="post" action="{{ url('admin/users/' ~ user.id ~ '/services/delete/' ~ srv.id) }}" style="display:inline" onsubmit="return confirm('{{ trans('admin.confirm_delete_service') }}')">
                            <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                            <button class="btn btn-sm btn-outline-danger">{{ trans('admin.delete') }}</button>
                        </form>
                    </td>
                </tr>
            {% else %}
                <tr><td colspan="5" class="text-center text-muted">{{ trans('admin.no_services') }}</td></tr>
            {% endfor %}
            </tbody>
        </table>
    </div>
</div>
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}