{% extends 'base.tpl' %}

{% block title %}{{ trans('nav.home') }} — {{ site_name }}{% endblock %}

{% block content %}
    <div class="widget-card p-4 mb-4">
        <h4 class="mb-3" style="color: var(--accent);">
            <i class="fas fa-server me-2"></i>{{ trans('site.welcome', {'%site_name%': site_name}) }}!
        </h4>
        <p>{{ trans('site.description') }}</p>
        {% if not app.user %}
            <div class="mt-3">
                <a href="{{ url('/login') }}" class="btn btn-primary me-2">{{ trans('auth.login') }}</a>
                <a href="{{ url('/register') }}" class="btn btn-outline-secondary">{{ trans('auth.register') }}</a>
            </div>
        {% endif %}
    </div>

    {% if module_enabled('chat') %}
    <div class="widget-card p-3 mb-4" x-data="chatWidget" x-init="init">
        <h5 class="d-flex align-items-center mb-3">
            <i class="fas fa-comment-dots me-2" style="color: var(--accent);"></i>
            {{ trans('home.chat.title') }}
        </h5>
        <div class="chat-messages" style="max-height: 350px; overflow-y: auto;" x-ref="messagesContainer">
            <template x-for="msg in messages" :key="msg.id">
                <div class="d-flex mb-3">
                    <img :src="msg.avatar" style="width:36px;height:36px;object-fit:cover;" class="rounded-circle me-2">
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong x-text="msg.username" style="color: var(--accent-bright);"></strong>
                            <span x-text="new Date(msg.time * 1000).toLocaleTimeString()"></span>
                        </div>
                        <div x-html="msg.text" class="small"></div>
                    </div>
                </div>
            </template>
            <div x-show="messages.length === 0" class="text-center text-muted py-3">
                {{ trans('home.chat.empty') }}
            </div>
        </div>
        {% if app.user %}
        <form @submit.prevent="sendMessage">
            <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="{{ trans('home.chat.placeholder') }}" x-model="newMessage" @keyup.enter="sendMessage" :disabled="sending">
                <button class="btn btn-primary" type="submit" :disabled="sending">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </form>
        {% else %}
        <div class="alert alert-light mt-3 mb-0 text-center">
            <a href="{{ url('/login') }}">{{ trans('home.chat.login_required') }}</a>
        </div>
        {% endif %}
    </div>
    {% endif %}

    {% if module_enabled('monitor') %}
    <div class="widget-card p-3" x-data="monitorWidget" x-init="init">
        <h5 class="d-flex align-items-center mb-3">
            <i class="fas fa-chart-line me-2" style="color: var(--accent);"></i>
            {{ trans('home.monitor.title') }}
        </h5>
        <div x-show="loading" class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            <span class="ms-2">{{ trans('monitor.loading') }}</span>
        </div>
        <div x-show="!loading">
            <template x-if="servers.length === 0">
                <div class="text-center text-muted py-3">{{ trans('home.monitor.offline') }}</div>
            </template>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr class="small">
                            <th>{{ trans('monitor.status') }}</th>
                            <th>{{ trans('monitor.server') }}</th>
                            <th>{{ trans('monitor.players') }}</th>
                            <th>{{ trans('monitor.map') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="srv in servers" :key="srv.id">
                            <tr>
                                <td>
                                    <span class="badge" :class="srv.online ? 'bg-success' : 'bg-danger'"
                                        x-text="srv.online ? 'ON' : 'OFF'"></span>
                                </td>
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
            <a href="{{ url('/monitor') }}" class="link-accent small">
                {{ trans('home.monitor.details') }} <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    {% endif %}
{% endblock %}