<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

/**
 * Angel Wallet Cart Validation & Restrictions - ENHANCED BULLETPROOF VERSION
 * Ensures top-ups can only be purchased alone for proper processing flow
 */

/**
 * ENHANCED: Check if cart contains top-up products with better detection
 */
function greenangel_cart_has_topup() {
    if (!WC()->cart || WC()->cart->is_empty()) return false;
    
    foreach (WC()->cart->get_cart() as $cart_item) {
        $product = $cart_item['data'];
        if (!$product || !$product->get_id()) continue;
        
        $product_id = $product->get_id();
        
        // Multiple ways to detect top-up products
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
        
        if ($is_topup) {
            error_log("Angel Wallet: Found top-up product in cart: " . $product->get_name() . " (ID: " . $product_id . ")");
            return true;
        }
    }
    return false;
}

/**
 * ENHANCED: Check if cart contains non-top-up products with better detection
 */
function greenangel_cart_has_non_topup() {
    if (!WC()->cart || WC()->cart->is_empty()) return false;
    
    foreach (WC()->cart->get_cart() as $cart_item) {
        $product = $cart_item['data'];
        if (!$product || !$product->get_id()) continue;
        
        $product_id = $product->get_id();
        
        // Multiple ways to detect top-up products
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
        
        // If this product is NOT a top-up, we have non-top-up items
        if (!$is_topup) {
            error_log("Angel Wallet: Found non-top-up product in cart: " . $product->get_name() . " (ID: " . $product_id . ")");
            return true;
        }
    }
    return false;
}

/**
 * ENHANCED: Centralized function to detect if a product is a top-up
 */
function greenangel_is_product_topup($product_id) {
    $product_id = absint($product_id);
    if (!$product_id) return false;
    
    // Check if product has 'top-up' category
    if (has_term('top-up', 'product_cat', $product_id)) {
        return true;
    }
    
    $product = wc_get_product($product_id);
    if (!$product) return false;
    
    // Check product name/title for top-up indicators
    $product_name = strtolower($product->get_name());
    if (strpos($product_name, 'top-up') !== false || 
        strpos($product_name, 'wallet') !== false ||
        strpos($product_name, 'credit') !== false ||
        preg_match('/£\d+\s*(top.?up|credit|wallet)/i', $product_name)) {
        return true;
    }
    
    // Check product SKU for top-up indicators
    $product_sku = strtolower($product->get_sku());
    if (strpos($product_sku, 'topup') !== false || 
        strpos($product_sku, 'wallet') !== false ||
        strpos($product_sku, 'credit') !== false) {
        return true;
    }
    
    return false;
}

/**
 * ENHANCED BULLETPROOF: Smart cart validation that only blocks actual conflicts
 */
add_filter('woocommerce_add_to_cart_validation', 'greenangel_validate_cart_topup_restriction', 15, 3);
function greenangel_validate_cart_topup_restriction($passed, $product_id, $quantity) {
    // If validation already failed, don't interfere
    if (!$passed) return false;
    
    // Skip if validation is disabled by admin
    if (get_option('angel_wallet_disable_validation', false)) {
        error_log("Angel Wallet: Validation bypassed by admin setting");
        return $passed;
    }
    
    // Get the product being added
    $product = wc_get_product($product_id);
    if (!$product) return $passed;
    
    // Determine if the product being added is a top-up
    $adding_topup = greenangel_is_product_topup($product_id);
    
    // If cart is empty, always allow (first product)
    if (!WC()->cart || WC()->cart->is_empty()) {
        error_log("Angel Wallet: Cart is empty - allowing product: " . $product->get_name());
        return true;
    }
    
    // Check current cart contents
    $cart_has_topup = greenangel_cart_has_topup();
    $cart_has_non_topup = greenangel_cart_has_non_topup();
    
    // Enhanced debug logging
    error_log("Angel Wallet Validation Debug:");
    error_log("- Adding product: " . $product->get_name() . " (ID: $product_id)");
    error_log("- Is adding topup: " . ($adding_topup ? 'YES' : 'NO'));
    error_log("- Cart has topup: " . ($cart_has_topup ? 'YES' : 'NO'));
    error_log("- Cart has non-topup: " . ($cart_has_non_topup ? 'YES' : 'NO'));
    error_log("- Cart contents count: " . WC()->cart->get_cart_contents_count());
    
    // RULE 1: If trying to add a top-up when cart has non-top-up items
    if ($adding_topup && $cart_has_non_topup) {
        error_log("Angel Wallet: BLOCKING - Adding topup to cart with non-topup items");
        wc_add_notice(
            '💸 Angel Wallet top-ups must be purchased separately. Please checkout your current items first, then add wallet credits.',
            'error'
        );
        return false;
    }
    
    // RULE 2: If trying to add non-top-up when cart has top-ups
    if (!$adding_topup && $cart_has_topup) {
        error_log("Angel Wallet: BLOCKING - Adding non-topup to cart with topup items");
        wc_add_notice(
            '🛍️ Your cart contains Angel Wallet top-ups which must be purchased alone. Please complete your wallet purchase first, then add other items.',
            'error'
        );
        return false;
    }
    
    // RULE 3: Allow everything else (including regular products with regular products)
    error_log("Angel Wallet: ALLOWING - No conflicts detected");
    return true;
}

/**
 * ENHANCED AJAX handler with better error handling and security
 */
add_action('wp_ajax_woocommerce_add_to_cart', 'greenangel_ajax_add_to_cart_handler', 5);
add_action('wp_ajax_nopriv_woocommerce_add_to_cart', 'greenangel_ajax_add_to_cart_handler', 5);
function greenangel_ajax_add_to_cart_handler() {
    // Skip if validation is disabled
    if (get_option('angel_wallet_disable_validation', false)) {
        return; // Let WooCommerce handle normally
    }
    
    // Get the product ID and validate
    $product_id = absint($_POST['product_id'] ?? 0);
    $quantity = absint($_POST['quantity'] ?? 1);
    
    if (!$product_id) {
        wp_send_json_error([
            'error' => true,
            'message' => 'Invalid product'
        ]);
        return;
    }
    
    // Clear any existing notices first
    wc_clear_notices();
    
    // Run our validation first
    $validation_passed = greenangel_validate_cart_topup_restriction(true, $product_id, $quantity);
    
    if (!$validation_passed) {
        $notices = wc_get_notices('error');
        $error_message = 'Unable to add to cart';
        
        if (!empty($notices)) {
            $error_message = wp_strip_all_tags($notices[0]['notice']);
        }
        
        wc_clear_notices(); // Clear the notices
        
        wp_send_json_error([
            'error' => true,
            'message' => $error_message
        ]);
        return; // Stop execution here
    }
    
    // If validation passed, let WooCommerce handle the add to cart
    // Don't interfere with the normal process
    return;
}

/**
 * EMERGENCY BYPASS: Add admin option to disable wallet validation temporarily
 */
add_action('admin_menu', 'greenangel_add_debug_menu');
function greenangel_add_debug_menu() {
    if (current_user_can('manage_options')) {
        add_submenu_page(
            'woocommerce',
            'Angel Wallet Debug',
            'Angel Wallet Debug',
            'manage_options',
            'angel-wallet-debug',
            'greenangel_debug_page'
        );
    }
}

function greenangel_debug_page() {
    if (isset($_POST['toggle_validation'])) {
        $current = get_option('angel_wallet_disable_validation', false);
        update_option('angel_wallet_disable_validation', !$current);
        echo '<div class="notice notice-success"><p>Validation ' . ($current ? 'enabled' : 'disabled') . '</p></div>';
    }
    
    $disabled = get_option('angel_wallet_disable_validation', false);
    ?>
    <div class="wrap">
        <h1>🔧 Angel Wallet Debug</h1>
        <div style="background: #fff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #aed604;">
            <h2>Emergency Controls</h2>
            <form method="post">
                <p><strong>Current status:</strong> Validation is <span style="color: <?php echo $disabled ? '#e74c3c' : '#27ae60'; ?>; font-weight: bold;"><?php echo $disabled ? 'DISABLED' : 'ENABLED'; ?></span></p>
                <button type="submit" name="toggle_validation" class="button button-primary">
                    <?php echo $disabled ? '✅ Enable' : '❌ Disable'; ?> Validation
                </button>
                <p><em>Use this if validation is blocking legitimate purchases. Don't forget to re-enable!</em></p>
            </form>
        </div>
        
        <div style="background: #fff; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h2>🔍 Current Cart Debug Info</h2>
            <?php if (WC()->cart): ?>
            <ul>
                <li><strong>Cart contents:</strong> <?php echo WC()->cart->get_cart_contents_count(); ?> items</li>
                <li><strong>Has top-up:</strong> <?php echo greenangel_cart_has_topup() ? '✅ YES' : '❌ NO'; ?></li>
                <li><strong>Has non-top-up:</strong> <?php echo greenangel_cart_has_non_topup() ? '✅ YES' : '❌ NO'; ?></li>
            </ul>
            
            <h3>Cart Items Breakdown:</h3>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>ID</th>
                        <th>SKU</th>
                        <th>Categories</th>
                        <th>Is Top-up?</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach (WC()->cart->get_cart() as $cart_item): 
                    $product = $cart_item['data'];
                    if ($product): 
                        $is_topup = greenangel_is_product_topup($product->get_id());
                ?>
                <tr>
                    <td><strong><?php echo esc_html($product->get_name()); ?></strong></td>
                    <td><?php echo $product->get_id(); ?></td>
                    <td><?php echo esc_html($product->get_sku()); ?></td>
                    <td><?php 
                    $terms = get_the_terms($product->get_id(), 'product_cat');
                    if ($terms) {
                        echo implode(', ', array_map(function($term) { return $term->name; }, $terms));
                    } else {
                        echo 'None';
                    }
                    ?></td>
                    <td style="color: <?php echo $is_topup ? '#27ae60' : '#e74c3c'; ?>; font-weight: bold;">
                        <?php echo $is_topup ? '✅ YES' : '❌ NO'; ?>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p><em>Cart not available or empty</em></p>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Add helpful notice on cart page when it contains top-ups
 */
add_action('woocommerce_before_cart', 'greenangel_cart_topup_notice');
function greenangel_cart_topup_notice() {
    if (!greenangel_cart_has_topup()) return;
    
    $cart_count = WC()->cart->get_cart_contents_count();
    $total = WC()->cart->get_total();
    
    ?>
    <div class="wc-block-components-notice-banner is-info" style="margin-bottom: 20px; padding: 16px; background: linear-gradient(135deg, #1a1a1a, #222); border: 2px solid #aed604; border-radius: 12px; color: #fff;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 24px;">💸</span>
            <div>
                <h4 style="margin: 0 0 4px; color: #aed604; font-size: 16px; font-weight: 600;">Angel Wallet Top-Up Ready!</h4>
                <p style="margin: 0; font-size: 14px; color: #ccc;">
                    You're adding <strong><?php echo $cart_count; ?> credit<?php echo $cart_count > 1 ? 's' : ''; ?></strong> worth <strong><?php echo $total; ?></strong> to your Angel Wallet. 
                    These will be available instantly after payment! ✨
                </p>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Add notice on checkout page for wallet top-ups
 */
add_action('woocommerce_before_checkout_form', 'greenangel_checkout_topup_notice');
function greenangel_checkout_topup_notice() {
    if (!greenangel_cart_has_topup()) return;
    
    ?>
    <div class="wc-block-components-notice-banner" style="margin-bottom: 24px; padding: 20px; background: linear-gradient(135deg, #1a1a1a, #222); border: 2px solid #aed604; border-radius: 16px; color: #fff; text-align: center;">
        <div style="font-size: 32px; margin-bottom: 12px;">👼</div>
        <h3 style="margin: 0 0 8px; color: #aed604; font-size: 20px; font-weight: 700;">Angel Wallet Top-Up</h3>
        <p style="margin: 0; font-size: 14px; color: #ccc; line-height: 1.4;">
            Your credits will be added to your Angel Wallet immediately after payment.<br>
            Use them for instant checkout on future orders! 🚀
        </p>
    </div>
    <?php
}

/**
 * Prevent checkout if mixed cart (extra safety)
 */
add_action('woocommerce_checkout_process', 'greenangel_validate_checkout_topup_restriction');
function greenangel_validate_checkout_topup_restriction() {
    // Skip if validation is disabled
    if (get_option('angel_wallet_disable_validation', false)) {
        return;
    }
    
    $cart_has_topup = greenangel_cart_has_topup();
    $cart_has_non_topup = greenangel_cart_has_non_topup();
    
    // If cart has both types, block checkout
    if ($cart_has_topup && $cart_has_non_topup) {
        wc_add_notice(
            '⚠️ Invalid cart contents. Angel Wallet top-ups must be purchased separately from other products. Please remove either the top-ups or other items to continue.',
            'error'
        );
    }
}

/**
 * Add informational text to top-up product pages
 */
add_action('woocommerce_single_product_summary', 'greenangel_topup_product_notice', 25);
function greenangel_topup_product_notice() {
    global $product;
    
    if (!$product || !greenangel_is_product_topup($product->get_id())) {
        return;
    }
    
    ?>
    <div class="angel-topup-notice" style="margin: 20px 0; padding: 16px; background: linear-gradient(135deg, #1a1a1a, #222); border: 2px solid #aed604; border-radius: 12px; border-left: 4px solid #aed604;">
        <div style="display: flex; align-items: flex-start; gap: 12px; color: #fff;">
            <span style="font-size: 20px; margin-top: 2px;">ℹ️</span>
            <div>
                <h4 style="margin: 0 0 8px; color: #aed604; font-size: 15px; font-weight: 600;">How Angel Wallet Works</h4>
                <ul style="margin: 0; padding-left: 16px; font-size: 13px; color: #ccc; line-height: 1.4;">
                    <li>Credits are added to your wallet instantly after payment</li>
                    <li>Use for super-fast checkout on future orders</li>
                    <li>Must be purchased separately from other products</li>
                    <li>Perfect for quick reorders and smooth transactions</li>
                </ul>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Clear cart notices when appropriate
 */
add_action('woocommerce_cart_updated', 'greenangel_clear_mixed_cart_notices');
function greenangel_clear_mixed_cart_notices() {
    // If cart becomes empty or valid, clear any wallet-related error notices
    if (WC()->cart->is_empty() || (!greenangel_cart_has_topup() || !greenangel_cart_has_non_topup())) {
        wc_clear_notices();
    }
}

/**
 * Debug function for frontend (admin only)
 */
add_action('wp_footer', 'greenangel_debug_cart_state_frontend');
function greenangel_debug_cart_state_frontend() {
    if (!current_user_can('manage_options') || is_admin()) return;
    
    if (is_cart() || is_checkout() || is_shop() || is_product()) {
        $has_topup = WC()->cart ? greenangel_cart_has_topup() : false;
        $has_non_topup = WC()->cart ? greenangel_cart_has_non_topup() : false;
        $validation_disabled = get_option('angel_wallet_disable_validation', false);
        $cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
        
        ?>
        <script>
        console.log('Angel Wallet Frontend Debug:');
        console.log('- Has top-up:', <?php echo $has_topup ? 'true' : 'false'; ?>);
        console.log('- Has non-top-up:', <?php echo $has_non_topup ? 'true' : 'false'; ?>);
        console.log('- Validation disabled:', <?php echo $validation_disabled ? 'true' : 'false'; ?>);
        console.log('- Cart count:', <?php echo $cart_count; ?>);
        console.log('- Page type:', '<?php echo is_cart() ? 'cart' : (is_checkout() ? 'checkout' : (is_shop() ? 'shop' : 'product')); ?>');
        </script>
        <?php
    }
}