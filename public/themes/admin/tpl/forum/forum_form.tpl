{% extends "base.tpl" %}

{% block title %}{{ forum ? 'Редактировать раздел' : 'Добавить раздел' }}{% endblock %}

{% block content %}
<h1>{{ forum ? 'Редактировать раздел' : 'Добавить раздел' }} в категории "{{ category.title }}"</h1>
<form method="post">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <div class="mb-3">
        <label class="form-label">Название</label>
        <input type="text" name="title" class="form-control" value="{{ forum.title|default('') }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Описание</label>
        <textarea name="description" class="form-control" rows="3">{{ forum.description|default('') }}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Иконка (FontAwesome класс)</label>
        <input type="text" name="icon" class="form-control" value="{{ forum.icon|default('fa fa-comments') }}" placeholder="fa fa-comments">
    </div>
    <div class="mb-3">
        <label class="form-label">Порядок сортировки</label>
        <input type="number" name="sort_order" class="form-control" value="{{ forum.sort_order|default(0) }}">
    </div>
    <button type="submit" class="btn btn-primary">Сохранить</button>
    <a href="/admin/forum/categories/{{ category.id }}/forums" class="btn btn-secondary">Отмена</a>
</form>
{% endblock %}