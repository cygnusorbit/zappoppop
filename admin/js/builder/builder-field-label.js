/* ZPP_FIELD_LABEL: every form element shows its Layer Name as a small label above the field.
   Model keys: showFieldLabel (undefined = on), fieldLabelColor. Lifecycle: Inspector DOM -> model -> history -> AJAX whitelist -> front renderer. */
(function (window, $) {
    'use strict';
    if (!$) { return; }
    var BOX = 'zpp-fl-box', DEF_COLOR = '#334155', LAYER_DEF = '#64748b', INSIDE_COLOR = '#64748b';
    /* ZPP_FL_POSITION: model key fieldLabelPosition = 'inside' | 'outside' (undefined = outside). Inside = label in the field's top-left; input-like types only. */
    var INPUTS = ['text', 'textfield', 'email', 'number', 'phone', 'tel', 'textarea', 'select', 'dropdown', 'date', 'time', 'password'];
    var STYLE_OUT = 'position:absolute;left:0;bottom:100%;margin-bottom:3px;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:11px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;line-height:1.2;pointer-events:none;text-shadow:none;';
    var STYLE_IN = 'position:absolute;left:12px;top:4px;max-width:calc(100% - 24px);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:11px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;line-height:1;pointer-events:none;text-shadow:none;';
    var POS_CSS = '.zpp-fld-in input,.zpp-fld-in select,.zpp-fld-in textarea{box-sizing:border-box!important;padding-top:20px!important;padding-bottom:4px!important}.zpp-fld-in textarea{padding-top:22px!important}.zpp-fld-in-stage>div:not(.ui-resizable-handle):not(.zappoppop-resize-handle){box-sizing:border-box!important;padding-top:20px!important;padding-bottom:4px!important}.zpp-fld-in-stage.zpp-fld-in-ta>div:not(.ui-resizable-handle):not(.zappoppop-resize-handle){padding-top:22px!important}.zpp-fld-in-stage>div [style*="top:50%"]{top:calc(50% + 8px)!important}/* ZPP_ICON_CENTER */html body #zappoppop-inspector-drawer.zappoppop-inspector-drawer #prop-fl-pos{height:28px;min-height:28px;max-width:150px;margin:0;padding:0 8px;font-size:12px}';
    var FORM = ['text', 'textfield', 'email', 'number', 'phone', 'tel', 'textarea', 'select', 'dropdown', 'multiselect', 'radio', 'radios', 'checkbox', 'checkboxes', 'checkboxgroup', 'date', 'time', 'upload', 'file', 'password', 'imageselect', 'tile', 'rating'];

    function core() { return window.ZapPopPopBuilderCore || null; }
    function reg() { return window.ZapPopPopElementsRegistry || {}; }
    function cid(v) { return String(v || '').replace(/^zappoppop-el-/, '').replace(/^el-/, '').trim(); }
    function isForm(el) { return !!el && FORM.indexOf(String(el.type || '').toLowerCase()) !== -1; }
    function shown(el) { return !(el.showFieldLabel === false || el.showFieldLabel === 'false' || el.showFieldLabel === 0 || el.showFieldLabel === '0'); }
    function colorOf(el) { return /^#[0-9a-f]{3,8}$/i.test(String(el.fieldLabelColor || '')) ? el.fieldLabelColor : LAYER_DEF; } /* ZPP_LC_DEFAULT: the default Layer Color is #64748b */
    function isInput(el) { return !!el && INPUTS.indexOf(String(el.type || '').toLowerCase()) !== -1; }
    function inside(el) { return isInput(el) && String(el.fieldLabelPosition || '') === 'inside'; }
    function textColorOf(el) { return /^#[0-9a-f]{3,8}$/i.test(String(el.fieldLabelTextColor || '')) ? el.fieldLabelTextColor : (inside(el) ? INSIDE_COLOR : DEF_COLOR); }
    function cssPos() {
        if (document.getElementById('zpp-fld-in-css')) { return; }
        var st = document.createElement('style');
        st.id = 'zpp-fld-in-css';
        st.textContent = POS_CSS;
        document.head.appendChild(st);
    }
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
    var CSS = "html body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw {\n    display: flex !important; align-items: center !important; gap: 10px !important; margin: 0 !important; position: relative; cursor: pointer;\n    font-size: 12px !important; line-height: 20px; font-weight: 500 !important; letter-spacing: 0 !important; color: var(--zpp-builder-text-color) !important;\n}\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw input[type=checkbox] {\n    position: absolute !important; opacity: 0 !important; width: 1px !important; height: 1px !important; min-width: 0 !important; margin: 0 !important;\n    pointer-events: none !important; -webkit-appearance: none; appearance: none;\n}\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw input[type=checkbox]::before { display: none !important; content: none !important; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw .zpp-sw-track {\n    display: block !important; position: relative; flex: 0 0 36px; width: 36px; height: 20px; border-radius: 20px; box-sizing: border-box;\n    background: var(--zpp-builder-toggle-off-bg); transition: background .15s ease;\n}\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw .zpp-sw-track::after {\n    content: \"\"; position: absolute; top: 2px; left: 2px; width: 16px; height: 16px; border-radius: 50%; background: #fff;\n    box-shadow: 0 1px 2px rgba(0,0,0,.4); transition: transform .15s ease;\n}\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw input[type=checkbox]:checked + .zpp-sw-track { background: var(--zpp-builder-accent-color); }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw input[type=checkbox]:checked + .zpp-sw-track::after { transform: translateX(16px); }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw input[type=checkbox]:focus-visible + .zpp-sw-track { box-shadow: 0 0 0 2px var(--zpp-builder-accent-color); }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw > span:last-child { flex: 1 1 auto; display: block; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw code { font-family: inherit !important; font-size: inherit !important; font-weight: 700 !important; background: transparent !important; padding: 0 !important; color: var(--zpp-builder-accent-color) !important; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl { margin-top: 10px; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl .zpp-fl-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 10px; font-size: 12px; font-weight: 500; line-height: 20px; color: var(--zpp-builder-text-color); }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl .zpp-fl-row.zpp-fl-col { flex-direction: column; align-items: stretch; justify-content: flex-start; gap: 4px; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer #prop-fl-text-color { height: 20px !important; min-height: 20px !important; width: 34px !important; padding: 1px !important; margin: 0 !important; cursor: pointer; border-radius: 4px !important; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw { flex-direction: row-reverse !important; justify-content: space-between !important; width: 100%; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl label.zpp-sw > span:last-child { flex: 1 1 auto; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer #prop-fl-color { height: 30px !important; min-height: 30px !important; width: 36px !important; flex: 0 0 36px !important; box-sizing: border-box !important; padding: 2px !important; margin: 0 !important; cursor: pointer; border-radius: 4px !important; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer #zpp-ln-row { display: flex; align-items: center; gap: 8px; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer #zpp-ln-row > #prop-layer-name { flex: 1 1 0 !important; min-width: 0 !important; width: auto !important; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer #insp-section-layer-name > label.zpp-fl-head { display: flex !important; align-items: center; justify-content: space-between; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); column-gap: 10px; align-items: start; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl > label.zpp-sw { grid-column: 1 / -1; order: -2; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl > #zpp-fl-pos-row { grid-column: 1 / -1; order: -1; }\nhtml body #zappoppop-inspector-drawer.zappoppop-inspector-drawer .zpp-fl > .zpp-fl-row.zpp-fl-col { min-width: 0; }\n";
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
        c.value = LAYER_DEF;
        c.style.display = 'none';
        c.setAttribute('data-zpp-fl', '1');
        /* ZPP_LN_ROW: the colour swatch sits in the same row as the Layer Name input, on the right, same height */
        var nameInp = document.getElementById('prop-layer-name'), rowEl = document.getElementById('zpp-ln-row');
        if (nameInp && head.parentNode && !rowEl) {
            rowEl = document.createElement('div');
            rowEl.id = 'zpp-ln-row';
            head.parentNode.insertBefore(rowEl, nameInp);
            rowEl.appendChild(nameInp);
        }
        if (rowEl) { rowEl.appendChild(c); } else { head.appendChild(c); }
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
        b.innerHTML = '<label class="zpp-sw"><input type="checkbox" id="prop-fl-show" /><span class="zpp-sw-track"></span><span>Hide Label</span></label><div class="zpp-fl-row zpp-fl-col"><span>Label</span><input type="color" id="prop-fl-text-color" value="' + DEF_COLOR + '" aria-label="Label" title="Label" /></div><div class="zpp-fl-row" id="zpp-fl-pos-row"><span>Label Position</span><select id="prop-fl-pos" class="zappoppop-input" aria-label="Label Position"><option value="outside">Above field</option><option value="inside">Inside field</option></select></div>'
            + '<div class="zpp-fl-row zpp-fl-col" id="zpp-fs-bg-row" style="display:none"><span>Background</span><input type="color" id="prop-fs-bg" data-zpp-alpha="1" value="#ffffff" aria-label="Background" title="Background" /></div>'
            + '<div class="zpp-fl-row zpp-fl-col" id="zpp-fs-bd-row" style="display:none"><span>Border</span><input type="color" id="prop-fs-bd" data-zpp-alpha="1" value="#cbd5e1" aria-label="Border" title="Border" /></div>'
            + '<div class="zpp-fl-row zpp-fl-col" id="zpp-fs-ph-row" style="display:none"><span>Placeholder</span><input type="color" id="prop-fs-ph" data-zpp-alpha="1" value="#64748b" aria-label="Placeholder" title="Placeholder" /></div>'; /* ZPP_FS_PH */ /* ZPP_FIELD_STYLE: logic in builder-field-style.js */
        host.appendChild(b);
        return b;
    }
    function setDisplay(n, v) { if (n && n.style.display !== v) { n.style.display = v; } }
    function hydrate() {
        var b = box(), el = activeEl(), c = document.getElementById('prop-fl-color');
        if (!b) { return; }
        /* ZPP_LAYER_COLOR_ALL: the Layer Color swatch belongs to every element; the label box is form-only */
        if (!el) { setDisplay(b, 'none'); setDisplay(c, 'none'); return; }
        setDisplay(c, 'inline-block');
        if (!isForm(el)) { setDisplay(b, 'none'); var nv = colorOf(el).length === 4 ? LAYER_DEF : colorOf(el).slice(0, 7); if (c && c.value !== nv) { c.value = nv; } return; }
        setDisplay(b, 'grid'); /* ZPP_FL_2COL: Label / Background / Border / Placeholder pickers sit in 2 columns x 2 rows */
        $('#prop-fl-show').prop('checked', !shown(el));
        cssPos(); /* ZPP_FL_POSITION */
        var pr = document.getElementById('zpp-fl-pos-row'), ps = document.getElementById('prop-fl-pos');
        setDisplay(pr, isInput(el) && shown(el) ? 'flex' : 'none');
        if (ps && ps.value !== (inside(el) ? 'inside' : 'outside')) { ps.value = inside(el) ? 'inside' : 'outside'; }
        var cv = colorOf(el).length === 4 ? LAYER_DEF : colorOf(el).slice(0, 7);
        if (c && c.value !== cv) { c.value = cv; }
        var tv = textColorOf(el).length === 4 ? DEF_COLOR : textColorOf(el).slice(0, 7), tc = document.getElementById('prop-fl-text-color');
        if (tc && tc.value !== tv) { tc.value = tv; }
    }

    /* ---- label on the builder stage (the live preview is the real front end, so PHP draws that one) ---- */
    function paintStage() {
        cssPos(); /* ZPP_FL_POSITION */
        var els = allEls();
        els.forEach(function (el) {
            var node = document.getElementById('el-' + cid(el.id)) || $('.zappoppop-canvas-item[data-id="' + cid(el.id) + '"]')[0];
            if (!node) { return; }
            var old = node.querySelector(':scope > .zpp-fld-label');
            var want = isForm(el) && shown(el) && String(el.name || '').trim() !== '';
            if (!want) { /* ZPP_FL_HIDE_RESET: hidden label = no label room; drop the inside-label padding classes so the placeholder sits vertically centred */
                if (old) { old.remove(); }
                node.classList.remove('zpp-fld-in', 'zpp-fld-in-stage', 'zpp-fld-in-ta');
                return;
            }
            if (!old) {
                old = document.createElement('span');
                old.className = 'zpp-fld-label';
                old.setAttribute('aria-hidden', 'true');
                old.setAttribute('style', 'position:absolute;left:0;bottom:100%;margin-bottom:3px;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:11px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;line-height:1.2;pointer-events:none;text-shadow:none;');
                node.appendChild(old);
                node.style.setProperty('overflow', 'visible', 'important');
            }
            var ins = inside(el); /* ZPP_FL_POSITION: inside = top-left of the field, outside = above it */
            if (old.getAttribute('data-zpp-pos') !== (ins ? 'in' : 'out')) { old.setAttribute('style', ins ? STYLE_IN : STYLE_OUT); old.setAttribute('data-zpp-pos', ins ? 'in' : 'out'); }
            node.classList.toggle('zpp-fld-in', ins);
            node.classList.toggle('zpp-fld-in-stage', ins); /* ZPP_FL_PARITY: the builder stage draws a div, not an input, so it gets the same top room through its own rule */
            node.classList.toggle('zpp-fld-in-ta', ins && String(el.type || '').toLowerCase() === 'textarea');
            if (ins) { if (node.style.getPropertyValue('overflow') === 'visible') { node.style.removeProperty('overflow'); } }
            else if (node.style.getPropertyValue('overflow') !== 'visible') { node.style.setProperty('overflow', 'visible', 'important'); }
            if (old.textContent !== el.name) { old.textContent = el.name; }
            old.style.color = textColorOf(el);
        });
    }
    /* the layer icon next to the name in the Layers Panel follows the label colour */
    function paintIcons() {
        allEls().forEach(function (el) {
            var ic = document.querySelector('#layer-item-' + cid(el.id) + ' .zappoppop-layer-icon');
            if (!ic) { return; }
            if (isForm(el) || /^#[0-9a-f]{3,8}$/i.test(String(el.fieldLabelColor || ''))) { ic.style.setProperty('color', colorOf(el), 'important'); ic.querySelectorAll('.dashicons').forEach(function (d) { d.style.setProperty('color', colorOf(el), 'important'); }); }
            else { ic.style.removeProperty('color'); ic.querySelectorAll('.dashicons').forEach(function (d) { d.style.removeProperty('color'); }); }
        });
    }
    function schedule() { [50, 300, 900].forEach(function (ms) { window.setTimeout(function () { hydrate(); paintStage(); paintIcons(); }, ms); }); }

    function commit(mutate, anyType) {
        var el = activeEl();
        if (!el || (!anyType && !isForm(el))) { return; }
        mutate(el);
        var c = core();
        if (c && typeof c.pushHistory === 'function') { c.pushHistory(); }
        hydrate(); paintStage(); paintIcons();
    }

    $(document).on('change.zppFl', '#prop-fl-show', function () { var hide = this.checked; commit(function (el) { el.showFieldLabel = !hide; }); });
    $(document).on('change.zppFl', '#prop-fl-pos', function () { var v = this.value === 'inside' ? 'inside' : 'outside'; commit(function (el) { el.fieldLabelPosition = v; }); }); /* ZPP_FL_POSITION */
    $(document).on('input.zppFl change.zppFl', '#prop-fl-color', function () { var v = this.value; commit(function (el) { el.fieldLabelColor = v; }, true); });
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
                var want = isForm(el), has = !!b && b.style.display !== 'none', cc = document.getElementById('prop-fl-color'), cwant = !!el, chas = !!cc && cc.style.display !== 'none';
                if (cwant !== chas) { want = !want; } /* ZPP_LAYER_COLOR_ALL: force a hydrate when the swatch visibility is out of step */
                var key = id + '|' + (el ? String(el.type || '') : '');
                if (key !== last || want !== has) { last = key; hydrate(); paintIcons(); }
            }, 150);
        })();
        var ll = document.getElementById('zappoppop-layers-list');
        var lp = document.getElementById('zappoppop-floating-layers-panel') || ll;
        if (lp && window.MutationObserver) { new MutationObserver(function () { paintIcons(); }).observe(lp, { childList: true, subtree: true }); }
    });
})(window, window.jQuery);
