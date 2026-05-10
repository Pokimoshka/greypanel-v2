{% extends 'base.tpl' %}

{% block title %}{{ trans('errors.429') }}{% endblock %}

{% block content %}
<div class="widget-card p-4 text-center">
    <i class="fas fa-exclamation-triangle fa-3x mb-3" style="color: var(--accent);"></i>
    <h3>{{ trans('errors.429') }}</h3>
    <p>{{ message }}</p>
    <a href="javascript:history.back()" class="btn btn-primary">{{ trans('errors.back') }}</a>
</div>
{% endblock %}