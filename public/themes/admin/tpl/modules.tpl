{% extends "base.tpl" %}

{% block title %}Управление модулями{% endblock %}

{% block content %}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Управление модулями</h1>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Модуль</th>
                    <th style="width: 100px;">Состояние</th>
                </tr>
            </thead>
            <tbody>
                {% for module in modules %}
                <tr>
                    <td>
                        <strong>{{ module.name|capitalize }}</strong>
                        <div class="small text-muted">
                            {% if module.name == 'chat' %}Общий чат на сайте{% endif %}
                            {% if module.name == 'monitor' %}Мониторинг серверов{% endif %}
                            {% if module.name == 'bans' %}Бан-лист сервера{% endif %}
                            {% if module.name == 'warcraft' %}Покупка опыта Warcraft{% endif %}
                        </div>
                    </td>
                    <td>
                        <div class="form-check form-switch">
                            <input class="form-check-input module-toggle" type="checkbox" 
                                   data-module="{{ module.name }}" 
                                   {{ module.enabled ? 'checked' : '' }}>
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
<script>
document.querySelectorAll('.module-toggle').forEach(toggle => {
    toggle.addEventListener('change', async function() {
        const module = this.dataset.module;
        const enabled = this.checked;
        
        const formData = new URLSearchParams();
        formData.append('module', module);
        formData.append('enabled', enabled ? '1' : '0');
        formData.append('csrf_token', '{{ csrf_token }}');
        
        try {
            const response = await fetch('/admin/modules/toggle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            });
            const data = await response.json();
            if (!data.success) {
                alert('Ошибка сохранения');
                this.checked = !enabled;
            }
        } catch (e) {
            alert('Ошибка соединения');
            this.checked = !enabled;
        }
    });
});
</script>
{% endblock %}