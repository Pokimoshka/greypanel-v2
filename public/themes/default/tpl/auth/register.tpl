{% extends "base.tpl" %}

{% block title %}Регистрация{% endblock %}

{% block content %}
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Регистрация</div>
            <div class="card-body">
                {% if error is defined %}
                    <div class="alert alert-danger">{{ error }}</div>
                {% endif %}
                <form method="post">
                    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                    <div class="mb-3">
                        <label for="username" class="form-label">Игровой ник</label>
                        <input type="text" class="form-control" id="username" name="username" value="{{ username|default('') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ email|default('') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="password2" class="form-label">Повторите пароль</label>
                        <input type="password" class="form-control" id="password2" name="password2" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
                </form>
            </div>
        </div>
    </div>
</div>
{% endblock %}