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

// Career Path form component
Alpine.data('careerPathForm', (availableCourses = [], existingStages = []) => ({
    availableCourses: availableCourses,
    stages: existingStages.length
        ? existingStages.map(s => ({
            title: s.title || '',
            description: s.description || '',
            required_courses: (s.required_courses || []).map(Number),
            required_certifications: s.required_certifications || [],
            estimated_duration_days: s.estimated_duration_days || '',
        }))
        : [],
    addStage() {
        this.stages.push({
            title: '',
            description: '',
            required_courses: [],
            required_certifications: [],
            estimated_duration_days: '',
        });
    },
    removeStage(index) {
        this.stages.splice(index, 1);
    },
}));

Alpine.start();
