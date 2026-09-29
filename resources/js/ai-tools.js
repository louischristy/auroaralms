/**
 * Alpine.js components for AI Tools pages.
 * Routes are passed via data-* attributes on the root element.
 * CSRF token is read from the <meta name="csrf-token"> tag.
 */

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

export function registerAiComponents(Alpine) {

    Alpine.data('aiCourseGenerator', () => ({
        form: { topic: '', difficulty: 'intermediate', target_audience: 'All employees', lesson_count: '4' },
        loading: false,
        error: '',
        result: null,
        async generate() {
            this.loading = true;
            this.error = '';
            this.result = null;
            try {
                const url = this.$root.dataset.generateUrl;
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
                    body: JSON.stringify(this.form)
                });
                const data = await res.json();
                if (data.success) { this.result = data.data; } else { this.error = data.error || 'Generation failed'; }
            } catch (e) { this.error = 'Network error. Please try again.'; }
            this.loading = false;
        }
    }));

    Alpine.data('aiQuizGenerator', () => ({
        form: { course_id: '', question_count: '10' },
        loading: false,
        error: '',
        result: null,
        async generate() {
            this.loading = true;
            this.error = '';
            this.result = null;
            try {
                const url = this.$root.dataset.generateUrl;
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
                    body: JSON.stringify(this.form)
                });
                const data = await res.json();
                if (data.success) { this.result = data.data; } else { this.error = data.error || 'Generation failed'; }
            } catch (e) { this.error = 'Network error. Please try again.'; }
            this.loading = false;
        }
    }));

    Alpine.data('aiPhishingGenerator', () => ({
        form: { scenario_type: 'credential_harvest', difficulty: 'medium', industry: 'general', count: '3' },
        loading: false,
        error: '',
        result: null,
        async generate() {
            this.loading = true;
            this.error = '';
            this.result = null;
            try {
                const url = this.$root.dataset.generateUrl;
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
                    body: JSON.stringify(this.form)
                });
                const data = await res.json();
                if (data.success) { this.result = data.data; } else { this.error = data.error || 'Generation failed'; }
            } catch (e) { this.error = 'Network error. Please try again.'; }
            this.loading = false;
        }
    }));
}
