(function(window, $) {
    'use strict';
    window.ZapPopPopFront = window.ZapPopPopFront || {};

    var Submit = {
        init: function($popup, config) {
            var self = this;
            var uid = $popup.data('uid');

            // Form Submit Trigger
            $popup.on('click', '.zappoppop-submit-trigger', function(e) {
                e.preventDefault();
                self.processForm($popup, config);
            });

            // Instant Payment Trigger
            $popup.on('click', '.zappoppop-pay-trigger', function(e) {
                e.preventDefault();
                self.processPayment($popup, config, $(this));
            });

            // Signature Pad clearing
            $popup.find('.zappoppop-sig-clear-btn').on('click', function() {
                var canvas = $(this).siblings('.zappoppop-sig-canvas')[0];
                if (canvas) {
                    var ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    $(this).siblings('.zappoppop-sig-input').val('');
                }
            });

            this.initSignaturePad($popup);
        },

        initSignaturePad: function($popup) {
            $popup.find('.zappoppop-sig-canvas').each(function() {
                var canvas = this;
                var ctx = canvas.getContext('2d');
                var isDrawing = false;
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.strokeStyle = '#1e293b';

                $(canvas).on('mousedown touchstart', function(e) {
                    isDrawing = true;
                    var offset = $(canvas).offset();
                    var x = (e.pageX || e.originalEvent.touches[0].pageX) - offset.left;
                    var y = (e.pageY || e.originalEvent.touches[0].pageY) - offset.top;
                    ctx.beginPath();
                    ctx.moveTo(x, y);
                }).on('mousemove touchmove', function(e) {
                    if (!isDrawing) return;
                    var offset = $(canvas).offset();
                    var x = (e.pageX || e.originalEvent.touches[0].pageX) - offset.left;
                    var y = (e.pageY || e.originalEvent.touches[0].pageY) - offset.top;
                    ctx.lineTo(x, y);
                    ctx.stroke();
                }).on('mouseup touchend', function() {
                    isDrawing = false;
                    $(canvas).siblings('.zappoppop-sig-input').val(canvas.toDataURL());
                });
            });
        },

        showErrorBubble: function($field, msg) {
            $('.zappoppop-error-bubble').remove();
            var $bubble = $('<div class="zappoppop-error-bubble">' + msg + '</div>');
            $('body').append($bubble);

            var offset = $field.offset();
            $bubble.css({
                position: 'absolute',
                top: (offset.top - $bubble.outerHeight() - 8) + 'px',
                left: offset.left + 'px',
                background: '#ef4444',
                color: '#ffffff',
                padding: '6px 12px',
                borderRadius: '0px',
                fontSize: '12px',
                fontWeight: '600',
                zIndex: 9999999,
                boxShadow: '0 4px 12px rgba(239, 68, 68, 0.3)'
            }).fadeIn(200);

            setTimeout(function() { $bubble.fadeOut(300, function() { $bubble.remove(); }); }, 3500);
        },

        validate: function($popup) {
            var self = this;
            var isValid = true;
            var disposableDomains = ['mailinator.com', '10minutemail.com', 'guerrillamail.com', 'trashmail.com', 'tempmail.com', 'sharklasers.com'];

            // Anti-Spam Honeypot
            if ($popup.find('input[name="_zappoppop_hp_email"]').val()) {
                return false;
            }

            // Required field check
            $popup.find('.zappoppop-layer-item[data-required="1"]').each(function() {
                var $input = $(this).find('input, select, textarea');
                var val = $input.val();
                if (!val || (Array.isArray(val) && val.length === 0) || ($input.is(':checkbox') && !$input.is(':checked'))) {
                    self.showErrorBubble($input, $(this).data('error-msg') || 'This field is required');
                    isValid = false;
                    return false;
                }
            });

            /* ZAPPOPPOP_REQ_MSG: native required inputs with custom message */
            $popup.find('input[required], select[required], textarea[required]').each(function() {
                var $f = $(this);
                var v = $f.val();
                var empty = !v || (Array.isArray(v) && !v.length) || ($f.is(':checkbox') && !$f.is(':checked'));
                if (empty && $f.is(':visible')) {
                    self.showErrorBubble($f, $f.attr('data-error-msg') || 'This field is required.');
                    isValid = false;
                    return false;
                }
            });

            if (!isValid) return false;

            // Email format & Disposable Domain Validation
            var $emailInput = $popup.find('.zappoppop-field-email');
            if ($emailInput.length) {
                var emailVal = $emailInput.val().trim();
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if ((emailVal !== '' || $emailInput.first().is('[required]')) && !emailRegex.test(emailVal)) { /* ZAPPOPPOP_EMAIL_JS */
                    self.showErrorBubble($emailInput, 'Please enter a valid email address.');
                    return false;
                }
                var domain = emailVal.split('@')[1] ? emailVal.split('@')[1].toLowerCase() : '';
                if ($emailInput.first().attr('data-block-disposable') === '1' && disposableDomains.indexOf(domain) !== -1) {
                    self.showErrorBubble($emailInput, 'Disposable and temporary email addresses are not allowed.');
                    return false;
                }
            }

            return true;
        },

        processForm: function($popup, config) {
            var self = this;
            if (!this.validate($popup)) return;

            var uid = $popup.data('uid');
            var $btn = $popup.find('.zappoppop-submit-trigger');
            $btn.prop('disabled', true).text('Submitting...');

            var fields = {};
            $popup.find('input, select, textarea').each(function() {
                var name = $(this).attr('name');
                if (name && name !== '_zappoppop_hp_email') { /* ZAPPOPPOP_CB_SUBMIT */
                    if ((this.type === 'checkbox' || this.type === 'radio') && !this.checked) { return; }
                    if (/\[\]$/.test(name)) { (fields[name] = fields[name] || []).push($(this).val()); } else { fields[name] = $(this).val(); }
                }
            });

            var email = fields['email'] || $popup.find('.zappoppop-field-email').val() || '';
            var restUrl = (window.zappoppop_front_vars && window.zappoppop_front_vars.rest_url) || '';
            var ajaxUrl = (window.zappoppop_front_vars && window.zappoppop_front_vars.ajax_url) || '';

            var payload = {
                uid: uid,
                email: email,
                fields: fields,
                country: (window.zappoppop_front_vars && window.zappoppop_front_vars.visitor_country) || '',
                quiz_score: window.ZapPopPopFront.Logic.tokens['quiz_score'] || 0,
                nonce: (window.zappoppop_front_vars && window.zappoppop_front_vars.nonce) || ''
            };

            function handleSuccess(res) {
                $btn.prop('disabled', false).text('Submitted');

                // Mark submission cookie
                window.ZapPopPopFront.Display.setCookie('zappoppop_sub_' + uid, '1', 365);
                window.ZapPopPopFront.Display.setCookie('zappoppop_unlocked_' + uid, '1', 365);

                // Sound & Confetti
                if (window.ZapPopPopFront.Gamification) {
                    window.ZapPopPopFront.Gamification.playChime('success');
                    /* ZAPPOPPOP_CELEBRATE */
                    var zppCel = (config && (config.celebration || (config.settings && config.settings.celebration))) || {};
                    var zppEffect = zppCel.effect || 'default';
                    window.ZapPopPopFront.celebrationMode = zppEffect;
                    if (typeof window.ZapPopPopFront.Gamification.celebrate === 'function') {
                        window.ZapPopPopFront.Gamification.celebrate(zppEffect, $popup);
                    } else {
                        window.ZapPopPopFront.Gamification.launchConfetti($popup);
                    }
                }

                // Show status overlay
                var $status = $popup.find('.zappoppop-status-overlay');
                $status.find('.zappoppop-status-message').text((res.data && res.data.message) || res.message || 'Thank you! Submitted successfully.');
                $status.fadeIn(200);

                // WooCommerce: the server issued a coupon for this subscriber (module acc-woocommerce)
                var wcCode = res.coupon_code || (res.data && res.data.coupon_code) || '';
                if (wcCode && typeof wcCode === 'string') {
                    var $wcMsg = $status.find('.zappoppop-status-message');
                    $wcMsg.text($wcMsg.text() + ' Your coupon code: ' + wcCode);
                    if (ajaxUrl) {
                        $.post(ajaxUrl, {
                            action: 'zappoppop_apply_wc_coupon',
                            nonce: (window.zappoppop_front_vars && window.zappoppop_front_vars.nonce) || '',
                            coupon_code: wcCode
                        });
                    }
                }

                // Downloads: the server issued a one-time link for this subscriber (module acc-downloads)
                var dlUrl = res.download_url || (res.data && res.data.download_url) || '';
                if (dlUrl && typeof dlUrl === 'string' && dlUrl.indexOf('http') === 0) {
                    var $dlMsg = $status.find('.zappoppop-status-message');
                    $dlMsg.text($dlMsg.text() + ' Your download is starting.');
                    window.location.assign(dlUrl);
                }

                // Redirect or Auto-close
                setTimeout(function() {
                    var redir = (res.data && res.data.redirect_url) || res.redirect_url;
                    if (redir) {
                        window.location.href = redir;
                    } else {
                        window.ZapPopPopFront.Display.close($popup, config);
                    }
                }, 2000);
            }

            if (restUrl) {
                fetch(restUrl + 'submit', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                }).then(function(r) { return r.json().then(function(j) { return { ok: r.ok, json: j }; }); }).then(function(o) {
                    if (!o.ok || (o.json && o.json.code && !o.json.success)) {
                        $btn.prop('disabled', false).text(wp.i18n.__('Submit', 'zappoppop'));
                        alert((o.json && o.json.message) || wp.i18n.__('Submission failed. Please try again.', 'zappoppop'));
                        return;
                    }
                    handleSuccess(o.json);
                }).catch(function() {
                    $btn.prop('disabled', false).text(wp.i18n.__('Submit', 'zappoppop'));
                    alert(wp.i18n.__('Submission error. Please check your connection.', 'zappoppop'));
                });
            } else {
                $.post(ajaxUrl, Object.assign({ action: 'zappoppop_submit_form', nonce: (window.zappoppop_front_vars && window.zappoppop_front_vars.nonce) || '', popup_uid: uid }, payload)).done(function(res) {
                    if (res && res.success) { handleSuccess(res); } else { $btn.prop('disabled', false).text(wp.i18n.__('Submit', 'zappoppop')); alert((res && res.data && res.data.message) || wp.i18n.__('Submission failed.', 'zappoppop')); }
                }).fail(function() { $btn.prop('disabled', false).text(wp.i18n.__('Submit', 'zappoppop')); alert(wp.i18n.__('Submission error. Please check your connection.', 'zappoppop')); });
            }
        },

        processPayment: function($popup, config, $btn) {
            var uid = $popup.data('uid');
            var email = $popup.find('.zappoppop-field-email').val() || 'customer@example.com';
            var amt = $btn.data('amount') || '10.00';
            var cur = $btn.data('currency') || 'USD';
            var ajaxUrl = (window.zappoppop_front_vars && window.zappoppop_front_vars.ajax_url) || '';

            $btn.prop('disabled', true).text('Processing Payment...');

            $.post(ajaxUrl, {
                action: 'zappoppop_process_payment',
                uid: uid,
                email: email,
                amount: amt,
                currency: cur,
                gateway: $btn.data('gateway') || 'stripe'
            }).done(function(res) {
                if (res.success) {
                    alert(res.data.message || wp.i18n.__('Payment completed!', 'zappoppop'));
                    window.ZapPopPopFront.Display.close($popup, config);
                }
            }).always(function() {
                $btn.prop('disabled', false).text('Pay ' + amt + ' ' + cur);
            });
        }
    };

    window.ZapPopPopFront.Submit = Submit;
})(window, jQuery);
