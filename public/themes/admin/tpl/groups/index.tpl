{% extends "base.tpl" %}

{% block title %}{{ trans('admin.groups') }}{% endblock %}
{% block page_title %}{{ trans('admin.groups') }}{% endblock %}

{% block content %}
<div class="d-flex justify-content-end mb-3">
    <a href="{{ url('admin/groups/add') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>{{ trans('admin.add_group') }}</a>
</div>

<div class="card p-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>{{ trans('admin.name') }}</th>
                    <th>{{ trans('admin.flags') }}</th>
                    <th>{{ trans('admin.is_default') }}</th>
                    <th>{{ trans('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                {% for grp in groups %}
                <tr>
                    <td>{{ grp.id }}</td>
                    <td>{{ grp.name }}</td>
                    <td><code>{{ grp.flags }}</code></td>
                    <td>{{ grp.isDefault ? trans('common.yes') : trans('common.no') }}</td>
                    <td>
                        <a href="{{ url('admin/groups/edit/' ~ grp.id) }}" class="btn btn-sm btn-outline-primary">{{ trans('admin.edit') }}</a>
                        <form method="post" action="{{ url('admin/groups/delete/' ~ grp.id) }}" style="display:inline;" onsubmit="return confirm('{{ trans('admin.confirm_delete') }}');">
                            <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                            <button class="btn btn-sm btn-outline-danger">{{ trans('admin.delete') }}</button>
                        </form>
                    </td>
                </tr>
                {% else %}
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">{{ trans('admin.no_groups') }}</td>
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