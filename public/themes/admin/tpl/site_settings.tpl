{% extends "base.tpl" %}

{% block title %}Настройки сайта{% endblock %}
{% block page_title %}Настройки сайта{% endblock %}

{% block content %}
<div class="card">
    <div class="card-header">
        <i class="fas fa-globe me-2"></i>Основные настройки
    </div>
    <div class="card-body">
        <form method="post" action="{{ url('admin/site-settings/save') }}">
            <input type="hidden" name="csrf_token" value="{{ csrf_token }}">

            <div class="mb-3">
                <label class="form-label">Протокол сайта</label>
                <select name="site_protocol" class="form-select">
                    <option value="auto" {{ protocol == 'auto' ? 'selected' }}>Автоопределение (по запросу)</option>
                    <option value="http" {{ protocol == 'http' ? 'selected' }}>HTTP</option>
                    <option value="https" {{ protocol == 'https' ? 'selected' }}>HTTPS</option>
                </select>
                <div class="form-text">Если выбран режим «Авто», будет использоваться протокол текущего запроса.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Полный URL сайта (опционально)</label>
                <input type="text" name="site_url_manual" class="form-control" value="{{ manual_url }}" placeholder="https://example.com">
                <div class="form-text">Если указано, этот URL будет использоваться вместо автоматически определённого (без учёта протокола).</div>
            </div>

            <div class="mb-3 alert alert-info">
                <strong>Текущий URL:</strong> {{ current_url }}
            </div>

            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
    </div>
</div>
{% endblock %}