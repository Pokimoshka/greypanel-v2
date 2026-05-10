{% extends 'base.tpl' %}

{% block title %}{{ trans('bans.title') }} — {{ site_name }}{% endblock %}

{% block content %}
    <h2 class="mb-4" style="color: var(--accent-bright);">
        <i class="fas fa-gavel me-2"></i>{{ trans('bans.title') }}
    </h2>

    <div class="widget-card p-3 mb-4">
        <form method="get" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="{{ trans('bans.search_placeholder') }}" value="{{ search }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">{{ trans('bans.status_all') }}</option>
                    <option value="0" {{ status_filter == 0 ? 'selected' : '' }}>{{ trans('bans.status_active') }}</option>
                    <option value="2" {{ status_filter == 2 ? 'selected' : '' }}>{{ trans('bans.status_expired') }}</option>
                    <option value="1" {{ status_filter == 1 ? 'selected' : '' }}>{{ trans('bans.status_unbanned') }}</option>
                    <option value="3" {{ status_filter == 3 ? 'selected' : '' }}>{{ trans('bans.status_paid') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i>{{ trans('bans.apply') }}
                </button>
            </div>
        </form>
    </div>

    <div class="widget-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="small text-secondary">
                    <tr>
                        <th>{{ trans('bans.player') }}</th>
                        <th>{{ trans('bans.admin') }}</th>
                        <th>{{ trans('bans.reason') }}</th>
                        <th>{{ trans('bans.date') }}</th>
                        <th>{{ trans('bans.expires') }}</th>
                        <th>{{ trans('bans.server') }}</th>
                        <th>{{ trans('bans.status') }}</th>
                        <th>{{ trans('bans.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    {% for ban in bans %}
                        <tr class="{{ ban.status == 0 ? 'table-danger' : (ban.status == 1 ? 'table-success' : (ban.status == 3 ? 'table-warning' : '')) }}">
                            <td class="fw-medium">{{ ban.playerNick }}</td>
                            <td>{{ ban.adminNick }}</td>
                            <td>{{ ban.reason|u.truncate(50) }}</td>
                            <td>{{ ban.created|format_datetime('medium', 'short', locale=locale) }}</td>
                            <td>{{ ban.getEndDate() }}</td>
                            <td><span class="badge bg-secondary">{{ ban.serverName }}</span></td>
                            <td>
                                {% if ban.status == 0 %}
                                    <span class="badge bg-danger">{{ trans('bans.status_active') }}</span>
                                {% elseif ban.status == 1 %}
                                    <span class="badge bg-success">{{ trans('bans.status_unbanned') }}</span>
                                {% elseif ban.status == 2 %}
                                    <span class="badge bg-info">{{ trans('bans.status_expired') }}</span>
                                {% elseif ban.status == 3 %}
                                    <span class="badge bg-warning text-dark">{{ trans('bans.status_paid') }}</span>
                                {% endif %}
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-info"
                                        x-data="banActions({{ {
                                            buy_razban: settings.buy_razban,
                                            demoPrompt: trans('bans.demo_prompt'),
                                            paidConfirmPrefix: trans('bans.paid_confirm_prefix')
                                        }|json_encode }})"
                                        @click="requestUnban({{ ban.bid }})"
                                        title="{{ trans('bans.request_unban') }}">
                                    <i class="fas fa-ticket-alt"></i>
                                </button>
                                {% if settings.buy_razban > 0 and ban.status == 0 %}
                                    <button class="btn btn-sm btn-outline-warning"
                                            x-data="banActions({{ {
                                                buy_razban: settings.buy_razban,
                                                demoPrompt: trans('bans.demo_prompt'),
                                                paidConfirmPrefix: trans('bans.paid_confirm_prefix')
                                            }|json_encode }})"
                                            @click="paidUnban({{ ban.bid }})"
                                            title="{{ trans('bans.paid_unban') }} {{ settings.buy_razban }} ₽">
                                        <i class="fas fa-coins"></i>
                                    </button>
                                {% endif %}
                            </td>
                        </tr>
                    {% else %}
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-ban fa-2x mb-2" style="color: var(--accent);"></i>
                                <p>{{ trans('bans.no_bans') }}</p>
                            </td>
                        </tr>
                    {% endfor %}
                </tbody>
            </table>
        </div>
    </div>

    {% include 'partials/pagination.tpl' with {
        'current': page,
        'total': total,
        'per_page': per_page,
        'url': '/bans',
        'params': {'search': search, 'status': status_filter}
    } %}
{% endblock %}