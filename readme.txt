=== ZapPopPop ===
Contributors: ZapPopPop
Donate link: https://zappoppop.com
Tags: popup, modal, popup builder, optin, lead generation, form builder
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.02
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A flexible, modular real-time responsive popup and form builder for WordPress.

== Description ==

ZapPopPop bringing you multi-layering real-time responsive popups or forms visual builder, designed to create responsive, flexible modals and animated overlays background on your site.

= Key Features =
* Visual builder with real-time responsive canvas preview.
* Precise layout scaling across mobile and desktop displays.
* Customizable trigger options (delay, user interaction).
* Fully modular settings and schema architecture.
* GPL-compliant and privacy focused.

== Installation ==

1. Upload the `zappoppop` folder to the `/wp-content/plugins/` directory, or install directly via the WordPress Plugins menu.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to **ZapPopPop** in your admin sidebar to create your first popup.

= Does ZapPopPop work on multisite? =
Yes. Network activate it and every site gets its own tables and settings; new sites are set up automatically and deleting a site removes its ZapPopPop tables. Super admins can set defaults for new sites under Network Admin > ZapPopPop.

== Frequently Asked Questions ==

= Does ZapPopPop track visitors? =
No. ZapPopPop runs locally on your WordPress installation and does not track personal data without configuration.

= How do I change, hide or restart the builder guide? =
Go to ZapPopPop > Settings > Builder. "Show guide" turns the guided tour on or off for everyone, and "Restart tour" replays it for you the next time you open the builder. The "Guide Steps" tab lets you edit, reorder, hide or add steps. Each admin sees the tour once; the ? button in the builder toolbar relaunches it at any time.

= How do I translate ZapPopPop? =
The plugin is fully internationalized (text domain `zappoppop`, domain path `/languages`). Use `languages/zappoppop.pot` as the template, save your `.po`/`.mo` as `wp-content/languages/plugins/zappoppop-{locale}.po/.mo`, then run `wp i18n make-json wp-content/languages/plugins --no-purge` to create the JSON files used by the JavaScript strings.

= Where does ZapPopPop store additional files? =
Only in `wp-content/uploads/` (resolved with `wp_upload_dir()`): imported template images under `uploads/zappoppop-templates/` and, if you install optional extension modules, `uploads/zappoppop-modules/`. The plugin never writes to its own folder, so updates never remove your data.

= How do I install an extension module? =
Go to ZapPopPop > Extend > Upload Module and choose a module zip. Only administrators can do this (network administrators on multisite), and it is disabled when `DISALLOW_FILE_MODS` is set. Modules are unpacked into `wp-content/uploads/zappoppop-modules/`, only static files are accepted (PHP is blocked), and the built-in modules are never modified.

== External services ==
ZapPopPop does not send any data to a third-party service. Nothing is loaded from a CDN: the fonts (Inter, Roboto, Montserrat, Poppins, Open Sans, Lato) and the Font Awesome icons are bundled with the plugin, and no visitor data leaves your site.

== Third-party libraries ==
* Font Awesome Free 6.5.1 - icons CC BY 4.0, fonts SIL OFL 1.1, code MIT - https://fontawesome.com/license/free (bundled in `assets/vendor/fontawesome/`).
* Inter, Roboto, Montserrat, Poppins, Open Sans and Lato - SIL Open Font License 1.1, packaged from Fontsource (bundled in `assets/vendor/fonts/`, licence file in each folder).

== Changelog ==

= 1.0.02 =
* Initial release of ZapPopPop.
