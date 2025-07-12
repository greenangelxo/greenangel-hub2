<?php
/**
 * Customer Module for Angel Hub
 * Beautiful customer relationship management with Angel Identity focus
 */

// Enqueue styles and scripts for the customer module
add_action('admin_enqueue_scripts', 'greenangel_customers_enqueue_assets');
function greenangel_customers_enqueue_assets($hook) {
    if (strpos($hook, 'greenangel-hub') === false) return;
    
    $plugin_url = plugin_dir_url(__FILE__);
    
    wp_enqueue_style(
        'greenangel-customers-css',
        $plugin_url . 'css/customers.css',
        [],
        '1.0.0'
    );
    
    wp_enqueue_script(
        'greenangel-customers-js',
        $plugin_url . 'js/customers.js',
        ['jquery'],
        '1.0.0',
        true
    );
    
    // Pass AJAX URL and nonce for wallet operations
    wp_localize_script('greenangel-customers-js', 'greenangel_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('greenangel_customers_nonce')
    ]);
}

// Main render function for the customers tab
function greenangel_render_customers_tab() {
    // Handle individual customer profile view
    if (isset($_GET['customer_id'])) {
        greenangel_render_customer_profile();
        return;
    }
    
    // Handle AJAX requests for customer search
    $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $filter_by = isset($_GET['filter']) ? sanitize_text_field($_GET['filter']) : 'all';
    $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = 12;
    
    // FIXED: Only get customers if there's a search query or filter is not 'all'
    $customers = [];
    $total_customers = 0;
    $total_pages = 0;
    
    if (!empty($search_query) || $filter_by !== 'all') {
        $customers = greenangel_get_customers($search_query, $filter_by, $page, $per_page);
        $total_customers = greenangel_get_customers_count($search_query, $filter_by);
        $total_pages = ceil($total_customers / $per_page);
    } else {
        // Get counts for the header stats even when not showing customers
        $total_customers = greenangel_get_all_customers_count();
    }
    
    ?>
    <div class="customers-dashboard">
        <!-- Header Section -->
        <div class="customers-header">
            <div class="header-main">
                <h1 class="customers-title">
                    <span class="title-icon">👥</span>
                    Customer Universe
                </h1>
                <p class="customers-subtitle">Your amazing Angels and their magical journeys ✨</p>
            </div>
            
            <div class="header-stats">
                <div class="stat-card total-customers">
                    <div class="stat-number"><?php echo number_format($total_customers); ?></div>
                    <div class="stat-label">Total Angels</div>
                </div>
                <div class="stat-card paying-customers">
                    <?php $paying_count = greenangel_get_paying_customers_count(); ?>
                    <div class="stat-number"><?php echo number_format($paying_count); ?></div>
                    <div class="stat-label">Paying Angels</div>
                </div>
                <div class="stat-card total-revenue">
                    <?php $total_revenue = greenangel_get_total_customer_revenue(); ?>
                    <div class="stat-number">£<?php echo number_format($total_revenue, 0); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="customers-controls">
            <div class="search-section">
                <div class="search-box">
                    <input type="text" 
                           id="customer-search" 
                           placeholder="Search by name, email, Angel identity..." 
                           value="<?php echo esc_attr($search_query); ?>">
                    <button type="button" class="search-btn">
                        <span class="search-icon">🔍</span>
                    </button>
                </div>
            </div>
            
            <div class="filter-section">
                <select id="customer-filter" class="filter-select">
                    <option value="all" <?php selected($filter_by, 'all'); ?>>All Angels</option>
                    <option value="paying" <?php selected($filter_by, 'paying'); ?>>Paying Customers</option>
                    <option value="high_value" <?php selected($filter_by, 'high_value'); ?>>High Value (£100+)</option>
                    <option value="vip" <?php selected($filter_by, 'vip'); ?>>VIP Angels</option>
                    <option value="new" <?php selected($filter_by, 'new'); ?>>New (Last 30 days)</option>
                    <option value="inactive" <?php selected($filter_by, 'inactive'); ?>>Inactive (90+ days)</option>
                    <option value="has_identity" <?php selected($filter_by, 'has_identity'); ?>>Has Angel Identity</option>
                </select>
            </div>
        </div>

        <!-- Customer Grid -->
        <div class="customers-grid" id="customers-grid">
            <?php if (!empty($search_query) || $filter_by !== 'all'): ?>
                <?php if (!empty($customers)): ?>
                    <?php foreach ($customers as $customer): ?>
                        <?php greenangel_render_customer_card($customer); ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-customers">
                        <div class="no-customers-icon">🔍</div>
                        <h3>No Angels found</h3>
                        <p>Try adjusting your search or filter criteria</p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-customers">
                    <div class="no-customers-icon">👼</div>
                    <h3>Search Your Angels</h3>
                    <p>Use the search box above or select a filter to find your customers</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1 && (!empty($search_query) || $filter_by !== 'all')): ?>
            <div class="customers-pagination">
                <?php 
                $base_url = admin_url('admin.php?page=greenangel-hub&tab=customers');
                $search_param = !empty($search_query) ? '&search=' . urlencode($search_query) : '';
                $filter_param = $filter_by !== 'all' ? '&filter=' . urlencode($filter_by) : '';
                
                // FIXED: Smart pagination - show max 10 pages
                $start_page = max(1, $page - 5);
                $end_page = min($total_pages, $start_page + 9);
                $start_page = max(1, $end_page - 9);
                
                // Previous button
                if ($page > 1): 
                    $prev_url = $base_url . $search_param . $filter_param . '&paged=' . ($page - 1);
                ?>
                    <a href="<?php echo esc_url($prev_url); ?>" class="page-link prev">
                        ← Prev
                    </a>
                <?php endif; ?>
                
                <?php for ($i = $start_page; $i <= $end_page; $i++): 
                    $is_current = ($i === $page);
                    $page_url = $base_url . $search_param . $filter_param . '&paged=' . $i;
                ?>
                    <a href="<?php echo esc_url($page_url); ?>" 
                       class="page-link <?php echo $is_current ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <!-- Next button -->
                <?php if ($page < $total_pages): 
                    $next_url = $base_url . $search_param . $filter_param . '&paged=' . ($page + 1);
                ?>
                    <a href="<?php echo esc_url($next_url); ?>" class="page-link next">
                        Next →
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Live search functionality
        let searchTimeout;
        $('#customer-search').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                filterCustomers();
            }, 500);
        });
        
        // Filter change
        $('#customer-filter').on('change', function() {
            filterCustomers();
        });
        
        function filterCustomers() {
            const search = $('#customer-search').val();
            const filter = $('#customer-filter').val();
            
            // Update URL and reload
            const url = new URL(window.location);
            url.searchParams.set('search', search);
            url.searchParams.set('filter', filter);
            url.searchParams.delete('paged'); // Reset to page 1
            
            window.location.href = url.toString();
        }
    });
    </script>
    <?php
}

// Render individual customer card in the grid
function greenangel_render_customer_card($customer) {
    $user_id = $customer->ID;
    
    // Get Angel Identity data
    $angel_emoji = get_user_meta($user_id, 'angel_identity_emoji', true) ?: '👤';
    $angel_name = get_user_meta($user_id, 'angel_identity_name', true);
    $angel_bio = get_user_meta($user_id, 'angel_identity_bio', true) ?: 'Still discovering their magical powers...';
    
    // FIXED: Show real username/email prominently, Angel name as secondary
    $display_name = $customer->user_login; // Start with username
    $secondary_name = '';
    
    // If it's an email, show just the part before @
    if (strpos($display_name, '@') !== false) {
        $display_name = explode('@', $display_name)[0];
    }
    
    // If they have an Angel identity, show that as secondary info
    if (!empty($angel_name)) {
        $secondary_name = $angel_name;
    } else {
        // If no Angel identity, show their display name or email as secondary
        $secondary_name = $customer->display_name ?: $customer->user_email;
    }
    
    // Get financial data - USE REAL WOOCOMMERCE DATA
    $wallet_balance = floatval(get_user_meta($user_id, 'angel_wallet_balance', true));
    
    // FIXED: Get REAL WooCommerce order data
    $customer = new WC_Customer($user_id);
    $total_spent = $customer->get_total_spent();
    
    // Get actual order count from WooCommerce
    $orders = wc_get_orders([
        'customer_id' => $user_id,
        'status' => ['completed', 'processing', 'on-hold'],
        'limit' => -1,
        'return' => 'ids'
    ]);
    $order_count = count($orders);
    
    // Get engagement data
    $last_login = get_user_meta($user_id, 'last_login_time', true);
    $member_since = get_user_meta($user_id, 'angel_identity_set_date', true) ?: $customer->user_registered;
    $is_paying = get_user_meta($user_id, 'paying_customer', true);
    
    // Calculate member tier
    $member_tier = greenangel_calculate_member_tier($total_spent, $order_count);
    $tier_class = strtolower(str_replace(' ', '-', $member_tier));
    
    // Activity status
    $activity_status = greenangel_get_activity_status($last_login);
    $activity_class = strtolower(str_replace(' ', '-', $activity_status));
    
    ?>
    <div class="customer-card <?php echo esc_attr($tier_class . ' ' . $activity_class); ?>" 
         data-customer-id="<?php echo esc_attr($user_id); ?>">
        
        <!-- Angel Identity Header -->
        <div class="card-header">
            <div class="angel-avatar">
                <span class="emoji-avatar"><?php echo esc_html($angel_emoji); ?></span>
                <div class="activity-indicator <?php echo esc_attr($activity_class); ?>"></div>
            </div>
            
            <div class="angel-identity">
                <h3 class="angel-name"><?php echo esc_html($display_name); ?></h3>
                <p class="angel-secondary"><?php echo esc_html($secondary_name); ?></p>
                <p class="angel-bio"><?php echo esc_html(wp_unslash(wp_trim_words($angel_bio, 8))); ?></p>
            </div>
            
            <div class="member-tier">
                <span class="tier-badge <?php echo esc_attr($tier_class); ?>">
                    <?php echo esc_html($member_tier); ?>
                </span>
            </div>
        </div>

        <!-- Financial Overview -->
        <div class="card-financial">
            <div class="financial-item wallet">
                <div class="financial-value">£<?php echo number_format($wallet_balance, 2); ?></div>
                <div class="financial-label">Wallet Balance</div>
            </div>
            
            <div class="financial-item spent">
                <div class="financial-value">£<?php echo number_format($total_spent, 0); ?></div>
                <div class="financial-label">Total Spent</div>
            </div>
            
            <div class="financial-item orders">
                <div class="financial-value"><?php echo $order_count; ?></div>
                <div class="financial-label">Orders</div>
            </div>
        </div>

        <!-- Engagement Stats -->
        <div class="card-engagement">
            <div class="engagement-item">
                <span class="engagement-label">Last Seen:</span>
                <span class="engagement-value"><?php echo greenangel_format_last_seen($last_login); ?></span>
            </div>
            
            <div class="engagement-item">
                <span class="engagement-label">Angel Since:</span>
                <span class="engagement-value"><?php echo greenangel_format_member_since($member_since); ?></span>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card-actions">
            <button class="action-btn primary" onclick="viewCustomerProfile(<?php echo $user_id; ?>)">
                <span class="btn-icon">👁️</span>
                View Profile
            </button>
            
            <button class="action-btn wallet" onclick="quickWalletAction(<?php echo $user_id; ?>, 'add')">
                <span class="btn-icon">💰</span>
                Add Funds
            </button>
            
            <button class="action-btn email" onclick="emailCustomer(<?php echo $user_id; ?>)">
                <span class="btn-icon">✉️</span>
                Email
            </button>
        </div>
    </div>
    <?php
}

// Render individual customer profile page (trading card style)
function greenangel_render_customer_profile() {
    $customer_id = intval($_GET['customer_id']);
    $customer = get_userdata($customer_id);
    
    if (!$customer) {
        echo '<div class="error-message">Customer not found!</div>';
        return;
    }
    
    // Get all customer data
    $angel_emoji = get_user_meta($customer_id, 'angel_identity_emoji', true) ?: '👤';
    $angel_name = get_user_meta($customer_id, 'angel_identity_name', true);
    $angel_bio = get_user_meta($customer_id, 'angel_identity_bio', true) ?: 'Still discovering their magical powers...';
    $angel_set_date = get_user_meta($customer_id, 'angel_identity_set_date', true);
    $angel_lock = get_user_meta($customer_id, 'angel_identity_lock', true);
    
    // FIXED: Same display logic as cards
    $display_name = $customer->user_login;
    $secondary_name = '';
    
    if (strpos($display_name, '@') !== false) {
        $display_name = explode('@', $display_name)[0];
    }
    
    if (!empty($angel_name)) {
        $secondary_name = $angel_name;
    } else {
        $secondary_name = $customer->display_name ?: $customer->user_email;
    }
    
    // FIXED: Use REAL WooCommerce data for profile page too
    $wallet_balance = floatval(get_user_meta($customer_id, 'angel_wallet_balance', true));
    
    $customer_obj = new WC_Customer($customer_id);
    $total_spent = $customer_obj->get_total_spent();
    
    $orders = wc_get_orders([
        'customer_id' => $customer_id,
        'status' => ['completed', 'processing', 'on-hold'],
        'limit' => -1,
        'return' => 'ids'
    ]);
    $order_count = count($orders);
    
    $last_login = get_user_meta($customer_id, 'last_login_time', true);
    $birth_month = get_user_meta($customer_id, 'birth_month', true);
    $birth_year = get_user_meta($customer_id, 'birth_year', true);
    
    $member_tier = greenangel_calculate_member_tier($total_spent, $order_count);
    $activity_status = greenangel_get_activity_status($last_login);
    
    // Calculate Angel identity status
    $has_identity = !empty($angel_name) && !empty($angel_emoji) && $angel_emoji !== '👤';
    $is_locked = false;
    $lock_expires = '';
    
    if ($angel_lock && is_numeric($angel_lock)) {
        $lock_time = intval($angel_lock);
        if ($lock_time > time()) {
            $is_locked = true;
            $lock_expires = date('F j, Y g:i A', $lock_time);
        }
    }
    
    ?>
    <div class="customer-profile-page">
        <!-- Back Button -->
        <div class="profile-header">
            <button class="back-btn" onclick="history.back()">
                <span class="back-icon">←</span>
                Back to Customer Grid
            </button>
        </div>

        <!-- Trading Card Style Profile -->
        <div class="customer-trading-card">
            <!-- Card Header with Angel Identity -->
            <div class="trading-card-header">
                <div class="hero-avatar">
                    <span class="massive-emoji"><?php echo esc_html($angel_emoji); ?></span>
                    <div class="avatar-glow"></div>
                </div>
                
                <div class="hero-identity">
                    <h1 class="hero-name"><?php echo esc_html($display_name); ?></h1>
                    <p class="hero-real-name"><?php echo esc_html($secondary_name); ?></p>
                    <p class="hero-email"><?php echo esc_html($customer->user_email); ?></p>
                </div>
                
                <div class="hero-tier">
                    <div class="tier-badge-large <?php echo strtolower(str_replace(' ', '-', $member_tier)); ?>">
                        <?php echo esc_html($member_tier); ?>
                    </div>
                    <div class="activity-status <?php echo strtolower(str_replace(' ', '-', $activity_status)); ?>">
                        <?php echo esc_html($activity_status); ?>
                    </div>
                </div>
            </div>

            <!-- Stats Panel (Gaming Style) -->
            <div class="stats-panel">
                <h2 class="stats-title">Angel Statistics</h2>
                
                <div class="stats-grid">
                    <div class="stat-item financial">
                        <div class="stat-icon">💰</div>
                        <div class="stat-info">
                            <div class="stat-value">£<?php echo number_format($wallet_balance, 2); ?></div>
                            <div class="stat-label">Current Wallet</div>
                        </div>
                    </div>
                    
                    <div class="stat-item lifetime">
                        <div class="stat-icon">💎</div>
                        <div class="stat-info">
                            <div class="stat-value">£<?php echo number_format($total_spent, 0); ?></div>
                            <div class="stat-label">Lifetime Spent</div>
                        </div>
                    </div>
                    
                    <div class="stat-item orders">
                        <div class="stat-icon">📦</div>
                        <div class="stat-info">
                            <div class="stat-value"><?php echo $order_count; ?></div>
                            <div class="stat-label">Total Orders</div>
                        </div>
                    </div>
                    
                    <div class="stat-item average">
                        <div class="stat-icon">📊</div>
                        <div class="stat-info">
                            <div class="stat-value">£<?php echo $order_count > 0 ? number_format($total_spent / $order_count, 0) : 0; ?></div>
                            <div class="stat-label">Avg Order Value</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NEW: Angel Identity Management Panel -->
            <div class="angel-identity-management">
                <h2 class="identity-title">Angel Identity Management</h2>
                
                <div class="identity-status-panel">
                    <div class="current-identity">
                        <div class="identity-display">
                            <div class="identity-avatar">
                                <span class="current-emoji"><?php echo esc_html($angel_emoji); ?></span>
                                <?php if ($is_locked): ?>
                                    <div class="lock-overlay">
                                        <span class="lock-icon">🔒</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="identity-details">
                                <?php if ($has_identity): ?>
                                    <h3 class="identity-name"><?php echo esc_html($angel_name); ?></h3>
                                    <p class="identity-bio"><?php echo esc_html(wp_unslash($angel_bio)); ?></p>
                                    
                                    <div class="identity-meta">
                                        <?php if ($angel_set_date): ?>
                                            <div class="meta-item">
                                                <span class="meta-icon">✨</span>
                                                <span class="meta-text">Set on <?php echo date('F j, Y g:i A', strtotime($angel_set_date)); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($is_locked): ?>
                                            <div class="meta-item locked">
                                                <span class="meta-icon">🔒</span>
                                                <span class="meta-text">Locked until <?php echo esc_html($lock_expires); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <div class="meta-item unlocked">
                                                <span class="meta-icon">🔓</span>
                                                <span class="meta-text">Can change identity</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <h3 class="identity-name no-identity">No Angel Identity Set</h3>
                                    <p class="identity-bio">This customer hasn't chosen their Angel identity yet.</p>
                                    
                                    <div class="identity-meta">
                                        <div class="meta-item unlocked">
                                            <span class="meta-icon">👤</span>
                                            <span class="meta-text">Default avatar active</span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="identity-actions">
                        <div class="action-section">
                            <h4>Admin Controls</h4>
                            <p class="action-description">
                                Reset this customer's Angel identity to allow them to choose a new one immediately.
                            </p>
                            
                            <div class="reset-warning">
                                <div class="warning-content">
                                    <span class="warning-icon">⚠️</span>
                                    <div class="warning-text">
                                        <strong>This will:</strong>
                                        <ul>
                                            <li>Clear their current Angel identity</li>
                                            <li>Remove the 30-day lock</li>
                                            <li>Allow immediate reselection</li>
                                            <li>Reset all identity metadata</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="reset-controls">
                                <input type="text" 
                                       id="reset-reason" 
                                       placeholder="Reason for reset (optional, for admin records)"
                                       class="reset-reason-input">
                                
                                <button class="reset-identity-btn" 
                                        onclick="resetAngelIdentity(<?php echo $customer_id; ?>)"
                                        <?php echo !$has_identity ? 'disabled' : ''; ?>>
                                    <span class="btn-icon">🔄</span>
                                    Reset Angel Identity
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bio & Personality Panel -->
            <div class="bio-panel">
                <h2 class="bio-title">Angel Story</h2>
                <div class="bio-content">
                    <p class="angel-bio-full"><?php echo esc_html(wp_unslash($angel_bio)); ?></p>
                    
                    <div class="personal-details">
                            <?php if ($birth_month && $birth_year): ?>
                                <div class="detail-item">
                                    <span class="detail-icon">🎂</span>
                                    <span class="detail-text">Born <?php echo date('F', mktime(0, 0, 0, intval($birth_month), 10)); ?> <?php echo intval($birth_year); ?></span>
                                </div>
                            <?php endif; ?>
                        
                        <?php if ($angel_set_date): ?>
                            <div class="detail-item">
                                <span class="detail-icon">✨</span>
                                <span class="detail-text">Became an Angel on <?php echo date('F j, Y', strtotime($angel_set_date)); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="detail-item">
                            <span class="detail-icon">👥</span>
                            <span class="detail-text">Member since <?php echo date('F j, Y', strtotime($customer->user_registered)); ?></span>
                        </div>
                        
                        <?php if ($last_login): ?>
                            <div class="detail-item">
                                <span class="detail-icon">🕐</span>
                                <span class="detail-text">Last seen <?php echo greenangel_format_last_seen($last_login); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Wallet Management Center -->
            <div class="wallet-management">
                <h2 class="wallet-title">Wallet Management</h2>
                
                <div class="current-balance">
                    <div class="balance-display">
                        <span class="balance-label">Current Balance:</span>
                        <span class="balance-amount" id="current-balance">£<?php echo number_format($wallet_balance, 2); ?></span>
                    </div>
                </div>
                
                <div class="wallet-actions">
                    <div class="action-group add-funds">
                        <h3>Add Funds</h3>
                        <div class="quick-amounts">
                            <button class="amount-btn" data-amount="5">£5</button>
                            <button class="amount-btn" data-amount="10">£10</button>
                            <button class="amount-btn" data-amount="25">£25</button>
                            <button class="amount-btn" data-amount="50">£50</button>
                        </div>
                        
                        <div class="custom-amount">
                            <input type="number" id="add-amount" placeholder="Custom amount" min="0.01" step="0.01">
                            <input type="text" id="add-reason" placeholder="Reason for addition (optional)">
                            <button class="action-button add" onclick="adjustWallet(<?php echo $customer_id; ?>, 'add')">
                                Add Funds
                            </button>
                        </div>
                    </div>
                    
                    <div class="action-group remove-funds">
                        <h3>Remove Funds</h3>
                        <div class="custom-amount">
                            <input type="number" id="remove-amount" placeholder="Amount to remove" min="0.01" step="0.01">
                            <input type="text" id="remove-reason" placeholder="Reason for removal (required)" required>
                            <button class="action-button remove" onclick="adjustWallet(<?php echo $customer_id; ?>, 'remove')">
                                Remove Funds
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction History Section -->
            <div class="transaction-history">
                <h2 class="history-title">Transaction History</h2>
                
                <div class="transaction-list">
                    <?php 
                    // Get transaction history using existing wallet function
                    $transactions = greenangel_get_wallet_transactions($customer_id, 20);
                    
                    if (!empty($transactions)): ?>
                        <?php foreach ($transactions as $transaction): 
                            $is_positive = $transaction->amount >= 0;
                            $icon_class = $is_positive ? 'positive' : 'negative';
                            $icon = $is_positive ? '💰' : '🛍️';
                            
                            // Smart display label detection
                            if ($transaction->type === 'topup') {
                                $type_label = 'Top-up';
                            } elseif ($transaction->type === 'spend') {
                                $type_label = 'Purchase';
                            } elseif (strpos($transaction->comment, 'Converted to coupon:') !== false) {
                                $type_label = 'Coupon Created';
                                $icon = '🎟️';
                                $icon_class = 'neutral';
                            } else {
                                $type_label = 'Manual Adjustment';
                            }
                            
                            $date = date('j M Y, g:i a', strtotime($transaction->timestamp));
                        ?>
                        <div class="transaction-item">
                            <div class="transaction-left">
                                <div class="transaction-icon <?php echo $icon_class; ?>">
                                    <?php echo $icon; ?>
                                </div>
                                <div class="transaction-details">
                                    <h4><?php echo esc_html($type_label); ?></h4>
                                    <p><?php echo esc_html(wp_unslash($transaction->comment) ?: 'Transaction processed'); ?></p>
                                    <div class="transaction-date"><?php echo esc_html($date); ?></div>
                                </div>
                            </div>
                            <div class="transaction-right">
                                <div class="transaction-amount <?php echo $icon_class; ?>">
                                    <?php echo $is_positive ? '+' : ''; ?>£<?php echo number_format(abs($transaction->amount), 2); ?>
                                </div>
                                <?php if ($transaction->order_id): ?>
                                    <div class="order-link">
                                        <a href="<?php echo admin_url('post.php?post=' . $transaction->order_id . '&action=edit'); ?>" target="_blank">
                                            Order #<?php echo $transaction->order_id; ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-transactions">
                            <div class="empty-icon">📱</div>
                            <h4>No transactions yet</h4>
                            <p>Transaction history will appear here when this customer makes purchases or receives wallet adjustments.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="profile-actions">
                <button class="profile-action-btn email" onclick="emailCustomer(<?php echo $customer_id; ?>)">
                    <span class="btn-icon">✉️</span>
                    Send Email
                </button>
                
                <button class="profile-action-btn orders" onclick="viewCustomerOrders(<?php echo $customer_id; ?>)">
                    <span class="btn-icon">📦</span>
                    View Orders
                </button>
                
                <button class="profile-action-btn edit" onclick="editCustomer(<?php echo $customer_id; ?>)">
                    <span class="btn-icon">✏️</span>
                    Edit Profile
                </button>
            </div>
        </div>
    </div>

    <script>
    // Quick amount button functionality
    jQuery(document).ready(function($) {
        $('.amount-btn').click(function() {
            const amount = $(this).data('amount');
            $('#add-amount').val(amount);
            $('.amount-btn').removeClass('selected');
            $(this).addClass('selected');
        });
    });
    </script>
    <?php
}
?>