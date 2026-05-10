<!DOCTYPE html>
<html lang="{{ locale|default('ru') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token }}">
    {% if meta_title is defined %}
        <title>{{ meta_title }} — {{ site_name }}</title>
        <meta name="description" content="{{ meta_description|default('') }}">
        <meta name="keywords" content="{{ meta_keywords|default('') }}">
    {% else %}
        <title>{% block title %}{{ site_name }}{% endblock %}</title>
    {% endif %}
    
    {{ vite_assets('vendor', 'vendor_style')|raw }}
    <link rel="stylesheet" href="{{ theme_url }}/css/theme.css">
    {% block head %}
    {% if recaptcha_enabled and recaptcha_site_key %}
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    {% endif %}
    {% endblock %}
</head>
<body x-data="app" x-init="init" class="fade-in">
    <div class="app-wrapper">
        <aside class="sidebar-left" :class="{ 'open': mobileMenuOpen }">
            {% include 'components/sidebar_left.tpl' %}
        </aside>

        <div class="app-main">
            {% block header %}
                {% include 'components/header.tpl' %}
            {% endblock %}

            <div class="main-content-wrapper">
                <div class="row g-4">
                    <div class="col-lg-8">
                        {% block content %}{% endblock %}
                    </div>

                    <div class="col-lg-4">
                        <div class="sidebar-right">
                            {% block right_sidebar %}
                                {% include 'components/sidebar_right.tpl' %}
                            {% endblock %}
                        </div>
                    </div>
                </div>
            </div>

            {% block footer %}
                {% include 'components/footer.tpl' %}
            {% endblock %}
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

    <script>
        window.__ = {{ {
            'theme.light': trans('theme.light'),
            'theme.dark': trans('theme.dark'),
            'theme.auto': trans('theme.auto'),
            'widgets.online': trans('widgets.online'),
            'widgets.top_donators': trans('widgets.top_donators'),
            'widgets.last_topics': trans('widgets.last_topics'),
            'widgets.last_bans': trans('widgets.last_bans'),
            'widgets.all_topics': trans('widgets.all_topics'),
            'widgets.all_bans': trans('widgets.all_bans'),
            'errors.429': trans('errors.429'),
            'errors.back': trans('errors.back'),
            'bans.demo_prompt': '{{ trans("bans.demo_prompt") }}',
            'bans.request_created': '{{ trans("bans.request_created") }}',
            'bans.paid_confirm_prefix': '{{ trans("bans.paid_confirm_prefix") }}',
            'bans.paid_success': '{{ trans("bans.paid_success") }}',
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

{% block scripts %}
    {{ vite_assets('vendor')|raw }}
    {{ vite_assets('theme-default')|raw }}
{% endblock %}
</body>
</html>