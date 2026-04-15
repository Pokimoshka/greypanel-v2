{% extends "base.tpl" %}

{% block title %}Модули{% endblock %}
{% block page_title %}Управление модулями{% endblock %}

{% block content %}
<div class="card">
    <div class="card-body">
        <table class="table">
            <thead><tr><th>Модуль</th><th>Состояние</th></tr></thead>
            <tbody>
                {% for module in modules %}
                <tr>
                    <td>
                        <strong>{{ module.name|capitalize }}</strong>
                        <div class="small text-secondary">
                            {% if module.name == 'chat' %}Чат{% elseif module.name == 'monitor' %}Мониторинг{% elseif module.name == 'bans' %}Бан-лист{% elseif module.name == 'warcraft' %}Warcraft{% endif %}
                        </div>
                    </td>
                    <td>
                        <div class="form-check form-switch">
                            <input class="form-check-input module-toggle" type="checkbox" data-module="{{ module.name }}" {{ module.enabled ? 'checked' }}>
                        </div>
                    </td>
                </tr>
                {% endfor %}
            </tbody>
        </table>
    </div>
</div>
{% endblock %}

{% block scripts %}
{{ parent() }}
<script>
document.querySelectorAll('.module-toggle').forEach(t => {
    t.addEventListener('change', async function() {
        const formData = new URLSearchParams();
        formData.append('module', this.dataset.module);
        formData.append('enabled', this.checked ? '1' : '0');
        formData.append('csrf_token', '{{ csrf_token }}');
        await fetch('/admin/modules/toggle', { method: 'POST', body: formData });
    });
});
</script>
{% endblock %}