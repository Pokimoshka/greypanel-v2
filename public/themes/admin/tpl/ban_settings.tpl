{% extends "base.tpl" %}

{% block title %}{{ trans('admin.ban_settings') }}{% endblock %}

{% block content %}
<h1>{{ trans('admin.ban_settings') }}</h1>
<form method="post">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <div class="mb-3 form-check">
        <input type="checkbox" name="banlist_active" class="form-check-input" id="active" value="1" {{ settings.banlist_active == '1' ? 'checked' : '' }}>
        <label class="form-check-label" for="active">{{ trans('admin.enable_ban_list') }}</label>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.db_host') }}</label>
        <input type="text" name="banlist_host" class="form-control" value="{{ settings.banlist_host }}">
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.db_name') }}</label>
        <input type="text" name="banlist_db" class="form-control" value="{{ settings.banlist_db }}">
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.db_user') }}</label>
        <input type="text" name="banlist_user" class="form-control" value="{{ settings.banlist_user }}">
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.db_password') }}</label>
        <input type="password" name="banlist_pass" class="form-control" placeholder="******">
        <div class="form-text">{{ trans('admin.leave_empty') }}</div>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.db_prefix') }}</label>
        <input type="text" name="banlist_prefix" class="form-control" value="{{ settings.banlist_prefix }}">
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.unban_forum') }}</label>
        <select name="banlist_forum" class="form-select">
            <option value="0">-- {{ trans('admin.select_forum') }} --</option>
            {% for forum in forums %}
            <option value="{{ forum.id }}" {{ forum.id == settings.banlist_forum ? 'selected' : '' }}>{{ forum.title }}</option>
            {% endfor %}
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.paid_unban_price') }}</label>
        <input type="number" name="buy_razban" class="form-control" value="{{ settings.buy_razban }}">
    </div>
    <button type="submit" class="btn btn-primary">{{ trans('admin.save') }}</button>
</form>
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}