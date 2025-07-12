<?php
/**
 * 🌿 Green Angel — Database Table Installer
 * Creates all required tables for the Angel Code system + Customer Module
 */
function greenangel_create_code_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    // ✅ Angel Codes Table
    $table = $wpdb->prefix . 'greenangel_codes';
    if (!get_option('greenangel_codes_created') || $wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            code varchar(100) NOT NULL,
            label varchar(100) DEFAULT '',
            type varchar(20) DEFAULT 'promo',
            active tinyint(1) DEFAULT 1,
            expires_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY code (code)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        update_option('greenangel_codes_created', 'yes');
    }
    
    // ✅ Usage Logs Table
    $table = $wpdb->prefix . 'greenangel_code_logs';
    if (!get_option('greenangel_code_logs_created') || $wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT NULL,
            email varchar(100) DEFAULT '',
            code_used varchar(100) DEFAULT '',
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        dbDelta($sql);
        update_option('greenangel_code_logs_created', 'yes');
    }
    
    // ✅ Failed Attempts Table
    $table = $wpdb->prefix . 'greenangel_failed_code_attempts';
    if (!get_option('greenangel_failed_code_logs_created') || $wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            email varchar(100) DEFAULT '',
            code_tried varchar(100) DEFAULT '',
            ip_address varchar(45) DEFAULT '',
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        dbDelta($sql);
        update_option('greenangel_failed_code_logs_created', 'yes');
    }
    
    // ✅ Postcode Rules Table
    $table = $wpdb->prefix . 'greenangel_postcode_rules';
    if (!get_option('greenangel_postcode_rules_created') || $wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type VARCHAR(20) NOT NULL,
            postcode_prefix VARCHAR(10) NOT NULL,
            value DECIMAL(10,2) DEFAULT 0.00,
            message TEXT NOT NULL,
            active ENUM('yes','no') NOT NULL DEFAULT 'yes'
        ) $charset_collate;";
        
        dbDelta($sql);
        update_option('greenangel_postcode_rules_created', 'yes');
    }
    
    // Fix any existing NULL values in the active column
    $wpdb->query("UPDATE {$table} SET active = 'yes' WHERE active IS NULL");
    
    // ✅ Customer Module uses existing wallet system - no new tables needed!
}

// 🚀 ACTIVATION HOOK NOTE:
// This hook should be called from your main plugin file (greenangel-hub.php)
// NOT from this include file! Add this line to your main file:
// register_activation_hook(__FILE__, 'greenangel_create_code_tables');

// Also run on admin_init to catch any missed tables
add_action('admin_init', 'greenangel_ensure_tables_exist');

function greenangel_ensure_tables_exist() {
    // Only run if we haven't checked recently
    if (get_transient('greenangel_tables_checked')) {
        return;
    }
    
    greenangel_create_code_tables();
    
    // Set transient to avoid checking every page load
    set_transient('greenangel_tables_checked', true, DAY_IN_SECONDS);
}