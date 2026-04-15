{% extends 'base.tpl' %}

{% block title %}VIP привилегии — {{ site_name }}{% endblock %}

{% block content %}
    <h2 class="mb-4"><i class="fas fa-crown me-2" style="color: var(--accent-bright);"></i>Выберите сервер</h2>
    <div class="row g-4">
        {% for server in servers %}
            <div class="col-md-6 col-lg-4">
                <div class="widget-card h-100 p-3">
                    <h5>{{ server.server_name }}</h5>
                    <p class="text-secondary small mb-2">{{ server.server_ip }}:{{ server.server_port }}</p>
                    <hr style="border-color: var(--border-color);">
                    <a href="{{ url('/vip/' ~ server.id) }}" class="btn btn-primary w-100">Выбрать привилегии</a>
                </div>
            </div>
        {% else %}
            <div class="col-12">
                <div class="widget-card p-4 text-center text-muted">
                    <i class="fas fa-server fa-3x mb-3"></i>
                    <h5>Нет доступных серверов</h5>
                </div>
            </div>
        {% endfor %}
    </div>
{% endblock %}