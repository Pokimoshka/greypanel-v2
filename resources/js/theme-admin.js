import Alpine from 'alpinejs';
import { createThemeComponent } from './themeManager.js';

document.addEventListener('alpine:init', () => {
    Alpine.data('theme', () => ({
        ...createThemeComponent(),

        sidebarCollapsed: localStorage.getItem('admin_sidebar_collapsed') === 'true',
        mobileMenuOpen: false,

        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('admin_sidebar_collapsed', this.sidebarCollapsed);
        },

        toggleMobileMenu() {
            this.mobileMenuOpen = !this.mobileMenuOpen;
        },
    }));
});
