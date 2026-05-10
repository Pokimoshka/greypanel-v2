{% extends "base.tpl" %}

{% block title %}{{ service ? trans('admin.edit_service') : trans('admin.add_service') }}{% endblock %}

{% block content %}
<h1>{{ service ? trans('admin.edit_service') : trans('admin.add_service') }}</h1>

{% if error %}
    <div class="alert alert-danger">{{ error }}</div>
{% endif %}

<form method="post">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.service_name') }}</label>
        <input type="text" name="name" class="form-control" value="{{ service.name ?? '' }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.service_description') }}</label>
        <textarea name="description" class="form-control" rows="3">{{ service.description ?? '' }}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.rights') }}</label>
        <input type="text" name="rights" class="form-control" value="{{ service.rights ?? '' }}" placeholder="abcdef">
        <div class="form-text">{{ trans('admin.rights_hint') }}</div>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.servers_available') }}</label>
        <div class="row">
            {% for srv in all_servers %}
            <div class="col-md-6 mb-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="servers[]" value="{{ srv.id }}" id="srv_{{ srv.id }}" 
                           {{ srv.id in selected_server_ids ? 'checked' : '' }}>
                    <label class="form-check-label" for="srv_{{ srv.id }}">
                        {{ srv.ip }}:{{ srv.c_port }} ({{ srv.type }})
                    </label>
                </div>
            </div>
            {% endfor %}
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.group_restriction') }}</label>
        <select name="group_id" class="form-select">
            <option value="">{{ trans('admin.all_groups') }}</option>
            {% for g in all_groups %}
                <option value="{{ g.id }}" {{ service and service.groupId == g.id ? 'selected' : '' }}>{{ g.name }}</option>
            {% endfor %}
        </select>
        <div class="form-text">{{ trans('admin.group_restriction_hint') }}</div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">{{ trans('admin.sort_order') }}</label>
            <input type="number" name="sort_order" class="form-control" value="{{ service.sortOrder ?? 0 }}">
        </div>
        <div class="col-md-6 mb-3 form-check d-flex align-items-end">
            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" {{ not service or service.isActive ? 'checked' : '' }}>
            <label class="form-check-label ms-2" for="is_active">{{ trans('admin.active') }}</label>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">{{ trans('admin.save') }}</button>
    <a href="{{ url('admin/services') }}" class="btn btn-secondary">{{ trans('admin.cancel') }}</a>
</form>
{% if service %}
    <div class="card mt-4">
        <div class="card-header">{{ trans('admin.tariffs_for') }} «{{ service.name }}»</div>
        <div class="card-body">
            <table class="table">
                <thead><tr><th>{{ trans('admin.tariff_duration') }}</th><th>{{ trans('admin.tariff_price') }}</th><th>{{ trans('admin.actions') }}</th></tr></thead>
                <tbody>
                    {% for t in tariffs %}
                    <tr>
                        <td>{{ t.durationDays }}</td>
                        <td>{{ t.price }} ₽</td>
                        <td>
                            <a href="{{ url('admin/services/' ~ service.id ~ '/tariffs/edit/' ~ t.id) }}" class="btn btn-sm btn-outline-primary">{{ trans('admin.edit') }}</a>
                            <form method="post" action="{{ url('admin/services/' ~ service.id ~ '/tariffs/delete/' ~ t.id) }}" style="display:inline;" onsubmit="return confirm('{{ trans('admin.confirm_delete') }}');">
                                <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                                <button class="btn btn-sm btn-outline-danger">{{ trans('admin.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                    {% endfor %}
                </tbody>
            </table>
            <a href="{{ url('admin/services/' ~ service.id ~ '/tariffs/add') }}" class="btn btn-primary">{{ trans('admin.add_tariff') }}</a>
        </div>
    </div>
{% endif %}
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}