<?php
/**
 * Helper functions for Customer Module
 * Data processing, calculations, and utility functions
 */

// Get customers with search and filtering - FIXED PAGINATION
function greenangel_get_customers($search = '', $filter = 'all', $page = 1, $per_page = 12) {
    // FIXED: Get more users initially, then filter, then paginate
    $initial_args = [
        'number' => -1, // Get all users first
        'orderby' => 'registered',
        'order' => 'DESC',
    ];
    
    // Add search functionality
    if (!empty($search)) {
        $initial_args['search'] = '*' . $search . '*';
        $initial_args['search_columns'] = ['user_login', 'user_email', 'user_nicename', 'display_name'];
    }
    
    // Get all users first
    $all_users = get_users($initial_args);
    
    // Apply custom filters BEFORE pagination
    if ($filter !== 'all') {
        $all_users = array_filter($all_users, function($user) use ($filter) {
            return greenangel_customer_matches_filter($user, $filter);
        });
    }
    
    // If search includes Angel identity, search user meta too
    if (!empty($search)) {
        $meta_users = greenangel_search_angel_identities($search, $filter, 1, -1);
        $all_users = array_merge($all_users, $meta_users);
        $all_users = array_unique($all_users, SORT_REGULAR);
    }
    
    // NOW apply pagination to the filtered results
    $offset = ($page - 1) * $per_page;
    $users = array_slice($all_users, $offset, $per_page);
    
    return $users;
}

// Get total count of all customers for header stats
function greenangel_get_all_customers_count() {
    $all_users = get_users(['fields' => 'ID']);
    return count($all_users);
}

// Search in Angel identity meta fields
function greenangel_search_angel_identities($search, $filter = 'all', $page = 1, $per_page = 12) {
    global $wpdb;
    
    $search_term = '%' . $wpdb->esc_like($search) . '%';
    
    // Search in angel identity fields
    $user_ids = $wpdb->get_col($wpdb->prepare("
        SELECT DISTINCT user_id 
        FROM {$wpdb->usermeta} 
        WHERE (meta_key = 'angel_identity_name' OR meta_key = 'angel_identity_bio') 
        AND meta_value LIKE %s
    ", $search_term));
    
    if (empty($user_ids)) {
        return [];
    }
    
    $args = [
        'include' => $user_ids,
        'number' => $per_page,
        'offset' => ($page - 1) * $per_page,
    ];
    
    $users = get_users($args);
    
    // Apply filters
    if ($filter !== 'all') {
        $users = array_filter($users, function($user) use ($filter) {
            return greenangel_customer_matches_filter($user, $filter);
        });
    }
    
    return $users;
}

// Check if customer matches filter criteria
function greenangel_customer_matches_filter($user, $filter) {
    $user_id = $user->ID;
    
    switch ($filter) {
        case 'paying':
            return get_user_meta($user_id, 'paying_customer', true) == 1;
            
        case 'high_value':
            $total_spent = floatval(get_user_meta($user_id, 'wc_money_spent_wp', true));
            return $total_spent >= 100;
            
        case 'vip':
            $total_spent = floatval(get_user_meta($user_id, 'wc_money_spent_wp', true));
            $order_count = intval(get_user_meta($user_id, 'wc_order_count_wpstg0', true));
            return $total_spent >= 200 || $order_count >= 10;
            
        case 'new':
            $registered = strtotime($user->user_registered);
            $thirty_days_ago = strtotime('-30 days');
            return $registered >= $thirty_days_ago;
            
        case 'inactive':
            $last_login = get_user_meta($user_id, 'last_login_time', true);
            if (empty($last_login)) return true;
            $last_login_time = strtotime($last_login);
            $ninety_days_ago = strtotime('-90 days');
            return $last_login_time < $ninety_days_ago;
            
        case 'has_identity':
            $angel_name = get_user_meta($user_id, 'angel_identity_name', true);
            return !empty($angel_name);
            
        default:
            return true;
    }
}

// Get total customers count - FIXED TO WORK WITH FILTERING
function greenangel_get_customers_count($search = '', $filter = 'all') {
    // Get all users first
    $args = ['fields' => 'all'];
    
    if (!empty($search)) {
        $args['search'] = '*' . $search . '*';
        $args['search_columns'] = ['user_login', 'user_email', 'user_nicename', 'display_name'];
    }
    
    $users = get_users($args);
    
    // Apply filters
    if ($filter !== 'all') {
        $users = array_filter($users, function($user) use ($filter) {
            return greenangel_customer_matches_filter($user, $filter);
        });
    }
    
    // Include Angel identity search results
    if (!empty($search)) {
        $meta_users = greenangel_search_angel_identities($search, $filter, 1, -1);
        $users = array_merge($users, $meta_users);
        $users = array_unique($users, SORT_REGULAR);
    }
    
    return count($users);
}

// Get paying customers count - USE REAL WOOCOMMERCE DATA
function greenangel_get_paying_customers_count() {
    $customers = get_users(['fields' => 'ID']);
    $paying_count = 0;
    
    foreach ($customers as $user_id) {
        $customer = new WC_Customer($user_id);
        if ($customer->get_total_spent() > 0) {
            $paying_count++;
        }
    }
    
    return $paying_count;
}

// Get total customer revenue - USE REAL WOOCOMMERCE DATA
function greenangel_get_total_customer_revenue() {
    $customers = get_users(['fields' => 'ID']);
    $total_revenue = 0;
    
    foreach ($customers as $user_id) {
        $customer = new WC_Customer($user_id);
        $total_revenue += $customer->get_total_spent();
    }
    
    return $total_revenue;
}

// Calculate member tier based on spending and orders - REALISTIC TIERS
function greenangel_calculate_member_tier($total_spent, $order_count) {
    // Convert to numbers to be sure
    $total_spent = floatval($total_spent);
    $order_count = intval($order_count);
    
    // Realistic tiers based on your actual customer data
    if ($total_spent >= 500 || $order_count >= 10) {
        return 'VIP Angel';       // £500+ or 10+ orders
    } elseif ($total_spent >= 200 || $order_count >= 5) {
        return 'Gold Angel';      // £200+ or 5+ orders  
    } elseif ($total_spent >= 50 || $order_count >= 2) {
        return 'Silver Angel';    // £50+ or 2+ orders
    } elseif ($total_spent > 0 || $order_count > 0) {
        return 'Bronze Angel';    // Any purchase
    } else {
        return 'New Angel';       // No purchases yet
    }
}

// Get activity status based on last login
function greenangel_get_activity_status($last_login) {
    if (empty($last_login)) {
        return 'Never Logged In';
    }
    
    $last_login_time = strtotime($last_login);
    $now = time();
    $diff = $now - $last_login_time;
    
    if ($diff < 86400) { // 24 hours
        return 'Active Today';
    } elseif ($diff < 604800) { // 7 days
        return 'Active This Week';
    } elseif ($diff < 2592000) { // 30 days
        return 'Active This Month';
    } elseif ($diff < 7776000) { // 90 days
        return 'Recently Active';
    } else {
        return 'Inactive';
    }
}

// Format last seen for display
function greenangel_format_last_seen($last_login) {
    if (empty($last_login)) {
        return 'Never';
    }
    
    $last_login_time = strtotime($last_login);
    $now = time();
    $diff = $now - $last_login_time;
    
    if ($diff < 3600) { // Less than 1 hour
        $minutes = floor($diff / 60);
        return $minutes <= 1 ? 'Just now' : $minutes . ' minutes ago';
    } elseif ($diff < 86400) { // Less than 24 hours
        $hours = floor($diff / 3600);
        return $hours == 1 ? '1 hour ago' : $hours . ' hours ago';
    } elseif ($diff < 604800) { // Less than 7 days
        $days = floor($diff / 86400);
        return $days == 1 ? 'Yesterday' : $days . ' days ago';
    } else {
        return date('M j, Y', $last_login_time);
    }
}

// Format member since date
function greenangel_format_member_since($date) {
    $timestamp = strtotime($date);
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 2592000) { // Less than 30 days
        $days = floor($diff / 86400);
        return $days <= 1 ? 'Today' : $days . ' days ago';
    } elseif ($diff < 31536000) { // Less than 1 year
        $months = floor($diff / 2592000);
        return $months == 1 ? '1 month ago' : $months . ' months ago';
    } else {
        return date('M Y', $timestamp);
    }
}

// AJAX handler for wallet adjustments - SECURED WITH RATE LIMITING & VALIDATION
add_action('wp_ajax_greenangel_adjust_wallet', 'greenangel_ajax_adjust_wallet');
function greenangel_ajax_adjust_wallet() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'greenangel_customers_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check permissions
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Insufficient permissions');
    }
    
    $admin_id = get_current_user_id();
    
    // Rate limiting - max 10 wallet adjustments per hour per admin
    $rate_key = 'wallet_adjust_' . $admin_id;
    $attempts = get_transient($rate_key) ?: 0;
    if ($attempts >= 10) {
        wp_send_json_error('Rate limit exceeded. Maximum 10 wallet adjustments per hour. Please try again later.');
    }
    set_transient($rate_key, $attempts + 1, HOUR_IN_SECONDS);
    
    $customer_id = intval($_POST['customer_id']);
    $action = sanitize_text_field($_POST['action_type']); // 'add' or 'remove'
    $amount = floatval($_POST['amount']);
    $reason = sanitize_textarea_field($_POST['reason']);
    
    // Enhanced validation
    if ($customer_id <= 0) {
        wp_send_json_error('Invalid customer ID');
    }
    
    // Maximum adjustment validation - £0.01 to £1,000
    if ($amount <= 0 || $amount > 1000) {
        wp_send_json_error('Invalid amount. Must be between £0.01 and £1,000');
    }
    
    // Validate customer exists
    $customer = get_userdata($customer_id);
    if (!$customer) {
        wp_send_json_error('Customer not found');
    }
    
    // Validate action type
    if (!in_array($action, ['add', 'remove'], true)) {
        wp_send_json_error('Invalid action type');
    }
    
    // Get current balance using YOUR existing function
    $current_balance = greenangel_get_wallet_balance($customer_id);
    
    // Check if adjustment would exceed wallet cap
    if ($action === 'add') {
        $new_potential_balance = $current_balance + $amount;
        if ($new_potential_balance > 50000) {
            wp_send_json_error('Adjustment would exceed maximum wallet balance of £50,000');
        }
    }
    
    // Log the admin action BEFORE processing
    greenangel_log_admin_wallet_action([
        'admin_id' => $admin_id,
        'customer_id' => $customer_id,
        'action' => $action,
        'amount' => $amount,
        'reason' => $reason,
        'current_balance' => $current_balance,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 200),
        'timestamp' => current_time('mysql')
    ]);
    
    // Use YOUR existing wallet functions!
    if ($action === 'add') {
        $new_balance = greenangel_add_to_wallet($customer_id, $amount, $reason ?: 'Admin adjustment via customer module', 'manual');
        
        // Send email notification for additions (top-ups)
        if ($new_balance !== false && function_exists('greenangel_send_wallet_topup_email')) {
            greenangel_send_wallet_topup_email($customer_id, $amount, $reason ?: 'Admin adjustment via customer module');
        }
    } else {
        // For remove, we need to deduct but use manual type
        $new_balance = greenangel_deduct_from_wallet($customer_id, $amount, null, $reason ?: 'Admin adjustment via customer module', 'manual');
    }
    
    if ($new_balance !== false) {
        
        // Send email notification for large adjustments (over £100)
        if ($amount >= 100) {
            greenangel_notify_large_wallet_adjustment($admin_id, $customer_id, $action, $amount, $reason);
        }
        
        // Send success response
        wp_send_json_success([
            'new_balance' => $new_balance,
            'formatted_balance' => '£' . number_format($new_balance, 2),
            'message' => ucfirst($action) . ' of £' . number_format($amount, 2) . ' completed successfully'
        ]);
    } else {
        wp_send_json_error('Failed to update wallet balance');
    }
}

// NEW: AJAX handler for Angel Identity Reset - ADMIN ONLY!
add_action('wp_ajax_greenangel_reset_angel_identity', 'greenangel_ajax_reset_angel_identity');
function greenangel_ajax_reset_angel_identity() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'greenangel_customers_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check permissions - ADMIN ONLY!
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Insufficient permissions');
    }
    
    $customer_id = intval($_POST['customer_id']);
    $reason = sanitize_text_field($_POST['reason']);
    
    if ($customer_id <= 0) {
        wp_send_json_error('Invalid customer ID');
    }
    
    // Verify customer exists
    $customer = get_userdata($customer_id);
    if (!$customer) {
        wp_send_json_error('Customer not found');
    }
    
    // Get current identity data before reset (for logging)
    $current_emoji = get_user_meta($customer_id, 'angel_identity_emoji', true);
    $current_name = get_user_meta($customer_id, 'angel_identity_name', true);
    $current_bio = get_user_meta($customer_id, 'angel_identity_bio', true);
    $current_lock = get_user_meta($customer_id, 'angel_identity_lock', true);
    $current_set_date = get_user_meta($customer_id, 'angel_identity_set_date', true);
    
    // Check if they actually have an identity to reset
    $has_identity = !empty($current_name) && !empty($current_emoji) && $current_emoji !== '👤';
    
    if (!$has_identity) {
        wp_send_json_error('Customer has no Angel identity to reset');
    }
    
    try {
        // RESET ALL ANGEL IDENTITY DATA
        delete_user_meta($customer_id, 'angel_identity_emoji');
        delete_user_meta($customer_id, 'angel_identity_name');
        delete_user_meta($customer_id, 'angel_identity_bio');
        delete_user_meta($customer_id, 'angel_identity_lock');
        delete_user_meta($customer_id, 'angel_identity_set_date');
        
        // Log the reset action for admin records
        $admin_user = wp_get_current_user();
        $log_entry = [
            'timestamp' => current_time('mysql'),
            'admin_id' => $admin_user->ID,
            'admin_name' => $admin_user->display_name,
            'customer_id' => $customer_id,
            'customer_email' => $customer->user_email,
            'action' => 'angel_identity_reset',
            'reason' => $reason ?: 'No reason provided',
            'previous_data' => [
                'emoji' => $current_emoji,
                'name' => $current_name,
                'bio' => $current_bio,
                'lock_expiry' => $current_lock,
                'set_date' => $current_set_date
            ]
        ];
        
        // Store reset log in options table (you could also create a custom table)
        $existing_logs = get_option('greenangel_identity_reset_logs', []);
        $existing_logs[] = $log_entry;
        
        // Keep only last 100 reset logs to prevent bloat
        if (count($existing_logs) > 100) {
            $existing_logs = array_slice($existing_logs, -100);
        }
        
        update_option('greenangel_identity_reset_logs', $existing_logs);
        
        // Success response
        wp_send_json_success([
            'message' => 'Angel identity reset successfully! Customer can now choose a new identity.',
            'reset_data' => [
                'previous_emoji' => $current_emoji,
                'previous_name' => $current_name,
                'reset_by' => $admin_user->display_name,
                'reset_time' => current_time('F j, Y g:i A')
            ]
        ]);
        
    } catch (Exception $e) {
        // Log the error
        error_log("Angel Identity Reset Error for user {$customer_id}: " . $e->getMessage());
        wp_send_json_error('Failed to reset Angel identity. Please try again.');
    }
}

// NEW: Get Angel Identity reset logs (for admin viewing if needed)
function greenangel_get_identity_reset_logs($limit = 50) {
    $logs = get_option('greenangel_identity_reset_logs', []);
    
    // Sort by timestamp (newest first)
    usort($logs, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    // Limit results
    if ($limit > 0) {
        $logs = array_slice($logs, 0, $limit);
    }
    
    return $logs;
}

// NEW: Check if user can change Angel identity (no lock)
function greenangel_can_change_identity($user_id) {
    $lock_time = get_user_meta($user_id, 'angel_identity_lock', true);
    
    if (empty($lock_time) || !is_numeric($lock_time)) {
        return true; // No lock set
    }
    
    return intval($lock_time) <= time(); // Lock has expired
}

// NEW: Get Angel identity lock status
function greenangel_get_identity_lock_status($user_id) {
    $lock_time = get_user_meta($user_id, 'angel_identity_lock', true);
    
    if (empty($lock_time) || !is_numeric($lock_time)) {
        return [
            'is_locked' => false,
            'can_change' => true,
            'expires_at' => null,
            'expires_formatted' => null,
            'time_remaining' => null
        ];
    }
    
    $lock_timestamp = intval($lock_time);
    $current_time = time();
    $is_locked = $lock_timestamp > $current_time;
    
    return [
        'is_locked' => $is_locked,
        'can_change' => !$is_locked,
        'expires_at' => $lock_timestamp,
        'expires_formatted' => date('F j, Y g:i A', $lock_timestamp),
        'time_remaining' => $is_locked ? ($lock_timestamp - $current_time) : 0
    ];
}

// Get wallet transaction history for a customer - USES YOUR EXISTING SYSTEM!
function greenangel_get_customer_wallet_transactions($customer_id, $limit = 10) {
    // Use your existing function but with a different name to avoid conflicts
    return greenangel_get_wallet_transactions($customer_id, $limit);
}

// AJAX handler for quick actions
add_action('wp_ajax_greenangel_customer_quick_action', 'greenangel_ajax_customer_quick_action');
function greenangel_ajax_customer_quick_action() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'greenangel_customers_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check permissions
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Insufficient permissions');
    }
    
    $action = sanitize_text_field($_POST['quick_action']);
    $customer_id = intval($_POST['customer_id']);
    
    switch ($action) {
        case 'email':
            $customer = get_userdata($customer_id);
            if ($customer) {
                $email_url = admin_url('admin.php?page=wc-admin&path=/customers/' . $customer_id);
                wp_send_json_success(['redirect' => $email_url]);
            } else {
                wp_send_json_error('Customer not found');
            }
            break;
            
        case 'view_orders':
            $orders_url = admin_url('edit.php?post_type=shop_order&customer_id=' . $customer_id);
            wp_send_json_success(['redirect' => $orders_url]);
            break;
            
        default:
            wp_send_json_error('Unknown action');
    }
}

// Add the customers tab to the navigation (integrate with existing system)
add_filter('greenangel_hub_tabs', 'greenangel_add_customers_tab');
function greenangel_add_customers_tab($tabs) {
    $tabs['customers'] = '👥 Customers';
    return $tabs;
}

// Handle the customers tab rendering (integrate with existing switch statement)
add_action('greenangel_hub_render_tab_customers', 'greenangel_render_customers_tab');

// WALLET SECURITY HELPER FUNCTIONS

/**
 * Log admin wallet actions for security audit trail
 */
function greenangel_log_admin_wallet_action($data) {
    $existing_logs = get_option('greenangel_admin_wallet_logs', []);
    $existing_logs[] = $data;
    
    // Keep only last 500 logs to prevent database bloat
    if (count($existing_logs) > 500) {
        $existing_logs = array_slice($existing_logs, -500);
    }
    
    update_option('greenangel_admin_wallet_logs', $existing_logs);
    
    // Also log to error log for immediate visibility
    error_log(sprintf(
        'Green Angel Admin Wallet Action: %s %s %.2f for customer %d by admin %d | IP: %s | Reason: %s',
        $data['action'],
        $data['action'] === 'add' ? 'added' : 'removed',
        $data['amount'],
        $data['customer_id'],
        $data['admin_id'],
        $data['ip'],
        $data['reason']
    ));
}

/**
 * Send email notification for large wallet adjustments
 */
function greenangel_notify_large_wallet_adjustment($admin_id, $customer_id, $action, $amount, $reason) {
    $admin = get_userdata($admin_id);
    $customer = get_userdata($customer_id);
    
    if (!$admin || !$customer) {
        return false;
    }
    
    $site_name = get_bloginfo('name');
    $admin_email = get_option('admin_email');
    
    $subject = sprintf(
        '[%s] Large Wallet Adjustment Alert - £%.2f',
        $site_name,
        $amount
    );
    
    $message = sprintf(
        "A large wallet adjustment has been made:\n\n" .
        "Admin: %s (%s)\n" .
        "Customer: %s (%s)\n" .
        "Action: %s\n" .
        "Amount: £%.2f\n" .
        "Reason: %s\n" .
        "Date: %s\n" .
        "IP: %s\n\n" .
        "Please review this adjustment in your admin dashboard.",
        $admin->display_name,
        $admin->user_email,
        $customer->display_name,
        $customer->user_email,
        ucfirst($action),
        $amount,
        $reason ?: 'No reason provided',
        current_time('mysql'),
        $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    );
    
    return wp_mail($admin_email, $subject, $message);
}
?>