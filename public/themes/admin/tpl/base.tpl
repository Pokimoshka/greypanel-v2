<!DOCTYPE html>
<html lang="{{ locale|default('ru') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token }}">
    <title>{% block title %}{{ trans('admin.title') }} | {{ site_name }}{% endblock %}</title>
    {{ vite_assets('admin', 'vendor_style')|raw }}
    <link rel="stylesheet" href="{{ theme_url }}/css/admin.css">
    {% block head %}{% endblock %}
</head>
<body x-data="theme" x-init="init">
    <div class="admin-wrapper">
        <aside class="admin-sidebar" :class="{ 'collapsed': sidebarCollapsed, 'mobile-open': mobileMenuOpen }">
            <div class="sidebar-header">
                <a href="{{ url('admin') }}" class="sidebar-brand">
                    <i class="fas fa-shield-alt me-1"></i><span>{{ site_name }}</span>
                    <span class="badge bg-primary ms-2">Admin</span>
                </a>
            </div>

            <div class="sidebar-collapse-control">
                <button class="sidebar-toggle" @click="toggleSidebar">
                    <i class="fas" :class="sidebarCollapsed ? 'fa-angle-right' : 'fa-angle-left'"></i>
                    <span x-show="!sidebarCollapsed">{{ sidebarCollapsed ? trans('sidebar.expand') : trans('sidebar.collapse') }}</span>
                </button>
            </div>

            <a href="{{ url('/') }}" class="btn btn-sm btn-outline-secondary ms-2" target="_blank">
                <i class="fas fa-external-link-alt"></i> {{ trans('nav.site') }}
            </a>

            <div class="sidebar-menu">
                <ul class="nav flex-column">
                    <li class="nav-item"><a href="{{ url('admin') }}" class="nav-link"><i class="fas fa-tachometer-alt"></i> <span>{{ trans('admin.dashboard') }}</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/site-settings') }}" class="nav-link"><i class="fas fa-globe"></i> <span>{{ trans('admin.settings') }}</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/security') }}" class="nav-link"><i class="fas fa-shield-alt"></i> <span>{{ trans('admin.security') }}</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/groups') }}" class="nav-link"><i class="fas fa-users-cog"></i> <span>{{ trans('admin.groups') }}</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/services') }}" class="nav-link"><i class="fas fa-cogs"></i> <span>{{ trans('admin.services') }}</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/users') }}" class="nav-link"><i class="fas fa-users"></i> <span>{{ trans('admin.users') }}</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/logs') }}" class="nav-link"><i class="fas fa-history"></i> <span>{{ trans('admin.logs') }}</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/forum/categories') }}" class="nav-link"><i class="fas fa-comments"></i> <span>{{ trans('admin.forum') }}</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/server-settings') }}" class="nav-link"><i class="fas fa-server"></i> <span>{{ trans('admin.servers') }}</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/news') }}" class="nav-link"><i class="fas fa-newspaper"></i> <span>{{ trans('admin.news') }}</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/themes') }}" class="nav-link"><i class="fas fa-palette"></i> <span>{{ trans('admin.themes') }}</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/theme-editor') }}" class="nav-link"><i class="fas fa-edit"></i> <span>{{ trans('admin.editor') }}</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/modules') }}" class="nav-link"><i class="fas fa-puzzle-piece"></i> <span>{{ trans('admin.modules') }}</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/seo') }}" class="nav-link"><i class="fas fa-search"></i> <span>{{ trans('admin.seo') }}</span></a></li>
                    <li class="nav-item"><a href="{{ url('admin/payments') }}" class="nav-link"><i class="fas fa-credit-card"></i> <span>{{ trans('admin.payments') }}</span></a></li>
                </ul>
            </div>

            <div class="sidebar-footer mt-auto pt-3">
                <button class="theme-toggle w-100" @click="toggleTheme">
                    <i class="fas" :class="getThemeIcon()"></i>
                    <span x-text="getThemeText()"></span>
                </button>
            </div>
        </aside>

        <div class="admin-main">
            <nav class="admin-navbar">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <button class="btn btn-link text-secondary d-lg-none" @click="toggleMobileMenu"><i class="fas fa-bars"></i></button>
                            <h4 class="mb-0">{% block page_title %}{{ trans('admin.title') }}{% endblock %}</h4>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <button class="theme-toggle d-none d-sm-flex" @click="toggleTheme">
                                <i class="fas" :class="getThemeIcon()"></i>
                                <span x-text="getThemeText()"></span>
                            </button>
                            <div class="dropdown">
                                <button class="btn dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                                    <img src="{{ app.user.avatar|e('html_attr') }}" width="32" height="32" class="rounded-circle">
                                    <span>{{ app.user.username }}</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ url('profile') }}"><i class="fas fa-user me-2"></i>{{ trans('nav.profile') }}</a></li>
                                    <li><a class="dropdown-item" href="{{ url('settings') }}"><i class="fas fa-cog me-2"></i>{{ trans('nav.settings') }}</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="{{ url('logout') }}"><i class="fas fa-sign-out-alt me-2"></i>{{ trans('nav.logout') }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <main class="admin-content">
                {% if install_exists %}
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>{{ trans('admin.install_warning') }}</strong> {{ trans('admin.install_warning_text') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                {% endif %}

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

    <div x-data="toast" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="toast show align-items-center text-white" :class="'bg-' + toast.type" role="alert">
                <div class="d-flex">
                    <div class="toast-body" x-text="toast.message"></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="toasts = toasts.filter(t => t.id !== toast.id)"></button>
                </div>
            </div>
        </template>
    </div>
{% block scripts %}
    {{ vite_assets('vendor')|raw }}
    {{ vite_assets('admin')|raw }}
    {{ vite_assets('theme-admin')|raw }}

    <script>
        window.__ = {{ {
            'theme.light': trans('theme.light'),
            'theme.dark': trans('theme.dark'),
            'theme.auto': trans('theme.auto'),
            'sidebar.collapse': trans('sidebar.collapse'),
            'sidebar.expand': trans('sidebar.expand'),
            'admin.chart_registrations': trans('admin.chart_registrations'),
            'online.just_now': trans('online.just_now'),
            'online.min_ago': trans('online.min_ago'),
            'online.hour_ago': trans('online.hour_ago'),
            'editor.link_title': trans('editor.link_title'),
            'editor.image_title': trans('editor.image_title'),
            'editor.emoji_title': trans('editor.emoji_title'),
            'editor.image_error': trans('editor.image_error'),
            'theme_editor.file_saved': trans('theme_editor.file_saved'),
            'theme_editor.error_occurred': trans('theme_editor.error_occurred'),
            'theme_editor.confirm_delete': trans('theme_editor.confirm_delete'),
        }|json_encode|raw }};
    </script>
{% endblock %}
</body>
</html>