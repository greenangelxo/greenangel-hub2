<?php
// 🌿 Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// 🌿 Enqueue account dashboard styles
add_action('wp_enqueue_scripts', 'greenangel_enqueue_account_dashboard_styles');
function greenangel_enqueue_account_dashboard_styles() {
    if (!is_singular()) {
        return; // Return from THIS FUNCTION only, not the whole file!
    }
    
    global $post;

    if (has_shortcode($post->post_content, 'greenangel_account_dashboard')) {
        wp_enqueue_style(
            'greenangel-account-dashboard',
            plugin_dir_url(__FILE__) . 'account-dashboard.css',
            [],
            '1.0'
        );
    }
}

// 🌿 BACKUP: Always load (debug)
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    add_action( 'wp_enqueue_scripts', 'greenangel_force_enqueue_account_dashboard_styles', 20 );
    function greenangel_force_enqueue_account_dashboard_styles() {
        wp_enqueue_style(
            'greenangel-account-dashboard-force',
            plugin_dir_url( __FILE__ ) . 'account-dashboard.css',
            [],
            time()
        );
    }
}

// 🌿 WP LOYALTY POINTS - FIXED COLUMN NAMES!
function greenangel_get_wp_loyalty_points_safe($user_id) {
    global $wpdb;
    
    // Get user email
    $user = get_userdata($user_id);
    if (!$user) return ['available' => 0, 'redeemed' => 0];
    
    $email = $user->user_email;
    
    // Direct database query to WP Loyalty users table
    $table_name = $wpdb->prefix . 'wlr_users';
    
    // Check if table exists first
    $table_exists = greenangel_table_exists($table_name);
    if (!$table_exists) {
        return ['available' => 0, 'redeemed' => 0];
    }
    
    // 🎯 FIXED: Using correct column names from your debug!
    $user_data = $wpdb->get_row($wpdb->prepare(
        "SELECT points as available_points, used_total_points as redeemed_points FROM $table_name WHERE user_email = %s",
        $email
    ));
    
    if ($user_data) {
        return [
            'available' => (int) $user_data->available_points,
            'redeemed' => (int) $user_data->redeemed_points
        ];
    }
    
    return ['available' => 0, 'redeemed' => 0];
}

// 🌟 GET EARNING CAMPAIGNS/WAYS TO EARN
function greenangel_get_earning_campaigns() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'wlr_earn_campaign';
    
    // Check if table exists
    $table_exists = greenangel_table_exists($table_name);
    if (!$table_exists) {
        return [];
    }
    
    // Get active earning campaigns
    $campaigns = $wpdb->get_results("SELECT * FROM $table_name WHERE active = 1 ORDER BY ordering ASC");
    
    return $campaigns ?: [];
}


// 🌟 GET ALL ACTIVITIES - combined from WP Loyalty tables
function greenangel_get_recent_activities($user_id, $limit = 100) {
    global $wpdb;

    $user = get_userdata($user_id);
    if (!$user) {
        return [];
    }

    $email = $user->user_email;

    $tables = [
        'logs'   => $wpdb->prefix . 'wlr_logs',
        'earn'   => $wpdb->prefix . 'wlr_earn_campaign_transaction',
        'reward' => $wpdb->prefix . 'wlr_reward_transactions',
        'ledger' => $wpdb->prefix . 'wlr_points_ledger',
    ];

    foreach ($tables as $key => $table) {
        if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) !== $table) {
            unset($tables[$key]);
        }
    }

    if (empty($tables)) {
        return [];
    }

    $parts = [];

    if (isset($tables['logs'])) {
        $parts[] = $wpdb->prepare(
            "SELECT 'log' AS source, id, user_email, action_type AS activity_type, points, order_id, created_at, note, action_process_type, reward_display_name, discount_code, customer_note FROM {$tables['logs']} WHERE user_email = %s",
            $email
        );
    }

    if (isset($tables['earn'])) {
        $parts[] = $wpdb->prepare(
            "SELECT 'earn' AS source, id, user_email, COALESCE(campaign_type, action_type) AS activity_type, points, order_id, created_at, display_name AS note, NULL AS action_process_type, NULL AS reward_display_name, NULL AS discount_code, NULL AS customer_note FROM {$tables['earn']} WHERE user_email = %s",
            $email
        );
    }

    if (isset($tables['reward'])) {
        $parts[] = $wpdb->prepare(
            "SELECT 'redeem' AS source, id, user_email, 'coupon_redeem' AS activity_type, -1 * reward_amount AS points, order_id, created_at, CONCAT('Redeemed ', discount_code) AS note, 'redeem' AS action_process_type, NULL AS reward_display_name, discount_code, NULL AS customer_note FROM {$tables['reward']} WHERE user_email = %s",
            $email
        );
    }

    if (isset($tables['ledger'])) {
        $parts[] = $wpdb->prepare(
            "SELECT 'ledger' AS source, id, user_email, action_type AS activity_type, CASE WHEN credit_points > 0 THEN credit_points WHEN debit_points > 0 THEN -1 * debit_points ELSE 0 END AS points, 0 AS order_id, created_at, note, action_process_type, NULL AS reward_display_name, NULL AS discount_code, NULL AS customer_note FROM {$tables['ledger']} WHERE user_email = %s",
            $email
        );
    }

    $sql = implode(' UNION ALL ', $parts) . ' ORDER BY created_at DESC, id DESC LIMIT %d';
    $sql = $wpdb->prepare($sql, $limit);

    $activities = $wpdb->get_results($sql);

    return $activities ?: [];
}
// 🌿 Custom My Account Dashboard
/**
 * Get Active Main Angel Code
 */
function greenangel_get_main_angel_code() {
    global $wpdb;
    
    $table = $wpdb->prefix . 'greenangel_codes';
    
    // Query for active main code that hasn't expired
    $code = $wpdb->get_row($wpdb->prepare("
        SELECT * FROM $table 
        WHERE type = %s 
        AND active = 1 
        AND (expires_at IS NULL OR expires_at > NOW())
        ORDER BY created_at DESC
        LIMIT 1
    ", 'main'));
    
    if ($code) {
        return $code->code;
    }
    
    return null;
}

function greenangel_account_dashboard() {
    if (!is_user_logged_in()) {
        return '<p class="greenangel-notice">Please log in to view your dashboard.</p>';
    }

    $user_id      = get_current_user_id();
    $user         = wp_get_current_user();
    $display_name = esc_html( $user->display_name ?: $user->user_login );

    // WooCommerce data
    $customer    = new WC_Customer($user_id);
    $orders      = wc_get_orders(['customer' => $user_id, 'limit' => -1]);
    $order_count = count($orders);
    $total_spent = $customer->get_total_spent();

    // 🌟 GET LOYALTY POINTS - SAFE METHOD
    $loyalty_points = greenangel_get_wp_loyalty_points_safe($user_id);
    $halo_points = $loyalty_points['available'];
    $redeemed_points = $loyalty_points['redeemed'];

    // 🌟 GET EARNING CAMPAIGNS
    $earning_campaigns = greenangel_get_earning_campaigns();
    
    // 🌟 GET RECENT ACTIVITIES - NOW WITH MORE DATA!
    $recent_activities = greenangel_get_recent_activities($user_id, 200); // Increased limit to ensure we get all
    
    // Debug: inspect $recent_activities here if needed.

    // First order date
    $first_order_date = 'Not yet placed';
    if (!empty($orders)) {
        $first = end($orders);
        $first_order_date = $first->get_date_created()->format('M Y');
    }

    // Angel status
    $angel_status = 'Member';
    if ($total_spent > 500)  $angel_status = 'VIP Angel';
    if ($total_spent > 1000) $angel_status = 'Elite Angel';

    // 🌟 Fetch WP Loyalty referral code
    if ( function_exists('greenangel_get_loyalty_referral_code') ) {
        $ref_code = greenangel_get_loyalty_referral_code( $user->user_email );
    } else {
        $ref_code = null;
    }
    // Build full dynamic URL
    $ref_url = $ref_code
        ? esc_url( home_url( '?wlr_ref=' . $ref_code ) )
        : '';
    
    // 💌 Get notification settings
    $notification_text = get_option( 'greenangel_notification_text', '' );
    $notification_active = get_option( 'greenangel_notification_active', 'no' );

    ob_start();
    ?>
    <div class="greenangel-dashboard-wrapper">

        <!-- Hero Welcome Section -->
        <div class="ga-hero-section">
            <div class="ga-avatar-section">
                <div class="ga-avatar">🌿</div>
                <div class="ga-welcome-text">
                    <h1 class="ga-welcome">Hey <?php echo $display_name; ?>!</h1>
                    <div class="ga-pills-container">
                        <div class="ga-status-pill">Status: <?php echo $angel_status; ?></div>
                        <div class="ga-angel-since-pill">Angel Since <?php echo $first_order_date; ?></div>
                        <div class="ga-registered-pill">Registered <?php echo date('M Y', strtotime($user->user_registered)); ?></div>
                    </div>
                </div>
            </div>
            
            <?php if ( $notification_active === 'yes' && ! empty( $notification_text ) ) : ?>
            <div class="ga-hero-message-wrapper">
                <div class="ga-hero-message">
                    <div class="ga-message-text"><?php echo wp_kses_post( $notification_text ); ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="ga-points-section">
                <!-- 🔄 Showing Live Halo Points from Database -->
                <div class="ga-halo-points">
                    <div class="ga-points-number"><?php echo number_format($halo_points); ?></div>
                    <div class="ga-points-label">Halo Points</div>
                </div>
                <div class="ga-redeemed-points">
                    <div class="ga-redeemed-number"><?php echo number_format($redeemed_points); ?></div>
                    <div class="ga-redeemed-label">Redeemed</div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Grid -->
        <div class="ga-stats-grid">
            <div class="ga-stat-card">
                <div class="ga-stat-icon">📦</div>
                <div class="ga-stat-content">
                    <div class="ga-stat-number"><?php echo $order_count; ?></div>
                    <div class="ga-stat-label">Orders</div>
                </div>
            </div>
            <a href="<?php echo wc_get_account_endpoint_url('orders'); ?>" class="ga-stat-card ga-stat-clickable">
                <div class="ga-stat-icon">📋</div>
                <div class="ga-stat-content">
                    <div class="ga-stat-number">My Orders</div>
                    <div class="ga-stat-label">View &amp; Track</div>
                </div>
            </a>
            <a href="<?php echo wc_get_account_endpoint_url('edit-address'); ?>" class="ga-stat-card ga-stat-clickable">
                <div class="ga-stat-icon">🏠</div>
                <div class="ga-stat-content">
                    <div class="ga-stat-number">Address Info</div>
                    <div class="ga-stat-label">Update Details</div>
                </div>
            </a>
            <a href="<?php echo wc_get_account_endpoint_url('edit-account'); ?>" class="ga-stat-card ga-stat-clickable">
                <div class="ga-stat-icon">⚙️</div>
                <div class="ga-stat-content">
                    <div class="ga-stat-number">Account</div>
                    <div class="ga-stat-label">Personal Details</div>
                </div>
            </a>
        </div>

        <!-- Codes Section -->
        <div class="ga-codes-section">
            <!-- Referral Link Panel -->
            <div class="ga-code-panel">
                <div class="ga-code-header-pill">Referral Link 🎁</div>
                <div class="ga-code-container">
                    <div
                      class="ga-code"
                      id="referralLink"
                      style="
                        font-family: 'Poppins', sans-serif;
                        font-size: 0.85rem;
                        letter-spacing: 0;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                      "
                    >
                        <?php echo $ref_url ?: '—'; ?>
                    </div>
                    <?php if ( $ref_url ): ?>
                        <button class="ga-copy-btn" onclick="copyReferralLink()">Copy Link</button>
                    <?php else: ?>
                        <button class="ga-copy-btn ga-disabled" disabled>No Link</button>
                    <?php endif; ?>
                </div>
                <p class="ga-code-text">Share with friends and earn rewards!</p>
            </div>

            <!-- Access Code Panel -->
            <?php
            // Get the active main Angel Code
            $angel_code = greenangel_get_main_angel_code();
            ?>
            <div class="ga-code-panel">
                <div class="ga-code-header-pill">Angel Access Code 👑</div>
                <div class="ga-code-container">
                    <?php if ($angel_code): ?>
                        <div
                          class="ga-code"
                          id="accessCode"
                          style="
                            font-family: 'Poppins', sans-serif;
                            font-size: 0.85rem;
                            letter-spacing: 0;
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                          "
                        >
                            <?php echo esc_html($angel_code); ?>
                        </div>
                        <button class="ga-copy-btn" onclick="copyAccessCode()">Copy</button>
                    <?php else: ?>
                        <div
                          class="ga-code"
                          id="accessCode"
                          style="
                            font-family: 'Poppins', sans-serif;
                            font-size: 0.85rem;
                            letter-spacing: 0;
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                            opacity: 0.5;
                          "
                        >
                            No active code
                        </div>
                        <button class="ga-copy-btn ga-disabled" disabled>No Code</button>
                    <?php endif; ?>
                </div>
                <p class="ga-code-text">Share this exclusive code with friends to invite them!</p>
            </div>
        </div>

        <!-- Action Buttons Grid -->
        <div class="ga-actions-grid">
            <a href="/shop" class="ga-action-card">
                <div class="ga-action-icon">🛍️</div>
                <div class="ga-action-content">
                    <div class="ga-action-title">Shop Now</div>
                    <div class="ga-action-subtitle">Explore our products</div>
                </div>
            </a>
            <a href="<?php echo wc_get_account_endpoint_url('loyalty_reward'); ?>" class="ga-action-card">
                <div class="ga-action-icon">🌟</div>
                <div class="ga-action-content">
                    <div class="ga-action-title">Halo Hub</div>
                    <div class="ga-action-subtitle">Spend your points</div>
                </div>
            </a>
            <?php 
            // Check if user is an affiliate
            $is_affiliate = function_exists('slicewp_is_user_affiliate') && slicewp_is_user_affiliate(get_current_user_id());
            ?>
            <a href="<?php echo $is_affiliate ? '/my-account/affiliate/' : '#'; ?>" class="ga-action-card <?php echo !$is_affiliate ? 'ga-locked' : ''; ?>">
                <div class="ga-action-icon"><?php echo $is_affiliate ? '💎' : '🔒'; ?></div>
                <div class="ga-action-content">
                    <div class="ga-action-title">Affiliate Hub</div>
                    <div class="ga-action-subtitle"><?php echo $is_affiliate ? 'Your dashboard' : 'Access locked'; ?></div>
                </div>
            </a>
            <a href="<?php echo wc_get_account_endpoint_url('customer-logout'); ?>" class="ga-action-card">
                <div class="ga-action-icon">🚪</div>
                <div class="ga-action-content">
                    <div class="ga-action-title">Log Out</div>
                    <div class="ga-action-subtitle">See you soon!</div>
                </div>
            </a>
        </div>

        <!-- Order Activity -->
        <?php if (!empty($orders)): ?>
        <div class="ga-panel ga-collapsible">
            <h3 class="ga-panel-title ga-panel-toggle" onclick="togglePanel(this)">
                <span class="ga-title-pill" style="background: #02a8d1;">Order Activity</span>
                <span class="ga-toggle-icon">➕</span>
            </h3>
            <div class="ga-panel-content ga-collapsed">
                <div class="ga-orders-grid" id="ordersGrid">
                    <?php 
                    $order_counter = 0;
                    foreach ($orders as $order): 
                        $order_counter++;
                        $hidden_class = $order_counter > 4 ? 'ga-order-hidden' : '';
                        
                        // Get order status color class
                        $status_class = 'status-' . $order->get_status();
                        
                        // Format order total
                        $order_total = wc_price($order->get_total());
                    ?>
                    <div class="ga-order-card <?php echo $hidden_class; ?>">
                        <div class="ga-order-card-header">
                            <div class="ga-order-number">#<?php echo $order->get_order_number(); ?></div>
                            <div class="ga-order-date"><?php echo $order->get_date_created()->format('M j, Y'); ?></div>
                        </div>
                        <div class="ga-order-card-details">
                            <div class="ga-order-total"><?php echo $order_total; ?></div>
                            <div class="ga-order-items"><?php echo $order->get_item_count(); ?> items</div>
                        </div>
                        <a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="ga-order-status <?php echo $status_class; ?>">
                            <?php echo ucfirst($order->get_status()); ?>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($order_counter > 4): ?>
                <div class="ga-load-more-container">
                    <button class="ga-load-more-btn" onclick="loadMoreOrders()">
                        <span class="ga-load-more-text">Load More Orders</span>
                        <span class="ga-load-more-icon">↓</span>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 🌟 FIXED: Recent Activities - ALL ACTIVITIES, NOT JUST POINTS! -->
        <?php 
        // NO FILTERING! Show ALL activities!
        if (!empty($recent_activities)): 
        ?>
        <div class="ga-panel ga-collapsible">
            <h3 class="ga-panel-title ga-panel-toggle" onclick="togglePanel(this)">
                <span class="ga-title-pill" style="background: #cf11a0;">Halo Activity</span>
                <span class="ga-toggle-icon">➕</span>
            </h3>
            <div class="ga-panel-content ga-collapsed">
                <div class="ga-activities-grid" id="activitiesGrid">
                <?php 
                $activity_counter = 0;
                foreach ($recent_activities as $activity): 
                    $activity_counter++;
                    
                    // Determine activity type and styling based on action_type
                    $activity_icon = '🎁';
                    $activity_type_display = 'Activity';
                    $activity_class = 'general';
                    $points_value = isset($activity->points) ? intval($activity->points) : 0;
                    
                    // Format based on action type from logs table
                    $action_type = strtolower($activity->activity_type ?? $activity->action_type ?? '');
                    
                    if (strpos($action_type, 'point_for_purchase') !== false || strpos($action_type, 'purchase') !== false) {
                        $activity_icon = '🛍️';
                        $activity_type_display = 'Points For Purchase';
                        $activity_class = 'purchase';
                    } elseif (strpos($action_type, 'referral') !== false || strpos($action_type, 'referee') !== false) {
                        $activity_icon = '🤝';
                        $activity_type_display = 'Referral';
                        $activity_class = 'referral';
                    } elseif (strpos($action_type, 'review') !== false) {
                        $activity_icon = '⭐';
                        $activity_type_display = 'Review';
                        $activity_class = 'review';
                    } elseif (strpos($action_type, 'signup') !== false || strpos($action_type, 'sign_up') !== false) {
                        $activity_icon = '👋';
                        $activity_type_display = 'Welcome';
                        $activity_class = 'signup';
                    } elseif (strpos($action_type, 'birthday') !== false) {
                        $activity_icon = '🎂';
                        $activity_type_display = 'Birthday';
                        $activity_class = 'birthday';
                    } elseif (strpos($action_type, 'coupon_redeem') !== false || $activity->source == 'redeem') {
                        $activity_icon = '🎁';
                        $activity_type_display = 'Redeemed';
                        $activity_class = 'redeem';
                    } elseif (strpos($action_type, 'level') !== false || strpos($action_type, 'new_level') !== false) {
                        $activity_icon = '🏆';
                        $activity_type_display = 'New Level';
                        $activity_class = 'level';
                    }
                    
                    // Format order number if available
                    $order_display = '';
                    if (!empty($activity->order_id) && $activity->order_id > 0) {
                        $order_display = '#' . $activity->order_id;
                    }
                    
                    // Format date - handle both timestamp and datetime formats
                    if (is_numeric($activity->created_at)) {
                        $activity_date = date('M j', $activity->created_at);
                    } else {
                        $activity_date = date('M j', strtotime($activity->created_at));
                    }
                    
                    // Handle points display
                    $points_display = '';
                    if ($points_value != 0) {
                        $points_prefix = $points_value > 0 ? '+' : '';
                        $points_display = $points_prefix . number_format(abs($points_value));
                    } else {
                        $points_display = '—'; // Show dash for no points like WP Loyalty does
                    }
                    
                    // Add hidden class if beyond initial 4 items (was 8)
                    $hidden_class = $activity_counter > 4 ? 'ga-activity-hidden' : '';
                ?>
                <div class="ga-activity-card ga-activity-<?php echo $activity_class; ?> <?php echo $hidden_class; ?>">
                    <div class="ga-activity-card-icon"><?php echo $activity_icon; ?></div>
                    <div class="ga-activity-card-content">
                        <div class="ga-activity-card-info">
                            <div class="ga-activity-card-type"><?php echo $activity_type_display; ?></div>
                            <?php if (!empty($activity->note) || !empty($activity->customer_note)): ?>
                            <div class="ga-activity-card-message">
                                <?php 
                                $message = !empty($activity->note) ? $activity->note : $activity->customer_note;
                                // Clean up the message
                                $message = strip_tags($message);
                                $message = str_replace('(admin@greenangelshop.com)', '', $message);
                                $message = trim($message);
                                echo esc_html($message);
                                ?>
                            </div>
                            <?php endif; ?>
                            <div class="ga-activity-card-date"><?php echo $activity_date; ?></div>
                            <?php if ($order_display): ?>
                            <div class="ga-activity-card-order"><?php echo $order_display; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="ga-activity-card-points"><?php echo $points_display; ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
                
                <?php if ($activity_counter > 4): ?>
                <div class="ga-load-more-container">
                    <button class="ga-load-more-btn" onclick="loadMoreActivities()">
                        <span class="ga-load-more-text">Load More Activities</span>
                        <span class="ga-load-more-icon">↓</span>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="ga-panel">
            <h3 class="ga-panel-title">
                <span class="ga-title-pill" style="background: #cf11a0;">Halo Activity 😇</span>
            </h3>
            <p style="text-align: center; color: #999; padding: 2rem;">No activities yet. Start earning by shopping!</p>
        </div>
        <?php endif; ?>

        <!-- 🌟 Earn Halo Points - Now as a separate main panel! -->
        <?php if (!empty($earning_campaigns)): ?>
        <div class="ga-panel ga-collapsible">
            <h3 class="ga-panel-title ga-panel-toggle" onclick="togglePanel(this)">
                <span class="ga-title-pill" style="background: #e1a003;">Earn Halo Points</span>
                <span class="ga-toggle-icon">➕</span>
            </h3>
            <div class="ga-panel-content ga-collapsed">
                <div class="ga-earning-campaigns">
                    <?php foreach ($earning_campaigns as $campaign): 
                        // Parse point rule to extract meaningful info
                        $point_rule = json_decode($campaign->point_rule, true);
                        $points_value = $point_rule['points'] ?? 0;
                        
                        // Determine campaign icon and description
                        $campaign_icon = '🎁';
                        $campaign_desc = $campaign->description ?: $campaign->name;
                        
                        // Campaign type specific formatting
                        if (strpos(strtolower($campaign->action_type), 'purchase') !== false) {
                            $campaign_icon = '🛍️';
                            if ($points_value > 0) {
                                $campaign_desc = $points_value . " Halo Points for each £1.00 spent";
                            }
                        } elseif (strpos(strtolower($campaign->action_type), 'referral') !== false) {
                            $campaign_icon = '🤝';
                            if ($points_value > 0) {
                                $campaign_desc = "You get " . $points_value . " Halo Points, your friend gets " . $points_value . " Halo Points";
                            }
                        } elseif (strpos(strtolower($campaign->action_type), 'review') !== false) {
                            $campaign_icon = '⭐';
                            if ($points_value > 0) {
                                $campaign_desc = "+" . $points_value . " Halo Points for verified reviews";
                            }
                        } elseif (strpos(strtolower($campaign->action_type), 'signup') !== false) {
                            $campaign_icon = '👋';
                            if ($points_value > 0) {
                                $campaign_desc = "+" . $points_value . " Halo Points for joining";
                            }
                        }
                    ?>
                    <div class="ga-earning-card">
                        <div class="ga-earning-icon"><?php echo $campaign_icon; ?></div>
                        <div class="ga-earning-content">
                            <div class="ga-earning-title"><?php echo esc_html($campaign->name); ?></div>
                            <div class="ga-earning-desc"><?php echo esc_html($campaign_desc); ?></div>
                            <?php if ($points_value > 0): ?>
                            <div class="ga-earning-points">+<?php echo number_format($points_value); ?> points</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div> <!-- Close greenangel-dashboard-wrapper -->

    <!-- 🌟 Version Badge -->
    <div class="ga-version-badge">Green Angel Hub v1.0</div>
    
    <!-- Spacer for footer -->
    <div style="height: 3rem;"></div>

    <script>
    function copyReferralLink() {
        const text = document.getElementById('referralLink').textContent;
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.querySelector('.ga-code-panel:first-child .ga-copy-btn');
            const orig = btn.textContent;
            btn.textContent = 'Copied!';
            btn.style.backgroundColor = '#aed604';
            setTimeout(() => {
                btn.textContent = orig;
                btn.style.backgroundColor = '';
            }, 2000);
        });
    }

    function copyAccessCode() {
        const text = document.getElementById('accessCode').textContent;
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.querySelector('.ga-code-panel:last-child .ga-copy-btn');
            const orig = btn.textContent;
            btn.textContent = 'Copied!';
            btn.style.backgroundColor = '#aed604';
            setTimeout(() => {
                btn.textContent = orig;
                btn.style.backgroundColor = '';
            }, 2000);
        });
    }
    
    // 🌟 Toggle Panel Function
    function togglePanel(header) {
        const panel = header.closest('.ga-panel, .ga-inner-panel');
        const content = panel.querySelector('.ga-panel-content');
        const icon = header.querySelector('.ga-toggle-icon');
        
        if (content.classList.contains('ga-collapsed')) {
            content.classList.remove('ga-collapsed');
            content.classList.add('ga-expanded');
            icon.textContent = '➖';
            icon.style.color = '#ffffff';
        } else {
            content.classList.remove('ga-expanded');
            content.classList.add('ga-collapsed');
            icon.textContent = '➕';
            icon.style.color = '#222222';
        }
    }
    
    // 🌟 Load More Activities Function
    function loadMoreActivities() {
        const btn = document.querySelector('.ga-load-more-btn');
        const hiddenActivities = document.querySelectorAll('.ga-activity-hidden');
        const loadMoreText = btn.querySelector('.ga-load-more-text');
        
        // Add loading state
        btn.classList.add('loading');
        loadMoreText.textContent = 'Loading...';
        
        // Simulate loading delay for smooth UX
        setTimeout(() => {
            let count = 0;
            hiddenActivities.forEach((activity, index) => {
                if (index < 4) { // Show next 4 items
                    activity.classList.remove('ga-activity-hidden');
                    activity.classList.add('ga-show');
                    count++;
                }
            });
            
            // Check if there are more hidden items
            const remainingHidden = document.querySelectorAll('.ga-activity-hidden').length;
            
            btn.classList.remove('loading');
            
            if (remainingHidden === 0) {
                // No more items to load
                btn.style.display = 'none';
            } else {
                // Update button text
                loadMoreText.textContent = `Load More Activities (${remainingHidden})`;
            }
        }, 300);
    }
    
    // 🌟 Load More Orders Function
    function loadMoreOrders() {
        const btn = event.target.closest('.ga-load-more-btn');
        const hiddenOrders = document.querySelectorAll('.ga-order-hidden');
        const loadMoreText = btn.querySelector('.ga-load-more-text');
        
        // Add loading state
        btn.classList.add('loading');
        loadMoreText.textContent = 'Loading...';
        
        // Simulate loading delay for smooth UX
        setTimeout(() => {
            let count = 0;
            hiddenOrders.forEach((order, index) => {
                if (index < 4) { // Show next 4 items
                    order.classList.remove('ga-order-hidden');
                    order.classList.add('ga-show');
                    count++;
                }
            });
            
            // Check if there are more hidden items
            const remainingHidden = document.querySelectorAll('.ga-order-hidden').length;
            
            btn.classList.remove('loading');
            
            if (remainingHidden === 0) {
                // No more items to load
                btn.style.display = 'none';
            } else {
                // Update button text
                loadMoreText.textContent = `Load More Orders (${remainingHidden})`;
            }
        }, 300);
    }
    </script>
    <?php
    return ob_get_clean();
}

// Register the shortcode
function greenangel_account_dashboard_wrapper() {
    if (!is_user_logged_in()) {
        return '<p class="greenangel-notice">Please log in to view your dashboard.</p>';
    }

    ob_start();

    echo '<div class="greenangel-dashboard-wrapper">';

    // Check if a WooCommerce endpoint is active
    if (WC()->query->get_current_endpoint()) {
        // 💫 Inject WooCommerce native endpoint content (edit-account, orders, etc.)
        woocommerce_account_content();
    } else {
        // 🌿 Load the custom dashboard view
        echo greenangel_account_dashboard(); // Call your full dashboard function
    }

    echo '</div>';

    return ob_get_clean();
}
add_shortcode('greenangel_account_dashboard', 'greenangel_account_dashboard_wrapper');