{% extends "base.tpl" %}

{% block title %}SEO настройки{% endblock %}

{% block content %}
<h1>SEO настройки</h1>

<form method="post">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <div class="card mb-3">
        <div class="card-header">Мета-теги по умолчанию</div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Description (описание сайта)</label>
                <textarea name="seo_default_description" class="form-control" rows="3">{{ seo_default_description }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Keywords (ключевые слова через запятую)</label>
                <input type="text" name="seo_keywords" class="form-control" value="{{ seo_keywords }}">
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Sitemap.xml</div>
        <div class="card-body">
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="seo_sitemap_enabled" value="1" id="sitemapEnabled" {{ seo_sitemap_enabled ? 'checked' : '' }}>
                <label class="form-check-label" for="sitemapEnabled">Включить автоматическую генерацию sitemap.xml</label>
            </div>
            <button type="button" id="regenerateBtn" class="btn btn-secondary">Сгенерировать сейчас</button>
            <span id="regenerateResult" class="ms-2"></span>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">robots.txt</div>
        <div class="card-body">
            <textarea name="robots_txt" class="form-control font-monospace" rows="8">{{ robots_txt }}</textarea>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Сохранить настройки</button>
</form>
{% endblock %}

{% block scripts %}
<script>
document.getElementById('regenerateBtn')?.addEventListener('click', async function() {
    const resultSpan = document.getElementById('regenerateResult');
    resultSpan.textContent = 'Генерация...';
    try {
        const formData = new URLSearchParams();
        formData.append('csrf_token', '{{ csrf_token }}');
        const resp = await fetch('/admin/seo/regenerate', {
            method: 'POST',
            body: formData
        });
        if (resp.ok) {
            resultSpan.textContent = '✓ Sitemap обновлён';
        } else {
            resultSpan.textContent = '✗ Ошибка';
        }
    } catch (e) {
        resultSpan.textContent = '✗ Ошибка соединения';
    }
});
</script>
{% endblock %}