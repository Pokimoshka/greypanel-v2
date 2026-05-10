{% extends "base.tpl" %}

{% block title %}{{ trans('admin.server_settings') }}{% endblock %}
{% block page_title %}{{ trans('admin.servers') }}{% endblock %}

{% block content %}
<div class="d-flex justify-content-end mb-3">
    <a href="{{ url('admin/server-settings/add') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>{{ trans('admin.add_server') }}</a>
</div>

<div class="card p-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>ID</th><th>IP:{{ trans('admin.port') }}</th><th>{{ trans('admin.privileges') }}</th><th>{{ trans('admin.stats') }}</th><th>{{ trans('admin.status') }}</th><th>{{ trans('admin.actions') }}</th></tr>
            </thead>
            <tbody>
                {% for server in servers %}
                <tr>
                    <td>{{ server.id }}</td>
                    <td><code>{{ server.ip }}:{{ server.c_port }}</code></td>
                    <td>
                        {% if server.privilege_storage == 1 %}<span class="badge bg-secondary">users.ini</span>
                        {% elseif server.privilege_storage == 2 %}<span class="badge bg-info">AmxBans</span>
                        {% elseif server.privilege_storage == 3 %}<span class="badge bg-primary">AmxBans + users.ini</span>
                        {% else %}<span class="badge bg-light text-dark">—</span>{% endif %}
                    </td>
                    <td>
                        {% if server.stats_engine == 1 %}<span class="badge bg-secondary">CSStats</span>
                        {% elseif server.stats_engine == 2 %}<span class="badge bg-info">AES</span>
                        {% elseif server.stats_engine == 3 %}<span class="badge bg-primary">CSStats + AES</span>
                        {% else %}<span class="badge bg-light text-dark">—</span>{% endif %}
                    </td>
                    <td>
                        <span class="badge {{ server.status == 0 ? 'bg-success' : 'bg-danger' }}">
                            {{ server.status == 0 ? trans('admin.online') : trans('admin.offline') }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ url('admin/server-settings/edit/' ~ server.id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <a href="{{ url('admin/server-settings/test/' ~ server.id) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-sync-alt"></i></a>
                        <form method="post" action="{{ url('admin/server-settings/delete/' ~ server.id) }}" style="display:inline;" onsubmit="return confirm('{{ trans('admin.confirm_delete') }}');">
                            <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                {% else %}
                <tr><td colspan="6" class="text-center text-muted py-3">{{ trans('admin.no_servers') }}</td></tr>
                {% endfor %}
            </tbody>
        </table>
    </div>
</div>
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}