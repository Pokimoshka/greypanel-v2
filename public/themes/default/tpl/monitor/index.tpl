{% extends "base.tpl" %}

{% block title %}Мониторинг серверов{% endblock %}

{% block content %}
<h1>Мониторинг серверов</h1>

<div x-data="monitorWidget">
    <div x-show="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Загрузка...</span>
        </div>
    </div>
    <table class="table table-striped" x-show="!loading">
        <thead>
            <tr><th>Статус</th><th>Адрес</th><th>Название</th><th>Карта</th><th>Игроки</th></tr>
        </thead>
        <tbody>
            <template x-for="s in servers" :key="s.id">
                <tr>
                    <td x-html="s.status_html"></td>
                    <td><a :href="'steam://connect/' + s.address" x-text="s.address"></a></td>
                    <td x-text="s.server_name"></td>
                    <td x-text="s.map"></td>
                    <td x-text="s.players"></td>
                </tr>
            </template>
            <tr x-show="servers.length === 0">
                <td colspan="5" class="text-center">Нет серверов</td>
            </tr>
        </tbody>
    </table>
</div>
{% endblock %}