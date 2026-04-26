{% extends 'base.tpl' %}

{% block title %}Мониторинг серверов — {{ site_name }}{% endblock %}

{% block content %}
    <h2 class="mb-4" style="color: var(--accent-bright);">
        <i class="fas fa-chart-line me-2"></i>Мониторинг серверов
    </h2>

    <div class="widget-card p-0 overflow-hidden" x-data="monitorWidget" x-init="init">
        <div x-show="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-3 text-secondary">Загрузка данных...</p>
        </div>
        <div x-show="!loading">
            <template x-if="servers.length === 0">
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-server fa-3x mb-3" style="color: var(--accent);"></i>
                    <h5>Нет активных серверов</h5>
                    <p>Мониторинг в данный момент недоступен.</p>
                </div>
            </template>
            <template x-if="servers.length > 0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="small text-secondary">
                            <tr>
                                <th>Статус</th>
                                <th>Сервер</th>
                                <th>Игроки</th>
                                <th>Карта</th>
                                <th>Подключиться</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="srv in servers" :key="srv.id">
                                <tr>
                                    <td x-text="srv.status_html"></td>
                                    <td>
                                        <span x-text="srv.server_name"></span>
                                        <div class="small text-secondary" x-text="srv.address"></div>
                                    </td>
                                    <td x-text="srv.players"></td>
                                    <td x-text="srv.map"></td>
                                    <td>
                                        <a :href="'steam://connect/' + srv.address" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-gamepad me-1"></i>Подключиться
                                        </a>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </template>
        </div>
    </div>
{% endblock %}