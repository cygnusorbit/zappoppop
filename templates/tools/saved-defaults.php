<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}
$zpp_sd_status = ZapPopPop_Saved_Defaults::status();
$zpp_sd_rows   = [
    'settings' => [
        'title' => __('Settings defaults', 'zappoppop'),
        'desc'  => __('Used by "Reset Defaults" on the Settings page and for fresh installs. Emails, keys/secrets and custom code are never saved.', 'zappoppop'),
        'save'  => true,
    ],
    'theme'    => [
        'title' => __('Theme defaults', 'zappoppop'),
        'desc'  => __('Used by Revert/Reset on the Theme page and for fresh installs. The top-bar logo is not saved.', 'zappoppop'),
        'save'  => true,
    ],
    'popup'    => [
        'title' => __('New popup defaults', 'zappoppop'),
        'desc'  => __('Canvas size, background, animation and popup settings applied to every new popup (layers are not included). Save them from the builder: Settings drawer, "Save as default for new popups".', 'zappoppop'),
        'save'  => false,
    ],
    'layers'   => [
        'title' => __('Layer defaults', 'zappoppop'),
        'desc'  => __('Per layer type and Inspector tab (Content, Style, Logic, Data, Advanced): applied to every new layer of that type and used by that tab\'s Reset button. Save them from the builder: Layer Settings, "Save ... Settings as Default".', 'zappoppop'),
        'save'  => false,
    ],
];
?>
<div class="zappoppop-card-box zpp-tools-card" id="zappoppop-saved-defaults">
    <div class="zpp-tools-card-head">
        <span class="dashicons dashicons-star-filled"></span>
        <h3><?php esc_html_e('Saved Defaults', 'zappoppop'); ?></h3>
    </div>
    <p class="zpp-tools-help"><?php esc_html_e('Turn your current setup into the real defaults. They are stored on this site and in the plugin "defaults" folder, so they travel with the plugin.', 'zappoppop'); ?></p>

    <?php if (ZapPopPop_Saved_Defaults::dev_mode()) : ?>
        <p class="zpp-tools-help"><strong><?php esc_html_e('Development mode: every saved default is also written to defaults/defaults.json.', 'zappoppop'); ?></strong>
            <?php if (!ZapPopPop_Saved_Defaults::file_writable()) { esc_html_e('The file is currently read-only, so only the database copy is updated.', 'zappoppop'); } ?></p>
    <?php endif; ?>

    <div class="zpp-tools-grid zpp-tools-grid-3">
        <?php foreach ($zpp_sd_rows as $zpp_sd_scope => $zpp_sd_row) :
            $zpp_sd_st = $zpp_sd_status[$zpp_sd_scope]; ?>
            <div class="zpp-tools-panel">
                <h4><?php echo esc_html($zpp_sd_row['title']); ?></h4>
                <p class="zpp-tools-help"><?php echo esc_html($zpp_sd_row['desc']); ?></p>
                <p class="zpp-tools-help"><strong><?php esc_html_e('Status:', 'zappoppop'); ?></strong>
                    <?php if ($zpp_sd_st['saved']) :
                        $zpp_sd_when = ZapPopPop_Saved_Defaults::format_time($zpp_sd_st['time']);
                        echo esc_html(sprintf(
                            /* translators: 1: date/time, 2: source (site or plugin file) */
                            __('Saved %1$s (%2$s)', 'zappoppop'),
                            $zpp_sd_when !== '' ? $zpp_sd_when : __('earlier', 'zappoppop'),
                            $zpp_sd_st['source'] === 'file' ? __('plugin file', 'zappoppop') : __('this site', 'zappoppop')
                        ));
                    else :
                        esc_html_e('Built-in defaults', 'zappoppop');
                    endif; ?>
                </p>
                <?php if ($zpp_sd_scope === 'layers') : /* ZPP_TOOLS_FACTORY */
                    $zpp_sd_fac_ok = is_readable(ZapPopPop_Saved_Defaults::factory_file_path());
                    $zpp_sd_fac_n  = count(ZapPopPop_Saved_Defaults::factory_layers()); ?>
                    <p class="zpp-tools-help"><strong><?php esc_html_e('Factory file:', 'zappoppop'); ?></strong>
                        <?php if ($zpp_sd_fac_ok) :
                            echo esc_html(sprintf(
                                /* translators: 1: number of element types defined in defaults/layer-defaults.json */
                                _n('defaults/layer-defaults.json defines %1$d element type. New elements start from it; your saved layer defaults are applied on top. Clear never changes this file.', 'defaults/layer-defaults.json defines %1$d element types. New elements start from them; your saved layer defaults are applied on top. Clear never changes this file.', $zpp_sd_fac_n, 'zappoppop'),
                                $zpp_sd_fac_n
                            ));
                        else :
                            esc_html_e('defaults/layer-defaults.json was not found, so new elements start with generic defaults only.', 'zappoppop');
                        endif; ?>
                    </p>
                <?php endif; ?>
                <div class="zpp-tools-row">
                    <?php if ($zpp_sd_row['save']) : ?>
                        <button type="button" class="button" data-zpp-defaults-save="<?php echo esc_attr($zpp_sd_scope); ?>" data-zpp-defaults-reload="1"><?php esc_html_e('Save current as default', 'zappoppop'); ?></button>
                    <?php endif; ?>
                    <button type="button" class="button" data-zpp-defaults-clear="<?php echo esc_attr($zpp_sd_scope); ?>" data-zpp-defaults-reload="1" <?php disabled(!$zpp_sd_st['saved']); ?>><?php esc_html_e('Clear', 'zappoppop'); ?></button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>
