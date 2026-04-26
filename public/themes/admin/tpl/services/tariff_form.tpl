{% extends "base.tpl" %}

{% block title %}{{ tariff ? 'Редактировать тариф' : 'Добавить тариф' }} — {{ service.name }}{% endblock %}

{% block content %}
<h1>{{ tariff ? 'Редактирование' : 'Создание' }} тарифа для «{{ service.name }}»</h1>

{% if error %}
    <div class="alert alert-danger">{{ error }}</div>
{% endif %}

<form method="post">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Длительность (дней)</label>
            <input type="number" name="duration_days" class="form-control" value="{{ tariff.durationDays ?? 30 }}" min="1" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Цена (₽)</label>
            <input type="number" name="price" class="form-control" value="{{ tariff.price ?? 100 }}" min="1" required>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Порядок сортировки</label>
            <input type="number" name="sort_order" class="form-control" value="{{ tariff.sortOrder ?? 0 }}">
        </div>
        <div class="col-md-6 mb-3 form-check d-flex align-items-end">
            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" {{ not tariff or tariff.isActive ? 'checked' : '' }}>
            <label class="form-check-label ms-2" for="is_active">Активен</label>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Сохранить</button>
    <a href="{{ url('admin/services/' ~ service.id ~ '/tariffs') }}" class="btn btn-secondary">Отмена</a>
</form>
{% endblock %}