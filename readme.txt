=== ZapPopPop ===
Contributors: wppoppop
Donate link: https://zappoppop.com
Tags: popup, modal, popup builder, optin, lead generation
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.15
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

= Does ZapPopPop add a "Powered by" credit? =
No, not unless you turn it on. The small "Powered by ZapPopPop" badge is optional and is off by default. It can be switched on or off under ZapPopPop > Settings > General, and its link can be turned off while keeping the text.

= How do I change, hide or restart the builder guide? =
Go to ZapPopPop > Settings > Builder. "Show guide" turns the guided tour on or off for everyone, and "Restart tour" replays it for you the next time you open the builder. The "Guide Steps" tab lets you edit, reorder, hide or add steps. Each admin sees the tour once; the ? button in the builder toolbar relaunches it at any time.

= How do I translate ZapPopPop? =
The plugin is fully internationalized (text domain `zappoppop`, domain path `/languages`). Use `languages/zappoppop.pot` as the template, save your `.po`/`.mo` as `wp-content/languages/plugins/zappoppop-{locale}.po/.mo`, then run `wp i18n make-json wp-content/languages/plugins --no-purge` to create the JSON files used by the JavaScript strings.

= Where does ZapPopPop store additional files? =
Only in `wp-content/uploads/` (resolved with `wp_upload_dir()`): imported template images under `uploads/zappoppop-templates/`. The plugin never writes to its own folder, so updates never remove your data.

= How do I add an extension module? =
Extension modules ship with ZapPopPop or with companion plugins. Open ZapPopPop > Modules and click Install on a module to turn it on. ZapPopPop does not accept uploaded JavaScript, CSS, HTML or PHP files: a module only runs code that is part of a plugin you installed through WordPress.

= What are add-ons? =
Add-ons are separate plugins listed under ZapPopPop > Modules > Add-ons. The Install button runs WordPress's own plugin installer (the same one as Plugins > Add New) and Activate uses WordPress's normal plugin activation, so WordPress itself downloads, verifies and updates the add-on. ZapPopPop does not download or run anything on its own and sends no data to any server for this.

= Is there a Pro version? =
ZapPopPop is complete and fully functional on its own. Every feature in this plugin works without a license, with no trial period, usage limits or locked options.

A separate, optional paid plugin, ZapPopPop Pro, is sold and distributed from zappoppop.com. It adds extra elements, triggers and templates. It is not hosted on WordPress.org and is not required to use this plugin.

The plugin shows a few small links to the Pro version. The banners can be closed, and a dismissed notice stays hidden for 14 days. They never block any feature.

== External services ==
ZapPopPop makes no outgoing request on its own and loads no scripts from a CDN. Two optional features contact an outside service, and only when you use them. The first is the "Test webhook" button in the admin: when an administrator clicks it, the plugin sends one small JSON ping (an event name, the time and a test message, signed with the secret the administrator typed) to the webhook URL that administrator entered, so the owner of that URL receives that request. Nothing is sent automatically. The second is the YouTube and Vimeo video layer, described below. The fonts (Inter, Roboto, Montserrat, Poppins, Open Sans, Lato, Nunito, Raleway, DM Sans, Work Sans, Source Sans 3, Oswald, Playfair Display, Merriweather) and the Font Awesome icons are bundled with the plugin and are not loaded from a CDN.

**YouTube and Vimeo video layers (optional).** Only when you add a Video layer that uses a YouTube or Vimeo link, the popup shows that provider's player in an iframe. The visitor's browser then contacts the provider (youtube.com or player.vimeo.com), which receives the visitor's IP address and browser details and may set cookies. While you edit such a layer in the builder, the admin screen also requests the video's preview image (img.youtube.com for YouTube). Nothing is sent to these services when no video layer is used, and the plugin sends no other data to them. YouTube: Terms https://www.youtube.com/t/terms - Privacy https://policies.google.com/privacy. Vimeo: Terms https://vimeo.com/terms - Privacy https://vimeo.com/privacy.


== Third-party libraries ==
* Font Awesome Free 6.5.1 - icons CC BY 4.0, fonts SIL OFL 1.1, code MIT - https://fontawesome.com/license/free (bundled in `assets/vendor/fontawesome/`).
* Air Datepicker 3.5.0 - date picker, MIT - https://github.com/t1m0n/air-datepicker (bundled in `public/vendor/air-datepicker.min.js` and `.css`; full source at the link above, licence copy in `public/vendor/LICENSES/`).
* Ion.RangeSlider 2.3.1 - range slider, MIT - https://github.com/IonDen/ion.rangeSlider (bundled in `public/vendor/ion.rangeSlider.min.js` and `.css`; full source at the link above, licence copy in `public/vendor/LICENSES/`).
* jQuery Mask Plugin 1.14.16 - input masks, MIT - https://github.com/igorescobar/jQuery-Mask-Plugin (bundled in `public/vendor/jquery.mask.min.js`; full source at the link above, licence copy in `public/vendor/LICENSES/`).
* Inter, Roboto, Montserrat, Poppins, Open Sans, Lato, Nunito, Raleway, DM Sans, Work Sans, Source Sans 3, Oswald, Playfair Display and Merriweather - SIL Open Font License 1.1, latin and latin-ext subsets packaged from Fontsource (bundled in `assets/vendor/fonts/`, one licence file per family in `assets/vendor/fonts/LICENSES/`).

== Changelog ==

= 1.0.15 =
* Initial release of ZapPopPop.
