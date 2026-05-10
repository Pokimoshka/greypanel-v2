export function createThemeComponent() {
    return {
        theme: localStorage.getItem('theme') || 'dark',
        systemTheme: window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light',

        init() {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                this.systemTheme = e.matches ? 'dark' : 'light';
                if (this.theme === 'auto') {
                    document.documentElement.setAttribute('data-bs-theme', this.systemTheme);
                }
            });

            this.$watch('theme', (val) => {
                const applied = val === 'auto' ? this.systemTheme : val;
                document.documentElement.setAttribute('data-bs-theme', applied);
                localStorage.setItem('theme', val);
            });

            const initial = this.theme === 'auto' ? this.systemTheme : this.theme;
            document.documentElement.setAttribute('data-bs-theme', initial);
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

        getThemeText() {
            const t = window.__ || {};
            if (this.theme === 'light') return t['theme.light'] || 'Light';
            if (this.theme === 'dark') return t['theme.dark'] || 'Dark';
            return t['theme.auto'] || 'Auto';
        },
    };
}
