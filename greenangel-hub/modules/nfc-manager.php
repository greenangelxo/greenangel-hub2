<?php
// 🌿 Green Angel Hub – NFC Card Manager Module

use Wlr\App\Models\Users;

// 🔁 Save Card Assignment
add_action('admin_post_greenangel_save_card_status', 'greenangel_save_card_status');
function greenangel_save_card_status() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Permission denied');
    }
    check_admin_referer('greenangel_save_card_status', 'greenangel_nonce');

    if (
        isset($_POST['order_id']) &&
        isset($_POST['card_issued'])
    ) {
        $order_id    = intval($_POST['order_id']);
        $card_issued = sanitize_text_field($_POST['card_issued']);

        if (in_array($card_issued, ['angel', 'affiliate'])) {
            update_post_meta($order_id, '_greenangel_card_issued', $card_issued);
            update_post_meta($order_id, '_greenangel_card_status', 'issued');
        } else {
            delete_post_meta($order_id, '_greenangel_card_issued');
            delete_post_meta($order_id, '_greenangel_card_status');
        }
    }

    wp_redirect(admin_url('admin.php?page=greenangel-hub&tab=nfc-manager'));
    exit;
}

// 🧠 Get Referral Code
function greenangel_get_loyalty_referral_code($email) {
    if (!class_exists('Wlr\App\Models\Users')) return null;
    $user_model = new Users();
    $where      = sanitize_email($email);
    $user       = $user_model->getWhere("user_email = '{$where}'", '*', true);
    return (!empty($user) && isset($user->refer_code))
         ? $user->refer_code
         : null;
}

// 💳 Main Renderer
function greenangel_render_nfc_card_manager() {
    ?>
    
    <div class="nfc-header">
        <h2 class="nfc-title">💚 NFC Card Manager</h2>
        <p class="nfc-subtitle">Manage angel cards for Ship Today orders</p>
    </div>

    <style>
        /* NFC Manager - Angel Hub Dark Theme */
        .nfc-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .nfc-title {
            font-size: 24px;
            font-weight: 600;
            color: #aed604;
            margin: 0 0 8px 0;
        }
        
        .nfc-subtitle {
            font-size: 14px;
            color: #888;
            margin: 0;
        }
        
        /* Mobile Cards View */
        .nfc-cards-container {
            display: grid;
            gap: 20px;
            margin-top: 20px;
        }
        
        .nfc-card {
            background: #1a1a1a;
            border: 2px solid #333;
            border-radius: 14px;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        
        .nfc-card:hover {
            border-color: #444;
        }
        
        .nfc-card-header {
            background: #222;
            padding: 15px 20px;
            border-bottom: 2px solid #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-badge {
            background: rgba(174, 214, 4, 0.2);
            color: #aed604;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .customer-name {
            font-weight: 600;
            color: #fff;
            font-size: 16px;
        }
        
        .nfc-card-body {
            padding: 20px;
        }
        
        .info-grid {
            display: grid;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 10px;
            border-bottom: 1px solid #333;
        }
        
        .info-label {
            color: #666;
            font-size: 13px;
            font-weight: 500;
        }
        
        .info-value {
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            text-align: right;
        }
        
        .info-value.price {
            color: #aed604;
            font-weight: 600;
        }
        
        /* Referral Section */
        .referral-section {
            background: rgba(174, 214, 4, 0.05);
            border: 1px solid rgba(174, 214, 4, 0.2);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .referral-label {
            color: #aed604;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            display: block;
        }
        
        .referral-link-wrapper {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .referral-code {
            flex: 1;
            background: #0a0a0a;
            border: 1px solid #333;
            padding: 10px 14px;
            border-radius: 8px;
            color: #aed604;
            font-family: monospace;
            font-size: 13px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .copy-btn {
            background: #aed604;
            color: #222;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .copy-btn:hover {
            background: #9bc603;
            transform: translateY(-2px);
        }
        
        /* Card Assignment Section */
        .card-assignment {
            margin-bottom: 20px;
        }
        
        .angel-select {
            position: relative;
            width: 100%;
        }
        
        .angel-select select {
            width: 100%;
            background: #0a0a0a;
            color: #fff;
            border: 2px solid #333;
            border-radius: 10px;
            padding: 12px 40px 12px 16px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            appearance: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .angel-select select:hover,
        .angel-select select:focus {
            border-color: #aed604;
            outline: none;
        }
        
        .angel-select::after {
            content: '▼';
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #aed604;
            font-size: 12px;
            pointer-events: none;
        }
        
        /* Status Section */
        .status-section {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }
        .status-bubble {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            justify-content: center;
        }
        
        .status-first {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.3);
        }
        
        .status-needed {
            background: rgba(52, 152, 219, 0.2);
            color: #3498db;
            border: 1px solid rgba(52, 152, 219, 0.3);
        }
        
        .status-issued {
            background: rgba(39, 174, 96, 0.2);
            color: #27ae60;
            border: 1px solid rgba(39, 174, 96, 0.3);
        }
        
        /* Desktop Table - Hidden on Mobile */
        .nfc-table-wrapper {
            display: none;
        }
        
        /* Desktop Styles */
        @media (min-width: 1024px) {
            .nfc-cards-container {
                display: none;
            }
            
            .nfc-table-wrapper {
                display: block;
                background: #1a1a1a;
                border: 2px solid #333;
                border-radius: 14px;
                overflow: hidden;
                margin-top: 20px;
            }
            
            .nfc-table {
                width: 100%;
                border-collapse: collapse;
                font-family: 'Poppins', sans-serif;
            }
            
            .nfc-table thead {
                background: #222;
                border-bottom: 2px solid #333;
            }
            
            .nfc-table th {
                padding: 16px;
                text-align: left;
                font-weight: 600;
                font-size: 13px;
                color: #aed604;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            
            .nfc-table td {
                padding: 16px;
                background: #1a1a1a;
                color: #fff;
                border-top: 1px solid #333;
            }
            
            .nfc-table tbody tr:hover td {
                background: #222;
            }
            
            .nfc-table th:nth-child(1),
            .nfc-table td:nth-child(1) { width: 8%; }
            .nfc-table th:nth-child(2),
            .nfc-table td:nth-child(2) { width: 15%; }
            .nfc-table th:nth-child(3),
            .nfc-table td:nth-child(3) { width: 10%; }
            .nfc-table th:nth-child(4),
            .nfc-table td:nth-child(4) { width: 10%; }
            .nfc-table th:nth-child(5),
            .nfc-table td:nth-child(5) { width: 25%; }
            .nfc-table th:nth-child(6),
            .nfc-table td:nth-child(6) { width: 17%; }
            .nfc-table th:nth-child(7),
            .nfc-table td:nth-child(7) { width: 15%; text-align: center; }
            
            .desktop-referral-link {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .desktop-referral-link .referral-code {
                max-width: 220px;
                padding: 6px 12px;
                font-size: 12px;
            }
            
            .desktop-referral-link .copy-btn {
                padding: 6px 14px;
                font-size: 12px;
            }
            
            .desktop-card-select .angel-select select {
                padding: 8px 35px 8px 14px;
                font-size: 13px;
            }
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #1a1a1a;
            border: 2px solid #333;
            border-radius: 14px;
            margin-top: 20px;
        }
        
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .empty-state-title {
            color: #fff;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .empty-state-text {
            color: #666;
            font-size: 14px;
        }
        
        /* Utility Classes */
        .no-referral {
            color: #666;
            font-style: italic;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .nfc-card {
            animation: fadeIn 0.3s ease;
        }
    </style>

    <script>
        function copyToClipboard(url) {
            navigator.clipboard.writeText(url).then(
                () => {
                    // Find the button that was clicked
                    const btn = event.target;
                    const originalText = btn.textContent;
                    btn.textContent = '✅ Copied!';
                    btn.style.background = '#27ae60';
                    
                    setTimeout(() => {
                        btn.textContent = originalText;
                        btn.style.background = '';
                    }, 2000);
                },
                (err) => {
                    alert('Copy failed: ' + err);
                }
            );
        }
        
        function confirmAndSubmit(select) {
            const value = select.value;
            if (value === 'angel' || value === 'affiliate') {
                const label = value === 'angel' ? 'Angel Card' : 'Angel Affiliate Card';
                if (!confirm(`💎 Are you sure you've written the referral link to the NFC ${label}?`)) {
                    select.value = '';
                    return;
                }
            }
            select.closest('form').submit();
        }
    </script>

    <?php
    // Fetch orders
    $orders = wc_get_orders([
        'status'  => 'ship-today',
        'limit'   => -1,
        'orderby' => 'date',
        'order'   => 'DESC',
    ]);

    if (empty($orders)) {
        ?>
        <div class="empty-state">
            <div class="empty-state-icon">📦</div>
            <h3 class="empty-state-title">No Ship Today Orders</h3>
            <p class="empty-state-text">There are no orders marked as "Ship Today" right now. Check back later!</p>
        </div>
        <?php
        return;
    }

    // Process orders data
    $orders_data          = [];
    $email_orders_cache   = [];
    $referral_code_cache  = [];

    foreach ($orders as $order) {
        $id = $order->get_id();
        $name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        $email = $order->get_billing_email();
        $total = floatval($order->get_total());
        $spend = 0;

        // Get customer history
        if ( ! isset( $email_orders_cache[ $email ] ) ) {
            $email_orders_cache[ $email ] = wc_get_orders([
                'billing_email' => $email,
                'status'       => ['completed', 'processing', 'on-hold', 'ship-today'],
                'limit'        => -1,
            ]);
        }
        $customer_orders = $email_orders_cache[ $email ];
        
        foreach ($customer_orders as $co) {
            $spend += floatval($co->get_total());
        }
        
        // Get referral code
        if ( ! isset( $referral_code_cache[ $email ] ) ) {
            $referral_code_cache[ $email ] = greenangel_get_loyalty_referral_code( $email );
        }
        $referral_code = $referral_code_cache[ $email ];
        $referral_url = $referral_code ? "https://greenangelshop.com?wlr_ref={$referral_code}" : null;
        
        // Card status
        $current_card = get_post_meta($id, '_greenangel_card_issued', true);
        $just_issued = !empty($current_card);
        $previous_order_id = null;
        $previous_card_type = null;
        
        if (empty($current_card)) {
            foreach ($customer_orders as $co) {
                $status = get_post_meta($co->get_id(), '_greenangel_card_status', true);
                $type = get_post_meta($co->get_id(), '_greenangel_card_issued', true);
                if ($status === 'issued' && $type && $co->get_id() !== $id) {
                    $current_card = $type;
                    $previous_order_id = $co->get_id();
                    $previous_card_type = $type;
                    break;
                }
            }
        }
        
        $orders_data[] = [
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'total' => $total,
            'spend' => $spend,
            'referral_url' => $referral_url,
            'current_card' => $current_card,
            'just_issued' => $just_issued,
            'previous_order_id' => $previous_order_id,
            'previous_card_type' => $previous_card_type,
            'order_count' => count($customer_orders),
        ];
    }
    ?>

    <!-- Mobile Cards View -->
    <div class="nfc-cards-container">
        <?php foreach ($orders_data as $data): ?>
            <div class="nfc-card">
                <div class="nfc-card-header">
                    <span class="customer-name"><?php echo esc_html($data['name']); ?></span>
                    <span class="order-badge">#<?php echo $data['id']; ?></span>
                </div>
                
                <div class="nfc-card-body">
                    <div class="info-grid">
                        <div class="info-row">
                            <span class="info-label">Order Total</span>
                            <span class="info-value">£<?php echo number_format($data['total'], 2); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Lifetime Spend</span>
                            <span class="info-value price">£<?php echo number_format($data['spend'], 2); ?></span>
                        </div>
                    </div>
                    
                    <?php if ($data['referral_url']): ?>
                        <div class="referral-section">
                            <span class="referral-label">Referral Link</span>
                            <div class="referral-link-wrapper">
                                <code class="referral-code"><?php echo esc_html($data['referral_url']); ?></code>
                                <button class="copy-btn" onclick="copyToClipboard('<?php echo esc_js($data['referral_url']); ?>')">
                                    📋
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="referral-section">
                            <span class="referral-label">Referral Link</span>
                            <span class="no-referral">No referral code available</span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-assignment">
                        <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
                            <input type="hidden" name="action" value="greenangel_save_card_status">
                            <input type="hidden" name="order_id" value="<?php echo esc_attr($data['id']); ?>">
                            <?php wp_nonce_field('greenangel_save_card_status', 'greenangel_nonce'); ?>
                            
                            <div class="angel-select">
                                <select name="card_issued" onchange="confirmAndSubmit(this)">
                                    <option value="">Select card type...</option>
                                    <option value="angel" <?php selected($data['current_card'], 'angel'); ?>>
                                        💚 Angel Card
                                    </option>
                                    <option value="affiliate" <?php selected($data['current_card'], 'affiliate'); ?>>
                                        💎 Angel Affiliate Card
                                    </option>
                                </select>
                            </div>
                        </form>
                    </div>
                    
                    <div class="status-section">
                        <?php if ($data['previous_order_id'] && $data['previous_card_type']): ?>
                            <span class="status-bubble status-issued">
                                ✅ Already issued on #<?php echo $data['previous_order_id']; ?>
                            </span>
                        <?php elseif ($data['just_issued']): ?>
                            <span class="status-bubble status-issued">
                                ✅ Card assigned just now
                            </span>
                        <?php else: ?>
                            <?php if ($data['order_count'] === 1): ?>
                                <span class="status-bubble status-first">
                                    🆕 1st Order - No Card
                                </span>
                            <?php else: ?>
                                <span class="status-bubble status-needed">
                                    💳 Card Needed
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Desktop Table View -->
    <div class="nfc-table-wrapper">
        <table class="nfc-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Order Total</th>
                    <th>Total Spend</th>
                    <th>Referral Link</th>
                    <th>Card Assigned</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders_data as $data): ?>
                    <tr>
                        <td>
                            <span class="order-badge">#<?php echo $data['id']; ?></span>
                        </td>
                        <td>
                            <strong><?php echo esc_html($data['name']); ?></strong>
                        </td>
                        <td>
                            £<?php echo number_format($data['total'], 2); ?>
                        </td>
                        <td>
                            <span class="info-value price">£<?php echo number_format($data['spend'], 2); ?></span>
                        </td>
                        <td>
                            <?php if ($data['referral_url']): ?>
                                <div class="desktop-referral-link">
                                    <code class="referral-code"><?php echo esc_html($data['referral_url']); ?></code>
                                    <button class="copy-btn" onclick="copyToClipboard('<?php echo esc_js($data['referral_url']); ?>')">
                                        Copy
                                    </button>
                                </div>
                            <?php else: ?>
                                <span class="no-referral">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>" class="desktop-card-select">
                                <input type="hidden" name="action" value="greenangel_save_card_status">
                                <input type="hidden" name="order_id" value="<?php echo esc_attr($data['id']); ?>">
                                <?php wp_nonce_field('greenangel_save_card_status', 'greenangel_nonce'); ?>
                                
                                <div class="angel-select">
                                    <select name="card_issued" onchange="confirmAndSubmit(this)">
                                        <option value="">Select card...</option>
                                        <option value="angel" <?php selected($data['current_card'], 'angel'); ?>>
                                            Angel Card
                                        </option>
                                        <option value="affiliate" <?php selected($data['current_card'], 'affiliate'); ?>>
                                            Affiliate Card
                                        </option>
                                    </select>
                                </div>
                            </form>
                        </td>
                        <td>
                            <?php if ($data['previous_order_id'] && $data['previous_card_type']): ?>
                                <span class="status-bubble status-issued">
                                    ✅ Issued
                                </span>
                            <?php elseif ($data['just_issued']): ?>
                                <span class="status-bubble status-issued">
                                    ✅ Assigned
                                </span>
                            <?php else: ?>
                                <?php if ($data['order_count'] === 1): ?>
                                    <span class="status-bubble status-first">
                                        1st Order
                                    </span>
                                <?php else: ?>
                                    <span class="status-bubble status-needed">
                                        Needed
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
}