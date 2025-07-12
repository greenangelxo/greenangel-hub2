<?php
/**
 * 🎨 Green Angel Account Pages - Styles
 * Location: plugins/greenangel-hub/account/includes/styles.php
 */

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
            border-radius: 50px;
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

// Add CSS for account details page
add_action('wp_head', 'greenangel_account_details_styles');
function greenangel_account_details_styles() {
    if (!is_wc_endpoint_url('edit-account')) return;
    
    // Load the dice game CSS from the login plugin
    $login_plugin_url = plugin_dir_url(dirname(dirname(dirname(__FILE__)))) . 'greenangel-login-gate/assets/';
    ?>
    <link rel="stylesheet" href="<?php echo esc_url($login_plugin_url . 'angel-login.css?v=' . time()); ?>">
    
    <style>
        /* 🎨 GREEN ANGEL ACCOUNT EDIT STYLES - FIXED VERSION! */
        
        /* Only hide the default title */
        .woocommerce-MyAccount-content > h3 {
            display: none !important;
        }
        
        /* Style the account edit container */
        .woocommerce-MyAccount-content {
            background: linear-gradient(135deg, #1a1a1a 0%, #222222 100%);
            padding: 2rem;
            border-radius: 16px;
            color: #ffffff;
            font-family: 'Poppins', sans-serif;
        }
        
        /* Style the form container */
        .woocommerce-EditAccountForm {
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* Add custom title */
        .woocommerce-EditAccountForm:before {
            content: '👤 MY ACCOUNT DETAILS';
            display: block;
            background: #aed604;
            color: #222222;
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.9rem;
            text-align: center;
            margin: 0 auto 2rem;
            width: fit-content;
        }
        
        /* Style form rows */
        .woocommerce-form-row {
            margin-bottom: 1.5rem;
        }
        
        .woocommerce-form-row label {
            color: #aed604;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .woocommerce-form-row input[type="text"],
        .woocommerce-form-row input[type="email"],
        .woocommerce-form-row input[type="password"],
        .woocommerce-form-row select {
            width: 100%;
            background: rgba(26, 26, 26, 0.5);
            border: 1px solid #333;
            color: #ffffff;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.95rem;
        }
        
        .woocommerce-form-row input:focus,
        .woocommerce-form-row select:focus {
            outline: none;
            border-color: #aed604;
        }
        
        /* Required asterisk */
        .required {
            color: #c6f731;
            font-weight: 700;
        }
        
        /* Save button */
        .woocommerce-Button {
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
        
        .woocommerce-Button:hover {
            transform: translateY(-2px);
        }
        
        /* Field hints */
        .woocommerce-form-row span em {
            display: block;
            color: #999;
            font-size: 0.8rem;
            margin-top: 0.3rem;
            font-style: italic;
        }
        
        /* Password fields section */
        fieldset {
            border: none;
            padding: 0;
            margin: 2rem 0 0 0;
        }
        
        fieldset legend {
            color: #aed604;
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Birth date section */
        .ga-birth-date-section {
            margin-top: 2rem;
            padding: 1.5rem;
            background: rgba(26, 26, 26, 0.5);
            border: 1px solid #333;
            border-radius: 12px;
        }
        
        .ga-birth-notice {
            background: linear-gradient(135deg, #aed604 0%, #c6f731 100%);
            color: #000;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .ga-birth-notice p {
            margin: 0;
            font-weight: 600;
        }
        
        .ga-display-name-locked,
        .ga-field-locked {
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid rgba(255, 107, 107, 0.3);
            border-radius: 8px;
            padding: 0.75rem;
            margin-top: 0.5rem;
            color: #ff6b6b;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        /* Override dice game colors for dark theme */
        .angel-name-dice-game {
            margin-top: 0.5rem;
        }
        
        /* Success/Error messages */
        .woocommerce-message,
        .woocommerce-error {
            background: rgba(174, 214, 4, 0.1);
            border: 1px solid rgba(174, 214, 4, 0.3);
            color: #aed604;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .woocommerce-error {
            background: rgba(255, 107, 107, 0.1);
            border-color: rgba(255, 107, 107, 0.3);
            color: #ff6b6b;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .woocommerce-MyAccount-content {
                padding: 1rem;
            }
        }
    </style>
    <?php
}

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