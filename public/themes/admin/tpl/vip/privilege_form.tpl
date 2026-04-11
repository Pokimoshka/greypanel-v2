{% extends "base.tpl" %}

{% block title %}{{ privilege ? 'Редактировать' : 'Добавить' }} привилегию{% endblock %}

{% block content %}
<div class="card">
    <div class="card-header">
        <h3>{{ privilege ? 'Редактирование привилегии' : 'Новая привилегия' }}</h3>
    </div>
    <div class="card-body">
        <form method="post">
            <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
            <div class="mb-3">
                <label class="form-label">Название</label>
                <input type="text" name="title" class="form-control" value="{{ privilege.title ?? '' }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Флаги (например: abcdef)</label>
                <input type="text" name="flags" class="form-control" value="{{ privilege.flags ?? '' }}" required>
                <div class="form-text">Строка флагов для AMX / SourceMod</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Цена за день (руб.)</label>
                <input type="number" name="price_per_day" class="form-control" value="{{ privilege.price_per_day ?? 0 }}" min="0" step="1" required>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
            <a href="/admin/vip/servers/{{ server.id }}/privileges" class="btn btn-secondary">Отмена</a>
        </form>
    </div>
</div>
{% endblock %}