(function () {
    if (window.__packstubFormBuilder) return;
    window.__packstubFormBuilder = true;

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    function fieldEl(form, key) { return form.querySelector('[data-fb-field="' + key + '"]'); }
    function inputs(el) { return el ? el.querySelectorAll('input, select, textarea') : []; }

    function setError(form, key, message) {
        var el = form.querySelector('[data-fb-error-for="' + key + '"]');
        var field = fieldEl(form, key);
        if (el) { el.textContent = message || ''; el.hidden = !message; }
        if (field) {
            field.classList.toggle('fb-field--error', !!message);
            inputs(field).forEach(function (input) {
                if (message) input.setAttribute('aria-invalid', 'true'); else input.removeAttribute('aria-invalid');
            });
        }
    }

    function clearErrors(form) {
        form.querySelectorAll('[data-fb-error-for]').forEach(function (el) { setError(form, el.getAttribute('data-fb-error-for'), ''); });
        var alert = form.querySelector('[data-fb-alert]');
        if (alert) alert.hidden = true;
    }

    function showAlert(form, message) {
        var alert = form.querySelector('[data-fb-alert]');
        if (!alert) return;
        alert.textContent = message;
        alert.hidden = false;
        alert.setAttribute('tabindex', '-1');
        alert.focus();
    }

    function showErrors(form, errors, fallback) {
        var first = null;
        Object.keys(errors).forEach(function (key) {
            if (key === 'form') return;
            var base = key.split('.')[0];
            var message = Array.isArray(errors[key]) ? errors[key][0] : errors[key];
            setError(form, base, message);
            if (!first) first = form.querySelector('[data-fb-field="' + base + '"] input, [data-fb-field="' + base + '"] select, [data-fb-field="' + base + '"] textarea');
        });
        showAlert(form, (errors.form && errors.form[0]) || fallback || form.getAttribute('data-fb-message-invalid') || 'Please check the form.');
        if (first) first.focus();
    }

    function headers(form) {
        var h = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
        var token = form.querySelector('input[name="_token"]');
        if (token) h['X-CSRF-TOKEN'] = token.value;
        return h;
    }

    // ------------------------------------------------------------------
    // Values and conditions (mirrors Packstub\FormBuilder\Fields\Conditions)
    // ------------------------------------------------------------------

    function valueOf(form, key) {
        var field = fieldEl(form, key);
        if (!field) return null;
        var list = Array.prototype.slice.call(inputs(field));
        if (!list.length) return null;
        var type = field.getAttribute('data-fb-type') || '';
        var first = list[0];

        if (first.type === 'checkbox' && list.length === 1 && first.value === '1') return first.checked;
        if (first.type === 'checkbox') return list.filter(function (i) { return i.checked; }).map(function (i) { return i.value; });
        if (first.type === 'radio') { var checked = list.filter(function (i) { return i.checked; })[0]; return checked ? checked.value : null; }
        if (first.tagName === 'SELECT' && first.multiple) return Array.prototype.slice.call(first.selectedOptions).map(function (o) { return o.value; });
        if (first.type === 'file') return first.files && first.files.length ? Array.prototype.slice.call(first.files).map(function (f) { return f.name; }) : [];
        if (type === 'tags') return first.value.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
        return first.value === '' ? null : first.value;
    }

    function isEmpty(v) { return v === null || v === undefined || v === '' || v === false || (Array.isArray(v) && v.length === 0); }
    function eq(a, b) {
        if (Array.isArray(a)) { if (a.length === 1) a = a[0]; else return false; }
        if (typeof a === 'boolean') return a === (String(b).toLowerCase() === 'true' || b === '1' || b === 1 || String(b).toLowerCase() === 'yes' || String(b).toLowerCase() === 'on');
        if (a !== null && b !== null && a !== '' && b !== '' && !isNaN(a) && !isNaN(b)) return parseFloat(a) === parseFloat(b);
        return String(a === null ? '' : a).trim().toLowerCase() === String(b === null ? '' : b).trim().toLowerCase();
    }
    function contains(a, b) {
        if (Array.isArray(a)) return a.some(function (item) { return eq(item, b); });
        if (a === null || typeof a === 'boolean' || b === null || b === '') return false;
        return String(a).toLowerCase().indexOf(String(b).toLowerCase()) !== -1;
    }
    function compare(op, actual, expected) {
        switch (op) {
            case 'is_empty': return isEmpty(actual);
            case 'is_not_empty': return !isEmpty(actual);
            case 'equals': return eq(actual, expected);
            case 'not_equals': return !eq(actual, expected);
            case 'contains': return contains(actual, expected);
            case 'not_contains': return !contains(actual, expected);
            case 'greater_than': return !isNaN(actual) && !isNaN(expected) && actual !== null && parseFloat(actual) > parseFloat(expected);
            case 'less_than': return !isNaN(actual) && !isNaN(expected) && actual !== null && parseFloat(actual) < parseFloat(expected);
        }
        return false;
    }
    function matches(group, values) {
        if (!group.rules || !group.rules.length) return true;
        var all = group.logic !== 'any';
        for (var i = 0; i < group.rules.length; i++) {
            var r = group.rules[i];
            var ok = compare(r.operator, values[r.field], r.value);
            if (!all && ok) return true;
            if (all && !ok) return false;
        }
        return all;
    }
    function passes(group, values) {
        if (!group) return true;
        if (group.mode === 'when') return matches(group, values);
        if (group.mode === 'unless') return !matches(group, values);
        return true;
    }

    function applyLogic(form) {
        var logic = form.__fbLogic;
        if (!logic) return;
        var keys = Object.keys(logic.fields);
        var values = {};
        keys.forEach(function (key) { values[key] = valueOf(form, key); });

        var visible = {};
        keys.forEach(function (key) { visible[key] = !logic.fields[key].visibility; });
        var changed = true, guard = 0;
        while (changed && guard++ < 20) {
            changed = false;
            keys.forEach(function (key) {
                var group = logic.fields[key].visibility;
                if (!group) return;
                var deps = group.rules.map(function (r) { return r.field; });
                var depsVisible = deps.every(function (d) { return !(d in visible) || visible[d]; });
                var next = depsVisible && passes(group, values);
                if (next !== visible[key]) { visible[key] = next; changed = true; }
            });
        }
        (logic.sections || []).forEach(function (section) {
            var on = passes(section.visibility, values);
            var el = form.querySelector('[data-fb-section="' + section.key + '"]');
            if (el) el.hidden = !on || section.fields.length && section.fields.every(function (k) { return !visible[k]; }) && !el.querySelector('.fb-field--heading, .fb-field--paragraph');
            if (!on) section.fields.forEach(function (k) { visible[k] = false; });
        });

        keys.forEach(function (key) {
            var el = fieldEl(form, key);
            if (!el) return;
            el.hidden = !visible[key];
            var def = logic.fields[key];
            var required = def.required && visible[key] && passes(def.requirement, values);
            inputs(el).forEach(function (input) {
                input.disabled = !visible[key];
                if (def.required) {
                    if (required) input.setAttribute('aria-required', 'true'); else input.removeAttribute('aria-required');
                }
            });
            var star = el.querySelector('.fb-required');
            if (star && def.requirement) star.hidden = !required;
        });
        form.__fbVisible = visible;
    }

    // ------------------------------------------------------------------
    // Steps
    // ------------------------------------------------------------------

    function stepEls(form) { return Array.prototype.slice.call(form.querySelectorAll('[data-fb-step]')).filter(function (s) { return !s.hidden; }); }

    function showStep(form, index) {
        var steps = stepEls(form);
        if (!steps.length) return;
        index = Math.max(0, Math.min(index, steps.length - 1));
        form.__fbStep = index;
        Array.prototype.forEach.call(form.querySelectorAll('[data-fb-step]'), function (s) { s.style.display = 'none'; });
        steps[index].style.display = '';
        var last = index === steps.length - 1;
        var prev = form.querySelector('[data-fb-previous]'), next = form.querySelector('[data-fb-next]'), submit = form.querySelector('[data-fb-submit]'), captcha = form.querySelector('[data-fb-field="captcha"]');
        if (prev) prev.hidden = index === 0 || !(form.__fbLogic.wizard && form.__fbLogic.wizard.navigation);
        if (next) next.hidden = last;
        if (submit) submit.hidden = !last;
        if (captcha) captcha.style.display = last ? '' : 'none';
        var progress = form.querySelector('[data-fb-progress]');
        if (progress) {
            progress.hidden = false;
            var value = progress.querySelector('[data-fb-progress-value]');
            if (value) value.style.width = Math.round(((index + 1) / steps.length) * 100) + '%';
            var label = progress.querySelector('[data-fb-progress-label]');
            if (label) label.textContent = (form.getAttribute('data-fb-step-of') || 'Step :current of :total').replace(':current', index + 1).replace(':total', steps.length);
        }
        var focusable = steps[index].querySelector('input:not([type=hidden]):not([disabled]), select:not([disabled]), textarea:not([disabled])');
        if (focusable && form.__fbStepStarted) focusable.focus();
        form.__fbStepStarted = true;
    }

    function stepKeys(form) {
        var step = stepEls(form)[form.__fbStep || 0];
        if (!step) return [];
        return Array.prototype.slice.call(step.querySelectorAll('[data-fb-field]')).filter(function (f) { return !f.hidden; }).map(function (f) { return f.getAttribute('data-fb-field'); });
    }

    function validateStep(form) {
        clearErrors(form);
        var body = new FormData(form);
        stepKeys(form).forEach(function (key) { body.append('_fb_fields[]', key); });
        var next = form.querySelector('[data-fb-next]');
        if (next) next.disabled = true;
        return fetch(form.__fbLogic.validate, { method: 'POST', headers: headers(form), body: body, credentials: 'same-origin' })
            .then(function (response) { return response.json().catch(function () { return {}; }).then(function (json) { return { status: response.status, body: json }; }); })
            .then(function (result) {
                if (result.status >= 200 && result.status < 300) return true;
                showErrors(form, (result.body && result.body.errors) || {}, result.body && result.body.message);
                return false;
            })
            .catch(function () { showAlert(form, form.getAttribute('data-fb-message-failed') || 'Something went wrong. Please try again.'); return false; })
            .finally(function () { if (next) next.disabled = false; });
    }

    function initSteps(form) {
        var logic = form.__fbLogic;
        if (!logic || !logic.wizard) return;
        var prev = form.querySelector('[data-fb-previous]'), next = form.querySelector('[data-fb-next]');
        if (next) next.addEventListener('click', function () { validateStep(form).then(function (ok) { if (ok) showStep(form, (form.__fbStep || 0) + 1); }); });
        if (prev) prev.addEventListener('click', function () { clearErrors(form); showStep(form, (form.__fbStep || 0) - 1); });
        form.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' || event.target.tagName === 'TEXTAREA') return;
            var steps = stepEls(form);
            if ((form.__fbStep || 0) < steps.length - 1) { event.preventDefault(); if (next) next.click(); }
        });
        showStep(form, 0);
    }

    // ------------------------------------------------------------------
    // Captcha (reCAPTCHA v3 needs a token per submit)
    // ------------------------------------------------------------------

    function captchaToken(form) {
        var el = form.querySelector('[data-fb-recaptcha]');
        if (!el || !window.grecaptcha) return Promise.resolve();
        return new Promise(function (resolve) {
            window.grecaptcha.ready(function () {
                window.grecaptcha.execute(el.getAttribute('data-fb-recaptcha'), { action: 'submit' }).then(function (token) { el.value = token; resolve(); }, function () { resolve(); });
            });
        });
    }

    function resetCaptcha(form) {
        if (window.turnstile && form.querySelector('.cf-turnstile')) { try { window.turnstile.reset(); } catch (e) {} }
        if (window.hcaptcha && form.querySelector('.h-captcha')) { try { window.hcaptcha.reset(); } catch (e) {} }
    }

    // ------------------------------------------------------------------
    // Submit
    // ------------------------------------------------------------------

    function handle(form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            clearErrors(form);

            var button = form.querySelector('[data-fb-submit]');
            if (button) button.disabled = true;

            captchaToken(form).then(function () {
                return fetch(form.action, { method: 'POST', headers: headers(form), body: new FormData(form), credentials: 'same-origin' });
            })
                .then(function (response) {
                    return response.json().catch(function () { return {}; }).then(function (body) {
                        return { status: response.status, body: body };
                    });
                })
                .then(function (result) {
                    var body = result.body || {};
                    if (result.status >= 200 && result.status < 300 && body.ok) {
                        if (body.redirect) { window.location.assign(body.redirect); return; }
                        var wrapper = form.closest('[data-fb-form]');
                        var success = document.createElement('div');
                        success.className = 'fb-success';
                        success.setAttribute('role', 'status');
                        success.setAttribute('data-fb-success', '');
                        success.textContent = body.message || '';
                        form.replaceWith(success);
                        if (wrapper) wrapper.dispatchEvent(new CustomEvent('form-builder:submitted', { bubbles: true, detail: body }));
                        return;
                    }
                    var errors = body.errors || {};
                    if (form.__fbLogic && form.__fbLogic.wizard) {
                        var steps = stepEls(form);
                        var firstKey = Object.keys(errors).filter(function (k) { return k !== 'form'; })[0];
                        if (firstKey) {
                            var el = fieldEl(form, firstKey.split('.')[0]);
                            var idx = steps.findIndex(function (s) { return el && s.contains(el); });
                            if (idx >= 0) showStep(form, idx);
                        }
                    }
                    showErrors(form, errors, body.message);
                    resetCaptcha(form);
                })
                .catch(function () {
                    showAlert(form, form.getAttribute('data-fb-message-failed') || 'Something went wrong. Please try again.');
                })
                .finally(function () { if (button) button.disabled = false; });
        });
    }

    function initLogic(form) {
        var script = form.querySelector('[data-fb-logic]');
        if (!script) return;
        try { form.__fbLogic = JSON.parse(script.textContent); } catch (e) { return; }
        Object.keys(form.__fbLogic.fields).forEach(function (key) {
            var el = fieldEl(form, key);
            if (el) el.setAttribute('data-fb-type', form.__fbLogic.fields[key].type);
        });
        form.addEventListener('input', function () { applyLogic(form); });
        form.addEventListener('change', function () { applyLogic(form); });
        applyLogic(form);
    }

    function init(root) {
        (root || document).querySelectorAll('form[data-fb-enhance="true"]').forEach(function (form) {
            if (form.__fb) return;
            form.__fb = true;
            initLogic(form);
            initSteps(form);
            handle(form);
        });
        (root || document).querySelectorAll('form[data-fb-enhance="false"]').forEach(function (form) {
            if (form.__fb) return;
            form.__fb = true;
            initLogic(form);
        });
    }

    window.PackstubFormBuilder = { init: init, applyLogic: applyLogic };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { init(); }); else init();
    document.addEventListener('form-builder:init', function (event) { init(event.target); });
})();
