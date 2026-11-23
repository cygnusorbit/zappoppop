/**
 * ZapPopPop Builder Registry Module: registry-label
 * WordPress value tokens + URL link support for the Label element.
 */
(function(window, $) {
    'use strict';

    var TOKENS = [
        { t: "{{record-id}}", l: "Record ID" },
        { t: "{{ip}}", l: wp.i18n.__("IP Address", 'zappoppop') },
        { t: "{{user-agent}}", l: "User Agent" },
        { t: "{{date}}", l: "Date" },
        { t: "{{time}}", l: "Time" },
        { t: "{{wp-user-login}}", l: "WP User Login" },
        { t: "{{wp-user-email}}", l: "WP User Email" },
        { t: "{{url}}", l: "Current URL" },
        { t: "{{page-title}}", l: "Current Page Title" }
    ];

    function buildMenu() {
        var $m = $("#zappoppop-label-token-menu");
        if (!$m.length || $m.children().length) return;
        $.each(TOKENS, function(i, o) {
            $m.append($('<div class="zappoppop-token-item" role="menuitem" tabindex="0"></div>')
                .attr("data-token", o.t).text(o.l)
                ); /* ZPP_TOKEN_UI: colours come from the theme CSS (late stylesheet), no dark inline colours */
        });
    }

    function insertAtCursor(token) {
        var ta = document.getElementById("prop-label-content");
        if (!ta) return;
        var v = ta.value || "";
        var s = (typeof ta.selectionStart === "number") ? ta.selectionStart : v.length;
        var e = (typeof ta.selectionEnd === "number") ? ta.selectionEnd : v.length;
        ta.value = v.substring(0, s) + token + v.substring(e);
        var pos = s + token.length;
        try { ta.focus(); ta.setSelectionRange(pos, pos); } catch (err) {}
        // Triggers the shared gp-bind handler: JS model (el.content) + stage re-render
        $(ta).trigger("input").trigger("change");
        if (window.ZapPopPopBuilderCore && typeof window.ZapPopPopBuilderCore.pushHistory === "function") {
            window.ZapPopPopBuilderCore.pushHistory();
        }
    }

    function bind() {
        buildMenu();
        $(document).off("click.zappoppopLabelTok");
        $(document).on("click.zappoppopLabelTok", "#zappoppop-label-token-btn", function(ev) {
            ev.preventDefault(); ev.stopPropagation();
            buildMenu();
            $("#zappoppop-label-token-menu").toggle();
        });
        $(document).on("click.zappoppopLabelTok", "#zappoppop-label-token-menu .zappoppop-token-item", function(ev) {
            ev.preventDefault(); ev.stopPropagation();
            insertAtCursor($(this).attr("data-token"));
            $("#zappoppop-label-token-menu").hide();
        });
        $(document).on("click.zappoppopLabelTok", function(ev) {
            if (!$(ev.target).closest("#zappoppop-label-token-btn, #zappoppop-label-token-menu").length) {
                $("#zappoppop-label-token-menu").hide();
            }
        });
        // Keep url/targetBlank aliases in the model (data lifecycle)
        $(document).on("input.zappoppopLabelTok change.zappoppopLabelTok", "#prop-label-url, #prop-label-blank", function() {
            var Reg = window.ZapPopPopElementsRegistry;
            var el = Reg && typeof Reg.resolveElement === "function" ? Reg.resolveElement() : null;
            if (!el) return;
            if (this.id === "prop-label-url") { el.url = $.trim($(this).val()); el.linkUrl = el.url; }
            else { el.targetBlank = $(this).is(":checked"); el.linkBlank = el.targetBlank; }
        });
    }

    $(function() { bind(); setTimeout(bind, 150); });
})(window, jQuery);
