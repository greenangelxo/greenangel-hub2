<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

/**
 * Angel Wallet Cart Validation & Restrictions
 * Ensures top-ups can only be purchased alone for proper processing flow
 */

/**
 * Check if cart contains top-up products
 */
function greenangel_cart_has_topup() {
    if (!WC()->cart) return false;
    
    foreach (WC()->cart->get_cart() as $cart_item) {
        $product = $cart_item['data'];
        if ($product && has_term('top-up', 'product_cat', $product->get_id())) {
            return true;
        }
    }
    return false;
}

/**
 * Check if cart contains non-top-up products
 */
function greenangel_cart_has_non_topup() {
    if (!WC()->cart) return false;
    
    foreach (WC()->cart->get_cart() as $cart_item) {
        $product = $cart_item['data'];
        if ($product && !has_term('top-up', 'product_cat', $product->get_id())) {
            return true;
        }
    }
    return false;
}

/**
 * Validate cart before adding products
 */
add_filter('woocommerce_add_to_cart_validation', 'greenangel_validate_cart_topup_restriction', 20, 3);
function greenangel_validate_cart_topup_restriction($passed, $product_id, $quantity) {
    if (!$passed) return false;
    
    $product = wc_get_product($product_id);
    if (!$product) return $passed;
    
    $is_topup = has_term('top-up', 'product_cat', $product_id);
    $cart_has_topup = greenangel_cart_has_topup();
    $cart_has_non_topup = greenangel_cart_has_non_topup();
    
    // Debug logging
    error_log("Cart validation - Is topup: " . ($is_topup ? 'yes' : 'no'));
    error_log("Cart validation - Cart has topup: " . ($cart_has_topup ? 'yes' : 'no'));
    error_log("Cart validation - Cart has non-topup: " . ($cart_has_non_topup ? 'yes' : 'no'));
    error_log("Cart validation - Cart contents: " . WC()->cart->get_cart_contents_count());
    
    // If cart is empty, always allow
    if (WC()->cart->is_empty()) {
        error_log("Cart is empty - allowing add");
        return true;
    }
    
    // If trying to add a top-up when cart has non-top-up items
    if ($is_topup && $cart_has_non_topup) {
        error_log("Blocking: Adding topup to cart with non-topup items");
        wc_add_notice(
            '💸 Angel Wallet top-ups must be purchased separately. Please checkout your current items first, then add wallet credits.',
            'error'
        );
        return false;
    }
    
    // If trying to add non-top-up when cart has top-ups
    if (!$is_topup && $cart_has_topup) {
        error_log("Blocking: Adding non-topup to cart with topup items");
        wc_add_notice(
            '🛍️ Your cart contains Angel Wallet top-ups which must be purchased alone. Please complete your wallet purchase first, then add other items.',
            'error'
        );
        return false;
    }
    
    error_log("Validation passed - allowing add");
    return true;
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
    
    if (!$product || !has_term('top-up', 'product_cat', $product->get_id())) {
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
    
    <!-- Special clean notice for quick views -->
    <div class="angel-quickview-notice" style="display: none; margin: 16px 0; padding: 12px 16px; background: rgba(174, 214, 4, 0.1); border: 1px solid rgba(174, 214, 4, 0.3); border-radius: 8px; color: #aed604; font-size: 13px; line-height: 1.4;">
        <strong>💸 Angel Wallet Top-Up</strong><br>
        Credits added instantly • Use for super-fast checkout • Must be purchased separately
    </div>
    <?php
}

/**
 * Custom styles for wallet-related notices
 */
add_action('wp_head', 'greenangel_wallet_notice_styles');
function greenangel_wallet_notice_styles() {
    if (!is_cart() && !is_checkout() && !is_product()) return;
    
    ?>
    <style>
    /* Angel Wallet Notice Animations */
    .wc-block-components-notice-banner {
        animation: slideInFromTop 0.5s ease-out;
    }
    
    @keyframes slideInFromTop {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .angel-topup-notice {
        animation: fadeInUp 0.6s ease-out;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(15px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Enhanced error notice styling for wallet restrictions */
    .woocommerce-error:has-text("Angel Wallet"),
    .woocommerce-error:has-text("💸"),
    .woocommerce-error:has-text("🛍️") {
        background: linear-gradient(135deg, #2a1a1a, #332222) !important;
        border-left: 4px solid #f44336 !important;
        border-radius: 8px !important;
        padding: 16px 20px !important;
        color: #fff !important;
        font-weight: 500 !important;
    }
    
    /* Success notice for wallet operations */
    .woocommerce-message:has-text("Angel Wallet"),
    .woocommerce-message:has-text("💸"),
    .woocommerce-message:has-text("credits") {
        background: linear-gradient(135deg, #1a2a1a, #223322) !important;
        border-left: 4px solid #4caf50 !important;
        border-radius: 8px !important;
        padding: 16px 20px !important;
        color: #fff !important;
        font-weight: 500 !important;
    }
    </style>
    <?php
}

/**
 * Auto-redirect to checkout for wallet top-ups (optional enhancement)
 */
add_filter('woocommerce_add_to_cart_redirect_url', 'greenangel_topup_auto_redirect');
function greenangel_topup_auto_redirect($url) {
    // Only redirect if the added product is a top-up and cart only contains top-ups
    if (greenangel_cart_has_topup() && !greenangel_cart_has_non_topup()) {
        // Optional: Uncomment to auto-redirect to checkout for faster flow
        // return wc_get_checkout_url();
    }
    
    return $url;
}

/**
 * Enhanced AJAX handler for add to cart with proper error responses
 */
add_action('wp_ajax_woocommerce_add_to_cart', 'greenangel_ajax_add_to_cart_handler');
add_action('wp_ajax_nopriv_woocommerce_add_to_cart', 'greenangel_ajax_add_to_cart_handler');
function greenangel_ajax_add_to_cart_handler() {
    // Get the product ID and validate
    $product_id = absint($_POST['product_id'] ?? 0);
    $quantity = absint($_POST['quantity'] ?? 1);
    
    if (!$product_id) {
        wp_send_json_error([
            'error' => true,
            'message' => 'Invalid product'
        ]);
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
    $result = WC()->cart->add_to_cart($product_id, $quantity);
    
    if ($result) {
        wc_clear_notices(); // Clear any lingering notices
        
        wp_send_json_success([
            'success' => true,
            'message' => 'Added to cart successfully!',
            'fragments' => WC_AJAX::get_refreshed_fragments()
        ]);
    } else {
        // Check for WooCommerce errors
        $notices = wc_get_notices('error');
        $error_message = 'Failed to add to cart';
        
        if (!empty($notices)) {
            $error_message = wp_strip_all_tags($notices[0]['notice']);
        }
        
        wc_clear_notices();
        
        wp_send_json_error([
            'error' => true,
            'message' => $error_message
        ]);
    }
}

/**
 * Add cart validation JavaScript for better UX and error display
 */
add_action('wp_footer', 'greenangel_cart_validation_js');
function greenangel_cart_validation_js() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // Wait for WooCommerce to initialize
        let cartCheckAttempts = 0;
        const maxAttempts = 10;
        
        function waitForCart() {
            cartCheckAttempts++;
            
            // Check if WooCommerce is ready
            if (typeof wc_add_to_cart_params !== 'undefined' || cartCheckAttempts >= maxAttempts) {
                initializeCartValidation();
            } else {
                setTimeout(waitForCart, 100);
            }
        }
        
        function initializeCartValidation() {
            console.log('Angel Wallet validation system initialized');
            
            function showCenterNotification(type, title, message) {
                // Remove any existing notifications first
                removeExistingNotifications();
                
                // Create notification overlay with MAXIMUM z-index
                const overlay = document.createElement('div');
                overlay.className = 'angel-notification-overlay';
                overlay.style.zIndex = '999999'; // Super high z-index
                
                // Create notification content
                const notification = document.createElement('div');
                notification.className = `angel-center-notification ${type}`;
                
                const icon = type === 'error' ? '⚠️' : (type === 'success' ? '🎉' : 'ℹ️');
                
                notification.innerHTML = `
                    <div class="notification-icon">${icon}</div>
                    <div class="notification-content">
                        <h3 class="notification-title">${title}</h3>
                        <p class="notification-message">${message}</p>
                        <button class="notification-btn" onclick="this.closest('.angel-notification-overlay').remove()">
                            ${type === 'error' ? 'Got it' : 'Continue'}
                        </button>
                    </div>
                `;
                
                overlay.appendChild(notification);
                document.body.appendChild(overlay);
                
                // Prevent scrolling while notification is open
                document.body.style.overflow = 'hidden';
                
                // NEVER auto-remove - customer must close manually
                // Commenting out auto-removal completely
                /*
                const timeout = type === 'error' ? 999999 : 999999;
                const autoRemoveTimer = setTimeout(() => {
                    if (overlay.parentNode) {
                        overlay.classList.add('fade-out');
                        setTimeout(() => {
                            overlay.remove();
                            document.body.style.overflow = ''; // Restore scrolling
                        }, 300);
                    }
                }, timeout);
                */
                
                // Close on overlay click
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) {
                        overlay.classList.add('fade-out');
                        setTimeout(() => {
                            overlay.remove();
                            document.body.style.overflow = ''; // Restore scrolling
                        }, 300);
                    }
                });
                
                // Close on button click
                notification.querySelector('.notification-btn').addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    overlay.classList.add('fade-out');
                    setTimeout(() => {
                        overlay.remove();
                        document.body.style.overflow = ''; // Restore scrolling
                    }, 300);
                });
                
                // Close on ESC key
                const escapeHandler = function(e) {
                    if (e.key === 'Escape') {
                        overlay.classList.add('fade-out');
                        setTimeout(() => {
                            overlay.remove();
                            document.body.style.overflow = ''; // Restore scrolling
                        }, 300);
                        document.removeEventListener('keydown', escapeHandler);
                    }
                };
                document.addEventListener('keydown', escapeHandler);
            }
            
            function removeExistingNotifications() {
                document.querySelectorAll('.angel-notification-overlay').forEach(notification => {
                    notification.remove();
                });
                document.body.style.overflow = ''; // Restore scrolling
            }
            
            // Check if we're on the wallet console page
            function isWalletConsolePage() {
                return document.querySelector('.angel-wallet-console') !== null;
            }
            
            // IMPROVED: Check if cart has any items (multiple selectors for different themes)
            function hasCartItems() {
                // First, check if WooCommerce cart data is available in session
                if (typeof wc_cart_fragments_params !== 'undefined' && wc_cart_fragments_params.cart_hash_key) {
                    const cartHash = sessionStorage.getItem(wc_cart_fragments_params.cart_hash_key);
                    if (!cartHash || cartHash === '') {
                        console.log('Cart hash empty - no items');
                        return false;
                    }
                }
                
                // Check various cart count selectors
                const selectors = [
                    '.cart-contents-count',
                    '.count', 
                    '.cart-count',
                    '.cart-quantity',
                    '.mini-cart-count',
                    '.header-cart-count',
                    '.woocommerce-mini-cart__total .amount',
                    '[data-cart-items-count]',
                    '.cart-items-count'
                ];
                
                for (let selector of selectors) {
                    const elements = document.querySelectorAll(selector);
                    for (let element of elements) {
                        // Extract just numbers from the text
                        const text = element.textContent || element.innerText || '';
                        const count = parseInt(text.replace(/[^0-9]/g, '')) || 0;
                        
                        // Also check data attributes
                        const dataCount = parseInt(element.getAttribute('data-cart-items-count')) || 0;
                        
                        if (count > 0 || dataCount > 0) {
                            console.log(`Found cart items via ${selector}: ${count || dataCount}`);
                            return true;
                        }
                    }
                }
                
                // Check if we're on cart page with items
                if (document.querySelector('.woocommerce-cart-form .cart_item')) {
                    console.log('Found cart items on cart page');
                    return true;
                }
                
                // Check WooCommerce fragments in session storage
                if (typeof Storage !== 'undefined') {
                    const fragments = sessionStorage.getItem('wc_fragments');
                    if (fragments) {
                        try {
                            const parsed = JSON.parse(fragments);
                            // Look for cart count in fragments
                            for (let key in parsed) {
                                if (key.includes('cart') && parsed[key].includes('class="count"')) {
                                    const match = parsed[key].match(/>(\d+)</);
                                    if (match && parseInt(match[1]) > 0) {
                                        console.log('Found cart items in fragments:', match[1]);
                                        return true;
                                    }
                                }
                            }
                        } catch (e) {
                            console.error('Error parsing fragments:', e);
                        }
                    }
                }
                
                console.log('No cart items found');
                return false;
            }
            
            // Check if we're in a mini cart context
            function isInMiniCart(element) {
                return element.closest('.mini-cart, .cart-dropdown, .header-cart, .cart-widget, .widget_shopping_cart, [class*="mini-cart"], [class*="cart-widget"]') !== null;
            }
            
            // FIXED: INTERCEPT ALL ADD TO CART ATTEMPTS
            document.addEventListener('click', function(e) {
                const button = e.target.closest('.ajax-add-to-cart, .add_to_cart_button, .single_add_to_cart_button, .product_type_simple, .button.alt');
                if (!button) return;
                
                // CRITICAL FIX: Always allow if cart is empty (regardless of page type)
                if (!hasCartItems()) {
                    console.log('Cart is empty - allowing normal add to cart flow');
                    return; // Let it proceed normally
                }
                
                // Now we know cart has items, so we need to validate
                console.log('Cart has items - intercepting for validation');
                
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                const productId = button.dataset.product_id || 
                                 button.getAttribute('data-product_id') || 
                                 button.getAttribute('data-product-id') ||
                                 document.querySelector('[name="add-to-cart"]')?.value ||
                                 button.value;
                const quantity = button.dataset.quantity || 
                               button.getAttribute('data-quantity') || 
                               document.querySelector('[name="quantity"]')?.value || 
                               1;
                
                if (!productId) {
                    showCenterNotification('error', 'Invalid Product', 'Unable to determine which product to add to cart.');
                    return;
                }
                
                // Show loading state
                const originalText = button.innerHTML;
                const originalDisabled = button.disabled;
                button.innerHTML = '<span>⏳</span> Adding...';
                button.disabled = true;
                button.style.pointerEvents = 'none';
                button.style.opacity = '0.7';
                
                // Make AJAX request with enhanced error handling
                fetch(wc_add_to_cart_params.ajax_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'woocommerce_add_to_cart',
                        product_id: productId,
                        quantity: quantity
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Only show success notification on regular shop pages, not wallet console
                        if (!isWalletConsolePage()) {
                            showCenterNotification('success', 'Added to Cart! 🛒', 'Your item has been successfully added to your cart.');
                        }
                        
                        // Update cart fragments if available
                        if (data.data && data.data.fragments) {
                            Object.keys(data.data.fragments).forEach(key => {
                                const elements = document.querySelectorAll(key);
                                elements.forEach(element => {
                                    element.outerHTML = data.data.fragments[key];
                                });
                            });
                        }
                        
                        // Trigger cart updated events
                        document.dispatchEvent(new Event('wc_cart_updated'));
                        if (typeof jQuery !== 'undefined') {
                            jQuery('body').trigger('wc_fragment_refresh');
                            jQuery('body').trigger('added_to_cart');
                        }
                        
                        // Close any open quick views or modals
                        const closeButtons = document.querySelectorAll('.close, .mfp-close, .modal-close, [data-dismiss="modal"]');
                        closeButtons.forEach(btn => {
                            if (btn.style.display !== 'none') {
                                btn.click();
                            }
                        });
                        
                    } else {
                        const errorMsg = data.data?.message || 'Unable to add item to cart.';
                        
                        // Determine title and description based on error content
                        let title = '🚫 Cart Restriction';
                        let description = errorMsg;
                        
                        if (errorMsg.includes('top-ups must be purchased separately')) {
                            title = '💸 Wallet Top-Up Restriction';
                            description = 'Angel Wallet top-ups must be purchased alone. Please complete your current cart first, then add wallet credits.';
                        } else if (errorMsg.includes('cart contains Angel Wallet top-ups')) {
                            title = '🛍️ Cart Contains Wallet Credits';
                            description = 'Your cart already contains wallet top-ups which must be purchased separately. Complete your wallet purchase first, then add other items.';
                        }
                        
                        // ALWAYS show error notification as popup
                        showCenterNotification('error', title, description);
                    }
                })
                .catch(error => {
                    console.error('Cart error:', error);
                    showCenterNotification('error', '🌐 Network Error', 'Unable to connect to the server. Please check your connection and try again.');
                })
                .finally(() => {
                    // Restore button state
                    button.innerHTML = originalText;
                    button.disabled = originalDisabled;
                    button.style.pointerEvents = '';
                    button.style.opacity = '';
                });
            });
            
            // Also listen for cart updates to refresh our understanding
            if (typeof jQuery !== 'undefined') {
                jQuery(document.body).on('wc_fragments_refreshed added_to_cart removed_from_cart', function() {
                    console.log('Cart fragments updated');
                });
            }
        }
        
        // Start the initialization
        waitForCart();
        
        // Add notification styles with MAXIMUM z-index
        if (!document.getElementById('angel-notification-styles')) {
            const styles = document.createElement('style');
            styles.id = 'angel-notification-styles';
            styles.textContent = `
                .angel-notification-overlay {
                    position: fixed !important;
                    top: 0 !important;
                    left: 0 !important;
                    width: 100% !important;
                    height: 100% !important;
                    background: rgba(0, 0, 0, 0.85) !important;
                    backdrop-filter: blur(8px) !important;
                    z-index: 999999 !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    animation: fadeIn 0.3s ease-out !important;
                    padding: 20px !important;
                    box-sizing: border-box !important;
                }
                
                .angel-notification-overlay.fade-out {
                    animation: fadeOut 0.3s ease-out !important;
                }
                
                .angel-center-notification {
                    background: linear-gradient(135deg, #1a1a1a, #222) !important;
                    border: 3px solid #aed604 !important;
                    border-radius: 20px !important;
                    padding: 32px !important;
                    max-width: 450px !important;
                    width: 100% !important;
                    text-align: center !important;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(174, 214, 4, 0.3) !important;
                    animation: slideInUp 0.4s ease-out !important;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
                    color: #fff !important;
                    position: relative !important;
                    overflow: hidden !important;
                    z-index: 1000000 !important;
                }
                
                .angel-center-notification::before {
                    content: '' !important;
                    position: absolute !important;
                    top: 0 !important;
                    left: 0 !important;
                    right: 0 !important;
                    height: 6px !important;
                    background: linear-gradient(90deg, #aed604, #cfff00, #aed604) !important;
                    background-size: 300% 300% !important;
                    animation: shimmer 2s ease-in-out infinite !important;
                }
                
                .angel-center-notification.error {
                    border-color: #f44336 !important;
                }
                
                .angel-center-notification.error::before {
                    background: linear-gradient(90deg, #f44336, #ff6b6b, #f44336) !important;
                    background-size: 300% 300% !important;
                }
                
                .angel-center-notification.success::before {
                    background: linear-gradient(90deg, #4caf50, #66bb6a, #4caf50) !important;
                    background-size: 300% 300% !important;
                }
                
                .notification-icon {
                    font-size: 56px !important;
                    margin-bottom: 20px !important;
                    display: block !important;
                    animation: bounce 1s ease-in-out !important;
                }
                
                @keyframes bounce {
                    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
                    40% { transform: translateY(-10px); }
                    60% { transform: translateY(-5px); }
                }
                
                .notification-title {
                    font-size: 24px !important;
                    font-weight: 800 !important;
                    margin: 0 0 16px 0 !important;
                    color: #fff !important;
                    text-shadow: 0 2px 4px rgba(0,0,0,0.5) !important;
                }
                
                .angel-center-notification.error .notification-title {
                    color: #ff6b6b !important;
                }
                
                .angel-center-notification.success .notification-title {
                    color: #66bb6a !important;
                }
                
                .notification-message {
                    font-size: 16px !important;
                    line-height: 1.6 !important;
                    margin: 0 0 28px 0 !important;
                    color: #ddd !important;
                    font-weight: 500 !important;
                }
                
                .notification-btn {
                    background: linear-gradient(135deg, #aed604, #cfff00) !important;
                    color: #1a1a1a !important;
                    border: none !important;
                    padding: 14px 32px !important;
                    border-radius: 30px !important;
                    font-weight: 700 !important;
                    font-size: 15px !important;
                    cursor: pointer !important;
                    transition: all 0.3s ease !important;
                    text-transform: uppercase !important;
                    letter-spacing: 1px !important;
                    min-width: 120px !important;
                    box-shadow: 0 4px 16px rgba(174, 214, 4, 0.3) !important;
                }
                
                .angel-center-notification.error .notification-btn {
                    background: linear-gradient(135deg, #f44336, #ff6b6b) !important;
                    color: #fff !important;
                    box-shadow: 0 4px 16px rgba(244, 67, 54, 0.3) !important;
                }
                
                .angel-center-notification.success .notification-btn {
                    background: linear-gradient(135deg, #4caf50, #66bb6a) !important;
                    color: #fff !important;
                    box-shadow: 0 4px 16px rgba(76, 175, 80, 0.3) !important;
                }
                
                .notification-btn:hover {
                    transform: translateY(-3px) !important;
                    box-shadow: 0 8px 24px rgba(174, 214, 4, 0.4) !important;
                }
                
                .angel-center-notification.error .notification-btn:hover {
                    box-shadow: 0 8px 24px rgba(244, 67, 54, 0.4) !important;
                }
                
                .angel-center-notification.success .notification-btn:hover {
                    box-shadow: 0 8px 24px rgba(76, 175, 80, 0.4) !important;
                }
                
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                
                @keyframes fadeOut {
                    from { opacity: 1; }
                    to { opacity: 0; }
                }
                
                @keyframes slideInUp {
                    from {
                        opacity: 0;
                        transform: translateY(40px) scale(0.9);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0) scale(1);
                    }
                }
                
                @keyframes shimmer {
                    0%, 100% { background-position: 0% 50%; }
                    50% { background-position: 100% 50%; }
                }
                
                @media (max-width: 768px) {
                    .angel-center-notification {
                        margin: 0 15px !important;
                        padding: 28px 20px !important;
                        max-width: 90% !important;
                    }
                    
                    .notification-title {
                        font-size: 22px !important;
                    }
                    
                    .notification-message {
                        font-size: 15px !important;
                    }
                    
                    .notification-icon {
                        font-size: 48px !important;
                    }
                }
                
                /* Hide the angel-topup-notice in quick views - it's causing issues */
                .nm-qv-summary-content .angel-topup-notice,
                .nm-quickview .angel-topup-notice,
                .quickview .angel-topup-notice,
                .quick-view .angel-topup-notice,
                [class*="nm-qv"] .angel-topup-notice {
                    display: none !important;
                }
                
                /* Show the clean quick view notice instead */
                .nm-qv-summary-content .angel-quickview-notice,
                .nm-quickview .angel-quickview-notice,
                .quickview .angel-quickview-notice,
                .quick-view .angel-quickview-notice,
                [class*="nm-qv"] .angel-quickview-notice {
                    display: block !important;
                    margin: 12px 0 !important;
                    padding: 10px 12px !important;
                    background: rgba(174, 214, 4, 0.15) !important;
                    border: 1px solid rgba(174, 214, 4, 0.4) !important;
                    border-radius: 6px !important;
                    color: #aed604 !important;
                    font-size: 12px !important;
                    line-height: 1.3 !important;
                    font-weight: 500 !important;
                }
                
                /* Also hide it in any modal or overlay context */
                .modal .angel-topup-notice,
                .overlay .angel-topup-notice,
                [class*="modal"] .angel-topup-notice,
                [class*="overlay"] .angel-topup-notice {
                    display: none !important;
                }
            `;
            document.head.appendChild(styles);
        }
    });
    </script>
    <?php
}

/**
 * Enhanced order processing for top-up only orders
 */
add_action('woocommerce_checkout_order_processed', 'greenangel_fast_track_topup_orders', 5, 2);
function greenangel_fast_track_topup_orders($order_id, $posted_data) {
    $order = wc_get_order($order_id);
    if (!$order) return;
    
    // Check if this is a top-up only order
    $is_topup_only = true;
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        if (!$product || !has_term('top-up', 'product_cat', $product->get_id())) {
            $is_topup_only = false;
            break;
        }
    }
    
    if ($is_topup_only) {
        // Mark with special meta for faster processing
        $order->update_meta_data('_is_wallet_topup_only', 'yes');
        $order->add_order_note('Angel Wallet top-up order - processing for instant credit');
        $order->save();
        
        // If payment method is not wallet (i.e., it's a fresh topup), prioritize processing
        if ($order->get_payment_method() !== 'angel_wallet') {
            // Add priority flag for payment gateways that support it
            $order->update_meta_data('_priority_processing', 'wallet_topup');
            $order->save();
        }
    }
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
?>