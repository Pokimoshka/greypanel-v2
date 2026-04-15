document.addEventListener('alpine:init', () => {
    Alpine.data('adminApp', () => ({
        theme: localStorage.getItem('admin_theme') || 'dark',
        systemTheme: window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light',
        sidebarCollapsed: localStorage.getItem('admin_sidebar_collapsed') === 'true',
        mobileMenuOpen: false,
        
        init() {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                this.systemTheme = e.matches ? 'dark' : 'light';
                if (this.theme === 'auto') {
                    document.documentElement.setAttribute('data-bs-theme', this.systemTheme);
                }
            });
            
            this.$watch('theme', val => {
                const applied = val === 'auto' ? this.systemTheme : val;
                document.documentElement.setAttribute('data-bs-theme', applied);
                localStorage.setItem('admin_theme', val);
            });
            
            const initial = this.theme === 'auto' ? this.systemTheme : this.theme;
            document.documentElement.setAttribute('data-bs-theme', initial);
            
            this.$watch('sidebarCollapsed', val => {
                localStorage.setItem('admin_sidebar_collapsed', val);
            });
        },
        
        toggleTheme() {
            const themes = ['light', 'dark', 'auto'];
            const idx = themes.indexOf(this.theme);
            this.theme = themes[(idx + 1) % themes.length];
        },
        
        getThemeIcon() {
            if (this.theme === 'light') return 'fa-sun';
            if (this.theme === 'dark') return 'fa-moon';
            return 'fa-circle-half-stroke';
        },
        
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
        },
        
        toggleMobileMenu() {
            this.mobileMenuOpen = !this.mobileMenuOpen;
        }
    }));
});