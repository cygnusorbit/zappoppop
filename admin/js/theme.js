(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState !== 'loading') { fn(); } else { document.addEventListener('DOMContentLoaded', fn); }
    }
    function $all(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }
    function isHex6(v) { return /^#(?:[0-9a-f]{6}|[0-9a-f]{8})$/i.test(v); }

    ready(function () {
        var form = document.getElementById('zpp-theme-form');
        if (!form) { return; }
        var main = form.querySelector('.zpp-theme-main');

        /* ZPP_THEME_PICKER_TEXT: the colour picker wraps its model input in .zpp-cp, so look the saved text field up from the field row */
        function textOfPicker(picker) {
            var row = (picker.closest && picker.closest('.zpp-theme-input-row')) || picker.parentNode;
            return row ? row.querySelector('input[type="text"][data-zpp-var]') : null;
        }

        /* ---- Model: DOM inputs -> live CSS variables on <body> ---- */
        function applyVar(input) {
            var v = input.getAttribute('data-zpp-var');
            if (v) { document.body.style.setProperty(v, input.value); }
        }
        $all('[data-zpp-var]', form).forEach(function (input) {
            input.addEventListener('input', function () {
                applyVar(input);
                var picker = ((input.closest && input.closest('.zpp-theme-input-row')) || input.parentNode).querySelector('.zpp-theme-picker');
                if (picker && isHex6(input.value)) { picker.value = input.value; }
            });
            input.addEventListener('change', function () { applyVar(input); });
        });

        /* ---- Color pickers -> text field ---- */
        $all('.zpp-theme-picker', form).forEach(function (picker) {
            picker.addEventListener('input', function () {
                var text = textOfPicker(picker);
                if (!text) { return; }
                text.value = picker.value;
                applyVar(text);
            });
        });

        /* ---- Per-field reset ---- */
        $all('.zpp-theme-field-reset', form).forEach(function (btn) {
            btn.addEventListener('click', function () {
                var text = btn.parentNode.querySelector('input[type="text"][data-zpp-var]');
                text.value = text.getAttribute('data-default') || '';
                text.dispatchEvent(new Event('input', { bubbles: true }));
            });
        });

        /* ---- Tabs ---- */
        var tabs = $all('.zpp-theme-tab', form), panels = $all('.zpp-theme-panel', form);
        var TAB_PREFIX = 'tab-', TAB_STORE = 'zappoppop_theme_tab';

        function storeGet() { try { return window.sessionStorage.getItem(TAB_STORE) || ''; } catch (e) { return ''; } }
        function storeSet(v) { try { window.sessionStorage.setItem(TAB_STORE, v); } catch (e) { /* storage blocked */ } }
        function hasTab(id) { return tabs.some(function (t) { return t.getAttribute('data-tab') === id; }); }
        function hashTab() {
            var h = (window.location.hash || '').replace(/^#/, '');
            return h.indexOf(TAB_PREFIX) === 0 ? decodeURIComponent(h.slice(TAB_PREFIX.length)) : '';
        }
        function activateTab(id, writeHash) {
            if (!hasTab(id)) { return false; }
            tabs.forEach(function (t) {
                var on = t.getAttribute('data-tab') === id;
                t.classList.toggle('is-active', on);
                t.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            panels.forEach(function (p) { p.classList.toggle('is-active', p.getAttribute('data-panel') === id); });
            storeSet(id);
            if (writeHash) {
                var hash = '#' + TAB_PREFIX + encodeURIComponent(id);
                if (window.location.hash !== hash) {
                    if (window.history && window.history.replaceState) {
                        window.history.replaceState(null, '', window.location.pathname + window.location.search + hash);
                    } else {
                        window.location.hash = hash;
                    }
                }
            }
            return true;
        }
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () { activateTab(tab.getAttribute('data-tab'), true); });
        });
        window.addEventListener('hashchange', function () { activateTab(hashTab(), false); });
        /* Restore: URL anchor first, then last-used tab (survives the save redirect). */
        if (!activateTab(hashTab(), false)) {
            var remembered = storeGet();
            if (remembered) { activateTab(remembered, true); }
        }

        /* ---- Search ---- */
        var search = document.getElementById('zpp-theme-search');
        if (search) {
            search.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.keyCode === 13) { e.preventDefault(); }
            });
            search.addEventListener('input', function () {
                var q = search.value.toLowerCase().trim();
                main.classList.toggle('is-searching', q !== '');
                $all('.zpp-theme-field', form).forEach(function (f) {
                    var hit = q === '' || (f.getAttribute('data-label') || '').indexOf(q) !== -1;
                    f.classList.toggle('is-hidden', !hit);
                });
                panels.forEach(function (p) {
                    var any = p.querySelectorAll('.zpp-theme-field:not(.is-hidden)').length;
                    if (q !== '') { p.style.display = any ? 'block' : 'none'; } else { p.style.display = ''; }
                });
            });
        }

        /* ---- Revert unsaved ---- */
        var revert = document.getElementById('zpp-theme-reset-form');
        if (revert) { revert.addEventListener('click', function () { window.location.reload(); }); }

        /* ---- Export ---- */
        var exportBtn = document.getElementById('zpp-theme-export'), json = document.getElementById('zpp-theme-json');
        if (exportBtn && json) {
            exportBtn.addEventListener('click', function () {
                var out = {};
                $all('[name^="zpp_theme["]', form).forEach(function (el) {
                    var key = el.name.replace(/^zpp_theme\[|\]$/g, '');
                    out[key] = el.type === 'checkbox' ? (el.checked ? 1 : 0) : el.value;
                });
                json.value = JSON.stringify(out, null, 2);
            });
        }

        /* ---- Import / Reset buttons set the action; Pre-save flush ---- */
        $all('[data-action]', form).forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                if (btn.getAttribute('data-action') === 'reset' && !window.confirm(wp.i18n.__('Reset the whole theme to defaults?', 'zappoppop'))) {
                    e.preventDefault();
                    return;
                }
                document.getElementById('zpp-theme-action').value = btn.getAttribute('data-action');
            });
        });
        form.addEventListener('submit', function () {
            /* Keep the active tab anchor on the POST target so it survives the redirect. */
            var activeTab = form.querySelector('.zpp-theme-tab.is-active');
            if (activeTab) {
                form.setAttribute('action', form.getAttribute('action').replace(/#.*$/, '') + '#' + TAB_PREFIX + encodeURIComponent(activeTab.getAttribute('data-tab')));
            }
            var action = document.getElementById('zpp-theme-action').value;
            if (action === 'save') {
                $all('.zpp-theme-picker', form).forEach(function (picker) {
                    var text = textOfPicker(picker);
                    if (text && text.value === '' ) { text.value = picker.value; }
                });
            }
        });
    });
})();
