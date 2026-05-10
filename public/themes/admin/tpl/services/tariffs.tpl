{% extends "base.tpl" %}

{% block title %}{{ trans('admin.tariffs') }}: {{ service.name }}{% endblock %}
{% block page_title %}{{ trans('admin.tariffs_for') }} «{{ service.name }}»{% endblock %}

{% block content %}
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ url('admin/services') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>{{ trans('admin.back_to_services') }}</a>
    <a href="{{ url('admin/services/' ~ service.id ~ '/tariffs/add') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>{{ trans('admin.add_tariff') }}</a>
</div>

<div class="card p-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>{{ trans('admin.tariff_duration') }}</th>
                    <th>{{ trans('admin.tariff_price') }}</th>
                    <th>{{ trans('admin.tariff_active') }}</th>
                    <th>{{ trans('admin.sort_order') }}</th>
                    <th>{{ trans('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                {% for tariff in tariffs %}
                <tr>
                    <td>{{ tariff.id }}</td>
                    <td>{{ tariff.durationDays }}</td>
                    <td>{{ tariff.price }} ₽</td>
                    <td>{{ tariff.isActive ? trans('common.yes') : trans('common.no') }}</td>
                    <td>{{ tariff.sortOrder }}</td>
                    <td>
                        <a href="{{ url('admin/services/' ~ service.id ~ '/tariffs/edit/' ~ tariff.id) }}" class="btn btn-sm btn-outline-primary">{{ trans('admin.edit') }}</a>
                        <form method="post" action="{{ url('admin/services/' ~ service.id ~ '/tariffs/delete/' ~ tariff.id) }}" style="display:inline;" onsubmit="return confirm('{{ trans('admin.confirm_delete') }}');">
                            <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                            <button class="btn btn-sm btn-outline-danger">{{ trans('admin.delete') }}</button>
                        </form>
                    </td>
                </tr>
                {% else %}
                <tr><td colspan="6" class="text-center text-muted py-3">{{ trans('admin.no_tariffs') }}</td></tr>
                {% endfor %}
            </tbody>
        </table>
    </div>
</div>
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}