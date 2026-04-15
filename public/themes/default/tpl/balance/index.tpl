{% extends 'base.tpl' %}

{% block title %}Баланс — {{ site_name }}{% endblock %}

{% block content %}
    <div class="row g-4">
        <div class="col-md-6">
            <div class="widget-card p-4 text-center">
                <i class="fas fa-wallet fa-3x mb-3" style="color: var(--accent);"></i>
                <h3>Ваш баланс</h3>
                <div class="display-4 fw-bold mb-3">{{ app.user.money }} ₽</div>
                <p class="text-secondary">Всего пополнено: {{ total_recharge }} ₽</p>
                <a href="{{ url('/balance/history') }}" class="link-accent">История операций →</a>
            </div>
        </div>
        <div class="col-md-6">
            <div class="widget-card p-4">
                <h5 class="mb-3">Пополнение счёта</h5>
                <p class="text-secondary">Выберите способ пополнения:</p>
                <div class="list-group">
                    <a href="{{ url('/payment') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" style="background: var(--bg-surface); color: var(--text-primary); border-color: var(--border-color);">
                        <span><i class="fab fa-yandex me-2"></i>ЮMoney</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <div class="list-group-item text-secondary" style="background: var(--bg-surface); border-color: var(--border-color);">
                        Другие способы в разработке
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="widget-card p-3 mt-4">
        <h5 class="mb-3">Последние операции</h5>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr><th>Дата</th><th>Тип</th><th>Сумма</th><th>Описание</th></tr>
                </thead>
                <tbody>
                    {% for log in logs %}
                        <tr>
                            <td>{{ log.created_at|date('d.m.Y H:i') }}</td>
                            <td>{{ log.type == 0 ? 'Пополнение' : 'Списание' }}</td>
                            <td class="{{ log.type == 0 ? 'text-success' : 'text-danger' }}">
                                {{ log.type == 0 ? '+' : '-' }}{{ log.amount }} ₽
                            </td>
                            <td>{{ log.title }}</td>
                        </tr>
                    {% else %}
                        <tr><td colspan="4" class="text-center text-muted">Нет операций</td></tr>
                    {% endfor %}
                </tbody>
            </table>
        </div>
    </div>
{% endblock %}