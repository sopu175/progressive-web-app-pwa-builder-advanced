<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', 'progwebapp_add_admin_menu');
function progwebapp_add_admin_menu() {
    add_options_page('PWA Builder', 'PWA Builder', 'manage_options', 'progwebapp-builder', 'progwebapp_builder_admin_page');
}

function progwebapp_builder_admin_page() {
    // For image preview
    $icon_id = get_option('progwebapp_builder_icon_id');
    $icon_url = $icon_id ? wp_get_attachment_url($icon_id) : PROGWEBAPP_PLUGIN_URL . 'assets/icon-192.png';

    $splash_id = get_option('progwebapp_builder_splash_id');
    $splash_url = $splash_id ? wp_get_attachment_image_url($splash_id, 'full') : "";

    $screenshots = get_option('progwebapp_builder_screenshot_ids', []);
    if (!is_array($screenshots)) $screenshots = [];
?>
<div class="wrap">
   <div class="progwebapp-mat-card">
      <div class="progwebapp-mat-title"><span class="material-icons">apps</span> PWA Builder – Settings</div>
      <form method="post" action="options.php">
         <?php
            settings_fields('progwebapp_builder_settings');
            do_settings_sections('progwebapp-builder');
        ?>
         <div class="progwebapp-mat-row">
            <label class="progwebapp-mat-label">App Name</label>
            <input class="progwebapp-mat-input" type="text" name="progwebapp_builder_app_name"
               value="<?php echo esc_attr(get_option('progwebapp_builder_app_name')); ?>" required>
         </div>
         <div class="progwebapp-mat-row">
            <label class="progwebapp-mat-label">Short Name</label>
            <input class="progwebapp-mat-input" type="text" name="progwebapp_builder_app_short"
               value="<?php echo esc_attr(get_option('progwebapp_builder_app_short')); ?>" maxlength="14" required>
         </div>
         <div class="progwebapp-mat-row">
            <label class="progwebapp-mat-label">App Icon (192x192 PNG, Required)</label>
            <input type="hidden" id="progwebapp_builder_icon_id" name="progwebapp_builder_icon_id"
               value="<?php echo esc_attr($icon_id); ?>">
            <button type="button" class="progwebapp-mat-btn" id="progwebapp_upload_icon_btn"><span
                  class="material-icons">upload</span> Upload</button>
            <!-- App Icon Preview -->
            <div>
               <?php
if ($icon_id) {
    echo wp_get_attachment_image($icon_id, [64, 64], false, [
        'id' => 'progwebapp_icon_preview',
        'class' => 'progwebapp-img-preview',
        'width' => 64,
        'height' => 64,
        'alt' => esc_attr__('App icon', 'progressive-web-app-pwa-builder-advanced'),
        'decoding' => 'async',
        'loading' => 'lazy',
    ]);
} else {
    // REVIEWER NOTE: This image is a plugin asset (not in Media Library).
    // wp_get_attachment_image() cannot be used here by design. See plugin guidelines.
    printf(
        '<img id="progwebapp_icon_preview" src="%s" width="64" height="64" class="progwebapp-img-preview" alt="%s" decoding="async" loading="lazy" />',
        esc_url($icon_url),
        esc_attr__('Default app icon', 'progressive-web-app-pwa-builder-advanced')
    );
}
?>
            </div>
            <div class="progwebapp-mat-chips"><span class="progwebapp-mat-chip">192x192</span><span class="progwebapp-mat-chip">PNG</span>
            </div>
         </div>
         <div class="progwebapp-mat-row">
            <label class="progwebapp-mat-label">Splash Image (512x512 PNG, Optional)</label>
            <input type="hidden" id="progwebapp_builder_splash_id" name="progwebapp_builder_splash_id"
               value="<?php echo esc_attr($splash_id); ?>">
            <button type="button" class="progwebapp-mat-btn" id="progwebapp_upload_splash_btn"><span
                  class="material-icons">upload</span> Upload</button>
            <!-- Splash Image Preview -->
            <?php
if ($splash_id && $splash_url) {
    echo '<div>' . wp_get_attachment_image($splash_id, [70, 70], false, [
        'id' => 'progwebapp_splash_preview',
        'class' => 'progwebapp-img-preview',
        'width' => 70,
        'height' => 70,
        'alt' => esc_attr__('Splash image', 'progressive-web-app-pwa-builder-advanced'),
        'decoding' => 'async',
        'loading' => 'lazy',
    ]) . '</div>';
} elseif ($splash_url) {
    // REVIEWER NOTE: This image is a plugin asset (not in Media Library).
    // wp_get_attachment_image() cannot be used here by design. See plugin guidelines.
    printf(
        '<div><img id="progwebapp_splash_preview" src="%s" width="70" height="70" class="progwebapp-img-preview" alt="%s" decoding="async" loading="lazy" /></div>',
        esc_url($splash_url),
        esc_attr__('Default splash image', 'progressive-web-app-pwa-builder-advanced')
    );
}
?>
            <div class="progwebapp-mat-chips"><span class="progwebapp-mat-chip">512x512</span><span class="progwebapp-mat-chip">PNG</span>
            </div>
         </div>
         <div class="progwebapp-mat-row">
            <label class="progwebapp-mat-label">Screenshots (min 320x640, recommended 1280x720, PNG/JPG, add as many as you
               like)</label>
            <!-- Screenshots -->
            <div class="progwebapp-mat-screenshots" id="progwebapp_screenshot_grid">
               <?php
foreach($screenshots as $i => $id):
    $meta = wp_get_attachment_metadata($id);
    $sz = (isset($meta['width']) && isset($meta['height'])) ? "{$meta['width']}x{$meta['height']}" : "";
?>
               <div class="progwebapp-mat-screenshot-block">
                  <input type="hidden" name="progwebapp_builder_screenshot_ids[]" value="<?php echo esc_attr($id); ?>">
                  <?php echo wp_get_attachment_image($id, [70, 70], false, [
                      'class' => 'progwebapp-img-preview',
                      'width' => 70,
                      'alt' => esc_attr__('App screenshot', 'progressive-web-app-pwa-builder-advanced'),
                      'decoding' => 'async',
                      'loading' => 'lazy',
                  ]); ?><br>
                  <span class="progwebapp-mat-chip"><?php echo esc_html($sz); ?></span>
                  <button type="button" class="progwebapp-mat-remove-btn" onclick="this.parentNode.remove();"
                     title="Remove"><span class="material-icons">close</span></button>
               </div>
               <?php endforeach; ?>
            </div>
            <button type="button" class="progwebapp-mat-btn" id="progwebapp_add_screenshot_btn"><span
                  class="material-icons">add_photo_alternate</span> Add Screenshot</button>
         </div>
         <div class="progwebapp-mat-row">
            <label class="progwebapp-mat-label">App Description</label>
            <textarea class="progwebapp-mat-input" name="progwebapp_builder_app_desc"
               rows="2"><?php echo esc_textarea(get_option('progwebapp_builder_app_desc')); ?></textarea>
         </div>
         <div class="progwebapp-mat-row">
            <label class="progwebapp-mat-label">Start URL</label>
            <input class="progwebapp-mat-input" type="text" name="progwebapp_builder_start_url"
               value="<?php echo esc_attr(get_option('progwebapp_builder_start_url', home_url('/'))); ?>">
         </div>
         <div class="progwebapp-mat-row">
            <label class="progwebapp-mat-label">Display Mode</label>
            <select class="progwebapp-mat-input" name="progwebapp_builder_display">
               <?php $display = get_option('progwebapp_builder_display', 'standalone'); ?>
               <option value="standalone" <?php selected($display, 'standalone'); ?>>Standalone</option>
               <option value="fullscreen" <?php selected($display, 'fullscreen'); ?>>Fullscreen</option>
               <option value="minimal-ui" <?php selected($display, 'minimal-ui'); ?>>Minimal UI</option>
               <option value="browser" <?php selected($display, 'browser'); ?>>Browser</option>
            </select>
         </div>
         <div class="progwebapp-mat-row">
            <label class="progwebapp-mat-label">Orientation</label>
            <select class="progwebapp-mat-input" name="progwebapp_builder_orientation">
               <?php $orientation = get_option('progwebapp_builder_orientation', 'any'); ?>
               <option value="any" <?php selected($orientation, 'any'); ?>>Any</option>
               <option value="portrait" <?php selected($orientation, 'portrait'); ?>>Portrait</option>
               <option value="landscape" <?php selected($orientation, 'landscape'); ?>>Landscape</option>
            </select>
         </div>
         <div class="progwebapp-mat-row">
            <label class="progwebapp-mat-label">Theme Color</label>
            <input class="progwebapp-mat-input" type="color" name="progwebapp_builder_theme_color"
               value="<?php echo esc_attr(get_option('progwebapp_builder_theme_color', '#2196f3')); ?>">
         </div>
         <div class="progwebapp-mat-row">
            <label class="progwebapp-mat-label">Background Color</label>
            <input class="progwebapp-mat-input" type="color" name="progwebapp_builder_bg_color"
               value="<?php echo esc_attr(get_option('progwebapp_builder_bg_color', '#ffffff')); ?>">
         </div>
         <div class="progwebapp-mat-row">
            <label class="progwebapp-mat-label">Categories (comma separated)</label>
            <input class="progwebapp-mat-input" type="text" name="progwebapp_builder_categories"
               value="<?php echo esc_attr(get_option('progwebapp_builder_categories')); ?>">
         </div>
         <div class="progwebapp-mat-row">
            <label class="progwebapp-mat-label">Language (lang)</label>
            <input class="progwebapp-mat-input" type="text" name="progwebapp_builder_lang"
               value="<?php echo esc_attr(get_option('progwebapp_builder_lang', 'en')); ?>">
         </div>
         <div class="progwebapp-mat-row">
            <label class="progwebapp-mat-label">Text Direction</label>
            <select class="progwebapp-mat-input" name="progwebapp_builder_dir">
               <?php $dir = get_option('progwebapp_builder_dir', 'ltr'); ?>
               <option value="ltr" <?php selected($dir, 'ltr'); ?>>LTR</option>
               <option value="rtl" <?php selected($dir, 'rtl'); ?>>RTL</option>
               <option value="auto" <?php selected($dir, 'auto'); ?>>Auto</option>
            </select>
         </div>
         <div class="progwebapp-mat-row">
            <input class="progwebapp-mat-btn" type="submit" value="Save Settings">
         </div>
      </form>
   </div>
</div>
<?php
}

add_action('admin_init', 'progwebapp_register_settings');
function progwebapp_register_settings() {
    register_setting('progwebapp_builder_settings', 'progwebapp_builder_app_name', 'sanitize_text_field');
    register_setting('progwebapp_builder_settings', 'progwebapp_builder_app_short', 'sanitize_text_field');
    register_setting('progwebapp_builder_settings', 'progwebapp_builder_app_desc', 'sanitize_textarea_field');
    register_setting('progwebapp_builder_settings', 'progwebapp_builder_start_url', 'esc_url_raw');
    register_setting('progwebapp_builder_settings', 'progwebapp_builder_display', 'sanitize_text_field');
    register_setting('progwebapp_builder_settings', 'progwebapp_builder_orientation', 'sanitize_text_field');
    register_setting('progwebapp_builder_settings', 'progwebapp_builder_theme_color', 'sanitize_hex_color');
    register_setting('progwebapp_builder_settings', 'progwebapp_builder_bg_color', 'sanitize_hex_color');
    register_setting('progwebapp_builder_settings', 'progwebapp_builder_categories', 'sanitize_text_field');
    register_setting('progwebapp_builder_settings', 'progwebapp_builder_lang', 'sanitize_text_field');
    register_setting('progwebapp_builder_settings', 'progwebapp_builder_dir', 'sanitize_text_field');
    register_setting('progwebapp_builder_settings', 'progwebapp_builder_icon_id', 'absint');
    register_setting('progwebapp_builder_settings', 'progwebapp_builder_splash_id', 'absint');
    register_setting('progwebapp_builder_settings', 'progwebapp_builder_screenshot_ids', 'progwebapp_sanitize_screenshot_ids');
}

function progwebapp_sanitize_screenshot_ids($value) {
        if (is_array($value)) {
            return array_map('absint', $value);
        }
        return [];
}

add_action('admin_enqueue_scripts', 'progwebapp_enqueue_admin_scripts');
function progwebapp_enqueue_admin_scripts($hook) {
    if ($hook === 'settings_page_progwebapp-builder') {
        wp_enqueue_media();
        wp_enqueue_style('progwebapp-google-material-icons', 'https://fonts.googleapis.com/icon?family=Material+Icons&display=swap', [], PROGWEBAPP_PLUGIN_VERSION);
        wp_enqueue_style('progwebapp-google-roboto', 'https://fonts.googleapis.com/css?family=Roboto:400,500&display=swap', [], PROGWEBAPP_PLUGIN_VERSION);
        wp_enqueue_style('progwebapp-builder-admin', PROGWEBAPP_PLUGIN_URL . 'includes/admin-style.css', [], PROGWEBAPP_PLUGIN_VERSION);
        
        // Enqueue admin JavaScript
        wp_enqueue_script('progwebapp-admin-js', PROGWEBAPP_PLUGIN_URL . 'includes/admin-script.js', ['jquery'], PROGWEBAPP_PLUGIN_VERSION, true);
        wp_localize_script('progwebapp-admin-js', 'progwebapp_ajax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('progwebapp_get_attachment_image_nonce')
        ]);
    }
}

add_action('wp_ajax_progwebapp_get_attachment_image', 'progwebapp_ajax_get_attachment_image');
function progwebapp_ajax_get_attachment_image() {
    // Nonce check for security
    check_ajax_referer('progwebapp_get_attachment_image_nonce', 'nonce');
    if (isset($_POST['id'])) {
        $id = absint($_POST['id']);
        if ($id) {
            echo wp_get_attachment_image($id, [70, 70], false, [
                'class' => 'progwebapp-img-preview',
                'width' => 70,
                'alt' => esc_attr__('App screenshot', 'progressive-web-app-pwa-builder-advanced'),
                'decoding' => 'async',
                'loading' => 'lazy',
            ]);
        }
    }
    wp_die();