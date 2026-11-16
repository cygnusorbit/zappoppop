<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ZapPopPop Hooks & Extensibility Event Bus Registry
 * Catalogs and standardizes all actions and filter hooks for developers and add-ons.
 */
class ZapPopPop_Hooks {

    public function __construct() {
        // Core initialization hook point
        do_action('zappoppop_init');
    }

    /**
     * Retrieve the catalog of official ZapPopPop action and filter hooks.
     *
     * @return array
     */
    public static function get_registered_hooks() {
        return [
            'filters' => [
                'zappoppop_pre_save_popup_data'     => 'Filters popup configuration array before persisting to database.',
                'zappoppop_load_popup_config'       => 'Filters loaded popup configuration before returning to visual builder.',
                'zappoppop_export_campaign_payload' => 'Filters JSON export payload prior to download.',
                'zappoppop_pre_import_campaign_data'=> 'Filters uploaded campaign data prior to import insertion.',
                'zappoppop_pre_process_submission'  => 'Filters incoming lead form field data before database insertion.',
                'zappoppop_autoresponder_mail'      => 'Filters autoresponder email arguments (array: to, subject, body, headers).',
                'zappoppop_webhook_payload'         => 'Filters outbound HTTP webhook payload before transmission.',
                'zappoppop_visitor_country'         => 'Filters detected two-letter ISO country code for visitor targeting.',
                'zappoppop_targeting_decision'      => 'Filters the final boolean display determination for a popup campaign.',
                'zappoppop_pro_active'              => 'Companion plugin reports that it is active (bool).',
                'zappoppop_factory_defaults'        => 'Filters the factory defaults of the global settings (array).',
                'zappoppop_sanitize_global_settings'=> 'Filters the sanitised global settings before they are stored (array, raw input).',
                'zappoppop_settings_tab_keys'       => 'Filters the option keys each settings tab resets (array of tab => keys).',
                'zappoppop_submission_gate'         => 'Lets add-ons reject a lead: return an error message (string) to block it; stage is request or fields (message, stage, context).',
                'zappoppop_front_vars'              => 'Filters the zappoppop_front_vars object printed for the frontend scripts (array).',
                'zappoppop_pro_licensed'            => 'Companion plugin reports a valid licence (bool).',
                'zappoppop_feature_enabled'         => 'Companion plugin switches a feature on or off (bool, feature key).',
                'zappoppop_layer_elements'          => 'Filters the element type => label list used by the builder.',
                'zappoppop_layer_form_types'        => 'Filters the list of form-field element types.',
                'zappoppop_layer_style_layout'      => 'Filters the per-element Style tab layout map.',
                'zappoppop_layer_catalogue'         => 'Filters the layer-settings catalogue (tab => entries) before it is cached.',
                'zappoppop_module_roots'            => 'Extra module folders: list of array(path, url).',
                'zappoppop_preset_roots'            => 'Extra library template folders: list of array(path, url).',
                'zappoppop_render_element'          => 'Renders an element type that core does not handle (escaped HTML).',
                'zappoppop_builder_payload'         => 'Filters the data passed to the builder JavaScript.',
                'zappoppop_bar_links'               => 'Filters the dashboard bar links: list of array(page slug, label, dashicon).',
                'zappoppop_tools_tables'            => 'Filters the database tables listed in Tools: key => array(label, name).',
                'zappoppop_export_data'             => 'Filters the full backup payload before download.',
                'zappoppop_import_has_data'         => 'Filters whether an uploaded backup contains restorable data (bool, decoded payload).',
                'zappoppop_import_extra'            => 'Restores extra backup data; return the counts array or a WP_Error to roll back.',
                'zappoppop_system_report_lines'     => 'Filters the extra text lines of the Tools system report.',
                'zappoppop_extension_settings_keys' => 'Filters the popup-settings keys owned by extension modules (list of keys).',
                'zappoppop_sanitize_popup_settings' => 'Allow-lists the extension popup settings on save and on template save / import (settings object, context array).',
                'zappoppop_handled_settings_keys'   => 'Extensions list the settings keys they sanitised; other extension keys keep their stored value.',
                'zappoppop_popup_wrapper_attrs'     => 'Adds data-* attributes to the popup wrapper (attribute string, settings, args).',
                'zappoppop_popup_inline_css'        => 'Filters the popup inline CSS before minifying (css, settings, uid).',
                'zappoppop_popup_overlay'           => 'Filters the popup backdrop: array(css, layers) (default, settings).',
                'zappoppop_popup_position'          => 'Filters the popup position: array(css, pos) (default, settings).',
                'zappoppop_pay_button_attrs'        => 'Filters the data-* attributes of a Pay layer button (attribute string, element).',
                'zappoppop_popup_eligible'          => 'Filters whether a published popup may be shown (bool, popup row, decoded config).',
                'zappoppop_submission_precheck'     => 'Antibot / technical info before a lead is stored: array(deny, tech, mail_skip) (default, uid, fields, config).',
                'zappoppop_double_optin_config'     => 'Double Opt-In state of a popup: array(active, cfg) (default, config).',
                'zappoppop_double_optin_send'       => 'Sends the Double Opt-In confirmation email (bool sent, args array).',
                'zappoppop_submission_extras'       => 'Values added to the form response: array(download_url, coupon_code) (default, context).',
                'zappoppop_confirmation_thank'      => 'Thank-you text / address after a confirmed email: array(message, url) (default, config, email).',
                'zappoppop_resend_user_email'       => 'Submissions > Resend: sends the user email (bool sent, submission, config, email, fields, id).',
                'zappoppop_notification_tech_rows'  => 'Technical-info rows allowed in notification emails (rows, tech fields, config).',
                'zappoppop_submission_record_fields'=> 'Filters the fields shown on a submission detail (fields, popup uid).',
                'zappoppop_redact_config'           => 'Removes private account data from a popup config before it is printed or exported (config).',
                'zappoppop_diagnostic_sms'          => 'Sends the diagnostic test SMS (result, to number, credentials).',
                'zappoppop_site_tables'             => 'Multisite: table suffixes dropped when a site is deleted (list; companion plugins add theirs).',
            ],
            'actions' => [
                'zappoppop_init'                    => 'Fires when the ZapPopPop core system completes initialization.',
                'zappoppop_loaded'                  => 'Fires after all core components are created; companion plugins boot here.',
                'zappoppop_admin_menu'              => 'Fires after core submenus are added; receives the parent slug.',
                'zappoppop_admin_builder_assets'    => 'Fires after builder scripts are enqueued; companion plugins enqueue theirs here.',
                'zappoppop_builder_ribbon_tools'    => 'Prints extra element buttons in the builder ribbon.',
                'zappoppop_builder_element_panels'  => 'Prints extra element inspector panels in the builder.',
                'zappoppop_register_element_sanitizers' => 'Companion plugins register backend element sanitizers here.',
                'zappoppop_before_save_popup'       => 'Fires immediately before a popup is inserted or updated in the database.',
                'zappoppop_after_save_popup'        => 'Fires immediately after a popup is successfully saved.',
                'zappoppop_after_duplicate_popup'   => 'Fires after a popup campaign is cloned.',
                'zappoppop_before_delete_popup'      => 'Fires before a campaign and its linked records are deleted.',
                'zappoppop_after_delete_popup'       => 'Fires after a campaign and its submissions have been deleted.',
                'zappoppop_before_submission_save'  => 'Fires before a new lead submission is written to the database.',
                'zappoppop_after_submission_saved'   => 'Fires after a new lead is inserted into wp_zappoppop_submissions.',
                'zappoppop_webhook_dispatched'      => 'Fires after an outbound webhook request is sent (non-blocking: args are the response stub, payload, popup uid).',
                'zappoppop_create_tables'           => 'Fires while core creates its tables; companion plugins create theirs here.',
                'zappoppop_site_setup'              => 'Fires once for each site that gets set up: on activation, network activation and new-site creation (blog id).',
                'zappoppop_impression_recorded'     => 'Fires after a popup impression is counted (popup uid).',
                'zappoppop_submission_saved'        => 'Fires right after a lead row is inserted (submission id).',
                'zappoppop_popup_uid_renamed'       => 'Fires after a popup uid is renamed (old uid, new uid).',
                'zappoppop_tracking_tab_sections'   => 'Prints extra sections in Settings > Tracking (value getter, checkbox row, number row).',
                'zappoppop_settings_performance_rows' => 'Prints extra table rows in Settings > Speed (settings array).',
                'zappoppop_settings_library_extensions' => 'Prints extra checkboxes inside Library Extensions (settings array).',
                'zappoppop_settings_tab_buttons'    => 'Prints extra tab buttons in the settings navigation (after Typography & Libraries).',
                'zappoppop_settings_tab_panels'     => 'Prints extra tab panels inside the settings form (settings array).',
                'zappoppop_front_enqueue'           => 'Fires on public pages after core frontend scripts are enqueued; add-ons enqueue theirs here.',
                'zappoppop_front_footer'            => 'Fires in wp_footer when popups may run; add-ons print footer markup here.',
                'zappoppop_after_save_ab_campaign'  => 'Fires after an A/B split-testing campaign is created.',
                'zappoppop_after_delete_ab_campaign'=> 'Fires after an A/B split-testing campaign is removed.',
                'zappoppop_submission_dispatch'     => 'Fires after a lead is stored and the Notifications module ran; extension modules send their messages here (context).',
                'zappoppop_after_confirmation'      => 'Fires after a Double Opt-In link was confirmed (context).',
            ]
        ];
    }
}
