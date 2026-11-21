/**
 * ZapPopPop Extend page: install and activate add-ons without leaving the page. ZAPPOPPOP_ADDONS_INPLACE
 * Install = WordPress's own installer (wp.updates.installPlugin, the same code as Plugins > Add New).
 * Activate = zappoppop_addon_activate (only plugins listed in the add-on catalog).
 * Progressive enhancement: if anything is missing or fails, the button's normal WordPress link still works.
 * ES5 only (Chrome, Safari, Firefox, Edge).
 */
(function ($) {
    'use strict';

    var M = window.ZapPopPopModules;
    var A = window.zappoppop_addons_vars || {};
    if (!M) { return; }

    function t(key, fallback) { return (A.i18n && A.i18n[key]) ? A.i18n[key] : fallback; }

    $(function () {
        var $wrap = $('.zpp-ext-addons');
        var $notice = $('#zpp-ext-notice');
        if (!$wrap.length) { return; }

        function card(el) { return $(el).closest('.zpp-ext-item'); }

        function busy($b, on, label) {
            if (on) {
                $b.data('zppLabel', $b.text()).data('zppBusy', true).addClass('disabled').attr('aria-disabled', 'true').text(label);
            } else {
                $b.data('zppBusy', false).removeClass('disabled').removeAttr('aria-disabled');
                if ($b.data('zppLabel')) { $b.text($b.data('zppLabel')); }
            }
        }

        function pill($c, text, cls) {
            $c.find('.zpp-mod-pill.is-ok, .zpp-mod-pill.is-warn, .zpp-mod-pill.is-lock').first()
                .removeClass('is-ok is-warn is-lock').addClass(cls).text(text);
        }

        function fail($b, message) {
            busy($b, false);
            M.notice($notice, 'error', message || t('failed', 'Something went wrong. Please try again.'), { href: $b.attr('href'), text: t('native', 'Use the WordPress Plugins screen') });
        }

        $wrap.on('click', 'a[data-zpp-act="install"]', function (e) {
            var $b = $(this);
            if (!window.wp || !window.wp.updates || typeof window.wp.updates.installPlugin !== 'function') { return; }
            e.preventDefault();
            if ($b.data('zppBusy')) { return; }
            var $c = card(this);
            busy($b, true, t('installing', 'Installing…'));
            window.wp.updates.installPlugin({
                slug: String($c.data('addon')),
                success: function (r) {
                    busy($b, false);
                    pill($c, t('inactive', 'Inactive'), 'is-warn');
                    $b.attr('data-zpp-act', 'activate').text(t('activate', 'Activate'));
                    if (r && r.activateUrl) { $b.attr('href', r.activateUrl); }
                    M.notice($notice, 'success', t('installed', 'Add-on installed. Click Activate to turn it on.'));
                },
                error: function (r) { fail($b, r && r.errorMessage ? r.errorMessage : ''); }
            });
        });

        $wrap.on('click', 'a[data-zpp-act="activate"]', function (e) {
            var $b = $(this);
            e.preventDefault();
            if ($b.data('zppBusy')) { return; }
            var $c = card(this);
            busy($b, true, t('activating', 'Activating…'));
            $.ajax({
                url: M.vars.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: { action: 'zappoppop_addon_activate', nonce: M.vars.nonce, slug: String($c.data('addon')) }
            }).done(function (res) {
                if (!res || !res.success) { fail($b, res && res.data && res.data.message ? res.data.message : ''); return; }
                pill($c, t('active', 'Active'), 'is-ok');
                $c.find('.zpp-ext-item-actions').remove();
                M.notice($notice, 'success', t('activated', 'Add-on activated. Reloading to load its modules…'));
                window.setTimeout(function () { window.location.reload(); }, 900);
            }).fail(function () { fail($b, ''); });
        });
    });
})(jQuery);
