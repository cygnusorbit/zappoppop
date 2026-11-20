/* ZPP_FIELD_LABEL: every form element shows its Layer Name as a small label above the field.
   Model keys: showFieldLabel (undefined = on), fieldLabelColor. Lifecycle: Inspector DOM -> model -> history -> AJAX whitelist -> front renderer. */
(function (window, $) {
    'use strict';
    if (!$) { return; }
    var BOX = 'zpp-fl-box', DEF_COLOR = '#334155';
    var FORM = ['text', 'textfield', 'email', 'number', 'phone', 'tel', 'textarea', 'select', 'dropdown', 'multiselect', 'radio', 'radios', 'checkbox', 'checkboxes', 'checkboxgroup', 'date', 'time', 'upload', 'file', 'password', 'imageselect', 'tile', 'rating'];

    function core() { return window.ZapPopPopBuilderCore || null; }
    function reg() { return window.ZapPopPopElementsRegistry || {}; }
    function cid(v) { return String(v || '').replace(/^zappoppop-el-/, '').replace(/^el-/, '').trim(); }
    function isForm(el) { return !!el && FORM.indexOf(String(el.type || '').toLowerCase()) !== -1; }
    function shown(el) { return !(el.showFieldLabel === false || el.showFieldLabel === 'false' || el.showFieldLabel === 0 || el.showFieldLabel === '0'); }
    function colorOf(el) { return /^#[0-9a-f]{3,8}$/i.test(String(el.fieldLabelColor || '')) ? el.fieldLabelColor : DEF_COLOR; }
    function textColorOf(el) { return /^#[0-9a-f]{3,8}$/i.test(String(el.fieldLabelTextColor || '')) ? el.fieldLabelTextColor : DEF_COLOR; }
    function activeEl() { var R = reg(); return R.resolveElement ? R.resolveElement() : null; }
    function allEls() {
        var c = core(), out = [];
        if (c && c.state && c.state.canvases) {
            var list = c.state.canvases[c.state.currentCanvas || 1];
            if ($.isArray(list)) { out = list; }
        }
        return out;
    }

    /* ---- inspector controls: switch row under Layer Name, colour pick next to the "Layer Name" caption ---- */
    var CSS = "html body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw {\n    display: flex !important; align-items: center !important; gap: 10px !important; margin: 0 !important; position: relative; cursor: pointer;\n    font-size: 12px !important; line-height: 20px; font-weight: 500 !important; letter-spacing: 0 !important; color: var(--zpp-builder-text-color) !important;\n}\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw input[type=checkbox] {\n    position: absolute !important; opacity: 0 !important; width: 1px !important; height: 1px !important; min-width: 0 !important; margin: 0 !important;\n    pointer-events: none !important; -webkit-appearance: none; appearance: none;\n}\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw input[type=checkbox]::before { display: none !important; content: none !important; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw .zpp-sw-track {\n    display: block !important; position: relative; flex: 0 0 36px; width: 36px; height: 20px; border-radius: 20px; box-sizing: border-box;\n    background: var(--zpp-builder-toggle-off-bg); transition: background .15s ease;\n}\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw .zpp-sw-track::after {\n    content: \"\"; position: absolute; top: 2px; left: 2px; width: 16px; height: 16px; border-radius: 50%; background: #fff;\n    box-shadow: 0 1px 2px rgba(0,0,0,.4); transition: transform .15s ease;\n}\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw input[type=checkbox]:checked + .zpp-sw-track { background: var(--zpp-builder-accent-color); }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw input[type=checkbox]:checked + .zpp-sw-track::after { transform: translateX(16px); }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw input[type=checkbox]:focus-visible + .zpp-sw-track { box-shadow: 0 0 0 2px var(--zpp-builder-accent-color); }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw > span:last-child { flex: 1 1 auto; display: block; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw code { font-family: inherit !important; font-size: inherit !important; font-weight: 700 !important; background: transparent !important; padding: 0 !important; color: var(--zpp-builder-accent-color) !important; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl { margin-top: 10px; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl .zpp-fl-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 10px; font-size: 12px; font-weight: 500; line-height: 20px; color: var(--zpp-builder-text-color); }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer #prop-fl-text-color { height: 20px !important; min-height: 20px !important; width: 34px !important; padding: 1px !important; margin: 0 !important; cursor: pointer; border-radius: 4px !important; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw { flex-direction: row-reverse !important; justify-content: space-between !important; width: 100%; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw > span:last-child { flex: 1 1 auto; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer #prop-fl-color { height: 20px !important; min-height: 20px !important; width: 34px !important; padding: 1px !important; margin: 0 !important; cursor: pointer; border-radius: 4px !important; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer #insp-section-layer-name > label.zpp-fl-head { display: flex !important; align-items: center; justify-content: space-between; }\n";
    function css() {
        if (document.getElementById('zpp-fl-css')) { return; }
        var st = document.createElement('style');
        st.id = 'zpp-fl-css';
        st.textContent = CSS;
        document.head.appendChild(st);
    }
    function colorInput() {
        var c = document.getElementById('prop-fl-color');
        if (c) { return c; }
        var head = document.querySelector('#insp-section-layer-name > label');
        if (!head) { return null; }
        head.classList.add('zpp-fl-head');
        c = document.createElement('input');
        c.type = 'color';
        c.setAttribute('id', 'prop-fl-color');
        c.title = 'Layer Color';
        c.setAttribute('aria-label', 'Layer Color');
        c.value = DEF_COLOR;
        c.style.display = 'none';
        c.setAttribute('data-zpp-fl', '1');
        head.appendChild(c);
        c.addEventListener('click', function (e) { e.stopPropagation(); });
        return c;
    }
    function box() {
        css();
        colorInput();
        var b = document.getElementById(BOX);
        if (b) { return b; }
        var host = document.getElementById('insp-section-layer-name');
        if (!host) { return null; }
        b = document.createElement('div');
        b.id = BOX;
        b.className = 'zpp-fl';
        b.style.display = 'none';
        b.innerHTML = '<label class="zpp-sw"><input type="checkbox" id="prop-fl-show" /><span class="zpp-sw-track"></span><span>Hide Label</span></label><div class="zpp-fl-row"><span>Label Text Color</span><input type="color" id="prop-fl-text-color" value="' + DEF_COLOR + '" aria-label="Label Text Color" title="Label Text Color" /></div>';
        host.appendChild(b);
        return b;
    }
    function setDisplay(n, v) { if (n && n.style.display !== v) { n.style.display = v; } }
    function hydrate() {
        var b = box(), el = activeEl(), c = document.getElementById('prop-fl-color');
        if (!b) { return; }
        if (!isForm(el)) { setDisplay(b, 'none'); setDisplay(c, 'none'); return; }
        setDisplay(b, 'block');
        setDisplay(c, 'inline-block');
        $('#prop-fl-show').prop('checked', !shown(el));
        var cv = colorOf(el).length === 4 ? DEF_COLOR : colorOf(el).slice(0, 7);
        if (c && c.value !== cv) { c.value = cv; }
        var tv = textColorOf(el).length === 4 ? DEF_COLOR : textColorOf(el).slice(0, 7), tc = document.getElementById('prop-fl-text-color');
        if (tc && tc.value !== tv) { tc.value = tv; }
    }

    /* ---- label on the builder stage (the live preview is the real front end, so PHP draws that one) ---- */
    function paintStage() {
        var els = allEls();
        els.forEach(function (el) {
            var node = document.getElementById('el-' + cid(el.id)) || $('.zappoppop-canvas-item[data-id="' + cid(el.id) + '"]')[0];
            if (!node) { return; }
            var old = node.querySelector(':scope > .zpp-fld-label');
            var want = isForm(el) && shown(el) && String(el.name || '').trim() !== '';
            if (!want) { if (old) { old.remove(); } return; }
            if (!old) {
                old = document.createElement('span');
                old.className = 'zpp-fld-label';
                old.setAttribute('aria-hidden', 'true');
                old.setAttribute('style', 'position:absolute;left:0;bottom:100%;margin-bottom:3px;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;font-weight:600;line-height:1.2;pointer-events:none;text-shadow:none;');
                node.appendChild(old);
                node.style.setProperty('overflow', 'visible', 'important');
            }
            if (old.textContent !== el.name) { old.textContent = el.name; }
            old.style.color = textColorOf(el);
        });
    }
    /* the layer icon next to the name in the Layers Panel follows the label colour */
    function paintIcons() {
        allEls().forEach(function (el) {
            var ic = document.querySelector('#layer-item-' + cid(el.id) + ' .zappoppop-layer-icon');
            if (!ic) { return; }
            if (isForm(el)) { ic.style.setProperty('color', colorOf(el), 'important'); ic.querySelectorAll('.dashicons').forEach(function (d) { d.style.setProperty('color', colorOf(el), 'important'); }); }
            else { ic.style.removeProperty('color'); ic.querySelectorAll('.dashicons').forEach(function (d) { d.style.removeProperty('color'); }); }
        });
    }
    function schedule() { [50, 300, 900].forEach(function (ms) { window.setTimeout(function () { hydrate(); paintStage(); paintIcons(); }, ms); }); }

    function commit(mutate) {
        var el = activeEl();
        if (!el || !isForm(el)) { return; }
        mutate(el);
        var c = core();
        if (c && typeof c.pushHistory === 'function') { c.pushHistory(); }
        hydrate(); paintStage(); paintIcons();
    }

    $(document).on('change.zppFl', '#prop-fl-show', function () { var hide = this.checked; commit(function (el) { el.showFieldLabel = !hide; }); });
    $(document).on('input.zppFl change.zppFl', '#prop-fl-color', function () { var v = this.value; commit(function (el) { el.fieldLabelColor = v; }); });
    $(document).on('input.zppFl change.zppFl', '#prop-fl-text-color', function () { var v = this.value; commit(function (el) { el.fieldLabelTextColor = v; }); });
    $(document).on('input.zppFl change.zppFl', '#prop-layer-name, #prop-element-name', schedule);
    $(document).on('click.zppFl', '.zappoppop-canvas-item, .zappoppop-layer-item, #zappoppop-inspector-drawer [data-tab]', schedule);

    $(function () {
        box(); hydrate(); paintStage(); paintIcons();
        /* early burst: colour the Layers Panel icons as soon as the model/rows exist, not after the 2 s sweep */
        (function () { var n = 0, b = window.setInterval(function () { hydrate(); paintStage(); paintIcons(); if (++n >= 30) { window.clearInterval(b); } }, 100); })();
        var t = null, stage = document.querySelector('.zappoppop-canvas, #zappoppop-canvas, #zappoppop-canvas-stage') || document.body;
        if (window.MutationObserver) {
            new MutationObserver(function (m) {
                if (m.every(function (x) { return x.target && x.target.classList && x.target.classList.contains('zpp-fld-label'); })) { return; }
                paintStage(); /* ZPP_FL_NO_BLINK: microtask = before the browser paints, so a re-rendered layer never shows a frame without its label */
            }).observe(stage, { childList: true, subtree: true });
        }
        window.setInterval(function () { paintStage(); paintIcons(); }, 2000);
        /* ZPP_FL_SELECTION_WATCH: selecting a layer from the Layers Panel does not always reach the click handler above (it can stop the event, and the active layer is set a moment later).
           So follow the selection itself: whenever the active layer or the visibility of the two controls is out of step, hydrate again. */
        (function () {
            var last = '';
            window.setInterval(function () {
                var c = core(), id = (c && c.state) ? cid(c.state.activeId) : '', el = activeEl(), b = document.getElementById(BOX);
                var want = isForm(el), has = !!b && b.style.display !== 'none';
                var key = id + '|' + (el ? String(el.type || '') : '');
                if (key !== last || want !== has) { last = key; hydrate(); paintIcons(); }
            }, 150);
        })();
        var ll = document.getElementById('zappoppop-layers-list');
        var lp = document.getElementById('zappoppop-floating-layers-panel') || ll;
        if (lp && window.MutationObserver) { new MutationObserver(function () { paintIcons(); }).observe(lp, { childList: true, subtree: true }); }
    });
})(window, window.jQuery);
