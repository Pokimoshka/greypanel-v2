{% extends 'base.tpl' %}
{% block title %}{{ trans('stats.unavailable') }} — {{ site_name }}{% endblock %}

{% block content %}
<div class="widget-card p-5 text-center text-muted">
    <i class="fas fa-chart-bar fa-4x mb-3" style="color: var(--accent);"></i>
    <h4>{{ trans('stats.unavailable') }}</h4>
    <p>{{ trans('stats.unavailable_hint') }}</p>
    <p>{{ trans('stats.contact_admin') }}</p>
</div>
{% endblock %}