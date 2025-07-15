<?php
/*
Plugin Name: Angel Hub ❤️
Plugin URI: https://greenangelshop.com
Description: Unified admin interface for Ship Today, NFC Cards, Packing Slips, Customers, and more — beautifully styled for the Green Angel brand.
Version: 1.0.0
Author: Jess + Aurora
Author URI: https://greenangelshop.com
*/

use Wlr\App\Models\Users;

// Load modules
require_once plugin_dir_path(__FILE__) . 'modules/dashboard.php';
require_once plugin_dir_path(__FILE__) . 'modules/ship-today.php';
require_once plugin_dir_path(__FILE__) . 'orders/orders-preview.php';
require_once plugin_dir_path(__FILE__) . 'account/account-dashboard.php';
require_once plugin_dir_path(__FILE__) . 'account/view-order.php';
require_once plugin_dir_path(__FILE__) . 'modules/nfc-manager.php';
require_once plugin_dir_path(__FILE__) . 'modules/tracking-numbers.php';
require_once plugin_dir_path(__FILE__) . 'modules/packing-slips.php';
require_once plugin_dir_path(__FILE__) . 'modules/code-manager/tab.php';
require_once plugin_dir_path(__FILE__) . 'modules/tools.php';
require_once plugin_dir_path(__FILE__) . 'modules/delivery-settings/delivery-settings.php';
require_once plugin_dir_path(__FILE__) . 'modules/postcode-rules/enforce-checkout.php';
require_once plugin_dir_path(__FILE__) . 'modules/stock-check.php';
require_once plugin_dir_path(__FILE__) . 'modules/angel-wallet/angel-wallet.php';
require_once plugin_dir_path(__FILE__) . 'modules/angel-wallet/functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/wallet-products.php';

// Maintenance Mode - Angelic Sleep System
require_once plugin_dir_path(__FILE__) . 'modules/maintenance/maintenance.php';

// NEW CUSTOMER MODULE - The crown jewel of your admin interface!
require_once plugin_dir_path(__FILE__) . 'modules/customers/customers.php';
require_once plugin_dir_path(__FILE__) . 'modules/customers/functions.php';

// NEW MODULAR EMOJI PICKER SYSTEM
// Load the new modular emoji picker interface (replaces old single files)
$emoji_modular_interface = plugin_dir_path(__FILE__) . 'account/includes/emoji-picker/emoji-picker-interface.php';
if (file_exists($emoji_modular_interface)) {
    require_once $emoji_modular_interface;
    
    // Simple emoji picker page creator function (inline since we're modular now)
    if (!function_exists('greenangel_create_emoji_picker_page')) {
        function greenangel_create_emoji_picker_page() {
            // Check if we already created this page
            if (get_option('greenangel_emoji_picker_page_id')) {
                return true;
            }
            
            // Check if page already exists
            $existing = get_page_by_path('emoji-picker');
            
            if ($existing) {
                // Page exists, save its ID
                update_option('greenangel_emoji_picker_page_id', $existing->ID);
                return true;
            }
            
            // Create the page with our modular shortcode
            $page_data = array(
                'post_title' => 'Emoji Picker',
                'post_name' => 'emoji-picker',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_content' => '[greenangel_emoji_picker]', // This shortcode is in the modular interface
            );
            
            $page_id = wp_insert_post($page_data);
            
            if ($page_id && !is_wp_error($page_id)) {
                update_option('greenangel_emoji_picker_page_id', $page_id);
                return true;
            }
            
            return false;
        }
    }
    
    // Simple admin tab renderer function (inline since we're modular now)
    if (!function_exists('greenangel_render_emoji_picker_tab')) {
        function greenangel_render_emoji_picker_tab() {
            echo '<h2>🎨 Emoji Picker - Modular Edition</h2>';
            
            $page_id = get_option('greenangel_emoji_picker_page_id');
            
            if ($page_id) {
                $page_url = get_permalink($page_id);
                echo '<p>✅ Modular emoji picker page created successfully!</p>';
                echo '<p><a href="' . esc_url($page_url) . '" target="_blank" class="button">View Emoji Picker Page →</a></p>';
                
                echo '<div style="background: #333; padding: 20px; border-radius: 8px; margin-top: 20px;">';
                echo '<h3>🚀 Modular System Active!</h3>';
                echo '<p><strong>✨ Core Features:</strong></p>';
                echo '<ul>';
                echo '<li>🎨 <strong>Split CSS:</strong> Core styles + Fate spinner styles</li>';
                echo '<li>⚡ <strong>Split JS:</strong> Main functionality + Fast fate spinner</li>';
                echo '<li>🔙 <strong>Sleek Back Button:</strong> Bottom-left pill with arrow only</li>';
                echo '<li>📱 <strong>Mobile Categories:</strong> 2 per row on mobile</li>';
                echo '<li>🎲 <strong>Enticing Fate Button:</strong> Wiggles and breathes to entice users</li>';
                echo '<li>⚡ <strong>Fast Spinner:</strong> No glitchy 5-second waits!</li>';
                echo '</ul>';
                echo '</div>';
            } else {
                echo '<p>⚠️ Emoji picker page not found. Try reactivating the plugin.</p>';
                
                echo '<div style="background: #444; padding: 15px; border-radius: 8px; margin-top: 15px;">';
                echo '<p><strong>🔧 Troubleshooting:</strong></p>';
                echo '<p>If you\'re having issues, make sure these files exist:</p>';
                echo '<ul>';
                echo '<li><code>account/includes/emoji-picker/emoji-picker-interface.php</code></li>';
                echo '<li><code>account/includes/emoji-picker/css/emoji-picker-core.css</code></li>';
                echo '<li><code>account/includes/emoji-picker/css/emoji-picker-fate.css</code></li>';
                echo '<li><code>account/includes/emoji-picker/js/emoji-picker-core.js</code></li>';
                echo '<li><code>account/includes/emoji-picker/js/emoji-picker-fate.js</code></li>';
                echo '</ul>';
                echo '</div>';
            }
        }
    }
} else {
    // FALLBACK: Load old emoji picker files if modular system not found
    $emoji_picker_page_file = plugin_dir_path(__FILE__) . 'account/includes/emoji-picker-page.php';
    if (file_exists($emoji_picker_page_file)) {
        require_once $emoji_picker_page_file;
    }

    $emoji_interface_file = plugin_dir_path(__FILE__) . 'account/includes/emoji-picker-interface.php';
    if (file_exists($emoji_interface_file)) {
        require_once $emoji_interface_file;
    }
}

// Load DB installer - KEEP YOUR EXISTING SYSTEM!
require_once plugin_dir_path(__FILE__) . 'includes/db-install.php';
add_action('plugins_loaded', 'greenangel_create_code_tables');

// Automatically create the custom account page if not exists
register_activation_hook(__FILE__, 'greenangel_create_account_page');
function greenangel_create_account_page() {
    $slug = 'angel-hub';
    if (!get_page_by_path($slug)) {
        wp_insert_post([
            'post_title'   => 'Angel Hub',
            'post_name'    => $slug,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '[greenangel_account_dashboard]',
        ]);
    }
}

// Auto-Create the Angel Wallet Page
register_activation_hook(__FILE__, 'greenangel_create_angel_wallet_page');
function greenangel_create_angel_wallet_page() {
    if (get_option('greenangel_angel_wallet_page_id')) return;
    
    $existing = get_page_by_path('angel-wallet');
    $page_id = $existing ? $existing->ID : wp_insert_post([
        'post_title' => 'Angel Wallet',
        'post_name' => 'angel-wallet',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_content' => '[greenangel_wallet_console]',
    ]);
    
    if ($page_id && !is_wp_error($page_id)) {
        update_option('greenangel_angel_wallet_page_id', $page_id);
    }
}

// Auto-Create the Emoji Picker Page (now uses modular system)
if (function_exists('greenangel_create_emoji_picker_page')) {
    register_activation_hook(__FILE__, 'greenangel_create_emoji_picker_page');
}

// Auto-Create Wallet Top-up Products
register_activation_hook(__FILE__, 'greenangel_create_wallet_topup_products');

// SAFE Redirect - only redirect logged-in users to existing Angel Hub page
add_action('template_redirect', function () {
    // Only run on account pages for logged-in users
    if (!is_user_logged_in() || !is_account_page()) {
        return;
    }
    
    // Don't redirect if we're already on angel-hub
    if (is_page('angel-hub')) {
        return;
    }
    
    // Don't redirect if we're on the emoji picker page
    if (is_page('emoji-picker')) {
        return;
    }
    
    // Don't redirect if we're on a WooCommerce endpoint (like orders, edit-address, etc)
    if (is_wc_endpoint_url()) {
        return;
    }
    
    // Find the Angel Hub page safely
    $angel_hub_page = get_page_by_path('angel-hub');
    
    // Only redirect if the page actually exists
    if ($angel_hub_page && $angel_hub_page->post_status === 'publish') {
        // Get the proper permalink (works with any permalink structure)
        $angel_hub_url = get_permalink($angel_hub_page->ID);
        
        // Only redirect if we got a valid URL on the CURRENT site
        if ($angel_hub_url && strpos($angel_hub_url, home_url()) === 0) {
            wp_safe_redirect($angel_hub_url);
            exit;
        }
    }
});

// Add to WP Admin menu
add_action('admin_menu', 'greenangel_hub_menu');
function greenangel_hub_menu() {
    add_menu_page(
        'Angel Hub ❤️',
        'Angel Hub ❤️',
        'manage_woocommerce',
        'greenangel-hub',
        'greenangel_hub_page',
        'data:image/svg+xml;base64,' . base64_encode('<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill="#aed604" d="M10 2C7.5 2 5.5 4 5.5 6.5V8H4c-.6 0-1 .4-1 1v8c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V9c0-.6-.4-1-1-1h-1.5V6.5C14.5 4 12.5 2 10 2zm-2.5 4V6.5C7.5 5.1 8.6 4 10 4s2.5 1.1 2.5 2.5V8h-5zm2.5 7c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>'),
        4.1
    );
}

// Admin styling + hide notices
add_action('admin_head','greenangel_admin_styles');
function greenangel_admin_styles() { ?>
    <style>
        #wpbody-content .notice, #wpbody-content .update-nag, #wpbody-content .updated, #wpbody-content .error, #wpbody-content .notice-alt { display: none!important; }
        #adminmenu #toplevel_page_greenangel-hub a.menu-top{color:#aed604!important;font-weight:600;transition:all .3s ease;} 
        #adminmenu #toplevel_page_greenangel-hub:hover a.menu-top,
        #adminmenu #toplevel_page_greenangel-hub.wp-has-current-submenu a.menu-top,
        #adminmenu #toplevel_page_greenangel-hub.current a.menu-top {
            background:#222;color:#aed604!important;
        }
        #adminmenu #toplevel_page_greenangel-hub .wp-menu-image:before {
            color:#aed604!important;
        }
    </style>
<?php }

// Main Admin Page Renderer
function greenangel_hub_page(){
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
    
    // Unified dark theme wrapper for all other tabs
    echo '<div class="wrap greenangel-dark-wrap">';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';  
    echo '<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">';
    echo '<style>
        /* Dark theme mobile-first styles */
        .greenangel-dark-wrap {
            font-family: "Poppins", sans-serif !important;
            background: #1a1a1a;
            padding: 20px;
            border-radius: 16px;
            color: #fff;
            min-height: calc(100vh - 80px);
            position: relative;
            overflow-x: hidden;
        }
        
        /* Header section */
        .angel-dark-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .title-dark {
            font-size: 28px;
            font-weight: 700;
            color: #aed604;
            margin: 0 0 10px 0;
            line-height: 1.2;
        }
        
        .subtitle-dark {
            font-size: 14px;
            color: #888;
            margin: 0 0 20px 0;
        }
        
        /* Diamond power badge */
        .diamond-power-dark {
            display: inline-block;
            background: #aed604;
            color: #222;
            font-size: 11px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 50px;
            margin-bottom: 20px;
        }
        
        .diamond-power-dark:before {
            content: "✨ ";
        }
        
        /* Navigation tabs */
        .nav-tabs-dark {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 10px;
            margin-bottom: 30px;
            padding: 0;
        }
        
        .nav-tab-dark {
            background: #222;
            border: 2px solid #333;
            color: #aed604;
            padding: 12px 10px;
            border-radius: 14px;
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            transition: all .2s ease;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 50px;
            -webkit-tap-highlight-color: transparent;
        }
        
        .nav-tab-dark:hover,
        .nav-tab-dark:focus {
            background: #2a2a2a;
            border-color: #aed604;
            transform: translateY(-2px);
            color: #fff;
        }
        
        .nav-tab-dark.nav-tab-active {
            background: #aed604;
            color: #222;
            border-color: #aed604;
        }
        
        .nav-tab-dark:active {
            transform: translateY(0);
        }
        
        /* Content area */
        .angel-content-dark {
            background: #222;
            border: 2px solid #333;
            border-radius: 14px;
            padding: 20px;
            min-height: 400px;
        }
        
        /* Hide content area default styles */
        .angel-content-dark > * {
            color: #fff;
        }
        
        .angel-content-dark h2,
        .angel-content-dark h3 {
            color: #aed604;
        }
        
        .angel-content-dark table {
            color: #fff;
        }
        
        .angel-content-dark input,
        .angel-content-dark select,
        .angel-content-dark textarea {
            background: #333;
            border: 1px solid #444;
            color: #fff;
            border-radius: 8px;
            padding: 8px 12px;
        }
        
        .angel-content-dark .button,
        .angel-content-dark .button-primary {
            background: #aed604;
            color: #222;
            border: none;
            border-radius: 20px;
            padding: 8px 20px;
            font-weight: 600;
        }
        
        .angel-content-dark .button:hover {
            background: #9bc603;
        }
        
        /* Tablet styles (768px+) */
        @media (min-width: 768px) {
            .greenangel-dark-wrap {
                padding: 30px;
            }
            
            .angel-dark-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 35px;
                flex-wrap: wrap;
                gap: 20px;
            }
            
            .header-left-dark {
                text-align: left;
            }
            
            .title-dark {
                font-size: 36px;
                margin-bottom: 5px;
            }
            
            .subtitle-dark {
                font-size: 15px;
                margin: 0;
            }
            
            .diamond-power-dark {
                margin: 0;
            }
            
            .nav-tabs-dark {
                display: flex;
                flex-wrap: wrap;
                grid-template-columns: none;
                gap: 12px;
                justify-content: flex-start;
            }
            
            .nav-tab-dark {
                flex: 0 0 auto;
                padding: 12px 20px;
                font-size: 14px;
                min-width: 140px;
            }
            
            .angel-content-dark {
                padding: 30px;
            }
        }
        
        /* Desktop styles (1024px+) */
        @media (min-width: 1024px) {
            .greenangel-dark-wrap {
                padding: 40px;
                max-width: 1400px;
                margin: 20px auto;
            }
            
            .title-dark {
                font-size: 42px;
            }
            
            .subtitle-dark {
                font-size: 16px;
            }
            
            .nav-tab-dark {
                font-size: 15px;
                padding: 12px 24px;
            }
            
            .angel-content-dark {
                padding: 40px;
            }
        }
        
        /* Fix for WordPress default styles */
        .greenangel-dark-wrap .wrap {
            background: transparent;
            box-shadow: none;
            padding: 0;
            margin: 0;
        }
        
        /* Responsive tables */
        @media (max-width: 767px) {
            .angel-content-dark table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>';

    // Header with title and powered badge
    echo '<div class="angel-dark-header">';
    echo '<div class="header-left-dark">';
    echo '<h1 class="title-dark">Welcome, Angel 💫</h1>';
    echo '<p class="subtitle-dark">Your mission control for managing everything magical ✨</p>';
    echo '</div>';
    echo '<div class="diamond-power-dark">Powered by diamonds</div>';
    echo '</div>';
    
    // Navigation tabs - only show if NOT on dashboard
    if ($active_tab !== 'dashboard') {
        echo '<div class="nav-tabs-dark">';
        $tabs = [
            'dashboard' => '🌟 Dashboard',
            'ship-today' => '💌 Ship Today',
            'nfc-manager' => '💳 NFC Manager',
            'packing-slips' => '📦 Packing Slips',
            'tracking-numbers' => '📮 Tracking',
            'angel-codes' => '🪽 Angel Codes',
            'delivery-settings' => '🚚 Delivery Settings',
            'stock-check' => '📊 Stock Check',
            'wallet' => '💸 Wallet',
            'customers' => '👥 Customers',
            'maintenance' => '🌙 Maintenance',  // NEW MAINTENANCE TAB
            'tools' => '🛠️ Tools'
        ];
        
        // Only add emoji picker tab if the functions exist
        if (function_exists('greenangel_render_emoji_picker_tab')) {
            $tabs['emoji-picker'] = '🎨 Emoji Picker';
        }
        
        foreach($tabs as $key => $label){
            $active = ($active_tab === $key) ? 'nav-tab-active' : '';
            echo "<a href='?page=greenangel-hub&tab={$key}' class='nav-tab-dark {$active}'>{$label}</a>";
        }
        echo '</div>';
    }
    
    // Content area with dark theme
    echo '<div class="angel-content-dark">';
    switch($active_tab){
        case 'dashboard':
            greenangel_render_dashboard_tab();
            break;
        case 'ship-today': 
            greenangel_render_ship_today_tab(); 
            break;
        case 'tracking-numbers':
            greenangel_render_tracking_numbers();
            break;
        case 'nfc-manager': 
            greenangel_render_nfc_card_manager(); 
            break;
        case 'packing-slips': 
            greenangel_render_packing_slips_tab(); 
            break;
        case 'angel-codes': 
            greenangel_render_angel_codes_tab(); 
            break;
        case 'delivery-settings': 
            greenangel_render_delivery_settings_tab(); 
            break;
        case 'stock-check':
            greenangel_render_stock_check_tab(); 
            break;
        case 'wallet':
            greenangel_render_wallet_tab();
            break;
        case 'customers':  // Customer management module
            greenangel_render_customers_tab();
            break;
        case 'maintenance':  // Maintenance mode module
            greenangel_render_maintenance_tab();
            break;
        case 'emoji-picker':
            // Only call function if it exists
            if (function_exists('greenangel_render_emoji_picker_tab')) {
                greenangel_render_emoji_picker_tab();
            } else {
                echo '<h2>🎨 Emoji Picker</h2>';
                echo '<p>Emoji picker module is being prepared... ✨</p>';
            }
            break;
        case 'tools': 
            greenangel_render_tools_tab(); 
            break;
    }
    echo '</div>';
    echo '</div>';
}
?>