/* ZPP_TAB_BLOCKS: Style / Logic / Data / Advanced tabs. Collapsible Border / Shadow blocks (remembered) + per-tab Reset to the layer type's spawn defaults. */
(function (window, $) {
    'use strict';
    if (!$) { return; }

    var LS = 'zppTabAcc', HEAD = 'zpp-tb-head', CLOSED = 'zpp-tb-closed';
    var TAB_LABEL = { style: 'Style', logic: 'Logic', data: 'Data', advanced: 'Advanced' };
    var ROUTING = ['buttonAction', 'linkUrl', 'url', 'linkBlank', 'targetBlank', 'nofollow', 'routeValue', 'routeLabel', 'goto_canvas', 'goto_screen', 'target_canvas', 'linkTarget'];
    var FIXED = {
        style: ['borderEnabled', 'shadowEnabled', 'textAlign'], /* ZPP_STYLE_TEXTALIGN: the Left/Center/Right buttons have no data-prop, so list the key here for Save-as-Default and Reset */
        logic: ['logicEnabled', 'logicAction', 'logicMatch', 'logicRules'].concat(ROUTING),
        data: ['defaultValue', 'dynamicDefault', 'paramName', 'dynamicParam', 'readOnly', 'saveToDb'],
        advanced: ['fieldName', 'fieldId', 'inputMask', 'errorMessage', 'validatorEqual', 'validatorList', 'filters', 'validators']
    };
    var EMPTY = { logicRules: [] };

    function core() { return window.ZapPopPopBuilderCore || null; }
    function cid(v) { return String(v || '').replace(/^zappoppop-el-/, '').replace(/^el-/, '').trim(); }
    function activeEl() {
        var c = core();
        if (!c || !c.state) { return null; }
        var id = cid(c.state.activeId);
        if (!id) { return null; }
        var list = (c.state.canvases && c.state.canvases[c.state.currentCanvas || 1]) || [];
        for (var i = 0; i < list.length; i++) { if (list[i] && cid(list[i].id) === id) { return list[i]; } }
        return null;
    }
    function drawer() { return document.getElementById('zappoppop-inspector-drawer'); }
    function readState() { try { return JSON.parse(window.localStorage.getItem(LS) || '{}') || {}; } catch (e) { return {}; } }
    function writeState(o) { try { window.localStorage.setItem(LS, JSON.stringify(o)); } catch (e) { /* per page view only */ } }

    /* ---- collapsible blocks that the section accordion does not cover (Border, Shadow: title + on/off switch) ---- */
    function hideTransparentLabels() {
        var n = document.getElementById('prop-bg-transparent'), l = n && n.closest('label');
        if (l) { l.style.display = 'none'; }
    }

    function setupBlocks() {
        hideTransparentLabels();
        var st = readState(), any = false;
        ['style', 'logic', 'data', 'advanced'].forEach(function (tab) {
            var pane = document.getElementById('insp-tab-' + tab);
            if (!pane) { return; }
            any = true;
            Array.prototype.forEach.call(pane.children, function (sec) {
                var head = sec.firstElementChild;
                if (!head || !head.classList.contains('zappoppop-sec-head') || head.classList.contains(HEAD)) { return; }
                var id = 'sec:' + (sec.id || tab);
                head.classList.add(HEAD);
                head.setAttribute('role', 'button');
                head.setAttribute('tabindex', '0');
                head.setAttribute('data-zpp-tb', id);
                head.setAttribute('aria-expanded', st[id] ? 'false' : 'true');
                sec.classList.toggle(CLOSED, !!st[id]);
            });
            if (!pane.querySelector('.zpp-tr-wrap')) {
                var wrap = document.createElement('div');
                wrap.className = 'zpp-tr-wrap';
                wrap.innerHTML = '<button type="button" class="zpp-tr-btn" data-zpp-tab="' + tab + '">↺ <span>Reset ' + TAB_LABEL[tab] + ' Settings</span></button>';
                pane.appendChild(wrap);
            }
        });
        return any;
    }

    /* ---- the layer type's real spawn defaults, read from the canvas' own addElement (side effects suppressed) ---- */
    function spawnDefaults(type) {
        var cv = window.ZapPopPopBuilderCanvas, c = core();
        if (!cv || typeof cv.addElement !== 'function' || !c || !c.state) { return null; }
        var list = cv.getActiveElements ? cv.getActiveElements() : null;
        if (!list) { return null; }
        var L = window.ZapPopPopBuilderLayers, I = window.ZapPopPopBuilderInspector, SP = window.ZapPopPopSpawnPlace;
        var saved = {
            render: cv.renderCanvas, history: c.pushHistory,
            layers: L && L.renderLayers, open: I && I.open, place: SP && SP.place, active: c.state.activeId, len: list.length
        };
        var made = null;
        try {
            cv.renderCanvas = function () {};
            c.pushHistory = function () {};
            if (L) { L.renderLayers = function () {}; }
            if (I) { I.open = function () {}; }
            if (SP) { SP.place = function () {}; }
            window.__zppFactorySpawn = true; /* ZPP_LAYER_DEFAULTS: built-in spawn only, saved layer defaults are skipped */
            cv.addElement(type, 0, 0);
            made = list.length > saved.len ? list[list.length - 1] : null;
            if (made) { list.pop(); made = JSON.parse(JSON.stringify(made)); }
        } catch (e) {
            made = null;
        } finally {
            window.__zppFactorySpawn = false;
            cv.renderCanvas = saved.render; c.pushHistory = saved.history;
            if (L) { L.renderLayers = saved.layers; }
            if (I) { I.open = saved.open; }
            if (SP) { SP.place = saved.place; }
            c.state.activeId = saved.active;
            while (list.length > saved.len) { list.pop(); }
        }
        return made;
    }

    function resetDom(pane) {
        Array.prototype.forEach.call(pane.querySelectorAll('input,select,textarea'), function (n) {
            if (n.closest('.zpp-tr-wrap')) { return; }
            var t = (n.type || '').toLowerCase();
            if (t === 'checkbox' || t === 'radio') { n.checked = n.defaultChecked; return; }
            if (n.tagName === 'SELECT') {
                var d = null;
                Array.prototype.forEach.call(n.options, function (o) { if (!d && o.defaultSelected) { d = o; } });
                n.value = d ? d.value : (n.options[0] ? n.options[0].value : '');
                return;
            }
            if (t === 'button' || t === 'submit' || t === 'file' || t === 'hidden') { return; }
            n.value = n.defaultValue;
        });
    }

    function tabKeys(tab) {
        var pane = document.getElementById('insp-tab-' + tab), keys = {};
        (FIXED[tab] || []).forEach(function (k) { keys[k] = 1; });
        if (pane) {
            Array.prototype.forEach.call(pane.querySelectorAll('[data-prop]'), function (n) {
                var k = n.getAttribute('data-prop');
                if (k && k !== 'name' && k !== 'id' && k !== 'type') { keys[k] = 1; }
            });
        }
        return keys;
    }
    /* ZPP_LAYER_DEFAULTS: every model key of these tabs, read by "Save <Tab> Settings as Default" */
    window.ZapPopTabKeys = window.ZapPopTabKeys || {};
    Object.keys(FIXED).forEach(function (t) { window.ZapPopTabKeys[t] = function () { return tabKeys(t); }; });

    function resetTab(tab) {
        var el = activeEl(), pane = document.getElementById('insp-tab-' + tab);
        if (!el || !pane) { return; }
        var keys = tabKeys(tab);
        var def = spawnDefaults(String(el.type || '')) || {};
        Object.keys(keys).forEach(function (k) {
            if (Object.prototype.hasOwnProperty.call(def, k)) { el[k] = def[k]; }
            else if (Object.prototype.hasOwnProperty.call(EMPTY, k)) { el[k] = EMPTY[k].slice(); }
            else { delete el[k]; }
        });
        if (window.ZapPopPopLayerDefaults) { window.ZapPopPopLayerDefaults.applyTo(el, tab); } /* ZPP_LAYER_DEFAULTS: saved default wins over the built-in one */
        resetDom(pane);
        var c = core();
        if (c && typeof c.pushHistory === 'function') { c.pushHistory('Reset ' + TAB_LABEL[tab] + ' Settings'); }
        var cv = window.ZapPopPopBuilderCanvas;
        if (cv && typeof cv.renderCanvas === 'function') { cv.renderCanvas(); }
        window.setTimeout(function () {
            var $n = $('.zappoppop-canvas-item[data-id="' + cid(el.id) + '"]').first();
            if ($n.length) { $n.trigger('click'); }
        }, 60);
    }

    /* ---- events ---- */
    document.addEventListener('click', function (e) {
        var d = drawer(), t = e.target;
        if (!d || !t || !t.closest) { return; }
        if (d.contains(t)) { setupBlocks(); }
        var h = t.closest('.zappoppop-sec-head.' + HEAD);
        if (h && d.contains(h)) {
            var ctl = t.closest('button,input,select,textarea,a,label');
            if (ctl && h.contains(ctl) && ctl !== h) { return; }
            var sec = h.parentNode, closed = !sec.classList.contains(CLOSED), st = readState(), id = h.getAttribute('data-zpp-tb');
            sec.classList.toggle(CLOSED, closed);
            h.setAttribute('aria-expanded', closed ? 'false' : 'true');
            if (closed) { st[id] = 1; } else { delete st[id]; }
            writeState(st);
            return;
        }
        var b = t.closest('.zpp-tr-btn');
        if (!b || !d.contains(b)) { return; }
        var label = b.querySelector('span');
        if (!b.classList.contains('is-armed')) {
            b.classList.add('is-armed');
            label.textContent = 'Click again to reset everything';
            b.__t = window.setTimeout(function () { b.classList.remove('is-armed'); label.textContent = 'Reset ' + TAB_LABEL[b.getAttribute('data-zpp-tab')] + ' Settings'; }, 3000);
            return;
        }
        window.clearTimeout(b.__t);
        b.classList.remove('is-armed');
        label.textContent = 'Reset ' + TAB_LABEL[b.getAttribute('data-zpp-tab')] + ' Settings';
        resetTab(b.getAttribute('data-zpp-tab'));
    });
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' && e.key !== ' ' && e.key !== 'Spacebar') { return; }
        var t = e.target;
        if (t && t.classList && t.classList.contains('zappoppop-sec-head') && t.classList.contains(HEAD) && drawer() && drawer().contains(t)) {
            e.preventDefault();
            t.click();
        }
    });

    /* Border and Shadow no longer have an on/off switch: they are always on (a 0 width / 0 blur value shows nothing). */
    var NO_TRANSPARENT = ['prop-shape-transparent', 'prop-shape-stroke-transparent', 'prop-bg-transparent'];
    function alwaysOn() {
        /* the colour pickers carry their own alpha, so the separate Transparent switches are hidden and any leftover state is cleared */
        NO_TRANSPARENT.forEach(function (id) {
            var n = document.getElementById(id);
            if (n && n.checked && activeEl()) { $(n).prop('checked', false).trigger('change'); }
        });
        ['prop-border-enabled', 'prop-shadow-enabled'].forEach(function (id) {
            var n = document.getElementById(id);
            if (n && !n.checked && activeEl()) { $(n).prop('checked', true).trigger('change'); }
        });
    }

    $(function () {
        setupBlocks();
        var d = drawer(), timer = null;
        if (d && window.MutationObserver) {
            new MutationObserver(function () {
                window.clearTimeout(timer);
                timer = window.setTimeout(setupBlocks, 80);
            }).observe(d, { childList: true, subtree: true });
        }
        $(document).on('click.zppTabBlocks', '.zappoppop-canvas-item, .zappoppop-layer-item', function () {
            [120, 400, 900].forEach(function (ms) { window.setTimeout(function () { setupBlocks(); alwaysOn(); }, ms); });
        });
        var tries = 0, t = window.setInterval(function () { setupBlocks(); if (++tries > 20) { window.clearInterval(t); } }, 500);
    });
})(window, window.jQuery);
