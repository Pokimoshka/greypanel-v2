<div class="sidebar-right">

    <div class="widget-card" x-data="onlineWidget" x-init="init">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">
                <i class="fas fa-users me-2" style="color: var(--accent);"></i>
                {{ trans('widgets.online') }}
                <span x-show="!loading" class="ms-1">
                    (<span x-text="count"></span>)
                </span>
            </h6>
            <button class="btn btn-sm btn-link text-secondary" @click="open = !open">
                <i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
        </div>
        <div x-show="open" x-collapse>
            <div x-show="loading" class="text-center py-2">
                <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
            </div>
            <div x-show="!loading">
                <template x-if="users.length === 0">
                    <div class="text-muted small text-center py-2">{{ trans('widgets.no_users') }}</div>
                </template>
                <ul class="list-unstyled small">
                    <template x-for="user in users" :key="user.user_id">
                        <li class="d-flex align-items-center mb-2">
                            <img :src="user.avatar" width="24" height="24" class="rounded-circle me-2">
                            <a :href="'/profile/' + user.user_id" x-text="user.username" style="color: var(--text-primary);"></a>
                            <span class="ms-auto small text-muted" x-text="formatActivity(user.last_activity)"></span>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
    </div>

    <div class="widget-card" x-data="collapsibleWidget('donators', true)" x-cloak>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">
                <i class="fas fa-trophy me-2" style="color: var(--accent);"></i>
                {{ trans('widgets.top_donators') }}
            </h6>
            <button class="btn btn-sm btn-link text-secondary" @click="toggle">
                <i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
        </div>
        <div x-show="open" x-collapse>
            <div x-data="topDonatorsWidget" x-init="init">
                <div x-show="loading" class="text-center py-2">
                    <div class="spinner-border spinner-border-sm text-secondary"></div>
                </div>
                <ul x-show="!loading" class="list-unstyled small">
                    <template x-if="donators.length === 0">
                        <li class="text-muted text-center py-2">{{ trans('widgets.no_data') }}</li>
                    </template>
                    <template x-for="don in donators" :key="don.id">
                        <li class="d-flex align-items-center mb-2">
                            <img :src="don.avatar" width="24" height="24" class="rounded-circle me-2">
                            <a :href="'/profile/' + don.id" style="color: var(--text-primary);" x-text="don.username"></a>
                            <span class="ms-auto badge" style="background: var(--accent);" x-text="don.all_money + ' ₽'"></span>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
    </div>

    <div class="widget-card" x-data="collapsibleWidget('last_topics', true)" x-cloak>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">
                <i class="fas fa-clock me-2" style="color: var(--accent);"></i>
                {{ trans('widgets.last_topics') }}
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
                        <li class="text-muted text-center py-2">{{ trans('widgets.no_topics') }}</li>
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
                        {{ trans('widgets.all_topics') }} <i class="fas fa-arrow-right ms-1"></i>
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
                {{ trans('widgets.last_bans') }}
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
                        <div class="text-muted small text-center py-2">{{ trans('widgets.no_bans') }}</div>
                    </template>
                    <ul class="list-unstyled small">
                        <template x-for="ban in bans" :key="ban.bid">
                            <li class="mb-2">
                                <div class="fw-medium" x-text="ban.player_nick"></div>
                                <div class="text-muted small" x-text="ban.ban_reason || ban.cs_ban_reason || '{{ trans('bans.no_reason') }}'"></div>
                                <div class="text-muted small d-flex justify-content-between">
                                    <span x-text="'{{ trans('bans.admin') }}: ' + ban.admin_nick"></span>
                                    <span x-text="new Date(ban.ban_created * 1000).toLocaleDateString('ru-RU')"></span>
                                </div>
                            </li>
                        </template>
                    </ul>
                    <div class="text-end mt-2">
                        <a href="{{ url('/bans') }}" class="link-accent small">
                            {{ trans('widgets.all_bans') }} <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {% endif %}

</div>