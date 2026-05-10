{% extends 'base.tpl' %}

{% block title %}{{ trans('profile.title') }} {{ user.username }} — {{ site_name }}{% endblock %}

{% block content %}
    <div class="row g-4">
        <div class="col-md-4">
            <div class="widget-card text-center p-4">
                <img src="{{ user.avatar|e('html_attr') }}" alt="{{ user.username }}" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                <h3 class="mb-1">{{ user.username }}</h3>
                <p class="text-secondary">{{ user.group.name }}</p>
                <hr style="border-color: var(--border-color);">
                <div class="text-start small">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-secondary">{{ trans('balance.title') }}:</span>
                        <strong>{{ user.money }} ₽</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-secondary">{{ trans('balance.total_recharge') }}:</span>
                        <strong>{{ user.allMoney }} ₽</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-secondary">{{ trans('profile.registration') }}:</span>
                        <span>{{ user.regData|format_date('medium', locale=locale) }}</span>
                    </div>
                </div>
                {% if app.user and app.user.id == user.id %}
                    <a href="{{ url('/settings') }}" class="btn btn-primary w-100 mt-3">
                        <i class="fas fa-cog me-1"></i>{{ trans('settings.title') }}
                    </a>
                {% endif %}
            </div>
        </div>
        <div class="col-md-8">
            <div class="widget-card p-3">
                <h5 class="mb-3">{{ trans('profile.stats') }}</h5>
                <div class="row text-center">
                    <div class="col-4">
                        <div class="display-6 fw-bold">{{ user.countThread }}</div>
                        <div class="text-secondary">{{ trans('profile.threads') }}</div>
                    </div>
                    <div class="col-4">
                        <div class="display-6 fw-bold">{{ user.countPost }}</div>
                        <div class="text-secondary">{{ trans('profile.posts') }}</div>
                    </div>
                    <div class="col-4">
                        <div class="display-6 fw-bold">{{ user.countLike }}</div>
                        <div class="text-secondary">{{ trans('profile.likes') }}</div>
                    </div>
                </div>
            </div>
            {% if app.user and app.user.id == user.id %}
                <div class="widget-card p-3 mt-4">
                    <h5 class="mb-3">{{ trans('profile.referral') }}</h5>
                    <p class="text-secondary">{{ trans('profile.referral_desc') }}</p>
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ url('/register?ref=' ~ user.id) }}" readonly id="refLink">
                        <button class="btn btn-primary" onclick="copyRefLink()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <a href="{{ url('/profile/referrals') }}" class="link-accent mt-2 d-inline-block">
                        {{ trans('profile.referrals') }} <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            {% endif %}
        </div>
    </div>
{% endblock %}

{% block scripts %}
    {{ parent() }}
    <script>
        function copyRefLink() {
            const input = document.getElementById('refLink');
            input.select();
            navigator.clipboard.writeText(input.value).then(() => {
                Toast.success('{{ trans("profile.copy_success") }}');
            }).catch(() => {
                Toast.error('{{ trans("profile.copy_error") }}');
            });
        }
    </script>
{% endblock %}