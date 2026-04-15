{% extends "base.tpl" %}

{% block title %}Панель управления{% endblock %}
{% block page_title %}Дашборд{% endblock %}

{% block content %}
<div class="row g-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary h-100 animate__animated animate__fadeInUp">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase small opacity-75">Пользователи</h6>
                        <h2 class="mb-0">{{ total_users }}</h2>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
                <small class="opacity-75">За всё время</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success h-100 animate__animated animate__fadeInUp animate__delay-1s">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase small opacity-75">Темы форума</h6>
                        <h2 class="mb-0">{{ total_threads }}</h2>
                    </div>
                    <i class="fas fa-comments fa-3x opacity-50"></i>
                </div>
                <small class="opacity-75">Всего тем</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning h-100 animate__animated animate__fadeInUp animate__delay-2s">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase small opacity-75">VIP активации</h6>
                        <h2 class="mb-0">{{ total_vip }}</h2>
                    </div>
                    <i class="fas fa-crown fa-3x opacity-50"></i>
                </div>
                <small class="opacity-75">Действующих</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info h-100 animate__animated animate__fadeInUp animate__delay-3s">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase small opacity-75">Онлайн</h6>
                        <h2 class="mb-0">{{ online_count|default(0) }}</h2>
                    </div>
                    <i class="fas fa-eye fa-3x opacity-50"></i>
                </div>
                <small class="opacity-75">Сейчас на сайте</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-line me-2"></i>Регистрации за 7 дней
            </div>
            <div class="card-body">
                <canvas id="registrationsChart" height="200"></canvas>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between">
                <span><i class="fas fa-history me-2"></i>Последние действия</span>
                <a href="{{ url('admin/logs') }}" class="btn btn-sm btn-outline-primary">Все логи →</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Пользователь</th><th>Действие</th><th>IP</th><th>Дата</th></tr>
                        </thead>
                        <tbody>
                        {% for log in recent_logs %}
                            <tr>
                                <td>{{ log.username ?? 'Гость' }}</td>
                                <td>{{ log.action }}</td>
                                <td>{{ log.ip }}</td>
                                <td>{{ log.created_at|date('d.m.Y H:i') }}</td>
                            </tr>
                        {% else %}
                            <tr><td colspan="4" class="text-center text-muted py-3">Нет записей</td></tr>
                        {% endfor %}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-plus me-2"></i>Последние пользователи
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                {% for user in recent_users %}
                    <li class="list-group-item bg-transparent d-flex justify-content-between">
                        <div>
                            <img src="{{ user.avatar }}" width="30" height="30" class="rounded-circle me-2">
                            <a href="{{ url('admin/users/edit/' ~ user.id) }}">{{ user.username }}</a>
                        </div>
                        <small class="text-secondary">{{ user.regData|date('d.m.Y') }}</small>
                    </li>
                {% else %}
                    <li class="list-group-item text-center text-muted">Нет пользователей</li>
                {% endfor %}
                </ul>
            </div>
            <div class="card-footer text-center">
                <a href="{{ url('admin/users') }}" class="btn btn-sm btn-outline-primary">Все пользователи →</a>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <i class="fas fa-bolt me-2"></i>Быстрые действия
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ url('admin/vip/servers') }}" class="btn btn-outline-primary"><i class="fas fa-crown me-2"></i>VIP серверы</a>
                    <a href="{{ url('admin/forum/categories') }}" class="btn btn-outline-success"><i class="fas fa-comments me-2"></i>Форум</a>
                    <a href="{{ url('admin/server-settings') }}" class="btn btn-outline-info"><i class="fas fa-server me-2"></i>Мониторинг</a>
                    <a href="{{ url('admin/themes') }}" class="btn btn-outline-warning"><i class="fas fa-palette me-2"></i>Темы</a>
                    <a href="{{ url('admin/modules') }}" class="btn btn-outline-secondary"><i class="fas fa-puzzle-piece me-2"></i>Модули</a>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}

{% block scripts %}
{{ parent() }}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('/admin/stats/registrations')
        .then(res => res.json())
        .then(data => {
            const ctx = document.getElementById('registrationsChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Регистрации',
                        data: data.values,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: { legend: { labels: { color: getComputedStyle(document.body).getPropertyValue('--admin-text-secondary') } } },
                    scales: {
                        y: { ticks: { color: getComputedStyle(document.body).getPropertyValue('--admin-text-secondary') }, grid: { color: getComputedStyle(document.body).getPropertyValue('--admin-border') } },
                        x: { ticks: { color: getComputedStyle(document.body).getPropertyValue('--admin-text-secondary') }, grid: { color: getComputedStyle(document.body).getPropertyValue('--admin-border') } }
                    }
                }
            });
        });
});
</script>
{% endblock %}