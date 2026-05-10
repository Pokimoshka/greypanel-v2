{% extends "base.tpl" %}

{% block title %}{{ trans('admin.forum_sections') }} "{{ category.title }}"{% endblock %}

{% block content %}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>{{ trans('admin.forum_sections') }}: {{ category.title }}</h1>
    <a href="/admin/forum/categories/{{ category.id }}/forums/add" class="btn btn-primary">{{ trans('admin.add_section') }}</a>
</div>

<table class="table table-striped" id="sortable-table">
    <thead><tr><th>ID</th><th>{{ trans('admin.icon') }}</th><th>{{ trans('admin.heading') }}</th><th>{{ trans('admin.description') }}</th><th>{{ trans('admin.sort_order') }}</th><th>{{ trans('admin.actions') }}</th></tr></thead>
    <tbody id="sortable-forums">
        {% for forum in forums %}
        <tr data-id="{{ forum.id }}">
            <td>{{ forum.id }}</td>
            <td><i class="{{ forum.icon }}"></i></td>
            <td>{{ forum.title }}</td>
            <td>{{ forum.description }}</td>
            <td class="sort-order">{{ forum.sort_order }}</td>
            <td>
                <a href="/admin/forum/categories/{{ category.id }}/forums/edit/{{ forum.id }}" class="btn btn-sm btn-primary">{{ trans('admin.edit') }}</a>
                <form method="post" action="/admin/forum/categories/{{ category.id }}/forums/delete/{{ forum.id }}" style="display:inline;" onsubmit="return confirm('{{ trans('admin.confirm_delete') }}');">
                    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                    <button type="submit" class="btn btn-sm btn-danger">{{ trans('admin.delete') }}</button>
                </form>
            </td>
        </tr>
        {% endfor %}
    </tbody>
</table>
<button id="save-order" class="btn btn-secondary">{{ trans('admin.save_order') }}</button>
<a href="/admin/forum/categories" class="btn btn-link">← {{ trans('admin.back_to_categories') }}</a>
{% endblock %}

{% block scripts %}
{{ parent() }}
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

document.getElementById('save-order')?.addEventListener('click', function() {
    let rows = document.querySelectorAll('#sortable-forums tr');
    let order = {};
    rows.forEach((row, idx) => { order[row.dataset.id] = idx; });
    fetch('/admin/forum/forums/sort', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'order=' + JSON.stringify(order) + '&csrf_token=' + encodeURIComponent(csrfToken)
    }).then(() => location.reload());
});
</script>
{% endblock %}