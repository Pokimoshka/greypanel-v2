{% extends "base.tpl" %}

{% block title %}SEO{% endblock %}
{% block page_title %}SEO настройки{% endblock %}

{% block content %}
<form method="post">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <div class="card mb-3">
        <div class="card-header">Мета-теги</div>
        <div class="card-body">
            <label class="form-label">Description</label>
            <textarea name="seo_default_description" class="form-control mb-3">{{ seo_default_description }}</textarea>
            <label class="form-label">Keywords</label>
            <input type="text" name="seo_keywords" class="form-control" value="{{ seo_keywords }}">
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header">Sitemap</div>
        <div class="card-body">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="seo_sitemap_enabled" value="1" {{ seo_sitemap_enabled ? 'checked' }}>
                <label class="form-check-label">Включить sitemap.xml</label>
            </div>
            <button type="button" id="regenerateBtn" class="btn btn-secondary">Сгенерировать сейчас</button>
            <span id="regenerateResult" class="ms-2"></span>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header">robots.txt</div>
        <div class="card-body">
            <textarea name="robots_txt" class="form-control font-monospace" rows="6">{{ robots_txt }}</textarea>
        </div>
    </div>
    <button class="btn btn-primary">Сохранить</button>
</form>
{% endblock %}

{% block scripts %}
{{ parent() }}
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

document.getElementById('regenerateBtn').addEventListener('click', async () => {
    const res = await fetch('/admin/seo/regenerate', { method: 'POST', body: new URLSearchParams({csrf_token: csrfToken}) });
    document.getElementById('regenerateResult').textContent = res.ok ? '✓ Готово' : '✗ Ошибка';
});
</script>
{% endblock %}