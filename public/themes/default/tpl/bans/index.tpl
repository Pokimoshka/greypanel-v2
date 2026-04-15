{% extends 'base.tpl' %}

{% block title %}Бан-лист — {{ site_name }}{% endblock %}

{% block content %}
    <h2 class="mb-4" style="color: var(--accent-bright);">
        <i class="fas fa-gavel me-2"></i>Бан-лист
    </h2>

    <div class="widget-card p-3 mb-4">
        <form method="get" class="row g-2">
            <div class="col-md-8">
                <input type="text" name="search" class="form-control" placeholder="Поиск по нику, IP или причине" value="{{ search }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i>Найти
                </button>
            </div>
        </form>
    </div>

    <div class="widget-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="small text-secondary">
                    <tr>
                        <th>Игрок</th>
                        <th>Админ</th>
                        <th>Причина</th>
                        <th>Дата</th>
                        <th>Сервер</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    {% for ban in bans %}
                        <tr>
                            <td class="fw-medium">{{ ban.player_nick }}</td>
                            <td>{{ ban.admin_nick }}</td>
                            <td>{{ ban.ban_reason ?: ban.cs_ban_reason }}</td>
                            <td>{{ ban.ban_created|date('d.m.Y H:i') }}</td>
                            <td>{{ ban.server_name }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-info unban-request" data-id="{{ ban.bid }}" title="Подать заявку на разбан">
                                    <i class="fas fa-ticket-alt"></i>
                                </button>
                                {% if settings.buy_razban > 0 %}
                                    <button class="btn btn-sm btn-outline-warning paid-unban" data-id="{{ ban.bid }}" title="Купить разбан за {{ settings.buy_razban }} ₽">
                                        <i class="fas fa-coins"></i>
                                    </button>
                                {% endif %}
                            </td>
                        </tr>
                    {% else %}
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-ban fa-2x mb-2" style="color: var(--accent);"></i>
                                <p>Банов не найдено</p>
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
        'params': {'search': search}
    } %}
{% endblock %}

{% block scripts %}
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.unban-request').forEach(btn => {
        btn.addEventListener('click', async function() {
            const banId = this.dataset.id;
            const demoUrl = prompt('Введите ссылку на демо (если есть):', '');
            
            const formData = new URLSearchParams();
            formData.append('ban_id', banId);
            if (demoUrl) formData.append('demo_url', demoUrl);
            formData.append('csrf_token', '{{ csrf_token }}');
            
            try {
                const response = await fetch('/bans/request', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    alert('Заявка создана! Перейти к теме?');
                    if (confirm('Открыть тему?')) {
                        window.location.href = '/forum/thread/' + data.thread_id;
                    }
                } else {
                    alert('Ошибка: ' + (data.error || 'Неизвестная ошибка'));
                }
            } catch (err) {
                alert('Ошибка соединения');
            }
        });
    });

    document.querySelectorAll('.paid-unban').forEach(btn => {
        btn.addEventListener('click', async function() {
            const banId = this.dataset.id;
            if (!confirm('Списать {{ buy_razban }} ₽ за снятие бана?')) return;
            
            const formData = new URLSearchParams();
            formData.append('ban_id', banId);
            formData.append('csrf_token', '{{ csrf_token }}');
            
            try {
                const response = await fetch('/bans/paid', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    alert('Бан успешно снят! Страница будет обновлена.');
                    location.reload();
                } else {
                    alert('Ошибка: ' + (data.error || 'Недостаточно средств или бан не найден'));
                }
            } catch (err) {
                alert('Ошибка соединения');
            }
        });
    });
});
</script>
{% endblock %}