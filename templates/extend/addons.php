<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}
$zappoppop_addons = ZapPopPop_Addon_Catalog::items();
if (!$zappoppop_addons) {
    return;
}
?>
<section class="zpp-ext-card zpp-ext-addons" aria-labelledby="zpp-ext-addons-title">
    <div class="zpp-ext-card-head">
        <span class="dashicons dashicons-admin-plugins" aria-hidden="true"></span>
        <h3 id="zpp-ext-addons-title"><?php esc_html_e('Add-ons', 'zappoppop'); ?></h3>
    </div>
    <p class="zpp-ext-help"><?php esc_html_e('Add-ons are separate plugins. WordPress installs and updates them, so ZapPopPop never accepts uploaded code. Modules from an active add-on appear in the list above.', 'zappoppop'); ?></p>
    <div class="zpp-mod-notice zpp-ext-addon-info" role="note">
        <span class="dashicons dashicons-info" aria-hidden="true"></span>
        <span><?php esc_html_e('Once an add-on is activated, its module appears in the builder under Popup Settings.', 'zappoppop'); ?></span>
    </div>
    <div class="zpp-ext-grid zpp-ext-addon-grid">
        <?php foreach ($zappoppop_addons as $zappoppop_it) :
            $zappoppop_status = ZapPopPop_Addon_Catalog::status($zappoppop_it);
            $zappoppop_act    = ZapPopPop_Addon_Catalog::action($zappoppop_it, $zappoppop_status);
            ?>
            <div class="zpp-ext-item" data-addon="<?php echo esc_attr($zappoppop_it['slug']); ?>">
                <div class="zpp-ext-item-top">
                    <span class="zpp-ext-item-img is-empty"><span class="dashicons <?php echo esc_attr($zappoppop_it['icon']); ?>" aria-hidden="true"></span></span>
                    <div class="zpp-ext-item-head">
                        <h3 class="zpp-ext-item-name"><?php echo esc_html($zappoppop_it['name']); ?></h3>
                        <?php if ($zappoppop_it['badge'] !== '') : ?>
                            <span class="zpp-mod-pill"><?php echo esc_html($zappoppop_it['badge']); ?></span>
                        <?php endif; ?>
                        <?php if ($zappoppop_status === 'active') : ?>
                            <span class="zpp-mod-pill is-ok"><?php esc_html_e('Active', 'zappoppop'); ?></span>
                        <?php elseif ($zappoppop_status === 'inactive') : ?>
                            <span class="zpp-mod-pill is-warn"><?php esc_html_e('Inactive', 'zappoppop'); ?></span>
                        <?php else : ?>
                            <span class="zpp-mod-pill is-lock"><?php esc_html_e('Not installed', 'zappoppop'); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($zappoppop_it['description'] !== '') : ?>
                    <p class="zpp-ext-item-desc"><?php echo esc_html($zappoppop_it['description']); ?></p>
                <?php endif; ?>
                <?php if ($zappoppop_act && $zappoppop_status === 'missing') : ?>
                    <div class="zpp-ext-item-actions">
                        <a class="button<?php echo $zappoppop_act['primary'] ? ' button-primary' : ''; ?>" href="<?php echo esc_url($zappoppop_act['url']); ?>"<?php echo $zappoppop_act['external'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?><?php echo $zappoppop_act['act'] !== '' ? ' data-zpp-act="' . esc_attr($zappoppop_act['act']) . '"' : ''; ?>>
                            <?php echo esc_html($zappoppop_act['label']); ?>
                            <?php if ($zappoppop_act['external']) : ?>
                                <span class="screen-reader-text"><?php esc_html_e('(opens in a new tab)', 'zappoppop'); ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if ($zappoppop_status !== 'missing') :
                    $zappoppop_ver = ZapPopPop_Addon_Catalog::version($zappoppop_it);
                    $zappoppop_on  = ($zappoppop_status === 'active');
                    ?>
                    <div class="zpp-ext-item-actions zpp-ext-item-installed">
                        <span class="zpp-ext-item-ver">
                            <?php
                            if ($zappoppop_ver !== '') {
                                /* translators: %s: add-on version number */
                                echo esc_html(sprintf(__('Version: %s', 'zappoppop'), $zappoppop_ver));
                            }
                            ?>
                        </span>
                        <?php if (current_user_can('activate_plugins')) : ?>
                            <span class="zpp-ext-switch-wrap">
                                <span class="zpp-ext-switch-label"><?php echo $zappoppop_on ? esc_html__('Activated', 'zappoppop') : esc_html__('Deactivated', 'zappoppop'); ?></span>
                                <a class="zpp-ext-switch<?php echo $zappoppop_on ? ' is-on' : ''; ?>" role="switch" aria-checked="<?php echo $zappoppop_on ? 'true' : 'false'; ?>" href="<?php echo esc_url(ZapPopPop_Addon_Catalog::toggle_url($zappoppop_it, $zappoppop_on)); ?>">
                                    <span class="screen-reader-text"><?php echo $zappoppop_on ? esc_html__('Deactivate', 'zappoppop') : esc_html__('Activate', 'zappoppop'); ?></span>
                                </a>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
