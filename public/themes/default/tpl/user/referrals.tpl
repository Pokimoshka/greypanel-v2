{% extends 'base.tpl' %}

{% block title %}Рефералы — {{ site_name }}{% endblock %}

{% block content %}
    <div class="widget-card p-4">
        <h4 class="mb-3"><i class="fas fa-users me-2" style="color: var(--accent);"></i>Реферальная программа</h4>
        <p class="text-secondary">Приглашайте друзей и получайте 10% от их пополнений.</p>
        
        <div class="alert" style="background: var(--bg-surface); border-color: var(--border-color);">
            <strong>Ваша реферальная ссылка:</strong>
            <div class="input-group mt-2">
                <input type="text" class="form-control" value="{{ ref_link }}" readonly id="refLink">
                <button class="btn btn-primary" onclick="copyRefLink()">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
        </div>
        
        <div class="d-flex justify-content-between mb-3">
            <h5 class="mb-0">Приглашённые пользователи ({{ referrals|length }})</h5>
            <span class="text-secondary">Заработано: <strong>{{ earnings }} ₽</strong></span>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr><th>Ник</th><th>Email</th><th>Дата регистрации</th></tr>
                </thead>
                <tbody>
                    {% for ref in referrals %}
                        <tr>
                            <td>{{ ref.username }}</td>
                            <td>{{ ref.email }}</td>
                            <td>{{ ref.reg_data|date('d.m.Y H:i') }}</td>
                        </tr>
                    {% else %}
                        <tr><td colspan="3" class="text-center text-muted py-3">Нет приглашённых</td></tr>
                    {% endfor %}
                </tbody>
            </table>
        </div>
    </div>
{% endblock %}