{# 
  Правая колонка с виджетами
  Каждый виджет обёрнут в collapsibleWidget с сохранением состояния в localStorage
#}

<div class="sidebar-right">

    {# Виджет онлайн #}
    <div class="widget-card" x-data="collapsibleWidget('online', true)" x-cloak>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">
                <i class="fas fa-users me-2" style="color: var(--accent);"></i>
                Сейчас на сайте
                <span x-data="onlineWidget" x-init="init" x-show="!loading" class="ms-1">
                    (<span x-text="count"></span>)
                </span>
            </h6>
            <button class="btn btn-sm btn-link text-secondary" @click="toggle">
                <i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
        </div>
        <div x-show="open" x-collapse>
            <div x-data="onlineWidget" x-init="init">
                <div x-show="loading" class="text-center py-2">
                    <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                </div>
                <div x-show="!loading">
                    <template x-if="users.length === 0">
                        <div class="text-muted small text-center py-2">Нет активных пользователей</div>
                    </template>
                    <ul class="list-unstyled small">
                        <template x-for="user in users" :key="user.user_id">
                            <li class="d-flex align-items-center mb-2">
                                <img :src="user.avatar" width="24" height="24" class="rounded-circle me-2">
                                <a :href="'/profile/' + user.user_id" x-text="user.username" style="color: var(--text-primary);"></a>
                                <span class="ms-auto small text-muted" x-text="user.last_activity"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {# Виджет топ донатеров #}
    <div class="widget-card" x-data="collapsibleWidget('donators', true)" x-cloak>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">
                <i class="fas fa-trophy me-2" style="color: var(--accent);"></i>
                Топ донатеров
            </h6>
            <button class="btn btn-sm btn-link text-secondary" @click="toggle">
                <i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
        </div>
        <div x-show="open" x-collapse>
            <ul class="list-unstyled small">
                {% for user in top_donators|default([]) %}
                <li class="d-flex align-items-center mb-2">
                    <img src="{{ user.avatar }}" width="24" height="24" class="rounded-circle me-2">
                    <a href="/profile/{{ user.id }}" style="color: var(--text-primary);">{{ user.username }}</a>
                    <span class="ms-auto badge" style="background: var(--accent);">{{ user.all_money }} ₽</span>
                </li>
                {% else %}
                <li class="text-muted text-center py-2">Нет данных</li>
                {% endfor %}
            </ul>
        </div>
    </div>

    {# Виджет последних тем #}
    <div class="widget-card" x-data="collapsibleWidget('last_topics', true)" x-cloak>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">
                <i class="fas fa-clock me-2" style="color: var(--accent);"></i>
                Последние темы
            </h6>
            <button class="btn btn-sm btn-link text-secondary" @click="toggle">
                <i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
        </div>
        <div x-show="open" x-collapse>
            <div x-data="lastTopicsWidget" x-init="init">
                <div x-show="loading" class="text-center py-2">
                    <div class="spinner-border spinner-border-sm text-secondary"></div>
                </div>
                <ul x-show="!loading" class="list-unstyled small">
                    <template x-if="topics.length === 0">
                        <li class="text-muted text-center py-2">Нет тем</li>
                    </template>
                    <template x-for="topic in topics" :key="topic.id">
                        <li class="mb-2">
                            <a :href="'/forum/thread/' + topic.id" class="d-block text-truncate fw-medium" style="color: var(--text-primary);" x-text="topic.title"></a>
                            <small class="text-muted">
                                <span x-text="new Date(topic.created_at * 1000).toLocaleDateString('ru-RU')"></span>
                                · <span x-text="topic.author_name"></span>
                            </small>
                        </li>
                    </template>
                </ul>
                <div class="text-end mt-2">
                    <a href="{{ url('/forum') }}" class="link-accent small">
                        Все темы <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    {% if module_enabled('bans') %}
    <div class="widget-card" x-data="collapsibleWidget('last_bans', true)" x-cloak>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">
                <i class="fas fa-gavel me-2" style="color: var(--accent);"></i>
                Последние баны
            </h6>
            <button class="btn btn-sm btn-link text-secondary" @click="toggle">
                <i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
        </div>
        <div x-show="open" x-collapse>
            <div x-data="lastBansWidget" x-init="init">
                <div x-show="loading" class="text-center py-2">
                    <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                </div>
                <div x-show="!loading">
                    <template x-if="bans.length === 0">
                        <div class="text-muted small text-center py-2">Нет банов</div>
                    </template>
                    <ul class="list-unstyled small">
                        <template x-for="ban in bans" :key="ban.bid">
                            <li class="mb-2">
                                <div class="fw-medium" x-text="ban.player_nick"></div>
                                <div class="text-muted small" x-text="ban.ban_reason || ban.cs_ban_reason || 'Причина не указана'"></div>
                                <div class="text-muted small d-flex justify-content-between">
                                    <span x-text="'Админ: ' + ban.admin_nick"></span>
                                    <span x-text="new Date(ban.ban_created * 1000).toLocaleDateString('ru-RU')"></span>
                                </div>
                            </li>
                        </template>
                    </ul>
                    <div class="text-end mt-2">
                        <a href="{{ url('/bans') }}" class="link-accent small">
                            Все баны <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {% endif %}

</div>