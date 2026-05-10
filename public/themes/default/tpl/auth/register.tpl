{% extends 'base.tpl' %}

{% block title %}{{ trans('auth.register') }} — {{ site_name }}{% endblock %}

{% block content %}
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="widget-card p-4">
                <h4 class="mb-4 text-center"><i class="fas fa-user-plus me-2" style="color: var(--accent);"></i>{{ trans('auth.register') }}</h4>
                {% if error %}<div class="alert alert-danger">{{ error }}</div>{% endif %}
                <form method="post">
                    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                    <div class="mb-3">
                        <label class="form-label">{{ trans('auth.username') }}</label>
                        <input type="text" name="username" class="form-control" value="{{ username|default('') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ trans('auth.email') }}</label>
                        <input type="email" name="email" class="form-control" value="{{ email|default('') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ trans('auth.password') }}</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ trans('auth.password_confirm') }}</label>
                        <input type="password" name="password2" class="form-control" required>
                    </div>

                    {% if recaptcha_enabled and recaptcha_site_key %}
                        <div class="mb-3">
                            <div class="g-recaptcha" data-sitekey="{{ recaptcha_site_key }}"></div>
                        </div>
                    {% endif %}

                    <button type="submit" class="btn btn-primary w-100">{{ trans('auth.register_button') }}</button>
                </form>
                <hr style="border-color: var(--border-color);">
                <p class="text-center mb-0">{{ trans('auth.has_account') }} <a href="{{ url('/login') }}" class="link-accent">{{ trans('auth.login_link') }}</a></p>
            </div>
        </div>
    </div>
{% endblock %}