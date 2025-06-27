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

// Settings/admin page
require_once plugin_dir_path(__FILE__).'includes/admin-settings.php';

// Manifest endpoint
add_action('init', function() { 
    // Public endpoint: no nonce needed, manifest must be accessible to all clients
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (isset($_GET['pwa-manifest'])) {
        header('Content-Type: application/json');
        $icon_id = get_option('pwa_builder_icon_id');
        $icon_url = $icon_id ? wp_get_attachment_image_url($icon_id, 'full') : plugin_dir_url(__FILE__).'assets/icon-192.png';

        $splash_id = get_option('pwa_builder_splash_id');
        $splash_url = $splash_id ? wp_get_attachment_image_url($splash_id, 'full') : '';

        $screenshots = get_option('pwa_builder_screenshot_ids', []);
        $screens_arr = [];
        if (!empty($screenshots) && is_array($screenshots)) {
            foreach ($screenshots as $id) {
                $url = wp_get_attachment_image_url($id, 'full');
                if ($url) {
                    $meta = wp_get_attachment_metadata($id);
                    $screens_arr[] = [
                        "src" => $url,
                        "sizes" => (isset($meta['width']) && isset($meta['height'])) ? "{$meta['width']}x{$meta['height']}" : "",
                        "type" => get_post_mime_type($id)
                    ];
                }
            }
        }

        $manifest = [
            "name" => get_option('pwa_builder_app_name', get_bloginfo('name')),
            "short_name" => get_option('pwa_builder_app_short', get_bloginfo('name')),
            "description" => get_option('pwa_builder_app_desc', ''),
            "start_url" => get_option('pwa_builder_start_url', home_url('/')),
            "display" => get_option('pwa_builder_display', 'standalone'),
            "orientation" => get_option('pwa_builder_orientation', 'any'),
            "background_color" => get_option('pwa_builder_bg_color', '#ffffff'),
            "theme_color" => get_option('pwa_builder_theme_color', '#2196f3'),
            "categories" => array_map('trim', explode(',', get_option('pwa_builder_categories', ''))),
            "lang" => get_option('pwa_builder_lang', 'en'),
            "dir" => get_option('pwa_builder_dir', 'ltr'),
            "icons" => [
                [
                    "src" => $icon_url,
                    "sizes" => "192x192",
                    "type" => "image/png",
                    "purpose" => "any maskable"
                ]
            ],
        ];
        if ($splash_url) {
            $manifest["screenshots"][] = [
                "src" => $splash_url,
                "sizes" => "512x512",
                "type" => "image/png"
            ];
        }
        if (!empty($screens_arr)) {
            $manifest["screenshots"] = array_merge($manifest["screenshots"]??[], $screens_arr);
        }
        echo json_encode($manifest);
        exit;
    }
});

// Service Worker endpoint
add_action('init', function() {
    // Public endpoint: no nonce needed, service worker must be accessible to all clients
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (isset($_GET['pwa-sw'])) {
        header('Content-Type: application/javascript');
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once(ABSPATH . '/wp-admin/includes/file.php');
            WP_Filesystem();
        }
        $sw_path = plugin_dir_path(__FILE__).'assets/sw.js';
        if ($wp_filesystem->exists($sw_path)) {
            echo esc_js($wp_filesystem->get_contents($sw_path));
        }
        exit;
    }
});

// Enqueue manifest/service worker and onboarding script
add_action('wp_head', function() {
    echo '<link rel="manifest" href="' . esc_url(home_url('/?pwa-manifest=1')) . '">';
    echo '<meta name="theme-color" content="#2196f3">';
});
add_action('wp_footer', function() {
    ?>
<script>
if ('serviceWorker' in navigator) {
   navigator.serviceWorker.register('<?php echo esc_url(home_url('/?pwa-sw=1')); ?>');
}
</script>
<?php
});

// Add icon
add_action('wp_head', function() {
    $icon_id = get_option('pwa_builder_icon_id');
    $icon = $icon_id ? esc_url(wp_get_attachment_image_url($icon_id, 'full')) : plugin_dir_url(__FILE__).'assets/icon-192.png';
    echo '<link rel="icon" href="' . esc_url($icon) . '" sizes="192x192">';
});