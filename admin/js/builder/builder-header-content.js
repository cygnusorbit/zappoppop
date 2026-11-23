/**
 * ZapPopPop Builder: Header content mini editor + HTML ID / Attributes rows (ZPP_HEADER_CONTENT).
 *
 * Data lifecycle:
 *  - content        inline HTML string. The hidden textarea #prop-label-content stays the data-prop control; the visual editor writes into it.
 *  - custom_css_id  string, data-prop control #prop-label-id (same key as Logic > Custom CSS ID).
 *  - htmlAttrs      [{name, value}] kept on the layer model, written live; the server keeps only allow-listed rows (ZapPopPop_Html_Attrs).
 * Cross-browser: contenteditable + execCommand (Chrome, Safari, Firefox, Edge); output is rebuilt from the DOM with a small allow-list.
 */
(function (window, document, $) {
    'use strict';
    if (!$ || !window.HTMLTextAreaElement) { return; }

    var NAME_OK = /^(?:data-[a-z0-9_.\-]+|aria-(?:hidden|label|live|pressed|expanded|controls|describedby|labelledby|modal|checked|selected|disabled|required|invalid|busy|atomic|current|haspopup|valuenow|valuemin|valuemax|valuetext|errormessage|relevant|roledescription)|role|title|tabindex|lang|dir|draggable|spellcheck)$/;
    var KEEP = { B: 'b', STRONG: 'strong', I: 'i', EM: 'em', U: 'u', S: 's', STRIKE: 's', DEL: 's', SUP: 'sup', SUB: 'sub' };
    var SAFE_HREF = /^(?:https?:\/\/|mailto:|tel:|\/(?!\/)|#)/i;
    var SAFE_STYLE = /^[A-Za-z0-9\s:;#%.,()\-!]*$/;
    var SAFE_CLASS = /^[A-Za-z0-9_\-\s]*$/;
    var desc = Object.getOwnPropertyDescriptor(window.HTMLTextAreaElement.prototype, 'value');
    var ta, editor, box, bar, linkbar, linkInput, note, htmlBtn;
    var savedRange = null, syncing = false, dirty = false, histTimer = 0, ready = false;

    function __(s) { return (window.wp && window.wp.i18n && window.wp.i18n.__) ? window.wp.i18n.__(s, 'zappoppop') : s; }
    function esc(s) { return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/ /g, '&nbsp;'); }
    function attrEsc(s) { return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }
    function core() { return window.ZapPopPopBuilderCore || null; }
    function currentEl() { var R = window.ZapPopPopElementsRegistry; return (R && typeof R.resolveElement === 'function') ? R.resolveElement() : null; }
    function history() { var c = core(); if (c && typeof c.pushHistory === 'function') { c.pushHistory(); } }
    function queueHistory() { window.clearTimeout(histTimer); histTimer = window.setTimeout(history, 700); }

    /* ---------- serialise / parse (allow-list: b strong i em u s sup sub br a span) ---------- */
    function parse(html) {
        var t = document.createElement('template');
        t.innerHTML = String(html || '');
        return t.content;
    }
    function ser(parent) {
        var out = '', kids = parent.childNodes, i, n, tag, inner, href, st, cl;
        for (i = 0; i < kids.length; i++) {
            n = kids[i];
            if (n.nodeType === 3) { out += esc(n.nodeValue); continue; }
            if (n.nodeType !== 1) { continue; }
            tag = n.nodeName.toUpperCase();
            if (tag === 'BR') { out += '<br>'; continue; }
            if (tag === 'SCRIPT' || tag === 'STYLE' || tag === 'TEMPLATE') { continue; }
            inner = ser(n);
            if (KEEP[tag]) {
                out += inner === '' ? '' : '<' + KEEP[tag] + '>' + inner + '</' + KEEP[tag] + '>';
            } else if (tag === 'A') {
                href = n.getAttribute('href') || '';
                out += (href && SAFE_HREF.test(href)) ? '<a href="' + attrEsc(href) + '"' + (n.getAttribute('target') === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '') + '>' + inner + '</a>' : inner;
            } else if (tag === 'SPAN') {
                st = n.getAttribute('style') || '';
                cl = n.getAttribute('class') || '';
                out += ((st && SAFE_STYLE.test(st)) || (cl && SAFE_CLASS.test(cl)))
                    ? '<span' + (cl && SAFE_CLASS.test(cl) ? ' class="' + attrEsc(cl) + '"' : '') + (st && SAFE_STYLE.test(st) ? ' style="' + attrEsc(st) + '"' : '') + '>' + inner + '</span>'
                    : inner;
            } else if (tag === 'DIV' || tag === 'P') {
                out += inner + (i < kids.length - 1 ? '<br>' : '');
            } else {
                out += inner;
            }
        }
        return out;
    }
    /* true when the markup uses more than the visual editor can keep (other tags, attributes, blocks) */
    function hasComplex(root) {
        var all = root.querySelectorAll('*'), i, j, n, tag, a, ok;
        for (i = 0; i < all.length; i++) {
            n = all[i];
            tag = n.nodeName.toUpperCase();
            if (tag === 'BR') { continue; }
            if (!KEEP[tag] && tag !== 'A' && tag !== 'SPAN') { return true; }
            for (j = 0; j < n.attributes.length; j++) {
                a = n.attributes[j].name.toLowerCase();
                ok = (tag === 'A' && (a === 'href' || a === 'target' || a === 'rel')) || (tag === 'SPAN' && (a === 'style' || a === 'class'));
                if (!ok) { return true; }
            }
            if (tag === 'A' && !SAFE_HREF.test(n.getAttribute('href') || '')) { return true; }
        }
        return false;
    }

    /* ---------- editor <-> textarea ---------- */
    function setMode(mode) {
        var isHtml = mode === 'html';
        box.classList.toggle('is-html', isHtml);
        htmlBtn.classList.toggle('is-active', isHtml);
        htmlBtn.setAttribute('aria-pressed', isHtml ? 'true' : 'false');
        $(bar).find('[data-zpp-cmd]').not('[data-zpp-cmd="html"]').prop('disabled', isHtml);
        if (isHtml) { linkbar.hidden = true; }
    }
    function loadFromSource() {
        var frag = parse(desc.get.call(ta)), complex = hasComplex(frag);
        if (complex) {
            setMode('html');
            note.hidden = true;
        } else {
            setMode('visual');
            editor.innerHTML = ser(frag);
            note.hidden = true;
        }
    }
    function push(final) {
        var html = ser(editor);
        if (html === '<br>') { html = ''; editor.innerHTML = ''; }
        if (desc.get.call(ta) !== html) {
            syncing = true;
            desc.set.call(ta, html);
            syncing = false;
            $(ta).trigger('input');
        }
        if (final) { dirty = false; $(ta).trigger('change'); window.clearTimeout(histTimer); history(); } else { dirty = true; queueHistory(); }
    }

    /* ---------- selection helpers ---------- */
    function inEditor(node) { return !!node && (node === editor || editor.contains(node)); }
    function saveRange() {
        var s = window.getSelection();
        if (s && s.rangeCount && inEditor(s.anchorNode)) { savedRange = s.getRangeAt(0).cloneRange(); }
    }
    function restoreRange() {
        var s = window.getSelection(), r = savedRange;
        if (!s) { return; }
        if (!r) { r = document.createRange(); r.selectNodeContents(editor); r.collapse(false); }
        s.removeAllRanges();
        s.addRange(r);
    }
    function focusEditor() {
        if (document.activeElement !== editor) { editor.focus(); restoreRange(); }
    }
    function currentAnchor() {
        var s = window.getSelection(), n = s && s.rangeCount ? s.anchorNode : null;
        while (n && n !== editor) { if (n.nodeType === 1 && n.nodeName === 'A') { return n; } n = n.parentNode; }
        return null;
    }
    function refreshState() {
        var s = window.getSelection(), inside = !!(s && s.rangeCount && inEditor(s.anchorNode));
        $(bar).find('[data-zpp-cmd]').each(function () {
            var c = this.getAttribute('data-zpp-cmd'), on = false;
            if (c === 'clear' || c === 'html') { return; }
            if (inside) {
                if (c === 'link') { on = !!currentAnchor(); } else { try { on = document.queryCommandState(c); } catch (e) { on = false; } }
            }
            this.classList.toggle('is-active', on);
            this.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
    }

    /* ---------- commands ---------- */
    function exec(cmd) {
        focusEditor();
        try { document.execCommand('styleWithCSS', false, false); } catch (e) { /* older engines */ }
        if (cmd === 'clear') { document.execCommand('removeFormat'); document.execCommand('unlink'); }
        else { document.execCommand(cmd, false, null); }
        push(true);
        refreshState();
    }
    function normUrl(u) {
        u = $.trim(u);
        if (!u) { return ''; }
        if (!/^[a-z][a-z0-9+.\-]*:/i.test(u) && !/^[\/#]/.test(u)) { u = 'https://' + u; }
        return SAFE_HREF.test(u) ? u : null;
    }
    function openLink() {
        var a;
        saveRange();
        a = currentAnchor();
        linkInput.value = a ? (a.getAttribute('href') || '') : '';
        linkInput.removeAttribute('aria-invalid');
        linkbar.hidden = false;
        linkInput.focus();
        linkInput.select();
    }
    function closeLink() { linkbar.hidden = true; }
    function applyLink() {
        var u = normUrl(linkInput.value), s, a;
        if (u === null) { linkInput.setAttribute('aria-invalid', 'true'); return; }
        linkInput.removeAttribute('aria-invalid');
        editor.focus();
        restoreRange();
        s = window.getSelection();
        a = currentAnchor();
        if (u === '') {
            document.execCommand('unlink');
        } else if (s && s.isCollapsed && a) {
            a.setAttribute('href', u);
        } else if (s && s.isCollapsed) {
            document.execCommand('insertHTML', false, '<a href="' + attrEsc(u) + '">' + esc(u) + '</a>');
        } else {
            document.execCommand('createLink', false, u);
        }
        closeLink();
        push(true);
        refreshState();
    }
    function removeLink() {
        editor.focus();
        restoreRange();
        document.execCommand('unlink');
        closeLink();
        push(true);
        refreshState();
    }
    /* WordPress value tokens (registry-label.js calls this first; false = use the textarea path) */
    function insertText(text) {
        if (!ready || box.classList.contains('is-html')) { return false; }
        focusEditor();
        document.execCommand('insertText', false, String(text));
        push(true);
        return true;
    }

    /* ---------- HTML ID + Attributes ---------- */
    function rowEl(name, value) {
        var $r = $('<div class="zpp-hc-attr"></div>');
        $('<input type="text" class="zpp-hc-attr-name zappoppop-input" list="zpp-hc-attr-names" maxlength="64" autocomplete="off" spellcheck="false" />')
            .attr({ placeholder: __('Name'), 'aria-label': __('Attribute name') }).val(name || '').appendTo($r);
        $('<input type="text" class="zpp-hc-attr-val zappoppop-input" maxlength="200" autocomplete="off" spellcheck="false" />')
            .attr({ placeholder: __('Value'), 'aria-label': __('Attribute value') }).val(value || '').appendTo($r);
        $('<button type="button" class="zpp-hc-attr-del"></button>')
            .attr({ title: __('Remove attribute'), 'aria-label': __('Remove attribute') })
            .html('<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>').appendTo($r);
        return $r;
    }
    function collect() {
        var rows = [];
        $('#zpp-hc-attrs .zpp-hc-attr').each(function () {
            rows.push({ name: $.trim($(this).find('.zpp-hc-attr-name').val()).toLowerCase(), value: $(this).find('.zpp-hc-attr-val').val() });
        });
        return rows;
    }
    function validate() {
        $('#zpp-hc-attrs .zpp-hc-attr').each(function () {
            var $n = $(this).find('.zpp-hc-attr-name'), $v = $(this).find('.zpp-hc-attr-val');
            var name = $.trim($n.val()).toLowerCase(), badName = name !== '' && !NAME_OK.test(name);
            var badVal = name !== '' && !badName && name.indexOf('data-') !== 0 && $.trim($v.val()) === '';
            $n.attr('aria-invalid', badName ? 'true' : null);
            $v.attr('aria-invalid', badVal ? 'true' : null);
            $n.attr('title', badName ? __('Allowed: data-*, aria-*, role, title, tabindex, lang, dir, draggable, spellcheck') : null);
        });
    }
    function commitAttrs() {
        var el = currentEl(), rows;
        validate();
        if (!el) { return; }
        rows = collect();
        if (rows.length) { el.htmlAttrs = rows; } else { delete el.htmlAttrs; }
        queueHistory();
    }
    function hydrate() {
        var el = currentEl(), $w = $('#zpp-hc-attrs'), rows;
        if (!$w.length) { return; }
        $w.empty();
        rows = (el && $.isArray(el.htmlAttrs)) ? el.htmlAttrs : [];
        $.each(rows, function (i, r) { if (r) { $w.append(rowEl(r.name, r.value)); } });
        validate();
        if (el) { $('#prop-label-id').val(el.custom_css_id ? el.custom_css_id : ''); }
    }
    function onExternalValue() {
        var cur = ser(editor);
        if (cur === '<br>') { cur = ''; }
        if (document.activeElement === editor && cur === desc.get.call(ta)) { return; } /* the Inspector echoed what is being typed: keep the caret */
        loadFromSource();
        window.setTimeout(hydrate, 0);
        window.setTimeout(hydrate, 150);
    }

    /* ---------- wiring ---------- */
    function init() {
        ta = document.getElementById('prop-label-content');
        editor = document.getElementById('zpp-hc-editor');
        box = document.getElementById('zpp-hc-box');
        bar = document.getElementById('zpp-hc-bar');
        linkbar = document.getElementById('zpp-hc-linkbar');
        linkInput = document.getElementById('zpp-hc-link-url');
        note = document.getElementById('zpp-hc-note');
        if (!ta || !editor || !box || !bar || !linkbar || !linkInput || !note) { return false; }
        if (ready) { return true; }
        htmlBtn = bar.querySelector('[data-zpp-cmd="html"]');

        /* every programmatic write to the textarea (Inspector populate, Reset, defaults sync) refreshes the editor */
        Object.defineProperty(ta, 'value', {
            configurable: true, enumerable: true,
            get: function () { return desc.get.call(this); },
            set: function (v) { desc.set.call(this, v); if (!syncing) { onExternalValue(); } }
        });

        bar.addEventListener('mousedown', function (e) { if (e.target.closest && e.target.closest('.zpp-hc-btn')) { e.preventDefault(); } });
        bar.addEventListener('click', function (e) {
            var b = e.target.closest ? e.target.closest('[data-zpp-cmd]') : null, cmd;
            if (!b || b.disabled) { return; }
            cmd = b.getAttribute('data-zpp-cmd');
            if (cmd === 'html') {
                if (box.classList.contains('is-html')) {
                    note.hidden = !hasComplex(parse(desc.get.call(ta)));
                    setMode('visual');
                    editor.innerHTML = ser(parse(desc.get.call(ta)));
                } else {
                    push(true);
                    setMode('html');
                    note.hidden = true;
                    ta.focus();
                }
            } else if (cmd === 'link') {
                if (linkbar.hidden) { openLink(); } else { closeLink(); }
            } else {
                exec(cmd);
            }
        });
        editor.addEventListener('input', function () { push(false); });
        editor.addEventListener('blur', function () { saveRange(); if (dirty) { push(true); } });
        editor.addEventListener('keyup', function () { saveRange(); refreshState(); });
        editor.addEventListener('mouseup', function () { saveRange(); refreshState(); });
        editor.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (!document.execCommand('insertLineBreak')) { document.execCommand('insertHTML', false, '<br>'); }
            }
        });
        editor.addEventListener('paste', function (e) {
            var cd = e.clipboardData || window.clipboardData, t;
            e.preventDefault();
            t = cd ? String(cd.getData('text') || '').replace(/\s*[\r\n]+\s*/g, ' ') : '';
            if (t) { document.execCommand('insertText', false, t); }
        });
        document.addEventListener('selectionchange', function () {
            var s = window.getSelection();
            if (s && s.rangeCount && inEditor(s.anchorNode)) { saveRange(); refreshState(); }
        });

        linkInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); applyLink(); }
            else if (e.key === 'Escape') { e.preventDefault(); closeLink(); editor.focus(); restoreRange(); }
        });
        document.getElementById('zpp-hc-link-apply').addEventListener('click', applyLink);
        document.getElementById('zpp-hc-link-remove').addEventListener('click', removeLink);

        /* ID: keep it a valid id, mirror the Logic tab control (same key: custom_css_id) */
        $('#prop-label-id').on('input', function () {
            var clean = this.value.replace(/[^A-Za-z0-9_\-:.]/g, '');
            if (clean !== this.value) { this.value = clean; }
            $('#prop-logic-css-id').val(clean);
        });

        $(document).off('.zppHc')
            .on('click.zppHc', '#zpp-hc-attr-add', function (e) {
                var $r = rowEl('', '');
                e.preventDefault();
                $('#zpp-hc-attrs').append($r);
                $r.find('.zpp-hc-attr-name').trigger('focus');
                commitAttrs();
            })
            .on('input.zppHc change.zppHc', '#zpp-hc-attrs .zpp-hc-attr input', commitAttrs)
            .on('click.zppHc', '#zpp-hc-attrs .zpp-hc-attr-del', function (e) {
                e.preventDefault();
                $(this).closest('.zpp-hc-attr').remove();
                commitAttrs();
            })
            .on('click.zppHc', '.zpp-hc-sethead', function () {
                var open = this.getAttribute('aria-expanded') !== 'true';
                this.setAttribute('aria-expanded', open ? 'true' : 'false');
                $('#zpp-hc-setbody').prop('hidden', !open);
                $(this).find('.dashicons').toggleClass('dashicons-arrow-up-alt2', open).toggleClass('dashicons-arrow-down-alt2', !open);
            })
            .on('click.zppHc', '.zappoppop-canvas-item, .zappoppop-layer-item', function () { window.setTimeout(hydrate, 120); });

        ready = true;
        loadFromSource();
        hydrate();
        return true;
    }

    window.ZapPopPopHeaderContent = { insertText: insertText, hydrate: function () { if (ready) { onExternalValue(); } } };

    $(function () {
        var tries = 0, t;
        if (init()) { return; }
        t = window.setInterval(function () { if (init() || ++tries > 40) { window.clearInterval(t); } }, 300);
    });
})(window, document, window.jQuery);
