{% extends 'base.tpl' %}

{% block title %}Главная — {{ site_name }}{% endblock %}

{% block content %}
    {# Приветственный блок #}
    <div class="widget-card p-4 mb-4">
        <h4 class="mb-3" style="color: var(--accent);">
            <i class="fas fa-server me-2"></i>Добро пожаловать на {{ site_name }}!
        </h4>
        <p class="text-secondary">Современная панель управления игровыми серверами с форумом, VIP-системой, мониторингом, новостной лентой и тикет-системой.</p>
        {% if not app.user %}
            <div class="mt-3">
                <a href="{{ url('/login') }}" class="btn btn-primary me-2">Войти</a>
                <a href="{{ url('/register') }}" class="btn btn-outline-secondary">Регистрация</a>
            </div>
        {% endif %}
    </div>

    {# Чат (если модуль включен) #}
    {% if module_enabled('chat') %}
    <div class="widget-card p-3 mb-4" x-data="chatWidget" x-init="init">
        <h5 class="d-flex align-items-center mb-3">
            <i class="fas fa-comment-dots me-2" style="color: var(--accent);"></i>
            Общий чат
        </h5>
        <div class="chat-messages" style="max-height: 350px; overflow-y: auto;" x-ref="messagesContainer">
            <template x-for="msg in messages" :key="msg.id">
                <div class="d-flex mb-3">
                    <img :src="msg.avatar" width="36" height="36" class="rounded-circle me-2">
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong x-text="msg.username" style="color: var(--accent-bright);"></strong>
                            <small class="text-secondary" x-text="msg.time"></small>
                        </div>
                        <div x-html="msg.text" class="small"></div>
                    </div>
                </div>
            </template>
            <div x-show="messages.length === 0" class="text-center text-muted py-3">
                Нет сообщений. Будьте первым!
            </div>
        </div>
        {% if app.user %}
        <div class="input-group mt-3">
            <input type="text" class="form-control" placeholder="Напишите сообщение..." x-model="newMessage" @keyup.enter="sendMessage">
            <button class="btn btn-primary" @click="sendMessage">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
        {% else %}
        <div class="alert alert-light mt-3 mb-0 text-center">
            <a href="{{ url('/login') }}">Войдите</a>, чтобы писать в чат.
        </div>
        {% endif %}
    </div>
    {% endif %}

    {# Мониторинг серверов (если модуль включен) #}
    {% if module_enabled('monitor') %}
    <div class="widget-card p-3" x-data="monitorWidget" x-init="init">
        <h5 class="d-flex align-items-center mb-3">
            <i class="fas fa-chart-line me-2" style="color: var(--accent);"></i>
            Мониторинг серверов
        </h5>
        <div x-show="loading" class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            <span class="ms-2">Загрузка...</span>
        </div>
        <div x-show="!loading">
            <template x-if="servers.length === 0">
                <div class="text-center text-muted py-3">Нет активных серверов</div>
            </template>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr class="small">
                            <th>Статус</th>
                            <th>Название</th>
                            <th>Игроки</th>
                            <th>Карта</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="srv in servers" :key="srv.id">
                            <tr>
                                <td x-html="srv.status_html"></td>
                                <td>
                                    <a :href="'steam://connect/' + srv.address" x-text="srv.server_name" style="color: var(--text-primary);"></a>
                                </td>
                                <td x-text="srv.players"></td>
                                <td x-text="srv.map"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="text-end mt-2">
            <div class="text-end mt-2">
                <a href="{{ url('/monitor') }}" class="link-accent small">
                    Подробнее <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    {% endif %}
{% endblock %}