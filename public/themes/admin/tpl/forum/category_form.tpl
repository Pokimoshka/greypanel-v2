{% extends "base.tpl" %}

{% block title %}{{ category ? 'Редактировать категорию' : 'Добавить категорию' }}{% endblock %}

{% block content %}
<h1>{{ category ? 'Редактировать категорию' : 'Добавить категорию' }}</h1>
<form method="post">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <div class="mb-3">
        <label class="form-label">Название</label>
        <input type="text" name="title" class="form-control" value="{{ category.title|default('') }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Описание</label>
        <textarea name="description" class="form-control" rows="3">{{ category.description|default('') }}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Порядок сортировки</label>
        <input type="number" name="sort_order" class="form-control" value="{{ category.sort_order|default(0) }}">
    </div>
    <button type="submit" class="btn btn-primary">Сохранить</button>
    <a href="/admin/forum/categories" class="btn btn-secondary">Отмена</a>
</form>
{% endblock %}