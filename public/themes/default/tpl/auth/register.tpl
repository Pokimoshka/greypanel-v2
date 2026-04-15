{% extends 'base.tpl' %}

{% block title %}Регистрация — {{ site_name }}{% endblock %}

{% block content %}
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="widget-card p-4">
                <h4 class="mb-4 text-center"><i class="fas fa-user-plus me-2" style="color: var(--accent);"></i>Регистрация</h4>
                {% if error %}<div class="alert alert-danger">{{ error }}</div>{% endif %}
                <form method="post">
                    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                    <div class="mb-3">
                        <label class="form-label">Игровой ник</label>
                        <input type="text" name="username" class="form-control" value="{{ username|default('') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ email|default('') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Пароль</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Повторите пароль</label>
                        <input type="password" name="password2" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                </form>
                <hr style="border-color: var(--border-color);">
                <p class="text-center mb-0">Уже есть аккаунт? <a href="{{ url('/login') }}" class="link-accent">Войти</a></p>
            </div>
        </div>
    </div>
{% endblock %}