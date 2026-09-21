/**
 * Bulk Course Assignment — interactive helpers
 * External file for CSP compliance (no inline scripts).
 */
document.addEventListener('DOMContentLoaded', function () {
    var page = document.getElementById('bulk-assign-page');
    if (!page) return;

    // Cache initial state to show change count
    var initialState = {};
    document.querySelectorAll('.cell-checkbox').forEach(function (cb) {
        initialState[cb.dataset.courseId + '-' + cb.dataset.tenantId] = cb.checked;
    });

    function updateChangeCounter() {
        var count = 0;
        document.querySelectorAll('.cell-checkbox').forEach(function (cb) {
            var key = cb.dataset.courseId + '-' + cb.dataset.tenantId;
            if (cb.checked !== initialState[key]) count++;
        });
        var text = count > 0 ? count + ' change' + (count !== 1 ? 's' : '') + ' pending' : '';
        var el = document.getElementById('change-counter');
        var el2 = document.getElementById('change-counter-bottom');
        if (el) { el.textContent = text; el.classList.toggle('hidden', count === 0); }
        if (el2) { el2.textContent = text; }
    }

    // Listen for any checkbox change in the matrix
    var matrix = document.getElementById('assign-matrix');
    if (matrix) {
        matrix.addEventListener('change', function () { syncToggles(); updateChangeCounter(); });
    }

    // ── Row toggle (select all tenants for one course) ──
    document.querySelectorAll('.row-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            var courseId = this.dataset.courseId;
            var checked = this.checked;
            document.querySelectorAll('.cell-checkbox[data-course-id="' + courseId + '"]').forEach(function (cb) {
                cb.checked = checked;
            });
            updateChangeCounter();
            syncColToggles();
        });
    });

    // ── Column toggle (select all courses for one tenant) ──
    document.querySelectorAll('.col-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            var tenantId = this.dataset.tenantId;
            var checked = this.checked;
            document.querySelectorAll('.cell-checkbox[data-tenant-id="' + tenantId + '"]').forEach(function (cb) {
                cb.checked = checked;
            });
            updateChangeCounter();
            syncRowToggles();
        });
    });

    // ── Global select / deselect ──
    var selectAllBtn = document.getElementById('select-all-global');
    var deselectAllBtn = document.getElementById('deselect-all-global');
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function () {
            document.querySelectorAll('.cell-checkbox').forEach(function (cb) { cb.checked = true; });
            syncToggles(); updateChangeCounter();
        });
    }
    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function () {
            document.querySelectorAll('.cell-checkbox').forEach(function (cb) { cb.checked = false; });
            syncToggles(); updateChangeCounter();
        });
    }

    // ── Quick Assign by Category ──
    var qaAssign = document.getElementById('qa-assign-btn');
    var qaUnassign = document.getElementById('qa-unassign-btn');
    if (qaAssign) qaAssign.addEventListener('click', function () { quickAssign(true); });
    if (qaUnassign) qaUnassign.addEventListener('click', function () { quickAssign(false); });

    function quickAssign(checked) {
        var catVal = document.getElementById('qa-category').value;
        var tenantVal = document.getElementById('qa-tenant').value;
        if (!catVal || !tenantVal) {
            alert('Please select both a category and a tenant.');
            return;
        }

        // Find matching course rows
        var rows = document.querySelectorAll('#assign-matrix tbody tr');
        rows.forEach(function (row) {
            var match = false;
            if (catVal === '__mandatory__') {
                match = row.dataset.mandatory === '1';
            } else {
                match = row.dataset.category === catVal;
            }
            if (!match) return;

            var courseId = row.dataset.courseId;
            if (tenantVal === '__all__') {
                row.querySelectorAll('.cell-checkbox').forEach(function (cb) { cb.checked = checked; });
            } else {
                var cb = row.querySelector('.cell-checkbox[data-tenant-id="' + tenantVal + '"]');
                if (cb) cb.checked = checked;
            }
        });
        syncToggles();
        updateChangeCounter();
    }

    // ── Sync toggle states to match cell checkboxes ──
    function syncToggles() { syncRowToggles(); syncColToggles(); }

    function syncRowToggles() {
        document.querySelectorAll('.row-toggle').forEach(function (toggle) {
            var courseId = toggle.dataset.courseId;
            var cells = document.querySelectorAll('.cell-checkbox[data-course-id="' + courseId + '"]');
            var allChecked = cells.length > 0;
            cells.forEach(function (cb) { if (!cb.checked) allChecked = false; });
            toggle.checked = allChecked;
        });
    }

    function syncColToggles() {
        document.querySelectorAll('.col-toggle').forEach(function (toggle) {
            var tenantId = toggle.dataset.tenantId;
            var cells = document.querySelectorAll('.cell-checkbox[data-tenant-id="' + tenantId + '"]');
            var allChecked = cells.length > 0;
            cells.forEach(function (cb) { if (!cb.checked) allChecked = false; });
            toggle.checked = allChecked;
        });
    }

    // Initial sync
    syncToggles();
});
