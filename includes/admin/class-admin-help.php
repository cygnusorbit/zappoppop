<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ZapPopPop Native Contextual Help Tabs Controller
 * Integrates WordPress core WP_Screen::add_help_tab() and WP_Screen::set_help_sidebar().
 */
class ZapPopPop_Admin_Help {

    public function __construct() {
        add_action('current_screen', [$this, 'add_help_tabs']);
    }

    /**
     * Contextual help router based on current screen ID.
     *
     * @param WP_Screen $screen Current WordPress screen object.
     */
    public function add_help_tabs($screen) {
        if (!$screen || strpos($screen->id, 'zappoppop') === false) {
            return;
        }

        // Set persistent contextual sidebar for all ZapPopPop administration screens
        $screen->set_help_sidebar(
            '<p><strong>' . __('For more information:', 'zappoppop') . '</strong></p>' .
            '<p><a href="https://github.com/cygnusorbit/zappoppop" target="_blank">' . __('Documentation & GitHub', 'zappoppop') . '</a></p>' .
            '<p><a href="https://github.com/cygnusorbit/zappoppop/issues" target="_blank">' . __('Support & Issue Tracker', 'zappoppop') . '</a></p>'
        );

        if ($screen->id === 'toplevel_page_zappoppop') {
            $this->add_dashboard_help($screen);
        } elseif (strpos($screen->id, 'zappoppop-builder') !== false) {
            $this->add_builder_help($screen);
        } elseif (strpos($screen->id, 'zappoppop-submissions') !== false) {
            $this->add_submissions_help($screen);
        } elseif (strpos($screen->id, 'zappoppop-settings') !== false) {
            $this->add_settings_help($screen);
        }
    }

    /**
     * Help tabs for Main Campaigns Dashboard.
     */
    protected function add_dashboard_help($screen) {
        $screen->add_help_tab([
            'id'      => 'zappoppop_dash_overview',
            'title'   => __('Overview', 'zappoppop'),
            'content' => '<p>' . __('This screen provides an overview of all popup marketing campaigns. You can organize campaigns, evaluate performance KPIs (impressions, submissions, conversion rate), toggle draft/published states, and launch the visual builder.', 'zappoppop') . '</p>',
        ]);

        $screen->add_help_tab([
            'id'      => 'zappoppop_dash_lifecycle',
            'title'   => __('Trash & Restore', 'zappoppop'),
            'content' => '<p>' . __('ZapPopPop uses a native two-stage soft trash lifecycle. Trashing a campaign moves it to the Trash view and deactivates it on the frontend. Trashed campaigns can be restored with a single click. Permanent deletion is reserved for administrators and removes all linked submission records.', 'zappoppop') . '</p>',
        ]);

        $screen->add_help_tab([
            'id'      => 'zappoppop_dash_actions',
            'title'   => __('Quick & Bulk Actions', 'zappoppop'),
            'content' => '<p>' . __('Hover over any row to use Quick Edit (for changing title and status inline), or Duplicate (to clone a campaign). Use the checkbox column on the left to apply bulk actions (Move to Trash, Publish, Draft, Duplicate) across multiple campaigns at once.', 'zappoppop') . '</p>',
        ]);
    }

    /**
     * Help tabs for Visual Drag-and-Drop Builder.
     */
    protected function add_builder_help($screen) {
        $screen->add_help_tab([
            'id'      => 'zappoppop_bld_canvas',
            'title'   => __('Canvas & Viewports', 'zappoppop'),
            'content' => '<p>' . __('Drag elements from the top ribbon palette onto the canvas stage. Use the header controls to switch between Desktop (640px) and Mobile (360px) viewports, or navigate between multi-step sequence screens (Screen 1, Screen 2, Screen 3).', 'zappoppop') . '</p>',
        ]);

        $screen->add_help_tab([
            'id'      => 'zappoppop_bld_inspector',
            'title'   => __('Layer Properties', 'zappoppop'),
            'content' => '<p>' . __('Selecting any element opens the Layer Properties inspector drawer. Here you can configure pixel coordinates, dimensions, typography, CSS colors, drop shadows, border radius, animations, and conditional visibility logic.', 'zappoppop') . '</p>',
        ]);

        $screen->add_help_tab([
            'id'      => 'zappoppop_bld_settings',
            'title'   => __('Campaign Settings', 'zappoppop'),
            'content' => '<p>' . __('Click Campaign Settings in the top bar to configure display triggers (Exit Intent, Scroll %, Idle Inactivity, Page Load Delay), targeting filters (Geolocation, Devices, User authentication), subscriber autoresponders, and webhook integrations.', 'zappoppop') . '</p>',
        ]);

        $screen->add_help_tab([
            'id'      => 'zappoppop_bld_shortcuts',
            'title'   => __('Keyboard Shortcuts', 'zappoppop'),
            'content' => '<p><strong>Ctrl / Cmd + Z:</strong> ' . __('Undo the previous canvas action.', 'zappoppop') . '<br>' .
                         '<strong>Ctrl / Cmd + Y:</strong> ' . __('Redo the last undone canvas action.', 'zappoppop') . '<br>' .
                         '<strong>Escape:</strong> ' . __('Deselect active layer or close open inspector drawers.', 'zappoppop') . '</p>',
        ]);
    }

    /**
     * Help tabs for Submissions Screen.
     */
    protected function add_submissions_help($screen) {
        $screen->add_help_tab([
            'id'      => 'zappoppop_sub_overview',
            'title'   => __('Lead Management', 'zappoppop'),
            'content' => '<p>' . __('All form submissions captured across your campaigns are recorded here in real time, including email addresses, custom fields, visitor IP, detected country, and quiz scores.', 'zappoppop') . '</p>',
        ]);

        $screen->add_help_tab([
            'id'      => 'zappoppop_sub_export',
            'title'   => __('CSV Export & Delimiters', 'zappoppop'),
            'content' => '<p>' . __('Use the "Export CSV" button to download your subscriber lists. The file delimiter (comma, semicolon, or tab) can be adjusted in ZapPopPop Settings under the General tab.', 'zappoppop') . '</p>',
        ]);
    }

    /**
     * Help tabs for Global Settings Screen.
     */
    protected function add_settings_help($screen) {
        $screen->add_help_tab([
            'id'      => 'zappoppop_set_overview',
            'title'   => __('Global Settings', 'zappoppop'),
            'content' => '<p>' . __('Configure global settings for automated autoresponder emails, performance optimizations, font and UI component libraries, Geolocation providers, and custom global CSS/JS stylesheets.', 'zappoppop') . '</p>',
        ]);
    }
}
