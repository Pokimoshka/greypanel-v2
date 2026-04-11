{% extends "base.tpl" %}

{% block title %}Привилегии на серверах{% endblock %}

{% block content %}
<h1>Выберите сервер</h1>
<div class="row">
    {% for server in servers %}
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ server.server_name }}</h5>
                <p class="card-text">{{ server.server_ip }}:{{ server.server_port }}</p>
                <a href="/vip/{{ server.id }}" class="btn btn-primary">Выбрать</a>
            </div>
        </div>
    </div>
    {% else %}
    <div class="col-12">
        <div class="alert alert-info">Нет доступных серверов.</div>
    </div>
    {% endfor %}
</div>
{% endblock %}