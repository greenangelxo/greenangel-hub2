<?php
/**
 * 🌿 Green Angel - Single Order View
 * Location: plugins/greenangel-hub/account/includes/order-details.php
 * 
 * 🔧 OVERRIDE TEMPORARILY DISABLED - REMOVE THE RETURN BELOW TO RE-ENABLE
 */

// 🚫 DISABLE OVERRIDE - Comment out this line to re-enable your custom view
// return;

// First, remove ALL possible Woo view-order hooks
remove_action('woocommerce_account_view-order_endpoint', 'woocommerce_account_view_order', 10);
remove_action('woocommerce_view_order', 'woocommerce_order_details_table', 10);
remove_action('woocommerce_order_details_after_order_table', 'woocommerce_order_again_button');

// Now inject your custom layout with high priority
add_action('woocommerce_account_view-order_endpoint', 'greenangel_override_view_order_page', 1);
function greenangel_override_view_order_page($order_id) {
    // Clear any buffered content from Woo
    if (ob_get_level()) {
        ob_clean();
    }
    
    if (!is_user_logged_in()) {
        echo '<p class="greenangel-notice">Please log in to view your order details.</p>';
        return;
    }
    
    $order = wc_get_order($order_id);
    if (!$order || $order->get_user_id() !== get_current_user_id()) {
        echo '<p class="greenangel-notice">You do not have permission to view this order.</p>';
        return;
    }
    
    $delivery_date = get_post_meta($order_id, '_delivery_date', true);
    $formatted = $delivery_date ? date('l, F j, Y', strtotime($delivery_date)) : null;
    
    // Start output with a wrapper
    echo '<div class="greenangel-dashboard-wrapper">';
    
    // Back to orders button
    echo '<a href="' . esc_url(wc_get_account_endpoint_url('orders')) . '" class="ga-back-button">← Back to Orders</a>';
    
    echo '<div class="ga-panel">';
    echo '<h3 class="ga-panel-title"><span class="ga-title-pill" style="background:#aed604;">Order #' . esc_html($order->get_order_number()) . '</span></h3>';
    
    // Order meta info
    echo '<div class="ga-order-meta">';
    echo '<p><strong>Status:</strong> ' . wc_get_order_status_name($order->get_status()) . '</p>';
    echo '<p><strong>Date:</strong> ' . wc_format_datetime($order->get_date_created()) . '</p>';
    echo '<p><strong>Total:</strong> ' . $order->get_formatted_order_total() . '</p>';
    echo '</div>';
    
    // Items panel
    echo '<div class="ga-inner-panel">';
    echo '<h4 class="ga-panel-title">Items</h4>';
    echo '<ul>';
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        $price = $order->get_formatted_line_subtotal($item);
        echo '<li>';
        echo esc_html($item->get_name()) . ' × ' . esc_html($item->get_quantity());
        echo '<span class="ga-item-price">' . $price . '</span>';
        echo '</li>';
    }
    echo '</ul>';
    echo '</div>';
    
    // Shipping Address panel
    echo '<div class="ga-inner-panel">';
    echo '<h4 class="ga-panel-title">Shipping Address</h4>';
    echo '<p>' . wp_kses_post($order->get_formatted_shipping_address()) . '</p>';
    echo '</div>';
    
    // Delivery date if exists
    if ($formatted) {
        echo '<div class="ga-inner-panel">';
        echo '<h4 class="ga-panel-title">Delivery Date</h4>';
        echo '<p><strong>' . esc_html($formatted) . '</strong></p>';
        echo '</div>';
    }
    
    // Payment method
    if ($order->get_payment_method_title()) {
        echo '<div class="ga-inner-panel">';
        echo '<h4 class="ga-panel-title">Payment Method</h4>';
        echo '<p>' . esc_html($order->get_payment_method_title()) . '</p>';
        echo '</div>';
    }
    
    // Customer notes if any
    if ($order->get_customer_note()) {
        echo '<div class="ga-inner-panel">';
        echo '<h4 class="ga-panel-title">Order Notes</h4>';
        echo '<p>' . wp_kses_post($order->get_customer_note()) . '</p>';
        echo '</div>';
    }
    
    // 💰 ORDER ACTIONS - Pay/Cancel buttons ONLY for LTC Litecoin orders
    $actions = wc_get_account_orders_actions($order);
    $payment_method = $order->get_payment_method_title();
    
    if (!empty($actions)) {
        echo '<div class="ga-inner-panel">';
        echo '<h4 class="ga-panel-title">Actions</h4>';
        echo '<div class="ga-order-actions">';
        
        foreach ($actions as $key => $action) {
            // Skip the "view" action since we're already viewing the order
            if ($key === 'view') continue;
            
            // Only show PAY button for LTC Litecoin orders
            if ($key === 'pay' && $payment_method !== 'LTC Litecoin') {
                continue;
            }
            
            echo '<a href="' . esc_url($action['url']) . '" class="ga-action-button ga-action-' . esc_attr($key) . '">';
            echo esc_html($action['name']);
            echo '</a>';
        }
        
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>'; // Close ga-panel
    echo '</div>'; // Close wrapper
    
    // Prevent any further Woo output
    return false;
}

// Additional hook to prevent Woo from adding anything after our content
add_filter('woocommerce_account_view-order_endpoint', function($order_id) {
    // This runs after our custom function, so we can stop any trailing content
    if (did_action('woocommerce_account_view-order_endpoint')) {
        // Stop execution of any further hooks
        remove_all_actions('woocommerce_after_account_view_order');
        remove_all_actions('woocommerce_order_details_after_order_table');
    }
    return $order_id;
}, 999);