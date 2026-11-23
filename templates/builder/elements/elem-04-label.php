<?php
if (!defined('ABSPATH')) exit;
?>
<!-- DECOMPOSED MODULE 04: HEADER / HEADLINE / TEXT (label / title / paragraph) - ZPP_LK_OWN_BLOCK - ZPP_HEADER_CONTENT -->
<div class="element-panel zappoppop-panel-specific zpp-hc" id="panel-elem-label" data-elem-family="media" style="display:none;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:12px;margin-bottom:14px;">
    <div class="zpp-hc-head">
        <span class="zpp-hc-title"><?php esc_html_e('Header / Headline Content', 'zappoppop'); ?></span>
        <span class="zpp-hc-tok">
            <button type="button" id="zappoppop-label-token-btn" title="<?php esc_attr_e('Insert WordPress value', 'zappoppop'); ?>" aria-haspopup="true" class="zpp-tok-btn">&lt;/&gt;</button>
            <div id="zappoppop-label-token-menu" role="menu" class="zpp-tok-menu" style="display:none;"></div>
        </span>
    </div>

    <div class="zpp-hc-box" id="zpp-hc-box">
        <div class="zpp-hc-bar" id="zpp-hc-bar" role="toolbar" aria-label="<?php esc_attr_e('Text formatting', 'zappoppop'); ?>">
            <button type="button" class="zpp-hc-btn" data-zpp-cmd="clear" title="<?php esc_attr_e('Clear formatting', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Clear formatting', 'zappoppop'); ?>">&mdash;</button>
            <button type="button" class="zpp-hc-btn" data-zpp-cmd="bold" title="<?php esc_attr_e('Bold', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Bold', 'zappoppop'); ?>" aria-pressed="false"><b>B</b></button>
            <button type="button" class="zpp-hc-btn" data-zpp-cmd="italic" title="<?php esc_attr_e('Italic', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Italic', 'zappoppop'); ?>" aria-pressed="false"><i>I</i></button>
            <button type="button" class="zpp-hc-btn" data-zpp-cmd="underline" title="<?php esc_attr_e('Underline', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Underline', 'zappoppop'); ?>" aria-pressed="false"><span class="zpp-hc-u">T</span></button>
            <button type="button" class="zpp-hc-btn" data-zpp-cmd="strikeThrough" title="<?php esc_attr_e('Strikethrough', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Strikethrough', 'zappoppop'); ?>" aria-pressed="false"><span class="zpp-hc-s">T</span></button>
            <button type="button" class="zpp-hc-btn" data-zpp-cmd="superscript" title="<?php esc_attr_e('Superscript', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Superscript', 'zappoppop'); ?>" aria-pressed="false">x<sup>2</sup></button>
            <button type="button" class="zpp-hc-btn" data-zpp-cmd="subscript" title="<?php esc_attr_e('Subscript', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Subscript', 'zappoppop'); ?>" aria-pressed="false">x<sub>2</sub></button>
            <button type="button" class="zpp-hc-btn" data-zpp-cmd="link" title="<?php esc_attr_e('Link', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Link', 'zappoppop'); ?>" aria-pressed="false"><span class="dashicons dashicons-admin-links" aria-hidden="true"></span></button>
            <button type="button" class="zpp-hc-btn zpp-hc-btn-html" data-zpp-cmd="html" title="<?php esc_attr_e('Edit as HTML', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Edit as HTML', 'zappoppop'); ?>" aria-pressed="false">HTML</button>
        </div>
        <div class="zpp-hc-linkbar" id="zpp-hc-linkbar" hidden>
            <input type="text" id="zpp-hc-link-url" class="zappoppop-input" placeholder="https://example.com" autocomplete="off" spellcheck="false" aria-label="<?php esc_attr_e('Link URL', 'zappoppop'); ?>" />
            <button type="button" class="button button-small" id="zpp-hc-link-apply"><?php esc_html_e('Apply', 'zappoppop'); ?></button>
            <button type="button" class="button button-small" id="zpp-hc-link-remove"><?php esc_html_e('Unlink', 'zappoppop'); ?></button>
        </div>
        <div id="zpp-hc-editor" class="zpp-hc-area" contenteditable="true" role="textbox" aria-multiline="true" aria-label="<?php esc_attr_e('Header content', 'zappoppop'); ?>" data-placeholder="<?php esc_attr_e( 'Hello World', 'zappoppop' ); ?>" spellcheck="true"></div>
        <textarea id="prop-label-content" rows="4" class="widefat zappoppop-input zappoppop-gp-bind zpp-hc-src" data-prop="content" tabindex="-1" aria-label="<?php esc_attr_e('Header content (HTML)', 'zappoppop'); ?>" placeholder="<?php esc_attr_e( 'Hello World', 'zappoppop' ); ?>"></textarea>
        <div class="zpp-hc-note" id="zpp-hc-note" hidden><?php esc_html_e('This header contains custom HTML. Visual editing keeps only basic formatting and links.', 'zappoppop'); ?></div>
    </div>
    <input type="hidden" id="prop-title-text" class="zappoppop-gp-alias" data-prop="content">
    <input type="hidden" id="prop-paragraph-text" class="zappoppop-gp-alias" data-prop="content">
    <input type="hidden" id="prop-text-content" class="zappoppop-gp-alias" data-prop="content">

    <div class="zpp-hc-set">
        <button type="button" class="zpp-hc-sethead" aria-expanded="true" aria-controls="zpp-hc-setbody">
            <span><?php esc_html_e('Settings', 'zappoppop'); ?></span>
            <span class="dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span>
        </button>
        <div class="zpp-hc-setbody" id="zpp-hc-setbody">
            <div class="zpp-hc-row">
                <label for="prop-label-tag"><?php esc_html_e('Tag', 'zappoppop'); ?></label>
                <select id="prop-label-tag" class="zappoppop-input zappoppop-gp-bind" data-prop="tag">
                    <option value="div"><?php esc_html_e('DIV Container', 'zappoppop'); ?></option>
                    <option value="p"><?php esc_html_e('Paragraph (P)', 'zappoppop'); ?></option>
                    <option value="span"><?php esc_html_e('Inline SPAN', 'zappoppop'); ?></option>
                    <option value="h1"><?php esc_html_e('Heading H1', 'zappoppop'); ?></option>
                    <option value="h2"><?php esc_html_e('Heading H2', 'zappoppop'); ?></option>
                    <option value="h3"><?php esc_html_e('Heading H3', 'zappoppop'); ?></option>
                    <option value="h4"><?php esc_html_e('Heading H4', 'zappoppop'); ?></option>
                    <option value="h5"><?php esc_html_e('Heading H5', 'zappoppop'); ?></option>
                    <option value="h6"><?php esc_html_e('Heading H6', 'zappoppop'); ?></option>
                </select>
                <input type="hidden" id="prop-title-tag" class="zappoppop-gp-alias" data-prop="tag">
                <input type="hidden" id="prop-paragraph-tag" class="zappoppop-gp-alias" data-prop="tag">
                <input type="hidden" id="prop-text-tag" class="zappoppop-gp-alias" data-prop="tag">
            </div>
            <div class="zpp-hc-row">
                <label for="prop-label-transform"><?php esc_html_e('Text Transform', 'zappoppop'); ?></label>
                <select id="prop-label-transform" class="zappoppop-input zappoppop-gp-bind" data-prop="textTransform">
                    <option value="none"><?php esc_html_e('None (As Typed)', 'zappoppop'); ?></option>
                    <option value="uppercase"><?php esc_html_e('Uppercase', 'zappoppop'); ?></option>
                    <option value="lowercase"><?php esc_html_e('lowercase', 'zappoppop'); ?></option>
                    <option value="capitalize"><?php esc_html_e('Capitalize Words', 'zappoppop'); ?></option>
                </select>
                <input type="hidden" id="prop-title-transform" class="zappoppop-gp-alias" data-prop="textTransform">
            </div>
            <div class="zpp-hc-row zpp-hc-row-block">
                <?php /* ZPP_LK_OWN_BLOCK: URL + Open in new tab + rel="nofollow" = the shared link group (same keys as Logic > Action & Click Routing) */ $zappoppop_lk_id = 'label'; $zappoppop_lk_nofollow = false; include ZAPPOPPOP_PATH . 'templates/builder/elements/partial-link-group.php'; ?>
            </div>
            <div class="zpp-hc-row">
                <label for="prop-label-id"><?php esc_html_e('ID', 'zappoppop'); ?></label>
                <input type="text" id="prop-label-id" class="zappoppop-input zappoppop-gp-bind" data-prop="custom_css_id" maxlength="64" autocomplete="off" spellcheck="false" />
            </div>
            <div class="zpp-hc-row zpp-hc-row-attr">
                <span id="zpp-hc-attr-title"><?php esc_html_e('Attributes', 'zappoppop'); ?></span>
                <button type="button" id="zpp-hc-attr-add" class="zpp-hc-plus" title="<?php esc_attr_e('Add attribute', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Add attribute', 'zappoppop'); ?>"><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span></button>
            </div>
            <div class="zpp-hc-attrs" id="zpp-hc-attrs" aria-labelledby="zpp-hc-attr-title"></div>
            <datalist id="zpp-hc-attr-names">
                <option value="role"></option>
                <option value="title"></option>
                <option value="tabindex"></option>
                <option value="lang"></option>
                <option value="dir"></option>
                <option value="aria-label"></option>
                <option value="aria-labelledby"></option>
                <option value="aria-describedby"></option>
                <option value="aria-hidden"></option>
                <option value="data-"></option>
            </datalist>
        </div>
    </div>
</div>
