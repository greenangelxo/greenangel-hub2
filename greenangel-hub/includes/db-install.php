<?php
// 🌿 Green Angel — DB Table Installer (Future-Proof Version)

function greenangel_create_code_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // ✅ Angel Codes Table
    if (!get_option('greenangel_codes_created')) {
        $table = $wpdb->prefix . 'greenangel_codes';
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
    if (!get_option('greenangel_code_logs_created')) {
        $table = $wpdb->prefix . 'greenangel_code_logs';
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
    if (!get_option('greenangel_failed_code_logs_created')) {
        $table = $wpdb->prefix . 'greenangel_failed_code_attempts';
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
}