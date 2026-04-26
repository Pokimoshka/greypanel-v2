{% extends "base.tpl" %}

{% block title %}{{ service ? 'Редактировать услугу' : 'Добавить услугу' }}{% endblock %}

{% block content %}
<h1>{{ service ? 'Редактирование' : 'Создание' }} услуги</h1>

{% if error %}
    <div class="alert alert-danger">{{ error }}</div>
{% endif %}

<form method="post">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <div class="mb-3">
        <label class="form-label">Название</label>
        <input type="text" name="name" class="form-control" value="{{ service.name ?? '' }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Описание</label>
        <textarea name="description" class="form-control" rows="3">{{ service.description ?? '' }}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Права (флаги для сервера)</label>
        <input type="text" name="rights" class="form-control" value="{{ service.rights ?? '' }}" placeholder="abcdef">
        <div class="form-text">Строка флагов, которые будут выдаваться на сервере (AMX/SourceMod).</div>
    </div>
    <div class="mb-3">
        <label class="form-label">Серверы, на которых будет доступна услуга</label>
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
        <label class="form-label">Группа, которой доступна услуга</label>
        <select name="group_id" class="form-select">
            <option value="">Все группы</option>
            {% for g in all_groups %}
                <option value="{{ g.id }}" {{ service and service.groupId == g.id ? 'selected' : '' }}>{{ g.name }}</option>
            {% endfor %}
        </select>
        <div class="form-text">Оставьте пустым, чтобы услуга была доступна всем.</div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Порядок сортировки</label>
            <input type="number" name="sort_order" class="form-control" value="{{ service.sortOrder ?? 0 }}">
        </div>
        <div class="col-md-6 mb-3 form-check d-flex align-items-end">
            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" {{ not service or service.isActive ? 'checked' : '' }}>
            <label class="form-check-label ms-2" for="is_active">Активна</label>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Сохранить</button>
    <a href="{{ url('admin/services') }}" class="btn btn-secondary">Отмена</a>
</form>
{% if service %}
    <div class="card mt-4">
        <div class="card-header">Тарифы услуги «{{ service.name }}»</div>
        <div class="card-body">
            <table class="table">
                <thead><tr><th>Длительность (дней)</th><th>Цена (₽)</th><th>Действия</th></tr></thead>
                <tbody>
                    {% for t in tariffs %}
                    <tr>
                        <td>{{ t.durationDays }}</td>
                        <td>{{ t.price }}</td>
                        <td>
                            <a href="{{ url('admin/services/' ~ service.id ~ '/tariffs/edit/' ~ t.id) }}" class="btn btn-sm btn-outline-primary">Ред.</a>
                            <form method="post" action="{{ url('admin/services/' ~ service.id ~ '/tariffs/delete/' ~ t.id) }}" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                                <button class="btn btn-sm btn-outline-danger">Удалить</button>
                            </form>
                        </td>
                    </tr>
                    {% endfor %}
                </tbody>
            </table>
            <a href="{{ url('admin/services/' ~ service.id ~ '/tariffs/add') }}" class="btn btn-primary">Добавить тариф</a>
        </div>
    </div>
{% endif %}
{% endblock %}