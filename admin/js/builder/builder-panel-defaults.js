/* ZPP_PANEL_DEFAULTS: element settings panel (Text, Email, Number, Phone ...). The Inspector only writes a stored value into a control when the layer has one,
   so a new layer inherited the previous layer's Max Length / Browser Autocomplete (e.g. a new Text or Email layer showed "Telephone (tel)") and the pre-save flush then saved
   that stale value into the new layer. After a selection, controls whose property the layer does not store are set back to their own default (the option marked
   selected, else the first option; or the input's default value). Props that have their own fallback in the Inspector are left alone. */
(function (window, $) {
    'use strict';
    if (!$) { return; }

    var HOST = 'zappoppop-element-specific-settings';
    var SKIP = ['content', 'optionsList', 'options', 'fieldName', 'min', 'max', 'step', 'placeholder', 'label', 'titleText', 'text', 'token', 'fieldId'];

    function core() { return window.ZapPopPopBuilderCore || null; }
    function cid(v) { return String(v || '').replace(/^zappoppop-el-/, '').replace(/^el-/, '').trim(); }
    function activeEl() {
        var c = core();
        if (!c || !c.state) { return null; }
        var id = cid(c.state.activeId);
        if (!id) { return null; }
        var cv = c.state.canvases || {}, k, i, list;
        for (k in cv) {
            if (!Object.prototype.hasOwnProperty.call(cv, k)) { continue; }
            list = cv[k] || [];
            for (i = 0; i < list.length; i++) { if (list[i] && cid(list[i].id) === id) { return list[i]; } }
        }
        return null;
    }
    function visiblePanel() {
        var host = document.getElementById(HOST), i;
        if (!host) { return null; }
        for (i = 0; i < host.children.length; i++) {
            var c = host.children[i];
            if (c.classList.contains('element-panel') && window.getComputedStyle(c).display !== 'none') { return c; }
        }
        return null;
    }
    function defaultOf(n) {
        if (n.tagName === 'SELECT') {
            var o = null, i;
            for (i = 0; i < n.options.length; i++) { if (n.options[i].defaultSelected) { o = n.options[i]; break; } }
            if (!o) { o = n.options[0]; }
            return o ? o.value : '';
        }
        return n.defaultValue || '';
    }

    function reset() {
        var el = activeEl(), p = visiblePanel();
        if (!el || !p) { return; }
        Array.prototype.forEach.call(p.querySelectorAll('.zappoppop-gp-bind[data-prop]'), function (n) {
            var t = (n.type || '').toLowerCase();
            if (t === 'checkbox' || t === 'radio' || t === 'file' || t === 'button') { return; }
            var k = n.getAttribute('data-prop');
            if (!k || SKIP.indexOf(k) !== -1) { return; }
            if (el[k] !== undefined && el[k] !== null) { return; }
            if (n === document.activeElement) { return; }
            var d = defaultOf(n);
            if (n.value !== d) { n.value = d; }
        });
    }

    function burst() { [80, 250, 600, 1100].forEach(function (ms) { window.setTimeout(reset, ms); }); }

    $(function () {
        $(document).on('click.zppPanelDef', '.zappoppop-canvas-item, .zappoppop-layer-item, .zappoppop-ribbon-bar button, #zappoppop-inspector-drawer [data-tab]', burst);
        var n = 0, iv = window.setInterval(function () { reset(); if (++n > 10) { window.clearInterval(iv); } }, 500);
    });
})(window, window.jQuery);
