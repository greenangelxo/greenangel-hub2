<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

add_action('admin_post_greenangel_update_wallet', function() {
    // Enhanced permission check
    if (!current_user_can('manage_woocommerce') || !current_user_can('edit_users')) {
        wp_die('Unauthorized access attempt.', 'Unauthorized', array('response' => 403));
    }
    
    // Enhanced nonce verification
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'greenangel_wallet_nonce')) {
        wp_die('Security check failed.', 'Security Error', array('response' => 403));
    }

    // Enhanced input validation and sanitization
    $user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
    $amount = isset($_POST['amount']) ? sanitize_text_field($_POST['amount']) : '';
    $comment = isset($_POST['comment']) ? wp_unslash(sanitize_textarea_field($_POST['comment'])) : '';
    $action = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';

    // Validate user exists and amount is valid
    if (!$user_id || !get_userdata($user_id)) {
        wp_die('Invalid user ID.', 'Invalid Data', array('response' => 400));
    }

    // Validate amount is numeric and within reasonable bounds
    if (!is_numeric($amount) || floatval($amount) <= 0 || floatval($amount) > 10000) {
        wp_die('Invalid amount. Must be between 0.01 and 10,000.', 'Invalid Data', array('response' => 400));
    }

    // Validate action type
    if (!in_array($action, ['add', 'deduct'], true)) {
        wp_die('Invalid action type.', 'Invalid Data', array('response' => 400));
    }

    $amount = floatval($amount);
    if ($action === 'deduct') $amount = -$amount;

    // Rate limiting check
    $transient_key = 'wallet_update_' . get_current_user_id();
    if (get_transient($transient_key)) {
        wp_die('Please wait before making another wallet update.', 'Rate Limited', array('response' => 429));
    }
    set_transient($transient_key, true, 5);

    // Use proper wallet functions to maintain integrity
    if ($action === 'add') {
        // Add to wallet with manual type
        $result = greenangel_add_to_wallet($user_id, $amount, $comment, 'manual');
        if ($result === false) {
            wp_die('Failed to add funds to wallet or amount exceeds limit.', 'Wallet Error', array('response' => 500));
        }
        
        // Send email notification for additions (top-ups)
        if (function_exists('greenangel_send_wallet_topup_email')) {
            greenangel_send_wallet_topup_email($user_id, $amount, $comment);
        }
    } else {
        // Deduct from wallet with manual type
        $result = greenangel_deduct_from_wallet($user_id, $amount, null, $comment, 'manual');
        if ($result === false) {
            wp_die('Failed to deduct from wallet or insufficient balance.', 'Wallet Error', array('response' => 500));
        }
    }

    $message = $action === 'deduct' ? 'deducted' : 'added';
    wp_redirect(admin_url('admin.php?page=greenangel-hub&tab=wallet&search=' . urlencode($_POST['ref'] ?? '') . '&success=' . $message));
    exit;
});

function greenangel_render_wallet_tab() {
    global $wpdb;
    
    // Check if wallet system is disabled
    $wallet_disabled = get_option('greenangel_wallet_disabled', false);
    
    // Handle emergency disable/enable
    if (isset($_POST['emergency_toggle']) && current_user_can('manage_options')) {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'greenangel_emergency_nonce')) {
            wp_die('Security check failed.', 'Security Error', array('response' => 403));
        }
        
        $new_status = $_POST['emergency_toggle'] === 'disable' ? true : false;
        update_option('greenangel_wallet_disabled', $new_status);
        $wallet_disabled = $new_status;
        
        $message = $new_status ? 'disabled' : 'enabled';
        wp_redirect(admin_url('admin.php?page=greenangel-hub&tab=wallet&emergency=' . $message));
        exit;
    }
?>
<style>
    /* Animations */
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    @keyframes shimmer {
        0% { background-position: -1000px 0; }
        100% { background-position: 1000px 0; }
    }

    .wallet-header {
        text-align: center;
        margin-bottom: 30px;
        animation: slideIn 0.6s ease-out;
    }

    .wallet-header h2 {
        font-size: 28px;
        font-weight: 700;
        color: #aed604;
        margin: 0 0 8px 0;
        text-shadow: 0 2px 4px rgba(174, 214, 4, 0.2);
    }

    .wallet-header p {
        font-size: 15px;
        color: #999;
        margin: 0;
    }

    /* Success message */
    .wallet-success {
        background: linear-gradient(135deg, #4caf50, #45a049);
        color: white;
        padding: 15px 20px;
        border-radius: 12px;
        margin: 0 auto 25px;
        max-width: 600px;
        text-align: center;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        animation: slideIn 0.4s ease-out, pulse 2s ease-in-out infinite;
    }

    /* Search container */
    .wallet-search-container {
        max-width: 600px;
        margin: 0 auto 35px;
        position: relative;
        animation: slideIn 0.6s ease-out 0.1s both;
    }

    .wallet-search {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .wallet-search-wrapper {
        flex: 1;
        position: relative;
    }

    .wallet-search input[type="text"] {
        width: 100%;
        padding: 16px 20px 16px 48px;
        border-radius: 14px;
        border: 2px solid #333;
        background: #111;
        color: #fff;
        font-size: 15px;
        transition: all 0.3s ease;
    }

    .wallet-search input[type="text"]:focus {
        outline: none;
        border-color: #aed604;
        background: #1a1a1a;
        box-shadow: 0 0 0 4px rgba(174, 214, 4, 0.1);
    }

    .wallet-search input[type="text"]::placeholder {
        color: #666;
    }

    /* Search icon */
    .search-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #666;
        pointer-events: none;
    }

    /* Clear button */
    .clear-search {
        background: #333;
        color: #fff;
        border: none;
        padding: 12px 20px;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .clear-search:hover {
        background: #444;
        transform: translateY(-1px);
    }

    /* Stats strip with interactive features */
    .wallet-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        max-width: 1000px;
        margin: 0 auto 35px;
        animation: slideIn 0.6s ease-out 0.2s both;
    }

    .stat-card {
        background: linear-gradient(135deg, #1a1a1a, #222);
        border: 1px solid #333;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        position: relative;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        background: linear-gradient(45deg, transparent, #aed604, transparent);
        transform: translateX(-100%);
        transition: transform 0.6s;
        opacity: 0;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        border-color: #aed604;
        box-shadow: 0 8px 24px rgba(174, 214, 4, 0.2);
    }

    .stat-card:hover::before {
        transform: translateX(100%);
        opacity: 0.1;
    }

    .stat-card.active {
        border-color: #aed604;
        background: linear-gradient(135deg, #222, #2a2a2a);
    }

    .stat-label {
        font-size: 13px;
        color: #888;
        margin-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .stat-value {
        font-size: 24px;
        font-weight: bold;
        color: #aed604;
    }

    .toggle-amount {
        background: #333;
        color: #aed604;
        border: 1px solid #444;
        padding: 3px 6px;
        border-radius: 8px;
        font-size: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .toggle-amount:hover {
        background: #444;
        border-color: #aed604;
    }

    .expandable-content {
        display: none;
        margin-top: 16px;
        padding: 16px;
        background: #0a0a0a;
        border-radius: 12px;
        border: 1px solid #333;
        max-height: 300px;
        overflow-y: auto;
    }

    .expandable-content.show {
        display: block;
        animation: slideIn 0.3s ease-out;
    }

    .close-btn {
        background: transparent;
        color: #666;
        border: none;
        font-size: 16px;
        cursor: pointer;
        float: right;
        transition: color 0.2s ease;
        margin: -8px -8px 8px 8px;
    }

    .close-btn:hover {
        color: #aed604;
    }

    .wallet-list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #333;
        transition: all 0.2s ease;
    }

    .wallet-list-item:hover {
        padding-left: 4px;
        border-color: #aed604;
    }

    .wallet-list-item:last-child {
        border-bottom: none;
    }

    .user-quick-info {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .user-mini-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: linear-gradient(135deg, #aed604, #8bc34a);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: bold;
        color: #1a1a1a;
    }

    .user-mini-details {
        display: flex;
        flex-direction: column;
    }

    .user-mini-name {
        color: #aed604;
        font-size: 13px;
        font-weight: 600;
    }

    .user-mini-balance {
        color: #888;
        font-size: 11px;
    }

    .activity-log-item {
        padding: 8px 0;
        border-bottom: 1px solid #333;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
    }

    .activity-log-item:last-child {
        border-bottom: none;
    }

    .activity-summary {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .activity-icon {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
    }

    .activity-icon.topup {
        background: rgba(76, 175, 80, 0.2);
        color: #4caf50;
    }

    .activity-icon.spend {
        background: rgba(244, 67, 54, 0.2);
        color: #f44336;
    }

    .activity-icon.manual {
        background: rgba(174, 214, 4, 0.2);
        color: #aed604;
    }

    .activity-user {
        color: #ccc;
        font-size: 12px;
    }

    .activity-amount {
        font-weight: 600;
        font-size: 12px;
    }

    .activity-amount.positive {
        color: #4caf50;
    }

    .activity-amount.negative {
        color: #f44336;
    }

    /* Cards container */
    .wallet-cards-container {
        display: grid;
        gap: 20px;
        max-width: 1000px;
        margin: 0 auto;
        grid-template-columns: 1fr;
    }

    @media (min-width: 768px) {
        .wallet-cards-container {
            grid-template-columns: 1fr 1fr;
        }
    }

    .wallet-card {
        background: #1a1a1a;
        border: 2px solid #333;
        border-radius: 16px;
        padding: 28px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        animation: slideIn 0.6s ease-out 0.3s both;
    }

    .wallet-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #aed604, #8bc34a);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
    }

    .wallet-card:hover {
        border-color: #444;
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
    }

    .wallet-card:hover::after {
        transform: scaleX(1);
    }

    /* Customer info */
    .customer-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .customer-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #aed604, #8bc34a);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: bold;
        color: #1a1a1a;
        flex-shrink: 0;
    }

    .customer-info {
        flex: 1;
    }

    .customer-name {
        color: #aed604;
        font-size: 18px;
        font-weight: 600;
        margin: 0 0 4px;
    }

    .customer-email {
        color: #999;
        font-size: 14px;
        margin: 0;
    }

    .wallet-balance {
        font-size: 36px;
        font-weight: 800;
        color: #fff;
        margin: 20px 0 8px;
        display: flex;
        align-items: baseline;
        gap: 10px;
    }

    .balance-change {
        font-size: 16px;
        font-weight: 500;
        padding: 4px 10px;
        border-radius: 20px;
        background: rgba(76, 175, 80, 0.2);
        color: #4caf50;
    }

    .balance-change.negative {
        background: rgba(244, 67, 54, 0.2);
        color: #f44336;
    }

    .wallet-note {
        font-size: 14px;
        color: #888;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .wallet-actions {
        margin-top: 25px;
        padding-top: 25px;
        border-top: 1px solid #333;
    }

    .wallet-actions form {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .wallet-actions input[type="number"],
    .wallet-actions input[type="text"] {
        padding: 14px 16px;
        border-radius: 12px;
        border: 2px solid #333;
        background: #111;
        color: #fff;
        font-size: 15px;
        transition: all 0.2s ease;
    }

    .wallet-actions input[type="number"]:focus,
    .wallet-actions input[type="text"]:focus {
        outline: none;
        border-color: #666;
        background: #1a1a1a;
    }

    .action-buttons {
        display: flex;
        gap: 12px;
    }

    .wallet-actions button {
        flex: 1;
        padding: 14px 24px;
        border-radius: 24px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        font-size: 15px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .add-btn { 
        background: linear-gradient(135deg, #4caf50, #45a049);
        color: #fff; 
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
    }
    
    .add-btn:hover { 
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
    }
    
    .deduct-btn { 
        background: linear-gradient(135deg, #f44336, #da190b);
        color: #fff; 
        box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
    }
    
    .deduct-btn:hover { 
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(244, 67, 54, 0.4);
    }

    /* Transaction log */
    .wallet-log {
        margin-top: 25px;
        padding-top: 25px;
        border-top: 1px solid #333;
    }

    .log-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .log-title {
        color: #aed604;
        font-size: 15px;
        font-weight: 600;
        margin: 0;
    }

    .log-count {
        color: #666;
        font-size: 13px;
    }

    .wallet-log-entry {
        margin-bottom: 14px;
        padding: 12px;
        background: #111;
        border-radius: 10px;
        transition: all 0.2s ease;
    }

    .wallet-log-entry:hover {
        background: #1a1a1a;
        transform: translateX(4px);
    }

    .wallet-log-entry:last-child {
        margin-bottom: 0;
    }

    .log-amount {
        font-weight: 700;
        font-size: 16px;
    }

    .log-positive { color: #4caf50; }
    .log-negative { color: #f44336; }

    .log-details {
        color: #999;
        font-size: 13px;
        margin-top: 4px;
    }

    .log-date {
        color: #666;
        font-size: 12px;
    }

    /* Empty states */
    .no-results {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 40px;
        color: #888;
    }

    .no-results-icon {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    .no-results p {
        margin: 8px 0;
    }

    /* Quick actions */
    .quick-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .quick-btn {
        background: #222;
        color: #aed604;
        border: 1px solid #333;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .quick-btn:hover {
        background: #333;
        border-color: #aed604;
    }

    /* Footer emergency controls - subtle and clean */
    .wallet-footer {
        margin-top: 40px;
        padding: 20px;
        background: #111;
        border-radius: 12px;
        border: 1px solid #333;
        text-align: center;
    }

    .emergency-status-footer {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        font-size: 14px;
        color: #888;
    }

    .status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #4caf50;
    }

    .status-indicator.disabled {
        background: #f44336;
    }

    .emergency-btn-small {
        background: transparent;
        color: #666;
        border: 1px solid #333;
        padding: 6px 12px;
        border-radius: 16px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-transform: none;
        letter-spacing: normal;
    }

    .emergency-btn-small:hover {
        border-color: #aed604;
        color: #aed604;
    }

    .emergency-btn-small.disable:hover {
        border-color: #f44336;
        color: #f44336;
    }
</style>

<div class="wallet-header">
    <h2>💸 Angel Wallet Manager</h2>
    <p>Manage customer balances with style and grace</p>
</div>

<?php if (isset($_GET['emergency'])): ?>
<div class="wallet-success">
    <?php if ($_GET['emergency'] === 'disabled'): ?>
        🚨 Angel Wallet system disabled. All payments blocked.
    <?php else: ?>
        ✅ Angel Wallet system enabled. Normal operations resumed.
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="wallet-success">
    ✨ Success! Funds <?php echo $_GET['success'] === 'added' ? 'added' : 'deducted'; ?> successfully.
</div>
<?php endif; ?>

<!-- Quick stats with simple interactive features -->
<?php
$total_users = count_users();
$active_wallets_query = $wpdb->get_results("SELECT user_id, COUNT(*) as transaction_count FROM {$wpdb->prefix}angel_wallet_transactions GROUP BY user_id ORDER BY transaction_count DESC LIMIT 100");
$active_wallets_count = count($active_wallets_query);
$total_balance = floatval($wpdb->get_var("SELECT SUM(meta_value) FROM {$wpdb->prefix}usermeta WHERE meta_key = 'angel_wallet_balance'")) ?: 0;
$recent_activity = $wpdb->get_results("SELECT t.*, u.display_name FROM {$wpdb->prefix}angel_wallet_transactions t LEFT JOIN {$wpdb->users} u ON t.user_id = u.ID WHERE t.timestamp > DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY t.timestamp DESC LIMIT 100");
$recent_activity_count = count($recent_activity);
?>
<div class="wallet-stats">
    <div class="stat-card">
        <div class="stat-label">Total Customers</div>
        <div class="stat-value"><?php echo number_format($total_users['total_users']); ?></div>
    </div>
    
    <div class="stat-card" data-expandable="active-wallets">
        <div class="stat-label">
            Active Wallets
            <span class="toggle-amount" data-amounts='[10,25,50]' data-current="25">Show 25</span>
        </div>
        <div class="stat-value"><?php echo number_format($active_wallets_count); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-label">Total Balance</div>
        <div class="stat-value">£<?php echo number_format($total_balance, 2); ?></div>
    </div>
    
    <div class="stat-card" data-expandable="weekly-activity">
        <div class="stat-label">
            Week's Activity
            <span class="toggle-amount" data-amounts='[25,50,100]' data-current="25">Show 25</span>
        </div>
        <div class="stat-value"><?php echo number_format($recent_activity_count); ?></div>
    </div>
</div>

<!-- Search section -->
<div class="wallet-search-container">
    <form method="get" class="wallet-search">
        <input type="hidden" name="page" value="greenangel-hub" />
        <input type="hidden" name="tab" value="wallet" />
        <div class="wallet-search-wrapper">
            <span class="search-icon">🔍</span>
            <input type="text" name="search" placeholder="Search by name, email, username..." value="<?php echo esc_attr($_GET['search'] ?? ''); ?>" />
        </div>
        <?php if (!empty($_GET['search'])): ?>
        <button type="button" class="clear-search" onclick="window.location.href='?page=greenangel-hub&tab=wallet'">
            ✕ Clear
        </button>
        <?php endif; ?>
    </form>
</div>

<!-- Results -->
<div class="wallet-cards-container" id="main-content">
    <?php if (!empty($_GET['search'])):
        $search = sanitize_text_field($_GET['search']);
        
        // Comprehensive search
        $user_ids = $wpdb->get_col($wpdb->prepare("
            SELECT DISTINCT u.ID 
            FROM {$wpdb->users} u 
            LEFT JOIN {$wpdb->usermeta} um1 ON u.ID = um1.user_id AND um1.meta_key = 'first_name'
            LEFT JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id AND um2.meta_key = 'last_name'
            LEFT JOIN {$wpdb->usermeta} um3 ON u.ID = um3.user_id AND um3.meta_key = 'nickname'
            WHERE u.user_login LIKE %s 
               OR u.user_email LIKE %s 
               OR u.display_name LIKE %s 
               OR um1.meta_value LIKE %s 
               OR um2.meta_value LIKE %s 
               OR um3.meta_value LIKE %s
               OR CONCAT(um1.meta_value, ' ', um2.meta_value) LIKE %s
               OR CONCAT(um2.meta_value, ' ', um1.meta_value) LIKE %s
            ORDER BY u.display_name ASC
            LIMIT 20
        ", 
            '%' . $wpdb->esc_like($search) . '%',
            '%' . $wpdb->esc_like($search) . '%', 
            '%' . $wpdb->esc_like($search) . '%',
            '%' . $wpdb->esc_like($search) . '%',
            '%' . $wpdb->esc_like($search) . '%',
            '%' . $wpdb->esc_like($search) . '%',
            '%' . $wpdb->esc_like($search) . '%',
            '%' . $wpdb->esc_like($search) . '%'
        ));

        if (!empty($user_ids)):
            foreach ($user_ids as $user_id):
                $user = get_userdata($user_id);
                if (!$user) continue;
                
                $balance = greenangel_get_wallet_balance($user->ID);
                $logs = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}angel_wallet_transactions WHERE user_id = %d ORDER BY timestamp DESC LIMIT 5",
                    $user->ID
                ));
                
                // Get initials for avatar
                $name_parts = explode(' ', $user->display_name);
                $initials = '';
                foreach ($name_parts as $part) {
                    $initials .= strtoupper(substr(sanitize_text_field($part), 0, 1));
                }
                $initials = substr($initials, 0, 2);
                
                // Calculate recent change
                $recent_change = 0;
                if (!empty($logs)) {
                    $recent_change = floatval($logs[0]->amount);
                }
    ?>
    <div class="wallet-card">
        <div class="customer-header">
            <div class="customer-avatar"><?php echo esc_html($initials ?: '👤'); ?></div>
            <div class="customer-info">
                <h3 class="customer-name"><?php echo esc_html($user->display_name); ?></h3>
                <p class="customer-email"><?php echo esc_html($user->user_email); ?></p>
            </div>
        </div>
        
        <div class="wallet-balance">
            £<?php echo number_format($balance, 2); ?>
            <?php if ($recent_change != 0 && !empty($logs)): ?>
            <span class="balance-change <?php echo $recent_change < 0 ? 'negative' : ''; ?>">
                <?php echo $recent_change >= 0 ? '+' : ''; ?>£<?php echo number_format(abs($recent_change), 2); ?>
            </span>
            <?php endif; ?>
        </div>
        <div class="wallet-note">
            👼 Angel Wallet · Available Balance
        </div>

        <div class="quick-actions">
            <button class="quick-btn" onclick="this.closest('.wallet-card').querySelector('input[name=amount]').value='10.00'">+£10</button>
            <button class="quick-btn" onclick="this.closest('.wallet-card').querySelector('input[name=amount]').value='25.00'">+£25</button>
            <button class="quick-btn" onclick="this.closest('.wallet-card').querySelector('input[name=amount]').value='50.00'">+£50</button>
            <button class="quick-btn" onclick="this.closest('.wallet-card').querySelector('input[name=amount]').value='100.00'">+£100</button>
        </div>

        <?php if ($wallet_disabled): ?>
        <div style="padding: 12px; background: rgba(255,68,68,0.1); border: 1px solid rgba(255,68,68,0.3); border-radius: 8px; margin-top: 16px; color: #ff6666; font-size: 13px; text-align: center;">
            ⚠️ Wallet system currently disabled
        </div>
        <?php else: ?>
        <div class="wallet-actions">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('greenangel_wallet_nonce'); ?>
                <input type="hidden" name="action" value="greenangel_update_wallet" />
                <input type="hidden" name="user_id" value="<?php echo esc_attr($user->ID); ?>" />
                <input type="hidden" name="ref" value="<?php echo esc_attr($search); ?>" />
                <input type="number" step="0.01" name="amount" placeholder="Amount (e.g. 25.00)" required min="0.01" max="10000" />
                <input type="text" name="comment" placeholder="Add a note (optional)" maxlength="255" />
                <div class="action-buttons">
                    <button type="submit" name="action_type" value="add" class="add-btn">
                        <span>➕</span> Add Funds
                    </button>
                    <button type="submit" name="action_type" value="deduct" class="deduct-btn">
                        <span>➖</span> Deduct
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <?php if (!empty($logs)): ?>
        <div class="wallet-log">
            <div class="log-header">
                <h4 class="log-title">Recent Activity</h4>
                <span class="log-count"><?php echo count($logs); ?> transactions</span>
            </div>
            <?php foreach ($logs as $log): ?>
            <div class="wallet-log-entry">
                <?php
                    $date = date('j M Y, g:i a', strtotime($log->timestamp));
                    $is_positive = floatval($log->amount) >= 0;
                    $formatted = '£' . number_format(abs(floatval($log->amount)), 2);
                    $context = $log->type === 'topup' ? '💳 Top-up' : ($log->type === 'spend' ? '🛍️ Purchase' : '✏️ Manual Adjustment');
                ?>
                <div>
                    <span class="log-amount <?php echo $is_positive ? 'log-positive' : 'log-negative'; ?>">
                        <?php echo $is_positive ? '+' : '-'; ?><?php echo $formatted; ?>
                    </span>
                    <span class="log-details">
                        <?php echo $context; ?>
                        <?php if ($log->comment): ?>
                            — "<?php echo esc_html(stripslashes($log->comment)); ?>"
                        <?php endif; ?>
                    </span>
                </div>
                <div class="log-date"><?php echo esc_html($date); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; else: ?>
        <div class="no-results">
            <div class="no-results-icon">🔍</div>
            <p><strong>No angels found!</strong></p>
            <p>Try searching by their name, email, or username</p>
        </div>
    <?php endif; else: ?>
        <div class="no-results" id="default-content">
            <div class="no-results-icon">👆</div>
            <p><strong>Ready to manage some wallets?</strong></p>
            <p>Search for a customer above to get started!</p>
        </div>
    <?php endif; ?>
</div>

<!-- Hidden data for JavaScript -->
<div style="display: none;">
    <?php 
    // Active wallets data
    $display_wallets = array_slice($active_wallets_query, 0, 100);
    foreach ($display_wallets as $wallet):
        $user = get_userdata($wallet->user_id);
        if (!$user) continue;
        $balance = greenangel_get_wallet_balance($wallet->user_id);
        $name_parts = explode(' ', $user->display_name);
        $initials = '';
        foreach ($name_parts as $part) {
            $initials .= strtoupper(substr(sanitize_text_field($part), 0, 1));
        }
        $initials = substr($initials, 0, 2) ?: '👤';
    ?>
    <div class="wallet-card active-wallet-item" data-transaction-count="<?php echo $wallet->transaction_count; ?>" style="display: none;">
        <div class="customer-header">
            <div class="customer-avatar"><?php echo esc_html($initials); ?></div>
            <div class="customer-info">
                <h3 class="customer-name"><?php echo esc_html($user->display_name); ?></h3>
                <p class="customer-email"><?php echo esc_html($user->user_email); ?></p>
            </div>
        </div>
        
        <div class="wallet-balance">
            £<?php echo number_format($balance, 2); ?>
        </div>
        <div class="wallet-note">
            👼 Angel Wallet · <?php echo $wallet->transaction_count; ?> transactions
        </div>
    </div>
    <?php endforeach; ?>
    
    <?php 
    // Weekly activity data
    $display_activity = array_slice($recent_activity, 0, 100);
    foreach ($display_activity as $activity):
        $amount = floatval($activity->amount);
        $is_positive = $amount >= 0;
        $icon_class = $activity->type === 'topup' ? 'topup' : ($activity->type === 'spend' ? 'spend' : 'manual');
        $icon = $activity->type === 'topup' ? '💰' : ($activity->type === 'spend' ? '🛍️' : '✏️');
        $date = date('M j, g:i a', strtotime($activity->timestamp));
        $user = get_userdata($activity->user_id);
        if (!$user) continue;
        $name_parts = explode(' ', $user->display_name);
        $initials = '';
        foreach ($name_parts as $part) {
            $initials .= strtoupper(substr(sanitize_text_field($part), 0, 1));
        }
        $initials = substr($initials, 0, 2) ?: '👤';
    ?>
    <div class="wallet-card activity-item" style="display: none;">
        <div class="customer-header">
            <div class="customer-avatar"><?php echo esc_html($initials); ?></div>
            <div class="customer-info">
                <h3 class="customer-name"><?php echo esc_html($user->display_name); ?></h3>
                <p class="customer-email"><?php echo esc_html($user->user_email); ?></p>
            </div>
        </div>
        
        <div class="wallet-balance">
            <span class="activity-amount <?php echo $is_positive ? 'positive' : 'negative'; ?>" style="font-size: 28px;">
                <?php echo $is_positive ? '+' : ''; ?>£<?php echo number_format(abs($amount), 2); ?>
            </span>
        </div>
        <div class="wallet-note">
            <?php echo $icon; ?> <?php echo ucfirst($activity->type); ?> · <?php echo $date; ?>
        </div>
        <?php if ($activity->comment): ?>
        <div style="background: #111; padding: 8px 12px; border-radius: 8px; margin-top: 8px; font-size: 13px; color: #ccc;">
            "<?php echo esc_html(stripslashes($activity->comment)); ?>"
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<!-- Clean footer with emergency controls -->
<div class="wallet-footer">
    <div class="emergency-status-footer">
        <span class="status-indicator <?php echo $wallet_disabled ? 'disabled' : ''; ?>"></span>
        System Status: <?php echo $wallet_disabled ? 'Disabled' : 'Active'; ?>
    </div>
    <form method="post" style="display: inline;">
        <?php wp_nonce_field('greenangel_emergency_nonce'); ?>
        <button type="submit" 
                name="emergency_toggle" 
                value="<?php echo $wallet_disabled ? 'enable' : 'disable'; ?>"
                class="emergency-btn-small <?php echo $wallet_disabled ? 'enable' : 'disable'; ?>"
                onclick="return confirm('<?php echo $wallet_disabled ? 'Enable Angel Wallet system?' : 'Disable Angel Wallet system? This will block all payments.'; ?>')">
            <?php echo $wallet_disabled ? 'Enable System' : 'Emergency Disable'; ?>
        </button>
    </form>
</div>

<script>
// Auto-dismiss success message
setTimeout(() => {
    const success = document.querySelector('.wallet-success');
    if (success) {
        success.style.transition = 'all 0.5s ease';
        success.style.opacity = '0';
        success.style.transform = 'translateY(-20px)';
        setTimeout(() => success.remove(), 500);
    }
}, 4000);

// Simple expandable functionality
document.addEventListener('DOMContentLoaded', function() {
    const mainContent = document.getElementById('main-content');
    const defaultContent = document.getElementById('default-content');
    
    // Stat card click handlers
    document.querySelectorAll('.stat-card[data-expandable]').forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking on toggle amount button
            if (e.target.classList.contains('toggle-amount')) {
                e.stopPropagation();
                return;
            }
            
            const targetId = this.dataset.expandable;
            const isActive = this.classList.contains('active');
            
            // Close all others first
            document.querySelectorAll('.stat-card.active').forEach(activeCard => {
                activeCard.classList.remove('active');
            });
            
            // Toggle current
            if (isActive) {
                this.classList.remove('active');
                showDefaultContent();
            } else {
                this.classList.add('active');
                showExpandableContent(targetId);
            }
        });
    });
    
    // Toggle amount handlers
    document.querySelectorAll('.toggle-amount').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const amounts = JSON.parse(this.dataset.amounts);
            const current = parseInt(this.dataset.current);
            const currentIndex = amounts.indexOf(current);
            const nextIndex = (currentIndex + 1) % amounts.length;
            const nextAmount = amounts[nextIndex];
            
            this.dataset.current = nextAmount;
            this.textContent = `Show ${nextAmount}`;
            
            // Update content if expanded
            const card = this.closest('.stat-card');
            if (card && card.classList.contains('active')) {
                showExpandableContent(card.dataset.expandable);
            }
        });
    });
    
    function showDefaultContent() {
        mainContent.innerHTML = `
            <div class="no-results" id="default-content">
                <div class="no-results-icon">👆</div>
                <p><strong>Ready to manage some wallets?</strong></p>
                <p>Search for a customer above to get started!</p>
            </div>
        `;
    }
    
    function showExpandableContent(targetId) {
        if (targetId === 'active-wallets') {
            showActiveWallets();
        } else if (targetId === 'weekly-activity') {
            showWeeklyActivity();
        }
    }
    
    function showActiveWallets() {
        const toggle = document.querySelector('[data-expandable="active-wallets"] .toggle-amount');
        const amount = toggle ? parseInt(toggle.dataset.current) : 25;
        
        const walletItems = document.querySelectorAll('.active-wallet-item');
        let html = '';
        
        Array.from(walletItems).slice(0, amount).forEach(item => {
            html += item.outerHTML.replace('style="display: none;"', '');
        });
        
        if (html) {
            mainContent.innerHTML = html;
        } else {
            mainContent.innerHTML = `
                <div class="no-results">
                    <div class="no-results-icon">📊</div>
                    <p><strong>No active wallets found</strong></p>
                    <p>Users will appear here once they make transactions</p>
                </div>
            `;
        }
    }
    
    function showWeeklyActivity() {
        const toggle = document.querySelector('[data-expandable="weekly-activity"] .toggle-amount');
        const amount = toggle ? parseInt(toggle.dataset.current) : 25;
        
        const activityItems = document.querySelectorAll('.activity-item');
        let html = '';
        
        Array.from(activityItems).slice(0, amount).forEach(item => {
            html += item.outerHTML.replace('style="display: none;"', '');
        });
        
        if (html) {
            mainContent.innerHTML = html;
        } else {
            mainContent.innerHTML = `
                <div class="no-results">
                    <div class="no-results-icon">📈</div>
                    <p><strong>No recent activity</strong></p>
                    <p>Transactions from the past week will appear here</p>
                </div>
            `;
        }
    }
});

function closeExpandable(targetId) {
    const card = document.querySelector(`[data-expandable="${targetId}"]`);
    const content = document.getElementById(targetId);
    
    card.classList.remove('active');
    content.classList.remove('show');
}
</script>

<?php
}

// Add emergency disable check to gateway availability
add_filter('woocommerce_available_payment_gateways', 'greenangel_disable_wallet_if_emergency');
function greenangel_disable_wallet_if_emergency($gateways) {
    if (get_option('greenangel_wallet_disabled', false)) {
        unset($gateways['angel_wallet']);
    }
    return $gateways;
}
?>