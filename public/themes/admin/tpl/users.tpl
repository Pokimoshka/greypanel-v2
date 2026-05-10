{% extends "base.tpl" %}

{% block title %}{{ trans('admin.users') }}{% endblock %}
{% block page_title %}{{ trans('admin.user_list') }}{% endblock %}

{% block content %}
<div class="card">
    <div class="card-body">
        <form method="get" class="row g-2 mb-3">
            <div class="col-md-8">
                <input type="text" name="search" class="form-control" placeholder="{{ trans('admin.search_placeholder') }}" value="{{ search }}">
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>{{ trans('admin.search') }}</button>
            </div>
        </form>
    </div>
</div>

<div class="card p-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>ID</th><th>{{ trans('admin.username') }}</th><th>{{ trans('admin.email') }}</th><th>{{ trans('admin.group') }}</th><th>{{ trans('admin.balance') }}</th><th>{{ trans('admin.banned') }}</th><th>{{ trans('admin.actions') }}</th></tr>
            </thead>
            <tbody>
                {% for user in users %}
                <tr>
                    <td>{{ user.id }}</td>
                    <td>{{ user.username }}</td>
                    <td>{{ user.email }}</td>
                    <td>{{ user.group ? user.group.name : '—' }}</td>
                    <td>{{ user.money }} ₽</td>
                    <td>{{ user.banned ? trans('common.yes') : trans('common.no') }}</td>
                    <td><a href="{{ url('admin/users/edit/' ~ user.id) }}" class="btn btn-sm btn-outline-primary">{{ trans('admin.edit') }}</a></td>
                </tr>
                {% endfor %}
            </tbody>
        </table>
    </div>
</div>
{% include 'partials/pagination.tpl' with {'current': page, 'total': total, 'per_page': per_page, 'url': '/admin/users', 'params': {'search': search}} %}
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}