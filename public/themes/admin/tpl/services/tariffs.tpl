{% extends "base.tpl" %}

{% block title %}Тарифы: {{ service.name }}{% endblock %}
{% block page_title %}Тарифы услуги «{{ service.name }}»{% endblock %}

{% block content %}
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ url('admin/services') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>К услугам</a>
    <a href="{{ url('admin/services/' ~ service.id ~ '/tariffs/add') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Добавить тариф</a>
</div>

<div class="card p-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Длительность (дней)</th>
                    <th>Цена (₽)</th>
                    <th>Активен</th>
                    <th>Порядок</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                {% for tariff in tariffs %}
                <tr>
                    <td>{{ tariff.id }}</td>
                    <td>{{ tariff.durationDays }}</td>
                    <td>{{ tariff.price }} ₽</td>
                    <td>{{ tariff.isActive ? 'Да' : 'Нет' }}</td>
                    <td>{{ tariff.sortOrder }}</td>
                    <td>
                        <a href="{{ url('admin/services/' ~ service.id ~ '/tariffs/edit/' ~ tariff.id) }}" class="btn btn-sm btn-outline-primary">Ред.</a>
                        <form method="post" action="{{ url('admin/services/' ~ service.id ~ '/tariffs/delete/' ~ tariff.id) }}" style="display:inline;" onsubmit="return confirm('Удалить тариф?');">
                            <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                            <button class="btn btn-sm btn-outline-danger">Удалить</button>
                        </form>
                    </td>
                </tr>
                {% else %}
                <tr><td colspan="6" class="text-center text-muted py-3">Нет тарифов</td></tr>
                {% endfor %}
            </tbody>
        </table>
    </div>
</div>
{% endblock %}