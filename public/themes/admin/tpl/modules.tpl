{% extends "base.tpl" %}

{% block title %}{{ trans('admin.modules') }}{% endblock %}
{% block page_title %}{{ trans('admin.modules_list') }}{% endblock %}

{% block content %}
<div class="card">
    <div class="card-body">
        <table class="table">
            <thead><tr><th>{{ trans('admin.module') }}</th><th>{{ trans('admin.status') }}</th></tr></thead>
            <tbody>
                {% for module in modules %}
                <tr>
                    <td>
                        <strong>{{ module.name|capitalize }}</strong>
                        <div class="small text-secondary">
                            {% if module.name == 'chat' %}{{ trans('admin.chat') }}{% elseif module.name == 'monitor' %}{{ trans('admin.monitor') }}{% elseif module.name == 'bans' %}{{ trans('admin.bans') }}{% elseif module.name == 'warcraft' %}Warcraft{% endif %}
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
{% endblock %}