{% extends "base.tpl" %}

{% block title %}{{ trans('admin.news') }}{% endblock %}
{% block page_title %}{{ trans('admin.news') }}{% endblock %}

{% block content %}
<div class="d-flex justify-content-end mb-3">
    <a href="{{ url('admin/news/create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>{{ trans('admin.add_news') }}</a>
</div>

<div class="card p-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>ID</th><th>{{ trans('admin.heading') }}</th><th>{{ trans('admin.author') }}</th><th>{{ trans('admin.published') }}</th><th>{{ trans('admin.views') }}</th><th>{{ trans('admin.actions') }}</th></tr>
            </thead>
            <tbody>
                {% for item in news_list %}
                <tr>
                    <td>{{ item.id }}</td>
                    <td><a href="{{ url('news/' ~ item.slug) }}" target="_blank">{{ item.title }}</a></td>
                    <td>{{ item.author_name }}</td>
                    <td>{{ item.is_published ? trans('common.yes') : trans('common.no') }}</td>
                    <td>{{ item.views }}</td>
                    <td>
                        <a href="{{ url('admin/news/edit/' ~ item.id) }}" class="btn btn-sm btn-outline-primary">{{ trans('admin.edit') }}</a>
                        <form method="post" action="{{ url('admin/news/delete/' ~ item.id) }}" style="display:inline;" onsubmit="return confirm('{{ trans('admin.confirm_delete') }}');">
                            <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                            <button class="btn btn-sm btn-outline-danger">{{ trans('admin.delete') }}</button>
                        </form>
                    </td>
                </tr>
                {% endfor %}
            </tbody>
        </table>
    </div>
</div>
{% include 'partials/pagination.tpl' with {'current': page, 'total': total, 'per_page': per_page, 'url': '/admin/news'} %}
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}