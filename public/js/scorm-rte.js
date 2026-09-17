/**
 * Auroara LMS — SCORM 1.2 Runtime Environment (RTE) API Shim
 *
 * This script is injected into the parent page and exposes the
 * window.API object that SCORM 1.2 content looks for by walking
 * up the frame hierarchy.
 *
 * Configuration: set window.SCORM_CONFIG before loading this script:
 *   { lessonId, csrfToken, baseUrl }
 */
(function () {
    'use strict';

    var config = window.SCORM_CONFIG || {};
    var lessonId = config.lessonId;
    var csrfToken = config.csrfToken;
    var baseUrl = (config.baseUrl || '').replace(/\/$/, '');

    // Internal CMI data model
    var cmi = {};
    var initialized = false;
    var finished = false;
    var lastError = '0';
    var dirty = false;

    // Error codes
    var ERRORS = {
        '0':   'No Error',
        '101': 'General Exception',
        '201': 'Invalid Argument Error',
        '301': 'Not Initialized',
        '401': 'Not Implemented Error',
        '402': 'Invalid Set Value',
        '403': 'Element Is Read Only',
    };

    // Read-only CMI elements
    var READ_ONLY = [
        'cmi.core.student_id', 'cmi.core.student_name',
        'cmi.core.credit', 'cmi.core.entry',
        'cmi.core.total_time', 'cmi.core.lesson_mode',
        'cmi.launch_data',
    ];

    function setNestedValue(obj, path, value) {
        var parts = path.split('.');
        for (var i = 0; i < parts.length - 1; i++) {
            if (obj[parts[i]] === undefined) obj[parts[i]] = {};
            obj = obj[parts[i]];
        }
        obj[parts[parts.length - 1]] = value;
    }

    function getNestedValue(obj, path) {
        var parts = path.split('.');
        for (var i = 0; i < parts.length; i++) {
            if (obj === undefined || obj === null) return '';
            obj = obj[parts[i]];
        }
        return (obj === undefined || obj === null) ? '' : String(obj);
    }

    function apiCall(endpoint, data) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', baseUrl + '/api/scorm/' + lessonId + '/' + endpoint, false); // synchronous
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        xhr.setRequestHeader('Accept', 'application/json');
        try {
            xhr.send(JSON.stringify(data || {}));
            if (xhr.status >= 200 && xhr.status < 300) {
                return JSON.parse(xhr.responseText);
            }
        } catch (e) {
            console.error('[SCORM RTE] API error:', e);
        }
        return null;
    }

    // ─── SCORM 1.2 API Object ───

    window.API = {
        LMSInitialize: function (param) {
            if (initialized) { lastError = '101'; return 'false'; }

            var result = apiCall('initialize');
            if (result && result.success) {
                cmi = result.cmi || {};
                initialized = true;
                finished = false;
                lastError = '0';
                return 'true';
            }
            lastError = '101';
            return 'false';
        },

        LMSGetValue: function (element) {
            if (!initialized) { lastError = '301'; return ''; }
            lastError = '0';

            // Strip the leading "cmi." to navigate our cmi object
            var path = element.replace(/^cmi\./, '');
            var val = getNestedValue(cmi, path);
            return val;
        },

        LMSSetValue: function (element, value) {
            if (!initialized) { lastError = '301'; return 'false'; }

            // Check read-only
            if (READ_ONLY.indexOf(element) !== -1) {
                lastError = '403';
                return 'false';
            }

            var path = element.replace(/^cmi\./, '');
            setNestedValue(cmi, path, value);
            dirty = true;
            lastError = '0';
            return 'true';
        },

        LMSCommit: function (param) {
            if (!initialized) { lastError = '301'; return 'false'; }
            if (!dirty) { lastError = '0'; return 'true'; }

            var result = apiCall('commit', { cmi: cmi });
            if (result && result.success) {
                dirty = false;
                lastError = '0';
                return 'true';
            }
            lastError = '101';
            return 'false';
        },

        LMSFinish: function (param) {
            if (!initialized) { lastError = '301'; return 'false'; }

            // Auto-commit if dirty
            if (dirty) {
                this.LMSCommit('');
            }

            var result = apiCall('finish', { cmi: cmi });
            initialized = false;
            finished = true;
            lastError = '0';
            return 'true';
        },

        LMSGetLastError: function () {
            return lastError;
        },

        LMSGetErrorString: function (errorCode) {
            return ERRORS[errorCode] || 'Unknown Error';
        },

        LMSGetDiagnostic: function (errorCode) {
            return ERRORS[errorCode] || '';
        }
    };

    // Auto-commit on page unload
    window.addEventListener('beforeunload', function () {
        if (initialized && !finished) {
            window.API.LMSFinish('');
        }
    });
})();
