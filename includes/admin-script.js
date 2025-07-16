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
    $("#progwebapp_upload_icon_btn").on("click", function(e) {
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
                $("#progwebapp_builder_icon_id").val(attachment.id);
                $("#progwebapp_icon_preview").attr("src", attachment.url);
            });
        });
        frame.open();
    });
    
    // Splash upload
    $("#progwebapp_upload_splash_btn").on("click", function(e) {
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
                $("#progwebapp_builder_splash_id").val(attachment.id);
                $("#progwebapp_splash_preview").remove();
                $("<img id='progwebapp_splash_preview' class='progwebapp-img-preview' width='70' height='70'>")
                    .attr("src", attachment.url).insertAfter("#progwebapp_upload_splash_btn");
            });
        });
        frame.open();
    });
    
    // Screenshot upload
    $("#progwebapp_add_screenshot_btn").on("click", function(e) {
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
                var block = $('<div class="progwebapp-mat-screenshot-block"></div>');
                block.append('<input type="hidden" name="progwebapp_builder_screenshot_ids[]" value="' +
                    attachment.id + '">');
                // This <img> is for preview only; on save and reload, PHP will render with wp_get_attachment_image()
                block.append('<img src="' + attachment.url +
                    '" width="70" class="progwebapp-img-preview" /><br>');
                // Use AJAX to get the correct image HTML from PHP
                $.post(progwebapp_ajax.ajaxurl, {
                    action: 'progwebapp_get_attachment_image',
                    id: attachment.id,
                    nonce: progwebapp_ajax.nonce
                }, function(html) {
                    block.append(html + '<br>');
                    block.append('<span class="progwebapp-mat-chip">' + sz + '</span>');
                    block.append(
                        '<button type="button" class="progwebapp-mat-remove-btn" title="Remove"><span class="material-icons">close</span></button>'
                    );
                    $("#progwebapp_screenshot_grid").append(block);
                });
            });
        });
        frame.open();
    });
    
    // Remove screenshot block
    $(document).on('click', '.progwebapp-mat-remove-btn', function() {
        $(this).closest('.progwebapp-mat-screenshot-block').remove();
    });
});