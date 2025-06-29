<?php
/*
Plugin Name: Angel Hub ❤️
Plugin URI: https://greenangelshop.com
Description: Unified admin interface for Ship Today, NFC Cards, Packing Slips, and more — beautifully styled for the Green Angel brand.
Version: 1.0.0
Author: Jess + Aurora
Author URI: https://greenangelshop.com
*/

use Wlr\App\Models\Users;

// ✅ Load DB installer early for shared helpers
require_once plugin_dir_path(__FILE__) . 'includes/db-install.php';
add_action('plugins_loaded', 'greenangel_create_code_tables');

// 🌿 Load modules
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

// ➕ Register frontend user actions
function greenangel_register_user_actions() {
    add_action('admin_post_nopriv_greenangel_register_user', 'greenangel_handle_registration');
    add_action('admin_post_greenangel_register_user', 'greenangel_handle_registration');
}
add_action('init', 'greenangel_register_user_actions');

// 🪄 Automatically create the custom account page if not exists
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

// 🔁 Redirect /my-account to your custom page and preserve endpoints
add_action('template_redirect', function () {
    if (is_account_page() && !is_page('angel-hub')) {
        $request_uri = $_SERVER['REQUEST_URI'];
        $endpoint = remove_query_arg('page', $request_uri);
        wp_redirect(home_url('/angel-hub' . $endpoint));
        exit;
    }
});

// 🌈 Add to WP Admin menu
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

// 💅 Admin styling + hide notices
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

// 🌌 Main Admin Page Renderer (unchanged)
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
            'tools' => '🛠️ Tools'
        ];
        
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
        case 'tools': 
            greenangel_render_tools_tab(); 
            break;
    }
    echo '</div>';
    echo '</div>';
}