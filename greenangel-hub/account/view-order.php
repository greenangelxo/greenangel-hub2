<?php
// 🌿 Green Angel – Fully replace Woo view order endpoint with style overrides

// First, remove ALL possible Woo view-order hooks
remove_action('woocommerce_account_view-order_endpoint', 'woocommerce_account_view_order', 10);
remove_action('woocommerce_view_order', 'woocommerce_order_details_table', 10);
remove_action('woocommerce_order_details_after_order_table', 'woocommerce_order_again_button');

// Add custom CSS to hide any remaining Woo elements
add_action('wp_head', 'greenangel_hide_woo_order_elements');
function greenangel_hide_woo_order_elements() {
    if (!is_account_page()) return;
    ?>
    <style>
        /* Hide Woo's default order view completely */
        .woocommerce-account .woocommerce-order-details,
        .woocommerce-account .woocommerce-customer-details,
        .woocommerce-account .order_details,
        .woocommerce-account .woocommerce-table--order-details,
        .woocommerce-account .woocommerce-order-overview,
        .woocommerce-account .woocommerce-thankyou-order-details,
        .woocommerce-account .woocommerce-order-downloads,
        .woocommerce-account .woocommerce-bacs-bank-details,
        .woocommerce-MyAccount-content > h2:not(.ga-custom-title),
        .woocommerce-MyAccount-content > p:not(.greenangel-notice),
        .woocommerce-MyAccount-content > table,
        .woocommerce-MyAccount-content > .woocommerce-notices-wrapper {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* Hide the side navigation on view-order pages */
        body.woocommerce-account.woocommerce-view-order .woocommerce-MyAccount-navigation {
            display: none !important;
        }
        
        /* Full width for content when nav is hidden */
        body.woocommerce-account.woocommerce-view-order .woocommerce-MyAccount-content {
            width: 100% !important;
            float: none !important;
            margin: 0 !important;
        }
        
        /* 🌿 Green Angel Order View Styles - Matching Hub Aesthetic */
        .greenangel-dashboard-wrapper {
            background: linear-gradient(135deg, #1a1a1a 0%, #222222 100%);
            padding: 1rem;
            border-radius: 16px;
            color: #ffffff;
            font-family: 'Poppins', sans-serif;
            max-width: 100%;
            margin: 0;
        }
        
        /* Panel Styling */
        .ga-panel {
            background: linear-gradient(135deg, #2a2a2a 0%, #333333 100%);
            border: 1px solid #444;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .ga-panel-title {
            color: #aed604;
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0 0 1rem 0;
            text-align: center;
        }
        
        /* Title Pills */
        .ga-title-pill {
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 700;
            display: inline-block;
            min-width: 200px;
            text-align: center;
            color: #222222 !important;
            text-transform: none;
            letter-spacing: 0.5px;
        }
        
        /* Inner Panel Styling */
        .ga-inner-panel {
            background: rgba(26, 26, 26, 0.5);
            border: 1px solid #333;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1.5rem;
        }
        
        .ga-inner-panel h4 {
            color: #aed604;
            font-size: 1rem;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 0.75rem;
        }
        
        .ga-inner-panel ul {
            margin: 0.5rem 0;
            padding-left: 20px;
            list-style: none;
        }
        
        .ga-inner-panel li {
            margin: 0.5rem 0;
            color: #cccccc;
            font-size: 0.9rem;
            position: relative;
            padding-left: 1rem;
        }
        
        .ga-inner-panel li:before {
            content: "•";
            color: #aed604;
            font-weight: bold;
            position: absolute;
            left: 0;
        }
        
        .ga-inner-panel p {
            color: #cccccc;
            font-size: 0.9rem;
            line-height: 1.5;
            margin: 0.5rem 0;
        }
        
        /* Order meta info styling */
        .ga-order-meta {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin: 1.5rem 0;
        }
        
        .ga-order-meta p {
            margin: 0;
            color: #ffffff;
            font-size: 0.95rem;
        }
        
        .ga-order-meta strong {
            color: #aed604;
            margin-right: 0.5rem;
        }
        
        /* Back button styling */
        .ga-back-button {
            background: linear-gradient(135deg, #444 0%, #555 100%);
            color: white !important;
            text-decoration: none !important;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 1.5rem;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }
        
        .ga-back-button:hover {
            background: linear-gradient(135deg, #aed604 0%, #c6f731 100%);
            color: #111 !important;
            transform: translateY(-2px);
            text-decoration: none !important;
        }
        
        /* Notice styling */
        .greenangel-notice {
            background: linear-gradient(135deg, #aed604 0%, #c6f731 100%);
            color: #111;
            padding: 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-align: center;
            margin: 1rem 0;
            font-size: 1rem;
        }
        
        /* Item price styling */
        .ga-item-price {
            color: #aed604;
            font-weight: 600;
            margin-left: 0.5rem;
        }
        
        /* Responsive styles */
        @media (min-width: 768px) {
            .greenangel-dashboard-wrapper {
                padding: 2rem;
                margin: 1rem auto;
                max-width: 900px;
                border-radius: 20px;
            }
            
            .ga-panel {
                padding: 2rem;
            }
            
            .ga-panel-title {
                font-size: 1.2rem;
            }
            
            .ga-title-pill {
                min-width: 250px;
                font-size: 1rem;
                padding: 0.6rem 2rem;
            }
            
            .ga-inner-panel {
                padding: 1.5rem;
            }
            
            .ga-order-meta {
                flex-direction: row;
                gap: 2rem;
                justify-content: center;
            }
        }
        
        @media (min-width: 1024px) {
            .greenangel-dashboard-wrapper {
                max-width: 1200px;
                padding: 2.5rem;
            }
            
            .ga-panel {
                padding: 2.5rem;
            }
            
            .ga-title-pill {
                min-width: 300px;
                font-size: 1.1rem;
                padding: 0.7rem 2.5rem;
            }
        }
    </style>
    <?php
}

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

// Add body class for more specific targeting
add_filter('body_class', function($classes) {
    if (is_account_page() && get_query_var('view-order')) {
        $classes[] = 'woocommerce-view-order';
    }
    if (is_wc_endpoint_url('orders')) {
        $classes[] = 'greenangel-orders-page';
    }
    if (is_wc_endpoint_url('edit-address')) {
        $classes[] = 'greenangel-edit-address';
    }
    if (is_wc_endpoint_url('edit-account')) {
        $classes[] = 'greenangel-edit-account';
    }
    return $classes;
});

// 🌿 Green Angel – ORDERS PAGE OVERRIDE (My Account > Orders)
// Remove Woo's default orders table
remove_action('woocommerce_account_orders_endpoint', 'woocommerce_account_orders', 10);

// Add CSS for orders page
add_action('wp_head', 'greenangel_hide_woo_orders_elements');
function greenangel_hide_woo_orders_elements() {
    if (!is_wc_endpoint_url('orders')) return;
    ?>
    <style>
        /* Hide Woo's default orders table completely */
        .woocommerce-account .woocommerce-orders-table,
        .woocommerce-account .woocommerce-MyAccount-content > h2,
        .woocommerce-account .woocommerce-message,
        .woocommerce-account .woocommerce-info,
        .woocommerce-account .woocommerce-Pagination,
        .woocommerce-account .woocommerce-orders,
        .woocommerce-account .woocommerce-order-overview {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* HIDE THE SIDEBAR ON ORDERS PAGE TOO! */
        body.greenangel-orders-page .woocommerce-MyAccount-navigation {
            display: none !important;
        }
        
        /* Full width for content when nav is hidden */
        body.greenangel-orders-page .woocommerce-MyAccount-content {
            width: 100% !important;
            float: none !important;
            margin: 0 !important;
        }
        
        /* Orders page specific styles */
        .greenangel-orders-wrapper {
            background: linear-gradient(135deg, #1a1a1a 0%, #222222 100%);
            padding: 1rem;
            border-radius: 16px;
            color: #ffffff;
            font-family: 'Poppins', sans-serif;
            max-width: 100%;
            margin: 0;
        }

        /* Status Guide */
        .greenangel-status-guide {
            background: transparent;
            padding: 0;
            margin: 0;
            color: #ffffff;
        }

        .guide-title-pill {
            background: #222222;
            color: #aed604;
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            text-align: center;
            margin: 0 auto 1rem;
            display: block;
            width: fit-content;
            max-width: 250px;
            border: 1px solid #444;
        }

        .guide-grid {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .guide-item {
            background: #1a1a1a;
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            border: 1px solid #333;
        }

        .status-circle {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-label {
            color: #cccccc;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Status Circle Colors */
        .status-circle.pending-payment { background: #ff9800; }
        .status-circle.processing { background: #2196f3; }
        .status-circle.ship-today { background: #9c27b0; }
        .status-circle.completed { background: #4caf50; }
        .status-circle.cancelled { background: #f44336; }

        .guide-footer {
            background: #1a1a1a;
            color: #ffffff;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 500;
            text-align: center;
            border-left: 4px solid #aed604;
            margin: 0 auto;
            max-width: 600px;
            border: 1px solid #333;
        }

        /* Orders Panel */
        .ga-orders-panel {
            background: linear-gradient(135deg, #2a2a2a 0%, #333333 100%);
            border: 1px solid #444;
            border-radius: 16px;
            padding: 1rem 1.5rem 1.5rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* No Orders Message */
        .greenangel-no-orders {
            background: #1a1a1a;
            color: #999;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            font-weight: 500;
            font-size: 1rem;
            margin: 0 0 1rem 0;
            border: 1px solid #333;
        }

        /* Year Separator */
        .greenangel-year-separator {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 1rem 0 0.75rem 0;
            width: 100%;
            text-align: center;
        }

        .greenangel-year-separator:first-child {
            margin-top: 0;
        }

        .year-pill {
            background: linear-gradient(135deg, #aed604 0%, #c6f731 100%);
            color: #222222;
            font-weight: 700;
            font-size: 0.8rem;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            text-align: center;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            display: inline-block;
        }

        /* Orders Grid */
        .orders-grid {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        /* Order Cards */
        .greenangel-order-card {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 1rem;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .greenangel-order-card:hover {
            border-color: #aed604;
            transform: translateY(-1px);
        }

        /* Left border accent */
        .greenangel-order-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 6px;
            background: #aed604;
        }

        /* Status-specific accent colors */
        .greenangel-order-card[data-status="pending-payment"]::before { background: #ff9800; }
        .greenangel-order-card[data-status="processing"]::before { background: #2196f3; }
        .greenangel-order-card[data-status="ship-today"]::before { background: #9c27b0; }
        .greenangel-order-card[data-status="completed"]::before { background: #4caf50; }
        .greenangel-order-card[data-status="cancelled"]::before { background: #f44336; }

        .order-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            gap: 0.75rem;
        }

        .order-number {
            color: #aed604;
            font-weight: 700;
            font-size: 1rem;
        }

        .order-date {
            color: #999;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .order-status-center {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .order-status {
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: inline-block;
        }

        /* Status Colors */
        .order-status.pending-payment { background: #ff9800; color: #222222; }
        .order-status.processing { background: #2196f3; color: #222222; }
        .order-status.ship-today { background: #9c27b0; color: #222222; }
        .order-status.completed { background: #4caf50; color: #222222; }
        .order-status.cancelled { background: #f44336; color: #222222; }

        /* Delivery Pills */
        .delivery-date-pill {
            background: rgba(174, 214, 4, 0.2);
            color: #aed604;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            text-align: center;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .delivery-placeholder-pill {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.5);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            text-align: center;
            text-transform: uppercase;
            white-space: nowrap;
        }

        /* View Button */
        .view-button {
            display: block;
            width: 100%;
            background: #444;
            border: none;
            color: #aed604;
            font-weight: 600;
            font-size: 0.85rem;
            text-align: center;
            padding: 0.75rem 1rem;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .view-button:hover {
            transform: translateY(-2px);
            color: #c6f731;
            background: #555;
            text-decoration: none;
        }

        /* Load More Button */
        .load-more-container {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }

        .load-more-orders-button {
            background: linear-gradient(135deg, #aed604 0%, #c6f731 100%);
            color: #222222;
            padding: 8px 20px;
            border: none;
            border-radius: 25px;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .load-more-orders-button:hover {
            transform: translateY(-2px);
        }

        .load-more-orders-button:active {
            transform: scale(0.98);
        }

        /* Animation */
        .greenangel-order-card.ga-show {
            animation: slideIn 0.5s ease forwards;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Tablet Styles */
        @media (min-width: 768px) {
            .greenangel-orders-wrapper {
                padding: 2rem;
                margin: 1rem auto;
                max-width: 900px;
                border-radius: 20px;
            }
            
            .ga-panel, .ga-orders-panel {
                padding: 2rem;
            }
            
            .guide-grid {
                flex-direction: row;
                flex-wrap: wrap;
            }
            
            .guide-item {
                flex: 1 1 calc(50% - 0.375rem);
            }
            
            .orders-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .greenangel-year-separator {
                grid-column: 1 / -1;
            }
        }

        /* Desktop Styles */
        @media (min-width: 1024px) {
            .greenangel-orders-wrapper {
                max-width: 1200px;
                padding: 2.5rem;
            }
            
            .guide-grid {
                flex-wrap: nowrap;
            }
            
            .guide-item {
                flex: 1;
                flex-direction: column;
                text-align: center;
                padding: 0.75rem;
            }
            
            .status-circle {
                margin: 0 auto 0.5rem;
            }
            
            .orders-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
    </style>
    <?php
}

// Now create the custom orders page
add_action('woocommerce_account_orders_endpoint', 'greenangel_override_orders_page', 1);
function greenangel_override_orders_page() {
    if (!is_user_logged_in()) {
        echo '<p class="greenangel-notice">Please log in to view your orders.</p>';
        return;
    }
    
    $user_id = get_current_user_id();
    $orders = wc_get_orders([
        'customer_id' => $user_id,
        'limit' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);
    
    // Group orders by year
    $orders_by_year = [];
    foreach ($orders as $order) {
        $year = $order->get_date_created()->date_i18n('Y');
        if (!isset($orders_by_year[$year])) {
            $orders_by_year[$year] = [];
        }
        $orders_by_year[$year][] = $order;
    }
    
    ?>
    <div class="greenangel-orders-wrapper">
        <div class="greenangel-orders-container">
            <!-- 🌿 Order Status Guide Panel -->
            <div class="ga-panel">
                <div class="greenangel-status-guide">
                    <div class="guide-title-pill">📋 ORDER STATUS GUIDE</div>
                    <div class="guide-grid">
                        <div class="guide-item">
                            <div class="status-circle pending-payment"></div>
                            <span class="status-label">PENDING PAYMENT</span>
                        </div>
                        <div class="guide-item">
                            <div class="status-circle processing"></div>
                            <span class="status-label">PROCESSING</span>
                        </div>
                        <div class="guide-item">
                            <div class="status-circle ship-today"></div>
                            <span class="status-label">SHIP TODAY</span>
                        </div>
                        <div class="guide-item">
                            <div class="status-circle completed"></div>
                            <span class="status-label">COMPLETED</span>
                        </div>
                        <div class="guide-item">
                            <div class="status-circle cancelled"></div>
                            <span class="status-label">CANCELLED</span>
                        </div>
                    </div>
                    <div class="guide-footer">
                        📧 Important: Emails are sent for each status update. Check your spam folder if you don't see them.
                    </div>
                </div>
            </div>
            
            <!-- 🌿 Orders List Panel -->
            <div class="ga-orders-panel">
                <?php if (empty($orders)) : ?>
                    <p class="greenangel-no-orders">No orders found.</p>
                <?php else : ?>
                    <div class="orders-grid" id="greenangel-orders-grid">
                    <?php 
                        $global_index = 0;
                        foreach ($orders_by_year as $year => $year_orders) : 
                    ?>
                        <!-- Year Separator -->
                        <div class="greenangel-year-separator" data-index="<?php echo $global_index; ?>" style="<?php echo $global_index >= 10 ? 'display:none;' : ''; ?>">
                            <div class="year-pill"><?php echo esc_html($year); ?></div>
                        </div>
                        <?php $global_index++; ?>
                        
                        <?php foreach ($year_orders as $order) : ?>
                        <?php
                            $order_id = $order->get_id();
                            $order_date = $order->get_date_created()->date_i18n('jS M');
                            $status = wc_get_order_status_name($order->get_status());
                            $status_slug = $order->get_status();
                            $view_url = esc_url($order->get_view_order_url());
                            
                            // Delivery date detection
                            $delivery_date = null;
                            $possible_keys = ['_delivery_date', 'delivery_date', '_jckwds_delivery_date', 'jckwds_delivery_date'];
                            foreach ($possible_keys as $key) {
                                $delivery_date = $order->get_meta($key);
                                if ($delivery_date) break;
                            }
                            
                            if (!$delivery_date) {
                                foreach ($possible_keys as $key) {
                                    $delivery_date = get_post_meta($order_id, $key, true);
                                    if ($delivery_date) break;
                                }
                            }
                            
                            // Format delivery date
                            $formatted_delivery_date = '';
                            if ($delivery_date) {
                                $date_obj = null;
                                $formats = ['Y-m-d', 'Y-m-d H:i:s', 'd/m/Y', 'm/d/Y', 'Y/m/d'];
                                foreach ($formats as $format) {
                                    $date_obj = DateTime::createFromFormat($format, $delivery_date);
                                    if ($date_obj) break;
                                }
                                
                                if (!$date_obj) {
                                    $timestamp = strtotime($delivery_date);
                                    if ($timestamp) {
                                        $date_obj = new DateTime();
                                        $date_obj->setTimestamp($timestamp);
                                    }
                                }
                                
                                if ($date_obj) {
                                    $formatted_delivery_date = $date_obj->format('j M Y');
                                } else {
                                    $formatted_delivery_date = $delivery_date;
                                }
                            }
                            
                            // Map statuses
                            $status_class_map = [
                                'pending' => 'pending-payment',
                                'processing' => 'processing', 
                                'ship-today' => 'ship-today',
                                'completed' => 'completed',
                                'cancelled' => 'cancelled',
                                'on-hold' => 'pending-payment',
                                'refunded' => 'cancelled',
                                'failed' => 'cancelled'
                            ];
                            $status_class = isset($status_class_map[$status_slug]) ? $status_class_map[$status_slug] : 'processing';
                        ?>
                        <div class="greenangel-order-card" data-status="<?php echo esc_attr($status_class); ?>" data-index="<?php echo $global_index; ?>" style="<?php echo $global_index >= 10 ? 'display:none;' : ''; ?>">
                            <div class="order-meta">
                                <span class="order-number">#<?php echo esc_html($order_id); ?></span>
                                <span class="order-date"><?php echo esc_html($order_date); ?></span>
                            </div>
                            
                            <div class="order-status-center">
                                <div class="order-status <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status); ?></div>
                                
                                <?php if ($formatted_delivery_date) : ?>
                                    <div class="delivery-date-pill">
                                        Delivery: <?php echo esc_html($formatted_delivery_date); ?>
                                    </div>
                                <?php else : ?>
                                    <div class="delivery-placeholder-pill">
                                        No delivery date
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <a href="<?php echo $view_url; ?>" class="view-button">View Order</a>
                        </div>
                        <?php $global_index++; ?>
                    <?php endforeach; ?>
                    <?php endforeach; ?>
                    </div>
                    
                    <div class="load-more-container">
                        <button id="load-more-orders" class="load-more-orders-button" style="<?php echo count($orders) <= 10 ? 'display:none;' : ''; ?>">SHOW MORE</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const loadMoreBtn = document.getElementById('load-more-orders');
        const cards = document.querySelectorAll('.greenangel-order-card, .greenangel-year-separator');
        let visibleCount = 10;
        
        // Force-hide cards 11+ 
        cards.forEach((card,i) => {
            if (i >= visibleCount) {
                card.style.setProperty('display','none','important');
            }
        });
        
        // If ≤10 items total, hide the button
        if (cards.length <= visibleCount) {
            loadMoreBtn.style.setProperty('display','none','important');
        }
        
        loadMoreBtn.addEventListener('click', () => {
            let revealed = 0;
            cards.forEach(card => {
                if (card.style.getPropertyValue('display') === 'none' && revealed < 10) {
                    card.style.setProperty('display','block','important');
                    revealed++;
                }
            });
            
            visibleCount += revealed;
            
            if (visibleCount >= cards.length) {
                loadMoreBtn.style.setProperty('display','none','important');
            }
        });
    });
    </script>
    <?php
    
    // Prevent any further Woo output
    return false;
}

// 🌿 HIDE SIDEBAR ON ALL ACCOUNT PAGES - EVERYWHERE!
add_action('wp_head', 'greenangel_hide_all_account_sidebars');
function greenangel_hide_all_account_sidebars() {
    // Only run on account pages
    if (!is_account_page()) return;
    ?>
    <style>
        /* NUCLEAR OPTION - Hide sidebar on ALL account pages */
        body.woocommerce-account .woocommerce-MyAccount-navigation {
            display: none !important;
            visibility: hidden !important;
            width: 0 !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            position: absolute !important;
            left: -9999px !important;
        }
        
        /* Make content full width on ALL account pages */
        body.woocommerce-account .woocommerce-MyAccount-content {
            width: 100% !important;
            max-width: 100% !important;
            float: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* Fix any container that might be using flexbox */
        body.woocommerce-account .woocommerce {
            display: block !important;
        }
        
        /* Extra targeting for stubborn themes */
        .woocommerce-MyAccount-navigation,
        .woocommerce-account-navigation,
        #account-navigation,
        .account-navigation,
        aside.woocommerce-MyAccount-navigation {
            display: none !important;
        }
    </style>
    <?php
}

// 🌿 Green Angel – ADDRESS EDIT PAGES
// Add CSS for edit address pages
add_action('wp_head', 'greenangel_address_edit_styles');
function greenangel_address_edit_styles() {
    if (!is_wc_endpoint_url('edit-address')) return;
    ?>
    <style>
        /* Hide WooCommerce's default address forms */
        .woocommerce-Addresses,
        .woocommerce-address-fields,
        .woocommerce-address-fields__field-wrapper,
        body.greenangel-edit-address .woocommerce-MyAccount-content > h3 {
            display: none !important;
        }
        
        /* Hide sidebar on address pages */
        body.greenangel-edit-address .woocommerce-MyAccount-navigation {
            display: none !important;
        }
        
        body.greenangel-edit-address .woocommerce-MyAccount-content {
            width: 100% !important;
            float: none !important;
            margin: 0 !important;
        }
        
        /* Form field styling */
        .ga-address-form .form-row {
            margin-bottom: 1.5rem;
        }
        
        .ga-address-form label {
            color: #aed604;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .ga-address-form .woocommerce-input-wrapper {
            width: 100%;
        }
        
        .ga-address-form input[type="text"],
        .ga-address-form input[type="email"],
        .ga-address-form input[type="tel"],
        .ga-address-form select,
        .ga-address-form .select2-container .select2-selection--single {
            width: 100%;
            background: rgba(26, 26, 26, 0.5);
            border: 1px solid #333;
            color: #ffffff;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s ease;
        }
        
        .ga-address-form input:focus,
        .ga-address-form select:focus {
            outline: none;
            border-color: #aed604;
        }
        
        /* Required asterisk */
        .ga-address-form .required {
            color: #c6f731;
            font-weight: 700;
        }
        
        /* Submit button */
        .ga-address-submit {
            background: linear-gradient(135deg, #aed604 0%, #c6f731 100%);
            color: #222222;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 100%;
            margin-top: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .ga-address-submit:hover {
            transform: translateY(-2px);
        }
        
        /* Select2 styling */
        .select2-dropdown {
            background: #1a1a1a;
            border: 1px solid #444;
        }
        
        .select2-container--default .select2-selection--single {
            background-color: rgba(26, 26, 26, 0.5);
            border: 1px solid #333;
            height: auto;
            padding: 0.75rem 1rem;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #ffffff;
            padding: 0;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            right: 10px;
        }
        
        .select2-results__option {
            color: #ffffff;
            padding: 0.5rem 1rem;
        }
        
        .select2-results__option--highlighted {
            background: #aed604 !important;
            color: #222222 !important;
        }
        
        /* Address selection cards */
        .ga-address-cards {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .ga-address-card {
            background: rgba(26, 26, 26, 0.5);
            border: 1px solid #333;
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        
        .ga-address-card:hover {
            border-color: #aed604;
            transform: translateY(-2px);
        }
        
        .ga-address-card h3 {
            color: #aed604;
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0 0 1rem 0;
            text-transform: uppercase;
        }
        
        .ga-address-card address {
            color: #cccccc;
            font-style: normal;
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        .ga-edit-link {
            display: inline-block;
            background: #444;
            color: #aed604 !important;
            text-decoration: none !important;
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s ease;
        }
        
        .ga-edit-link:hover {
            background: linear-gradient(135deg, #aed604 0%, #c6f731 100%);
            color: #222222 !important;
        }
        
        @media (min-width: 768px) {
            .ga-address-cards {
                max-width: 100%;
            }
        }
    </style>
    <?php
}

// Override the main addresses page (selection page)
add_action('woocommerce_account_edit-address_endpoint', 'greenangel_custom_addresses_page', 1);
function greenangel_custom_addresses_page($type) {
    // Remove default WooCommerce address handling
    remove_action('woocommerce_account_edit-address_endpoint', 'woocommerce_account_edit_address', 10);
    
    // If no type specified, show address selection
    if (!$type) {
        $user_id = get_current_user_id();
        ?>
        <div class="greenangel-dashboard-wrapper">
            <div class="ga-panel">
                <h3 class="ga-panel-title">
                    <span class="ga-title-pill" style="background:#aed604;">📍 MY ADDRESSES</span>
                </h3>
                
                <div class="ga-address-cards">
                    <!-- Only Shipping Address Card -->
                    <div class="ga-address-card">
                        <h3>📦 Shipping Address</h3>
                        <address>
                            <?php
                            $shipping_address = WC()->countries->get_formatted_address([
                                'first_name' => get_user_meta($user_id, 'shipping_first_name', true),
                                'last_name'  => get_user_meta($user_id, 'shipping_last_name', true),
                                'company'    => get_user_meta($user_id, 'shipping_company', true),
                                'address_1'  => get_user_meta($user_id, 'shipping_address_1', true),
                                'address_2'  => get_user_meta($user_id, 'shipping_address_2', true),
                                'city'       => get_user_meta($user_id, 'shipping_city', true),
                                'state'      => get_user_meta($user_id, 'shipping_state', true),
                                'postcode'   => get_user_meta($user_id, 'shipping_postcode', true),
                                'country'    => get_user_meta($user_id, 'shipping_country', true),
                            ]);
                            
                            echo $shipping_address ? wp_kses_post($shipping_address) : 'No shipping address set.';
                            ?>
                        </address>
                        <a href="<?php echo esc_url(wc_get_endpoint_url('edit-address', 'shipping')); ?>" class="ga-edit-link">Edit</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return;
    }
    
    // For billing, redirect to shipping
    if ($type === 'billing') {
        wp_redirect(wc_get_endpoint_url('edit-address', 'shipping'));
        exit;
    }
    
    // Only handle shipping address
    if ($type !== 'shipping') {
        echo '<p class="greenangel-notice">Invalid address type.</p>';
        return;
    }
    
    // Get address fields
    $user_id = get_current_user_id();
    $address = WC()->countries->get_address_fields(get_user_meta($user_id, 'shipping_country', true), 'shipping_');
    
    // Remove company field - we don't need it!
    unset($address['shipping_company']);
    
    // Load current values
    foreach ($address as $key => $field) {
        $address[$key]['value'] = get_user_meta($user_id, $key, true);
    }
    
    ?>
    <div class="greenangel-dashboard-wrapper">
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-address')); ?>" class="ga-back-button">← Back to Addresses</a>
        
        <div class="ga-panel">
            <h3 class="ga-panel-title">
                <span class="ga-title-pill" style="background:#aed604;">📦 EDIT SHIPPING ADDRESS</span>
            </h3>
            
            <div class="ga-inner-panel">
                <form method="post" class="ga-address-form">
                    <?php 
                    // Manually output fields in the order we want
                    woocommerce_form_field('shipping_first_name', $address['shipping_first_name'], $address['shipping_first_name']['value']);
                    woocommerce_form_field('shipping_last_name', $address['shipping_last_name'], $address['shipping_last_name']['value']);
                    woocommerce_form_field('shipping_address_1', $address['shipping_address_1'], $address['shipping_address_1']['value']);
                    woocommerce_form_field('shipping_address_2', $address['shipping_address_2'], $address['shipping_address_2']['value']);
                    woocommerce_form_field('shipping_city', $address['shipping_city'], $address['shipping_city']['value']);
                    woocommerce_form_field('shipping_state', $address['shipping_state'], $address['shipping_state']['value']);
                    woocommerce_form_field('shipping_country', $address['shipping_country'], $address['shipping_country']['value']);
                    woocommerce_form_field('shipping_postcode', $address['shipping_postcode'], $address['shipping_postcode']['value']);
                    ?>
                    
                    <p>
                        <button type="submit" class="ga-address-submit" name="save_address" value="<?php esc_attr_e('Save address', 'woocommerce'); ?>">
                            <?php esc_html_e('Save address', 'woocommerce'); ?>
                        </button>
                        <?php wp_nonce_field('woocommerce-edit_address', 'woocommerce-edit-address-nonce'); ?>
                        <input type="hidden" name="action" value="edit_address" />
                    </p>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    // Hide any leftover WooCommerce titles
    document.addEventListener('DOMContentLoaded', function() {
        const titles = document.querySelectorAll('h2');
        titles.forEach(title => {
            if (title.textContent.trim() === 'Shipping address') {
                title.style.display = 'none';
            }
        });
    });
    </script>
    <?php
}

// Remove default WooCommerce address handling
remove_action('woocommerce_account_edit-address_endpoint', 'woocommerce_account_edit_address', 10);

// 🌿 Green Angel – ACCOUNT DETAILS PAGE OVERRIDE
// Remove Woo's default account details
remove_action('woocommerce_account_edit-account_endpoint', 'woocommerce_account_edit_account', 10);

// Add CSS for account details page
add_action('wp_head', 'greenangel_account_details_styles');
function greenangel_account_details_styles() {
    if (!is_wc_endpoint_url('edit-account')) return;
    ?>
    <style>
        /* Hide WooCommerce's default account form */
        .woocommerce-EditAccountForm,
        .woocommerce-form-row,
        body.greenangel-edit-account .woocommerce-MyAccount-content > *:not(.greenangel-dashboard-wrapper) {
            display: none !important;
        }
        
        /* Account form styling matching address form */
        .ga-account-form .form-group {
            margin-bottom: 1.5rem;
        }
        
        .ga-account-form label {
            color: #aed604;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .ga-account-form input[type="text"],
        .ga-account-form input[type="email"],
        .ga-account-form input[type="password"],
        .ga-account-form select {
            width: 100%;
            background: rgba(26, 26, 26, 0.5);
            border: 1px solid #333;
            color: #ffffff;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s ease;
        }
        
        .ga-account-form input:focus,
        .ga-account-form select:focus {
            outline: none;
            border-color: #aed604;
        }
        
        .ga-account-form input::placeholder {
            color: #666;
            opacity: 1;
        }
        
        /* Required asterisk */
        .ga-account-form .required {
            color: #c6f731;
            font-weight: 700;
        }
        
        /* Submit button matching address submit */
        .ga-account-submit {
            background: linear-gradient(135deg, #aed604 0%, #c6f731 100%);
            color: #222222;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 100%;
            margin-top: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .ga-account-submit:hover {
            transform: translateY(-2px);
        }
        
        /* Helper text */
        .ga-helper-text {
            color: #999;
            font-size: 0.8rem;
            margin-top: 0.3rem;
            font-style: italic;
        }
        
        /* Birthday select styling */
        .ga-birthday-group {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1rem;
        }
        
        .ga-birthday-note {
            background: rgba(174, 214, 4, 0.1);
            border: 1px solid rgba(174, 214, 4, 0.3);
            border-radius: 8px;
            padding: 1rem;
            margin-top: 0.5rem;
            color: #aed604;
            font-size: 0.85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .ga-birthday-note:before {
            content: "⚠️";
            font-size: 1.1rem;
        }
        
        /* Password section styling */
        .ga-password-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #333;
        }
        
        .ga-section-title {
            color: #aed604;
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Responsive */
        @media (max-width: 767px) {
            .ga-birthday-group {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <?php
}

// Create custom account details page
add_action('woocommerce_account_edit-account_endpoint', 'greenangel_custom_account_details', 1);
function greenangel_custom_account_details() {
    if (!is_user_logged_in()) {
        echo '<p class="greenangel-notice">Please log in to edit your account details.</p>';
        return;
    }
    
    $user = wp_get_current_user();
    $user_id = $user->ID;
    
    // Get user meta
    $first_name = get_user_meta($user_id, 'first_name', true);
    $last_name = get_user_meta($user_id, 'last_name', true);
    $display_name = $user->display_name;
    $email = $user->user_email;
    
    // Birthday fields
    $birth_month = get_user_meta($user_id, 'birth_month', true);
    $birth_year = get_user_meta($user_id, 'birth_year', true);
    
    ?>
    <div class="greenangel-dashboard-wrapper">
        <div class="ga-panel">
            <h3 class="ga-panel-title">
                <span class="ga-title-pill" style="background:#aed604;">👤 MY ACCOUNT DETAILS</span>
            </h3>
            
            <div class="ga-inner-panel">
                <form method="post" class="ga-account-form" id="ga-account-form">
                    <!-- Personal Information Section -->
                    <div class="form-group">
                        <label for="account_first_name">First name <span class="required">*</span></label>
                        <input type="text" id="account_first_name" name="account_first_name" value="<?php echo esc_attr($first_name); ?>" required />
                    </div>
                    
                    <div class="form-group">
                        <label for="account_last_name">Last name <span class="required">*</span></label>
                        <input type="text" id="account_last_name" name="account_last_name" value="<?php echo esc_attr($last_name); ?>" required />
                    </div>
                    
                    <div class="form-group">
                        <label for="account_display_name">Display name <span class="required">*</span></label>
                        <input type="text" id="account_display_name" name="account_display_name" value="<?php echo esc_attr($display_name); ?>" required />
                        <p class="ga-helper-text">This will be how your name will be displayed in the account section and in reviews</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="account_email">Email address <span class="required">*</span></label>
                        <input type="email" id="account_email" name="account_email" value="<?php echo esc_attr($email); ?>" required />
                    </div>
                    
                    <!-- Birthday Section -->
                    <div class="ga-password-section">
                        <h4 class="ga-section-title">🎂 Birthday Information</h4>
                        
                        <div class="ga-birthday-group">
                            <div class="form-group">
                                <label for="birth_month">Birth Month <span class="required">*</span></label>
                                <select id="birth_month" name="birth_month" <?php echo $birth_month ? 'disabled' : ''; ?>>
                                    <option value="">Select Month</option>
                                    <?php
                                    $months = [
                                        '01' => 'January',
                                        '02' => 'February',
                                        '03' => 'March',
                                        '04' => 'April',
                                        '05' => 'May',
                                        '06' => 'June',
                                        '07' => 'July',
                                        '08' => 'August',
                                        '09' => 'September',
                                        '10' => 'October',
                                        '11' => 'November',
                                        '12' => 'December'
                                    ];
                                    foreach ($months as $num => $name) {
                                        $selected = ($birth_month == $num) ? 'selected' : '';
                                        echo '<option value="' . esc_attr($num) . '" ' . $selected . '>' . esc_html($name) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="birth_year">Birth Year <span class="required">*</span></label>
                                <input type="text" id="birth_year" name="birth_year" 
                                       value="<?php echo esc_attr($birth_year); ?>" 
                                       placeholder="YYYY" 
                                       maxlength="4" 
                                       pattern="\d{4}"
                                       <?php echo $birth_year ? 'disabled' : ''; ?> />
                            </div>
                        </div>
                        
                        <?php if ($birth_month && $birth_year) : ?>
                            <div class="ga-birthday-note">
                                Birthday information can only be set once and cannot be changed later.
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Password Section -->
                    <div class="ga-password-section">
                        <h4 class="ga-section-title">🔐 Change Password</h4>
                        
                        <div class="form-group">
                            <label for="password_current">Current password (leave blank to leave unchanged)</label>
                            <input type="password" id="password_current" name="password_current" autocomplete="off" />
                        </div>
                        
                        <div class="form-group">
                            <label for="password_1">New password (leave blank to leave unchanged)</label>
                            <input type="password" id="password_1" name="password_1" autocomplete="off" />
                        </div>
                        
                        <div class="form-group">
                            <label for="password_2">Confirm new password</label>
                            <input type="password" id="password_2" name="password_2" autocomplete="off" />
                        </div>
                    </div>
                    
                    <p>
                        <button type="submit" class="ga-account-submit" name="save_account_details" value="Save changes">
                            Save changes
                        </button>
                        <?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>
                        <input type="hidden" name="action" value="save_account_details" />
                    </p>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Year validation
        const yearInput = document.getElementById('birth_year');
        if (yearInput && !yearInput.disabled) {
            yearInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 4);
            });
        }
    });
    </script>
    <?php
}