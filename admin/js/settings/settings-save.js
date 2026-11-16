/**
 * ZapPopPop Settings: Asynchronous Save Controller
 */
(function($) {
    'use strict';

    window.ZapPopPopSettingsSave = {
        init: function() {
            this.bindSubmit();
        },

        bindSubmit: function() {
            var self = this;
            $('#zappoppop-settings-form').on('submit', function(e) {
                e.preventDefault();

                var $form = $(this);
                var $btn = $('#zappoppop-save-settings-btn');
                var $spinner = $('#zappoppop-save-settings-spinner');
                var $msg = $('#zappoppop-save-settings-msg');
                var $alert = $('#zappoppop-settings-alert');
                var $barBtn = $('#zappoppop-bar-save-btn');
                var $barMsg = $('#zappoppop-bar-save-msg');

                $btn.prop('disabled', true);
                $barBtn.prop('disabled', true);
                $barMsg.text('Saving...');
                $spinner.addClass('is-active');
                $msg.css('color', '#64748b').text('Saving settings...');

                var ajaxUrl = (window.zappoppop_settings_vars && window.zappoppop_settings_vars.ajax_url)
                    ? window.zappoppop_settings_vars.ajax_url
                    : ((window.zappoppop_vars && window.zappoppop_vars.ajax_url) ? window.zappoppop_vars.ajax_url : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php'));

                var nonce = (window.zappoppop_settings_vars && window.zappoppop_settings_vars.nonce)
                    ? window.zappoppop_settings_vars.nonce
                    : ((window.zappoppop_vars && window.zappoppop_vars.nonce) ? window.zappoppop_vars.nonce : $('#zappoppop_settings_nonce').val());

                var formData = $form.serializeArray();
                formData.push({ name: 'action', value: 'zappoppop_save_settings' });
                formData.push({ name: 'nonce', value: nonce });

                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(res) {
                        $btn.prop('disabled', false);
                        $barBtn.prop('disabled', false);
                        $barMsg.text('');
                        $spinner.removeClass('is-active');

                        if (res && res.success) {
                            var successText = (res.data && res.data.message) ? res.data.message : 'Settings saved successfully!';
                            $msg.css('color', '#16a34a').text('✓ ' + successText);
                            if ($alert.length) {
                                $alert.removeClass('notice-error').addClass('notice-success').show().find('p').text(successText);
                            }
                            setTimeout(function() {
                                $msg.fadeOut(300, function() { $(this).text('').show(); });
                            }, 3500);
                        } else {
                            var errMsg = (res && res.data && res.data.message) ? res.data.message : 'Failed to save settings.';
                            $msg.css('color', '#dc2626').text('✕ ' + errMsg);
                            if ($alert.length) {
                                $alert.removeClass('notice-success').addClass('notice-error').show().find('p').text(errMsg);
                            }
                        }
                    },
                    error: function() {
                        $btn.prop('disabled', false);
                        $barBtn.prop('disabled', false);
                        $barMsg.text('');
                        $spinner.removeClass('is-active');
                        $msg.css('color', '#dc2626').text('✕ Server communication error.');
                    }
                });
            });
        }
    };
})(jQuery);
