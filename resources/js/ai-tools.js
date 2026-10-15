/**
 * Alpine.js components for AI Tools pages.
 * Routes are passed via data-* attributes on the root element.
 * CSRF token is read from the <meta name="csrf-token"> tag.
 */

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

export function registerAiComponents(Alpine) {

    Alpine.data('aiCourseGenerator', () => ({
        form: { topic: '', difficulty: 'intermediate', target_audience: 'All employees', lesson_count: '4' },
        lessonTypes: { text: 4, video: 0, ppt: 0 },
        sourceFile: null,
        dragOver: false,
        loading: false,
        error: '',
        result: null,

        get lessonTypeTotal() {
            return (this.lessonTypes.text || 0) + (this.lessonTypes.video || 0) + (this.lessonTypes.ppt || 0);
        },

        get contentSuggestions() {
            const topic = (this.form.topic || '').toLowerCase();
            const suggestions = [
                { label: 'Compliance walkthrough (text)', types: { text: 3, video: 1, ppt: 0 }, selected: false },
                { label: 'Hands-on workshop (video-heavy)', types: { text: 1, video: 2, ppt: 1 }, selected: false },
                { label: 'Executive briefing (PPT-heavy)', types: { text: 1, video: 0, ppt: 3 }, selected: false },
                { label: 'Balanced mix', types: { text: 2, video: 1, ppt: 1 }, selected: false },
                { label: 'All text lessons', types: { text: parseInt(this.form.lesson_count) || 4, video: 0, ppt: 0 }, selected: false },
            ];
            return suggestions;
        },

        applySuggestion(suggestion) {
            this.contentSuggestions.forEach(s => s.selected = false);
            suggestion.selected = true;
            const total = parseInt(this.form.lesson_count) || 4;
            const sTotal = suggestion.types.text + suggestion.types.video + suggestion.types.ppt;
            if (sTotal <= total) {
                this.lessonTypes.text = suggestion.types.text + (total - sTotal);
                this.lessonTypes.video = suggestion.types.video;
                this.lessonTypes.ppt = suggestion.types.ppt;
            } else {
                const ratio = total / sTotal;
                this.lessonTypes.text = Math.max(1, Math.round(suggestion.types.text * ratio));
                this.lessonTypes.video = Math.round(suggestion.types.video * ratio);
                this.lessonTypes.ppt = total - this.lessonTypes.text - this.lessonTypes.video;
            }
        },

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) this.sourceFile = file;
        },

        handleFileDrop(event) {
            this.dragOver = false;
            const file = event.dataTransfer.files[0];
            if (file) {
                const ext = file.name.split('.').pop().toLowerCase();
                if (['pdf', 'docx', 'txt'].includes(ext)) {
                    this.sourceFile = file;
                } else {
                    this.error = 'Only PDF, DOCX, and TXT files are accepted.';
                }
            }
        },

        async generate() {
            this.loading = true;
            this.error = '';
            this.result = null;
            try {
                const url = this.$root.dataset.generateUrl;
                const fd = new FormData();
                fd.append('topic', this.form.topic);
                fd.append('difficulty', this.form.difficulty);
                fd.append('target_audience', this.form.target_audience);
                fd.append('lesson_count', this.form.lesson_count);

                if (this.sourceFile) {
                    fd.append('source_file', this.sourceFile);
                }

                fd.append('lesson_type_counts[text]', this.lessonTypes.text || 0);
                fd.append('lesson_type_counts[video]', this.lessonTypes.video || 0);
                fd.append('lesson_type_counts[ppt]', this.lessonTypes.ppt || 0);

                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
                    body: fd
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
