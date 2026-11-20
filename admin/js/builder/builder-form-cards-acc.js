/* ZPP_FORM_CARDS_ACC: Style tab > Input Styling module. "Text Alignment" and "Input Icons & Offsets" become accordion blocks
   (same framed header bar, chevron and remembered open / closed state as the other Style blocks). Visual only: no inputs are moved or re-created,
   so the Inspector DOM -> model -> flush -> AJAX -> renderer lifecycle is untouched. Prefix zpp-fc-* (no overlap with zpp-tb-* / zpp-ca-* / zpp-lk). */
(function (window, $) {
    'use strict';
    if (!$) { return; }

    var LS = 'zppTabAcc', MOD = '#zappoppop-form-style-module', HEAD = 'zpp-fc-head', CLOSED = 'zpp-fc-closed', P = 'html body #zappoppop-inspector-drawer.zappoppop-inspector-drawer ' + MOD + ' > .zpp-fc';

    var CSS = [
        P + '{background:var(--zpp-builder-area-bg,var(--zpp-builder-accordion-bg)) !important;background-color:var(--zpp-builder-area-bg,var(--zpp-builder-accordion-bg)) !important;border:1px solid var(--zpp-builder-area-border,var(--zpp-builder-accordion-border)) !important;border-radius:8px !important;padding:0 12px 12px !important;overflow:hidden;box-sizing:border-box;margin-bottom:12px !important}',
        P + ' > .zpp-fc-head{display:block !important;margin:0 -12px 12px !important;padding:10px 24px 10px 12px !important;border:0 !important;border-bottom:1px solid var(--zpp-builder-accordion-border) !important;border-radius:0 !important;background:var(--zpp-builder-accordion-bg) !important;background-color:var(--zpp-builder-accordion-bg) !important;box-shadow:none !important;height:auto !important;font-size:10px !important;font-weight:800 !important;letter-spacing:.5px !important;line-height:1.3 !important;text-transform:uppercase !important;color:var(--zpp-builder-accordion-text) !important;position:relative;cursor:pointer;-webkit-user-select:none;user-select:none}',
        P + ' > .zpp-fc-head::after{content:"";position:absolute;right:9px;top:50%;width:6px;height:6px;margin-top:-5px;opacity:1;border-right:2px solid currentColor;border-bottom:2px solid currentColor;-webkit-transform:rotate(45deg);transform:rotate(45deg);transition:transform .15s ease}',
        P + '.zpp-fc-closed{padding-bottom:0 !important}',
        P + '.zpp-fc-closed > .zpp-fc-head{margin-bottom:0 !important;border-bottom-color:transparent !important}',
        P + '.zpp-fc-closed > .zpp-fc-head::after{-webkit-transform:rotate(-45deg);transform:rotate(-45deg);margin-top:-3px}',
        P + '.zpp-fc-closed > *:not(.zpp-fc-head){display:none !important}',
        /* Setting Area: labels use the area text colour, rows inside show the block's own area colour (no per-row boxes) */
        P + ' label:not(.zpp-fc-head):not(.zpp-sw):not(.zappoppop-insp-switch):not(.zappoppop-gp-switch),' + P + ' .zappoppop-shape-label{color:var(--zpp-builder-area-text,var(--zpp-builder-text-color)) !important}',
        P + ' [data-zpp-role="content-panel"]{background:transparent !important;background-color:transparent !important;box-shadow:none !important}',
        P + ' > .zpp-fc-head:focus-visible{outline:2px solid var(--zpp-builder-accordion-text);outline-offset:-2px}'
    ].join('\n');

    function readState() { try { return JSON.parse(window.localStorage.getItem(LS) || '{}') || {}; } catch (e) { return {}; } }
    function writeState(o) { try { window.localStorage.setItem(LS, JSON.stringify(o)); } catch (e) { /* per page view only */ } }

    function ensureCss() {
        if (document.getElementById('zpp-fc-css')) { return; }
        var s = document.createElement('style');
        s.id = 'zpp-fc-css';
        s.appendChild(document.createTextNode(CSS));
        (document.head || document.documentElement).appendChild(s);
    }

    function setup() {
        var m = document.querySelector(MOD);
        if (!m) { return; }
        var st = readState();
        Array.prototype.forEach.call(m.children, function (card) {
            if (!card.classList.contains('zappoppop-insp-card')) { return; }
            var head = card.firstElementChild;
            if (!head || head.classList.contains(HEAD)) { return; }
            var id = 'fc:' + (card.id || String(head.textContent || '').trim().toLowerCase().replace(/[^a-z0-9]+/g, '-'));
            card.classList.add('zpp-fc');
            card.setAttribute('data-zpp-fc', id);
            head.classList.add(HEAD);
            head.setAttribute('role', 'button');
            head.setAttribute('tabindex', '0');
            head.setAttribute('aria-expanded', st[id] ? 'false' : 'true');
            card.classList.toggle(CLOSED, !!st[id]);
        });
    }

    function toggle(head) {
        var card = head.parentNode, closed = !card.classList.contains(CLOSED), st = readState(), id = card.getAttribute('data-zpp-fc');
        card.classList.toggle(CLOSED, closed);
        head.setAttribute('aria-expanded', closed ? 'false' : 'true');
        if (closed) { st[id] = 1; } else { delete st[id]; }
        writeState(st);
    }

    document.addEventListener('click', function (e) {
        var h = e.target && e.target.closest ? e.target.closest('.zpp-fc-head') : null;
        if (h && h.parentNode && h.parentNode.classList.contains('zpp-fc')) { toggle(h); }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' && e.key !== ' ' && e.key !== 'Spacebar') { return; }
        var t = e.target;
        if (t && t.classList && t.classList.contains(HEAD)) { e.preventDefault(); toggle(t); }
    });

    $(function () {
        ensureCss(); setup();
        var d = document.getElementById('zappoppop-inspector-drawer'), t = null;
        if (d && window.MutationObserver) {
            new MutationObserver(function () { window.clearTimeout(t); t = window.setTimeout(setup, 60); }).observe(d, { childList: true, subtree: true });
        }
        $(document).on('click.zppFc', '.zappoppop-canvas-item, .zappoppop-layer-item, #zappoppop-inspector-drawer [data-tab]', function () {
            [60, 300, 900].forEach(function (ms) { window.setTimeout(setup, ms); });
        });
        var n = 0, iv = window.setInterval(function () { setup(); if (++n > 20) { window.clearInterval(iv); } }, 500);
    });
})(window, window.jQuery);
