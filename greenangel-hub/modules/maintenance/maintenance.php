<?php
/**
 * MAINTENANCE MODE - ICONIC LED Main Controller
 * The brain of our jaw-dropping electric maintenance system
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Load our organized modules
require_once plugin_dir_path(__FILE__) . 'includes/maintenance-functions.php';
require_once plugin_dir_path(__FILE__) . 'maintenance-dashboard.php';
require_once plugin_dir_path(__FILE__) . 'maintenance-page.php';

// 🔐 Core maintenance check - runs super early
add_action('init', 'greenangel_maintenance_guardian', 1);

// Load admin assets when needed
add_action('admin_enqueue_scripts', 'greenangel_maintenance_admin_assets');

/**
 * 🛡️ The Guardian - protects your site while you work
 */
function greenangel_maintenance_guardian() {
    // Skip if maintenance is off
    if (!greenangel_is_maintenance_enabled()) {
        return;
    }
    
    // Skip for admins (only you!)
    if (current_user_can('manage_options')) {
        return;
    }
    
    // Secret backdoor for emergencies
    if (greenangel_check_emergency_access()) {
        return;
    }
    
    // 👋 Force logout non-admins
    if (is_user_logged_in()) {
        wp_logout();
    }
    
    // Block wp-admin access
    if (is_admin() && !wp_doing_ajax() && !wp_doing_cron()) {
        greenangel_show_maintenance_page();
        exit;
    }
    
    // Block frontend access (but allow webhooks/callbacks)
    if (!is_admin() && !wp_doing_ajax() && !wp_doing_cron() && 
        !greenangel_is_webhook_request()) {
        greenangel_show_maintenance_page();
        exit;
    }
}

/**
 * 🎨 Load admin dashboard assets
 */
function greenangel_maintenance_admin_assets($hook) {
    // Only load on our admin page
    if ($hook !== 'toplevel_page_greenangel-hub') {
        return;
    }
    
    if (!isset($_GET['tab']) || $_GET['tab'] !== 'maintenance') {
        return;
    }
    
    $plugin_url = plugin_dir_url(__FILE__);
    
    wp_enqueue_style(
        'greenangel-maintenance-admin',
        $plugin_url . 'assets/css/maintenance-admin.css',
        array(),
        '3.0.0'
    );
    
    wp_enqueue_script(
        'greenangel-maintenance-admin',
        $plugin_url . 'assets/js/maintenance-admin.js',
        array('jquery'),
        '3.0.0',
        true
    );
    
    // Pass data to JavaScript
    wp_localize_script('greenangel-maintenance-admin', 'maintenanceAdmin', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('maintenance_admin_nonce'),
        'isEnabled' => greenangel_is_maintenance_enabled(),
        'homeUrl' => home_url(),
        'currentEmoji' => greenangel_get_random_emoji(),
        'previewUrl' => home_url('?preview_maintenance=true')
    ));
}

/**
 * 🎛️ Main admin tab renderer
 */
function greenangel_render_maintenance_tab() {
    greenangel_render_maintenance_dashboard();
}

/**
 * 🔍 Preview mode for testing
 */
add_action('template_redirect', 'greenangel_maintenance_preview');
function greenangel_maintenance_preview() {
    if (isset($_GET['preview_maintenance']) && current_user_can('manage_options')) {
        greenangel_show_maintenance_page();
        exit;
    }
}

/**
 * 🔄 AJAX handlers for real-time updates
 */
add_action('wp_ajax_toggle_maintenance', 'greenangel_ajax_toggle_maintenance');
add_action('wp_ajax_save_maintenance_settings', 'greenangel_ajax_save_settings');
add_action('wp_ajax_test_emergency_access', 'greenangel_ajax_test_emergency');

function greenangel_ajax_toggle_maintenance() {
    if (!current_user_can('manage_options') || 
        !wp_verify_nonce($_POST['nonce'], 'maintenance_admin_nonce')) {
        wp_die('Unauthorized');
    }
    
    $enabled = greenangel_is_maintenance_enabled();
    $new_state = greenangel_toggle_maintenance_mode();
    
    $emoji = greenangel_get_random_emoji();
    
    $message = $new_state 
        ? 'Electric maintenance mode ACTIVATED! ' . $emoji . ' The LED magic has begun!'
        : 'Site is now LIVE with electric vibes! ' . $emoji . ' Welcome back to the neon paradise!';
    
    wp_send_json_success(array(
        'enabled' => $new_state,
        'message' => $message,
        'emoji' => $emoji
    ));
}

function greenangel_ajax_save_settings() {
    if (!current_user_can('manage_options') || 
        !wp_verify_nonce($_POST['nonce'], 'maintenance_admin_nonce')) {
        wp_die('Unauthorized');
    }
    
    $message = sanitize_textarea_field($_POST['message']);
    $eta = sanitize_text_field($_POST['eta']);
    
    update_option('greenangel_maintenance_message', $message);
    update_option('greenangel_maintenance_eta', $eta);
    
    $emoji = greenangel_get_random_emoji();
    
    wp_send_json_success(array(
        'message' => 'Electric settings saved! ' . $emoji . ' Pure LED magic!',
        'emoji' => $emoji
    ));
}

function greenangel_ajax_test_emergency() {
    if (!current_user_can('manage_options') || 
        !wp_verify_nonce($_POST['nonce'], 'maintenance_admin_nonce')) {
        wp_die('Unauthorized');
    }
    
    $emergency_url = home_url('?iamjess=true');
    
    wp_send_json_success(array(
        'url' => $emergency_url,
        'message' => 'Emergency access URL generated! 🚪✨'
    ));
}

/**
 * 🌟 Add special maintenance mode body class
 */
add_filter('body_class', 'greenangel_maintenance_body_class');
function greenangel_maintenance_body_class($classes) {
    if (greenangel_is_maintenance_enabled() && !current_user_can('manage_options')) {
        $classes[] = 'greenangel-maintenance-active';
        $classes[] = 'iconic-maintenance-mode';
    }
    return $classes;
}

/**
 * 🎭 Add custom maintenance headers for better SEO
 */
add_action('wp_head', 'greenangel_maintenance_headers');
function greenangel_maintenance_headers() {
    if (greenangel_is_maintenance_enabled() && !current_user_can('manage_options')) {
        echo '<meta name="maintenance-mode" content="active">' . "\n";
        echo '<meta name="maintenance-type" content="greenangel-iconic-led">' . "\n";
        echo '<meta name="robots" content="noindex, nofollow">' . "\n";
    }
}
?>