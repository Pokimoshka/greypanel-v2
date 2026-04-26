{% extends "base.tpl" %}

{% block title %}Настройки сайта{% endblock %}
{% block page_title %}Настройки сайта{% endblock %}

{% block content %}
<form method="post" action="{{ url('admin/site-settings/save') }}">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">

    <div class="row">
        {# Левая колонка: Основные настройки #}
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-globe me-2"></i>Основные настройки
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Название сайта</label>
                        <input type="text" name="site_name" class="form-control" value="{{ site_name }}" required>
                        <div class="form-text">Отображается в заголовке страниц, мета-тегах, подвале.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Протокол сайта</label>
                        <select name="site_protocol" class="form-select">
                            <option value="auto" {{ protocol == 'auto' ? 'selected' }}>Авто (определяется по запросу)</option>
                            <option value="http" {{ protocol == 'http' ? 'selected' }}>HTTP</option>
                            <option value="https" {{ protocol == 'https' ? 'selected' }}>HTTPS</option>
                        </select>
                        <div class="form-text">Если выбран режим «Авто», будет использоваться протокол текущего запроса.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Полный URL сайта (опционально)</label>
                        <input type="text" name="site_url_manual" class="form-control" value="{{ manual_url }}" placeholder="https://example.com">
                        <div class="form-text">Если указано, этот URL будет использоваться вместо автоматически определённого.</div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-1"></i> Текущий URL: <strong>{{ current_url }}</strong>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="app_debug" class="form-check-input" id="appDebug" value="1" {{ app_debug ? 'checked' }}>
                        <label class="form-check-label" for="appDebug">Режим отладки (показывать ошибки)</label>
                        <div class="form-text text-danger">Включайте только при поиске проблем!</div>
                    </div>
                </div>
            </div>
        </div>

        {# Правая колонка: Расширенные настройки #}
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-shield-alt me-2"></i>Расширенные настройки
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Время жизни сессии (секунд)</label>
                        <input type="number" name="session_lifetime" class="form-control" value="{{ session_lifetime }}" min="300" step="60">
                        <div class="form-text">По умолчанию 7200 (2 часа). После изменения нужно выйти и зайти заново.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Имя cookie сессии</label>
                        <input type="text" name="session_name" class="form-control" value="{{ session_name }}" pattern="[A-Za-z0-9_]+" required>
                        <div class="form-text">Только латинские буквы, цифры, знак подчёркивания.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-1"></i>Сохранить все настройки
    </button>
</form>
{% endblock %}