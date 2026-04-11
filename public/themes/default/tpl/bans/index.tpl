{% extends "base.tpl" %}

{% block title %}Бан-лист{% endblock %}

{% block content %}
<h1>Бан-лист</h1>

<form method="get" class="mb-3">
    <div class="input-group">
        <input type="text" name="search" class="form-control" placeholder="Поиск по нику, IP или причине" value="{{ search }}">
        <button class="btn btn-primary" type="submit">Искать</button>
    </div>
</form>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Ник</th>
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
            <td>{{ ban.player_nick }}</td>
            <td>{{ ban.admin_nick }}</td>
            <td>{{ ban.ban_reason ?: ban.cs_ban_reason }}</td>
            <td>{{ ban.ban_created|date('d.m.Y H:i') }}</td>
            <td>{{ ban.server_name }}</td>
            <td>
                <button class="btn btn-sm btn-info unban-request" data-id="{{ ban.bid }}">Заявка</button>
                {% if buy_razban > 0 %}
                <button class="btn btn-sm btn-warning paid-unban" data-id="{{ ban.bid }}">Разбан за {{ buy_razban }} ₽</button>
                {% endif %}
            </td>
        </tr>
        {% endfor %}
    </tbody>
</table>
{% include 'partials/pagination.tpl' %}
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