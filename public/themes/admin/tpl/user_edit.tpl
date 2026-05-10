{% extends "base.tpl" %}

{% block title %}{{ trans('admin.edit_user') }} {{ user.username }}{% endblock %}
{% block page_title %}{{ trans('admin.edit_user') }}: {{ user.username }}{% endblock %}

{% block content %}
<div class="card">
    <div class="card-body">
        <form method="post">
            <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
            <div class="mb-3">
                <label class="form-label">{{ trans('admin.group') }}</label>
                <select name="group_id" class="form-select">
                    {% for grp in groups %}
                        <option value="{{ grp.id }}" {{ user.group and user.group.id == grp.id ? 'selected' : '' }}>{{ grp.name }} ({{ grp.flags }})</option>
                    {% endfor %}
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ trans('admin.balance') }} (₽)</label>
                <input type="number" name="money" class="form-control" value="{{ user.money }}">
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="banned" class="form-check-input" id="banned" value="1" {{ user.banned ? 'checked' }}>
                <label class="form-check-label" for="banned">{{ trans('admin.banned') }}</label>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ trans('admin.new_password') }}</label>
                <input type="password" name="password" class="form-control" placeholder="{{ trans('admin.leave_empty') }}">
            </div>
            <button class="btn btn-primary">{{ trans('admin.save') }}</button>
            <a href="{{ url('admin/users') }}" class="btn btn-outline-secondary">{{ trans('admin.cancel') }}</a>
        </form>
    </div>
</div>
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}