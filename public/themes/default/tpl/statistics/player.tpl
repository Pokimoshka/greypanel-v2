{% extends 'base.tpl' %}
{% block title %}{{ player.nick }} — {{ trans('stats.title') }}{% endblock %}

{% block content %}
<a href="{{ url('/stats') }}" class="link-accent small mb-3 d-inline-block">
    <i class="fas fa-arrow-left me-1"></i>{{ trans('stats.back') }}
</a>

<div class="widget-card p-4">
    <h2 class="mb-4" style="color: var(--accent-bright);">
        <i class="fas fa-user me-2"></i>{{ player.nick }}
    </h2>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="bg-dark p-3 rounded-3">
                <h5>{{ trans('stats.main') }}</h5>
                <dl class="row mb-0">
                    <dt class="col-sm-6">{{ trans('stats.steam_id') }}</dt>
                    <dd class="col-sm-6">{{ player.steamId }}</dd>
                    <dt class="col-sm-6">{{ trans('stats.rank') }}</dt>
                    <dd class="col-sm-6">#{{ player.rank }}</dd>
                    <dt class="col-sm-6">{{ trans('stats.kills') }}</dt>
                    <dd class="col-sm-6">{{ player.frags }}</dd>
                    <dt class="col-sm-6">{{ trans('stats.deaths') }}</dt>
                    <dd class="col-sm-6">{{ player.deaths }}</dd>
                    <dt class="col-sm-6">K/D</dt>
                    <dd class="col-sm-6">{{ player.getKdRatio() }}</dd>
                    <dt class="col-sm-6">HS%</dt>
                    <dd class="col-sm-6">{{ player.getHsPercent() }}%</dd>
                    <dt class="col-sm-6">{{ trans('stats.skill') }}</dt>
                    <dd class="col-sm-6">{{ player.skill }}</dd>
                </dl>
            </div>
        </div>
        <div class="col-md-6">
            <div class="bg-dark p-3 rounded-3">
                <h5>{{ trans('stats.additional') }}</h5>
                <dl class="row mb-0">
                    <dt class="col-sm-6">{{ trans('stats.game_time') }}</dt>
                    <dd class="col-sm-6">{{ player.getGameTimeFormatted() }}</dd>
                    <dt class="col-sm-6">{{ trans('stats.last_seen') }}</dt>
                    <dd class="col-sm-6">{{ player.lastSeen|format_datetime('medium', 'short', locale=locale) }}</dd>
                    <dt class="col-sm-6">{{ trans('stats.shots') }}</dt>
                    <dd class="col-sm-6">{{ player.shots }}</dd>
                    <dt class="col-sm-6">{{ trans('stats.hits') }}</dt>
                    <dd class="col-sm-6">{{ player.hits }}</dd>
                    <dt class="col-sm-6">{{ trans('stats.defuses') }}</dt>
                    <dd class="col-sm-6">{{ player.defused }}</dd>
                    <dt class="col-sm-6">{{ trans('stats.plants') }}</dt>
                    <dd class="col-sm-6">{{ player.planted }}</dd>
                    <dt class="col-sm-6">{{ trans('stats.explosions') }}</dt>
                    <dd class="col-sm-6">{{ player.explode }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
{% endblock %}