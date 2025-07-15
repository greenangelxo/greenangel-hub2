<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

/**
 * Angel Wallet Security Functions
 * Central security infrastructure for all wallet operations
 */

/**
 * Central rate limiting function for wallet operations
 */
function greenangel_wallet_rate_limit($key, $limit, $window = 3600) {
    $attempts = get_transient($key) ?: 0;
    
    if ($attempts >= $limit) {
        return false;
    }
    
    set_transient($key, $attempts + 1, $window);
    return true;
}

/**
 * Get remaining attempts for rate limiting
 */
function greenangel_wallet_get_remaining_attempts($key, $limit) {
    $attempts = get_transient($key) ?: 0;
    return max(0, $limit - $attempts);
}

/**
 * Get time until rate limit resets
 */
function greenangel_wallet_get_rate_limit_reset_time($key) {
    $transient_timeout = get_option('_transient_timeout_' . $key);
    
    if (!$transient_timeout) {
        return 0;
    }
    
    return max(0, $transient_timeout - time());
}

/**
 * Central logging function for all wallet operations
 */
function greenangel_log_wallet_operation($data) {
    $log_entry = [
        'timestamp' => current_time('mysql'),
        'user_id' => $data['user_id'] ?? null,
        'admin_id' => $data['admin_id'] ?? null,
        'action' => $data['action'] ?? 'unknown',
        'amount' => $data['amount'] ?? 0,
        'reason' => $data['reason'] ?? '',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 200),
        'context' => $data['context'] ?? 'general'
    ];
    
    // Store in database
    $existing_logs = get_option('greenangel_wallet_security_logs', []);
    $existing_logs[] = $log_entry;
    
    // Keep only last 1000 logs
    if (count($existing_logs) > 1000) {
        $existing_logs = array_slice($existing_logs, -1000);
    }
    
    update_option('greenangel_wallet_security_logs', $existing_logs);
    
    // Also log to error log
    error_log(sprintf(
        'Green Angel Wallet Operation: %s | User: %d | Admin: %d | Amount: %.2f | IP: %s | Context: %s',
        $data['action'],
        $data['user_id'] ?? 0,
        $data['admin_id'] ?? 0,
        $data['amount'] ?? 0,
        $log_entry['ip'],
        $data['context'] ?? 'general'
    ));
}

/**
 * Suspicious activity detection
 */
function greenangel_detect_suspicious_wallet_activity($user_id, $action, $amount) {
    $suspicious_flags = [];
    
    // Check for rapid consecutive actions
    $recent_actions = greenangel_get_recent_wallet_actions($user_id, 300); // 5 minutes
    if (count($recent_actions) > 5) {
        $suspicious_flags[] = 'rapid_actions';
    }
    
    // Check for unusual amounts
    if ($amount > 5000) {
        $suspicious_flags[] = 'large_amount';
    }
    
    // Check for pattern of round numbers (potential testing)
    if ($amount == round($amount, 0) && $amount >= 100) {
        $suspicious_flags[] = 'round_amount';
    }
    
    // Check for off-hours activity (between 2 AM and 6 AM)
    $hour = date('G');
    if ($hour >= 2 && $hour <= 6) {
        $suspicious_flags[] = 'off_hours';
    }
    
    // Log suspicious activity
    if (!empty($suspicious_flags)) {
        greenangel_log_wallet_operation([
            'user_id' => $user_id,
            'action' => 'suspicious_activity_detected',
            'amount' => $amount,
            'reason' => 'Flags: ' . implode(', ', $suspicious_flags),
            'context' => 'security'
        ]);
        
        // Send alert for serious flags
        if (in_array('rapid_actions', $suspicious_flags) || in_array('large_amount', $suspicious_flags)) {
            greenangel_send_security_alert($user_id, $action, $amount, $suspicious_flags);
        }
    }
    
    return $suspicious_flags;
}

/**
 * Get recent wallet actions for a user
 */
function greenangel_get_recent_wallet_actions($user_id, $seconds = 3600) {
    $logs = get_option('greenangel_wallet_security_logs', []);
    $cutoff = date('Y-m-d H:i:s', time() - $seconds);
    
    return array_filter($logs, function($log) use ($user_id, $cutoff) {
        return $log['user_id'] == $user_id && $log['timestamp'] >= $cutoff;
    });
}

/**
 * Send security alert to admin
 */
function greenangel_send_security_alert($user_id, $action, $amount, $flags) {
    $user = get_userdata($user_id);
    
    if (!$user) {
        return false;
    }
    
    $site_name = get_bloginfo('name');
    $admin_email = get_option('admin_email');
    
    $subject = sprintf(
        '[%s] WALLET SECURITY ALERT - Suspicious Activity Detected',
        $site_name
    );
    
    $message = sprintf(
        "SUSPICIOUS WALLET ACTIVITY DETECTED:\n\n" .
        "User: %s (%s)\n" .
        "Action: %s\n" .
        "Amount: £%.2f\n" .
        "Flags: %s\n" .
        "Date: %s\n" .
        "IP: %s\n" .
        "User Agent: %s\n\n" .
        "Please investigate this activity immediately.",
        $user->display_name,
        $user->user_email,
        $action,
        $amount,
        implode(', ', $flags),
        current_time('mysql'),
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    );
    
    return wp_mail($admin_email, $subject, $message);
}

/**
 * Admin notification system for wallet operations
 */
function greenangel_send_wallet_admin_notification($type, $data) {
    $notifications = [
        'large_adjustment' => [
            'threshold' => 100,
            'subject' => 'Large Wallet Adjustment',
            'template' => 'large_adjustment_template'
        ],
        'large_conversion' => [
            'threshold' => 500,
            'subject' => 'Large Coupon Conversion',
            'template' => 'large_conversion_template'
        ],
        'suspicious_activity' => [
            'threshold' => 0,
            'subject' => 'Suspicious Wallet Activity',
            'template' => 'suspicious_activity_template'
        ]
    ];
    
    if (!isset($notifications[$type])) {
        return false;
    }
    
    $config = $notifications[$type];
    $amount = $data['amount'] ?? 0;
    
    // Check if amount meets threshold
    if ($amount < $config['threshold']) {
        return false;
    }
    
    $site_name = get_bloginfo('name');
    $admin_email = get_option('admin_email');
    
    $subject = sprintf('[%s] %s - £%.2f', $site_name, $config['subject'], $amount);
    $message = call_user_func($config['template'], $data);
    
    return wp_mail($admin_email, $subject, $message);
}

/**
 * Template for large adjustment notifications
 */
function large_adjustment_template($data) {
    $admin = get_userdata($data['admin_id']);
    $customer = get_userdata($data['user_id']);
    
    return sprintf(
        "A large wallet adjustment has been made:\n\n" .
        "Admin: %s (%s)\n" .
        "Customer: %s (%s)\n" .
        "Action: %s\n" .
        "Amount: £%.2f\n" .
        "Reason: %s\n" .
        "Date: %s\n" .
        "IP: %s\n\n" .
        "Please review this adjustment in your admin dashboard.",
        $admin->display_name ?? 'Unknown',
        $admin->user_email ?? 'Unknown',
        $customer->display_name ?? 'Unknown',
        $customer->user_email ?? 'Unknown',
        $data['action'],
        $data['amount'],
        $data['reason'] ?? 'No reason provided',
        current_time('mysql'),
        $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    );
}

/**
 * Template for large conversion notifications
 */
function large_conversion_template($data) {
    $user = get_userdata($data['user_id']);
    
    return sprintf(
        "A large wallet-to-coupon conversion has been made:\n\n" .
        "Customer: %s (%s)\n" .
        "Amount: £%.2f\n" .
        "Coupon Code: %s\n" .
        "Date: %s\n" .
        "IP: %s\n\n" .
        "Please review this conversion in your admin dashboard.",
        $user->display_name ?? 'Unknown',
        $user->user_email ?? 'Unknown',
        $data['amount'],
        $data['coupon_code'] ?? 'N/A',
        current_time('mysql'),
        $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    );
}

/**
 * Template for suspicious activity notifications
 */
function suspicious_activity_template($data) {
    $user = get_userdata($data['user_id']);
    
    return sprintf(
        "SUSPICIOUS WALLET ACTIVITY DETECTED:\n\n" .
        "User: %s (%s)\n" .
        "Action: %s\n" .
        "Amount: £%.2f\n" .
        "Flags: %s\n" .
        "Date: %s\n" .
        "IP: %s\n" .
        "User Agent: %s\n\n" .
        "Please investigate this activity immediately.",
        $user->display_name ?? 'Unknown',
        $user->user_email ?? 'Unknown',
        $data['action'],
        $data['amount'],
        $data['flags'] ?? 'None',
        current_time('mysql'),
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    );
}

/**
 * Hook system for wallet operation monitoring
 */
function greenangel_wallet_security_init() {
    // Hook into wallet operations
    add_action('greenangel_wallet_balance_changed', 'greenangel_monitor_balance_change', 10, 4);
    add_action('greenangel_wallet_coupon_created', 'greenangel_monitor_coupon_creation', 10, 3);
    add_action('greenangel_wallet_payment_processed', 'greenangel_monitor_payment', 10, 3);
}

/**
 * Monitor balance changes
 */
function greenangel_monitor_balance_change($user_id, $old_balance, $new_balance, $reason) {
    $amount = abs($new_balance - $old_balance);
    $action = $new_balance > $old_balance ? 'balance_increase' : 'balance_decrease';
    
    // Log the change
    greenangel_log_wallet_operation([
        'user_id' => $user_id,
        'action' => $action,
        'amount' => $amount,
        'reason' => $reason,
        'context' => 'balance_monitor'
    ]);
    
    // Check for suspicious activity
    greenangel_detect_suspicious_wallet_activity($user_id, $action, $amount);
}

/**
 * Monitor coupon creation
 */
function greenangel_monitor_coupon_creation($user_id, $coupon_code, $amount) {
    greenangel_log_wallet_operation([
        'user_id' => $user_id,
        'action' => 'coupon_created',
        'amount' => $amount,
        'reason' => 'Coupon: ' . $coupon_code,
        'context' => 'coupon_monitor'
    ]);
    
    greenangel_detect_suspicious_wallet_activity($user_id, 'coupon_created', $amount);
}

/**
 * Monitor payment processing
 */
function greenangel_monitor_payment($user_id, $order_id, $amount) {
    greenangel_log_wallet_operation([
        'user_id' => $user_id,
        'action' => 'payment_processed',
        'amount' => $amount,
        'reason' => 'Order: ' . $order_id,
        'context' => 'payment_monitor'
    ]);
    
    greenangel_detect_suspicious_wallet_activity($user_id, 'payment_processed', $amount);
}

// Initialize security monitoring
add_action('init', 'greenangel_wallet_security_init');

/**
 * Get wallet security statistics for admin dashboard
 */
function greenangel_get_wallet_security_stats() {
    $logs = get_option('greenangel_wallet_security_logs', []);
    $recent_logs = array_filter($logs, function($log) {
        return strtotime($log['timestamp']) > (time() - DAY_IN_SECONDS);
    });
    
    $stats = [
        'total_operations_today' => count($recent_logs),
        'suspicious_activities_today' => count(array_filter($recent_logs, function($log) {
            return $log['action'] === 'suspicious_activity_detected';
        })),
        'large_adjustments_today' => count(array_filter($recent_logs, function($log) {
            return in_array($log['action'], ['add', 'remove']) && $log['amount'] >= 100;
        })),
        'total_amount_today' => array_sum(array_column($recent_logs, 'amount')),
        'unique_users_today' => count(array_unique(array_column($recent_logs, 'user_id')))
    ];
    
    return $stats;
}

/**
 * Clean up old security logs (run via cron)
 */
function greenangel_cleanup_security_logs() {
    $logs = get_option('greenangel_wallet_security_logs', []);
    $cutoff = date('Y-m-d H:i:s', time() - (30 * DAY_IN_SECONDS)); // Keep 30 days
    
    $cleaned_logs = array_filter($logs, function($log) use ($cutoff) {
        return $log['timestamp'] >= $cutoff;
    });
    
    update_option('greenangel_wallet_security_logs', $cleaned_logs);
}

// Schedule cleanup
if (!wp_next_scheduled('greenangel_cleanup_security_logs')) {
    wp_schedule_event(time(), 'daily', 'greenangel_cleanup_security_logs');
}
add_action('greenangel_cleanup_security_logs', 'greenangel_cleanup_security_logs');

?>