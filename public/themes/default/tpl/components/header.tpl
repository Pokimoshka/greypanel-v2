<header class="app-header">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between">
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="fas fa-gamepad me-2"></i>{{ site_name }}
            </a>
            
            <div class="d-flex align-items-center gap-3">
                <button class="theme-toggle" @click="toggleTheme">
                    <i class="fas" :class="getThemeIcon()"></i>
                    <span class="d-none d-sm-inline ms-2" x-text="getThemeText()"></span>
                </button>
                
                {% if app.user %}
                <div class="dropdown">
                    <button class="btn dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" style="color: var(--text-primary);">
                        <img src="{{ app.user.avatar }}" width="32" height="32" class="rounded-circle">
                        <span>{{ app.user.username }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ url('/profile') }}"><i class="fas fa-user me-2"></i>Профиль</a></li>
                        <li><a class="dropdown-item" href="{{ url('/balance') }}"><i class="fas fa-wallet me-2"></i>Баланс</a></li>
                        <li><a class="dropdown-item" href="{{ url('/settings') }}"><i class="fas fa-cog me-2"></i>Настройки</a></li>
                        {% if has_permission('a') %}
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ url('/admin') }}"><i class="fas fa-shield-alt me-2"></i>Админка</a></li>
                        {% endif %}
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="{{ url('/logout') }}"><i class="fas fa-sign-out-alt me-2"></i>Выход</a></li>
                    </ul>
                </div>
                {% else %}
                <a href="{{ url('/login') }}" class="btn btn-outline-light">Войти</a>
                {% endif %}
            </div>
        </div>
    </div>
</header>