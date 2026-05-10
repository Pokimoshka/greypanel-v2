{% extends "base.tpl" %}

{% block title %}{{ trans('admin.dashboard') }}{% endblock %}
{% block page_title %}{{ trans('admin.dashboard') }}{% endblock %}

{% block content %}
<div class="row g-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary h-100 animate__animated animate__fadeInUp">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase small opacity-75">{{ trans('admin.users') }}</h6>
                        <h2 class="mb-0">{{ total_users }}</h2>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
                <small class="opacity-75">{{ trans('admin.all_time') }}</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-white bg-success h-100 animate__animated animate__fadeInUp animate__delay-1s">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase small opacity-75">{{ trans('admin.forum_threads') }}</h6>
                        <h2 class="mb-0">{{ total_threads }}</h2>
                    </div>
                    <i class="fas fa-comments fa-3x opacity-50"></i>
                </div>
                <small class="opacity-75">{{ trans('admin.total_threads') }}</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-white bg-info h-100 animate__animated animate__fadeInUp animate__delay-3s">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase small opacity-75">{{ trans('admin.online') }}</h6>
                        <h2 class="mb-0">{{ online_count|default(0) }}</h2>
                    </div>
                    <i class="fas fa-eye fa-3x opacity-50"></i>
                </div>
                <small class="opacity-75">{{ trans('admin.now_online') }}</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-line me-2"></i>{{ trans('admin.registrations_7_days') }}
            </div>
            <div class="card-body">
                <canvas id="registrationsChart" height="200"></canvas>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between">
                <span><i class="fas fa-history me-2"></i>{{ trans('admin.recent_activity') }}</span>
                <a href="{{ url('admin/logs') }}" class="btn btn-sm btn-outline-primary">{{ trans('admin.all_logs') }} →</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>{{ trans('admin.user') }}</th><th>{{ trans('admin.action') }}</th><th>IP</th><th>{{ trans('admin.date') }}</th></tr>
                        </thead>
                        <tbody>
                        {% for log in recent_logs %}
                            <tr>
                                <td>{{ log.username ?? trans('admin.guest') }}</td>
                                <td>{{ log.action }}</td>
                                <td>{{ log.ip }}</td>
                                <td>{{ log.created_at|format_datetime('medium', 'short', locale=locale) }}</td>
                            </tr>
                        {% else %}
                            <tr><td colspan="4" class="text-center text-muted py-3">{{ trans('admin.no_records') }}</td></tr>
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
                <i class="fas fa-user-plus me-2"></i>{{ trans('admin.recent_users') }}
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                {% for user in recent_users %}
                    <li class="list-group-item bg-transparent d-flex justify-content-between">
                        <div>
                            <img src="{{ user.avatar }}" width="30" height="30" class="rounded-circle me-2">
                            <a href="{{ url('admin/users/edit/' ~ user.id) }}">{{ user.username }}</a>
                        </div>
                        <small class="text-secondary">{{ user.regData|format_date('medium', locale=locale) }}</small>
                    </li>
                {% else %}
                    <li class="list-group-item text-center text-muted">{{ trans('admin.no_users') }}</li>
                {% endfor %}
                </ul>
            </div>
            <div class="card-footer text-center">
                <a href="{{ url('admin/users') }}" class="btn btn-sm btn-outline-primary">{{ trans('admin.all_users') }} →</a>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <i class="fas fa-bolt me-2"></i>{{ trans('admin.quick_actions') }}
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ url('admin/forum/categories') }}" class="btn btn-outline-success"><i class="fas fa-comments me-2"></i>{{ trans('admin.forum') }}</a>
                    <a href="{{ url('admin/server-settings') }}" class="btn btn-outline-info"><i class="fas fa-server me-2"></i>{{ trans('admin.monitor') }}</a>
                    <a href="{{ url('admin/services') }}" class="btn btn-outline-warning"><i class="fas fa-cogs me-2"></i>{{ trans('admin.services') }}</a>
                    <a href="{{ url('admin/themes') }}" class="btn btn-outline-secondary"><i class="fas fa-palette me-2"></i>{{ trans('admin.themes') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}

{% block scripts %}
{{ parent() }}
{% endblock %}