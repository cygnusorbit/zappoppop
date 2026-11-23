/* ZPP_FIELD_ID: small "Field ID" line under Layer Name for form elements, once the popup is saved. */
(function (window, $) {
    'use strict';
    if (!$) { return; }
    var NODE_ID = 'zpp-field-id';

    function reg() { return window.ZapPopPopElementsRegistry || {}; }
    function saved() {
        var uid = '';
        try { uid = new URLSearchParams(window.location.search).get('uid') || ''; } catch (e) { uid = ''; }
        return !!(uid || $('#zappoppop-builder-uid').val() || (window.zappoppop_vars && window.zappoppop_vars.current_uid));
    }
    function isForm(el) {
        var R = reg(), t = String(el.type || '').toLowerCase(), c = (R.ALIAS_MAP && R.ALIAS_MAP[t]) ? R.ALIAS_MAP[t] : t;
        return !!(R.FORM_FAMILY && R.FORM_FAMILY.indexOf(c) !== -1);
    }
    function node() {
        var n = document.getElementById(NODE_ID);
        if (n) { return n; }
        var inp = document.getElementById('prop-layer-name');
        if (!inp || !inp.parentNode) { return null; }
        n = document.createElement('div');
        n.id = NODE_ID;
        n.setAttribute('style', 'display:none;margin-top:4px;font-size:10px;line-height:1.3;color:var(--zpp-builder-muted-text);word-break:break-all;');
        inp.parentNode.insertBefore(n, inp.nextSibling);
        return n;
    }
    function update() {
        var n = node();
        if (!n) { return; }
        var R = reg(), el = R.resolveElement ? R.resolveElement() : null, tok = '';
        if (el && saved() && isForm(el)) {
            tok = String(el.fieldName || el.fieldId || el.token || '');
            if (!tok && typeof R.ensureElementToken === 'function') { R.ensureElementToken(el); tok = String(el.fieldName || el.fieldId || el.token || ''); }
        }
        if (!tok) { n.style.display = 'none'; n.textContent = ''; return; }
        /* translators: %s: field identifier token */
        var txt = wp.i18n.sprintf(wp.i18n.__('Field ID: %s', 'zappoppop'), tok);
        if (n.textContent !== txt) { n.textContent = txt; }
        n.title = 'Field identifier used in submissions and merge tokens';
        n.style.display = 'block';
    }
    function later() { [60, 300, 900].forEach(function (ms) { window.setTimeout(update, ms); }); }

    $(document).on('click.zppFieldId', '.zappoppop-canvas-item, .zappoppop-layer-item, #zappoppop-inspector-drawer [data-tab]', later);
    $(document).on('input.zppFieldId change.zppFieldId', '#prop-layer-name, #prop-field-name', later);
    $(document).on('ajaxComplete.zppFieldId', later);
    $(function () { update(); window.setInterval(update, 1500); });
})(window, window.jQuery);
