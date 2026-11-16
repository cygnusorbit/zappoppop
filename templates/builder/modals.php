<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- Live Sandbox Preview Modal -->
<div class="zappoppop-modal-backdrop" id="zappoppop-builder-preview-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.8);backdrop-filter:blur(4px);z-index:999999;align-items:center;justify-content:center;">
    <div style="background:#ffffff;border-radius:8px;width:720px;max-width:95vw;max-height:90vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 18px;background:#0f172a;color:#ffffff;">
            <h3 style="margin:0;font-size:14px;font-weight:700;"><?php esc_html_e('Interactive Sandbox Preview', 'zappoppop'); ?></h3>
            <button type="button" id="zappoppop-builder-preview-close" style="background:transparent;border:none;color:#94a3b8;font-size:22px;cursor:pointer;line-height:1;">&times;</button>
        </div>
        <div id="zappoppop-preview-sandbox-root" style="flex:1;overflow:auto;padding:30px;display:flex;align-items:center;justify-content:center;background:#f1f5f9;"></div>
    </div>
</div>

<!-- How to Embed & Display Popup -->
<div class="zappoppop-modal-backdrop" id="zappoppop-modal-embed" style="display:none;">
    <div class="zappoppop-modal-dialog zappoppop-modal-embed-dialog" role="dialog" aria-modal="true" aria-labelledby="zappoppop-embed-title">
        <div class="zappoppop-modal-header">
            <h3 id="zappoppop-embed-title"><span class="dashicons dashicons-code-standards"></span> <?php esc_html_e('How to Embed & Display Popup', 'zappoppop'); ?></h3>
            <button type="button" class="zappoppop-modal-close" data-modal="zappoppop-modal-embed" aria-label="<?php esc_attr_e('Close', 'zappoppop'); ?>">&times;</button>
        </div>
        <div class="zappoppop-modal-body">
            <div class="zappoppop-embed-tabs">
                <button type="button" class="zappoppop-embed-tab active" data-tab="shortcode"><?php esc_html_e('Shortcode', 'zappoppop'); ?></button>
                <button type="button" class="zappoppop-embed-tab" data-tab="onclick"><?php esc_html_e('On-Click Link', 'zappoppop'); ?></button>
                <button type="button" class="zappoppop-embed-tab" data-tab="js-api"><?php esc_html_e('JavaScript API', 'zappoppop'); ?></button>
            </div>

            <div class="zappoppop-embed-notice" id="zappoppop-embed-unsaved" style="display:none;">
                <?php esc_html_e('This popup has not been saved yet. Click "Save Popup" first so the snippets below contain its real ID.', 'zappoppop'); ?>
            </div>

            <div class="zappoppop-embed-notice" id="zappoppop-embed-draft" style="display:none;">
                <?php esc_html_e('This popup is a Draft, so the snippets below output nothing on your site yet. Set its status to Published from the Popups list (Bulk Actions > Set Status: Published).', 'zappoppop'); ?>
            </div>

            <div class="zappoppop-embed-tab-content active" id="embed-tab-shortcode">
                <p class="zappoppop-embed-desc"><?php esc_html_e('Paste this shortcode anywhere inside your WordPress page, post, or template:', 'zappoppop'); ?></p>
                <div class="zappoppop-code-box">
                    <input type="text" id="zappoppop-embed-shortcode-val" readonly value="">
                    <button type="button" class="zappoppop-btn-copy" data-target="zappoppop-embed-shortcode-val">
                        <span class="dashicons dashicons-clipboard"></span> <?php esc_html_e('Copy', 'zappoppop'); ?>
                    </button>
                </div>
                <div class="zappoppop-embed-guide">
                    <h4><?php esc_html_e('How to use', 'zappoppop'); ?></h4>
                    <ol>
                        <li><?php esc_html_e('Click Save Popup and make sure its status is Published.', 'zappoppop'); ?></li>
                        <li><?php esc_html_e('Edit the page or post and add a Shortcode block (or use the Classic editor).', 'zappoppop'); ?></li>
                        <li><?php esc_html_e('Paste the shortcode, update the page, then view it on your site.', 'zappoppop'); ?></li>
                    </ol>
                    <h4><?php esc_html_e('Recommended', 'zappoppop'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('Use the shortcode to embed the popup inline inside content, such as landing pages and sidebars.', 'zappoppop'); ?></li>
                        <li><?php esc_html_e('Use only one inline copy of the same popup per page.', 'zappoppop'); ?></li>
                        <li><?php esc_html_e('Want a button that opens it as a popup instead? Use this variant:', 'zappoppop'); ?>
                            <code class="zappoppop-embed-ex">[zappoppop uid="<span class="zappoppop-embed-uid"></span>" mode="button" text="Get My Offer"]</code>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="zappoppop-embed-tab-content" id="embed-tab-onclick" style="display:none;">
                <p class="zappoppop-embed-desc"><?php esc_html_e('Attach this HTML snippet to any anchor link, button, or menu item to open the popup upon click:', 'zappoppop'); ?></p>
                <div class="zappoppop-code-box">
                    <input type="text" id="zappoppop-embed-onclick-val" readonly value="">
                    <button type="button" class="zappoppop-btn-copy" data-target="zappoppop-embed-onclick-val">
                        <span class="dashicons dashicons-clipboard"></span> <?php esc_html_e('Copy', 'zappoppop'); ?>
                    </button>
                </div>
                <div class="zappoppop-embed-guide">
                    <h4><?php esc_html_e('How to use', 'zappoppop'); ?></h4>
                    <ol>
                        <li><?php esc_html_e('Add a Custom HTML block (or an HTML widget in your page builder) where the link should appear.', 'zappoppop'); ?></li>
                        <li><?php esc_html_e('Paste the snippet and change the link text if you like.', 'zappoppop'); ?></li>
                        <li><?php esc_html_e('Menu item: Appearance > Menus > Custom Links, set the URL to:', 'zappoppop'); ?>
                            <code class="zappoppop-embed-ex">#zappoppop-<span class="zappoppop-embed-uid"></span></code>
                        </li>
                    </ol>
                    <h4><?php esc_html_e('Recommended', 'zappoppop'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('Works on any element. Add the data attribute to an existing button:', 'zappoppop'); ?>
                            <code class="zappoppop-embed-ex">&lt;button type="button" data-zappoppop-id="<span class="zappoppop-embed-uid"></span>"&gt;Open&lt;/button&gt;</code>
                        </li>
                        <li><?php esc_html_e('A click always reopens the popup, even if the visitor closed it earlier.', 'zappoppop'); ?></li>
                        <li><?php esc_html_e('Keep the class "zappoppop-trigger" and add your own classes for styling.', 'zappoppop'); ?></li>
                    </ul>
                </div>
            </div>

            <div class="zappoppop-embed-tab-content" id="embed-tab-js-api" style="display:none;">
                <p class="zappoppop-embed-desc"><?php esc_html_e('Trigger this popup programmatically via custom JavaScript or third-party webhooks:', 'zappoppop'); ?></p>
                <div class="zappoppop-code-box">
                    <input type="text" id="zappoppop-embed-js-val" readonly value="">
                    <button type="button" class="zappoppop-btn-copy" data-target="zappoppop-embed-js-val">
                        <span class="dashicons dashicons-clipboard"></span> <?php esc_html_e('Copy', 'zappoppop'); ?>
                    </button>
                </div>
                <div class="zappoppop-embed-guide">
                    <h4><?php esc_html_e('How to use', 'zappoppop'); ?></h4>
                    <ol>
                        <li><?php esc_html_e('Place the code in your theme script, a footer-scripts plugin, or an event callback.', 'zappoppop'); ?></li>
                        <li><?php esc_html_e('Run it after the page has loaded, for example inside a load event or a button click.', 'zappoppop'); ?></li>
                    </ol>
                    <h4><?php esc_html_e('Available methods', 'zappoppop'); ?></h4>
                    <ul>
                        <li><code>ZapPopPop.show(uid)</code> &ndash; <?php esc_html_e('open the popup', 'zappoppop'); ?></li>
                        <li><code>ZapPopPop.hide(uid)</code> &ndash; <?php esc_html_e('close the popup', 'zappoppop'); ?></li>
                        <li><code>ZapPopPop.toggle(uid)</code> / <code>ZapPopPop.isOpen(uid)</code></li>
                    </ul>
                    <h4><?php esc_html_e('Recommended', 'zappoppop'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('Keep the "window.ZapPopPop &&" guard so your script never errors if the plugin is inactive.', 'zappoppop'); ?></li>
                        <li><?php esc_html_e('Example, open after 5 seconds:', 'zappoppop'); ?>
                            <code class="zappoppop-embed-ex">setTimeout(function(){ window.ZapPopPop &amp;&amp; window.ZapPopPop.show('<span class="zappoppop-embed-uid"></span>'); }, 5000);</code>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Save As Template -->
<?php if (!class_exists('ZapPopPop_Ajax_Templates')) { require_once ZAPPOPPOP_PATH . 'includes/ajax/class-ajax-templates.php'; } ?>
<?php ZapPopPop_Inline_Assets::style('builder-modals'); ?><div class="zappoppop-modal-backdrop" id="zappoppop-modal-save-template" style="display:none;">
    <div class="wpt-dlg" role="dialog" aria-modal="true" aria-labelledby="zappoppop-tpl-title">
        <div class="wpt-hd">
            <h3 id="zappoppop-tpl-title"><?php esc_html_e('Save As Template', 'zappoppop'); ?></h3>
            <button type="button" class="wpt-x" id="zappoppop-tpl-close" aria-label="<?php esc_attr_e('Close', 'zappoppop'); ?>">&times;</button>
        </div>
        <div class="wpt-bd">
            <div class="wpt-grid">
                <div class="wpt-a-img">
                    <span class="wpt-l"><?php esc_html_e('Popup Image', 'zappoppop'); ?></span>
                    <div class="wpt-prev" id="zappoppop-tpl-prev" role="button" tabindex="0" title="<?php esc_attr_e('Click to upload', 'zappoppop'); ?>"><?php esc_html_e('Click to upload image', 'zappoppop'); ?></div>
                    <input type="hidden" id="zappoppop-tpl-image" value="">
                </div>
                <div class="wpt-a-btns">
                    <button type="button" class="wpt-btn wpt-btn-blue" id="zappoppop-tpl-upload"><?php esc_html_e('Upload Image', 'zappoppop'); ?></button>
                    <button type="button" class="wpt-btn wpt-btn-ghost" id="zappoppop-tpl-remove-img"><?php esc_html_e('Remove', 'zappoppop'); ?></button>
                </div>
                <div class="wpt-a-name">
                    <label class="wpt-l" for="zappoppop-tpl-name"><?php esc_html_e('Popup Name', 'zappoppop'); ?></label>
                    <input type="text" class="wpt-in" id="zappoppop-tpl-name" maxlength="120">
                </div>
                <div class="wpt-a-cat">
                    <label class="wpt-l" for="zappoppop-tpl-category"><?php esc_html_e('Category', 'zappoppop'); ?></label>
                    <select class="wpt-in" id="zappoppop-tpl-category">
                        <?php foreach (ZapPopPop_Ajax_Templates::get_categories() as $zpp_tc) : ?>
                            <option value="<?php echo esc_attr($zpp_tc['slug']); ?>"<?php selected($zpp_tc['slug'], 'custom'); ?>><?php echo esc_html($zpp_tc['label']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="wpt-a-desc">
                    <label class="wpt-l" for="zappoppop-tpl-desc"><?php esc_html_e('Description', 'zappoppop'); ?></label>
                    <textarea class="wpt-in" id="zappoppop-tpl-desc" maxlength="400"></textarea>
                </div>
                <div class="wpt-a-size">
                    <span class="wpt-l"><?php esc_html_e('Canvas Size', 'zappoppop'); ?></span>
                    <div class="wpt-ro" id="zappoppop-tpl-size"></div>
                </div>
                <div class="wpt-a-used">
                    <span class="wpt-l"><?php esc_html_e('Used Element(s)', 'zappoppop'); ?> <span id="zappoppop-tpl-total"></span></span>
                    <div class="wpt-box" id="zappoppop-tpl-elements"></div>
                </div>
            </div>
            <div class="wpt-msg" id="zappoppop-tpl-msg"></div>
        </div>
        <div class="wpt-ft">
            <button type="button" class="wpt-btn wpt-btn-ghost" id="zappoppop-tpl-cancel"><?php esc_html_e('Cancel', 'zappoppop'); ?></button>
            <button type="button" class="wpt-btn wpt-btn-green" id="zappoppop-tpl-submit"><?php esc_html_e('Save Template', 'zappoppop'); ?></button>
        </div>
    </div>
</div>
