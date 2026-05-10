<h5 class="mb-3 px-2" style="color: var(--accent);">
    <i class="fas fa-compass me-2"></i>{{ trans('nav.navigation') }}
</h5>
<nav class="nav flex-column">
    <a class="nav-link-custom {{ app.request.path == '/' or app.request.path == '/home' ? 'active' }}" href="{{ url('/') }}" @click="mobileMenuOpen = false">
        <i class="fas fa-home"></i> {{ trans('nav.home') }}
    </a>
    <a class="nav-link-custom {{ app.request.path starts with '/forum' ? 'active' }}" href="{{ url('/forum') }}" @click="mobileMenuOpen = false">
        <i class="fas fa-comments"></i> {{ trans('nav.forum') }}
    </a>
    <a class="nav-link-custom {{ app.request.path starts with '/news' ? 'active' }}" href="{{ url('/news') }}" @click="mobileMenuOpen = false">
        <i class="fas fa-newspaper"></i> {{ trans('nav.news') }}
    </a>
    <a class="nav-link-custom {{ app.request.path starts with '/monitor' ? 'active' }}" href="{{ url('/monitor') }}" @click="mobileMenuOpen = false">
        <i class="fas fa-chart-line"></i> {{ trans('nav.monitor') }}
    </a>
    <a class="nav-link-custom {{ app.request.path starts with '/stats' ? 'active' }}" href="{{ url('/stats') }}" @click="mobileMenuOpen = false">
        <i class="fas fa-trophy"></i> {{ trans('nav.stats') }}
    </a>
    <a class="nav-link-custom {{ app.request.path starts with '/bans' ? 'active' }}" href="{{ url('/bans') }}" @click="mobileMenuOpen = false">
        <i class="fas fa-gavel"></i> {{ trans('nav.bans') }}
    </a>
</nav>

<hr class="my-3" style="border-color: var(--border-color);">

<div class="px-2">
    <a href="{{ url('/forum') }}" class="btn w-100 mb-2" style="background: linear-gradient(145deg, var(--accent), var(--accent-hover)); color: white; border: none;" @click="mobileMenuOpen = false">
        <i class="fas fa-plus-circle me-1"></i> {{ trans('nav.create_thread') }}
    </a>
    <a href="{{ url('/payment') }}" class="btn btn-outline-primary w-100 mb-2" @click="mobileMenuOpen = false">
        <i class="fas fa-coins me-1"></i> {{ trans('nav.top_up') }}
    </a>
    <button class="theme-toggle w-100 mt-3" @click="toggleTheme">
        <i class="fas" :class="getThemeIcon()"></i>
        <span class="ms-2" x-text="getThemeText()"></span>
    </button>
</div>

<hr class="my-3" style="border-color: var(--border-color);">
<div class="px-2">
    <label class="small text-secondary mb-1">{{ trans('nav.language') }}</label>
    <select class="form-select form-select-sm" onchange="window.location.href = this.value">
        {% for code, name in available_languages %}
            <option value="{{ url('/language/' ~ code) }}" {{ locale == code ? 'selected' }}>
                {{ name }}
            </option>
        {% endfor %}
    </select>
</div>