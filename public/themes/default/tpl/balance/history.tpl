{% extends 'base.tpl' %}

{% block title %}История операций — {{ site_name }}{% endblock %}

{% block content %}
    <div class="widget-card p-0 overflow-hidden">
        <div class="p-3 border-bottom" style="border-color: var(--border-color);">
            <h4 class="mb-0"><i class="fas fa-history me-2" style="color: var(--accent);"></i>История операций</h4>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
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
                        <tr><td colspan="4" class="text-center text-muted py-3">Нет операций</td></tr>
                    {% endfor %}
                </tbody>
            </table>
        </div>
    </div>
    {% include 'partials/pagination.tpl' with {'current': page, 'total': total, 'per_page': per_page, 'url': '/balance/history'} %}
{% endblock %}