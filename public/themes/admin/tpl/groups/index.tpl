{% extends "base.tpl" %}

{% block title %}Группы пользователей{% endblock %}
{% block page_title %}Управление группами{% endblock %}

{% block content %}
<div class="d-flex justify-content-end mb-3">
    <a href="{{ url('admin/groups/add') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Добавить группу</a>
</div>

<div class="card p-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Флаги</th>
                    <th>По умолчанию</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                {% for grp in groups %}
                <tr>
                    <td>{{ grp.id }}</td>
                    <td>{{ grp.name }}</td>
                    <td><code>{{ grp.flags }}</code></td>
                    <td>{{ grp.isDefault ? 'Да' : 'Нет' }}</td>
                    <td>
                        <a href="{{ url('admin/groups/edit/' ~ grp.id) }}" class="btn btn-sm btn-outline-primary">Ред.</a>
                        <form method="post" action="{{ url('admin/groups/delete/' ~ grp.id) }}" style="display:inline;" onsubmit="return confirm('Удалить группу?');">
                            <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                            <button class="btn btn-sm btn-outline-danger">Удалить</button>
                        </form>
                    </td>
                </tr>
                {% else %}
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">Нет созданных групп</td>
                </tr>
                {% endfor %}
            </tbody>
        </table>
    </div>
</div>
{% endblock %}