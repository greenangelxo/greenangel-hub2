<?php
/*
Plugin Name: Green Angel Hub 🌈
Plugin URI: https://greenangelshop.com
Description: Unified admin interface for Ship Today, NFC Cards, Packing Slips, and more — beautifully styled for the Green Angel brand.
Version: 1.0.0
Author: Jess + Aurora
Author URI: https://greenangelshop.com
*/

use Wlr\App\Models\Users;

// 🌿 Load modules
require_once plugin_dir_path(__FILE__) . 'modules/ship-today.php';
require_once plugin_dir_path(__FILE__) . 'modules/nfc-manager.php';
require_once plugin_dir_path(__FILE__) . 'modules/packing-slips.php';
require_once plugin_dir_path(__FILE__) . 'modules/code-manager/tab.php'; // 💫 Angel Code Manager tab

// ✅ Load DB installer
require_once plugin_dir_path(__FILE__) . 'includes/db-install.php';
add_action('plugins_loaded', 'greenangel_create_code_tables');

function greenangel_register_user_actions() {
    add_action('admin_post_nopriv_greenangel_register_user', 'greenangel_handle_registration');
    add_action('admin_post_greenangel_register_user', 'greenangel_handle_registration');
}
add_action('init', 'greenangel_register_user_actions');

// 🌈 Add to WP Admin
add_action('admin_menu', 'greenangel_hub_menu');

function greenangel_hub_menu() {
    add_menu_page(
        'Green Angel Hub 🌈',
        'Green Angel Hub 🌈',
        'manage_woocommerce',
        'greenangel-hub',
        'greenangel_hub_page',
        'dashicons-admin-generic',
        4.1
    );
}

// 🖼 Hub Page
function greenangel_hub_page() {
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'ship-today';

    echo '<div class="wrap greenangel-wrap">';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">';

    echo '<style>
        .greenangel-wrap {
            font-family: "Poppins", sans-serif !important;
            background-color: #8cb000;
            background: linear-gradient(135deg, #aed604, #8cb000);
            padding: 30px;
            border-radius: 16px;
            margin-right: 20px;
            color: #222222;
            position: relative;
        }
        .diamond-power {
            position: absolute;
            top: 30px;
            right: 30px;
            background-color: #222222;
            color: #aed604;
            font-size: 12px;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 20px;
        }
        .diamond-power:before {
            content: "✨";
            margin-right: 5px;
        }
        .title-bubble {
            display: inline-block;
            background-color: #222222;
            color: #aed604;
            padding: 12px 20px;
            border-radius: 20px;
            font-size: 20px;
            font-weight: 500;
            margin-bottom: 25px;
            letter-spacing: 0.3px;
        }
        .nav-tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 30px;
        }
        .nav-tab {
            background: #222222;
            color: #aed604;
            padding: 10px 18px;
            border-radius: 20px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            outline: none;
            -webkit-tap-highlight-color: transparent;
            user-select: none;
        }
        .nav-tab:hover {
            color: #ffffff;
            background: #222222;
        }
        .nav-tab:active, .nav-tab:focus {
            color: #ffffff;
            background: #222222;
            outline: none;
            border: none;
            box-shadow: none;
        }
        .nav-tab.nav-tab-active {
            color: #ffffff;
            background: #222222;
            border: none;
            outline: none;
        }
    </style>';

    echo '<div class="diamond-power">Powered by diamonds</div>';
    echo '<div class="title-bubble">💚 Welcome to the Green Angel Hub</div>';

    // 🔀 Navigation Tabs
    echo '<div class="nav-tabs">';
    echo '<a href="?page=greenangel-hub&tab=ship-today" class="nav-tab ' . ($active_tab === 'ship-today' ? 'nav-tab-active' : '') . '">💌 Ship Today</a>';
    echo '<a href="?page=greenangel-hub&tab=nfc-manager" class="nav-tab ' . ($active_tab === 'nfc-manager' ? 'nav-tab-active' : '') . '">💳 NFC Manager</a>';
    echo '<a href="?page=greenangel-hub&tab=packing-slips" class="nav-tab ' . ($active_tab === 'packing-slips' ? 'nav-tab-active' : '') . '">📦 Packing Slips</a>';
    echo '<a href="?page=greenangel-hub&tab=angel-codes" class="nav-tab ' . ($active_tab === 'angel-codes' ? 'nav-tab-active' : '') . '">🪽 Angel Codes</a>';
    echo '</div>';

    // 🧠 Tab Renderer
    if ($active_tab === 'ship-today') {
        greenangel_render_ship_today_tab();
    } elseif ($active_tab === 'nfc-manager') {
        greenangel_render_nfc_card_manager();
    } elseif ($active_tab === 'packing-slips') {
        greenangel_render_packing_slips_tab();
    } elseif ($active_tab === 'angel-codes') {
        greenangel_render_angel_codes_tab(); // ✅ NEW RENDER FUNCTION
    }

    echo '</div>';
}