/* ZPP_SHORTCUTS: header button "Keyboard Shortcut" -> modal that lists every builder shortcut. Presentation only (no model data touched).
   Theme tokens only (same palette as the Theme > Builder accordions). Vanilla ES5; Chrome, Safari, Firefox, Edge. */
(function (window, document) {
    'use strict';
    if (window.__zppShortcuts) { return; }
    window.__zppShortcuts = true;

    var MAC = /Mac|iPhone|iPad|iPod/i.test(String(navigator.platform || navigator.userAgent || ''));
    var K = {
        mod: MAC ? '⌘' : 'Ctrl', alt: MAC ? '⌥' : 'Alt', shift: MAC ? '⇧' : 'Shift',
        esc: 'Esc', del: 'Delete', bksp: MAC ? '⌫' : 'Backspace', space: 'Space'
    };

    /* [action, [alternative key combos], note] */
    var DATA = [
        { t: 'Editing', rows: [
            ['Undo', [[K.mod, 'Z']], 'Inside a text field you get the browser’s own text undo'],
            ['Redo', [[K.mod, K.shift, 'Z'], [K.mod, 'Y']]],
            ['Save', [[K.mod, 'S']], 'Same as the Save button'],
            ['Copy layer(s)', [[K.mod, 'C']], 'The selected layer, or every layer in a box selection'],
            ['Paste', [[K.mod, 'V']], 'Each paste is offset 15px; one Undo removes it'],
            ['Delete layer', [[K.del], [K.bksp]], 'A box selection is deleted in one step; locked layers stay'],
            ['Deselect / close', [[K.esc]], 'Closes Layer Settings, clears a box selection, exits Preview']
        ] },
        { t: 'Moving layers', rows: [
            ['Nudge 1px', [['← → ↑ ↓']], 'Also moves a whole box selection'],
            ['Nudge 10px', [[K.shift, '← → ↑ ↓']]],
            ['Drop a copy', [[K.alt, 'drag a layer']], 'Leaves the original, drops a copy where you release']
        ] },
        { t: 'Selecting', rows: [
            ['Box select', [['drag on empty canvas']], 'Selects every layer the box touches'],
            ['Add / remove one layer', [[K.shift, 'click a layer']]],
            ['Add a box to the selection', [[K.shift, 'drag a box']]]
        ] },
        { t: 'View', rows: [
            ['Pan the stage', [[K.space, 'drag']]],
            ['Zoom around the pointer', [[K.alt, 'mouse wheel']]],
            ['Reset zoom to 100%', [[K.mod, '0']]]
        ] }
    ];

    /* Hover bubble text per key label (falls back to the label itself, e.g. "drag a layer"). */
    var TIPS = {
        'Ctrl': 'Control key', '\u2318': 'Command key', 'Alt': 'Alt key', '\u2325': 'Option key',
        'Shift': 'Shift key', '\u21E7': 'Shift key', 'Esc': 'Escape key', 'Delete': 'Delete key',
        'Backspace': 'Backspace key', '\u232B': 'Backspace key', 'Space': 'Space bar',
        '\u2190 \u2192 \u2191 \u2193': 'Arrow keys', '0': 'Zero key',
        'drag a layer': 'Hold the key, then drag a layer', 'drag on empty canvas': 'Click and drag on empty canvas',
        'click a layer': 'Click a layer', 'drag a box': 'Drag a selection box', 'drag': 'Click and drag',
        'mouse wheel': 'Scroll the mouse wheel'
    };

    var CSS = [
        '#zpp-sc-modal{position:fixed;top:0;right:0;bottom:0;left:0;z-index:100200;display:none;align-items:center;justify-content:center;padding:24px;box-sizing:border-box;background:rgba(15,23,42,.6);font-family:var(--zpp-font-base,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif)}',
        '#zpp-sc-modal.is-open{display:-webkit-flex;display:flex}',
        '#zpp-sc-modal *{box-sizing:border-box}',
        '#zpp-sc-modal .zpp-sc-card{width:560px;max-width:100%;max-height:calc(100vh - 48px);display:-webkit-flex;display:flex;flex-direction:column;border-radius:10px;overflow:hidden;background:var(--zpp-builder-content-bg,#111827);color:var(--zpp-builder-content-text,#f8fafc);border:1px solid var(--zpp-builder-accordion-border,#334155);box-shadow:0 20px 50px rgba(0,0,0,.45)}',
        '#zpp-sc-modal .zpp-sc-head{display:-webkit-flex;display:flex;align-items:center;justify-content:space-between;padding:12px 14px;background:var(--zpp-builder-accordion-bg,#1e293b);color:var(--zpp-builder-accordion-text,#f8fafc);border-bottom:1px solid var(--zpp-builder-accordion-border,#334155)}',
        '#zpp-sc-modal .zpp-sc-title{margin:0;font-size:13px;font-weight:800;letter-spacing:.5px;line-height:1.3;color:inherit}',
        '#zpp-sc-modal .zpp-sc-x{width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;padding:0;margin:0;border:1px solid transparent;border-radius:6px;background:transparent;color:inherit;font-size:20px;line-height:1;cursor:pointer}',
        '#zpp-sc-modal .zpp-sc-x:hover,#zpp-sc-modal .zpp-sc-x:focus-visible{border-color:var(--zpp-builder-accent-color,#3b82f6);color:var(--zpp-builder-accent-color,#3b82f6);outline:0}',
        '#zpp-sc-modal .zpp-sc-body{padding:14px;overflow:auto;-webkit-overflow-scrolling:touch}',
        '#zpp-sc-modal .zpp-sc-sec{margin:0 0 12px;border:1px solid var(--zpp-builder-area-border,#334155);border-radius:8px;overflow:hidden;background:var(--zpp-builder-area-bg,#1e293b)}',
        '#zpp-sc-modal .zpp-sc-sec:last-child{margin-bottom:0}',
        '#zpp-sc-modal .zpp-sc-sh{margin:0;padding:9px 12px;font-size:10px;font-weight:800;letter-spacing:.5px;line-height:1.3;background:var(--zpp-builder-accordion-bg,#1e293b);color:var(--zpp-builder-accordion-text,#f8fafc);border-bottom:1px solid var(--zpp-builder-accordion-border,#334155)}',
        '#zpp-sc-modal .zpp-sc-row{display:-webkit-flex;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:8px 12px;color:var(--zpp-builder-content-text,#f8fafc)}',
        '#zpp-sc-modal .zpp-sc-row+.zpp-sc-row{border-top:1px solid var(--zpp-builder-area-border,#334155)}',
        '#zpp-sc-modal .zpp-sc-what{min-width:0;font-size:12px;font-weight:600;line-height:1.35}',
        '#zpp-sc-modal .zpp-sc-note{display:block;margin-top:2px;font-size:11px;font-weight:400;color:var(--zpp-builder-muted-text,#94a3b8)}',
        '#zpp-sc-modal .zpp-sc-keys{display:-webkit-flex;display:flex;flex-wrap:wrap;justify-content:flex-end;align-items:center;gap:4px;flex:0 0 auto;max-width:55%}',
        '#zpp-sc-modal .zpp-sc-or{font-size:11px;color:var(--zpp-builder-muted-text,#94a3b8)}',
        '#zpp-sc-modal .zpp-sc-combo{display:-webkit-flex;display:flex;align-items:center;gap:3px}',
        '#zpp-sc-modal kbd{display:inline-block;min-width:22px;padding:2px 6px;border-radius:5px;text-align:center;font-family:inherit;font-size:11px;font-weight:700;line-height:1.5;background:var(--zpp-builder-field-bg,#0f172a);color:var(--zpp-builder-field-text,#f8fafc);border:1px solid var(--zpp-builder-field-border,#334155);box-shadow:0 1px 0 var(--zpp-builder-field-border,#334155)}',
        '#zpp-sc-modal .zpp-sc-foot{margin:12px 0 0;font-size:11px;line-height:1.4;color:var(--zpp-builder-muted-text,#94a3b8)}',
        '#zpp-sc-modal kbd[data-zpp-tip]{cursor:default}',
        '#zpp-sc-modal .zpp-sc-tip{position:fixed;z-index:3;display:none;max-width:220px;padding:5px 9px;border-radius:6px;font-size:11px;font-weight:600;line-height:1.35;text-align:center;white-space:normal;pointer-events:none;background:var(--zpp-builder-accordion-bg,#1e293b);color:var(--zpp-builder-accordion-text,#f8fafc);border:1px solid var(--zpp-builder-accent-color,#3b82f6);box-shadow:0 6px 18px rgba(0,0,0,.4)}',
        '#zpp-sc-modal .zpp-sc-tip.is-on{display:block}',
        '#zpp-sc-modal .zpp-sc-tip:before{content:"";position:absolute;left:var(--zpp-tip-x,50%);top:-5px;width:8px;height:8px;margin-left:-4px;transform:rotate(45deg);background:var(--zpp-builder-accordion-bg,#1e293b);border-left:1px solid var(--zpp-builder-accent-color,#3b82f6);border-top:1px solid var(--zpp-builder-accent-color,#3b82f6)}',
        '#zpp-sc-modal .zpp-sc-tip.is-above:before{top:auto;bottom:-5px;transform:rotate(225deg)}'
    ].join('\n');

    var modal = null, lastFocus = null, tip = null;

    function tipTarget(n) {
        while (n && n !== modal) {
            if (n.nodeType === 1 && n.tagName === 'KBD' && n.getAttribute('data-zpp-tip')) { return n; }
            n = n.parentNode;
        }
        return null;
    }
    function hideTip() { if (tip) { tip.classList.remove('is-on'); tip.classList.remove('is-above'); } }
    function showTip(k) {
        if (!tip) { return; }
        tip.textContent = k.getAttribute('data-zpp-tip');
        tip.classList.remove('is-above');
        tip.style.left = '0px'; tip.style.top = '0px';
        tip.classList.add('is-on');
        var r = k.getBoundingClientRect(), w = tip.offsetWidth, h = tip.offsetHeight;
        var vw = document.documentElement.clientWidth || window.innerWidth, vh = document.documentElement.clientHeight || window.innerHeight;
        var cx = r.left + r.width / 2, left = Math.max(8, Math.min(cx - w / 2, vw - w - 8)), top = r.bottom + 8;
        if (top + h > vh - 8 && r.top - h - 8 > 8) { top = r.top - h - 8; tip.classList.add('is-above'); }
        tip.style.left = Math.round(left) + 'px';
        tip.style.top = Math.round(top) + 'px';
        tip.style.setProperty('--zpp-tip-x', Math.round(cx - left) + 'px');
    }

    function el(tag, cls, text) {
        var n = document.createElement(tag);
        if (cls) { n.className = cls; }
        if (text != null) { n.textContent = text; }
        return n;
    }

    function build() {
        if (document.getElementById('zpp-sc-css')) { /* keep */ } else {
            var st = document.createElement('style');
            st.id = 'zpp-sc-css';
            st.appendChild(document.createTextNode(CSS));
            (document.head || document.documentElement).appendChild(st);
        }
        modal = el('div');
        modal.id = 'zpp-sc-modal';
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('aria-labelledby', 'zpp-sc-title');
        var card = el('div', 'zpp-sc-card');
        var head = el('div', 'zpp-sc-head');
        var title = el('h2', 'zpp-sc-title', 'Keyboard Shortcut');
        title.id = 'zpp-sc-title';
        var x = el('button', 'zpp-sc-x', '×');
        x.type = 'button';
        x.setAttribute('aria-label', 'Close');
        x.setAttribute('data-zpp-sc-close', '1');
        head.appendChild(title); head.appendChild(x);
        var body = el('div', 'zpp-sc-body');
        DATA.forEach(function (sec) {
            var s = el('section', 'zpp-sc-sec');
            s.appendChild(el('h3', 'zpp-sc-sh', sec.t));
            sec.rows.forEach(function (r) {
                var row = el('div', 'zpp-sc-row');
                var what = el('div', 'zpp-sc-what', r[0]);
                if (r[2]) { what.appendChild(el('span', 'zpp-sc-note', r[2])); }
                var keys = el('div', 'zpp-sc-keys');
                r[1].forEach(function (combo, i) {
                    if (i) { keys.appendChild(el('span', 'zpp-sc-or', 'or')); }
                    var c = el('span', 'zpp-sc-combo');
                    combo.forEach(function (k) { var kb = el('kbd', '', k); kb.setAttribute('data-zpp-tip', TIPS[k] || k); c.appendChild(kb); });
                    keys.appendChild(c);
                });
                row.appendChild(what); row.appendChild(keys);
                s.appendChild(row);
            });
            body.appendChild(s);
        });
        body.appendChild(el('p', 'zpp-sc-foot', MAC
            ? 'On Windows / Linux: ⌘ is Ctrl, ⌥ is Alt, ⇧ is Shift, ⌫ is Backspace.'
            : 'On a Mac: Ctrl is ⌘ (Command), Alt is ⌥ (Option).') );
        card.appendChild(head); card.appendChild(body);
        modal.appendChild(card);
        tip = el('div', 'zpp-sc-tip');
        tip.setAttribute('role', 'tooltip');
        modal.appendChild(tip);
        document.body.appendChild(modal);
        modal.addEventListener('mouseover', function (e) { var k = tipTarget(e.target); if (k) { showTip(k); } });
        modal.addEventListener('mouseout', function (e) { var k = tipTarget(e.target); if (k && tipTarget(e.relatedTarget) !== k) { hideTip(); } });
        body.addEventListener('scroll', hideTip);
        window.addEventListener('resize', hideTip);
        modal.addEventListener('mousedown', function (e) { if (e.target === modal) { close(); } });
        modal.addEventListener('click', function (e) {
            var t = e.target;
            while (t && t !== modal) { if (t.getAttribute && t.getAttribute('data-zpp-sc-close')) { close(); return; } t = t.parentNode; }
        });
    }

    function isOpen() { return !!modal && modal.classList.contains('is-open'); }
    function open() {
        if (!modal) { build(); }
        lastFocus = document.activeElement;
        modal.classList.add('is-open');
        var b = modal.querySelector('.zpp-sc-x');
        if (b && b.focus) { try { b.focus(); } catch (e) { /* ignore */ } }
    }
    function close() {
        if (!modal) { return; }
        hideTip();
        modal.classList.remove('is-open');
        if (lastFocus && lastFocus.focus) { try { lastFocus.focus(); } catch (e) { /* ignore */ } }
        lastFocus = null;
    }

    /* While the modal is open the builder must not see any key (Delete would remove the selected layer). Window capture runs before every document handler. */
    window.addEventListener('keydown', function (e) {
        if (!isOpen()) { return; }
        if (e.key === 'Escape' || e.key === 'Esc' || e.keyCode === 27) { e.preventDefault(); e.stopImmediatePropagation(); close(); return; }
        if (e.key === 'Tab' || e.keyCode === 9) {
            var f = modal.querySelectorAll('button, [href], [tabindex]:not([tabindex="-1"])');
            if (f.length) { e.preventDefault(); e.stopImmediatePropagation(); f[0].focus(); }
            return;
        }
        e.stopImmediatePropagation();
    }, true);

    document.addEventListener('click', function (e) {
        var t = e.target;
        while (t && t !== document && t.id !== 'zappoppop-btn-shortcuts') { t = t.parentNode; }
        if (!t || t === document) { return; }
        e.preventDefault();
        if (isOpen()) { close(); } else { open(); }
    });

    window.ZapPopPopShortcuts = { open: open, close: close };
})(window, document);
