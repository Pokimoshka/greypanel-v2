{% extends 'base.tpl' %}

{% block title %}Профиль {{ user.username }} — {{ site_name }}{% endblock %}

{% block content %}
    <div class="row g-4">
        <div class="col-md-4">
            <div class="widget-card text-center p-4">
                <img src="{{ user.avatar }}" alt="{{ user.username }}" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                <h3 class="mb-1">{{ user.username }}</h3>
                <p class="text-secondary">
                    {% if user.group == 4 %}Root Admin
                    {% elseif user.group == 3 %}Admin
                    {% elseif user.group == 2 %}Moderator
                    {% elseif user.group == 1 %}Меценат
                    {% else %}Пользователь
                    {% endif %}
                </p>
                <hr style="border-color: var(--border-color);">
                <div class="text-start small">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-secondary">Баланс:</span>
                        <strong>{{ user.money }} ₽</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-secondary">Всего пополнено:</span>
                        <strong>{{ user.allMoney }} ₽</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-secondary">Регистрация:</span>
                        <span>{{ user.regData|date('d.m.Y') }}</span>
                    </div>
                </div>
                {% if app.user and app.user.id == user.id %}
                    <a href="{{ url('/settings') }}" class="btn btn-primary w-100 mt-3">
                        <i class="fas fa-cog me-1"></i>Редактировать
                    </a>
                {% endif %}
            </div>
        </div>
        <div class="col-md-8">
            <div class="widget-card p-3">
                <h5 class="mb-3">Статистика</h5>
                <div class="row text-center">
                    <div class="col-4">
                        <div class="display-6 fw-bold">{{ user.countTheard }}</div>
                        <div class="text-secondary">Тем на форуме</div>
                    </div>
                    <div class="col-4">
                        <div class="display-6 fw-bold">{{ user.countPost }}</div>
                        <div class="text-secondary">Сообщений</div>
                    </div>
                    <div class="col-4">
                        <div class="display-6 fw-bold">{{ user.countLike }}</div>
                        <div class="text-secondary">Лайков</div>
                    </div>
                </div>
            </div>
            {% if app.user and app.user.id == user.id %}
                <div class="widget-card p-3 mt-4">
                    <h5 class="mb-3">Реферальная программа</h5>
                    <p class="text-secondary">Приглашайте друзей и получайте 10% от их пополнений.</p>
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ url('/register?ref=' ~ user.id) }}" readonly id="refLink">
                        <button class="btn btn-primary" onclick="copyRefLink()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <a href="{{ url('/profile/referrals') }}" class="link-accent mt-2 d-inline-block">
                        Список приглашённых <i class="fas fa-arrow-right ms-1"></i>
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
            document.execCommand('copy');
            alert('Ссылка скопирована!');
        }
    </script>
{% endblock %}