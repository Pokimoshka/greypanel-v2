{% extends 'base.tpl' %}

{% block title %}Привилегия активирована — {{ site_name }}{% endblock %}

{% block content %}
    <div class="widget-card p-5 text-center">
        <i class="fas fa-check-circle fa-5x mb-3" style="color: #10b981;"></i>
        <h3>Привилегия успешно активирована!</h3>
        <p class="text-secondary">Теперь вы можете заходить на сервер с новыми возможностями.</p>
        <div class="alert alert-info mt-3">
            Не забудьте установить пароль в консоли:<br>
            <code>setinfo "_pw" "{{ app.user.username }}"</code>
        </div>
        <a href="{{ url('/vip') }}" class="btn btn-primary mt-3">Вернуться к списку серверов</a>
    </div>
{% endblock %}