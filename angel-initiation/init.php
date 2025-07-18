<?php
/**
 * Angel Initiation Module - Initialization
 * 
 * This file should be included in the main GreenAngel Hub plugin
 * 
 * @package GreenAngel
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define Angel Initiation constants
define('ANGEL_INITIATION_VERSION', '1.0.0');
define('ANGEL_INITIATION_PATH', plugin_dir_path(__FILE__));
define('ANGEL_INITIATION_URL', plugin_dir_url(__FILE__));

/**
 * Initialize Angel Initiation Module
 */
function init_angel_initiation() {
    // Check if WordPress is loaded
    if (!function_exists('add_action')) {
        return;
    }
    
    // Include the integration file
    require_once ANGEL_INITIATION_PATH . 'integration.php';
    
    // Flush rewrite rules on activation
    add_action('wp_loaded', 'angel_initiation_flush_rewrite_rules');
}

/**
 * Flush rewrite rules for the /angel-initiation URL
 */
function angel_initiation_flush_rewrite_rules() {
    // Only flush once
    if (get_option('angel_initiation_flush_rules')) {
        flush_rewrite_rules();
        delete_option('angel_initiation_flush_rules');
    }
}

/**
 * Activation hook
 */
function angel_initiation_activate() {
    // Set flag to flush rewrite rules
    add_option('angel_initiation_flush_rules', true);
    
    // Create any necessary database tables or options
    angel_initiation_create_options();
}

/**
 * Deactivation hook
 */
function angel_initiation_deactivate() {
    // Clean up rewrite rules
    flush_rewrite_rules();
}

/**
 * Create default options
 */
function angel_initiation_create_options() {
    add_option('angel_initiation_enabled', true);
    add_option('angel_initiation_reward_amount', 5.00);
    add_option('angel_initiation_redirect_delay', 30); // days
}

// Initialize the module
init_angel_initiation();

// Register activation/deactivation hooks
register_activation_hook(__FILE__, 'angel_initiation_activate');
register_deactivation_hook(__FILE__, 'angel_initiation_deactivate');