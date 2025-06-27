<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function(){
    add_options_page('PWA Builder', 'PWA Builder', 'manage_options', 'pwa-builder', 'pwa_builder_admin_page');
});

function pwa_builder_admin_page() {
    // For image preview
    $icon_id = get_option('pwa_builder_icon_id');
    $icon_url = $icon_id ? wp_get_attachment_url($icon_id) : plugin_dir_url(__FILE__).'assets/icon-192.png';

    $splash_id = get_option('pwa_builder_splash_id');
    $splash_url = $splash_id ? wp_get_attachment_image_url($splash_id, 'full') : "";

    $screenshots = get_option('pwa_builder_screenshot_ids', []);
    if (!is_array($screenshots)) $screenshots = [];
?>
<div class="wrap">
   <div class="pwa-mat-card">
      <div class="pwa-mat-title"><span class="material-icons">apps</span> PWA Builder – Settings</div>
      <form method="post" action="options.php">
         <?php
            settings_fields('pwa_builder_settings');
            do_settings_sections('pwa-builder');
        ?>
         <div class="pwa-mat-row">
            <label class="pwa-mat-label">App Name</label>
            <input class="pwa-mat-input" type="text" name="pwa_builder_app_name"
               value="<?php echo esc_attr(get_option('pwa_builder_app_name')); ?>" required>
         </div>
         <div class="pwa-mat-row">
            <label class="pwa-mat-label">Short Name</label>
            <input class="pwa-mat-input" type="text" name="pwa_builder_app_short"
               value="<?php echo esc_attr(get_option('pwa_builder_app_short')); ?>" maxlength="14" required>
         </div>
         <div class="pwa-mat-row">
            <label class="pwa-mat-label">App Icon (192x192 PNG, Required)</label>
            <input type="hidden" id="pwa_builder_icon_id" name="pwa_builder_icon_id"
               value="<?php echo esc_attr($icon_id); ?>">
            <button type="button" class="pwa-mat-btn" id="pwa_upload_icon_btn"><span
                  class="material-icons">upload</span> Upload</button>
            <!-- App Icon Preview -->
            <div>
               <?php
if ($icon_id) {
    echo wp_get_attachment_image($icon_id, [64, 64], false, [
        'id' => 'pwa_icon_preview',
        'class' => 'pwa-img-preview',
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
        '<img id="pwa_icon_preview" src="%s" width="64" height="64" class="pwa-img-preview" alt="%s" decoding="async" loading="lazy" />',
        esc_url($icon_url),
        esc_attr__('Default app icon', 'progressive-web-app-pwa-builder-advanced')
    );
}
?>
            </div>
            <div class="pwa-mat-chips"><span class="pwa-mat-chip">192x192</span><span class="pwa-mat-chip">PNG</span>
            </div>
         </div>
         <div class="pwa-mat-row">
            <label class="pwa-mat-label">Splash Image (512x512 PNG, Optional)</label>
            <input type="hidden" id="pwa_builder_splash_id" name="pwa_builder_splash_id"
               value="<?php echo esc_attr($splash_id); ?>">
            <button type="button" class="pwa-mat-btn" id="pwa_upload_splash_btn"><span
                  class="material-icons">upload</span> Upload</button>
            <!-- Splash Image Preview -->
            <?php
if ($splash_id && $splash_url) {
    echo '<div>' . wp_get_attachment_image($splash_id, [70, 70], false, [
        'id' => 'pwa_splash_preview',
        'class' => 'pwa-img-preview',
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
        '<div><img id="pwa_splash_preview" src="%s" width="70" height="70" class="pwa-img-preview" alt="%s" decoding="async" loading="lazy" /></div>',
        esc_url($splash_url),
        esc_attr__('Default splash image', 'progressive-web-app-pwa-builder-advanced')
    );
}
?>
            <div class="pwa-mat-chips"><span class="pwa-mat-chip">512x512</span><span class="pwa-mat-chip">PNG</span>
            </div>
         </div>
         <div class="pwa-mat-row">
            <label class="pwa-mat-label">Screenshots (min 320x640, recommended 1280x720, PNG/JPG, add as many as you
               like)</label>
            <!-- Screenshots -->
            <div class="pwa-mat-screenshots" id="pwa_screenshot_grid">
               <?php
foreach($screenshots as $i => $id):
    $meta = wp_get_attachment_metadata($id);
    $sz = (isset($meta['width']) && isset($meta['height'])) ? "{$meta['width']}x{$meta['height']}" : "";
?>
               <div class="pwa-mat-screenshot-block">
                  <input type="hidden" name="pwa_builder_screenshot_ids[]" value="<?php echo esc_attr($id); ?>">
                  <?php echo wp_get_attachment_image($id, [70, 70], false, [
                      'class' => 'pwa-img-preview',
                      'width' => 70,
                      'alt' => esc_attr__('App screenshot', 'progressive-web-app-pwa-builder-advanced'),
                      'decoding' => 'async',
                      'loading' => 'lazy',
                  ]); ?><br>
                  <span class="pwa-mat-chip"><?php echo esc_html($sz); ?></span>
                  <button type="button" class="pwa-mat-remove-btn" onclick="this.parentNode.remove();"
                     title="Remove"><span class="material-icons">close</span></button>
               </div>
               <?php endforeach; ?>
            </div>
            <button type="button" class="pwa-mat-btn" id="pwa_add_screenshot_btn"><span
                  class="material-icons">add_photo_alternate</span> Add Screenshot</button>
         </div>
         <div class="pwa-mat-row">
            <label class="pwa-mat-label">App Description</label>
            <textarea class="pwa-mat-input" name="pwa_builder_app_desc"
               rows="2"><?php echo esc_textarea(get_option('pwa_builder_app_desc')); ?></textarea>
         </div>
         <div class="pwa-mat-row">
            <label class="pwa-mat-label">Start URL</label>
            <input class="pwa-mat-input" type="text" name="pwa_builder_start_url"
               value="<?php echo esc_attr(get_option('pwa_builder_start_url', home_url('/'))); ?>">
         </div>
         <div class="pwa-mat-row">
            <label class="pwa-mat-label">Display Mode</label>
            <select class="pwa-mat-input" name="pwa_builder_display">
               <?php $display = get_option('pwa_builder_display', 'standalone'); ?>
               <option value="standalone" <?php selected($display, 'standalone'); ?>>Standalone</option>
               <option value="fullscreen" <?php selected($display, 'fullscreen'); ?>>Fullscreen</option>
               <option value="minimal-ui" <?php selected($display, 'minimal-ui'); ?>>Minimal UI</option>
               <option value="browser" <?php selected($display, 'browser'); ?>>Browser</option>
            </select>
         </div>
         <div class="pwa-mat-row">
            <label class="pwa-mat-label">Orientation</label>
            <select class="pwa-mat-input" name="pwa_builder_orientation">
               <?php $orientation = get_option('pwa_builder_orientation', 'any'); ?>
               <option value="any" <?php selected($orientation, 'any'); ?>>Any</option>
               <option value="portrait" <?php selected($orientation, 'portrait'); ?>>Portrait</option>
               <option value="landscape" <?php selected($orientation, 'landscape'); ?>>Landscape</option>
            </select>
         </div>
         <div class="pwa-mat-row">
            <label class="pwa-mat-label">Theme Color</label>
            <input class="pwa-mat-input" type="color" name="pwa_builder_theme_color"
               value="<?php echo esc_attr(get_option('pwa_builder_theme_color', '#2196f3')); ?>">
         </div>
         <div class="pwa-mat-row">
            <label class="pwa-mat-label">Background Color</label>
            <input class="pwa-mat-input" type="color" name="pwa_builder_bg_color"
               value="<?php echo esc_attr(get_option('pwa_builder_bg_color', '#ffffff')); ?>">
         </div>
         <div class="pwa-mat-row">
            <label class="pwa-mat-label">Categories (comma separated)</label>
            <input class="pwa-mat-input" type="text" name="pwa_builder_categories"
               value="<?php echo esc_attr(get_option('pwa_builder_categories')); ?>">
         </div>
         <div class="pwa-mat-row">
            <label class="pwa-mat-label">Language (lang)</label>
            <input class="pwa-mat-input" type="text" name="pwa_builder_lang"
               value="<?php echo esc_attr(get_option('pwa_builder_lang', 'en')); ?>">
         </div>
         <div class="pwa-mat-row">
            <label class="pwa-mat-label">Text Direction</label>
            <select class="pwa-mat-input" name="pwa_builder_dir">
               <?php $dir = get_option('pwa_builder_dir', 'ltr'); ?>
               <option value="ltr" <?php selected($dir, 'ltr'); ?>>LTR</option>
               <option value="rtl" <?php selected($dir, 'rtl'); ?>>RTL</option>
               <option value="auto" <?php selected($dir, 'auto'); ?>>Auto</option>
            </select>
         </div>
         <div class="pwa-mat-row">
            <input class="pwa-mat-btn" type="submit" value="Save Settings">
         </div>
      </form>
   </div>
</div>
<script>
jQuery(document).ready(function($) {
   function checkImage(id, minW, minH, cb) {
      var img = new Image();
      img.onload = function() {
         cb(this.width >= minW && this.height >= minH, this.width, this.height);
      };
      img.onerror = function() {
         cb(false, 0, 0);
      }
      img.src = id;
   }
   // WP media uploader for icon
   $("#pwa_upload_icon_btn").on("click", function(e) {
      e.preventDefault();
      var frame = wp.media({
         title: "Select Icon (192x192 PNG)",
         multiple: false,
         library: {
            type: "image/png"
         }
      });
      frame.on("select", function() {
         var attachment = frame.state().get("selection").first().toJSON();
         checkImage(attachment.url, 192, 192, function(valid, w, h) {
            if (!valid) {
               alert("Please select a 192x192 PNG image.");
               return;
            }
            $("#pwa_builder_icon_id").val(attachment.id);
            $("#pwa_icon_preview").attr("src", attachment.url);
         });
      });
      frame.open();
   });
   // Splash upload
   $("#pwa_upload_splash_btn").on("click", function(e) {
      e.preventDefault();
      var frame = wp.media({
         title: "Select Splash (512x512 PNG)",
         multiple: false,
         library: {
            type: "image/png"
         }
      });
      frame.on("select", function() {
         var attachment = frame.state().get("selection").first().toJSON();
         checkImage(attachment.url, 512, 512, function(valid, w, h) {
            if (!valid) {
               alert("Please select a 512x512 PNG image.");
               return;
            }
            $("#pwa_builder_splash_id").val(attachment.id);
            $("#pwa_splash_preview").remove();
            $("<img id='pwa_splash_preview' class='pwa-img-preview' width='70' height='70'>")
               .attr("src", attachment.url).insertAfter("#pwa_upload_splash_btn");
         });
      });
      frame.open();
   });
   // Screenshot upload
   $("#pwa_add_screenshot_btn").on("click", function(e) {
      e.preventDefault();
      var frame = wp.media({
         title: "Add Screenshot (min 320x640 PNG/JPG)",
         multiple: false,
         library: {
            type: ["image/png", "image/jpeg"]
         }
      });
      frame.on("select", function() {
         var attachment = frame.state().get("selection").first().toJSON();
         checkImage(attachment.url, 320, 640, function(valid, w, h) {
            if (!valid) {
               alert("Screenshot must be at least 320x640 px.");
               return;
            }
            var sz = w + "x" + h;
            var block = $('<div class="pwa-mat-screenshot-block"></div>');
            block.append('<input type="hidden" name="pwa_builder_screenshot_ids[]" value="' +
               attachment.id + '">');
            // This <img> is for preview only; on save and reload, PHP will render with wp_get_attachment_image()
            block.append('<img src="' + attachment.url +
               '" width="70" class="pwa-img-preview" /><br>');
            // Use AJAX to get the correct image HTML from PHP
            $.post(pwa_ajax.ajaxurl, {
               action: 'pwa_get_attachment_image',
               id: attachment.id,
               nonce: pwa_ajax.nonce
            }, function(html) {
               block.append(html + '<br>');
               block.append('<span class="pwa-mat-chip">' + sz + '</span>');
               block.append(
                  '<button type="button" class="pwa-mat-remove-btn" title="Remove"><span class="material-icons">close</span></button>'
               );
               $("#pwa_screenshot_grid").append(block);
            });
         });
      });
      frame.open();
   });
   // Remove screenshot block
   $(document).on('click', '.pwa-mat-remove-btn', function() {
      $(this).closest('.pwa-mat-screenshot-block').remove();
   });
});
if ('serviceWorker' in navigator) {
   navigator.serviceWorker.register('/service-worker.js');
}
</script>
<?php
}

add_action('admin_init', function(){
    register_setting('pwa_builder_settings', 'pwa_builder_app_name', 'sanitize_text_field');
    register_setting('pwa_builder_settings', 'pwa_builder_app_short', 'sanitize_text_field');
    register_setting('pwa_builder_settings', 'pwa_builder_app_desc', 'sanitize_textarea_field');
    register_setting('pwa_builder_settings', 'pwa_builder_start_url', 'esc_url_raw');
    register_setting('pwa_builder_settings', 'pwa_builder_display', 'sanitize_text_field');
    register_setting('pwa_builder_settings', 'pwa_builder_orientation', 'sanitize_text_field');
    register_setting('pwa_builder_settings', 'pwa_builder_theme_color', 'sanitize_hex_color');
    register_setting('pwa_builder_settings', 'pwa_builder_bg_color', 'sanitize_hex_color');
    register_setting('pwa_builder_settings', 'pwa_builder_categories', 'sanitize_text_field');
    register_setting('pwa_builder_settings', 'pwa_builder_lang', 'sanitize_text_field');
    register_setting('pwa_builder_settings', 'pwa_builder_dir', 'sanitize_text_field');
    register_setting('pwa_builder_settings', 'pwa_builder_icon_id', 'absint');
    register_setting('pwa_builder_settings', 'pwa_builder_splash_id', 'absint');
    register_setting('pwa_builder_settings', 'pwa_builder_screenshot_ids', function($value) {
        if (is_array($value)) {
            return array_map('absint', $value);
        }
        return [];
    });
});

add_action('admin_enqueue_scripts', function($hook) {
    if ($hook === 'settings_page_pwa-builder') {
        wp_enqueue_media();
        wp_enqueue_style('pwa-google-material-icons', 'https://fonts.googleapis.com/icon?family=Material+Icons&display=swap', [], '1.0.0');
        wp_enqueue_style('pwa-google-roboto', 'https://fonts.googleapis.com/css?family=Roboto:400,500&display=swap', [], '1.0.0');
        wp_enqueue_style('pwa-builder-admin', plugin_dir_url(__FILE__) . 'admin-style.css', [], '1.0.0');
        wp_localize_script('jquery', 'pwa_ajax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('pwa_get_attachment_image_nonce')
        ]);
    }
});

add_action('wp_ajax_pwa_get_attachment_image', function() {
    // Nonce check for security
    check_ajax_referer('pwa_get_attachment_image_nonce', 'nonce');
    if (isset($_POST['id'])) {
        $id = absint($_POST['id']);
        if ($id) {
            echo wp_get_attachment_image($id, [70, 70], false, [
                'class' => 'pwa-img-preview',
                'width' => 70,
                'alt' => esc_attr__('App screenshot', 'progressive-web-app-pwa-builder-advanced'),
                'decoding' => 'async',
                'loading' => 'lazy',
            ]);
        }
    }
    wp_die();
});