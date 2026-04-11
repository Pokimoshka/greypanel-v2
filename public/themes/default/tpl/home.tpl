{% extends "base.tpl" %}

{% block title %}Главная{% endblock %}

{% block content %}
<div class="row g-4">
    <div class="col-lg-8">
        <!-- Последние темы -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-comments me-2"></i> Последние темы форума
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                {% for topic in last_topics %}
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="/forum/thread/{{ topic.id }}" class="fw-bold">{{ topic.title|slice(0, 60) }}</a>
                                <div class="small text-muted">
                                    <i class="fas fa-user"></i> {{ topic.author_name }} · 
                                    <i class="fas fa-clock"></i> {{ topic.created_at|date('d.m.Y H:i') }} · 
                                    <i class="fas fa-reply"></i> {{ topic.replies }}
                                </div>
                            </div>
                        </div>
                    </li>
                {% else %}
                    <li class="list-group-item text-center text-muted">Нет тем</li>
                {% endfor %}
                </ul>
            </div>
            <div class="card-footer text-center">
                <a href="/forum" class="btn btn-sm btn-outline-primary">Все темы →</a>
            </div>
        </div>

        <!-- Чат (в центре) -->
        {% if module_enabled('chat') %}
        <div class="card shadow-sm mb-4" x-data="chatWidget" x-init="init">
            <div class="card-header bg-info text-white">
                <i class="fas fa-comment me-2"></i> Чат
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;" x-ref="messagesContainer">
                <template x-for="msg in messages" :key="msg.id">
                    <div class="d-flex mb-3">
                        <img :src="msg.avatar" width="40" height="40" class="rounded-circle me-2">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <strong x-text="msg.username"></strong>
                                <small class="text-muted" x-text="msg.time"></small>
                            </div>
                            <div x-html="msg.text"></div>
                        </div>
                    </div>
                </template>
            </div>
            {% if app.user %}
            <div class="card-footer">
                <div class="input-group">
                    <input type="text" class="form-control" x-model="newMessage" @keyup.enter="sendMessage" placeholder="Ваше сообщение...">
                    <button class="btn btn-primary" @click="sendMessage">Отправить</button>
                </div>
            </div>
            {% endif %}
        </div>
        {% endif %}

        <!-- Мониторинг (в центре) -->
        {% if module_enabled('monitor') %}
        <div class="card shadow-sm" x-data="monitorWidget" x-init="init">
            <div class="card-header bg-success text-white">
                <i class="fas fa-server me-2"></i> Мониторинг серверов
            </div>
            <div class="card-body p-0">
                <div x-show="loading" class="text-center p-3">Загрузка...</div>
                <table class="table table-hover mb-0" x-show="!loading">
                    <thead>
                        <tr><th>Статус</th><th>Адрес</th><th>Название</th><th>Карта</th><th>Игроки</th></tr>
                    </thead>
                    <tbody>
                        <template x-for="s in servers" :key="s.id">
                            <tr>
                                <td x-html="s.status_html"></td>
                                <td><a :href="'steam://connect/' + s.address" x-text="s.address"></a></td>
                                <td x-text="s.server_name"></td>
                                <td x-text="s.map"></td>
                                <td x-text="s.players"></td>
                            </tr>
                        </template>
                        <tr x-show="servers.length === 0">
                            <td colspan="5" class="text-center text-muted">Нет серверов</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-center">
                <a href="/monitor" class="btn btn-sm btn-outline-success">Все серверы →</a>
            </div>
        </div>
        {% endif %}
    </div>

    <div class="col-lg-4">
        <!-- Онлайн -->
        <div class="card shadow-sm mb-4" id="online-widget">
            <div class="card-header bg-secondary text-white">
                <i class="fas fa-users me-2"></i> Сейчас на сайте (<span id="online-count">0</span>)
            </div>
            <div class="card-body p-0" id="online-list">
                <div class="text-center p-3">Загрузка...</div>
            </div>
        </div>

        <!-- Топ донатеров -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <i class="fas fa-trophy me-2"></i> Топ донатеров
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                {% for user in top_donators %}
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <img src="{{ user.avatar }}" width="30" height="30" class="rounded-circle me-2">
                            <a href="/profile/{{ user.id }}">{{ user.username }}</a>
                        </div>
                        <span class="badge bg-success rounded-pill">{{ user.all_money }} ₽</span>
                    </li>
                {% else %}
                    <li class="list-group-item text-center text-muted">Нет данных</li>
                {% endfor %}
                </ul>
            </div>
        </div>

        <!-- Последние баны -->
        {% if module_enabled('bans') %}
        <div class="card shadow-sm">
            <div class="card-header bg-danger text-white">
                <i class="fas fa-gavel me-2"></i> Последние баны
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                {% for ban in last_bans %}
                    <li class="list-group-item">
                        <div class="fw-bold">{{ ban.player_nick }}</div>
                        <div class="small text-muted">{{ ban.ban_reason ?: ban.cs_ban_reason }}</div>
                        <div class="small text-muted">Админ: {{ ban.admin_nick }} · {{ ban.ban_created|date('d.m.Y') }}</div>
                    </li>
                {% else %}
                    <li class="list-group-item text-center text-muted">Нет банов</li>
                {% endfor %}
                </ul>
            </div>
            <div class="card-footer text-center">
                <a href="/bans" class="btn btn-sm btn-outline-danger">Все баны →</a>
            </div>
        </div>
        {% endif %}
    </div>
</div>
{% endblock %}

{% block scripts %}
<script>
function loadOnline() {
    fetch('/online/data')
        .then(res => res.json())
        .then(data => {
            document.getElementById('online-count').innerText = data.count;
            const list = document.getElementById('online-list');
            if (data.users.length === 0) {
                list.innerHTML = '<div class="text-center p-3 text-muted">Нет активных пользователей</div>';
                return;
            }
            let html = '<ul class="list-group list-group-flush">';
            data.users.forEach(user => {
                html += `<li class="list-group-item d-flex align-items-center">
                            <img src="${user.avatar}" width="30" height="30" class="rounded-circle me-2">
                            <a href="/profile/${user.id}">${user.username}</a>
                            <span class="ms-auto small text-muted">${user.last_activity}</span>
                         </li>`;
            });
            html += '</ul>';
            list.innerHTML = html;
        })
        .catch(() => {
            document.getElementById('online-list').innerHTML = '<div class="text-center p-3 text-danger">Ошибка загрузки</div>';
        });
}
document.addEventListener('DOMContentLoaded', loadOnline);
setInterval(loadOnline, 30000);
</script>
{% endblock %}