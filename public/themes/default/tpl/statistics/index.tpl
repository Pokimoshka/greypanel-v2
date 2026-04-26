{% extends 'base.tpl' %}
{% block title %}Рейтинг игроков — {{ site_name }}{% endblock %}

{% block content %}
<h2 class="mb-4" style="color: var(--accent-bright);">
    <i class="fas fa-trophy me-2"></i>Рейтинг игроков
</h2>

<div class="widget-card p-3 mb-4">
    <form method="get" class="row g-2">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Поиск по нику или Steam ID" value="{{ search }}">
        </div>
        <div class="col-md-3">
            <select name="sort" class="form-select">
                {% for key, name in sort_types %}
                    <option value="{{ key }}" {{ sort == key ? 'selected' }}>{{ name }}</option>
                {% endfor %}
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary w-100">Применить</button>
        </div>
    </form>
</div>

<div class="widget-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="small text-secondary">
                <tr>
                    <th>#</th>
                    <th>Игрок</th>
                    <th>Steam ID</th>
                    <th>Убийств</th>
                    <th>Смертей</th>
                    <th>K/D</th>
                    <th>HS%</th>
                    <th>Время</th>
                </tr>
            </thead>
            <tbody>
                {% for player in players %}
                <tr>
                    <td>{{ player.rank }}</td>
                    <td>
                        <a href="{{ url('/stats/player/' ~ player.id) }}" style="color: var(--accent);">
                            {{ player.nick }}
                        </a>
                    </td>
                    <td class="small text-muted">{{ player.steamId }}</td>
                    <td>{{ player.frags }}</td>
                    <td>{{ player.deaths }}</td>
                    <td>{{ player.getKdRatio() }}</td>
                    <td>{{ player.getHsPercent() }}%</td>
                    <td>{{ player.getGameTimeFormatted() }}</td>
                </tr>
                {% else %}
                <tr><td colspan="8" class="text-center py-4 text-muted">Нет данных</td></tr>
                {% endfor %}
            </tbody>
        </table>
    </div>
</div>

{% include 'partials/pagination.tpl' with {
    'current': page,
    'total': total,
    'per_page': per_page,
    'url': '/stats',
    'params': {'sort': sort, 'search': search}
} %}
{% endblock %}