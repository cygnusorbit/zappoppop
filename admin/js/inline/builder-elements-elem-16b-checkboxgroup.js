(function () {
        'use strict';
        if (window.__zppCheckboxRepeater2) { return; }
        window.__zppCheckboxRepeater2 = true;
        window.__zppCheckboxRepeater = true;
        var last = null, drag = null;
        function ta()  { return document.getElementById('prop-checkboxgroup-options'); }
        function box() { return document.getElementById('zappoppop-checkboxgroup-rows'); }
        function trim(v) { return String(v == null ? '' : v).replace(/^\s+|\s+$/g, ''); }
        function clean(v) { return trim(String(v == null ? '' : v).replace(/[\r\n|]+/g, ' ')); }
        function fire(el) {
            var ev;
            try { ev = new Event('input', { bubbles: true }); } catch (err) { ev = document.createEvent('Event'); ev.initEvent('input', true, true); }
            el.dispatchEvent(ev);
        }
        function parse(line) {
            var i = line.indexOf('|');
            if (i === -1) { return { l: trim(line), v: '' }; }
            return { l: trim(line.slice(0, i)), v: trim(line.slice(i + 1)) };
        }
        function mkInput(ph, val, role) {
            var i = document.createElement('input');
            i.type = 'text';
            i.className = 'widefat';
            i.placeholder = ph;
            i.value = val || '';
            i.setAttribute('data-role', role);
            return i;
        }
        function mkBtn(txt, title, act) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'button button-small zappoppop-checkboxgroup-btn';
            b.title = title;
            b.setAttribute('aria-label', title);
            b.setAttribute('data-act', act);
            b.appendChild(document.createTextNode(txt));
            return b;
        }
        function mkRow(l, v) {
            var row = document.createElement('div');
            row.className = 'zappoppop-checkboxgroup-row';
            var g = document.createElement('span');
            g.className = 'zappoppop-checkboxgroup-grip';
            g.title = 'Drag to reorder';
            g.setAttribute('aria-label', 'Drag to reorder');
            g.appendChild(document.createTextNode('\u22EE\u22EE'));
            g.style.fontSize = '10px';
            g.style.letterSpacing = '-3px';
            row.appendChild(g);
            row.appendChild(mkInput('Label', l, 'label'));
            row.appendChild(mkInput('Value', v, 'value'));
            row.appendChild(mkBtn('\u29C9', 'Copy this choice', 'copy'));
            row.appendChild(mkBtn('+', 'Add a choice below', 'add'));
            row.appendChild(mkBtn('\u2212', 'Delete this choice', 'del'));
            return row;
        }
        function serialize() {
            var t = ta(), c = box();
            if (!t || !c) { return; }
            var rows = c.querySelectorAll('.zappoppop-checkboxgroup-row'), out = [], i;
            for (i = 0; i < rows.length; i++) {
                var l = clean(rows[i].querySelector('[data-role="label"]').value);
                var v = clean(rows[i].querySelector('[data-role="value"]').value);
                if (!l && !v) { continue; }
                if (!l) { l = v; }
                out.push((v && v !== l) ? (l + ' | ' + v) : l);
            }
            var joined = out.join('\n');
            last = joined;
            if (t.value !== joined) { t.value = joined; fire(t); }
        }
        function rebuild(force) {
            var t = ta(), c = box();
            if (!t || !c || drag) { return; }
            var raw = String(t.value || '');
            if (!force && last !== null && raw === last) { return; }
            var lines = raw.split(/\r?\n/).map(trim).filter(Boolean);
            if (lines.length === 1 && lines[0].indexOf(',') !== -1 && lines[0].indexOf('|') === -1) {
                lines = lines[0].split(',').map(trim).filter(Boolean);
            }
            c.innerHTML = '';
            lines.forEach(function (ln) { var p = parse(ln); c.appendChild(mkRow(p.l, p.v)); });
            if (!lines.length) { c.appendChild(mkRow('', '')); }
            last = raw;
        }
        window.__zppCheckboxRebuild = rebuild;

        /* ---------- mouse / touch / pen drag to reorder (handle only) ---------- */
        function moveTo(y) {
            var c = box();
            if (!c || !drag) { return; }
            var rows = c.querySelectorAll('.zappoppop-checkboxgroup-row'), i, ref = null;
            for (i = 0; i < rows.length; i++) {
                if (rows[i] === drag.row) { continue; }
                var r = rows[i].getBoundingClientRect();
                if (y < r.top + r.height / 2) { ref = rows[i]; break; }
            }
            if (ref) { if (drag.row.nextSibling !== ref) { c.insertBefore(drag.row, ref); } }
            else if (c.lastChild !== drag.row) { c.appendChild(drag.row); }
        }
        function endDrag() {
            if (!drag) { return; }
            var d = drag; drag = null;
            d.row.classList.remove('is-dragging');
            var c = box(); if (c) { c.classList.remove('is-sorting'); }
            try { if (d.grip.releasePointerCapture) { d.grip.releasePointerCapture(d.id); } } catch (er) {}
            serialize();
        }
        document.addEventListener('pointerdown', function (e) {
            var g = (e.target && e.target.closest) ? e.target.closest('#zappoppop-checkboxgroup-rows .zappoppop-checkboxgroup-grip') : null;
            if (!g || (e.button !== undefined && e.button !== 0)) { return; }
            var row = g.closest('.zappoppop-checkboxgroup-row');
            if (!row) { return; }
            e.preventDefault();
            e.stopPropagation();
            drag = { row: row, grip: g, id: e.pointerId };
            row.classList.add('is-dragging');
            box().classList.add('is-sorting');
            try { g.setPointerCapture(e.pointerId); } catch (er) {}
        }, true);
        document.addEventListener('pointermove', function (e) {
            if (!drag || e.pointerId !== drag.id) { return; }
            e.preventDefault();
            moveTo(e.clientY);
        }, true);
        document.addEventListener('pointerup', function (e) { if (drag && e.pointerId === drag.id) { endDrag(); } }, true);
        document.addEventListener('pointercancel', function (e) { if (drag && e.pointerId === drag.id) { endDrag(); } }, true);
        document.addEventListener('keydown', function (e) { if (drag && e.key === 'Escape') { endDrag(); } }, true);

        /* ---------- typing ---------- */
        document.addEventListener('input', function (e) {
            var t = e.target;
            if (t && t.closest && t.closest('#zappoppop-checkboxgroup-rows')) { serialize(); }
        }, true);

        /* ---------- add / copy / delete ---------- */
        document.addEventListener('click', function (e) {
            var tg = e.target;
            var addTop = (tg && tg.closest) ? tg.closest('#zappoppop-checkboxgroup-add') : null;
            if (addTop) {
                e.preventDefault();
                var c0 = box();
                if (c0) {
                    var r0 = mkRow('', '');
                    c0.appendChild(r0);
                    var f0 = r0.querySelector('input'); if (f0) { f0.focus(); }
                }
                return;
            }
            var b = (tg && tg.closest) ? tg.closest('#zappoppop-checkboxgroup-rows [data-act]') : null;
            if (b) {
                e.preventDefault();
                var row = b.closest('.zappoppop-checkboxgroup-row'), c = box();
                if (!row || !c) { return; }
                var act = b.getAttribute('data-act');
                if (act === 'add') {
                    var nr = mkRow('', '');
                    c.insertBefore(nr, row.nextSibling);
                    var nf = nr.querySelector('input'); if (nf) { nf.focus(); }
                } else if (act === 'copy') {
                    var cl = mkRow(row.querySelector('[data-role="label"]').value, row.querySelector('[data-role="value"]').value);
                    c.insertBefore(cl, row.nextSibling);
                } else if (act === 'del') {
                    if (c.querySelectorAll('.zappoppop-checkboxgroup-row').length > 1) { c.removeChild(row); }
                    else {
                        var ins = row.querySelectorAll('input'), k;
                        for (k = 0; k < ins.length; k++) { ins[k].value = ''; }
                    }
                }
                serialize();
                return;
            }
            if (tg && tg.closest && tg.closest('.zappoppop-canvas-item, .zappoppop-layer-item')) {
                [60, 250, 600].forEach(function (ms) { setTimeout(function () { rebuild(false); }, ms); });
            }
        }, true);

        function watch() {
            var p = document.getElementById('panel-elem-checkboxgroup');
            if (!p || p.__zppCheckboxWatch) { return; }
            p.__zppCheckboxWatch = true;
            if (window.MutationObserver) {
                new MutationObserver(function () { rebuild(false); }).observe(p, { attributes: true, attributeFilter: ['style', 'class'] });
            }
            p.addEventListener('mousedown', function () { rebuild(false); }, true);
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () { watch(); rebuild(false); });
        } else { watch(); rebuild(false); }
    })();
