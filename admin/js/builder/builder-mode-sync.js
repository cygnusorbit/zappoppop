/**
 * ZapPopPop Visual Builder: Day / Night mode <-> builder theme sync (phase 1)
 * ZAPPOPPOP_MODE_SYNC
 * - AUTO: a light builder theme = Day (body.theme-light), a dark one = Night. Re-evaluated on every load and on a theme change.
 * - Checker area (the workspace): in auto mode it is exactly the saved Builder Theme. Switching the toggle to the opposite mode
 *   swaps ONLY the workspace background + checker to the plugin's day / night pair, for this page session only.
 *   (phase 2 persists the choice and resets it when the Builder Theme is saved)
 * - Nothing is stored or saved: popup model, AJAX payload and frontend output are untouched.
 */
(function(window, $) {
    'use strict';
    if (window.__zppModeSync) { return; }
    window.__zppModeSync = true;

    var THEME_KEY = 'zappoppop_builder_theme_live';
    var WS_SEL = '.zappoppop-builder-workspace, #zappoppop-builder-workspace';
    var LOCK = 'data-zpp-mode-lock'; /* the theme script's setVarImportant() skips background props on a locked element */
    var SRC = ['--zpp-builder-header-bg', '--zpp-builder-content-bg', '--zpp-builder-stage-bg'];
    var PAIR = {
        light: { bg: '#f8fafc', checker: '#e2e8f0' },
        dark:  { bg: '#0f172a', checker: '#1e293b' }
    };
    var autoMode = null, settled = false, userSet = false, saved = null, orig = null, probe = null;

    function checkerImage(c) {
        return 'linear-gradient(45deg, ' + c + ' 25%, transparent 25%), linear-gradient(-45deg, ' + c + ' 25%, transparent 25%), ' +
               'linear-gradient(45deg, transparent 75%, ' + c + ' 75%), linear-gradient(-45deg, transparent 75%, ' + c + ' 75%)';
    }

    /* perceived brightness 0..1 of any CSS color (hex / rgb / rgba) */
    function brightness(val) {
        if (!val) { return null; }
        if (!probe) {
            probe = document.createElement('span');
            probe.style.cssText = 'position:absolute;visibility:hidden;pointer-events:none;';
            document.body.appendChild(probe);
        }
        probe.style.color = '';
        probe.style.color = val;
        if (!probe.style.color) { return null; }
        var m = (window.getComputedStyle(probe).color || '').match(/rgba?\(([^)]+)\)/);
        if (!m) { return null; }
        var p = m[1].split(',').map(parseFloat);
        return (0.299 * p[0] + 0.587 * p[1] + 0.114 * p[2]) / 255;
    }

    function detect() {
        var cs = window.getComputedStyle(document.documentElement), sum = 0, n = 0;
        SRC.forEach(function(v) {
            var b = brightness(String(cs.getPropertyValue(v) || '').replace(/^\s+|\s+$/g, ''));
            if (b !== null) { sum += b; n++; }
        });
        return n ? ((sum / n) >= 0.5 ? 'light' : 'dark') : null;
    }

    function ws() { return document.querySelector(WS_SEL); }

    function keep(el, prop) { return [el.style.getPropertyValue(prop), el.style.getPropertyPriority(prop)]; }
    function put(el, prop, pair) {
        if (pair[0]) { el.style.setProperty(prop, pair[0], pair[1]); } else { el.style.removeProperty(prop); }
    }

    function applyOverride(mode) {
        var el = ws();
        if (!el) { return; }
        if (!saved) { saved = { el: el, bc: keep(el, 'background-color'), bi: keep(el, 'background-image') }; }
        var pr = PAIR[mode];
        el.setAttribute(LOCK, '1');
        el.style.setProperty('background-color', pr.bg, 'important');
        el.style.setProperty('background-image', checkerImage(pr.checker), 'important');
    }

    function clearOverride() {
        if (!saved) { return; }
        saved.el.removeAttribute(LOCK);
        put(saved.el, 'background-color', saved.bc);
        put(saved.el, 'background-image', saved.bi);
        saved = null;
    }

    function sync() {
        var core = window.ZapPopPopBuilderCore;
        if (!core || !orig || userSet) { return; }
        clearOverride();
        var m = detect();
        if (!m) { return; }
        autoMode = m;
        settled = false;
        orig.call(core, m);
        settled = true;
    }

    function init() {
        var core = window.ZapPopPopBuilderCore;
        if (!core || typeof core.setThemeMode !== 'function' || core.__zppModeWrapped) { return false; }
        core.__zppModeWrapped = true;
        orig = core.setThemeMode;
        core.setThemeMode = function(theme) {
            var r = orig.apply(this, arguments);
            if (settled && autoMode) {
                var m = (theme === 'dark') ? 'dark' : 'light';
                userSet = true;
                if (m === autoMode) { clearOverride(); } else { applyOverride(m); }
            }
            return r;
        };
        return true;
    }

    window.ZapPopPopModeSync = {
        mode: function() { return autoMode; },
        resync: function() { userSet = false; sync(); }
    };

    $(function() {
        if (!init()) { return; }
        setTimeout(sync, 350);
    });
    $(window).on('load', function() { setTimeout(sync, 200); });

    /* the Builder Theme changed (saved in another tab): the vars repaint, then any override is dropped and the mode is re-detected */
    window.addEventListener('storage', function(e) {
        if (e.key !== THEME_KEY) { return; }
        setTimeout(function() { window.ZapPopPopModeSync.resync(); }, 80);
    });
})(window, jQuery);
