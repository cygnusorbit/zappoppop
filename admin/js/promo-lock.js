/**
 * ZapPopPop: static Pro teasers (Extend page cards and builder accordions). ZAPPOPPOP_PRO_LOCK
 * A click on an element with data-zpp-pro-lock opens the upgrade dialog (#zpp-pro-modal, text from defaults/promo.json).
 * ZAPPOPPOP_PRO_DISMISS: X and "Not now" snooze the dialog in this browser (same record as the Pro sections' X:
 * localStorage zappoppop_promo_dismissed_<id> = {t, d}); while snoozed a click does nothing or goes to the upgrade link.
 * Esc and the backdrop only close it. Nothing is sent anywhere. Tab stays inside. ES5 only (Chrome, Safari, Firefox, Edge).
 */
(function () {
    'use strict';

    var PREFIX = 'zappoppop_promo_dismissed_';
    var DAY = 86400000;
    var opener = null;

    function modal() { return document.getElementById('zpp-pro-modal'); }

    function storage() {
        try { return window.localStorage; } catch (e) { return null; }
    }

    function key(m) { return PREFIX + (m.getAttribute('data-zpp-pro-id') || ''); }

    /* true while a remembered close is still in force (expired records are removed) */
    function snoozed(m) {
        var ls = storage();
        if (!ls || m.getAttribute('data-zpp-pro-remember') === '0' || !m.getAttribute('data-zpp-pro-id')) { return false; }
        var raw = null;
        try { raw = ls.getItem(key(m)); } catch (e) { return false; }
        if (!raw) { return false; }
        var v = null;
        try { v = JSON.parse(raw); } catch (e2) { v = null; }
        if (!v || typeof v.t !== 'number') { return true; }
        if (v.d > 0 && Date.now() - v.t > v.d * DAY) {
            try { ls.removeItem(key(m)); } catch (e3) { /* ignore */ }
            return false;
        }
        return true;
    }

    function remember(m) {
        var ls = storage();
        if (!ls || m.getAttribute('data-zpp-pro-remember') === '0' || !m.getAttribute('data-zpp-pro-id')) { return; }
        var days = parseInt(m.getAttribute('data-zpp-pro-days'), 10) || 0;
        try { ls.setItem(key(m), JSON.stringify({ t: Date.now(), d: days })); } catch (e) { /* storage full or blocked */ }
    }

    function focusables(box) {
        var list = box.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])');
        var out = [];
        for (var i = 0; i < list.length; i++) {
            if (list[i].offsetWidth || list[i].offsetHeight) { out.push(list[i]); }
        }
        return out;
    }

    function open(from) {
        var m = modal();
        if (!m) { return; }
        if (snoozed(m)) {
            var url = m.getAttribute('data-zpp-pro-url');
            if (m.getAttribute('data-zpp-pro-after') === 'link' && url) {
                if (m.getAttribute('data-zpp-pro-newtab') === '1') {
                    var w = window.open(url, '_blank', 'noopener,noreferrer');
                    if (w) { w.opener = null; }
                } else {
                    window.location.href = url;
                }
            }
            return;
        }
        if (m.parentNode !== document.body) { document.body.appendChild(m); }
        var t = m.querySelector('[data-zpp-pro-title]');
        if (t) {
            t.textContent = (t.getAttribute('data-zpp-pro-title') || '').replace(/\{name\}/g, from.getAttribute('data-zpp-pro-name') || '');
        }
        opener = from;
        m.hidden = false;
        var box = m.querySelector('.zpp-pro-modal-box');
        if (box) { box.focus(); }
    }

    function close() {
        var m = modal();
        if (!m || m.hidden) { return; }
        m.hidden = true;
        if (opener && typeof opener.focus === 'function') { opener.focus(); }
        opener = null;
    }

    function up(el, attr) {
        while (el && el !== document) {
            if (el.getAttribute && el.getAttribute(attr) === '1') { return el; }
            el = el.parentNode;
        }
        return null;
    }

    document.addEventListener('click', function (e) {
        var c = up(e.target, 'data-zpp-pro-close');
        if (c) {
            e.preventDefault();
            var m = modal();
            if (m && up(e.target, 'data-zpp-pro-dismiss')) { remember(m); }
            close();
            return;
        }
        var l = up(e.target, 'data-zpp-pro-lock');
        if (l && e.target.closest && e.target.closest('a.zpp-pro-lock-btn')) { return; }
        if (l) { e.preventDefault(); open(l); }
    });

    document.addEventListener('keydown', function (e) {
        var m = modal();
        var isOpen = m && !m.hidden;
        var k = e.key || '';
        if (isOpen) {
            if (k === 'Escape' || k === 'Esc') {
                e.preventDefault();
                e.stopPropagation();
                close();
                return;
            }
            if (k === 'Tab') {
                var box = m.querySelector('.zpp-pro-modal-box');
                var f = box ? focusables(box) : [];
                if (!f.length) { e.preventDefault(); return; }
                var first = f[0];
                var last = f[f.length - 1];
                if (e.shiftKey && (document.activeElement === first || document.activeElement === box)) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
            return;
        }
        if (k === 'Enter' || k === ' ' || k === 'Spacebar') {
            var l = up(e.target, 'data-zpp-pro-lock');
            if (l && e.target === l) { e.preventDefault(); open(l); }
        }
    }, true);
})();
