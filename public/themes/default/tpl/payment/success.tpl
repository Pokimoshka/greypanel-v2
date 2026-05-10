{% extends "base.tpl" %}

{% block title %}{{ trans('payment.success') }}{% endblock %}

{% block content %}
<div class="alert alert-success">
    <h3>{{ trans('payment.success') }}!</h3>
    <p>{{ trans('payment.success_text') }}</p>
    <a href="/balance" class="btn btn-primary">{{ trans('balance.go_to_balance') }}</a>
</div>
{% endblock %}