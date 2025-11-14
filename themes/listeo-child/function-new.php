<?php
add_filter('woocommerce_email_enabled_customer_on_hold_order', 'disable_yaymail_for_arrival', 10, 2);
add_filter('woocommerce_email_enabled_customer_processing_order', 'disable_yaymail_for_arrival', 10, 2);
add_filter('woocommerce_email_enabled_customer_completed_order', 'disable_yaymail_for_arrival', 10, 2);

function disable_yaymail_for_arrival($enabled, $order) {
    if (!$order instanceof WC_Order) return $enabled;

    // Product IDs that should disable YayMail if found in the order
    $excluded_product_ids = [222556, 222557, 222558];

    // Check for arrival date
    // Check if order contains any excluded product
    $has_excluded_product = false;
    foreach ($order->get_items() as $item) {
        if (in_array($item->get_product_id(), $excluded_product_ids)) {
            $has_excluded_product = true;
            break;
        }
    }

    // Disable YayMail if arrival date exists OR order contains excluded product
    if ( $has_excluded_product) {
        return false;
    }

    return $enabled;
}



add_action('woocommerce_order_status_on-hold', 'send_custom_arrival_email', 20);
add_action('woocommerce_order_status_processing', 'send_custom_arrival_email', 20);
add_action('woocommerce_order_status_completed', 'send_custom_arrival_email', 20);

function send_custom_arrival_email($order_id, $from_reminder = false) {
    $order = wc_get_order($order_id);
    if (!$order) return;

    // ✅ Do not send for Dokan sub-orders
    if (get_post_meta($order_id, '_created_via', true) == 'dokan') {
        return;
    }

    // ✅ Only send if these product IDs exist in the order
    $allowed_product_ids = [222556, 222557, 222558];
    $found_allowed_product = false;

    foreach ($order->get_items() as $item) {
        $pid = $item->get_product_id();
        $parent_id = wp_get_post_parent_id($pid);

        if (in_array((int)$pid, $allowed_product_ids, true) ||
            in_array((int)$parent_id, $allowed_product_ids, true)) {
            $found_allowed_product = true;
            break;
        }
    }
	
	if ($order->get_status() !== 'completed') {
		return;
	}

    // ❌ No allowed product — do not send email
    if (!$found_allowed_product) {
        return;
    }
	
	$rand_password = get_post_meta($order_id, '_random_password', true);
	if (empty($rand_password)) {
		$rand_password = wp_generate_password(10, false);
		update_post_meta($order_id, '_random_password', $rand_password);
	}

    // 📩 Load template and send email
    $order_lang = get_post_meta($order_id, 'wpml_language', true);
    ob_start();
    $template_path = get_stylesheet_directory() . '/email-template/';
    switch ($order_lang) {
        case 'en':
            $template_file = 'christmas-email-en.php';
            break;
        case 'fr':
            $template_file = 'christmas-email-fr.php';
            break;
        default:
            $template_file = 'christmas-email.php'; // German
            break;
    }

    include $template_path . $template_file;

    $message = ob_get_clean();

    $to = $order->get_billing_email();
    if($order_lang === 'en'){
        $subject = 'Enjoy your voucher!';
    }elseif($order_lang === 'fr'){
        $subject = "Profite bien de ton bon d'achat !";
    }else{
        $subject = 'Viel Freude mit deinem Gutschein!';
    }
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    wp_mail($to, $subject, $message, $headers);
}


function get_coupon_pdf_url_from_code( $coupon_code ) {
    global $wpdb;

    // 1. Find coupon post ID by code
    $coupon_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} 
         WHERE post_type = 'shop_coupon' 
         AND post_title = %s 
         LIMIT 1",
        $coupon_code
    ));

    if ( ! $coupon_id ) {
        return false;
    }

    // 2. Get serialized data
    $coupon_data = get_post_meta( $coupon_id, '_fcpdf_coupon_data', true );

    if ( ! $coupon_data || ! is_array( $coupon_data ) ) {
        return false;
    }

    // 3. Return coupon_url if available
    return isset( $coupon_data['coupon_url'] ) ? $coupon_data['coupon_url'] : false;
}

function get_order_products_details($order_id) {
    $order = wc_get_order($order_id);
    
    if (!$order) {
        return false;
    }
    
    $products = array();
    
    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        $products[] = array(
            'name' => $product ? $product->get_name() : $item->get_name(),
            'quantity' => $item->get_quantity(),
            'price' => $item->get_total(),
            'product_id' => $item->get_product_id(),
            'variation_id' => $item->get_variation_id()
        );
    }
    
    return $products;
}
function get_order_coupon_code($order_id) {
    global $wpdb;

    $meta_key = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT meta_key 
            FROM {$wpdb->postmeta} 
            WHERE post_id = %d AND meta_key LIKE %s 
            LIMIT 1",
            $order_id,
            '%_coupon_code'
        )
    );

    if ($meta_key) {
        return get_post_meta($order_id, $meta_key, true);
    }

    return false;
}

function get_order_product_categories($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) return [];

    $categories = [];

    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        if ($product) {
            // If variation, get parent product
            $product_id = $product->is_type('variation') ? $product->get_parent_id() : $product->get_id();

            // WPML: get product in default language
            if (function_exists('wpml_object_id')) {
                $default_lang = apply_filters('wpml_default_language', null);
                $original_product_id = apply_filters('wpml_object_id', $product_id, 'product', false, $default_lang);
            } else {
                $original_product_id = $product_id;
            }

            // Get categories
            $terms = get_the_terms($original_product_id, 'product_cat');
            if (!empty($terms) && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $categories[] = $term->name;
                }
            }
        }
    }

    return array_unique($categories); // remove duplicates
}


/*********************************************************************************/

// Loaction page template Popup shortcodes
function smoobu_booking_shortcode($atts) {
    global $post;

    // Shortcode attributes (optional overrides)
    $atts = shortcode_atts([
    'product_id' => get_post_meta($post->ID, 'product_id', true),
    ], $atts, 'smoobu_booking');


    $woo_product_id = intval($atts['product_id']);
    $listing_product = wc_get_product($woo_product_id);

    if (!empty($listing_product)) {
        $property_id = $listing_product->get_meta('custom_property_id_field');
        $base_price = get_post_meta($woo_product_id, 'sa_cfw_cog_amount', true);
    } else {
        $property_id = 0;
        $base_price = 0;
    }
    // Get WPML language
    if (function_exists('icl_get_language_code')) {
        $current_language = icl_get_language_code();
    } else {
        $current_language = '';
    }



    // Construct base URL
    $base_url = get_home_url(null);
    if ($current_language !== 'de' && !empty($current_language)) {
        $base_url .= '/' . $current_language;
    }
    // Disable booking for some listings
    //$disableLocIDs = [149074, 149392, 149393, 149391, 149070, 149390, 149076, 149443, 149442, 149078, 149448, 149447, 145731, 146191, 146190];
    $disableLocIDs = [];
    ob_start();
    ?>


    <div class="elementor-element elementor-element-61a8cc2 elementor-absolute elementor-view-default elementor-widget elementor-widget-icon" data-id="61a8cc2" data-element_type="widget" id="calendar_popup_close" data-settings="{&quot;_position&quot;:&quot;absolute&quot;}" data-widget_type="icon.default">
        <div class="elementor-widget-container">
        <div class="elementor-icon-wrapper">
            <div class="elementor-icon">
                <svg aria-hidden="true" class="e-font-icon-svg e-fas-window-close" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M464 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h416c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zm-83.6 290.5c4.8 4.8 4.8 12.6 0 17.4l-40.5 40.5c-4.8 4.8-12.6 4.8-17.4 0L256 313.3l-66.5 67.1c-4.8 4.8-12.6 4.8-17.4 0l-40.5-40.5c-4.8-4.8-4.8-12.6 0-17.4l67.1-66.5-67.1-66.5c-4.8-4.8-4.8-12.6 0-17.4l40.5-40.5c4.8-4.8 12.6-4.8 17.4 0l66.5 67.1 66.5-67.1c4.8-4.8 12.6-4.8 17.4 0l40.5 40.5c4.8 4.8 4.8 12.6 0 17.4L313.3 256l67.1 66.5z"></path></svg></div>
            </div>
        </div>
    </div>

    <div class="sidebarnewmodeule">
        <?php if ( !in_array($post->ID, $disableLocIDs) ) : ?>
            <?php if ($property_id): ?>
            <?php
            $checkout_page_id = get_option('woocommerce_checkout_page_id');
            echo do_shortcode("[smoobu_calendar property_id='$property_id' layout='1x3' link='"
            . esc_url($base_url)
            . "?buy-now=$woo_product_id&qty=1&coupon=&ship-via=free_shipping&page=$checkout_page_id&with-cart=0&prices=$base_price']");
            ?>
        <?php else: ?>
        <p>
            <a href="javascript:void(0)" data-toggle="modal" data-target="#myModa22"
            class="button book-now fullwidth margin-top-5 hash-custom-book-id">
                <span class="book-now-text"><?php esc_html_e('Jetzt buchen', 'listeo_core'); ?></span>
            </a>
        </p>
        <?php endif; ?>
        <?php else : ?>
            <?php
            $currency_symbol = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '';
            $formatted_price = sprintf(__('From %1$s%2$s / Night', 'smoobu-calendar'), $base_price, $currency_symbol);
            ?>
            <div class="smoobu-price-display-container"><?php echo $formatted_price; ?></div>
            <h4 class="text-center">
                <?php echo __('For bookings please contact us at: <a href="mailto:contact@reserve-ta-bulle.fr">contact@reserve-ta-bulle.fr</a>', 'listeo_core'); ?>
            </h4>
            <style>.container .nwesidedetail .pricelisting {display: none;}</style>
        <?php endif; ?>
    </div>


<script>
jQuery(document).ready(function ($) {
    $(".smoobu-calendar-button-container").on("click", function () {
        if ($(".smoobu-calendar").val() === "") {
            // Use jQuery to access shadow DOM safely
            var shadowHost = $(".easepick-wrapper").get(0);



            if (shadowHost && shadowHost.shadowRoot) {
                var $myDiv = $(shadowHost.shadowRoot).find("div").first();



            $myDiv.css({
                "z-index": "100",
                "top": "40px",
                "left": "-184.778px"
                }).addClass("show");
            }
        }
    });
});
</script>
  <?php



return ob_get_clean();
}
add_shortcode('smoobu_booking', 'smoobu_booking_shortcode');

add_shortcode('mwew_product_popup', function ($atts) {
    $atts = shortcode_atts(array(
        'id' => 0,
    ), $atts);

    $product_id = intval($atts['id']);
    if (!$product_id) return '<p>No product ID provided.</p>';

    $product = wc_get_product($product_id);
    if (!$product) return '<p>Invalid product.</p>';

    $title       = $product->get_name();
    $price_html  = $product->get_price_html();
    $description = $product->get_description();
    $featured_img_url = wp_get_attachment_url($product->get_image_id());

    ob_start();

    ?>
	<div class="elementor-element elementor-element-61a8cc2 elementor-absolute elementor-view-default elementor-widget elementor-widget-icon" data-id="61a8cc2" data-element_type="widget" id="voucher_popup_close" data-settings="{&quot;_position&quot;:&quot;absolute&quot;}" data-widget_type="icon.default">
		<div class="elementor-widget-container">
			<div class="elementor-icon-wrapper">
				<div class="elementor-icon">
					<svg aria-hidden="true" class="e-font-icon-svg e-fas-window-close" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M464 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h416c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zm-83.6 290.5c4.8 4.8 4.8 12.6 0 17.4l-40.5 40.5c-4.8 4.8-12.6 4.8-17.4 0L256 313.3l-66.5 67.1c-4.8 4.8-12.6 4.8-17.4 0l-40.5-40.5c-4.8-4.8-4.8-12.6 0-17.4l67.1-66.5-67.1-66.5c-4.8-4.8-4.8-12.6 0-17.4l40.5-40.5c4.8-4.8 12.6-4.8 17.4 0l66.5 67.1 66.5-67.1c4.8-4.8 12.6-4.8 17.4 0l40.5 40.5c4.8 4.8 4.8 12.6 0 17.4L313.3 256l67.1 66.5z"></path></svg>			</div>
			</div>
		</div>
	</div>
    <div id="mwew-popup-<?php echo esc_attr($product_id); ?>" class="custom-product-popup" style="display:flex; gap:30px;">
        <div class="popup-image" style="flex:1; min-width:200px;">
            <?php if ($featured_img_url): ?>
                <div style="background-image: url(<?php echo esc_url($featured_img_url); ?>); width:100%; height: 100%; border-radius: 8px; background-size: cover; background-position: center;"></div>
            <?php endif; ?>
        </div>

        <div class="popup-details" style="flex:1;">
            <h2 class="popup-title" style="font-size:24px; margin-bottom:10px;"><?php echo esc_html($title); ?></h2>
            <div class="popup-price" style="font-weight:bold; font-size:18px; color:#3D6B50; margin-bottom:15px;">
                <?php echo wp_kses_post($price_html); ?>
            </div>
            <?php
            // 🔥 Force global product for WooCommerce templates
            global $product;
            $backup_product = $product;
            $product = wc_get_product($product_id);

            woocommerce_template_single_add_to_cart();

            // Restore global product
            $product = $backup_product;
            ?>
        </div>
	</div>
	<style>
		.quantity,.wgm-info.woocommerce_de_versandkosten{
			display:none !important;
		}
		.single_variation {
			padding:10px 16px;
		}
	</style>
	<?php
	if (is_page_template('template-location-page.php')) {
	?>
	<?php
	}
	?>
    <?php

    return ob_get_clean();
});

// ✅ Load WooCommerce variation scripts
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('wc-add-to-cart-variation');
});

function my_remove_cashier_side_cart_script() {
    if ( is_page(235452) || is_page(235518) || is_page(235520)) {

        // Fully remove side cart scripts
        wp_dequeue_script('sa-cfw-sidecart');
        wp_deregister_script('sa-cfw-sidecart');

        // Remove fragment script if used
        wp_dequeue_script('sa-cfw-fragments');
        wp_deregister_script('sa-cfw-fragments');

        // Remove inline scripts
        wp_dequeue_script('jquery-blockui');
        wp_dequeue_script('wc-cart-fragments');
    }
}
add_action('wp_enqueue_scripts', 'my_remove_cashier_side_cart_script', 9999);


?>