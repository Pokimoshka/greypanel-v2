{% extends "base.tpl" %}

{% block title %}Настройки профиля{% endblock %}

{% block content %}
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Настройки аккаунта</div>
            <div class="card-body">
                {% if error %}
                    <div class="alert alert-danger">{{ error }}</div>
                {% endif %}
                {% if success %}
                    <div class="alert alert-success">{{ success }}</div>
                {% endif %}
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ user.email }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Новый пароль (оставьте пустым, если не хотите менять)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Повторите пароль</label>
                        <input type="password" name="password_confirm" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Аватар (jpg, png, gif, до 2 МБ)</label>
                        <input type="file" name="avatar" class="form-control" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </form>
            </div>
        </div>
    </div>
</div>
{% endblock %}