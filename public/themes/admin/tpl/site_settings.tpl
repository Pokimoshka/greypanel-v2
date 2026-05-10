{% extends "base.tpl" %}

{% block title %}{{ trans('admin.settings') }}{% endblock %}
{% block page_title %}{{ trans('admin.settings') }}{% endblock %}

{% block content %}
<form method="post" action="{{ url('admin/site-settings/save') }}">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-globe me-2"></i>{{ trans('admin.main_settings') }}
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">{{ trans('admin.site_name_field') }}</label>
                        <input type="text" name="site_name" class="form-control" value="{{ site_name }}" required>
                        <div class="form-text">{{ trans('admin.site_name_hint') }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ trans('admin.protocol') }}</label>
                        <select name="site_protocol" class="form-select">
                            <option value="auto" {{ protocol == 'auto' ? 'selected' }}>{{ trans('admin.protocol_auto') }}</option>
                            <option value="http" {{ protocol == 'http' ? 'selected' }}>HTTP</option>
                            <option value="https" {{ protocol == 'https' ? 'selected' }}>HTTPS</option>
                        </select>
                        <div class="form-text">{{ trans('admin.protocol_hint') }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ trans('admin.manual_url') }}</label>
                        <input type="text" name="site_url_manual" class="form-control" value="{{ manual_url }}" placeholder="https://example.com">
                        <div class="form-text">{{ trans('admin.manual_url_hint') }}</div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-1"></i> {{ trans('admin.current_url_display') }}: <strong>{{ current_url }}</strong>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="app_debug" class="form-check-input" id="appDebug" value="1" {{ app_debug ? 'checked' }}>
                        <label class="form-check-label" for="appDebug">{{ trans('admin.debug_mode') }}</label>
                        <div class="form-text text-danger">{{ trans('admin.debug_mode_hint') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-shield-alt me-2"></i>{{ trans('admin.extended_settings') }}
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">{{ trans('admin.session_lifetime') }}</label>
                        <input type="number" name="session_lifetime" class="form-control" value="{{ session_lifetime }}" min="300" step="60">
                        <div class="form-text">{{ trans('admin.session_lifetime_hint') }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ trans('admin.session_name') }}</label>
                        <input type="text" name="session_name" class="form-control" value="{{ session_name }}" pattern="[A-Za-z0-9_]+" required>
                        <div class="form-text">{{ trans('admin.session_name_hint') }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ trans('admin.default_language') }}</label>
                        <select name="default_language" class="form-select">
                            {% for code, name in available_languages %}
                                <option value="{{ code }}" {{ code == default_language ? 'selected' }}>{{ name }}</option>
                            {% endfor %}
                        </select>
                        <div class="form-text">{{ trans('admin.default_language_hint') }}</div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-1"></i>{{ trans('admin.save_settings') }}
    </button>
</form>
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}