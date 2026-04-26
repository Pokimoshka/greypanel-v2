{% extends "base.tpl" %}

{% block title %}{{ group ? 'Редактировать группу' : 'Добавить группу' }}{% endblock %}

{% block content %}
<h1>{{ group ? 'Редактирование' : 'Создание' }} группы</h1>

{% if error %}
    <div class="alert alert-danger">{{ error }}</div>
{% endif %}

<form method="post">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <div class="mb-3">
        <label class="form-label">Название группы</label>
        <input type="text" name="name" class="form-control" value="{{ group.name ?? '' }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Флаги (права, например "abc")</label>
        <input type="text" name="flags" class="form-control" value="{{ group.flags ?? '' }}" placeholder="abcdef..." @input="normalizeFlags($event)">
        <div class="form-text">Буквы от a до z. Каждая буква даёт определённые права.</div>
    </div>
    <div class="mb-3 form-check">
        <input type="checkbox" name="is_default" class="form-check-input" id="is_default" value="1" {{ group and group.isDefault ? 'checked' : '' }}>
        <label class="form-check-label" for="is_default">Группа по умолчанию (назначается новым пользователям)</label>
    </div>
    <button type="submit" class="btn btn-primary">Сохранить</button>
    <a href="{{ url('admin/groups') }}" class="btn btn-secondary">Отмена</a>
</form>
{% endblock %}