import Alpine from 'alpinejs';
import 'bootstrap';
import serviceManager from './components/serviceManager';
import { initModuleToggles } from './admin/modulesToggle';
import { initSeoRegenerate } from './admin/seoSitemap';
import { initRegistrationsChart } from './admin/chart';

Alpine.data('serviceManager', serviceManager);

document.addEventListener('DOMContentLoaded', () => {
    initRegistrationsChart();
    initModuleToggles();
    initSeoRegenerate();
    Alpine.start();
});
