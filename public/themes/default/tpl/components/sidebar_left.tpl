<h5 class="mb-3 px-2" style="color: var(--accent);">
    <i class="fas fa-compass me-2"></i>Навигация
</h5>
<nav class="nav flex-column">
    <a class="nav-link-custom {{ app.request.path == '/' or app.request.path == '/home' ? 'active' }}" href="{{ url('/') }}" @click="mobileMenuOpen = false">
        <i class="fas fa-home"></i> Главная
    </a>
    <a class="nav-link-custom {{ app.request.path starts with '/forum' ? 'active' }}" href="{{ url('/forum') }}" @click="mobileMenuOpen = false">
        <i class="fas fa-comments"></i> Форум
    </a>
    <a class="nav-link-custom {{ app.request.path starts with '/news' ? 'active' }}" href="{{ url('/news') }}" @click="mobileMenuOpen = false">
        <i class="fas fa-newspaper"></i> Новости
    </a>
    <a class="nav-link-custom {{ app.request.path starts with '/monitor' ? 'active' }}" href="{{ url('/monitor') }}" @click="mobileMenuOpen = false">
        <i class="fas fa-chart-line"></i> Мониторинг
    </a>
    <a class="nav-link-custom {{ app.request.path starts with '/stats' ? 'active' }}" href="{{ url('/stats') }}" @click="mobileMenuOpen = false">
        <i class="fas fa-trophy"></i> Статистика
    </a>
    <a class="nav-link-custom {{ app.request.path starts with '/bans' ? 'active' }}" href="{{ url('/bans') }}" @click="mobileMenuOpen = false">
        <i class="fas fa-gavel"></i> Бан-лист
    </a>
</nav>

<hr class="my-3" style="border-color: var(--border-color);">

<div class="px-2">
    <a href="{{ url('/forum/forum/1/create') }}" class="btn w-100 mb-2" style="background: linear-gradient(145deg, var(--accent), var(--accent-hover)); color: white; border: none;" @click="mobileMenuOpen = false">
        <i class="fas fa-plus-circle me-1"></i> Создать тему
    </a>
    <a href="{{ url('/payment') }}" class="btn btn-outline-primary w-100 mb-2" @click="mobileMenuOpen = false">
        <i class="fas fa-coins me-1"></i> Пополнить баланс
    </a>
    <button class="theme-toggle w-100 mt-3" @click="toggleTheme">
        <i class="fas" :class="getThemeIcon()"></i>
        <span class="ms-2" x-text="getThemeText()"></span>
    </button>
</div>