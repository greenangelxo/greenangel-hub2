<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

/**
 * Angel Wallet - Shipping Exemptions for Top-up Products
 * Automatically removes shipping charges for wallet top-ups since they're instant digital products
 */

/**
 * Hide shipping when cart contains only top-up products
 */
add_filter('woocommerce_cart_needs_shipping', 'greenangel_topup_cart_needs_shipping', 50);
function greenangel_topup_cart_needs_shipping($needs_shipping) {
    // If cart doesn't need shipping for other reasons, don't override
    if (!$needs_shipping) {
        return false;
    }
    
    // Check if cart contains only top-up products
    if (greenangel_cart_contains_only_topups()) {
        error_log("Angel Wallet: Cart contains only top-ups - disabling shipping");
        return false;
    }
    
    return $needs_shipping;
}

/**
 * Check if cart contains only top-up products
 */
function greenangel_cart_contains_only_topups() {
    if (!WC()->cart || WC()->cart->is_empty()) {
        return false;
    }
    
    foreach (WC()->cart->get_cart() as $cart_item) {
        $product = $cart_item['data'];
        if (!$product || !$product->get_id()) continue;
        
        $product_id = $product->get_id();
        
        // Check if this product is a top-up using the same logic as validation
        $is_topup = false;
        
        // Check if product has 'top-up' category
        if (has_term('top-up', 'product_cat', $product_id)) {
            $is_topup = true;
        }
        
        // Check product name/title for top-up indicators
        $product_name = strtolower($product->get_name());
        if (strpos($product_name, 'top-up') !== false || 
            strpos($product_name, 'wallet') !== false ||
            strpos($product_name, 'credit') !== false ||
            preg_match('/£\d+\s*(top.?up|credit|wallet)/i', $product_name)) {
            $is_topup = true;
        }
        
        // Check product SKU for top-up indicators
        $product_sku = strtolower($product->get_sku());
        if (strpos($product_sku, 'topup') !== false || 
            strpos($product_sku, 'wallet') !== false ||
            strpos($product_sku, 'credit') !== false) {
            $is_topup = true;
        }
        
        // If we find any non-top-up product, cart is not top-up only
        if (!$is_topup) {
            return false;
        }
    }
    
    // All products in cart are top-ups
    return true;
}

/**
 * Remove shipping methods when cart contains only top-ups
 */
add_filter('woocommerce_package_rates', 'greenangel_remove_shipping_for_topups', 100, 2);
function greenangel_remove_shipping_for_topups($rates, $package) {
    // Only apply to main shipping package
    if (!isset($package['contents']) || empty($package['contents'])) {
        return $rates;
    }
    
    // Check if this package contains only top-up products
    $contains_only_topups = true;
    
    foreach ($package['contents'] as $cart_item) {
        $product = $cart_item['data'];
        if (!$product || !$product->get_id()) continue;
        
        $product_id = $product->get_id();
        $is_topup = false;
        
        // Same detection logic as above
        if (has_term('top-up', 'product_cat', $product_id)) {
            $is_topup = true;
        }
        
        $product_name = strtolower($product->get_name());
        if (strpos($product_name, 'top-up') !== false || 
            strpos($product_name, 'wallet') !== false ||
            strpos($product_name, 'credit') !== false ||
            preg_match('/£\d+\s*(top.?up|credit|wallet)/i', $product_name)) {
            $is_topup = true;
        }
        
        $product_sku = strtolower($product->get_sku());
        if (strpos($product_sku, 'topup') !== false || 
            strpos($product_sku, 'wallet') !== false ||
            strpos($product_sku, 'credit') !== false) {
            $is_topup = true;
        }
        
        if (!$is_topup) {
            $contains_only_topups = false;
            break;
        }
    }
    
    // If package contains only top-ups, remove all shipping rates
    if ($contains_only_topups) {
        error_log("Angel Wallet: Removing shipping rates for top-up only package");
        return array(); // Return empty array to hide shipping options
    }
    
    return $rates;
}

/**
 * Add a free shipping method specifically for top-up products
 */
add_filter('woocommerce_package_rates', 'greenangel_add_free_shipping_for_topups', 110, 2);
function greenangel_add_free_shipping_for_topups($rates, $package) {
    // Only apply to packages that contain only top-ups
    if (!isset($package['contents']) || empty($package['contents'])) {
        return $rates;
    }
    
    // Check if this package contains only top-up products
    $contains_only_topups = true;
    
    foreach ($package['contents'] as $cart_item) {
        $product = $cart_item['data'];
        if (!$product || !$product->get_id()) continue;
        
        $product_id = $product->get_id();
        $is_topup = false;
        
        // Same detection logic
        if (has_term('top-up', 'product_cat', $product_id)) {
            $is_topup = true;
        }
        
        $product_name = strtolower($product->get_name());
        if (strpos($product_name, 'top-up') !== false || 
            strpos($product_name, 'wallet') !== false ||
            strpos($product_name, 'credit') !== false ||
            preg_match('/£\d+\s*(top.?up|credit|wallet)/i', $product_name)) {
            $is_topup = true;
        }
        
        $product_sku = strtolower($product->get_sku());
        if (strpos($product_sku, 'topup') !== false || 
            strpos($product_sku, 'wallet') !== false ||
            strpos($product_sku, 'credit') !== false) {
            $is_topup = true;
        }
        
        if (!$is_topup) {
            $contains_only_topups = false;
            break;
        }
    }
    
    // If package contains only top-ups, add a special "instant delivery" method
    if ($contains_only_topups) {
        error_log("Angel Wallet: Adding instant delivery for top-up package");
        
        $rates['angel_instant_delivery'] = new WC_Shipping_Rate(
            'angel_instant_delivery',
            '✨ Instant Delivery (Wallet Credits)',
            0, // Cost is 0
            array(), // No taxes
            'angel_instant'
        );
    }
    
    return $rates;
}

/**
 * Customize checkout display for top-up orders
 */
add_action('woocommerce_review_order_after_shipping', 'greenangel_topup_checkout_notice');
function greenangel_topup_checkout_notice() {
    if (greenangel_cart_contains_only_topups()) {
        ?>
        <tr class="angel-instant-notice">
            <th colspan="2" style="text-align: center; padding: 15px; background: linear-gradient(135deg, #1a1a1a, #222); border: 2px solid #aed604; border-radius: 8px; color: #fff;">
                <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <span style="font-size: 20px;">⚡</span>
                    <span style="color: #aed604; font-weight: 600;">Credits added instantly after payment!</span>
                </div>
            </th>
        </tr>
        <?php
    }
}

/**
 * Hide delivery date selector for top-up only carts
 */
add_filter('woocommerce_checkout_fields', 'greenangel_hide_delivery_date_for_topups');
function greenangel_hide_delivery_date_for_topups($fields) {
    if (greenangel_cart_contains_only_topups()) {
        // Remove delivery date field if it exists
        if (isset($fields['billing']['delivery_date'])) {
            unset($fields['billing']['delivery_date']);
        }
        if (isset($fields['shipping']['delivery_date'])) {
            unset($fields['shipping']['delivery_date']);
        }
        if (isset($fields['order']['delivery_date'])) {
            unset($fields['order']['delivery_date']);
        }
    }
    
    return $fields;
}

/**
 * Add CSS to style the instant delivery notice
 */
add_action('wp_head', 'greenangel_topup_checkout_styles');
function greenangel_topup_checkout_styles() {
    if (is_checkout()) {
        ?>
        <style>
        .angel-instant-notice td,
        .angel-instant-notice th {
            border: none !important;
            padding: 15px !important;
        }
        
        .angel-instant-notice {
            animation: angelGlow 2s ease-in-out infinite alternate;
        }
        
        @keyframes angelGlow {
            from { box-shadow: 0 0 5px rgba(174, 214, 4, 0.3); }
            to { box-shadow: 0 0 15px rgba(174, 214, 4, 0.6); }
        }
        
        /* Hide shipping calculator for top-up only carts */
        body.topup-only-cart .shipping-calculator-form,
        body.topup-only-cart .woocommerce-shipping-calculator {
            display: none !important;
        }
        </style>
        
        <?php if (greenangel_cart_contains_only_topups()): ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('topup-only-cart');
            
            // Hide any delivery date pickers that might still be showing
            const deliveryFields = document.querySelectorAll('[name*="delivery"], [id*="delivery"], .delivery-date, .delivery_date');
            deliveryFields.forEach(field => {
                const container = field.closest('.form-row, .field-container, tr, .input-group');
                if (container) {
                    container.style.display = 'none';
                }
            });
        });
        </script>
        <?php endif; ?>
        <?php
    }
}

/**
 * Set top-up products as virtual (digital) automatically
 */
add_action('woocommerce_product_data_tabs', 'greenangel_auto_set_topups_virtual');
function greenangel_auto_set_topups_virtual() {
    // This will run when products are saved/updated
    global $post;
    
    if (!$post || $post->post_type !== 'product') return;
    
    $product_id = $post->ID;
    
    // Check if this is a top-up product
    $is_topup = false;
    
    if (has_term('top-up', 'product_cat', $product_id)) {
        $is_topup = true;
    }
    
    $product_name = strtolower(get_the_title($product_id));
    if (strpos($product_name, 'top-up') !== false || 
        strpos($product_name, 'wallet') !== false ||
        strpos($product_name, 'credit') !== false) {
        $is_topup = true;
    }
    
    // If it's a top-up, make sure it's set as virtual
    if ($is_topup) {
        $product = wc_get_product($product_id);
        if ($product && !$product->is_virtual()) {
            update_post_meta($product_id, '_virtual', 'yes');
            error_log("Angel Wallet: Auto-set product #{$product_id} as virtual (top-up)");
        }
    }
}

/**
 * Auto-set new top-up products as virtual when they're created
 */
add_action('save_post_product', 'greenangel_auto_set_new_topup_virtual', 10, 3);
function greenangel_auto_set_new_topup_virtual($post_id, $post, $update) {
    // Skip if this is an update or auto-save
    if ($update || wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }
    
    // Check if this is a top-up product
    $is_topup = false;
    
    if (has_term('top-up', 'product_cat', $post_id)) {
        $is_topup = true;
    }
    
    $product_name = strtolower($post->post_title);
    if (strpos($product_name, 'top-up') !== false || 
        strpos($product_name, 'wallet') !== false ||
        strpos($product_name, 'credit') !== false) {
        $is_topup = true;
    }
    
    // If it's a new top-up product, set it as virtual
    if ($is_topup) {
        update_post_meta($post_id, '_virtual', 'yes');
        update_post_meta($post_id, '_downloadable', 'no'); // Not downloadable, just virtual
        error_log("Angel Wallet: Auto-set new product #{$post_id} as virtual (top-up)");
    }
}

/**
 * Add admin notice to explain the automatic behavior
 */
add_action('admin_notices', 'greenangel_topup_shipping_admin_notice');
function greenangel_topup_shipping_admin_notice() {
    $screen = get_current_screen();
    
    // Only show on WooCommerce pages
    if (!$screen || strpos($screen->id, 'woocommerce') === false) {
        return;
    }
    
    // Only show occasionally, not every page load
    if (get_transient('angel_wallet_shipping_notice_shown')) {
        return;
    }
    
    set_transient('angel_wallet_shipping_notice_shown', true, DAY_IN_SECONDS);
    
    ?>
    <div class="notice notice-info is-dismissible">
        <h4>🚀 Angel Wallet - Shipping Configuration</h4>
        <p>
            <strong>Good news!</strong> Angel Wallet automatically handles shipping for top-up products:
        </p>
        <ul style="margin-left: 20px;">
            <li>✅ Top-up products are automatically set as <strong>virtual</strong> (no shipping needed)</li>
            <li>✅ Delivery date selectors are hidden for wallet-only orders</li>
            <li>✅ Customers see "Instant Delivery" instead of shipping options</li>
            <li>✅ No additional configuration required!</li>
        </ul>
        <p><em>This applies to any product in the "Top-Up" category or with "top-up", "wallet", or "credit" in the name.</em></p>
    </div>
    <?php
}

/**
 * Debug function to check shipping status
 */
add_action('wp_footer', 'greenangel_debug_shipping_status');
function greenangel_debug_shipping_status() {
    // Only show debug for logged-in admins
    if (!current_user_can('manage_options') || !is_checkout()) {
        return;
    }
    
    $cart_needs_shipping = WC()->cart ? WC()->cart->needs_shipping() : false;
    $contains_only_topups = greenangel_cart_contains_only_topups();
    
    ?>
    <!-- Angel Wallet Shipping Debug (Admin Only) -->
    <script>
    console.log('Angel Wallet Shipping Debug:');
    console.log('- Cart needs shipping:', <?php echo $cart_needs_shipping ? 'true' : 'false'; ?>);
    console.log('- Contains only top-ups:', <?php echo $contains_only_topups ? 'true' : 'false'; ?>);
    console.log('- Cart contents:', <?php echo WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?>);
    </script>
    <?php
}

/**
 * Make sure top-up products bypass any shipping requirements
 */
add_filter('woocommerce_cart_ready_to_calc_shipping', 'greenangel_topup_bypass_shipping_calc');
function greenangel_topup_bypass_shipping_calc($ready) {
    if (greenangel_cart_contains_only_topups()) {
        // Don't calculate shipping for top-up only carts
        return false;
    }
    return $ready;
}
?>