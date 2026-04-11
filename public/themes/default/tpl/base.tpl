<!DOCTYPE html>
<html lang="ru" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token }}">
    <title>{% block title %}{{ site_name }}{% endblock %}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    {{ vite_assets('vendor', 'vendor_style')|raw }}
    <link rel="stylesheet" href="{{ theme_url }}/css/theme.css">
    {% block head %}{% endblock %}
</head>
<body>

<div class="app-wrapper">
    <!-- Левое меню (обычный блок, не фиксированный) -->
    <aside class="app-sidebar">
        <div class="sidebar-header">
            <a class="sidebar-brand" href="{{ url('/') }}">{{ site_name }}</a>
        </div>
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link" href="{{ url('/') }}"><i class="fas fa-home me-2"></i> Главная</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('forum') }}"><i class="fas fa-comments me-2"></i> Форум</a></li>
                {% if module_enabled('monitor') %}
                <li class="nav-item"><a class="nav-link" href="{{ url('monitor') }}"><i class="fas fa-server me-2"></i> Мониторинг</a></li>
                {% endif %}
                {% if module_enabled('bans') %}
                <li class="nav-item"><a class="nav-link" href="{{ url('bans') }}"><i class="fas fa-gavel me-2"></i> Бан-лист</a></li>
                {% endif %}
                {% if app.user %}
                <li class="nav-item"><a class="nav-link" href="{{ url('vip') }}"><i class="fas fa-crown me-2"></i> VIP</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('balance') }}"><i class="fas fa-wallet me-2"></i> Баланс</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('profile') }}"><i class="fas fa-user me-2"></i> Профиль</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('settings') }}"><i class="fas fa-cog me-2"></i> Настройки</a></li>
                {% if app.user.group >= 3 %}
                <li class="nav-item"><a class="nav-link" href="{{ url('admin') }}"><i class="fas fa-tachometer-alt me-2"></i> Админка</a></li>
                {% endif %}
                <li class="nav-item"><a class="nav-link" href="{{ url('logout') }}"><i class="fas fa-sign-out-alt me-2"></i> Выйти</a></li>
                {% else %}
                <li class="nav-item"><a class="nav-link" href="{{ url('login') }}"><i class="fas fa-sign-in-alt me-2"></i> Вход</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('register') }}"><i class="fas fa-user-plus me-2"></i> Регистрация</a></li>
                {% endif %}
            </ul>
        </nav>
    </aside>

    <!-- Основной контент + футер -->
    <div class="app-main">
        <main class="flex-grow-1">
            <div class="container-fluid px-4 py-3">
                {% if app.flash.success %}
                    <div class="alert alert-success alert-dismissible fade show">{{ app.flash.success }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                {% endif %}
                {% if app.flash.error %}
                    <div class="alert alert-danger alert-dismissible fade show">{{ app.flash.error }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                {% endif %}
                {% block content %}{% endblock %}
            </div>
        </main>
        <footer class="app-footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4">
                        <h5>{{ site_name }}</h5>
                        <p class="text-muted">Современная панель управления игровыми серверами.</p>
                    </div>
                    <div class="col-md-4">
                        <h5>Навигация</h5>
                        <ul class="list-unstyled">
                            <li><a href="{{ url('/') }}">Главная</a></li>
                            <li><a href="{{ url('forum') }}">Форум</a></li>
                            <li><a href="{{ url('monitor') }}">Мониторинг</a></li>
                            <li><a href="{{ url('bans') }}">Бан-лист</a></li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h5>Контакты</h5>
                        <ul class="list-unstyled">
                            <li><i class="fab fa-vk me-2"></i> <a href="#">ВКонтакте</a></li>
                            <li><i class="fab fa-telegram me-2"></i> <a href="#">Telegram</a></li>
                            <li><i class="fab fa-discord me-2"></i> <a href="#">Discord</a></li>
                        </ul>
                    </div>
                </div>
                <div class="text-center mt-3">
                    &copy; {{ "now"|date("Y") }} {{ site_name }}. Все права защищены.
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- Мобильное меню (гамбургер) -->
<button class="sidebar-toggle d-md-none" id="sidebarToggle"><i class="fas fa-bars"></i></button>
<script>
    const sidebar = document.querySelector('.app-sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    }
</script>

{{ vite_assets('vendor')|raw }}
{% block scripts %}{% endblock %}
</body>
</html>