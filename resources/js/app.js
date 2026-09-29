import Alpine from 'alpinejs';
import htmx from 'htmx.org';
import { registerAiComponents } from './ai-tools.js';

window.Alpine = Alpine;
window.htmx = htmx;

registerAiComponents(Alpine);

// Dark mode toggle component
Alpine.data('darkMode', () => ({
    dark: false,
    init() {
        try {
            this.dark = localStorage.getItem('darkMode') === 'true' ||
                (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches);
        } catch (e) {
            this.dark = false;
        }
        this.applyTheme();
    },
    toggle() {
        this.dark = !this.dark;
        try {
            localStorage.setItem('darkMode', this.dark);
        } catch (e) {
            // storage unavailable
        }
        this.applyTheme();
    },
    applyTheme() {
        document.documentElement.classList.toggle('dark', this.dark);
    }
}));

Alpine.start();
