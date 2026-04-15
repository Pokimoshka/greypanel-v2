document.addEventListener('alpine:init', () => {
    Alpine.data('app', () => ({
        theme: localStorage.getItem('theme') || 'dark',
        systemTheme: window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light',
        mobileMenuOpen: false,
        
        init() {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                this.systemTheme = e.matches ? 'dark' : 'light';
                if (this.theme === 'auto') {
                    document.documentElement.setAttribute('data-bs-theme', this.systemTheme);
                }
            });
            
            this.$watch('theme', val => {
                const appliedTheme = val === 'auto' ? this.systemTheme : val;
                document.documentElement.setAttribute('data-bs-theme', appliedTheme);
                localStorage.setItem('theme', val);
            });
            
            const initialTheme = this.theme === 'auto' ? this.systemTheme : this.theme;
            document.documentElement.setAttribute('data-bs-theme', initialTheme);
        },
        
        toggleTheme() {
            const themes = ['light', 'dark', 'auto'];
            const currentIndex = themes.indexOf(this.theme);
            this.theme = themes[(currentIndex + 1) % themes.length];
        },
        
        getThemeIcon() {
            if (this.theme === 'light') return 'fa-sun';
            if (this.theme === 'dark') return 'fa-moon';
            return 'fa-circle-half-stroke';
        },
        
        getThemeText() {
            if (this.theme === 'light') return 'Светлая';
            if (this.theme === 'dark') return 'Тёмная';
            return 'Авто';
        },
        
        toggleMobileMenu() {
            this.mobileMenuOpen = !this.mobileMenuOpen;
        }
    }));

    Alpine.data('onlineWidget', () => ({
        count: 0,
        users: [],
        loading: true,
        
        async init() {
            await this.fetchOnline();
            setInterval(() => this.fetchOnline(), 30000);
        },
        
        async fetchOnline() {
            try {
                const res = await fetch('/online/data');
                const data = await res.json();
                this.count = data.count || 0;
                this.users = data.users || [];
            } catch (e) {
                console.error('Ошибка загрузки онлайн:', e);
            } finally {
                this.loading = false;
            }
        }
    }));

    Alpine.data('collapsibleWidget', (widgetId, defaultOpen = true) => ({
        open: localStorage.getItem(`widget_${widgetId}`) !== null 
            ? localStorage.getItem(`widget_${widgetId}`) === 'true'
            : defaultOpen,
        
        toggle() {
            this.open = !this.open;
            localStorage.setItem(`widget_${widgetId}`, this.open);
        }
    }));

    Alpine.data('lastTopicsWidget', () => ({
        topics: [],
        loading: true,
        
        async init() {
            await this.fetchTopics();
        },
        
        async fetchTopics() {
            try {
                const res = await fetch('/api/forum/last-topics?limit=5');
                const data = await res.json();
                this.topics = data;
            } catch (e) {
                console.error('Ошибка загрузки тем:', e);
            } finally {
                this.loading = false;
            }
        }
    }));

    Alpine.data('lastBansWidget', () => ({
        bans: [],
        loading: true,
        
        async init() {
            await this.fetchBans();
        },
        
        async fetchBans() {
            try {
                const res = await fetch('/api/bans/last-bans?limit=5');
                const data = await res.json();
                this.bans = data;
            } catch (e) {
                console.error('Ошибка загрузки банов:', e);
            } finally {
                this.loading = false;
            }
        }
    }));
});