<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

// Include wallet order handler
require_once plugin_dir_path(__FILE__) . '/includes/wallet-order-handler.php';
require_once plugin_dir_path(__FILE__) . '/gateway.php';
require_once plugin_dir_path(__FILE__) . '/includes/wallet-cart-validation.php';
require_once plugin_dir_path(__FILE__) . '/includes/wallet-coupon-converter.php';
require_once plugin_dir_path(__FILE__) . '/includes/wallet-shipping.php';
require_once plugin_dir_path(__FILE__) . '/includes/wallet-security.php';

/**
 * Angel Wallet Core Functions
 * Handles balance updates, transaction logs, and table creation
 */

/**
 * Create table for logging wallet transactions
 */
function greenangel_create_wallet_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'angel_wallet_transactions';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        order_id BIGINT UNSIGNED DEFAULT NULL,
        amount DECIMAL(10,2) NOT NULL,
        type ENUM('topup', 'spend', 'manual') NOT NULL,
        comment TEXT NULL,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

/**
 * Initialize wallet table on plugin load
 */
add_action('plugins_loaded', function() {
    if (!get_option('greenangel_wallet_table_created')) {
        greenangel_create_wallet_table();
        update_option('greenangel_wallet_table_created', true);
    }
});

/**
 * Log a wallet transaction
 */
function greenangel_log_wallet_transaction($user_id, $amount, $type = 'manual', $order_id = null, $comment = '') {
    global $wpdb;
    
    // Basic validation
    $user_id = intval($user_id);
    $amount = floatval($amount);
    $type = sanitize_text_field($type);
    $order_id = $order_id ? intval($order_id) : null;
    $comment = sanitize_textarea_field($comment);
    
    // Validate user exists
    if (!$user_id || !get_userdata($user_id)) {
        return false;
    }
    
    // Validate transaction type
    if (!in_array($type, ['topup', 'spend', 'manual'], true)) {
        return false;
    }
    
    $result = $wpdb->insert(
        $wpdb->prefix . 'angel_wallet_transactions',
        [
            'user_id' => $user_id,
            'order_id' => $order_id,
            'amount' => $amount,
            'type' => $type,
            'comment' => $comment,
            'timestamp' => current_time('mysql'),
        ],
        ['%d', '%d', '%f', '%s', '%s', '%s']
    );
    
    return $result !== false;
}

/**
 * Get current wallet balance for user
 */
function greenangel_get_wallet_balance($user_id = null) {
    if (!$user_id) $user_id = get_current_user_id();
    
    $user_id = intval($user_id);
    if (!$user_id) {
        return 0.0;
    }
    
    return (float) get_user_meta($user_id, 'angel_wallet_balance', true);
}

/**
 * Set wallet balance
 */
function greenangel_set_wallet_balance($user_id, $amount) {
    $user_id = intval($user_id);
    $amount = floatval($amount);
    
    if (!$user_id) {
        return false;
    }
    
    // Prevent negative balances
    if ($amount < 0) {
        $amount = 0;
    }
    
    return update_user_meta($user_id, 'angel_wallet_balance', $amount);
}

/**
 * Add funds to wallet
 */
function greenangel_add_to_wallet($user_id, $amount, $comment = '', $type = 'topup') {
    $user_id = intval($user_id);
    $amount = floatval($amount);
    $comment = sanitize_textarea_field($comment);
    $type = sanitize_text_field($type);
    
    // Validate transaction type
    if (!in_array($type, ['topup', 'manual'], true)) {
        $type = 'topup';
    }
    
    if (!$user_id || $amount <= 0) {
        return false;
    }
    
    $current = greenangel_get_wallet_balance($user_id);
    $new_balance = $current + $amount;
    
    // Prevent excessive balances
    if ($new_balance > 50000) {
        return false;
    }
    
    if (greenangel_set_wallet_balance($user_id, $new_balance)) {
        greenangel_log_wallet_transaction($user_id, $amount, $type, null, $comment);
        return $new_balance;
    }
    
    return false;
}

/**
 * Deduct from wallet
 */
function greenangel_deduct_from_wallet($user_id, $amount, $order_id = null, $comment = '', $type = 'spend') {
    $user_id = intval($user_id);
    $amount = floatval($amount);
    $order_id = $order_id ? intval($order_id) : null;
    $comment = sanitize_textarea_field($comment);
    $type = sanitize_text_field($type);
    
    // Validate transaction type
    if (!in_array($type, ['spend', 'manual'], true)) {
        $type = 'spend';
    }
    
    if (!$user_id || $amount <= 0) {
        return false;
    }
    
    $current = greenangel_get_wallet_balance($user_id);
    
    // Check for sufficient balance
    if ($current < $amount) {
        return false;
    }
    
    $new_balance = $current - $amount;
    
    if (greenangel_set_wallet_balance($user_id, $new_balance)) {
        greenangel_log_wallet_transaction($user_id, -$amount, $type, $order_id, $comment);
        return $new_balance;
    }
    
    return false;
}

/**
 * Get transaction history
 */
function greenangel_get_wallet_transactions($user_id, $limit = 50) {
    global $wpdb;
    
    $user_id = intval($user_id);
    $limit = intval($limit);
    
    if (!$user_id) {
        return [];
    }
    
    // Limit the number of transactions
    if ($limit > 100) {
        $limit = 100;
    }
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}angel_wallet_transactions 
         WHERE user_id = %d 
         ORDER BY timestamp DESC 
         LIMIT %d",
        $user_id,
        $limit
    ));
}

/**
 * Send wallet top-up notification email to customer
 */
function greenangel_send_wallet_topup_email($user_id, $amount, $comment = '') {
    // Input validation
    $user_id = absint($user_id);
    $amount = floatval($amount);
    $comment = sanitize_textarea_field($comment);
    
    if (!$user_id || $amount <= 0) {
        return false;
    }
    
    $user = get_userdata($user_id);
    if (!$user) {
        return false;
    }
    
    $current_balance = greenangel_get_wallet_balance($user_id);
    $site_name = sanitize_text_field(get_bloginfo('name'));
    
    // Email subject - keep it fun and on-brand (properly escaped)
    $subject = sprintf('💰 You\'ve got £%s babe! | %s', number_format($amount, 2), esc_html($site_name));
    
    // Create the email content in your style
    $message = sprintf("
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif; margin: 0; padding: 0; background: #0a0a0a; color: #fff; }
        .container { max-width: 600px; margin: 0 auto; background: linear-gradient(135deg, #1a1a1a, #222); border-radius: 20px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #aed604, #8bc34a); padding: 40px 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; color: #1a1a1a; font-weight: 700; }
        .content { padding: 40px 30px; }
        .balance-display { background: #111; border: 2px solid #aed604; border-radius: 16px; padding: 30px; text-align: center; margin: 20px 0; }
        .balance-amount { font-size: 42px; font-weight: 800; color: #aed604; margin: 10px 0; }
        .balance-label { color: #888; font-size: 16px; margin-bottom: 10px; }
        .message-text { font-size: 18px; line-height: 1.6; color: #ccc; margin: 20px 0; }
        .highlight { color: #aed604; font-weight: 600; }
        .footer { background: #111; padding: 30px; text-align: center; color: #666; font-size: 14px; }
        .button { display: inline-block; background: linear-gradient(135deg, #aed604, #8bc34a); color: #1a1a1a; padding: 16px 32px; border-radius: 25px; text-decoration: none; font-weight: 700; margin: 20px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>👼 You've got £%s babe!</h1>
        </div>
        
        <div class='content'>
            <p class='message-text'>
                Hey <span class='highlight'>%s</span>! 🎉
            </p>
            
            <p class='message-text'>
                Your Angel Wallet has just been topped up with <span class='highlight'>£%s</span> and is now ready to be spent! 
                Time to treat yourself to something special ✨
            </p>
            
            <div class='balance-display'>
                <div class='balance-label'>Your Current Balance</div>
                <div class='balance-amount'>£%s</div>
            </div>
            
            %s
            
            <p class='message-text'>
                Ready to spend? Your wallet balance is waiting for you in your account dashboard. 
                Go forth and shop, beautiful! 💫
            </p>
            
            <div style='text-align: center;'>
                <a href='%s' class='button'>💍 Start Shopping</a>
            </div>
        </div>
        
        <div class='footer'>
            <p>This email was sent because your Angel Wallet balance was updated.</p>
            <p>© %s - Spreading angel vibes since forever 🪽</p>
        </div>
    </div>
</body>
</html>
    ",
        number_format($amount, 2),
        esc_html($user->first_name ?: $user->display_name),
        number_format($amount, 2),
        number_format($current_balance, 2),
        $comment ? sprintf('<p class="message-text"><strong>Note:</strong> %s</p>', esc_html($comment)) : '',
        esc_url('https://greenangelshop.com'),
        esc_html(date('Y'))
    );
    
    // Set up email headers for HTML (properly sanitized)
    $admin_email = sanitize_email(get_option('admin_email'));
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        sprintf('From: %s <%s>', esc_html($site_name), $admin_email)
    );
    
    // Send the email
    $sent = wp_mail($user->user_email, $subject, $message, $headers);
    
    // Log the email attempt for monitoring
    if ($sent) {
        error_log(sprintf('Green Angel: Wallet top-up email sent to %s for £%s', $user->user_email, number_format($amount, 2)));
    } else {
        error_log(sprintf('Green Angel: Failed to send wallet top-up email to %s', $user->user_email));
    }
    
    return $sent;
}

/**
 * Frontend Angel Wallet Console Shortcode
 */
add_shortcode('greenangel_wallet_console', 'greenangel_wallet_console_shortcode');
function greenangel_wallet_console_shortcode() {
    if (!is_user_logged_in()) {
        return '<div class="angel-login-prompt">
            <div class="angel-login-icon">👼</div>
            <h3>Welcome, Future Angel!</h3>
            <p>Please <a href="' . esc_url(wp_login_url(get_permalink())) . '" class="angel-login-link">sign in</a> to access your magical wallet</p>
        </div>';
    }
    
    $user_id = get_current_user_id();
    $user = wp_get_current_user();
    $balance = greenangel_get_wallet_balance($user_id);
    $logs = greenangel_get_wallet_transactions($user_id, 8);
    
    // Get user initials for avatar
    $name_parts = explode(' ', $user->display_name);
    $initials = '';
    foreach ($name_parts as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    $initials = substr($initials, 0, 2) ?: '👤';
    
    ob_start();
    ?>
    
    <style>
    /* Angel Wallet Console Styles - Mobile First */
    .angel-wallet-console {
        max-width: 100%;
        margin: 0;
        padding: 20px;
        background: #0a0a0a;
        min-height: 100vh;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        color: #fff;
        position: relative;
    }
    
    @media (min-width: 768px) {
        .angel-wallet-console {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0a0a0a 100%);
            padding: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    }
    
    .angel-console-wrapper {
        width: 100%;
        max-width: 100%;
    }
    
    @media (min-width: 768px) {
        .angel-console-wrapper {
            max-width: 900px;
            background: linear-gradient(135deg, #1a1a1a 0%, #222 100%);
            border-radius: 32px;
            border: 2px solid #333;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8);
            position: relative;
            overflow: hidden;
        }
        
        .angel-console-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: radial-gradient(circle at 2px 2px, rgba(174, 214, 4, 0.03) 1px, transparent 0);
            background-size: 32px 32px;
            pointer-events: none;
        }
    }
    
    .angel-console-content {
        position: relative;
        z-index: 1;
        width: 100%;
    }
    
    @media (min-width: 768px) {
        .angel-console-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: start;
        }
        
        .angel-left-column {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        
        .angel-right-column {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
    }
    
    /* Header Section */
    .angel-header {
        text-align: center;
        margin-bottom: 32px;
        padding: 0 16px;
    }
    
    .angel-back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #aed604;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 24px;
        padding: 12px 20px;
        border: 2px solid #333;
        border-radius: 25px;
        background: #1a1a1a;
        transition: all 0.3s ease;
    }
    
    .angel-back-btn:hover {
        background: #222;
        border-color: #aed604;
        color: #aed604;
        transform: translateY(-2px);
        text-decoration: none;
    }
    
    .angel-welcome {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
        margin-bottom: 16px;
    }
    
    .angel-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, #aed604, #8bc34a);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: bold;
        color: #1a1a1a;
        box-shadow: 0 8px 32px rgba(174, 214, 4, 0.2);
    }
    
    .angel-user-info h2 {
        margin: 0 0 4px;
        font-size: 24px;
        font-weight: 700;
        color: #fff;
    }
    
    .angel-user-info p {
        margin: 0;
        color: #aed604;
        font-size: 14px;
        font-weight: 500;
    }
    
    /* Balance Card */
    .angel-balance-card {
        background: linear-gradient(135deg, #1a1a1a 0%, #222 100%);
        border: 2px solid #333;
        border-radius: 24px;
        padding: 32px 24px;
        margin-bottom: 24px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    
    .angel-balance-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #aed604, #8bc34a, #aed604);
    }
    
    .balance-label {
        font-size: 14px;
        color: #888;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }
    
    .balance-amount {
        font-size: 48px;
        font-weight: 900;
        color: #aed604;
        margin-bottom: 8px;
        text-shadow: 0 2px 8px rgba(174, 214, 4, 0.3);
        line-height: 1;
    }
    
    .balance-subtitle {
        font-size: 14px;
        color: #666;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    /* Quick Stats */
    .angel-stats {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
        margin-bottom: 32px;
    }
    
    .stat-item {
        background: #1a1a1a;
        border: 1px solid #333;
        border-radius: 16px;
        padding: 20px 16px;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .stat-item:hover {
        border-color: #444;
        transform: translateY(-2px);
    }
    
    .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: #aed604;
        margin-bottom: 4px;
    }
    
    .stat-label {
        font-size: 12px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    
    /* Top-up Section */
    .angel-topup-section {
        margin-bottom: 32px;
    }
    
    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .topup-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 16px;
    }
    
    .topup-option {
        background: linear-gradient(135deg, #1a1a1a, #222);
        border: 2px solid #333;
        border-radius: 16px;
        padding: 20px 16px;
        text-align: center;
        text-decoration: none;
        color: #fff;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        display: block;
    }
    
    .topup-option::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(174, 214, 4, 0.1), transparent);
        transition: left 0.5s ease;
    }
    
    .topup-option:hover {
        border-color: #aed604;
        transform: translateY(-4px);
        box-shadow: 0 8px 32px rgba(174, 214, 4, 0.2);
        text-decoration: none;
        color: #fff;
    }
    
    .topup-option:hover::before {
        left: 100%;
    }
    
    .topup-content {
        margin-bottom: 16px;
    }
    
    .topup-amount {
        font-size: 24px;
        font-weight: 800;
        color: #aed604;
        margin-bottom: 4px;
    }
    
    .topup-label {
        font-size: 12px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .add-to-cart-btn {
        background: linear-gradient(135deg, #aed604, #8bc34a);
        color: #1a1a1a;
        border: none;
        border-radius: 12px;
        padding: 12px 20px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .add-to-cart-btn:hover {
        background: linear-gradient(135deg, #8bc34a, #aed604);
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(174, 214, 4, 0.3);
    }
    
    .add-to-cart-btn:active {
        transform: translateY(0);
    }
    
    .topup-popular {
        background: linear-gradient(135deg, #aed604, #8bc34a);
        color: #1a1a1a !important;
        border-color: #aed604;
    }
    
    .topup-popular .topup-amount,
    .topup-popular .topup-label {
        color: #1a1a1a;
    }
    
    .topup-popular .add-to-cart-btn {
        background: linear-gradient(135deg, #1a1a1a, #333);
        color: #aed604;
    }
    
    .topup-popular .add-to-cart-btn:hover {
        background: linear-gradient(135deg, #333, #444);
        color: #fff;
    }
    
    .topup-popular::after {
        content: '⭐ Popular';
        position: absolute;
        top: 8px;
        right: 8px;
        font-size: 10px;
        background: rgba(26, 26, 26, 0.8);
        color: #aed604;
        padding: 4px 8px;
        border-radius: 12px;
        font-weight: 600;
    }
    
    /* Bottom Section */
    .angel-bottom-section {
        margin-top: 32px;
    }
    
    @media (min-width: 768px) {
        .angel-bottom-section {
            background: linear-gradient(135deg, #1a1a1a, #222);
            border-radius: 24px;
            border: 2px solid #333;
            padding: 32px;
            margin-top: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .angel-bottom-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #aed604, #8bc34a, #aed604);
        }
    }
    
    .bottom-section-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
    }
    
    @media (min-width: 768px) {
        .bottom-section-grid {
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            align-items: start;
        }
    }
    
    /* Future content placeholder */
    .angel-future-content {
        background: linear-gradient(135deg, #1a1a1a, #222);
        border: 2px dashed #333;
        border-radius: 16px;
        padding: 32px 24px;
        text-align: center;
        opacity: 0.7;
        transition: all 0.3s ease;
    }
    
    @media (min-width: 768px) {
        .angel-future-content {
            background: linear-gradient(135deg, #222, #2a2a2a);
            border: 2px dashed #444;
        }
    }
    
    .angel-future-content:hover {
        opacity: 1;
        transform: translateY(-2px);
    }
    
    .placeholder-content {
        color: #888;
    }
    
    .placeholder-icon {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.6;
    }
    
    .placeholder-content h4 {
        color: #aed604;
        margin: 0 0 8px;
        font-size: 18px;
        font-weight: 600;
    }
    
    .placeholder-content p {
        margin: 0;
        font-size: 14px;
        line-height: 1.4;
    }
    
    /* Info Bubble */
    .angel-info-bubble {
        background: linear-gradient(135deg, #1a1a1a, #222);
        border: 2px solid #333;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 32px;
    }
    
    @media (min-width: 768px) {
        .angel-bottom-section .angel-info-bubble {
            background: linear-gradient(135deg, #222, #2a2a2a);
            border: 2px solid #444;
            margin-bottom: 0;
        }
    }
    
    .mobile-only {
        display: block;
    }
    
    .desktop-only {
        display: none;
    }
    
    @media (min-width: 768px) {
        .mobile-only {
            display: none;
        }
        
        .desktop-only {
            display: block;
            margin-bottom: 0;
        }
    }
    
    .info-title {
        font-size: 18px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .info-content {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .info-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: 14px;
        color: #ccc;
        line-height: 1.4;
    }
    
    .info-icon {
        font-size: 16px;
        flex-shrink: 0;
        width: 24px;
        text-align: center;
        margin-top: 2px;
    }
    
    /* Activity Section */
    .angel-activity {
        margin-bottom: 32px;
    }
    
    .activity-list {
        background: #1a1a1a;
        border: 1px solid #333;
        border-radius: 16px;
        overflow: hidden;
        max-height: 400px;
        overflow-y: auto;
        position: relative;
    }
    
    /* Custom Scrollbar */
    .activity-list::-webkit-scrollbar {
        width: 8px;
    }
    
    .activity-list::-webkit-scrollbar-track {
        background: #0a0a0a;
        border-radius: 10px;
        margin: 8px 0;
    }
    
    .activity-list::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #aed604, #8bc34a);
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(174, 214, 4, 0.3);
        transition: all 0.2s ease;
    }
    
    .activity-list::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, #cfff00, #aed604);
        box-shadow: 0 4px 12px rgba(174, 214, 4, 0.5);
        transform: scaleX(1.2);
    }
    
    .activity-list::-webkit-scrollbar-thumb:active {
        background: linear-gradient(180deg, #aed604, #7ba336);
    }
    
    .activity-list {
        scrollbar-width: thin;
        scrollbar-color: #aed604 #0a0a0a;
    }
    
    /* Scroll fade indicators */
    .activity-list::before {
        content: '';
        position: sticky;
        top: 0;
        left: 0;
        right: 0;
        height: 20px;
        background: linear-gradient(180deg, #1a1a1a 0%, transparent 100%);
        z-index: 10;
        pointer-events: none;
    }
    
    .activity-list::after {
        content: '';
        position: sticky;
        bottom: 0;
        left: 0;
        right: 0;
        height: 20px;
        background: linear-gradient(0deg, #1a1a1a 0%, transparent 100%);
        z-index: 10;
        pointer-events: none;
    }
    
    .activity-item {
        padding: 20px;
        border-bottom: 1px solid #333;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.2s ease;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-item:hover {
        background: #222;
    }
    
    .activity-left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
    }
    
    .activity-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    
    .activity-icon.positive {
        background: rgba(76, 175, 80, 0.2);
        color: #4caf50;
    }
    
    .activity-icon.negative {
        background: rgba(244, 67, 54, 0.2);
        color: #f44336;
    }
    
    .activity-icon.neutral {
        background: rgba(174, 214, 4, 0.2);
        color: #aed604;
    }
    
    .activity-details h4 {
        margin: 0 0 4px;
        font-size: 14px;
        font-weight: 600;
        color: #fff;
    }
    
    .activity-details p {
        margin: 0;
        font-size: 12px;
        color: #888;
    }
    
    .activity-amount {
        font-size: 16px;
        font-weight: 700;
        text-align: right;
    }
    
    .activity-amount.positive {
        color: #4caf50;
    }
    
    .activity-amount.negative {
        color: #f44336;
    }
    
    .activity-date {
        font-size: 11px;
        color: #666;
        margin-top: 2px;
    }
    
    /* Empty States */
    .empty-activity {
        padding: 40px 20px;
        text-align: center;
        color: #666;
    }
    
    .empty-activity-icon {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }
    
    /* Login Prompt */
    .angel-login-prompt {
        background: linear-gradient(135deg, #1a1a1a, #222);
        border: 2px solid #333;
        border-radius: 24px;
        padding: 40px 24px;
        text-align: center;
        margin: 20px auto;
        max-width: 400px;
    }
    
    .angel-login-icon {
        font-size: 64px;
        margin-bottom: 20px;
    }
    
    .angel-login-prompt h3 {
        color: #aed604;
        margin-bottom: 12px;
        font-size: 24px;
    }
    
    .angel-login-prompt p {
        color: #888;
        margin-bottom: 0;
    }
    
    .angel-login-link {
        color: #aed604;
        text-decoration: none;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    
    .angel-login-link:hover {
        background: rgba(174, 214, 4, 0.1);
        text-decoration: none;
    }
    
    /* Mobile Responsiveness */
    @media (min-width: 480px) {
        .angel-wallet-console {
            padding: 24px;
        }
        
        .topup-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .angel-stats {
            grid-template-columns: repeat(1, 1fr);
        }
    }
    
    @media (min-width: 768px) {
        .angel-wallet-console {
            padding: 32px;
            max-width: none;
        }
        
        .angel-header {
            grid-column: 1 / -1;
            margin-bottom: 0;
        }
        
        .angel-welcome {
            gap: 20px;
        }
        
        .angel-avatar {
            width: 80px;
            height: 80px;
            font-size: 32px;
        }
        
        .angel-user-info h2 {
            font-size: 28px;
        }
        
        .balance-amount {
            font-size: 56px;
        }
        
        .topup-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        
        .angel-stats {
            margin-bottom: 0;
        }
        
        .angel-topup-section {
            margin-bottom: 0;
        }
        
        .angel-activity {
            margin-bottom: 0;
        }
    }
    
    /* Animations */
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes shimmer {
        from {
            background-position: -200% 0;
        }
        to {
            background-position: 200% 0;
        }
    }
    
    .angel-balance-card {
        animation: slideInUp 0.6s ease-out;
    }
    
    .angel-stats {
        animation: slideInUp 0.6s ease-out 0.1s both;
    }
    
    .angel-topup-section {
        animation: slideInUp 0.6s ease-out 0.2s both;
    }
    
    .angel-activity {
        animation: slideInUp 0.6s ease-out 0.3s both;
    }
    </style>
    
    <div class="angel-wallet-console">
        <div class="angel-console-wrapper">
            <div class="angel-console-content">
                <!-- Header -->
                <div class="angel-header">
                    <a href="<?php echo esc_url(wc_get_account_endpoint_url('dashboard')); ?>" class="angel-back-btn">
                        <span>←</span> Back to Dashboard
                    </a>
                    
                    <div class="angel-welcome">
                        <div class="angel-avatar"><?php echo esc_html($initials); ?></div>
                        <div class="angel-user-info">
                            <h2>Welcome, Angel!</h2>
                            <p>✨ <?php echo esc_html($user->display_name); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Left Column -->
                <div class="angel-left-column">
                    <!-- Balance Card -->
                    <div class="angel-balance-card">
                        <div class="balance-label">Your Angel Credits</div>
                        <div class="balance-amount">£<?php echo number_format($balance, 2); ?></div>
                        <div class="balance-subtitle">
                            <span>👼</span> Available Balance
                        </div>
                        <?php echo angel_get_convert_section_html($balance); ?>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="angel-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo count($logs); ?></div>
                            <div class="stat-label">Total Transactions</div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div class="angel-right-column">
                    <!-- Top-up Section -->
                    <div class="angel-topup-section">
                        <h3 class="section-title">
                            <span>💳</span> Quick Top-up
                        </h3>
                        
                        <div class="topup-grid">
                            <?php 
                            $topup_titles = [
                                50 => ['label' => 'Starter'],
                                100 => ['label' => 'Everyday'],
                                250 => ['label' => 'Popular', 'popular' => true],
                                500 => ['label' => 'Power Angel'],
                            ];
                            
                            foreach ($topup_titles as $amount => $meta) {
                                $title = "£{$amount} Top-Up";
                                $product = get_page_by_title($title, OBJECT, 'product');
                                
                                if (!$product) continue;
                                
                                $product_id = $product->ID;
                                $popular_class = isset($meta['popular']) ? 'topup-option topup-popular' : 'topup-option';
                                
                                echo '<div class="' . esc_attr($popular_class) . '">';
                                echo '<div class="topup-content">';
                                echo '<div class="topup-amount">£' . number_format($amount) . '</div>';
                                echo '<div class="topup-label">' . esc_html($meta['label']) . '</div>';
                                echo '</div>';
                                echo '<button class="add-to-cart-btn ajax-add-to-cart" data-product_id="' . esc_attr($product_id) . '" data-amount="' . esc_attr($amount) . '">';
                                echo '<span>➕</span> Add to Cart';
                                echo '</button>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <!-- Angel Wallet Info Bubble - Mobile position -->
                    <div class="angel-info-bubble mobile-only">
                        <h3 class="info-title">
                            <span>💡</span> Why Angel Credits?
                        </h3>
                        <div class="info-content">
                            <div class="info-item">
                                <span class="info-icon">🛡️</span>
                                <span>Skip the crypto chaos - load up and chill</span>
                            </div>
                            <div class="info-item">
                                <span class="info-icon">⚡</span>
                                <span>Get your meds instantly, no crypto delays</span>
                            </div>
                            <div class="info-item">
                                <span class="info-icon">💰</span>
                                <span>Occasional bonus credits for top-ups!</span>
                            </div>
                            <div class="info-item">
                                <span class="info-icon">🔒</span>
                                <span>Quick, easy & secure - the smart way</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Activity Section -->
                    <div class="angel-activity">
                        <h3 class="section-title">
                            <span>📊</span> Recent Activity
                        </h3>
                        
                        <div class="activity-list">
                            <?php if (!empty($logs)): ?>
                                <?php foreach ($logs as $log): 
                                    $is_positive = $log->amount >= 0;
                                    $icon_class = $is_positive ? 'positive' : 'negative';
                                    $icon = $is_positive ? '💰' : '🛍️';
                                    
                                    // FIXED: Smart display label detection
                                    if ($log->type === 'topup') {
                                        $type_label = 'Top-up';
                                    } elseif ($log->type === 'spend') {
                                        $type_label = 'Purchase';
                                    } elseif (strpos($log->comment, 'Converted to coupon:') !== false) {
                                        $type_label = 'Coupon Created';
                                        $icon = '🎟️';
                                        $icon_class = 'neutral';
                                    } else {
                                        $type_label = 'Adjustment';
                                    }
                                    
                                    $date = date('j M Y, g:i a', strtotime($log->timestamp));
                                ?>
                                <div class="activity-item">
                                    <div class="activity-left">
                                        <div class="activity-icon <?php echo $icon_class; ?>">
                                            <?php echo $icon; ?>
                                        </div>
                                        <div class="activity-details">
                                            <h4><?php echo esc_html($type_label); ?></h4>
                                            <p><?php echo esc_html($log->comment ?: 'Transaction processed'); ?></p>
                                        </div>
                                    </div>
                                    <div class="activity-right">
                                        <div class="activity-amount <?php echo $icon_class; ?>">
                                            <?php echo $is_positive ? '+' : '-'; ?>£<?php echo number_format(abs($log->amount), 2); ?>
                                        </div>
                                        <div class="activity-date"><?php echo esc_html($date); ?></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-activity">
                                    <div class="empty-activity-icon">📱</div>
                                    <p><strong>No activity yet!</strong></p>
                                    <p>Your transactions will appear here</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Section -->
            <div class="angel-bottom-section">
                <div class="bottom-section-grid">
                    <!-- Info bubble -->
                    <div class="angel-info-bubble">
                        <h3 class="info-title">
                            <span>💡</span> Why Angel Credits?
                        </h3>
                        <div class="info-content">
                            <div class="info-item">
                                <span class="info-icon">🛡️</span>
                                <span>Skip the crypto chaos - load up and chill</span>
                            </div>
                            <div class="info-item">
                                <span class="info-icon">⚡</span>
                                <span>Get your meds instantly, no crypto delays</span>
                            </div>
                            <div class="info-item">
                                <span class="info-icon">💰</span>
                                <span>Occasional bonus credits for top-ups!</span>
                            </div>
                            <div class="info-item">
                                <span class="info-icon">🔒</span>
                                <span>Quick, easy & secure - the smart way</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Future content placeholder -->
                    <div class="angel-future-content">
                        <div class="placeholder-content">
                            <div class="placeholder-icon">🚀</div>
                            <h4>More Features Coming</h4>
                            <p>We're cooking up something special for this space!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    /**
     * AJAX Add-to-Cart functionality for wallet console
     */
    document.addEventListener('DOMContentLoaded', function() {
        const addToCartButtons = document.querySelectorAll('.ajax-add-to-cart');
        
        // Rate limiting state
        let lastCartAction = 0;
        const RATE_LIMIT_MS = 3000; // 3 seconds between cart actions
        
        addToCartButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Client-side rate limiting
                const now = Date.now();
                if (now - lastCartAction < RATE_LIMIT_MS) {
                    this.innerHTML = '<span>⏱️</span> Please wait...';
                    setTimeout(() => {
                        this.innerHTML = this.dataset.originalText || 'Add to Cart';
                    }, 1000);
                    return;
                }
                lastCartAction = now;
                
                const productId = this.getAttribute('data-product_id');
                const amount = this.getAttribute('data-amount');
                const originalText = this.innerHTML;
                this.dataset.originalText = originalText;
                
                // Validate product ID
                if (!productId || isNaN(productId)) {
                    this.innerHTML = '<span>❌</span> Invalid';
                    setTimeout(() => {
                        this.innerHTML = originalText;
                    }, 2000);
                    return;
                }
                
                this.innerHTML = '<span>✨</span> Adding...';
                this.style.pointerEvents = 'none';
                this.style.opacity = '0.7';
                
                const formData = new FormData();
                formData.append('action', 'woocommerce_add_to_cart');
                formData.append('product_id', productId);
                formData.append('quantity', '1');
                
                // Add nonce if available
                if (window.wc_add_to_cart_params && window.wc_add_to_cart_params.wc_ajax_nonce) {
                    formData.append('_wpnonce', window.wc_add_to_cart_params.wc_ajax_nonce);
                }
                
                fetch(wc_add_to_cart_params.ajax_url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        this.innerHTML = '<span>❌</span> Error';
                        setTimeout(() => {
                            this.innerHTML = originalText;
                            this.style.pointerEvents = 'auto';
                            this.style.opacity = '1';
                        }, 2000);
                    } else {
                        this.innerHTML = '<span>🎉</span> Added!';
                        
                        document.dispatchEvent(new Event('wc_cart_updated'));
                        
                        if (typeof jQuery !== 'undefined') {
                            jQuery('body').trigger('wc_fragment_refresh');
                        }
                        
                        setTimeout(() => {
                            this.innerHTML = originalText;
                            this.style.pointerEvents = 'auto';
                            this.style.opacity = '1';
                        }, 2000);
                    }
                })
                .catch(error => {
                    this.innerHTML = '<span>❌</span> Error';
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.style.pointerEvents = 'auto';
                        this.style.opacity = '1';
                    }, 2000);
                });
            });
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}

/**
 * Override total display in WooCommerce emails for Angel Wallet orders
 */
add_filter('woocommerce_get_order_item_totals', function($totals, $order, $tax_display) {
    if ($order->get_payment_method() === 'angel_wallet') {
        $paid = $order->get_meta('_angel_wallet_paid_total');
        if ($paid) {
            $totals['order_total']['value'] = wc_price($paid) . ' <span style="color: #aed604; font-weight: 600;">(via Angel Wallet)</span>';
        }
    }
    return $totals;
}, 10, 3);

/**
 * Override My Account > Orders table total display
 */
add_filter('woocommerce_order_formatted_total', function($formatted_total, $order) {
    if ($order->get_payment_method() === 'angel_wallet') {
        $paid = $order->get_meta('_angel_wallet_paid_total');
        if ($paid) {
            return wc_price($paid) . ' <span style="color: #aed604; font-weight: 600;">(Angel Wallet)</span>';
        }
    }
    return $formatted_total;
}, 10, 2);

/**
 * WALLET BALANCE INTEGRITY CHECKS
 */

/**
 * Verify wallet balance integrity by comparing user meta with transaction log
 */
function greenangel_verify_wallet_balance_integrity($user_id) {
    global $wpdb;
    
    // Get current balance from user meta
    $current_balance = greenangel_get_wallet_balance($user_id);
    
    // Calculate balance from transaction log
    $calculated_balance = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(amount) FROM {$wpdb->prefix}angel_wallet_transactions WHERE user_id = %d",
        $user_id
    ));
    
    $calculated_balance = floatval($calculated_balance);
    
    // Check for discrepancy
    $discrepancy = abs($current_balance - $calculated_balance);
    
    if ($discrepancy > 0.01) { // Allow for minor rounding differences
        // Log the integrity issue
        greenangel_log_wallet_operation([
            'user_id' => $user_id,
            'action' => 'balance_integrity_error',
            'amount' => $discrepancy,
            'reason' => sprintf(
                'Balance mismatch: Meta=%.2f, Calculated=%.2f, Difference=%.2f',
                $current_balance,
                $calculated_balance,
                $discrepancy
            ),
            'context' => 'integrity_check'
        ]);
        
        // Send alert for significant discrepancies
        if ($discrepancy > 1.00) {
            greenangel_send_balance_integrity_alert($user_id, $current_balance, $calculated_balance, $discrepancy);
        }
        
        return false;
    }
    
    return true;
}

/**
 * Send balance integrity alert
 */
function greenangel_send_balance_integrity_alert($user_id, $current_balance, $calculated_balance, $discrepancy) {
    $user = get_userdata($user_id);
    
    if (!$user) {
        return false;
    }
    
    $site_name = get_bloginfo('name');
    $admin_email = get_option('admin_email');
    
    $subject = sprintf(
        '[%s] WALLET INTEGRITY ALERT - Balance Discrepancy Detected',
        $site_name
    );
    
    $message = sprintf(
        "WALLET BALANCE INTEGRITY ISSUE DETECTED:\n\n" .
        "User: %s (%s)\n" .
        "Current Balance (Meta): £%.2f\n" .
        "Calculated Balance (Transactions): £%.2f\n" .
        "Discrepancy: £%.2f\n" .
        "Date: %s\n\n" .
        "This requires immediate investigation and manual correction.",
        $user->display_name,
        $user->user_email,
        $current_balance,
        $calculated_balance,
        $discrepancy,
        current_time('mysql')
    );
    
    return wp_mail($admin_email, $subject, $message);
}

/**
 * Verify all wallet balances - for batch integrity checks
 */
function greenangel_verify_all_wallet_balances() {
    global $wpdb;
    
    // Get all users with wallet balance
    $users_with_balance = $wpdb->get_col("
        SELECT user_id 
        FROM {$wpdb->usermeta} 
        WHERE meta_key = 'angel_wallet_balance' 
        AND meta_value > 0
    ");
    
    $integrity_issues = [];
    
    foreach ($users_with_balance as $user_id) {
        if (!greenangel_verify_wallet_balance_integrity($user_id)) {
            $integrity_issues[] = $user_id;
        }
    }
    
    // Log batch check results
    greenangel_log_wallet_operation([
        'user_id' => null,
        'action' => 'batch_integrity_check',
        'amount' => count($integrity_issues),
        'reason' => sprintf(
            'Checked %d users, found %d integrity issues',
            count($users_with_balance),
            count($integrity_issues)
        ),
        'context' => 'batch_integrity'
    ]);
    
    return $integrity_issues;
}

/**
 * Reconcile wallet balance (fix discrepancies)
 */
function greenangel_reconcile_wallet_balance($user_id, $force_recalculate = false) {
    global $wpdb;
    
    $current_balance = greenangel_get_wallet_balance($user_id);
    
    // Calculate correct balance from transaction log
    $calculated_balance = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(amount) FROM {$wpdb->prefix}angel_wallet_transactions WHERE user_id = %d",
        $user_id
    ));
    
    $calculated_balance = floatval($calculated_balance);
    $discrepancy = abs($current_balance - $calculated_balance);
    
    if ($discrepancy > 0.01 || $force_recalculate) {
        // Update to correct balance
        $old_balance = $current_balance;
        greenangel_set_wallet_balance($user_id, $calculated_balance);
        
        // Log the reconciliation
        greenangel_log_wallet_operation([
            'user_id' => $user_id,
            'admin_id' => get_current_user_id(),
            'action' => 'balance_reconciliation',
            'amount' => $discrepancy,
            'reason' => sprintf(
                'Reconciled balance from %.2f to %.2f (difference: %.2f)',
                $old_balance,
                $calculated_balance,
                $calculated_balance - $old_balance
            ),
            'context' => 'reconciliation'
        ]);
        
        return [
            'success' => true,
            'old_balance' => $old_balance,
            'new_balance' => $calculated_balance,
            'adjustment' => $calculated_balance - $old_balance
        ];
    }
    
    return [
        'success' => false,
        'message' => 'No reconciliation needed'
    ];
}

/**
 * Daily cron job to verify wallet balance integrity
 */
function greenangel_daily_wallet_integrity_check() {
    $integrity_issues = greenangel_verify_all_wallet_balances();
    
    if (!empty($integrity_issues)) {
        // Send daily integrity report
        $site_name = get_bloginfo('name');
        $admin_email = get_option('admin_email');
        
        $subject = sprintf(
            '[%s] Daily Wallet Integrity Report - %d Issues Found',
            $site_name,
            count($integrity_issues)
        );
        
        $message = sprintf(
            "Daily wallet integrity check completed:\n\n" .
            "Issues found: %d\n" .
            "User IDs with issues: %s\n" .
            "Date: %s\n\n" .
            "Please review and resolve these issues in your admin dashboard.",
            count($integrity_issues),
            implode(', ', $integrity_issues),
            current_time('mysql')
        );
        
        wp_mail($admin_email, $subject, $message);
    }
}

// Schedule daily integrity check
if (!wp_next_scheduled('greenangel_daily_wallet_integrity_check')) {
    wp_schedule_event(time(), 'daily', 'greenangel_daily_wallet_integrity_check');
}
add_action('greenangel_daily_wallet_integrity_check', 'greenangel_daily_wallet_integrity_check');

/**
 * Create audit trail for balance adjustments
 */
function greenangel_create_balance_audit_trail($user_id, $old_balance, $new_balance, $reason, $admin_id = null) {
    $audit_entry = [
        'user_id' => $user_id,
        'admin_id' => $admin_id ?: get_current_user_id(),
        'old_balance' => $old_balance,
        'new_balance' => $new_balance,
        'adjustment' => $new_balance - $old_balance,
        'reason' => $reason,
        'timestamp' => current_time('mysql'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 200)
    ];
    
    $existing_audit = get_option('greenangel_balance_audit_trail', []);
    $existing_audit[] = $audit_entry;
    
    // Keep only last 1000 audit entries
    if (count($existing_audit) > 1000) {
        $existing_audit = array_slice($existing_audit, -1000);
    }
    
    update_option('greenangel_balance_audit_trail', $existing_audit);
}

/**
 * Hook into balance changes to create audit trail
 */
add_action('updated_user_meta', 'greenangel_track_balance_changes', 10, 4);
function greenangel_track_balance_changes($meta_id, $user_id, $meta_key, $meta_value) {
    if ($meta_key === 'angel_wallet_balance') {
        $old_balance = get_user_meta($user_id, 'angel_wallet_balance', true);
        $new_balance = floatval($meta_value);
        
        if ($old_balance != $new_balance) {
            greenangel_create_balance_audit_trail(
                $user_id,
                floatval($old_balance),
                $new_balance,
                'Balance updated via user meta'
            );
        }
    }
}

/**
 * Get balance adjustment audit trail for a user
 */
function greenangel_get_balance_audit_trail($user_id = null, $limit = 50) {
    $audit_trail = get_option('greenangel_balance_audit_trail', []);
    
    if ($user_id) {
        $audit_trail = array_filter($audit_trail, function($entry) use ($user_id) {
            return $entry['user_id'] == $user_id;
        });
    }
    
    // Sort by timestamp descending
    usort($audit_trail, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    return array_slice($audit_trail, 0, $limit);
}

?>