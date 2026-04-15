{% extends "base.tpl" %}

{% block title %}Пользователи{% endblock %}
{% block page_title %}Управление пользователями{% endblock %}

{% block content %}
<div class="card">
    <div class="card-body">
        <form method="get" class="row g-2 mb-3">
            <div class="col-md-8">
                <input type="text" name="search" class="form-control" placeholder="Поиск по нику или email" value="{{ search }}">
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Искать</button>
            </div>
        </form>
    </div>
</div>

<div class="card p-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>ID</th><th>Ник</th><th>Email</th><th>Группа</th><th>Баланс</th><th>Бан</th><th></th></tr>
            </thead>
            <tbody>
                {% for user in users %}
                <tr>
                    <td>{{ user.id }}</td>
                    <td>{{ user.username }}</td>
                    <td>{{ user.email }}</td>
                    <td>{{ user.group }}</td>
                    <td>{{ user.money }} ₽</td>
                    <td>{{ user.banned ? 'Да' : 'Нет' }}</td>
                    <td><a href="{{ url('admin/users/edit/' ~ user.id) }}" class="btn btn-sm btn-outline-primary">Ред.</a></td>
                </tr>
                {% endfor %}
            </tbody>
        </table>
    </div>
</div>
{% include 'partials/pagination.tpl' with {'current': page, 'total': total, 'per_page': per_page, 'url': '/admin/users', 'params': {'search': search}} %}
{% endblock %}