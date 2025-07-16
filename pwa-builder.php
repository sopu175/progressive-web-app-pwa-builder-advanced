<?php
/*
Plugin Name: Progressive Web App (PWA) Builder Advanced
Description: Turn your WP site into a rich PWA with push notifications, offline support, and advanced image upload for icons, splash, and screenshots.
Version: 1.0.0
Author: saif2456
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) exit;

// Define plugin constants with unique prefix
define('PROGWEBAPP_PLUGIN_VERSION', '1.0.0');
define('PROGWEBAPP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PROGWEBAPP_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Settings/admin page
require_once PROGWEBAPP_PLUGIN_PATH . 'includes/admin-settings.php';

// Manifest endpoint
add_action('init', 'progwebapp_handle_manifest_request');
function progwebapp_handle_manifest_request() {
    // Public endpoint: no nonce needed, manifest must be accessible to all clients
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (isset($_GET['pwa-manifest'])) {
        header('Content-Type: application/json');
        $icon_id = get_option('progwebapp_builder_icon_id');
        $icon_url = $icon_id ? wp_get_attachment_image_url($icon_id, 'full') : PROGWEBAPP_PLUGIN_URL . 'assets/icon-192.png';

        $splash_id = get_option('progwebapp_builder_splash_id');
        $splash_url = $splash_id ? wp_get_attachment_image_url($splash_id, 'full') : '';

        $screenshots = get_option('progwebapp_builder_screenshot_ids', []);
        $screens_arr = [];
        if (!empty($screenshots) && is_array($screenshots)) {
            foreach ($screenshots as $id) {
                $url = wp_get_attachment_image_url($id, 'full');
                if ($url) {
                    $meta = wp_get_attachment_metadata($id);
                    $screens_arr[] = [
                        "src" => esc_url_raw($url),
                        "sizes" => (isset($meta['width']) && isset($meta['height'])) ? esc_html("{$meta['width']}x{$meta['height']}") : "",
                        "type" => esc_html(get_post_mime_type($id))
                    ];
                }
            }
        }

        $manifest = [
            "name" => sanitize_text_field(get_option('progwebapp_builder_app_name', get_bloginfo('name'))),
            "short_name" => sanitize_text_field(get_option('progwebapp_builder_app_short', get_bloginfo('name'))),
            "description" => sanitize_textarea_field(get_option('progwebapp_builder_app_desc', '')),
            "start_url" => esc_url_raw(get_option('progwebapp_builder_start_url', home_url('/'))),
            "display" => sanitize_text_field(get_option('progwebapp_builder_display', 'standalone')),
            "orientation" => sanitize_text_field(get_option('progwebapp_builder_orientation', 'any')),
            "background_color" => sanitize_hex_color(get_option('progwebapp_builder_bg_color', '#ffffff')),
            "theme_color" => sanitize_hex_color(get_option('progwebapp_builder_theme_color', '#2196f3')),
            "categories" => array_map('trim', explode(',', sanitize_text_field(get_option('progwebapp_builder_categories', '')))),
            "lang" => sanitize_text_field(get_option('progwebapp_builder_lang', 'en')),
            "dir" => sanitize_text_field(get_option('progwebapp_builder_dir', 'ltr')),
            "icons" => [
                [
                    "src" => esc_url_raw($icon_url),
                    "sizes" => "192x192",
                    "type" => "image/png",
                    "purpose" => "any maskable"
                ]
            ],
        ];
        if ($splash_url) {
            $manifest["screenshots"][] = [
                "src" => esc_url_raw($splash_url),
                "sizes" => "512x512",
                "type" => "image/png"
            ];
        }
        if (!empty($screens_arr)) {
            $manifest["screenshots"] = array_merge($manifest["screenshots"]??[], $screens_arr);
        }
        echo wp_json_encode($manifest);
        exit;
    }
}

// Service Worker endpoint
add_action('init', 'progwebapp_handle_service_worker_request');
function progwebapp_handle_service_worker_request() {
    // Public endpoint: no nonce needed, service worker must be accessible to all clients
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (isset($_GET['pwa-sw'])) {
        header('Content-Type: application/javascript');
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once(ABSPATH . '/wp-admin/includes/file.php');
            WP_Filesystem();
        }
        $sw_path = PROGWEBAPP_PLUGIN_PATH . 'assets/sw.js';
        if ($wp_filesystem->exists($sw_path)) {
            // Service worker content is JavaScript code, not user input, so we don't escape it
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $wp_filesystem->get_contents($sw_path);
        }
        exit;
    }
}

// Enqueue manifest/service worker and onboarding script
add_action('wp_head', 'progwebapp_add_manifest_link');
function progwebapp_add_manifest_link() {
    echo '<link rel="manifest" href="' . esc_url(home_url('/?pwa-manifest=1')) . '">';
    echo '<meta name="theme-color" content="' . esc_attr(sanitize_hex_color(get_option('progwebapp_builder_theme_color', '#2196f3'))) . '">';
}

add_action('wp_footer', 'progwebapp_add_service_worker_script');
function progwebapp_add_service_worker_script() {
    $sw_script = "
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('" . esc_url(home_url('/?pwa-sw=1')) . "');
    }
    ";
    wp_add_inline_script('jquery', $sw_script);
}

// Add icon
add_action('wp_head', 'progwebapp_add_app_icon');
function progwebapp_add_app_icon() {
    $icon_id = get_option('progwebapp_builder_icon_id');
    $icon = $icon_id ? esc_url(wp_get_attachment_image_url($icon_id, 'full')) : PROGWEBAPP_PLUGIN_URL . 'assets/icon-192.png';
    echo '<link rel="icon" href="' . esc_url($icon) . '" sizes="192x192">';
}
?>