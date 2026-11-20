/* ZPP_CONTENT_TAB: accordion (remembered per block) + bottom Reset for the Content tab. Presentation only; the data lifecycle is untouched. */
(function (window, $) {
    'use strict';
    if (!$) { return; }

    var LS = 'zppContentAcc', HEAD = 'zpp-ca-head', CLOSED = 'zpp-ca-closed', HIDE = 'zpp-ca-hidden';
    var SHAPE_KEYS = ['shapePreset', 'shapeFill', 'shapeStroke', 'strokeColor', 'strokeWidth', 'shapeStrokeWidth', 'shapeNoFill', 'shapeRotate', 'rotate', 'rotation'];
    /* model keys behind the shared Animation / form fields (they have no data-prop; the pre-save flush reads them from the DOM) */
    var ALWAYS = { animation: 'none', animIn: 'none', animStart: 'after', animDuration: 1000, animDelay: 0 };
    var IF_SET = { required: false, requiredMessage: '', autocomplete: 'off' };
    /* ZPP_LD_LABEL: field-label settings (Element Settings panel, no data-prop) join Save / Reset Content as Default; they exist on the model only once changed */
    var LABEL_KEYS = ['showFieldLabel', 'fieldLabelPosition', 'fieldLabelTextColor'];

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

    function attach(head, id, isClosed) {
        head.classList.add(HEAD);
        head.setAttribute('role', 'button');
        head.setAttribute('tabindex', '0');
        head.setAttribute('data-zpp-ca', id);
        head.setAttribute('aria-expanded', isClosed ? 'false' : 'true');
    }

    function setup() {
        var pane = document.getElementById('insp-tab-basic');
        if (!pane) { return false; }
        var st = readState();

        var card = document.getElementById('insp-section-gp-basic-Shared');
        if (card && !card.querySelector('.zpp-ca-title')) {
            var h = document.createElement('div');
            h.className = 'zpp-ca-title';
            h.textContent = 'Animation & Behavior';
            card.insertBefore(h, card.firstChild);
            attach(h, 'anim', !!st.anim);
            card.classList.toggle(CLOSED, !!st.anim);
        }

        Array.prototype.forEach.call(document.querySelectorAll('#zappoppop-element-specific-settings > .element-panel'), function (panel) {
            var head = panel.firstElementChild;
            if (!head || head.classList.contains(HEAD)) { return; }
            var id = 'el:' + panel.id;
            attach(head, id, !!st[id]);
            panel.classList.toggle(CLOSED, !!st[id]);
        });

        var ch = document.getElementById('insp-section-coords');
        if (ch && !ch.classList.contains(HEAD)) {
            attach(ch, 'coords', !!st.coords);
            ch.classList.toggle(CLOSED, !!st.coords);
            var grid = document.getElementById('insp-section-coords-grid');
            if (grid) { grid.classList.toggle(HIDE, !!st.coords); }
        }

        if (!document.getElementById('zpp-ct-reset')) {
            var wrap = document.createElement('div');
            wrap.id = 'zpp-ct-reset-wrap';
            wrap.innerHTML = '<button type="button" id="zpp-ct-reset" class="zpp-ct-reset">↺ <span>Reset Content Settings</span></button>';
            pane.appendChild(wrap);
        }
        return true;
    }

    function toggle(head) {
        var id = head.getAttribute('data-zpp-ca'), st = readState(), closed;
        if (id === 'coords') {
            closed = !head.classList.contains(CLOSED);
            head.classList.toggle(CLOSED, closed);
            var grid = document.getElementById('insp-section-coords-grid');
            if (grid) { grid.classList.toggle(HIDE, closed); }
        } else {
            var root = head.parentNode;
            closed = !root.classList.contains(CLOSED);
            root.classList.toggle(CLOSED, closed);
        }
        head.setAttribute('aria-expanded', closed ? 'false' : 'true');
        if (closed) { st[id] = 1; } else { delete st[id]; }
        writeState(st);
    }

    document.addEventListener('click', function (e) {
        var d = drawer(), t = e.target;
        if (!d || !t || !t.closest) { return; }
        var h = t.closest('.' + HEAD);
        if (!h || !d.contains(h) || !h.hasAttribute('data-zpp-ca')) { return; }
        var ctl = t.closest('button,input,select,textarea,a,label');
        if (ctl && h.contains(ctl) && ctl !== h) { return; }
        toggle(h);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' && e.key !== ' ' && e.key !== 'Spacebar') { return; }
        var t = e.target;
        if (t && t.classList && t.classList.contains(HEAD) && t.hasAttribute('data-zpp-ca') && drawer() && drawer().contains(t)) {
            e.preventDefault();
            toggle(t);
        }
    });

    /* ---- Reset: every Content-tab setting of the selected layer back to its default (Layer Name is kept) ---- */
    var armTimer = 0;
    function disarm(btn) {
        window.clearTimeout(armTimer);
        btn.classList.remove('is-armed');
        btn.querySelector('span').textContent = 'Reset Content Settings';
    }
    function contentKeys(el) {
        var keys = {};
        SHAPE_KEYS.forEach(function (k) { if (el && el.type === 'shape') { keys[k] = 1; } });
        Array.prototype.forEach.call(document.querySelectorAll('#insp-tab-basic [data-prop]'), function (n) {
            if (n.closest('#insp-section-layer-name')) { return; }
            var k = n.getAttribute('data-prop');
            if (k && k !== 'name' && k !== 'id' && k !== 'type') { keys[k] = 1; }
        });
        return keys;
    }
    /* ZPP_LAYER_DEFAULTS: every Content-tab model key (incl. the Animation / form fields), read by "Save Content Settings as Default" */
    window.ZapPopTabKeys = window.ZapPopTabKeys || {};
    window.ZapPopTabKeys.basic = function (el) {
        var keys = contentKeys(el);
        Object.keys(ALWAYS).forEach(function (k) { keys[k] = 1; });
        Object.keys(IF_SET).forEach(function (k) { if (el && Object.prototype.hasOwnProperty.call(el, k)) { keys[k] = 1; } });
        LABEL_KEYS.forEach(function (k) { if (el && Object.prototype.hasOwnProperty.call(el, k)) { keys[k] = 1; } }); /* ZPP_LD_LABEL */
        return keys;
    };

    function resetContent() {
        var el = activeEl();
        if (!el) { return; }
        var keys = contentKeys(el);
        Object.keys(keys).forEach(function (k) {
            if (Object.prototype.hasOwnProperty.call(ALWAYS, k) || Object.prototype.hasOwnProperty.call(IF_SET, k)) { return; }
            delete el[k];
        });
        Object.keys(ALWAYS).forEach(function (k) { el[k] = ALWAYS[k]; });
        Object.keys(IF_SET).forEach(function (k) { if (Object.prototype.hasOwnProperty.call(el, k)) { el[k] = IF_SET[k]; } });
        LABEL_KEYS.forEach(function (k) { delete el[k]; }); /* ZPP_LD_LABEL: back to built-in (label shown above the field), then the saved default is re-applied below */
        $('#prop-anim-in').val('none');
        $('#prop-anim-start').val('after');
        $('#prop-anim-duration').val(1000);
        $('#prop-anim-delay').val(0);
        $('#prop-gp-required').prop('checked', false);
        $('#prop-gp-required-msg').val('');
        $('#prop-gp-autocomplete').val('off');
        if (window.ZapPopPopLayerDefaults) { window.ZapPopPopLayerDefaults.applyTo(el, 'basic'); window.ZapPopPopLayerDefaults.syncDom(el); } /* ZPP_LAYER_DEFAULTS */
        var c = core();
        if (c && typeof c.pushHistory === 'function') { c.pushHistory('Reset Content Settings'); }
        var cv = window.ZapPopPopBuilderCanvas;
        if (cv && typeof cv.renderCanvas === 'function') { cv.renderCanvas(); }
        window.setTimeout(function () {
            var $n = $('.zappoppop-canvas-item[data-id="' + cid(el.id) + '"]').first();
            if ($n.length) { $n.trigger('click'); }
        }, 60);
    }
    document.addEventListener('click', function (e) {
        var b = e.target && e.target.closest ? e.target.closest('#zpp-ct-reset') : null;
        if (!b) { return; }
        if (!b.classList.contains('is-armed')) {
            b.classList.add('is-armed');
            b.querySelector('span').textContent = 'Click again to reset everything';
            armTimer = window.setTimeout(function () { disarm(b); }, 3000);
            return;
        }
        disarm(b);
        resetContent();
    });

    $(function () {
        if (setup()) { return; }
        var tries = 0, t = window.setInterval(function () {
            if (setup() || ++tries > 40) { window.clearInterval(t); }
        }, 300);
    });
})(window, window.jQuery);
