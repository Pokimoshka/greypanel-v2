{% extends 'base.tpl' %}

{% block title %}Вход — {{ site_name }}{% endblock %}

{% block content %}
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="widget-card p-4">
                <h4 class="mb-4 text-center"><i class="fas fa-sign-in-alt me-2" style="color: var(--accent);"></i>Вход</h4>
                {% if error %}<div class="alert alert-danger">{{ error }}</div>{% endif %}
                <form method="post">
                    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                    <div class="mb-3">
                        <label class="form-label">Ник или Email</label>
                        <input type="text" name="username" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Пароль</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Запомнить меня</label>
                    </div>

                    {% if recaptcha_site_key %}
                    <div class="mb-3">
                        <div class="g-recaptcha" data-sitekey="{{ recaptcha_site_key }}"></div>
                    </div>
                    {% endif %}

                    <button type="submit" class="btn btn-primary w-100">Войти</button>
                </form>
                <hr style="border-color: var(--border-color);">
                <p class="text-center mb-0">Нет аккаунта? <a href="{{ url('/register') }}" class="link-accent">Зарегистрироваться</a></p>
            </div>
        </div>
    </div>
{% endblock %}