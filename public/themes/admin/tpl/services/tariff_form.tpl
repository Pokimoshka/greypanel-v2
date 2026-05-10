{% extends "base.tpl" %}

{% block title %}{{ tariff ? trans('admin.edit_tariff') : trans('admin.add_tariff') }} — {{ service.name }}{% endblock %}

{% block content %}
<h1>{{ tariff ? trans('admin.edit_tariff') : trans('admin.add_tariff') }} {{ trans('admin.for_service') }} «{{ service.name }}»</h1>

{% if error %}
    <div class="alert alert-danger">{{ error }}</div>
{% endif %}

<form method="post">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">{{ trans('admin.tariff_duration') }}</label>
            <input type="number" name="duration_days" class="form-control" value="{{ tariff.durationDays ?? 30 }}" min="1" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">{{ trans('admin.tariff_price') }}</label>
            <input type="number" name="price" class="form-control" value="{{ tariff.price ?? 100 }}" min="1" required>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">{{ trans('admin.sort_order') }}</label>
            <input type="number" name="sort_order" class="form-control" value="{{ tariff.sortOrder ?? 0 }}">
        </div>
        <div class="col-md-6 mb-3 form-check d-flex align-items-end">
            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" {{ not tariff or tariff.isActive ? 'checked' : '' }}>
            <label class="form-check-label ms-2" for="is_active">{{ trans('admin.tariff_active') }}</label>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">{{ trans('admin.save') }}</button>
    <a href="{{ url('admin/services/' ~ service.id ~ '/tariffs') }}" class="btn btn-secondary">{{ trans('admin.cancel') }}</a>
</form>
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}