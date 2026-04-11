{% extends "base.tpl" %}

{% block title %}Панель управления{% endblock %}

{% block page_title %}Дашборд{% endblock %}

{% block content %}
<div class="row g-4">
    <!-- Статистические карточки -->
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3 h-100 animate__animated animate__fadeInUp">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase fw-bold">Пользователи</h6>
                        <h2 class="display-6 mb-0">{{ total_users }}</h2>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
                <div class="mt-3 small">За всё время</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3 h-100 animate__animated animate__fadeInUp animate__delay-1s">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase fw-bold">Темы на форуме</h6>
                        <h2 class="display-6 mb-0">{{ total_threads }}</h2>
                    </div>
                    <i class="fas fa-comments fa-3x opacity-50"></i>
                </div>
                <div class="mt-3 small">Всего тем</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning mb-3 h-100 animate__animated animate__fadeInUp animate__delay-2s">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase fw-bold">VIP активации</h6>
                        <h2 class="display-6 mb-0">{{ total_vip }}</h2>
                    </div>
                    <i class="fas fa-crown fa-3x opacity-50"></i>
                </div>
                <div class="mt-3 small">Действующих</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3 h-100 animate__animated animate__fadeInUp animate__delay-3s">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase fw-bold">Онлайн</h6>
                        <h2 class="display-6 mb-0">{{ online_count|default(0) }}</h2>
                    </div>
                    <i class="fas fa-eye fa-3x opacity-50"></i>
                </div>
                <div class="mt-3 small">Сейчас на сайте</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <!-- График регистраций -->
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-chart-line me-2"></i> Регистрации за последние 7 дней
            </div>
            <div class="card-body">
                <canvas id="registrationsChart" height="200"></canvas>
            </div>
        </div>

        <!-- Последние логи действий -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <span><i class="fas fa-history me-2"></i> Последние действия</span>
                <a href="{{ url('admin/logs') }}" class="btn btn-sm btn-outline-light">Все логи →</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr><th>Пользователь</th><th>Действие</th><th>IP</th><th>Дата</th></tr>
                        </thead>
                        <tbody>
                        {% for log in recent_logs %}
                            <tr>
                                <td>{{ log.username ?? 'Гость' }} ({# log.user_id #})</td>
                                <td>{{ log.action }}</td>
                                <td>{{ log.ip }}</td>
                                <td>{{ log.created_at|date('d.m.Y H:i:s') }}</td>
                            </tr>
                        {% else %}
                            <tr><td colspan="4" class="text-center">Нет записей</td></tr>
                        {% endfor %}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Правая колонка -->
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-user-plus me-2"></i> Последние зарегистрированные
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                {% for user in recent_users %}
                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center">
                        <div>
                            <img src="{{ user.avatar }}" width="30" height="30" class="rounded-circle me-2">
                            <a href="{{ url('admin/users/edit/' ~ user.id) }}">{{ user.username }}</a>
                        </div>
                        <span class="small text-muted">{{ user.reg_data|date('d.m.Y') }}</span>
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

        <!-- Быстрые ссылки -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-bolt me-2"></i> Быстрые действия
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ url('admin/vip/servers') }}" class="btn btn-outline-primary"><i class="fas fa-crown me-2"></i> Управление VIP</a>
                    <a href="{{ url('admin/forum/categories') }}" class="btn btn-outline-success"><i class="fas fa-comments me-2"></i> Настройки форума</a>
                    <a href="{{ url('admin/monitor/servers') }}" class="btn btn-outline-info"><i class="fas fa-server me-2"></i> Мониторинг</a>
                    <a href="{{ url('admin/themes') }}" class="btn btn-outline-warning"><i class="fas fa-palette me-2"></i> Темы оформления</a>
                    <a href="{{ url('admin/modules') }}" class="btn btn-outline-secondary"><i class="fas fa-puzzle-piece me-2"></i> Модули</a>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}

{% block scripts %}
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
                    plugins: { legend: { labels: { color: '#cbd5e1' } } },
                    scales: {
                        y: {
                            ticks: {
                                stepSize: 1,
                                precision: 0,
                                color: '#cbd5e1'
                            },
                            grid: { color: '#334155' },
                            min: 0
                        },
                        x: {
                            ticks: { color: '#cbd5e1' },
                            grid: { color: '#334155' }
                        }
                    }
                }
            });
        })
        .catch(() => console.log('Не удалось загрузить статистику'));
});
</script>
{% endblock %}