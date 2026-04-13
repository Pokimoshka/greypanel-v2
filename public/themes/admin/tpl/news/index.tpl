{% extends "base.tpl" %}

{% block title %}Новости{% endblock %}

{% block content %}
<div class="d-flex justify-content-between mb-3">
    <h1>Новости</h1>
    <a href="{{ url('admin/news/create') }}" class="btn btn-primary">Добавить новость</a>
</div>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Заголовок</th>
            <th>Автор</th>
            <th>Опубликовано</th>
            <th>Просмотры</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        {% for news in news_list %}
        <tr>
            <td>{{ news.id }}</td>
            <td><a href="{{ url('news/' ~ news.slug) }}" target="_blank">{{ news.title }}</a></td>
            <td>{{ news.author_name }}</td>
            <td>{{ news.is_published ? 'Да' : 'Нет' }}</td>
            <td>{{ news.views }}</td>
            <td>
                <a href="{{ url('admin/news/edit/' ~ news.id) }}" class="btn btn-sm btn-primary">Ред.</a>
                <form method="post" action="{{ url('admin/news/delete/' ~ news.id) }}" style="display:inline;" onsubmit="return confirm('Удалить?');">
                    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                    <button class="btn btn-sm btn-danger">Удалить</button>
                </form>
            </td>
        </tr>
        {% endfor %}
    </tbody>
</table>
{% include 'partials/pagination.tpl' with {
    'current': page,
    'total': total,
    'per_page': per_page,
    'url': '/admin/news'
} %}
{% endblock %}