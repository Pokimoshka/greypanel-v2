{% extends "base.tpl" %}

{% block title %}Вход{% endblock %}

{% block content %}
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Вход на сайт</div>
            <div class="card-body">
                {% if error is defined %}
                    <div class="alert alert-danger">{{ error }}</div>
                {% endif %}
                <form method="post">
                    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                    <div class="mb-3">
                        <label for="username" class="form-label">Ник или Email</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Запомнить меня</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Войти</button>
                </form>
            </div>
        </div>
    </div>
</div>
{% endblock %}