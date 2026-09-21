// Policy editor - Quill enhancement with textarea fallback
// This file is loaded as an external script (CSP-compliant, no inline scripts)
(function() {
    var container = document.getElementById('policy-editor-container');
    var textarea = document.getElementById('policy-content');
    if (!container || !textarea) return;

    if (typeof Quill === 'undefined') {
        // Quill didn't load - textarea is already visible as fallback
        return;
    }

    // Quill is available - hide textarea, show Quill editor
    container.style.display = 'block';
    textarea.style.display = 'none';

    var quill = new Quill(container, {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link'],
                ['clean']
            ]
        }
    });

    // Load existing content from the textarea into Quill
    var existing = textarea.value;
    if (existing && existing.trim()) {
        quill.root.innerHTML = existing;
    }

    // Sync Quill content back to the hidden textarea on every change
    quill.on('text-change', function() {
        textarea.value = quill.root.innerHTML;
    });

    // Also sync before form submission
    var form = textarea.closest('form');
    if (form) {
        form.addEventListener('submit', function() {
            textarea.value = quill.root.innerHTML;
        });
    }
})();
