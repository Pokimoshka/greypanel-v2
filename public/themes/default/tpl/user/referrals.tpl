{% extends "base.tpl" %}

{% block title %}Реферальная программа{% endblock %}

{% block content %}
<h1>Реферальная программа</h1>
<p>Приглашайте друзей и получайте 10% от их пополнений.</p>
<div class="alert alert-info">
    <strong>Ваша реферальная ссылка:</strong><br>
    <code>{{ ref_link }}</code>
    <button class="btn btn-sm btn-secondary" onclick="navigator.clipboard.writeText('{{ ref_link }}')">Копировать</button>
</div>
<p>Заработано реферальных средств: <strong>{{ earnings }} ₽</strong></p>

<h3>Приглашённые пользователи ({{ referrals|length }})</h3>
<table class="table table-striped">
    <thead><tr><th>Ник</th><th>Email</th><th>Дата регистрации</th></tr></thead>
    <tbody>
    {% for ref in referrals %}
        <tr>
            <td>{{ ref.username }}</td>
            <td>{{ ref.email }}</td>
            <td>{{ ref.reg_data|date('d.m.Y H:i') }}</td>
        </tr>
    {% else %}
        <tr><td colspan="3" class="text-center">Нет приглашённых пользователей.</td></tr>
    {% endfor %}
    </tbody>
</table>
{% endblock %}