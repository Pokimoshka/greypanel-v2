<!DOCTYPE html>
<html lang="ru">
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
<body x-data="adminApp" x-init="init">
    <div class="admin-wrapper">
        <aside class="admin-sidebar" :class="{ 'collapsed': sidebarCollapsed, 'mobile-open': mobileMenuOpen }">
            <div class="sidebar-header">
                <a href="{{ url('admin') }}" class="sidebar-brand">
                    <i class="fas fa-shield-alt me-1"></i><span>{{ site_name }}</span>
                    <span class="badge bg-primary ms-2">Admin</span>
                </a>
            </div>
            <!-- Кнопка сворачивания отдельной строкой -->
            <div class="sidebar-collapse-control">
                <button class="sidebar-toggle" @click="toggleSidebar">
                    <i class="fas" :class="sidebarCollapsed ? 'fa-angle-right' : 'fa-angle-left'"></i>
                    <span x-show="!sidebarCollapsed">Свернуть</span>
                </button>
            </div>
            <div class="sidebar-menu">
                <ul class="nav flex-column">
                    <li class="nav-item"><a href="{{ url('admin') }}" class="nav-link"><i class="fas fa-tachometer-alt"></i> <span>Дашборд</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/site-settings') }}" class="nav-link"><i class="fas fa-globe"></i> <span>Настройки сайта</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/security') }}" class="nav-link"><i class="fas fa-shield-alt"></i> <span>Безопасность</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/users') }}" class="nav-link"><i class="fas fa-users"></i> <span>Пользователи</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/logs') }}" class="nav-link"><i class="fas fa-history"></i> <span>Логи</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/forum/categories') }}" class="nav-link"><i class="fas fa-comments"></i> <span>Форум</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/server-settings') }}" class="nav-link"><i class="fas fa-server"></i> <span>Серверы</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/news') }}" class="nav-link"><i class="fas fa-newspaper"></i> <span>Новости</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/themes') }}" class="nav-link"><i class="fas fa-palette"></i> <span>Темы</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/modules') }}" class="nav-link"><i class="fas fa-puzzle-piece"></i> <span>Модули</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/seo') }}" class="nav-link"><i class="fas fa-search"></i> <span>SEO</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/payments') }}" class="nav-link"><i class="fas fa-credit-card"></i> <span>Платежи</span></a></li>
                </ul>
            </div>
            <div class="sidebar-footer mt-auto pt-3">
                <button class="theme-toggle w-100" @click="toggleTheme">
                    <i class="fas" :class="getThemeIcon()"></i>
                    <span x-text="theme === 'light' ? 'Светлая' : (theme === 'dark' ? 'Тёмная' : 'Авто')"></span>
                </button>
            </div>
        </aside>

        <div class="admin-main">
            <nav class="admin-navbar">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <button class="btn btn-link text-secondary d-lg-none" @click="toggleMobileMenu">
                                <i class="fas fa-bars"></i>
                            </button>
                            <h4 class="mb-0">{% block page_title %}Панель управления{% endblock %}</h4>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <button class="theme-toggle d-none d-sm-flex" @click="toggleTheme">
                                <i class="fas" :class="getThemeIcon()"></i>
                                <span x-text="theme === 'light' ? 'Светлая' : (theme === 'dark' ? 'Тёмная' : 'Авто')"></span>
                            </button>
                            <div class="dropdown">
                                <button class="btn dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                                    <img src="{{ app.user.avatar }}" width="32" height="32" class="rounded-circle">
                                    <span>{{ app.user.username }}</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ url('profile') }}"><i class="fas fa-user me-2"></i>Профиль</a></li>
                                    <li><a class="dropdown-item" href="{{ url('settings') }}"><i class="fas fa-cog me-2"></i>Настройки</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="{{ url('logout') }}"><i class="fas fa-sign-out-alt me-2"></i>Выход</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <main class="admin-content">
                {% if app.flash.success %}
                    <div class="alert alert-success alert-dismissible fade show">{{ app.flash.success|e }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                {% endif %}
                {% if app.flash.error %}
                    <div class="alert alert-danger alert-dismissible fade show">{{ app.flash.error|e }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                {% endif %}
                {% block content %}{% endblock %}
            </main>
        </div>
    </div>

    <button class="mobile-menu-toggle d-lg-none" @click="toggleMobileMenu">
        <i class="fas fa-bars"></i>
    </button>

    {{ vite_assets('vendor')|raw }}
    <script src="{{ theme_url }}/js/theme.js"></script>
    {% block scripts %}{% endblock %}
</body>
</html>