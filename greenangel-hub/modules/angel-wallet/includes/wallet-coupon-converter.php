<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

/**
 * Angel Wallet to Coupon Converter
 * Convert wallet balance to single-use coupon codes
 */

// Add AJAX handlers for coupon conversion
add_action('wp_ajax_angel_convert_to_coupon', 'angel_handle_convert_to_coupon');
add_action('wp_ajax_nopriv_angel_convert_to_coupon', 'angel_handle_convert_to_coupon');

function angel_handle_convert_to_coupon() {
    // Enhanced security validation
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'angel_convert_coupon_nonce')) {
        wp_send_json_error(['message' => 'Security check failed']);
        return;
    }
    
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Please log in to convert wallet balance']);
        return;
    }
    
    $user_id = get_current_user_id();
    $user = get_userdata($user_id);
    
    if (!$user) {
        wp_send_json_error(['message' => 'Invalid user']);
        return;
    }
    
    // User-based rate limiting - prevent spam conversions
    $rate_limit_key = 'angel_coupon_convert_' . $user_id;
    if (get_transient($rate_limit_key)) {
        wp_send_json_error(['message' => 'Please wait before converting another coupon']);
        return;
    }
    set_transient($rate_limit_key, true, 60); // 1 minute cooldown
    
    // IP-based rate limiting - max 5 conversions per day per IP
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ip_rate_key = 'angel_coupon_convert_ip_' . md5($ip_address);
    $ip_attempts = get_transient($ip_rate_key) ?: 0;
    if ($ip_attempts >= 5) {
        wp_send_json_error(['message' => 'Daily conversion limit reached. Please try again tomorrow.']);
        return;
    }
    set_transient($ip_rate_key, $ip_attempts + 1, DAY_IN_SECONDS);
    
    // Velocity check - flag users converting frequently
    $velocity_key = 'angel_coupon_velocity_' . $user_id;
    $recent_conversions = get_transient($velocity_key) ?: 0;
    if ($recent_conversions >= 3) {
        // Log suspicious activity
        error_log(sprintf(
            'Green Angel Suspicious Activity: User %d has converted %d coupons in 24 hours | IP: %s',
            $user_id,
            $recent_conversions + 1,
            $ip_address
        ));
    }
    set_transient($velocity_key, $recent_conversions + 1, DAY_IN_SECONDS);
    
    // Get current balance
    $current_balance = greenangel_get_wallet_balance($user_id);
    
    if ($current_balance <= 0) {
        wp_send_json_error(['message' => 'No balance to convert']);
        return;
    }
    
    // Minimum conversion amount check
    if ($current_balance < 1.00) {
        wp_send_json_error(['message' => 'Minimum £1.00 required to convert to coupon']);
        return;
    }
    
    // Maximum conversion amount check (prevent abuse)
    if ($current_balance > 1000) {
        wp_send_json_error(['message' => 'Maximum £1,000 can be converted at once']);
        return;
    }
    
    try {
        // Create the coupon
        $coupon_code = angel_create_wallet_coupon($user_id, $current_balance);
        
        if (!$coupon_code) {
            wp_send_json_error(['message' => 'Failed to create coupon']);
            return;
        }
        
        // Zero out the wallet balance
        greenangel_set_wallet_balance($user_id, 0);
        
        // Log the conversion transaction
        greenangel_log_wallet_transaction(
            $user_id, 
            -$current_balance, 
            'manual', 
            null, 
            "Converted to coupon: " . $coupon_code . " (£" . number_format($current_balance, 2) . ")"
        );
        
        // Log conversion history for security tracking
        angel_log_coupon_conversion([
            'user_id' => $user_id,
            'coupon_code' => $coupon_code,
            'amount' => $current_balance,
            'ip' => $ip_address,
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 200),
            'timestamp' => current_time('mysql')
        ]);
        
        // Send admin notification for large conversions (over £500)
        if ($current_balance >= 500) {
            angel_notify_large_coupon_conversion($user_id, $coupon_code, $current_balance);
        }
        
        // Send success response with coupon details
        wp_send_json_success([
            'coupon_code' => $coupon_code,
            'amount' => $current_balance,
            'formatted_amount' => '£' . number_format($current_balance, 2),
            'message' => 'Successfully converted wallet balance to coupon!'
        ]);
        
    } catch (Exception $e) {
        error_log('Angel Wallet coupon conversion error: ' . $e->getMessage());
        wp_send_json_error(['message' => 'Conversion failed. Please try again.']);
    }
}

/**
 * Generate a descriptive summary for wallet coupon conversion
 */
function angel_generate_wallet_coupon_description($user_id, $amount) {
    global $wpdb;
    
    // Input validation
    $user_id = absint($user_id);
    $amount = floatval($amount);
    
    if (!$user_id || $amount <= 0) {
        return '';
    }
    
    $user = get_userdata($user_id);
    if (!$user) {
        return '';
    }
    
    $description = sprintf(
        "Wallet Balance Conversion - £%s converted from Angel Wallet balance on %s\n\n",
        number_format($amount, 2),
        current_time('d/m/Y \a\t H:i')
    );
    
    $description .= sprintf(
        "Customer: %s (%s)\n",
        sanitize_text_field($user->display_name),
        sanitize_email($user->user_email)
    );
    
    // Get recent wallet transactions to show what contributed to the balance
    $recent_transactions = $wpdb->get_results($wpdb->prepare(
        "SELECT amount, type, comment, timestamp FROM {$wpdb->prefix}angel_wallet_transactions 
         WHERE user_id = %d AND amount > 0 
         ORDER BY timestamp DESC LIMIT 5",
        $user_id
    ));
    
    if ($recent_transactions) {
        $description .= "\nRecent Balance Sources:\n";
        foreach ($recent_transactions as $transaction) {
            $type_label = sanitize_text_field(ucfirst($transaction->type));
            $date = date('d/m/Y', strtotime($transaction->timestamp));
            $comment = $transaction->comment ? ' - ' . sanitize_text_field($transaction->comment) : '';
            
            $description .= sprintf(
                "• £%s (%s) on %s%s\n",
                number_format($transaction->amount, 2),
                $type_label,
                $date,
                $comment
            );
        }
    }
    
    $description .= "\nThis coupon was automatically generated from Angel Wallet balance conversion.";
    
    return $description;
}

/**
 * Create a single-use coupon from wallet balance
 */
function angel_create_wallet_coupon($user_id, $amount) {
    $user_id = absint($user_id);
    $amount = floatval($amount);
    
    if (!$user_id || $amount <= 0) {
        return false;
    }
    
    $user = get_userdata($user_id);
    if (!$user) {
        return false;
    }
    
    // Generate unique coupon code
    $base_code = 'ANGEL-' . strtoupper(wp_generate_password(8, false));
    $coupon_code = $base_code;
    
    // Ensure uniqueness
    $attempt = 0;
    while (get_posts(['post_type' => 'shop_coupon', 'title' => $coupon_code, 'post_status' => 'publish'])) {
        $attempt++;
        $coupon_code = $base_code . '-' . $attempt;
        
        if ($attempt > 10) {
            return false; // Give up after 10 attempts
        }
    }
    
    // Generate descriptive content for the coupon
    $coupon_description = angel_generate_wallet_coupon_description($user_id, $amount);
    
    // Create the coupon post
    $coupon_id = wp_insert_post([
        'post_title' => $coupon_code,
        'post_content' => $coupon_description,
        'post_excerpt' => sprintf('Wallet conversion: £%s from %s', number_format($amount, 2), sanitize_text_field($user->display_name)),
        'post_status' => 'publish',
        'post_author' => 1,
        'post_type' => 'shop_coupon'
    ]);
    
    if (is_wp_error($coupon_id) || !$coupon_id) {
        return false;
    }
    
    // Set coupon meta data
    $expiry_date = date('Y-m-d', strtotime('+90 days')); // 90 day expiry
    
    update_post_meta($coupon_id, 'discount_type', 'fixed_cart');
    update_post_meta($coupon_id, 'coupon_amount', $amount);
    update_post_meta($coupon_id, 'individual_use', 'yes');
    update_post_meta($coupon_id, 'usage_limit', 1); // Single use only
    update_post_meta($coupon_id, 'usage_limit_per_user', 1);
    update_post_meta($coupon_id, 'limit_usage_to_x_items', '');
    update_post_meta($coupon_id, 'free_shipping', 'no');
    update_post_meta($coupon_id, 'exclude_sale_items', 'no');
    update_post_meta($coupon_id, 'minimum_amount', '');
    update_post_meta($coupon_id, 'maximum_amount', '');
    update_post_meta($coupon_id, 'customer_email', [$user->user_email]);
    update_post_meta($coupon_id, 'date_expires', strtotime($expiry_date));
    
    // Custom meta to track this as a wallet-converted coupon
    update_post_meta($coupon_id, '_angel_wallet_converted', 'yes');
    update_post_meta($coupon_id, '_angel_wallet_user_id', $user_id);
    update_post_meta($coupon_id, '_angel_wallet_original_amount', $amount);
    update_post_meta($coupon_id, '_angel_wallet_converted_date', current_time('mysql'));
    
    // Additional tracking meta for admin purposes
    update_post_meta($coupon_id, '_angel_wallet_customer_name', sanitize_text_field($user->display_name));
    update_post_meta($coupon_id, '_angel_wallet_customer_email', sanitize_email($user->user_email));
    update_post_meta($coupon_id, '_angel_wallet_conversion_ip', sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    
    return $coupon_code;
}

/**
 * Generate just a simple convert button (goes under balance)
 */
function angel_get_convert_section_html($balance) {
    if ($balance < 1.00) {
        return '';
    }
    
    ob_start();
    ?>
    
    <!-- Simple Convert Button -->
    <div class="angel-simple-convert">
        <button class="simple-convert-btn" id="angel-convert-btn" data-balance="<?php echo esc_attr($balance); ?>">
            <span class="convert-icon-small">🎟️</span>
            <span>Convert to Coupon</span>
        </button>
    </div>
    
    <!-- Enhanced Conversion Modal -->
    <div class="angel-convert-modal" id="angel-convert-modal" style="display: none;">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">💸 Convert to Coupon</h3>
                <button class="modal-close" id="angel-convert-modal-close">×</button>
            </div>
            <div class="modal-body">
                <!-- Current Balance Display -->
                <div class="current-balance-display">
                    <div class="balance-icon">👼</div>
                    <div class="balance-info">
                        <div class="balance-label">Your Current Balance</div>
                        <div class="balance-amount">£<?php echo number_format($balance, 2); ?></div>
                    </div>
                </div>
                
                <!-- Conversion Info -->
                <div class="conversion-info">
                    <h4 class="conversion-title">🎫 Cash Out Your Balance</h4>
                    <p class="conversion-description">
                        Convert your entire wallet balance into a single-use coupon code 
                        that you can use on any future order.
                    </p>
                    
                    <div class="conversion-benefits">
                        <div class="benefit-item">
                            <span class="benefit-icon">✨</span>
                            <span>Use towards any order amount</span>
                        </div>
                        <div class="benefit-item">
                            <span class="benefit-icon">⏰</span>
                            <span>Valid for 90 days</span>
                        </div>
                        <div class="benefit-item">
                            <span class="benefit-icon">🔒</span>
                            <span>Personal use only</span>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="conversion-actions">
                    <button class="convert-confirm-btn" id="angel-convert-confirm">
                        <span class="btn-icon">🎟️</span>
                        <span>Yes, Convert £<?php echo number_format($balance, 2); ?></span>
                    </button>
                    <button class="convert-cancel-btn" id="angel-convert-cancel">
                        <span>Maybe Later</span>
                    </button>
                </div>
                
                <div class="conversion-warning">
                    ⚠️ This will empty your wallet balance and cannot be undone
                </div>
            </div>
        </div>
    </div>
    
    <!-- Success Modal -->
    <div class="angel-coupon-modal" id="angel-coupon-modal" style="display: none;">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">🎉 Coupon Created!</h3>
                <button class="modal-close" id="angel-modal-close">×</button>
            </div>
            <div class="modal-body">
                <div class="coupon-display">
                    <div class="coupon-code-section">
                        <label class="coupon-label">Your Coupon Code:</label>
                        <div class="coupon-code" id="angel-coupon-code">ANGEL-XXXXXXXX</div>
                        <button class="copy-btn" id="angel-copy-btn">
                            <span class="copy-icon">📋</span> Copy Code
                        </button>
                    </div>
                    
                    <div class="coupon-details">
                        <div class="detail-item">
                            <span class="detail-label">Value:</span>
                            <span class="detail-value" id="angel-coupon-amount">£0.00</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Expires:</span>
                            <span class="detail-value"><?php echo date('M j, Y', strtotime('+90 days')); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Usage:</span>
                            <span class="detail-value">Single use only</span>
                        </div>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button class="action-btn primary" onclick="window.location.href='<?php echo esc_url(home_url('/shop')); ?>'">
                        🛍️ Start Shopping
                    </button>
                    <button class="action-btn secondary" id="angel-modal-done">
                        ✅ Got It
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    /* Simple Convert Button (goes under balance) */
    .angel-simple-convert {
        text-align: center;
        margin-top: 16px;
    }
    
    .simple-convert-btn {
        background: linear-gradient(135deg, #333, #444);
        color: #aed604;
        border: 2px solid #555;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .simple-convert-btn:hover {
        background: linear-gradient(135deg, #444, #555);
        border-color: #aed604;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(174, 214, 4, 0.2);
    }
    
    .convert-icon-small {
        font-size: 14px;
    }
    
    /* Convert Modal Styles */
    .angel-convert-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 999999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        box-sizing: border-box;
    }
    
    .modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(8px);
    }
    
    .modal-content {
        background: linear-gradient(135deg, #1a1a1a, #222);
        border: 2px solid #aed604;
        border-radius: 24px;
        padding: 0;
        max-width: 550px;
        width: 100%;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8);
        animation: modalSlideIn 0.4s ease-out;
    }
    
    @keyframes modalSlideIn {
        from { opacity: 0; transform: translateY(40px) scale(0.9); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    
    .modal-content::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #aed604, #8bc34a);
    }
    
    .modal-header {
        padding: 28px 28px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-title {
        color: #aed604;
        font-size: 24px;
        font-weight: 700;
        margin: 0;
    }
    
    .modal-close {
        background: transparent;
        border: none;
        color: #666;
        font-size: 24px;
        cursor: pointer;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s ease;
    }
    
    .modal-close:hover {
        background: rgba(244, 67, 54, 0.2);
        color: #f44336;
    }
    
    .modal-body {
        padding: 28px;
    }
    
    /* Current Balance Display */
    .current-balance-display {
        display: flex;
        align-items: center;
        gap: 16px;
        background: #111;
        padding: 20px;
        border-radius: 16px;
        margin-bottom: 24px;
        border: 2px solid #333;
    }
    
    .balance-icon {
        font-size: 32px;
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #aed604, #8bc34a);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .balance-info {
        flex: 1;
    }
    
    .balance-label {
        color: #888;
        font-size: 14px;
        margin-bottom: 4px;
        font-weight: 600;
    }
    
    .balance-amount {
        color: #aed604;
        font-size: 28px;
        font-weight: 800;
    }
    
    /* Conversion Info */
    .conversion-info {
        margin-bottom: 32px;
    }
    
    .conversion-title {
        color: #fff;
        font-size: 20px;
        font-weight: 700;
        margin: 0 0 12px 0;
        text-align: center;
    }
    
    .conversion-description {
        color: #ccc;
        font-size: 16px;
        line-height: 1.5;
        margin: 0 0 24px 0;
        text-align: center;
    }
    
    .conversion-benefits {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .benefit-item {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
        color: #888;
        padding: 12px 16px;
        background: #111;
        border-radius: 12px;
        border: 1px solid #333;
    }
    
    .benefit-icon {
        font-size: 18px;
        width: 24px;
        text-align: center;
        flex-shrink: 0;
    }
    
    /* Action Buttons */
    .conversion-actions {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 16px;
    }
    
    .convert-confirm-btn {
        background: linear-gradient(135deg, #aed604, #8bc34a);
        color: #1a1a1a;
        border: none;
        padding: 16px 24px;
        border-radius: 24px;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(174, 214, 4, 0.3);
    }
    
    .convert-confirm-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(174, 214, 4, 0.4);
    }
    
    .convert-cancel-btn {
        background: transparent;
        color: #888;
        border: 2px solid #444;
        padding: 12px 24px;
        border-radius: 24px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .convert-cancel-btn:hover {
        color: #aed604;
        border-color: #aed604;
        background: rgba(174, 214, 4, 0.05);
    }
    
    .conversion-warning {
        color: #f39c12;
        font-size: 12px;
        text-align: center;
        font-weight: 500;
        opacity: 0.9;
    }
    
    /* Success Modal Styles */
    .angel-coupon-modal .modal-content {
        max-width: 500px;
    }
    
    .coupon-display {
        text-align: center;
        margin-bottom: 32px;
    }
    
    .coupon-code-section {
        background: #111;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        border: 2px dashed #333;
    }
    
    .coupon-label {
        display: block;
        color: #888;
        font-size: 14px;
        margin-bottom: 8px;
        font-weight: 600;
    }
    
    .coupon-code {
        font-size: 24px;
        font-weight: 800;
        color: #aed604;
        font-family: 'Courier New', monospace;
        background: #1a1a1a;
        padding: 12px 20px;
        border-radius: 12px;
        border: 2px solid #333;
        margin-bottom: 16px;
        word-break: break-all;
        user-select: all;
    }
    
    .copy-btn {
        background: linear-gradient(135deg, #333, #444);
        color: #aed604;
        border: 1px solid #555;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
    }
    
    .copy-btn:hover {
        background: linear-gradient(135deg, #444, #555);
        transform: translateY(-1px);
    }
    
    .copy-btn.copied {
        background: linear-gradient(135deg, #4caf50, #45a049);
        color: #fff;
    }
    
    .coupon-details {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .detail-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        background: #1a1a1a;
        border-radius: 8px;
        border: 1px solid #333;
    }
    
    .detail-label {
        color: #888;
        font-size: 14px;
        font-weight: 500;
    }
    
    .detail-value {
        color: #fff;
        font-weight: 600;
    }
    
    .modal-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
    }
    
    .action-btn {
        padding: 12px 24px;
        border-radius: 25px;
        border: none;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .action-btn.primary {
        background: linear-gradient(135deg, #aed604, #8bc34a);
        color: #1a1a1a;
        box-shadow: 0 4px 16px rgba(174, 214, 4, 0.3);
    }
    
    .action-btn.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(174, 214, 4, 0.4);
    }
    
    .action-btn.secondary {
        background: transparent;
        color: #aed604;
        border: 2px solid #aed604;
    }
    
    .action-btn.secondary:hover {
        background: rgba(174, 214, 4, 0.1);
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .modal-content {
            margin: 20px;
            max-width: calc(100% - 40px);
        }
        
        .modal-header {
            padding: 20px 20px 0;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-title {
            font-size: 20px;
        }
        
        .current-balance-display {
            flex-direction: column;
            text-align: center;
            gap: 12px;
        }
        
        .balance-amount {
            font-size: 24px;
        }
        
        .coupon-code {
            font-size: 18px;
        }
        
        .modal-actions {
            flex-direction: column;
        }
        
        .action-btn {
            width: 100%;
        }
    }
    
    /* Loading states */
    .convert-confirm-btn.loading {
        opacity: 0.7;
        pointer-events: none;
    }
    
    .convert-confirm-btn.loading::after {
        content: '⏳ Converting...';
    }
    </style>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Simple convert button click
        const convertBtn = document.getElementById('angel-convert-btn');
        const convertModal = document.getElementById('angel-convert-modal');
        const convertModalClose = document.getElementById('angel-convert-modal-close');
        const convertConfirm = document.getElementById('angel-convert-confirm');
        const convertCancel = document.getElementById('angel-convert-cancel');
        
        // Success modal elements
        const successModal = document.getElementById('angel-coupon-modal');
        const successModalClose = document.getElementById('angel-modal-close');
        const successModalDone = document.getElementById('angel-modal-done');
        const copyBtn = document.getElementById('angel-copy-btn');

        // Show convert modal
        convertBtn?.addEventListener('click', function() {
            convertModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });

        // Close convert modal
        function closeConvertModal() {
            convertModal.style.display = 'none';
            document.body.style.overflow = '';
        }

        convertModalClose?.addEventListener('click', closeConvertModal);
        convertCancel?.addEventListener('click', closeConvertModal);

        // Close on overlay click
        convertModal?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeConvertModal();
            }
        });

        // Confirm conversion
        convertConfirm?.addEventListener('click', function() {
            const balance = convertBtn.getAttribute('data-balance');
            
            // Show loading state
            this.classList.add('loading');
            
            // Make AJAX request
            fetch(wc_add_to_cart_params?.ajax_url || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'angel_convert_to_coupon',
                    nonce: window.angel_wallet_nonce || ''
                })
            })
            .then(response => response.json())
            .then(data => {
                this.classList.remove('loading');
                
                if (data.success) {
                    // Close convert modal
                    closeConvertModal();
                    
                    // Show success modal
                    document.getElementById('angel-coupon-code').textContent = data.data.coupon_code;
                    document.getElementById('angel-coupon-amount').textContent = data.data.formatted_amount;
                    
                    successModal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                    
                    // Update balance display
                    const balanceElements = document.querySelectorAll('.balance-amount, .angel-wallet-balance-large');
                    balanceElements.forEach(el => {
                        el.textContent = '£0.00';
                    });
                    
                    // Hide the convert button since balance is now 0
                    convertBtn.closest('.angel-simple-convert').style.display = 'none';
                    
                } else {
                    alert('Error: ' + (data.data?.message || 'Failed to convert balance'));
                }
            })
            .catch(error => {
                this.classList.remove('loading');
                console.error('Conversion error:', error);
                alert('Network error. Please try again.');
            });
        });

        // Success modal handlers
        function closeSuccessModal() {
            successModal.style.display = 'none';
            document.body.style.overflow = '';
        }

        successModalClose?.addEventListener('click', closeSuccessModal);
        successModalDone?.addEventListener('click', closeSuccessModal);

        // Close on overlay click
        successModal?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeSuccessModal();
            }
        });

        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (convertModal && convertModal.style.display === 'flex') {
                    closeConvertModal();
                }
                if (successModal && successModal.style.display === 'flex') {
                    closeSuccessModal();
                }
            }
        });

        // Copy to clipboard functionality
        copyBtn?.addEventListener('click', function() {
            const couponCode = document.getElementById('angel-coupon-code').textContent;
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(couponCode).then(() => {
                    this.classList.add('copied');
                    this.innerHTML = '<span class="copy-icon">✅</span> Copied!';
                    
                    setTimeout(() => {
                        this.classList.remove('copied');
                        this.innerHTML = '<span class="copy-icon">📋</span> Copy Code';
                    }, 2000);
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = couponCode;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                
                this.classList.add('copied');
                this.innerHTML = '<span class="copy-icon">✅</span> Copied!';
                
                setTimeout(() => {
                    this.classList.remove('copied');
                    this.innerHTML = '<span class="copy-icon">📋</span> Copy Code';
                }, 2000);
            }
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}

/**
 * Add security nonce to footer
 */
add_action('wp_footer', 'angel_add_convert_nonce_script');
function angel_add_convert_nonce_script() {
    if (is_user_logged_in()) {
        $nonce = wp_create_nonce('angel_convert_coupon_nonce');
        ?>
        <script>
        window.angel_wallet_nonce = '<?php echo $nonce; ?>';
        </script>
        <?php
    }
}

/**
 * Add validation for wallet-converted coupons
 */
add_filter('woocommerce_coupon_is_valid', 'angel_validate_wallet_coupon', 10, 2);
function angel_validate_wallet_coupon($valid, $coupon) {
    if (!$valid) return false;
    
    $is_wallet_coupon = get_post_meta($coupon->get_id(), '_angel_wallet_converted', true);
    
    if ($is_wallet_coupon === 'yes') {
        // Check if user is the original owner
        $original_user_id = get_post_meta($coupon->get_id(), '_angel_wallet_user_id', true);
        $current_user_id = get_current_user_id();
        
        if ($original_user_id && $current_user_id != $original_user_id) {
            throw new Exception('This coupon can only be used by the original wallet owner.');
        }
        
        // Prevent using with Angel Wallet payment method
        if (WC()->session && WC()->session->get('chosen_payment_method') === 'angel_wallet') {
            throw new Exception('Wallet coupons cannot be combined with Angel Wallet payments.');
        }
    }
    
    return $valid;
}

/**
 * Track wallet coupon usage
 */
add_action('woocommerce_coupon_applied', 'angel_track_wallet_coupon_usage');
function angel_track_wallet_coupon_usage($coupon_code) {
    $coupon = new WC_Coupon($coupon_code);
    
    if ($coupon->get_id()) {
        $is_wallet_coupon = get_post_meta($coupon->get_id(), '_angel_wallet_converted', true);
        
        if ($is_wallet_coupon === 'yes') {
            $original_amount = get_post_meta($coupon->get_id(), '_angel_wallet_original_amount', true);
            $user_id = get_post_meta($coupon->get_id(), '_angel_wallet_user_id', true);
            
            if ($user_id && $original_amount) {
                // Log the coupon usage
                greenangel_log_wallet_transaction(
                    $user_id, 
                    $original_amount, 
                    'manual', 
                    null, 
                    "Used wallet coupon: " . $coupon_code . " (£" . number_format($original_amount, 2) . ")"
                );
            }
        }
    }
}

/**
 * Log coupon conversion for security tracking
 */
function angel_log_coupon_conversion($data) {
    $existing_logs = get_option('angel_coupon_conversion_logs', []);
    $existing_logs[] = $data;
    
    // Keep only last 500 conversions to prevent database bloat
    if (count($existing_logs) > 500) {
        $existing_logs = array_slice($existing_logs, -500);
    }
    
    update_option('angel_coupon_conversion_logs', $existing_logs);
    
    // Also log to error log for monitoring
    error_log(sprintf(
        'Green Angel Coupon Conversion: User %d converted £%.2f to coupon %s | IP: %s',
        $data['user_id'],
        $data['amount'],
        $data['coupon_code'],
        $data['ip']
    ));
}

/**
 * Send admin notification for large coupon conversions
 */
function angel_notify_large_coupon_conversion($user_id, $coupon_code, $amount) {
    $user = get_userdata($user_id);
    
    if (!$user) {
        return false;
    }
    
    $site_name = get_bloginfo('name');
    $admin_email = get_option('admin_email');
    
    $subject = sprintf(
        '[%s] Large Wallet Coupon Conversion Alert - £%.2f',
        $site_name,
        $amount
    );
    
    $message = sprintf(
        "A large wallet-to-coupon conversion has been made:\n\n" .
        "Customer: %s (%s)\n" .
        "Amount: £%.2f\n" .
        "Coupon Code: %s\n" .
        "Date: %s\n" .
        "IP: %s\n\n" .
        "Please review this conversion in your admin dashboard.",
        $user->display_name,
        $user->user_email,
        $amount,
        $coupon_code,
        current_time('mysql'),
        $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    );
    
    return wp_mail($admin_email, $subject, $message);
}
?>