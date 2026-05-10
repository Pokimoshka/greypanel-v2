{% extends "base.tpl" %}

{% block title %}{{ trans('admin.seo') }}{% endblock %}
{% block page_title %}{{ trans('admin.seo_settings') }}{% endblock %}

{% block content %}
<form method="post">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <div class="card mb-3">
        <div class="card-header">{{ trans('admin.meta_tags') }}</div>
        <div class="card-body">
            <label class="form-label">{{ trans('admin.description') }}</label>
            <textarea name="seo_default_description" class="form-control mb-3">{{ seo_default_description }}</textarea>
            <label class="form-label">{{ trans('admin.keywords') }}</label>
            <input type="text" name="seo_keywords" class="form-control" value="{{ seo_keywords }}">
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header">{{ trans('admin.sitemap') }}</div>
        <div class="card-body">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="seo_sitemap_enabled" value="1" {{ seo_sitemap_enabled ? 'checked' }}>
                <label class="form-check-label">{{ trans('admin.enable_sitemap') }}</label>
            </div>
            <button type="button" id="regenerateBtn" class="btn btn-secondary">{{ trans('admin.regenerate') }}</button>
            <span id="regenerateResult" class="ms-2"></span>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header">{{ trans('admin.robots') }}</div>
        <div class="card-body">
            <textarea name="robots_txt" class="form-control font-monospace" rows="6">{{ robots_txt }}</textarea>
        </div>
    </div>
    <button class="btn btn-primary">{{ trans('admin.save') }}</button>
</form>
{% endblock %}

{% block scripts %}
{{ parent() }}
{% endblock %}