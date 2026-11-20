/**
 * ZapPopPop Settings: Builder sub-tabs and Element / Layer Settings editor.
 * Writes the overrides (only values that differ from the element default) into #zappoppop-ls-json,
 * which is saved with the normal settings form as settings[builder_layer_settings].
 */
(function (window, $) {
    'use strict';

    function readJson($field) {
        var data = {};
        try { data = JSON.parse($field.val() || '{}'); } catch (e) { data = {}; }
        return (data && typeof data === 'object' && !$.isArray(data)) ? data : {};
    }

    function countKeys(obj) {
        var n = 0, k;
        for (k in obj) { if (Object.prototype.hasOwnProperty.call(obj, k)) { n++; } }
        return n;
    }

    function init() {
        var $root = $('#zappoppop-ls-root');
        var $field = $('#zappoppop-ls-json');

        /* Builder sub-tabs: Builder Tools | Element / Layer Settings */
        $('.zappoppop-bld-subtabs').on('click', '.zappoppop-bld-subtab', function (e) {
            e.preventDefault();
            var target = $(this).attr('data-bld-pane');
            $('.zappoppop-bld-subtab').removeClass('is-active').attr('aria-selected', 'false');
            $(this).addClass('is-active').attr('aria-selected', 'true');
            $('.zappoppop-bld-pane').each(function () {
                if ($(this).attr('data-bld-pane') === target) { this.removeAttribute('hidden'); }
                else { this.setAttribute('hidden', 'hidden'); }
            });
        });

        if (!$root.length || !$field.length) { return; }
        var data = readJson($field);

        function persist() {
            $field.val(JSON.stringify(data));
        }

        function markElement(type) {
            var has = (data[type] && countKeys(data[type]) > 0) || (order[type] && countKeys(order[type]) > 0);
            $root.find('.zappoppop-ls-el-btn[data-ls-el="' + type + '"]').toggleClass('has-override', !!has);
        }

        function store(type, tab, key, value, def) {
            if (value === def) {
                if (data[type] && data[type][tab]) {
                    delete data[type][tab][key];
                    if (!countKeys(data[type][tab])) { delete data[type][tab]; }
                    if (!countKeys(data[type])) { delete data[type]; }
                }
            } else {
                data[type] = data[type] || {};
                data[type][tab] = data[type][tab] || {};
                data[type][tab][key] = value;
            }
            markElement(type);
        }

        /* Element list */
        $root.on('click', '.zappoppop-ls-el-btn', function (e) {
            e.preventDefault();
            var type = $(this).attr('data-ls-el');
            $root.find('.zappoppop-ls-el-btn').removeClass('is-active').attr('aria-selected', 'false');
            $(this).addClass('is-active').attr('aria-selected', 'true');
            $root.find('.zappoppop-ls-el').each(function () {
                if ($(this).attr('data-ls-el') === type) { this.removeAttribute('hidden'); }
                else { this.setAttribute('hidden', 'hidden'); }
            });
        });

        /* Content | Style | Logic | Data | Advanced */
        $root.on('click', '.zappoppop-ls-tab-btn', function (e) {
            e.preventDefault();
            var tab = $(this).attr('data-ls-tab');
            var $el = $(this).closest('.zappoppop-ls-el');
            $el.find('.zappoppop-ls-tab-btn').removeClass('is-active');
            $(this).addClass('is-active');
            $el.find('.zappoppop-ls-pane').each(function () {
                if ($(this).attr('data-ls-tab') === tab) { this.removeAttribute('hidden'); }
                else { this.setAttribute('hidden', 'hidden'); }
            });
        });

        /* Enable / disable toggles */
        function applyToggle($c, on) {
            var def = parseInt($c.attr('data-default'), 10) === 1 ? 1 : 0;
            $c.prop('checked', !!on);
            store($c.attr('data-type'), $c.attr('data-tab'), $c.attr('data-key'), on, def);
            $c.closest('.zappoppop-ls-row').toggleClass('is-off', !on);
        }
        $root.on('change', '.zappoppop-ls-chk', function () {
            /* ZPP_GROUP_CASCADE: a group switches as one. Parent on / off sets its sub-settings; a sub-setting turned on also turns its parent on. */
            var $c = $(this), on = $c.is(':checked') ? 1 : 0;
            var $row = $c.closest('.zappoppop-ls-row'), $ul = $row.parent(), key = $row.attr('data-ls-key');
            applyToggle($c, on);
            if ($row.hasClass('is-child')) {
                if (on) {
                    $ul.children('li.zappoppop-ls-row').not('.is-child').filter(function () { return this.getAttribute('data-ls-key') === $row.attr('data-ls-parent'); }).each(function () {
                        applyToggle($(this).find('.zappoppop-ls-chk').first(), 1);
                    });
                }
            } else {
                $ul.children('li.zappoppop-ls-row.is-child').filter(function () { return this.getAttribute('data-ls-parent') === key; }).each(function () {
                    applyToggle($(this).find('.zappoppop-ls-chk').first(), on);
                });
            }
            persist();
        });

        /* Drag-and-drop reorder (Pointer Events + Up / Down keys; Chrome, Safari, Firefox, Edge, touch) and accordion groups. */
        var $order = $('#zappoppop-lo-json');
        var order = $order.length ? readJson($order) : {};

        function rowsOf($ul) { return $ul.children('li.zappoppop-ls-row').get(); }

        function unitsOf($ul, parent) {
            var units = [], cur = null;
            rowsOf($ul).forEach(function (li) {
                var isChild = li.classList.contains('is-child');
                if (parent) {
                    if (isChild && li.getAttribute('data-ls-parent') === parent) { units.push([li]); }
                } else if (isChild && cur) {
                    cur.push(li);
                } else if (!isChild) {
                    cur = [li];
                    units.push(cur);
                }
            });
            return units;
        }

        function placeUnit(unit, ref, before) {
            var anchor;
            if (before) {
                unit.forEach(function (el) { ref[0].parentNode.insertBefore(el, ref[0]); });
            } else {
                anchor = ref[ref.length - 1];
                unit.forEach(function (el) {
                    anchor.parentNode.insertBefore(el, anchor.nextSibling);
                    anchor = el;
                });
            }
        }

        function saveOrder($ul) {
            var type = $ul.attr('data-type'), tab = $ul.attr('data-tab');
            var rows = rowsOf($ul), isDefault = true, top = [], slot;
            rows.forEach(function (li, i) {
                if (i && parseInt(rows[i - 1].getAttribute('data-ls-pos'), 10) > parseInt(li.getAttribute('data-ls-pos'), 10)) { isDefault = false; }
            });
            order[type] = order[type] || {};
            Object.keys(order[type]).forEach(function (s) {
                if (s === tab || s.indexOf(tab + '|') === 0) { delete order[type][s]; }
            });
            if (!isDefault) {
                unitsOf($ul, '').forEach(function (u) {
                    var key = u[0].getAttribute('data-ls-key');
                    top.push(key);
                    if (u.length > 1) {
                        slot = tab + '|' + key;
                        order[type][slot] = u.slice(1).map(function (li) { return li.getAttribute('data-ls-key'); });
                    }
                });
                order[type][tab] = top;
            }
            if (!countKeys(order[type])) { delete order[type]; }
            $order.val(JSON.stringify(order));
            markElement(type);
        }

        function restoreDefaultOrder(type) {
            $root.find('.zappoppop-ls-sortable[data-type="' + type + '"]').each(function () {
                var ul = this;
                rowsOf($(ul)).sort(function (a, b) {
                    return parseInt(a.getAttribute('data-ls-pos'), 10) - parseInt(b.getAttribute('data-ls-pos'), 10);
                }).forEach(function (li) { ul.appendChild(li); });
            });
            $order.val(JSON.stringify(order));
        }

        function unitOf(li, units) {
            var found = null;
            units.forEach(function (u) { if (u.indexOf(li) !== -1) { found = u; } });
            return found;
        }

        $root[0].addEventListener('pointerdown', function (e) {
            var grip = e.target.closest ? e.target.closest('.zappoppop-ls-grip') : null;
            if (!grip || (e.pointerType === 'mouse' && e.button !== 0)) { return; }
            var li = grip.closest('li.zappoppop-ls-row');
            var $ul = $(li.parentNode);
            var parent = li.classList.contains('is-child') ? li.getAttribute('data-ls-parent') : '';
            var mine = unitOf(li, unitsOf($ul, parent));
            if (!mine) { return; }
            e.preventDefault();
            mine.forEach(function (el) { el.classList.add('is-dragging'); });
            $root.addClass('is-sorting');

            function move(ev) {
                var others = unitsOf($ul, parent).filter(function (u) { return u[0] !== mine[0]; });
                var i, r, target = null;
                for (i = 0; i < others.length; i++) {
                    r = others[i][0].getBoundingClientRect();
                    if (ev.clientY < r.top + r.height / 2) { target = others[i]; break; }
                }
                if (target) {
                    if (mine[mine.length - 1].nextElementSibling !== target[0]) { placeUnit(mine, target, true); }
                } else if (others.length) {
                    var last = others[others.length - 1];
                    if (mine[0].previousElementSibling !== last[last.length - 1]) { placeUnit(mine, last, false); }
                }
            }
            function end() {
                document.removeEventListener('pointermove', move);
                document.removeEventListener('pointerup', end);
                document.removeEventListener('pointercancel', end);
                mine.forEach(function (el) { el.classList.remove('is-dragging'); });
                $root.removeClass('is-sorting');
                saveOrder($ul);
            }
            document.addEventListener('pointermove', move);
            document.addEventListener('pointerup', end);
            document.addEventListener('pointercancel', end);
        });

        $root.on('keydown', '.zappoppop-ls-grip', function (e) {
            if (e.which !== 38 && e.which !== 40) { return; }
            e.preventDefault();
            var li = $(this).closest('li.zappoppop-ls-row')[0];
            var $ul = $(li.parentNode);
            var parent = li.classList.contains('is-child') ? li.getAttribute('data-ls-parent') : '';
            var units = unitsOf($ul, parent);
            var mine = unitOf(li, units);
            var idx = units.indexOf(mine);
            if (e.which === 38 && idx > 0) { placeUnit(mine, units[idx - 1], true); }
            else if (e.which === 40 && idx < units.length - 1) { placeUnit(mine, units[idx + 1], false); }
            else { return; }
            this.focus();
            saveOrder($ul);
        });

        /* Accordion: a setting with sub-settings opens / closes its child rows */
        function toggleGroup(li) {
            var chev = li.querySelector('.zappoppop-ls-chev');
            if (!chev) { return; }
            var collapsed = li.classList.toggle('is-collapsed');
            chev.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            var n = li.nextElementSibling;
            while (n && n.classList.contains('is-child')) {
                n.classList.toggle('is-hidden-child', collapsed);
                n = n.nextElementSibling;
            }
        }
        $root.on('click', '.zappoppop-ls-chev', function (e) {
            e.preventDefault();
            toggleGroup($(this).closest('li.zappoppop-ls-row')[0]);
        });
        $root.on('click', 'li.zappoppop-ls-row:not(.is-child) .bld-name', function () {
            toggleGroup($(this).closest('li.zappoppop-ls-row')[0]);
        });

        /* Reset one element to its defaults */
        $root.on('click', '.zappoppop-ls-reset', function (e) {
            e.preventDefault();
            var type = $(this).attr('data-ls-el');
            delete order[type];
            restoreDefaultOrder(type);
            $root.find('.zappoppop-ls-chk[data-type="' + type + '"]').each(function () {
                var def = parseInt($(this).attr('data-default'), 10) === 1;
                $(this).prop('checked', def).closest('.zappoppop-ls-row').toggleClass('is-off', !def);
            });
            delete data[type];
            markElement(type);
            persist();
        });
    }

    $(init);
})(window, jQuery);
