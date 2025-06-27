=== Progressive Web App (PWA) Builder Advanced ===
Contributors: saif2456
Tags: pwa, progressive web app, manifest, offline, service worker
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A plugin to add a customizable Progressive Web App (PWA) manifest, icons, splash images, and advanced settings to WordPress.

== Description ==

PWA Builder Advanced lets you easily add a PWA manifest, icons, splash images, screenshots, and all recommended manifest fields to your WordPress site. Uses Material Design (Roboto, Material Icons).

**Features:**
* Custom app name, short name, and description
* Upload app icon (192x192 PNG required)
* Optional splash image (512x512 PNG)
* Optional screenshots (min 320x640, JPG/PNG)
* Set start URL, display mode, orientation, theme color, background color, language, text direction, and categories
* All images validated for correct size
* Manifest auto-updates with new images and settings
* All images managed via WP Media Library
* Service worker endpoint for offline support
* Ready for push notifications and advanced PWA features

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/pwa-builder-advanced` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to **Settings > PWA Builder** to configure your app details and upload images.

== Frequently Asked Questions ==

= What image sizes are required? =
* App Icon: 192x192 PNG (required)
* Splash Image: 512x512 PNG (optional)
* Screenshots: Minimum 320x640, JPG or PNG (optional)

= What manifest fields can I customize? =
You can set app name, short name, description, start URL, display mode, orientation, theme color, background color, language, text direction, and categories.

= Where are images stored? =
All images are uploaded and managed through the WordPress Media Library.

= How do I uninstall? =
Simply delete the plugin from the WordPress admin.

== Changelog ==

= 1.0.0 =
* Initial release with advanced manifest and settings support

== Upgrade Notice ==

= 1.0.0 =
First release.

== Screenshots ==

1. Settings page for configuring your PWA (all manifest fields).
2. Example manifest.json output.

== Notes ==

- All images go through the WP Media Library, and size is checked before selection.
- Manifest automatically updates with new images and settings.
- Service worker endpoint included for offline support.
- Ready for push notifications and advanced PWA features.