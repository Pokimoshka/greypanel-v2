{% extends 'base.tpl' %}

{% block title %}Привилегии {{ server.server_name }} — {{ site_name }}{% endblock %}

{% block content %}
    <a href="{{ url('/vip') }}" class="link-accent small mb-3 d-inline-block">
        <i class="fas fa-arrow-left me-1"></i>К списку серверов
    </a>
    <h3 class="mb-4">{{ server.server_name }}</h3>
    
    {% if user_active %}
        <div class="alert alert-info">
            <i class="fas fa-check-circle me-1"></i>У вас активна привилегия до {{ user_active.expired_at|date('d.m.Y H:i') }}.
        </div>
    {% endif %}
    
    <form method="post" action="{{ url('/vip/confirm') }}">
        <input type="hidden" name="server_id" value="{{ server.id }}">
        <div class="widget-card p-0 overflow-hidden">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Привилегия</th><th>Флаги</th><th>Цена за день</th><th>Выбрать</th></tr>
                </thead>
                <tbody>
                    {% for priv in privileges %}
                        <tr>
                            <td>{{ priv.title }}</td>
                            <td><code>{{ priv.flags }}</code></td>
                            <td>{{ priv.price_per_day }} ₽</td>
                            <td><input type="radio" name="privilege_id" value="{{ priv.id }}" required></td>
                        </tr>
                    {% else %}
                        <tr><td colspan="4" class="text-center text-muted py-3">Нет привилегий</td></tr>
                    {% endfor %}
                </tbody>
            </table>
        </div>
        
        <div class="widget-card p-3 mt-4">
            <label class="form-label">Количество дней</label>
            <select name="days" class="form-select">
                <option value="7">7 дней</option><option value="14">14 дней</option><option value="30">30 дней</option>
                <option value="60">60 дней</option><option value="90">90 дней</option>
                <option value="180">180 дней</option>
                <option value="365">365 дней</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary btn-lg mt-3">
            <i class="fas fa-arrow-right me-1"></i>Далее
        </button>
    </form>
{% endblock %}