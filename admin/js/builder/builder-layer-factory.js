/* ZPP_LAYER_FACTORY: built-in defaults for NEW layers, read from defaults/layer-defaults.json (localized as zappoppop_vars.layer_factory).
   Order for a new layer: JS built-in (addElement) -> this factory file -> site "Save as Default" (builder-layer-defaults.js, applied last).
   Tab Reset buttons read addElement with saved defaults suppressed, so Reset returns to this factory layer automatically.
   Only writes into the JS model: pre-save flush, AJAX whitelist and the frontend renderer are untouched. */
(function (window) {
    'use strict';

    var TABS = ['basic', 'style', 'logic', 'data', 'advanced'];
    /* identity, position, state and unique field ids are never part of a default (mirrors ZapPopPop_Saved_Defaults::layer_skip) */
    var SKIP = ['id', 'type', 'name', 'right', 'bottom', 'zIndex', 'locked', 'hidden', 'custom_settings', 'fieldId', 'fieldName', 'field_id', 'field_name']; /* ZPP_LD_COORDS: X / Y / Width / Height can now be defaults */
    var hasOwn = Object.prototype.hasOwnProperty;

    function clone(v) { return JSON.parse(JSON.stringify(v)); }

    function data() {
        var v = window.zappoppop_vars && window.zappoppop_vars.layer_factory;
        return (v && typeof v === 'object' && !Array.isArray(v)) ? v : null;
    }

    function get(type, tab) {
        var d = data(), t = String(type || '');
        if (!d || !hasOwn.call(d, t) || !d[t] || typeof d[t] !== 'object') { return null; }
        if (!tab) { return d[t]; }
        return (d[t][tab] && typeof d[t][tab] === 'object') ? d[t][tab] : null;
    }

    /* writes the factory props of el.type into el; returns how many props were set */
    function apply(el) {
        if (!el || typeof el !== 'object') { return 0; }
        var n = 0;
        TABS.forEach(function (tab) {
            var sv = get(el.type, tab);
            if (!sv) { return; }
            Object.keys(sv).forEach(function (k) {
                if (SKIP.indexOf(k) !== -1) { return; }
                try { el[k] = clone(sv[k]); n++; } catch (e) { /* skip non-serialisable */ }
            });
        });
        return n;
    }

    window.ZapPopPopLayerFactory = { get: get, apply: apply, has: function (type) { return !!get(type); } };
})(window);
