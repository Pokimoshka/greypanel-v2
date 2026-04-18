<div class="widget">
    <div class="widget-header">
        <i class="fas fa-gavel me-2"></i> Последние баны
    </div>
    <div class="widget-body">
        <ul class="list-group list-group-flush">
            {% for ban in last_bans %}
            <li class="list-group-item">
                <div class="fw-bold">{{ ban.player_nick }}</div>
                <div class="small text-muted">Причина: {{ ban.ban_reason ?: ban.cs_ban_reason }}</div>
                <div class="small text-muted">Админ: {{ ban.admin_nick }} · {{ ban.ban_created|date('d.m.Y') }}</div>
            </li>
            {% else %}
            <li class="list-group-item text-muted">Нет банов</li>
            {% endfor %}
        </ul>
    </div>
    <div class="widget-footer text-center p-2">
        <a href="/bans" class="small text-primary">Все баны →</a>
    </div>
</div>