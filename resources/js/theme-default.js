import Alpine from 'alpinejs';
import { createThemeComponent } from './themeManager.js';

document.addEventListener('alpine:init', () => {
    Alpine.data('app', () => ({
        ...createThemeComponent(),
        mobileMenuOpen: false,
        toggleMobileMenu() {
            this.mobileMenuOpen = !this.mobileMenuOpen;
        },
    }));
});
