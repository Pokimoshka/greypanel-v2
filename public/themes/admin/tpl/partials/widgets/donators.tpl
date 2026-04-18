<div class="widget">
    <div class="widget-header">
        <i class="fas fa-trophy me-2"></i> Топ донатеров
    </div>
    <div class="widget-body">
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
            <li class="list-group-item text-muted">Нет данных</li>
            {% endfor %}
        </ul>
    </div>
</div>