{% extends "base.tpl" %}

{% block title %}Мой баланс{% endblock %}

{% block content %}
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Баланс</div>
            <div class="card-body">
                <h3>{{ app.user.money }} ₽</h3>
                <p>Всего пополнено: {{ total_recharge }} ₽</p>
                <a href="/balance/history" class="btn btn-secondary">История операций</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Пополнение счёта</div>
            <div class="card-body">
                <p>Выберите способ пополнения (в разработке):</p>
                <div class="alert alert-info">
                    Скоро здесь появятся платёжные системы: WebMoney, Robokassa, FreeKassa.
                </div>
            </div>
        </div>
    </div>
</div>

<h3 class="mt-4">Последние операции</h3>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Дата</th>
            <th>Тип</th>
            <th>Сумма</th>
            <th>Описание</th>
        </tr>
    </thead>
    <tbody>
        {% for log in logs %}
        <tr>
            <td>{{ log.created_at|date('d.m.Y H:i') }}</td>
            <td>{{ log.type == 0 ? 'Пополнение' : 'Списание' }}</td>
            <td>{{ log.type == 0 ? '+' : '-' }} {{ log.amount }} ₽</td>
            <td>{{ log.title }}</td>
        </tr>
        {% else %}
        <tr><td colspan="4" class="text-center">Нет операций</td></tr>
        {% endfor %}
    </tbody>
</table>
<a href="/balance/history" class="btn btn-link">Вся история →</a>
{% endblock %}