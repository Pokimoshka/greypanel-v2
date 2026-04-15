<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token }}">
    <title>{% block title %}{{ site_name }}{% endblock %}</title>
    
    {# Стили #}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    {{ vite_assets('vendor', 'vendor_style')|raw }}
    <link rel="stylesheet" href="{{ theme_url }}/css/theme.css">
    {% block head %}{% endblock %}
</head>
<body x-data="app" x-init="init" class="fade-in">
    <div class="app-wrapper">
        {# Левое меню — фиксированное на всю высоту #}
        <aside class="sidebar-left" :class="{ 'open': mobileMenuOpen }">
            {% include 'components/sidebar_left.tpl' %}
        </aside>

        {# Основная область (Header + контент + Footer) #}
        <div class="app-main">
            {# Header #}
            {% block header %}
                {% include 'components/header.tpl' %}
            {% endblock %}

            {# Контент с правой колонкой #}
            <div class="main-content-wrapper">
                <div class="row g-4">
                    {# Центральная колонка — основной контент #}
                    <div class="col-lg-8">
                        {% block content %}{% endblock %}
                    </div>

                    {# Правая колонка — виджеты #}
                    <div class="col-lg-4">
                        <div class="sidebar-right">
                            {% block right_sidebar %}
                                {% include 'components/sidebar_right.tpl' %}
                            {% endblock %}
                        </div>
                    </div>
                </div>
            </div>

            {# Footer #}
            {% block footer %}
                {% include 'components/footer.tpl' %}
            {% endblock %}
        </div>
    </div>

    {# Кнопка мобильного меню #}
    <button class="mobile-menu-toggle d-lg-none" @click="toggleMobileMenu">
        <i class="fas fa-bars"></i>
    </button>

    {# Скрипты #}
    {{ vite_assets('vendor')|raw }}
    <script src="{{ theme_url }}/js/theme.js"></script>
    {% block scripts %}{% endblock %}
</body>
</html>