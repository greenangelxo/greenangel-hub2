<?php
// Green Angel Hub – Tools Module with Notification Center

function greenangel_copy_login_form() {
    $source = plugin_dir_path(__FILE__) . 'assets/form-login.php';
    $dest   = get_theme_root() . '/savoy/woocommerce/myaccount/form-login.php';
    wp_mkdir_p( dirname( $dest ) );
    return copy( $source, $dest );
}

function greenangel_restore_login_form() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_die( 'Permission denied' );
    }
    check_admin_referer( 'greenangel_restore_login_form', 'greenangel_nonce' );
    if ( greenangel_copy_login_form() ) {
        update_option( 'greenangel_last_login_restore', current_time( 'mysql' ) );
        wp_redirect( admin_url( 'admin.php?page=greenangel-hub&tab=tools&restore=success' ) );
    } else {
        wp_redirect( admin_url( 'admin.php?page=greenangel-hub&tab=tools&restore=fail' ) );
    }
    exit;
}
add_action( 'admin_post_greenangel_restore_login_form', 'greenangel_restore_login_form' );

function greenangel_download_login_form() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_die( 'Permission denied' );
    }
    $file = get_theme_root() . '/savoy/woocommerce/myaccount/form-login.php';
    if ( ! file_exists( $file ) ) {
        wp_die( 'File not found' );
    }
    header( 'Content-Type: application/x-php' );
    header( 'Content-Disposition: attachment; filename="form-login.php"' );
    readfile( $file );
    exit;
}
add_action( 'admin_post_greenangel_download_login_form', 'greenangel_download_login_form' );

function greenangel_upload_login_form() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_die( 'Permission denied' );
    }
    check_admin_referer( 'greenangel_upload_login_form', 'greenangel_nonce_upload' );
    if ( empty( $_FILES['login_file']['name'] ) ) {
        wp_redirect( admin_url( 'admin.php?page=greenangel-hub&tab=tools&upload=fail' ) );
        exit;
    }
    $file = $_FILES['login_file'];
    $check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] );
    if ( $check['ext'] !== 'php' ) {
        wp_redirect( admin_url( 'admin.php?page=greenangel-hub&tab=tools&upload=invalid' ) );
        exit;
    }
    $dest = plugin_dir_path( __FILE__ ) . 'assets/form-login.php';
    if ( move_uploaded_file( $file['tmp_name'], $dest ) ) {
        greenangel_copy_login_form();
        update_option( 'greenangel_last_login_restore', current_time( 'mysql' ) );
        wp_redirect( admin_url( 'admin.php?page=greenangel-hub&tab=tools&upload=success' ) );
    } else {
        wp_redirect( admin_url( 'admin.php?page=greenangel-hub&tab=tools&upload=fail' ) );
    }
    exit;
}
add_action( 'admin_post_greenangel_upload_login_form', 'greenangel_upload_login_form' );

// 🔔 ENHANCED NOTIFICATION FUNCTIONS
function greenangel_save_notification() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Permission denied');
    }
    check_admin_referer('greenangel_save_notification', 'greenangel_notification_nonce');
    
    $notification_id = isset($_POST['notification_id']) ? sanitize_text_field($_POST['notification_id']) : 'notif_' . uniqid();
    $notification_type = isset($_POST['notification_type']) ? sanitize_text_field($_POST['notification_type']) : 'system';
    $notification_text = isset($_POST['notification_text']) ? stripslashes($_POST['notification_text']) : '';
    $notification_badge = isset($_POST['notification_badge']) ? sanitize_text_field($_POST['notification_badge']) : '';
    $notification_active = isset($_POST['notification_active']) ? 'yes' : 'no';
    
    // Clean up text
    $notification_text = str_replace(["\r\n", "\r", "\n"], ' ', $notification_text);
    $notification_text = preg_replace('/\s+/', ' ', $notification_text);
    $notification_text = str_replace(['<p>', '</p>'], '', $notification_text);
    
    $allowed_html = array(
        'strong' => array(),
        'b' => array(),
        'em' => array(),
        'i' => array(),
        'br' => array(),
        'span' => array('style' => array())
    );
    $notification_text = wp_kses($notification_text, $allowed_html);
    $notification_text = trim($notification_text);
    
    // Get existing messages
    $messages = get_option('greenangel_notification_messages', []);
    
    // If it's a new notification and no ID was provided, ensure unique ID
    if (empty($_POST['notification_id']) || $_POST['notification_id'] === '') {
        $notification_id = 'notif_' . time() . '_' . mt_rand(1000, 9999);
    }
    
    // Add/update message
    $messages[$notification_id] = [
        'type' => $notification_type,
        'text' => $notification_text,
        'badge' => $notification_badge,
        'active' => $notification_active,
        'created_at' => isset($messages[$notification_id]['created_at']) ? $messages[$notification_id]['created_at'] : time(),
        'updated_at' => time()
    ];
    
    update_option('greenangel_notification_messages', $messages);
    
    wp_redirect(admin_url('admin.php?page=greenangel-hub&tab=tools&notification=saved'));
    exit;
}
add_action('admin_post_greenangel_save_notification', 'greenangel_save_notification');

// Delete notification
function greenangel_delete_notification() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Permission denied');
    }
    check_admin_referer('greenangel_delete_notification');
    
    $notification_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    
    if ($notification_id) {
        $messages = get_option('greenangel_notification_messages', []);
        unset($messages[$notification_id]);
        update_option('greenangel_notification_messages', $messages);
    }
    
    wp_redirect(admin_url('admin.php?page=greenangel-hub&tab=tools&notification=deleted'));
    exit;
}
add_action('admin_post_greenangel_delete_notification', 'greenangel_delete_notification');

// Toggle notification
function greenangel_toggle_notification() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Permission denied');
    }
    check_admin_referer('greenangel_toggle_notification');
    
    $notification_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    
    if ($notification_id) {
        $messages = get_option('greenangel_notification_messages', []);
        if (isset($messages[$notification_id])) {
            $messages[$notification_id]['active'] = $messages[$notification_id]['active'] === 'yes' ? 'no' : 'yes';
            update_option('greenangel_notification_messages', $messages);
        }
    }
    
    wp_redirect(admin_url('admin.php?page=greenangel-hub&tab=tools&notification=toggled'));
    exit;
}
add_action('admin_post_greenangel_toggle_notification', 'greenangel_toggle_notification');

// 📮 NEW: Postcode Rule Functions
function greenangel_save_postcode_rule() {
    if (!current_user_can('manage_woocommerce')) wp_die('Nope');
    check_admin_referer('greenangel_save_postcode_rule');
    
    global $wpdb;
    $table = $wpdb->prefix . 'greenangel_postcode_rules';

    $type = sanitize_text_field($_POST['rule_type']);
    $prefix = strtoupper(sanitize_text_field($_POST['postcode_prefix']));
    $value = isset($_POST['min_value']) ? floatval($_POST['min_value']) : 0;
    $message = sanitize_text_field($_POST['error_message']);
    $rule_id = isset($_POST['rule_id']) ? intval($_POST['rule_id']) : 0;

    if ($rule_id > 0) {
        // Update existing rule
        $wpdb->update(
            $table,
            [
                'type' => $type,
                'postcode_prefix' => $prefix,
                'value' => $value,
                'message' => $message
            ],
            ['id' => $rule_id],
            ['%s', '%s', '%f', '%s'],
            ['%d']
        );
        wp_redirect(admin_url('admin.php?page=greenangel-hub&tab=tools&postcode=updated'));
    } else {
        // Force active to be 'yes' string, not boolean
        $result = $wpdb->insert($table, [
            'type' => $type,
            'postcode_prefix' => $prefix,
            'value' => $value,
            'message' => $message,
            'active' => 'yes'  // Explicitly set as string
        ], [
            '%s', // type
            '%s', // postcode_prefix
            '%f', // value
            '%s', // message
            '%s'  // active - IMPORTANT: string format
        ]);
        
        // If insert failed, try with direct SQL
        if ($result === false) {
            $wpdb->query($wpdb->prepare(
                "INSERT INTO $table (type, postcode_prefix, value, message, active) VALUES (%s, %s, %f, %s, 'yes')",
                $type, $prefix, $value, $message
            ));
        }
        wp_redirect(admin_url('admin.php?page=greenangel-hub&tab=tools&postcode=added'));
    }
    exit;
}
add_action('admin_post_greenangel_save_postcode_rule', 'greenangel_save_postcode_rule');

function greenangel_delete_postcode_rule() {
    if (!current_user_can('manage_woocommerce')) wp_die('Nope');
    check_admin_referer('greenangel_delete_postcode_rule');
    
    global $wpdb;
    $table = $wpdb->prefix . 'greenangel_postcode_rules';
    $wpdb->delete($table, ['id' => intval($_GET['id'])]);
    
    wp_redirect(admin_url('admin.php?page=greenangel-hub&tab=tools&postcode=deleted'));
    exit;
}
add_action('admin_post_greenangel_delete_postcode_rule', 'greenangel_delete_postcode_rule');

function greenangel_toggle_postcode_rule() {
    if (!current_user_can('manage_woocommerce')) wp_die('Nope');
    check_admin_referer('greenangel_toggle_postcode_rule');
    
    global $wpdb;
    $table = $wpdb->prefix . 'greenangel_postcode_rules';
    $id = intval($_GET['id']);
    
    // Get current status - handle NULL/empty values
    $current = $wpdb->get_var($wpdb->prepare("SELECT active FROM $table WHERE id = %d", $id));
    
    // If empty/null, treat as 'no'
    if (empty($current) || $current === NULL) {
        $current = 'no';
    }
    
    // Toggle
    $new_status = ($current === 'yes') ? 'no' : 'yes';
    
    // Update with explicit string format
    $wpdb->query($wpdb->prepare(
        "UPDATE $table SET active = %s WHERE id = %d",
        $new_status, $id
    ));
    
    wp_redirect(admin_url('admin.php?page=greenangel-hub&tab=tools&postcode=toggled'));
    exit;
}
add_action('admin_post_greenangel_toggle_postcode_rule', 'greenangel_toggle_postcode_rule');

function greenangel_render_tools_tab() {
    $theme_file    = get_theme_root() . '/savoy/woocommerce/myaccount/form-login.php';
    $code_contents = file_exists( $theme_file ) ? file_get_contents( $theme_file ) : '';
    $last_restored = get_option( 'greenangel_last_login_restore' );
    $action        = admin_url( 'admin-post.php' );
    
    // Get all notification messages
    $notification_messages = get_option('greenangel_notification_messages', []);
    
    // Get postcode rules
    global $wpdb;
    $table = $wpdb->prefix . 'greenangel_postcode_rules';
    $rules = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC");
    ?>
    
    <style>
        /* Tools Module - Angel Hub Dark Theme */
        .tools-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .tools-title {
            font-size: 24px;
            font-weight: 600;
            color: #aed604;
            margin: 0 0 8px 0;
        }
        
        .tools-subtitle {
            font-size: 14px;
            color: #888;
            margin: 0;
        }
        
        .tools-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .tool-card {
            background: #1a1a1a;
            border: 2px solid #333;
            border-radius: 14px;
            padding: 0;
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        
        .tool-card:hover {
            border-color: #444;
        }
        
        .tool-header {
            background: #222;
            padding: 20px;
            border-bottom: 2px solid #333;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .tool-icon {
            font-size: 24px;
        }
        
        .tool-title {
            font-size: 18px;
            font-weight: 600;
            color: #aed604;
            margin: 0;
        }
        
        .tool-content {
            padding: 25px;
        }
        
        /* Success Messages */
        .success-message {
            background: #222;
            border: 2px solid #aed604;
            color: #aed604;
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 15px;
            display: inline-block;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Angel Buttons */
        .angel-button {
            background: #aed604;
            color: #222;
            border: none;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'Poppins', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .angel-button:hover {
            background: #9bc603;
            transform: translateY(-2px);
            color: #222;
        }
        
        .angel-button:active {
            transform: translateY(0);
        }
        
        .angel-button.secondary {
            background: transparent;
            border: 2px solid #aed604;
            color: #aed604;
        }
        
        .angel-button.secondary:hover {
            background: rgba(174, 214, 4, 0.1);
            color: #aed604;
        }
        
        /* Notification Center Styles */
        .notification-center {
            margin-bottom: 30px;
        }
        
        .notification-cards {
            display: grid;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .notification-card {
            background: #0a0a0a;
            border: 2px solid #333;
            border-radius: 12px;
            padding: 15px;
            transition: all 0.2s ease;
            position: relative;
        }
        
        .notification-card:hover {
            border-color: #444;
        }
        
        .notification-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        
        .notification-card-type {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .notification-type-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .notification-type-badge.system {
            background: rgba(207, 17, 160, 0.2);
            color: #cf11a0;
        }
        
        .notification-type-badge.urgent {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }
        
        .notification-type-badge.love {
            background: rgba(236, 72, 153, 0.2);
            color: #ec4899;
        }
        
        .notification-type-badge.achievement {
            background: rgba(168, 85, 247, 0.2);
            color: #a855f7;
        }
        
        .notification-type-badge.promo {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }
        
        .notification-type-badge.reminder {
            background: rgba(245, 158, 11, 0.2);
            color: #f59e0b;
        }
        
        .notification-card-actions {
            display: flex;
            gap: 8px;
        }
        
        .notification-card-body {
            margin-bottom: 10px;
        }
        
        .notification-preview-text {
            color: #fff;
            font-size: 14px;
            line-height: 1.5;
            padding: 10px;
            background: rgba(174, 214, 4, 0.05);
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .notification-card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #666;
        }
        
        .notification-card-badge {
            background: #222;
            border: 1px solid #444;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 11px;
            color: #888;
        }
        
        /* Notification Form */
        .notification-form {
            background: rgba(174, 214, 4, 0.05);
            border: 2px solid rgba(174, 214, 4, 0.2);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .notification-type-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .type-option {
            background: #222;
            border: 2px solid #333;
            border-radius: 10px;
            padding: 15px 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .type-option:hover {
            border-color: #aed604;
            background: rgba(174, 214, 4, 0.1);
        }
        
        .type-option.selected {
            border-color: #aed604;
            background: #aed604;
            color: #000;
        }
        
        .type-option-emoji {
            font-size: 24px;
            display: block;
            margin-bottom: 5px;
        }
        
        .type-option-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        /* Editor Toolbar */
        .editor-wrapper {
            position: relative;
            margin-bottom: 20px;
        }
        
        .editor-toolbar {
            background: #0a0a0a;
            border: 2px solid #333;
            border-bottom: none;
            border-radius: 12px 12px 0 0;
            padding: 10px;
            display: flex;
            gap: 8px;
        }
        
        .format-btn {
            background: #222;
            color: #aed604;
            border: 1px solid #333;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
            min-width: 40px;
        }
        
        .format-btn:hover {
            background: #333;
            border-color: #aed604;
        }
        
        .notification-textarea {
            width: 100%;
            background: #0a0a0a;
            color: #fff;
            border: 2px solid #333;
            border-radius: 0 0 12px 12px;
            padding: 15px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            resize: vertical;
            min-height: 120px;
            transition: all 0.3s ease;
            margin: 0;
        }
        
        .notification-textarea:focus {
            outline: none;
            border-color: #aed604;
        }
        
        .toggle-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .toggle-switch {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
        }
        
        .toggle-switch input {
            display: none;
        }
        
        .toggle-slider {
            width: 50px;
            height: 26px;
            background: #333;
            border-radius: 13px;
            position: relative;
            transition: background 0.3s ease;
        }
        
        .toggle-slider::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            background: #fff;
            border-radius: 50%;
            top: 3px;
            left: 3px;
            transition: transform 0.3s ease;
        }
        
        .toggle-switch input:checked + .toggle-slider {
            background: #aed604;
        }
        
        .toggle-switch input:checked + .toggle-slider::after {
            transform: translateX(24px);
        }
        
        .toggle-label {
            color: #fff;
            font-size: 14px;
            font-weight: 500;
        }
        
        /* Badge Input */
        .badge-input-wrapper {
            margin-bottom: 15px;
        }
        
        .badge-input-wrapper label {
            color: #aed604;
            font-size: 13px;
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
        }
        
        /* Postcode Rules Styles */
        .postcode-form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            color: #aed604;
            font-size: 13px;
            font-weight: 600;
        }
        
        .angel-input {
            background: #0a0a0a;
            color: #fff;
            border: 2px solid #333;
            border-radius: 10px;
            padding: 10px 14px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .angel-input:focus {
            outline: none;
            border-color: #aed604;
        }
        
        /* Desktop Grid Layout */
        .tools-grid {
            display: block;
        }
        
        @media (min-width: 1024px) {
            .tools-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
                margin-bottom: 20px;
            }
            
            .tool-card.full-width {
                grid-column: 1 / -1;
            }
        }
        
        /* RULES CARDS */
        .rules-cards {
            display: grid;
            gap: 20px;
            margin-top: 20px;
            margin-bottom: 20px;
            grid-template-columns: 1fr;
            row-gap: 20px;
        }
        
        @media (min-width: 768px) {
            .rules-cards {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
        }
        
        .rule-card {
            background: #0a0a0a;
            border: 2px solid #333;
            border-radius: 12px;
            padding: 15px;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            align-self: start;
        }
        
        .rule-card:hover {
            border-color: #444;
        }
        
        .rule-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .rule-card-body {
            display: grid;
            gap: 8px;
            font-size: 13px;
            flex: 1;
        }
        
        .rule-card-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .rule-card-label {
            color: #666;
            font-weight: 500;
        }
        
        .rule-card-value {
            color: #fff;
            text-align: right;
        }
        
        .rule-card-message {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #333;
            min-height: 80px;
            max-height: 80px;
            overflow-y: auto;
        }
        
        .rule-card-message .rule-card-label {
            display: block;
            margin-bottom: 4px;
        }
        
        .rule-card-message .rule-card-value {
            text-align: left;
            line-height: 1.4;
            color: #ccc;
        }
        
        .rule-card-actions {
            display: flex;
            gap: 8px;
            margin-top: auto;
            padding-top: 12px;
            border-top: 1px solid #333;
        }
        
        .rule-card-actions .angel-button {
            padding: 8px 16px !important;
            font-size: 13px !important;
        }
        
        .rule-type-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .rule-type-badge.block {
            background: rgba(255, 107, 107, 0.2);
            color: #ff6b6b;
        }
        
        .rule-type-badge.minimum_spend {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }
        
        .postcode-prefix {
            font-weight: 700;
            color: #aed604;
            font-family: monospace;
            font-size: 15px;
        }
        
        /* Code Preview */
        .code-preview {
            width: 100%;
            height: 300px;
            background: #0a0a0a;
            color: #aed604;
            border: 2px solid #333;
            border-radius: 10px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
            resize: vertical;
            overflow: auto;
        }
        
        /* Scrollbar Styling */
        textarea::-webkit-scrollbar,
        .code-preview::-webkit-scrollbar {
            width: 10px;
        }
        
        textarea::-webkit-scrollbar-track,
        .code-preview::-webkit-scrollbar-track {
            background: #0a0a0a;
            border-radius: 5px;
        }
        
        textarea::-webkit-scrollbar-thumb,
        .code-preview::-webkit-scrollbar-thumb {
            background: #333;
            border-radius: 5px;
        }
        
        textarea::-webkit-scrollbar-thumb:hover,
        .code-preview::-webkit-scrollbar-thumb:hover {
            background: #444;
        }
        
        /* Firefox Scrollbar */
        textarea,
        .code-preview {
            scrollbar-width: thin;
            scrollbar-color: #333 #0a0a0a;
        }
        
        /* File Upload */
        .login-manager-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        @media (min-width: 768px) {
            .login-manager-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
        }
        
        @media (min-width: 1024px) {
            .login-manager-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        .login-manager-grid form {
            display: contents;
        }
        
        .login-manager-grid .angel-button,
        .login-manager-grid .file-input-label {
            width: 100%;
            text-align: center;
            justify-content: center;
            box-sizing: border-box;
        }
        
        .file-input-wrapper input[type="file"] {
            position: absolute;
            left: -9999px;
        }
        
        .file-input-label {
            background: transparent;
            border: 2px solid #333;
            color: #aed604;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-block;
            font-size: 14px;
            font-weight: 500;
        }
        
        .file-input-label:hover {
            border-color: #aed604;
            background: rgba(174, 214, 4, 0.05);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .tools-container {
                padding: 0 10px;
            }
            
            .tool-header {
                padding: 15px;
            }
            
            .tool-content {
                padding: 20px 15px;
            }
            
            .postcode-form-grid {
                grid-template-columns: 1fr;
            }
            
            .toggle-wrapper {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .rule-card-actions {
                flex-direction: row;
                gap: 8px;
            }
            
            .rule-card-actions .angel-button {
                width: auto;
                flex: 1;
                padding: 8px 12px !important;
                font-size: 12px !important;
            }
            
            .rule-card-actions .angel-button:last-child {
                flex: 0 0 auto;
            }
        }
        
        /* Utility Classes */
        .text-muted {
            color: #888;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .divider {
            height: 1px;
            background: #333;
            margin: 25px 0;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 15px;
        }
    </style>
    
    <div class="tools-header">
        <h2 class="tools-title">🛠️ Dev Tools & Utilities</h2>
        <p class="tools-subtitle">Manage notifications, postcode rules, and system files</p>
    </div>
    
    <div class="tools-container">
        
        <!-- 🔔 NOTIFICATION CENTER - FULL WIDTH -->
        <div class="tool-card full-width">
            <div class="tool-header">
                <span class="tool-icon">🔔</span>
                <h3 class="tool-title">Notification Center</h3>
            </div>
            <div class="tool-content">
                <?php if (isset($_GET['notification'])): ?>
                    <?php
                    $messages = [
                        'saved' => '✅ Notification saved successfully!',
                        'deleted' => '🗑️ Notification deleted.',
                        'toggled' => '✨ Notification status updated!'
                    ];
                    if (isset($messages[$_GET['notification']])):
                    ?>
                        <div class="success-message"><?php echo $messages[$_GET['notification']]; ?></div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- CREATE/EDIT NOTIFICATION FORM -->
                <div class="notification-form">
                    <h4 style="color: #aed604; margin-top: 0; margin-bottom: 20px;">📝 Create New Notification</h4>
                    
                    <form method="post" action="<?php echo esc_url($action); ?>" id="notification-form">
                        <input type="hidden" name="action" value="greenangel_save_notification">
                        <input type="hidden" name="notification_id" id="notification_id" value="">
                        <?php wp_nonce_field('greenangel_save_notification', 'greenangel_notification_nonce'); ?>
                        
                        <!-- Message Type Selector -->
                        <div style="margin-bottom: 20px;">
                            <label style="color: #aed604; font-size: 13px; font-weight: 600; display: block; margin-bottom: 10px;">Message Type</label>
                            <div class="notification-type-selector">
                                <?php
                                $types = [
                                    'system' => ['emoji' => '📢', 'label' => 'System'],
                                    'urgent' => ['emoji' => '🚨', 'label' => 'Urgent'],
                                    'love' => ['emoji' => '💚', 'label' => 'Love'],
                                    'achievement' => ['emoji' => '🏆', 'label' => 'Achievement'],
                                    'promo' => ['emoji' => '🌟', 'label' => 'Promo'],
                                    'reminder' => ['emoji' => '⏰', 'label' => 'Reminder']
                                ];
                                foreach ($types as $type => $info):
                                ?>
                                <div class="type-option" onclick="selectType('<?php echo $type; ?>')" data-type="<?php echo $type; ?>">
                                    <span class="type-option-emoji"><?php echo $info['emoji']; ?></span>
                                    <span class="type-option-label"><?php echo $info['label']; ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="notification_type" id="notification_type" value="system">
                        </div>
                        
                        <!-- Badge Input -->
                        <div class="badge-input-wrapper">
                            <label>Badge Text (Optional)</label>
                            <input type="text" name="notification_badge" id="notification_badge" 
                                   placeholder="e.g. Limited, Important, New" 
                                   class="angel-input" style="max-width: 300px;">
                        </div>
                        
                        <!-- Message Editor -->
                        <div class="editor-wrapper">
                            <div class="editor-toolbar">
                                <button type="button" class="format-btn" onclick="formatText('bold')" title="Bold">
                                    <strong>B</strong>
                                </button>
                                <button type="button" class="format-btn" onclick="formatText('italic')" title="Italic">
                                    <em>I</em>
                                </button>
                                <button type="button" class="format-btn" onclick="insertBreak()" title="Line Break">
                                    ↵
                                </button>
                            </div>
                            
                            <textarea 
                                name="notification_text" 
                                id="notification_textarea"
                                class="notification-textarea" 
                                placeholder="Write your message to your angels... Be personal, be real, be you 💚"
                                required></textarea>
                        </div>
                        
                        <div class="toggle-wrapper">
                            <label class="toggle-switch">
                                <input type="checkbox" name="notification_active" checked>
                                <span class="toggle-slider"></span>
                                <span class="toggle-label">Activate immediately</span>
                            </label>
                            
                            <div class="button-group">
                                <button type="submit" class="angel-button" id="submit-notification-btn">
                                    💚 Create Notification
                                </button>
                                <button type="button" class="angel-button secondary" id="cancel-notification-edit" 
                                        style="display:none;" onclick="cancelNotificationEdit()">
                                    Cancel Edit
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- ACTIVE NOTIFICATIONS -->
                <?php if (!empty($notification_messages)): ?>
                <h4 style="color: #aed604; margin-bottom: 15px;">📨 Active Notifications (<?php echo count($notification_messages); ?> total)</h4>
                <div class="notification-cards">
                    <?php 
                    $active_count = 0;
                    foreach ($notification_messages as $msg_id => $message): 
                        if (isset($message['active']) && $message['active'] === 'yes') {
                            $active_count++;
                        }
                    ?>
                    <div class="notification-card">
                        <div class="notification-card-header">
                            <div class="notification-card-type">
                                <span class="notification-type-badge <?php echo esc_attr($message['type'] ?? 'system'); ?>">
                                    <?php echo ucfirst($message['type'] ?? 'system'); ?>
                                </span>
                                <?php if (!empty($message['badge'])): ?>
                                <span class="notification-card-badge"><?php echo esc_html($message['badge']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="notification-card-actions">
                                <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=greenangel_toggle_notification&id=' . $msg_id), 'greenangel_toggle_notification'); ?>" 
                                   class="angel-button secondary" style="padding: 6px 12px !important; font-size: 12px !important;">
                                    <?php echo (isset($message['active']) && $message['active'] === 'yes') ? '✅' : '❌'; ?>
                                </a>
                                <button class="angel-button secondary" 
                                        style="padding: 6px 12px !important; font-size: 12px !important;"
                                        onclick='editNotification(<?php echo json_encode([
                                            "id" => $msg_id,
                                            "type" => $message['type'] ?? 'system',
                                            "text" => $message['text'],
                                            "badge" => $message['badge'] ?? '',
                                            "active" => $message['active'] ?? 'no'
                                        ]); ?>)'>✏️</button>
                                <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=greenangel_delete_notification&id=' . $msg_id), 'greenangel_delete_notification'); ?>" 
                                   class="angel-button secondary" 
                                   style="padding: 6px 12px !important; font-size: 12px !important;"
                                   onclick="return confirm('Delete this notification?');">🗑️</a>
                            </div>
                        </div>
                        <div class="notification-card-body">
                            <div class="notification-preview-text">
                                <?php echo $message['text']; ?>
                            </div>
                        </div>
                        <div class="notification-card-meta">
                            <span>Created: <?php echo date('M j, Y', $message['created_at'] ?? time()); ?></span>
                            <span><?php echo (isset($message['active']) && $message['active'] === 'yes') ? '🟢 Active' : '🔴 Inactive'; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <p style="color: #888; font-size: 12px; margin-top: 10px;">
                    📊 <?php echo $active_count; ?> active notification<?php echo $active_count !== 1 ? 's' : ''; ?> showing to customers
                </p>
                <?php else: ?>
                <div class="empty-state">
                    <p>No notifications created yet. Create your first message above! 💌</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Desktop Grid for postcode rules -->
        <div class="tools-grid">
            <!-- Postcode Restrictions -->
            <div class="tool-card">
                <div class="tool-header">
                    <span class="tool-icon">📮</span>
                    <h3 class="tool-title">Postcode Restrictions</h3>
                </div>
                <div class="tool-content">
                    <?php if (isset($_GET['postcode'])): ?>
                        <?php
                        $messages = [
                            'added' => '✅ Postcode rule added successfully!',
                            'updated' => '✏️ Rule updated successfully!',
                            'deleted' => '🗑️ Rule deleted.',
                            'toggled' => '✨ Rule status updated!'
                        ];
                        if (isset($messages[$_GET['postcode']])):
                        ?>
                            <div class="success-message"><?php echo $messages[$_GET['postcode']]; ?></div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <form method="post" action="<?php echo esc_url($action); ?>" id="postcode-form">
                        <input type="hidden" name="action" value="greenangel_save_postcode_rule">
                        <input type="hidden" name="rule_id" id="rule_id" value="0">
                        <?php wp_nonce_field('greenangel_save_postcode_rule'); ?>
                        
                        <div class="postcode-form-grid">
                            <div class="form-group">
                                <label>Rule Type</label>
                                <select name="rule_type" id="rule_type" required class="angel-input">
                                    <option value="block">Block Delivery</option>
                                    <option value="minimum_spend">Minimum Spend</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Postcode Prefix</label>
                                <input type="text" name="postcode_prefix" id="postcode_prefix" required placeholder="e.g. BT or NG9" class="angel-input">
                            </div>
                            
                            <div class="form-group">
                                <label>Min Spend (£)</label>
                                <input type="number" step="0.01" name="min_value" id="min_value" placeholder="0.00" class="angel-input">
                            </div>
                            
                            <div class="form-group full-width">
                                <label>Error Message</label>
                                <input type="text" name="error_message" id="error_message" required placeholder="Message shown to customer at checkout" class="angel-input">
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <button type="submit" class="angel-button" id="submit-btn">
                                ➕ Add Rule
                            </button>
                            <button type="button" class="angel-button secondary" id="cancel-edit" style="display:none;" onclick="cancelEdit()">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Postcode Rules Display -->
        <?php if (!empty($rules)): ?>
            <div class="tool-card full-width">
                <div class="tool-header">
                    <span class="tool-icon">📋</span>
                    <h3 class="tool-title">Active Postcode Rules</h3>
                </div>
                <div class="tool-content">
                    <div class="rules-cards">
                        <?php foreach ($rules as $rule): ?>
                            <div class="rule-card">
                                <div class="rule-card-header">
                                    <span class="rule-type-badge <?php echo esc_attr($rule->type); ?>">
                                        <?php echo $rule->type === 'block' ? '🚫 Block' : '💰 Min Spend'; ?>
                                    </span>
                                    <span class="postcode-prefix"><?php echo esc_html($rule->postcode_prefix); ?></span>
                                </div>
                                <div class="rule-card-body">
                                    <div class="rule-card-row">
                                        <span class="rule-card-label">Min Value:</span>
                                        <span class="rule-card-value">£<?php echo number_format($rule->value, 2); ?></span>
                                    </div>
                                    <div class="rule-card-row">
                                        <span class="rule-card-label">Status:</span>
                                        <span class="rule-card-value"><?php echo $rule->active === 'yes' ? '✅ Active' : '❌ Inactive'; ?></span>
                                    </div>
                                    <div class="rule-card-message">
                                        <span class="rule-card-label">Message:</span>
                                        <div class="rule-card-value">
                                            <?php echo esc_html($rule->message); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="rule-card-actions">
                                    <button class="angel-button secondary" style="flex: 1;" onclick='editRule(<?php echo json_encode([
                                        "id" => $rule->id,
                                        "type" => $rule->type,
                                        "prefix" => $rule->postcode_prefix,
                                        "value" => $rule->value,
                                        "message" => $rule->message
                                    ]); ?>)'>✏️ Edit</button>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=greenangel_toggle_postcode_rule&id=' . $rule->id), 'greenangel_toggle_postcode_rule'); ?>" 
                                       class="angel-button secondary" style="flex: 1; text-align: center;">
                                        <?php echo $rule->active === 'yes' ? '🔄 Toggle' : '✅ Enable'; ?>
                                    </a>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=greenangel_delete_postcode_rule&id=' . $rule->id), 'greenangel_delete_postcode_rule'); ?>" 
                                       class="angel-button secondary" style="flex: 0 0 auto;"
                                       onclick="return confirm('Delete this rule?');">🗑️</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="height: 20px;"></div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Login Form Manager - Full Width at Bottom -->
        <div class="tool-card full-width">
            <div class="tool-header">
                <span class="tool-icon">🔐</span>
                <h3 class="tool-title">Login Form Manager</h3>
            </div>
            <div class="tool-content">
                <?php if (isset($_GET['restore'])): ?>
                    <?php if ($_GET['restore'] === 'success'): ?>
                        <div class="success-message">✅ Login form restored successfully!</div>
                    <?php else: ?>
                        <div class="success-message">❌ Restore failed. Please check file permissions.</div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if (isset($_GET['upload'])): ?>
                    <?php
                    $upload_messages = [
                        'success' => '✅ File uploaded and deployed!',
                        'fail' => '❌ Upload failed. Please try again.',
                        'invalid' => '⚠️ Invalid file type. Only PHP files allowed.'
                    ];
                    if (isset($upload_messages[$_GET['upload']])):
                    ?>
                        <div class="success-message"><?php echo $upload_messages[$_GET['upload']]; ?></div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    <form method="post" action="<?php echo esc_url($action); ?>">
                        <input type="hidden" name="action" value="greenangel_restore_login_form">
                        <?php wp_nonce_field('greenangel_restore_login_form', 'greenangel_nonce'); ?>
                        <button type="submit" class="angel-button" style="width: 100%;">
                            🔄 Restore Default Form
                        </button>
                    </form>
                    
                    <a href="<?php echo esc_url($action . '?action=greenangel_download_login_form'); ?>" class="angel-button secondary" style="text-align: center;">
                        📥 Download Current
                    </a>
                    
                    <form method="post" action="<?php echo esc_url($action); ?>" enctype="multipart/form-data" style="display: contents;">
                        <input type="hidden" name="action" value="greenangel_upload_login_form">
                        <?php wp_nonce_field('greenangel_upload_login_form', 'greenangel_nonce_upload'); ?>
                        
                        <div class="file-input-wrapper">
                            <input type="file" name="login_file" id="login_file" accept=".php" required onchange="updateFileName(this)">
                            <label for="login_file" class="file-input-label" style="width: 100%; text-align: center; margin: 0;">
                                📁 Choose File
                            </label>
                        </div>
                        
                        <button type="submit" class="angel-button">
                            🚀 Deploy New Version
                        </button>
                    </form>
                </div>
                
                <span id="file-name" style="color: #888; font-size: 13px; text-align: center; display: block; margin-bottom: 15px;"></span>
                
                <?php if ($last_restored): ?>
                    <p class="text-muted" style="text-align: center; margin-bottom: 20px;">✨ Last restored: <?php echo esc_html($last_restored); ?></p>
                <?php endif; ?>
                
                <div class="divider"></div>
                
                <h4 style="color: #aed604; margin-bottom: 15px;">👁️ Current form-login.php</h4>
                <textarea class="code-preview" readonly><?php echo esc_textarea($code_contents); ?></textarea>
                
                <button type="button" class="angel-button secondary" style="margin-top: 15px;" onclick="copyCode()">
                    📋 Copy Code
                </button>
            </div>
        </div>
        
    </div>
    
    <script>
    // 🔔 Notification Type Selection
    function selectType(type) {
        document.getElementById('notification_type').value = type;
        
        // Update visual selection
        document.querySelectorAll('.type-option').forEach(option => {
            option.classList.remove('selected');
        });
        document.querySelector(`[data-type="${type}"]`).classList.add('selected');
    }
    
    // Edit Notification
    function editNotification(notification) {
        document.getElementById('notification_id').value = notification.id;
        document.getElementById('notification_type').value = notification.type;
        document.getElementById('notification_textarea').value = notification.text;
        document.getElementById('notification_badge').value = notification.badge || '';
        
        // Update type selection
        selectType(notification.type);
        
        // Update active checkbox
        document.querySelector('input[name="notification_active"]').checked = notification.active === 'yes';
        
        // Update button text
        document.getElementById('submit-notification-btn').innerHTML = '💾 Update Notification';
        document.getElementById('cancel-notification-edit').style.display = 'inline-flex';
        
        // Scroll to form
        document.getElementById('notification-form').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    function cancelNotificationEdit() {
        document.getElementById('notification-form').reset();
        document.getElementById('notification_id').value = '';
        document.getElementById('submit-notification-btn').innerHTML = '💚 Create Notification';
        document.getElementById('cancel-notification-edit').style.display = 'none';
        selectType('system');
    }
    
    // Notification Functions
    function formatText(command) {
        const textarea = document.getElementById('notification_textarea');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = textarea.value.substring(start, end);
        
        if (selectedText) {
            let formattedText;
            if (command === 'bold') {
                formattedText = '<strong>' + selectedText + '</strong>';
            } else if (command === 'italic') {
                formattedText = '<em>' + selectedText + '</em>';
            }
            
            textarea.value = textarea.value.substring(0, start) + formattedText + textarea.value.substring(end);
        }
    }
    
    function insertBreak() {
        const textarea = document.getElementById('notification_textarea');
        const start = textarea.selectionStart;
        textarea.value = textarea.value.substring(0, start) + '<br>' + textarea.value.substring(start);
        textarea.selectionStart = textarea.selectionEnd = start + 4;
        textarea.focus();
    }
    
    // Postcode Rule Functions
    function editRule(rule) {
        document.getElementById('rule_id').value = rule.id;
        document.getElementById('rule_type').value = rule.type;
        document.getElementById('postcode_prefix').value = rule.prefix;
        document.getElementById('min_value').value = rule.value;
        document.getElementById('error_message').value = rule.message;
        
        document.getElementById('submit-btn').innerHTML = '💾 Update Rule';
        document.getElementById('cancel-edit').style.display = 'inline-flex';
        
        document.getElementById('postcode-form').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        
        // Highlight form
        const form = document.getElementById('postcode-form').closest('.tool-card');
        form.style.borderColor = '#aed604';
        setTimeout(() => {
            form.style.borderColor = '';
        }, 2000);
    }
    
    function cancelEdit() {
        document.getElementById('postcode-form').reset();
        document.getElementById('rule_id').value = '0';
        document.getElementById('submit-btn').innerHTML = '➕ Add Rule';
        document.getElementById('cancel-edit').style.display = 'none';
    }
    
    // File Upload
    function updateFileName(input) {
        const fileName = input.files[0]?.name || '';
        document.getElementById('file-name').textContent = fileName;
    }
    
    // Copy Code
    function copyCode() {
        const codeArea = document.querySelector('.code-preview');
        codeArea.select();
        document.execCommand('copy');
        
        // Visual feedback
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '✅ Copied!';
        setTimeout(() => {
            button.innerHTML = originalText;
        }, 2000);
    }
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Select default type
        selectType('system');
    });
    </script>
    
    <?php
}
?>