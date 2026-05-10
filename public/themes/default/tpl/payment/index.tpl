{% extends "base.tpl" %}

{% block title %}{{ trans('balance.top_up') }}{% endblock %}

{% block content %}
<h1>{{ trans('balance.top_up') }}</h1>
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">{{ trans('balance.yoomoney') }}</div>
            <div class="card-body">
                <form method="post" action="/payment/yoomoney">
                    <div class="mb-3">
                        <label class="form-label">{{ trans('balance.amount') }} (₽)</label>
                        <input type="number" name="amount" class="form-control" min="1" max="50000" required>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ trans('balance.top_up') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
{% endblock %}