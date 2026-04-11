<!DOCTYPE html>
<html lang="ru" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token }}">
    <title>{% block title %}Админка | {{ site_name }}{% endblock %}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    {{ vite_assets('vendor', 'vendor_style')|raw }}
    <link rel="stylesheet" href="{{ theme_url }}/css/admin.css">
    {% block head %}{% endblock %}
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <!-- Левая боковая панель (можно сворачивать) -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <a href="{{ url('admin') }}" class="sidebar-brand">{{ site_name }} <span class="badge bg-primary">Admin</span></a>
                <button class="sidebar-toggle" id="sidebarCollapse"><i class="fas fa-bars"></i></button>
            </div>
            <div class="sidebar-menu">
                <ul class="nav flex-column">
                    <li class="nav-item"><a href="{{ url('admin') }}" class="nav-link"><i class="fas fa-tachometer-alt"></i> <span>Дашборд</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/users') }}" class="nav-link"><i class="fas fa-users"></i> <span>Пользователи</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/logs') }}" class="nav-link"><i class="fas fa-history"></i> <span>Логи</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/forum/categories') }}" class="nav-link"><i class="fas fa-comments"></i> <span>Форум</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/vip/servers') }}" class="nav-link"><i class="fas fa-crown "></i> <span>VIP серверы</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/monitor/servers') }}" class="nav-link"><i class="fas fa-server"></i> <span>Мониторинг</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/themes') }}" class="nav-link"><i class="fas fa-palette"></i> <span>Темы</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/modules') }}" class="nav-link"><i class="fas fa-puzzle-piece"></i> <span>Модули</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/seo') }}" class="nav-link"><i class="fas fa-search"></i> <span>SEO</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/payments') }}" class="nav-link"><i class="fas fa-credit-card"></i> <span>Платежи</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/bans/settings') }}" class="nav-link"><i class="fas fa-gavel"></i> <span>Бан-лист</span></a></li>
                </ul>
            </div>
        </aside>

        <!-- Основной контент -->
        <div class="admin-main">
            <!-- Верхняя панель -->
            <nav class="admin-navbar">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between w-100">
                        <div class="d-flex align-items-center">
                            <button class="navbar-toggler d-md-none" id="mobileSidebarToggle"><i class="fas fa-bars"></i></button>
                            <div class="navbar-brand">{% block page_title %}Панель управления{% endblock %}</div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="dropdown">
                                <button class="btn btn-link text-light dropdown-toggle" data-bs-toggle="dropdown">
                                    <img src="{{ app.user.avatar }}" width="30" height="30" class="rounded-circle me-1">
                                    {{ app.user.username }}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ url('profile') }}"><i class="fas fa-user me-2"></i> Профиль</a></li>
                                    <li><a class="dropdown-item" href="{{ url('settings') }}"><i class="fas fa-cog me-2"></i> Настройки</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ url('logout') }}"><i class="fas fa-sign-out-alt me-2"></i> Выйти</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <main class="admin-content">
                <div class="container-fluid">
                    {% if app.flash.success %}
                        <div class="alert alert-success alert-dismissible fade show">{{ app.flash.success }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    {% endif %}
                    {% if app.flash.error %}
                        <div class="alert alert-danger alert-dismissible fade show">{{ app.flash.error }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    {% endif %}
                    {% block content %}{% endblock %}
                </div>
            </main>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('adminSidebar');
        const sidebarCollapse = document.getElementById('sidebarCollapse');
        if (sidebarCollapse) {
            sidebarCollapse.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('adminSidebarCollapsed', sidebar.classList.contains('collapsed'));
            });
        }
        if (localStorage.getItem('adminSidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
        }

        const mobileToggle = document.getElementById('mobileSidebarToggle');
        if (mobileToggle) {
            mobileToggle.addEventListener('click', () => {
                sidebar.classList.toggle('mobile-open');
            });
        }
    </script>
    {{ vite_assets('vendor')|raw }}
    {% block scripts %}{% endblock %}
</body>
</html>