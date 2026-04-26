{% extends 'base.tpl' %}
{% block title %}{{ player.nick }} — Статистика{% endblock %}

{% block content %}
<a href="{{ url('/stats') }}" class="link-accent small mb-3 d-inline-block">
    <i class="fas fa-arrow-left me-1"></i>К рейтингу
</a>

<div class="widget-card p-4">
    <h2 class="mb-4" style="color: var(--accent-bright);">
        <i class="fas fa-user me-2"></i>{{ player.nick }}
    </h2>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="bg-dark p-3 rounded-3">
                <h5>Основное</h5>
                <dl class="row mb-0">
                    <dt class="col-sm-6">Steam ID</dt>
                    <dd class="col-sm-6">{{ player.steamId }}</dd>
                    <dt class="col-sm-6">Позиция в рейтинге</dt>
                    <dd class="col-sm-6">#{{ player.rank }}</dd>
                    <dt class="col-sm-6">Убийств</dt>
                    <dd class="col-sm-6">{{ player.frags }}</dd>
                    <dt class="col-sm-6">Смертей</dt>
                    <dd class="col-sm-6">{{ player.deaths }}</dd>
                    <dt class="col-sm-6">K/D</dt>
                    <dd class="col-sm-6">{{ player.getKdRatio() }}</dd>
                    <dt class="col-sm-6">HS%</dt>
                    <dd class="col-sm-6">{{ player.getHsPercent() }}%</dd>
                    <dt class="col-sm-6">Скилл</dt>
                    <dd class="col-sm-6">{{ player.skill }}</dd>
                </dl>
            </div>
        </div>
        <div class="col-md-6">
            <div class="bg-dark p-3 rounded-3">
                <h5>Дополнительно</h5>
                <dl class="row mb-0">
                    <dt class="col-sm-6">Время игры</dt>
                    <dd class="col-sm-6">{{ player.getGameTimeFormatted() }}</dd>
                    <dt class="col-sm-6">Последний вход</dt>
                    <dd class="col-sm-6">{{ player.getLastSeenFormatted() }}</dd>
                    <dt class="col-sm-6">Выстрелов</dt>
                    <dd class="col-sm-6">{{ player.shots }}</dd>
                    <dt class="col-sm-6">Попаданий</dt>
                    <dd class="col-sm-6">{{ player.hits }}</dd>
                    <dt class="col-sm-6">Разминирований</dt>
                    <dd class="col-sm-6">{{ player.defused }}</dd>
                    <dt class="col-sm-6">Установок бомбы</dt>
                    <dd class="col-sm-6">{{ player.planted }}</dd>
                    <dt class="col-sm-6">Подрывов</dt>
                    <dd class="col-sm-6">{{ player.explode }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
{% endblock %}