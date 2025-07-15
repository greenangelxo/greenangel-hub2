<?php
/**
 * GREEN ANGEL HUB v2.0 - MAIN DASHBOARD CONTROLLER
 * Beautiful modular dashboard with premium app-like interface
 * Completely refined with elegant, mobile-optimized components
 * ENHANCED: Full emoji picker integration!
 * 
 * @package GreenAngelHub
 * @version 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue all dashboard styles and scripts
add_action('wp_enqueue_scripts', 'ga_enqueue_dashboard_assets');
function ga_enqueue_dashboard_assets() {
    // Only load on pages with our shortcode
    if (!ga_should_load_dashboard_assets()) {
        return;
    }
    
    $plugin_url = plugin_dir_url(__FILE__);
    $version = defined('WP_DEBUG') && WP_DEBUG ? time() : '2.0.1'; // Bumped version
    
    // Enqueue styles in correct order (shared first, then components)
    wp_enqueue_style('ga-hub-shared', $plugin_url . 'shared.css', [], $version);
    wp_enqueue_style('ga-hub-header', $plugin_url . 'header.css', ['ga-hub-shared'], $version);
    wp_enqueue_style('ga-hub-tiles', $plugin_url . 'tiles.css', ['ga-hub-shared'], $version);
    wp_enqueue_style('ga-hub-referral', $plugin_url . 'referral.css', ['ga-hub-shared'], $version);
    wp_enqueue_style('ga-hub-activity', $plugin_url . 'activity.css', ['ga-hub-shared'], $version);
    wp_enqueue_style('ga-hub-notifications', $plugin_url . 'notifications.css', ['ga-hub-shared'], $version);
    
    // Add Poppins font with better loading
    wp_enqueue_style('ga-hub-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap', [], $version);
    
    // Add mobile viewport optimization
    add_action('wp_head', 'ga_add_mobile_viewport_meta');
}

// Add mobile viewport meta for better mobile experience
function ga_add_mobile_viewport_meta() {
    if (!ga_should_load_dashboard_assets()) {
        return;
    }
    
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">' . "\n";
    echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
    echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
    echo '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">' . "\n";
}

// Include all component files
function ga_load_dashboard_components() {
    $component_path = plugin_dir_path(__FILE__);
    
    $components = [
        'header.php',
        'tiles.php', 
        'referral.php',
        'activity.php',
        'notifications.php'
    ];
    
    foreach ($components as $component) {
        $file_path = $component_path . $component;
        if (file_exists($file_path)) {
            require_once $file_path;
        } else {
            error_log("Green Angel Hub: Component file not found: " . $file_path);
        }
    }
}

// ESSENTIAL FUNCTIONS FROM ORIGINAL DASHBOARD
function greenangel_get_wp_loyalty_points_safe($user_id) {
    global $wpdb;
    
    // Get user email
    $user = get_userdata($user_id);
    if (!$user) return ['available' => 0, 'redeemed' => 0];
    
    $email = $user->user_email;
    
    // Direct database query to WP Loyalty users table
    $table_name = $wpdb->prefix . 'wlr_users';
    
    // Check if table exists first
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    if (!$table_exists) {
        return ['available' => 0, 'redeemed' => 0];
    }
    
    // Using correct column names with error handling
    try {
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
    } catch (Exception $e) {
        error_log("Green Angel Hub: Error fetching loyalty points - " . $e->getMessage());
    }
    
    return ['available' => 0, 'redeemed' => 0];
}

function greenangel_get_recent_activities($user_id, $limit = 100) {
    global $wpdb;
    
    $user = get_userdata($user_id);
    if (!$user) return [];
    
    $email = $user->user_email;
    
    // Define all possible activity tables
    $logs_table = $wpdb->prefix . 'wlr_logs';
    $transaction_table = $wpdb->prefix . 'wlr_earn_campaign_transaction';
    $reward_trans_table = $wpdb->prefix . 'wlr_reward_transactions';
    $points_ledger_table = $wpdb->prefix . 'wlr_points_ledger';
    
    $all_activities = [];
    $activity_ids = [];
    
    // Get from logs table with error handling
    if ($wpdb->get_var("SHOW TABLES LIKE '$logs_table'") == $logs_table) {
        try {
            $log_activities = $wpdb->get_results($wpdb->prepare("
                SELECT 
                    'log' as source,
                    id,
                    user_email,
                    action_type as activity_type,
                    points,
                    order_id,
                    created_at,
                    note,
                    action_process_type,
                    reward_display_name,
                    discount_code
                FROM $logs_table
                WHERE user_email = %s
                ORDER BY created_at DESC, id DESC
                LIMIT %d
            ", $email, $limit));
            
            foreach ($log_activities as $activity) {
                $unique_key = $activity->source . '_' . $activity->id;
                if (!isset($activity_ids[$unique_key])) {
                    $activity_ids[$unique_key] = true;
                    $all_activities[] = $activity;
                }
            }
        } catch (Exception $e) {
            error_log("Green Angel Hub: Error fetching activities - " . $e->getMessage());
        }
    }
    
    // Sort by date and limit
    usort($all_activities, function($a, $b) {
        $date_a = is_numeric($a->created_at) ? $a->created_at : strtotime($a->created_at);
        $date_b = is_numeric($b->created_at) ? $b->created_at : strtotime($b->created_at);
        return $date_b - $date_a;
    });
    
    return array_slice($all_activities, 0, $limit);
}

function greenangel_get_main_angel_code() {
    global $wpdb;
    
    $table = $wpdb->prefix . 'greenangel_codes';
    
    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
        return null;
    }
    
    try {
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
    } catch (Exception $e) {
        error_log("Green Angel Hub: Error fetching angel code - " . $e->getMessage());
    }
    
    return null;
}

// Check if we should load dashboard assets
function ga_should_load_dashboard_assets() {
    global $post;
    
    // Check for shortcode in content
    if (is_singular() && $post && has_shortcode($post->post_content, 'greenangel_account_dashboard')) {
        return true;
    }
    
    // Check for Angel Hub page
    if (is_page('angel-hub')) {
        return true;
    }
    
    // Check for WooCommerce account pages
    if (function_exists('is_account_page') && is_account_page()) {
        return true;
    }
    
    return false;
}

// Main Dashboard Shortcode
function greenangel_account_dashboard_shortcode($atts = []) {
    // Check if user is logged in
    if (!is_user_logged_in()) {
        return ga_render_login_prompt();
    }
    
    // Load all components
    ga_load_dashboard_components();
    
    $user_id = get_current_user_id();
    
    // Get attributes with defaults
    $atts = shortcode_atts([
        'show_header' => 'true',
        'show_tiles' => 'true',
        'show_referral' => 'true',
        'show_activity' => 'true',
        'show_notifications' => 'true',
        'layout' => 'full' // full, compact, minimal
    ], $atts);
    
    ob_start();
    ?>
    
    <div class="ga-hub">
        <div class="ga-container">
            
            <?php
            // Header Section (Profile Banner)
            if ($atts['show_header'] === 'true' && function_exists('ga_render_header_section')) {
                echo ga_render_header_section($user_id);
            }
            
            // Smart Notifications (positioned after header)
            if ($atts['show_notifications'] === 'true' && function_exists('ga_render_notifications_section')) {
                echo ga_render_notifications_section($user_id);
            }
            
            // Navigation Tiles
            if ($atts['show_tiles'] === 'true' && function_exists('ga_render_navigation_tiles')) {
                echo ga_render_navigation_tiles($user_id);
            }
            
            // Referral & Codes Section
            if ($atts['show_referral'] === 'true' && function_exists('ga_render_referral_section')) {
                echo ga_render_referral_section($user_id);
            }
            
            // Activity Tabs (Orders | Halo Points | Wallet)
            if ($atts['show_activity'] === 'true' && function_exists('ga_render_activity_section')) {
                echo ga_render_activity_section($user_id);
            }
            ?>
            
            <!-- Footer Badge -->
            <div class="ga-footer-badge">
                <div class="ga-badge">
                    <span class="ga-badge-icon">🌿</span>
                    <span>Green Angel Hub v2.0</span>
                    <span class="ga-badge-sparkle">✨</span>
                </div>
            </div>
            
            <!-- Spacer for mobile -->
            <div style="height: 2rem;"></div>
            
        </div>
    </div>
    
    <style>
    /* Footer Badge Styling */
    .ga-footer-badge {
        text-align: center;
        margin-top: 2rem;
        margin-bottom: 1rem;
    }
    
    .ga-badge {
        background: linear-gradient(135deg, 
            rgba(174, 214, 4, 0.2) 0%, 
            rgba(198, 247, 49, 0.15) 50%, 
            rgba(174, 214, 4, 0.1) 100%
        );
        border: 1px solid rgba(174, 214, 4, 0.3);
        color: rgba(174, 214, 4, 0.9);
        padding: 0.6rem 1.5rem;
        border-radius: 25px;
        font-size: 0.8rem;
        font-weight: 600;
        font-family: 'Poppins', sans-serif;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }
    
    .ga-badge:hover {
        transform: translateY(-1px);
        border-color: rgba(174, 214, 4, 0.5);
        background: linear-gradient(135deg, 
            rgba(174, 214, 4, 0.25) 0%, 
            rgba(198, 247, 49, 0.2) 50%, 
            rgba(174, 214, 4, 0.15) 100%
        );
    }
    
    .ga-badge-icon {
        font-size: 1rem;
        animation: gentleFloat 3s ease-in-out infinite;
    }
    
    .ga-badge-sparkle {
        font-size: 0.9rem;
        animation: sparkle 2s ease-in-out infinite;
    }
    
    @keyframes gentleFloat {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-2px) rotate(1deg); }
    }
    
    @keyframes sparkle {
        0%, 100% { opacity: 0.6; transform: scale(1); }
        50% { opacity: 1; transform: scale(1.1); }
    }
    
    @media (max-width: 767px) {
        .ga-badge {
            font-size: 0.75rem;
            padding: 0.5rem 1.25rem;
        }
    }
    </style>
    
    <?php
    return ob_get_clean();
}
add_shortcode('greenangel_account_dashboard', 'greenangel_account_dashboard_shortcode');

// 🔐 Enhanced Login Prompt for Non-Logged-In Users
function ga_render_login_prompt() {
    ob_start();
    ?>
    
    <div class="ga-hub">
        <div class="ga-container">
            <div class="ga-login-prompt">
                <div class="ga-login-icon">🌿</div>
                <h2 class="ga-login-title">Welcome to Green Angel Hub</h2>
                <p class="ga-login-description">
                    Please log in to access your personalized dashboard with Halo Points, orders, and exclusive Angel features!
                </p>
                <a href="<?php echo wp_login_url(get_permalink()); ?>" class="ga-login-button">
                    <span>Log In to Your Hub</span>
                    <span>🚀</span>
                </a>
            </div>
        </div>
    </div>
    
    <style>
    .ga-login-prompt {
        background: linear-gradient(145deg, 
            rgba(26, 26, 26, 0.95) 0%, 
            rgba(42, 42, 42, 0.9) 50%, 
            rgba(26, 26, 26, 0.95) 100%
        );
        border: 1px solid rgba(174, 214, 4, 0.2);
        border-radius: 20px;
        padding: 3rem 2rem;
        text-align: center;
        color: #ffffff;
        backdrop-filter: blur(15px);
        max-width: 500px;
        margin: 2rem auto;
    }
    
    .ga-login-icon {
        font-size: 4rem;
        margin-bottom: 1.5rem;
        animation: gentleFloat 4s ease-in-out infinite;
    }
    
    .ga-login-title {
        color: rgba(174, 214, 4, 0.9);
        margin-bottom: 1rem;
        font-size: 1.8rem;
        font-weight: 700;
        font-family: 'Poppins', sans-serif;
    }
    
    .ga-login-description {
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 2rem;
        font-size: 1rem;
        line-height: 1.6;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .ga-login-button {
        background: linear-gradient(135deg, 
            rgba(174, 214, 4, 0.9) 0%, 
            rgba(198, 247, 49, 0.8) 100%
        );
        color: #1a1a1a;
        padding: 1rem 2rem;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 700;
        font-size: 1rem;
        font-family: 'Poppins', sans-serif;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .ga-login-button:hover {
        transform: translateY(-2px);
        text-decoration: none;
        color: #1a1a1a;
        background: linear-gradient(135deg, 
            rgba(174, 214, 4, 1) 0%, 
            rgba(198, 247, 49, 0.9) 100%
        );
        box-shadow: 0 8px 25px rgba(174, 214, 4, 0.3);
    }
    
    @media (max-width: 767px) {
        .ga-login-prompt {
            padding: 2rem 1.5rem;
            margin: 1rem;
        }
        
        .ga-login-icon {
            font-size: 3rem;
        }
        
        .ga-login-title {
            font-size: 1.5rem;
        }
        
        .ga-login-description {
            font-size: 0.9rem;
        }
        
        .ga-login-button {
            font-size: 0.9rem;
            padding: 0.9rem 1.8rem;
        }
    }
    </style>
    
    <?php
    return ob_get_clean();
}

// 🔄 Enhanced Back Button for WooCommerce Account Pages
add_action('woocommerce_before_account_navigation', 'ga_add_enhanced_back_button', 5);
function ga_add_enhanced_back_button() {
    // Only show on WooCommerce account pages, not on Angel Hub
    if (!function_exists('is_account_page') || !is_account_page() || is_page('angel-hub')) {
        return;
    }
    
    // Check if we have an Angel Hub page to go back to
    $angel_hub_page = get_page_by_path('angel-hub');
    if (!$angel_hub_page) {
        return;
    }
    
    $back_url = get_permalink($angel_hub_page->ID);
    ?>
    
    <div class="ga-back-button-container">
        <a href="<?php echo esc_url($back_url); ?>" class="ga-back-button">
            <span class="ga-back-arrow">←</span>
            <span>Back to Angel Hub</span>
        </a>
    </div>
    
    <style>
    .ga-back-button-container {
        display: flex;
        justify-content: center;
        margin-bottom: 1.5rem;
    }
    
    .ga-back-button {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: linear-gradient(135deg, 
            rgba(174, 214, 4, 0.2) 0%, 
            rgba(174, 214, 4, 0.1) 100%
        );
        border: 1px solid rgba(174, 214, 4, 0.3);
        color: rgba(174, 214, 4, 0.9);
        padding: 0.6rem 1.5rem;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
        font-family: 'Poppins', sans-serif;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }
    
    .ga-back-button:hover {
        transform: translateY(-2px);
        text-decoration: none;
        color: rgba(174, 214, 4, 1);
        border-color: rgba(174, 214, 4, 0.5);
        background: linear-gradient(135deg, 
            rgba(174, 214, 4, 0.25) 0%, 
            rgba(174, 214, 4, 0.15) 100%
        );
    }
    
    .ga-back-arrow {
        font-size: 1.1rem;
        transition: transform 0.3s ease;
    }
    
    .ga-back-button:hover .ga-back-arrow {
        transform: translateX(-2px);
    }
    </style>
    
    <?php
}

// Add Dashboard Body Classes
add_filter('body_class', 'ga_add_dashboard_body_classes');
function ga_add_dashboard_body_classes($classes) {
    if (ga_should_load_dashboard_assets()) {
        $classes[] = 'green-angel-hub';
        $classes[] = 'ga-dashboard-active';
        
        // Add mobile class for better targeting
        if (wp_is_mobile()) {
            $classes[] = 'ga-mobile';
        }
    }
    
    return $classes;
}

// 🔧 Dashboard Customization Hooks
add_action('ga_before_dashboard', 'ga_dashboard_maintenance_notice');
function ga_dashboard_maintenance_notice() {
    // Show maintenance notice if needed
    $maintenance_mode = get_option('ga_maintenance_mode', false);
    if ($maintenance_mode) {
        echo '<div class="ga-maintenance-notice">
            <span class="ga-maintenance-icon">🔧</span>
            <span>Dashboard is currently being enhanced. Some features may be temporarily unavailable.</span>
        </div>';
    }
}

// Add Dashboard Admin Bar Menu (for admins)
add_action('admin_bar_menu', 'ga_add_admin_bar_menu', 100);
function ga_add_admin_bar_menu($wp_admin_bar) {
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        return;
    }
    
    $wp_admin_bar->add_menu([
        'id' => 'green-angel-hub',
        'title' => 'Angel Hub',
        'href' => get_permalink(get_page_by_path('angel-hub')),
        'meta' => [
            'title' => 'Green Angel Hub Dashboard'
        ]
    ]);
    
    if (function_exists('admin_url')) {
        $wp_admin_bar->add_node([
            'parent' => 'green-angel-hub',
            'id' => 'ga-hub-settings',
            'title' => 'Hub Settings',
            'href' => admin_url('admin.php?page=green-angel-settings')
        ]);
    }
}

// 🚀 Initialize Dashboard
add_action('init', 'ga_init_dashboard');
function ga_init_dashboard() {
    // Load components early
    ga_load_dashboard_components();
    
    // Add dashboard-specific actions
    do_action('ga_dashboard_init');
}

/**
 * ENHANCED HELPER FUNCTIONS
 */

// Format currency for display
function ga_format_currency($amount) {
    return '£' . number_format($amount, 2);
}

// Get user's Angel level with enhanced logic
function ga_get_user_angel_level($total_spent) {
    if ($total_spent >= 5000) return 'Angel Legend';
    if ($total_spent >= 2500) return 'Elite Angel';
    if ($total_spent >= 1000) return 'VIP Angel';
    if ($total_spent >= 500) return 'Rising Angel';
    return 'New Angel';
}

// Check if user has specific capability
function ga_user_can($capability, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    return user_can($user_id, $capability);
}

// Get dashboard URL
function ga_get_dashboard_url() {
    $hub_page = get_page_by_path('angel-hub');
    return $hub_page ? get_permalink($hub_page) : home_url('/my-account/');
}

// 📊 Enhanced Dashboard Analytics
add_action('ga_dashboard_view', 'ga_track_dashboard_view');
function ga_track_dashboard_view($user_id) {
    // Track dashboard views for analytics
    $view_count = get_user_meta($user_id, 'ga_dashboard_views', true) ?: 0;
    update_user_meta($user_id, 'ga_dashboard_views', $view_count + 1);
    update_user_meta($user_id, 'ga_last_dashboard_view', time());
    
    // Track mobile vs desktop usage
    if (wp_is_mobile()) {
        $mobile_views = get_user_meta($user_id, 'ga_mobile_views', true) ?: 0;
        update_user_meta($user_id, 'ga_mobile_views', $mobile_views + 1);
    }
}

// Fire analytics when shortcode is rendered
add_action('init', function() {
    if (is_user_logged_in() && ga_should_load_dashboard_assets()) {
        do_action('ga_dashboard_view', get_current_user_id());
    }
});

// 🛡️ Security: Rate limiting for AJAX requests
function ga_check_rate_limit($user_id, $action = 'general', $limit = 60, $window = 3600) {
    $transient_key = "ga_rate_limit_{$user_id}_{$action}";
    $current_count = get_transient($transient_key) ?: 0;
    
    if ($current_count >= $limit) {
        return false; // Rate limit exceeded
    }
    
    set_transient($transient_key, $current_count + 1, $window);
    return true;
}

// Performance: Preload critical resources
add_action('wp_head', 'ga_preload_critical_resources', 1);
function ga_preload_critical_resources() {
    if (!ga_should_load_dashboard_assets()) {
        return;
    }
    
    echo '<link rel="preload" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" as="style">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}

// Clean up on plugin deactivation
register_deactivation_hook(__FILE__, 'ga_cleanup_dashboard');
function ga_cleanup_dashboard() {
    // Clean up any transients or temporary data
    global $wpdb;
    
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'ga_rate_limit_%'");
    wp_cache_flush();
}

/**
 * EMOJI PICKER INTEGRATION FUNCTIONS - THE MAGIC HAPPENS HERE!
 */

// 🔄 AJAX ENDPOINT TO REFRESH DASHBOARD AFTER EMOJI SELECTION
add_action('wp_ajax_refresh_dashboard_identity', 'handle_refresh_dashboard_identity');
function handle_refresh_dashboard_identity() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'emoji_identity_nonce')) {
        wp_die('Security check failed');
    }
    
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error('User not logged in');
    }
    
    // Get fresh user meta
    $user_emoji = get_user_meta($user_id, 'angel_identity_emoji', true);
    $user_identity_name = get_user_meta($user_id, 'angel_identity_name', true);
    $user_identity_bio = get_user_meta($user_id, 'angel_identity_bio', true);
    
    wp_send_json_success([
        'emoji' => $user_emoji,
        'identity_name' => $user_identity_name,
        'identity_bio' => $user_identity_bio,
        'has_identity' => !empty($user_emoji) && $user_emoji !== '💎'
    ]);
}

// ENHANCED EMOJI PICKER SUCCESS REDIRECT WITH DASHBOARD REFRESH
add_action('wp_head', 'ga_add_dashboard_refresh_script');
function ga_add_dashboard_refresh_script() {
    if (!is_page('emoji-picker')) {
        return;
    }
    ?>
    <script>
    // ENHANCED: Intercept emoji picker success to refresh dashboard
    document.addEventListener('DOMContentLoaded', function() {
        // Override the success close function to refresh dashboard
        window.originalCloseSuccessCelebration = window.closeSuccessCelebration;
        
        window.closeSuccessCelebration = function() {
            console.log('Enhanced success - refreshing dashboard identity...');
            
            // Call refresh endpoint before redirect
            const nonce = window.emojiPickerData?.nonce || window.emojiPickerNonce || '';
            
            if (nonce) {
                fetch(window.emojiPickerData?.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'refresh_dashboard_identity',
                        nonce: nonce
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('✅ Dashboard identity refreshed:', data.data);
                        
                        // Store identity data for dashboard refresh
                        sessionStorage.setItem('freshIdentity', JSON.stringify({
                            emoji: data.data.emoji,
                            identity_name: data.data.identity_name,
                            identity_bio: data.data.identity_bio,
                            timestamp: Date.now()
                        }));
                    }
                    
                    // Proceed with original redirect
                    if (window.originalCloseSuccessCelebration) {
                        window.originalCloseSuccessCelebration();
                    } else {
                        // Fallback redirect
                        const angelHubUrl = window.emojiPickerBackUrl || '/';
                        window.location.href = angelHubUrl;
                    }
                })
                .catch(error => {
                    console.error('❌ Failed to refresh dashboard identity:', error);
                    // Still proceed with redirect
                    if (window.originalCloseSuccessCelebration) {
                        window.originalCloseSuccessCelebration();
                    }
                });
            } else {
                // No nonce available, proceed normally
                if (window.originalCloseSuccessCelebration) {
                    window.originalCloseSuccessCelebration();
                }
            }
        };
        
        // 🎲 ENHANCED: Also override fate picker success
        if (window.FateEmojiPicker && window.FateEmojiPicker.closeSuccessCelebration) {
            window.originalFateCloseSuccess = window.FateEmojiPicker.closeSuccessCelebration;
            
            window.FateEmojiPicker.closeSuccessCelebration = function() {
                console.log('🎲 Enhanced fate success - refreshing dashboard identity...');
                
                // Same refresh logic for fate picker
                const nonce = window.emojiPickerData?.nonce || window.emojiPickerNonce || '';
                
                if (nonce) {
                    fetch(window.emojiPickerData?.ajaxurl || '/wp-admin/admin-ajax.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'refresh_dashboard_identity',
                            nonce: nonce
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('✅ Fate dashboard identity refreshed:', data.data);
                            
                            // Store identity data for dashboard refresh
                            sessionStorage.setItem('freshIdentity', JSON.stringify({
                                emoji: data.data.emoji,
                                identity_name: data.data.identity_name,
                                identity_bio: data.data.identity_bio,
                                timestamp: Date.now()
                            }));
                        }
                        
                        // Proceed with original redirect
                        if (window.originalFateCloseSuccess) {
                            window.originalFateCloseSuccess();
                        } else {
                            // Fallback redirect
                            const angelHubUrl = window.emojiPickerBackUrl || '/';
                            window.location.href = angelHubUrl;
                        }
                    })
                    .catch(error => {
                        console.error('❌ Failed to refresh fate dashboard identity:', error);
                        // Still proceed with redirect
                        if (window.originalFateCloseSuccess) {
                            window.originalFateCloseSuccess();
                        }
                    });
                } else {
                    // No nonce available, proceed normally
                    if (window.originalFateCloseSuccess) {
                        window.originalFateCloseSuccess();
                    }
                }
            };
        }
    });
    </script>
    <?php
}

// 🏠 DASHBOARD IDENTITY REFRESH ON LOAD
add_action('wp_head', 'ga_add_dashboard_identity_refresh_on_load');
function ga_add_dashboard_identity_refresh_on_load() {
    if (!ga_should_load_dashboard_assets()) {
        return;
    }
    ?>
    <script>
    // Check for fresh identity on dashboard load
    document.addEventListener('DOMContentLoaded', function() {
        const freshIdentity = sessionStorage.getItem('freshIdentity');
        
        if (freshIdentity) {
            try {
                const identity = JSON.parse(freshIdentity);
                const age = Date.now() - identity.timestamp;
                
                // Only use if less than 5 minutes old
                if (age < 300000) {
                    console.log('Found fresh identity, refreshing dashboard...', identity);
                    
                    // Call the refresh function
                    if (window.refreshDashboardIdentity) {
                        setTimeout(() => {
                            window.refreshDashboardIdentity(
                                identity.emoji,
                                identity.identity_name,
                                identity.identity_bio
                            );
                        }, 1000); // Delay for page to settle
                    }
                }
                
                // Clear the stored identity
                sessionStorage.removeItem('freshIdentity');
            } catch (e) {
                console.error('❌ Failed to parse fresh identity:', e);
                sessionStorage.removeItem('freshIdentity');
            }
        }
    });
    </script>
    <?php
}

// ADD ENHANCED IDENTITY CSS TO DASHBOARD PAGES
add_action('wp_head', 'ga_add_enhanced_identity_css');
function ga_add_enhanced_identity_css() {
    if (!ga_should_load_dashboard_assets()) {
        return;
    }
    ?>
    <style>
    /* ENHANCED IDENTITY SUCCESS ANIMATIONS */
    @keyframes identityUpdateSuccess {
        0% {
            opacity: 0;
            transform: translateY(20px) scale(0.9);
        }
        50% {
            opacity: 1;
            transform: translateY(-5px) scale(1.05);
        }
        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    @keyframes badgeVanish {
        0% {
            opacity: 1;
            transform: scale(1);
        }
        50% {
            opacity: 0.5;
            transform: scale(1.2) rotate(10deg);
        }
        100% {
            opacity: 0;
            transform: scale(0) rotate(180deg);
        }
    }
    
    @keyframes glowTransition {
        0% {
            background-position: 0% 50%;
            opacity: 0.6;
        }
        50% {
            background-position: 100% 50%;
            opacity: 1;
        }
        100% {
            background-position: 200% 50%;
            opacity: 0.8;
        }
    }
    
    /* IDENTITY UPDATE CELEBRATION PARTICLES */
    @keyframes celebrationParticle {
        0% {
            opacity: 1;
            transform: translate(0, 0) scale(1) rotate(0deg);
        }
        25% {
            opacity: 1;
            transform: translate(var(--random-x), var(--random-y)) scale(1.2) rotate(90deg);
        }
        50% {
            opacity: 0.8;
            transform: translate(calc(var(--random-x) * 1.5), calc(var(--random-y) * 1.5)) scale(1) rotate(180deg);
        }
        75% {
            opacity: 0.5;
            transform: translate(calc(var(--random-x) * 2), calc(var(--random-y) * 2)) scale(0.8) rotate(270deg);
        }
        100% {
            opacity: 0;
            transform: translate(calc(var(--random-x) * 3), calc(var(--random-y) * 3)) scale(0.3) rotate(360deg);
        }
    }
    
    /* SMOOTH IDENTITY TRANSITIONS */
    .ga-avatar-glow {
        transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .ga-avatar {
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .ga-identity-bio-display {
        animation: identityUpdateSuccess 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .ga-identity-name-tag {
        animation: identityUpdateSuccess 0.6s cubic-bezier(0.4, 0, 0.2, 1) 0.2s both;
    }
    
    .ga-identity-pill {
        animation: identityUpdateSuccess 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.4s both;
    }
    
    /* ENHANCED HOVER STATES FOR IDENTITY ELEMENTS */
    .ga-avatar-link:hover .ga-avatar {
        transform: scale(1.08) rotate(2deg);
    }
    
    .ga-identity-bio-display:hover {
        background: rgba(174, 214, 4, 0.15);
        border-color: rgba(174, 214, 4, 0.4);
        transform: translateY(-1px);
    }
    
    .ga-identity-name-tag:hover {
        color: rgba(174, 214, 4, 1);
        text-shadow: 0 0 8px rgba(174, 214, 4, 0.3);
    }
    
    /* MOBILE OPTIMIZATIONS FOR IDENTITY */
    @media (max-width: 767px) {
        .ga-identity-bio-display {
            font-size: 0.8rem;
            padding: 0.5rem;
            line-height: 1.3;
        }
        
        .ga-identity-name-tag {
            font-size: 0.65rem;
            margin-left: 0.3rem;
        }
        
        .ga-identity-pill {
            padding: 0.4rem 0.8rem;
            min-height: 36px;
        }
        
        .ga-identity-pill-text {
            font-size: 0.75rem;
        }
    }
    
    /* HIGH CONTRAST MODE SUPPORT */
    @media (prefers-contrast: high) {
        .ga-identity-bio-display {
            border-width: 2px;
            background: rgba(174, 214, 4, 0.2);
        }
        
        .ga-identity-name-tag {
            background: rgba(174, 214, 4, 0.1);
            padding: 0.1rem 0.3rem;
            border-radius: 4px;
        }
        
        .ga-identity-pill {
            border-width: 2px;
        }
    }
    
    /* REDUCED MOTION SUPPORT */
    @media (prefers-reduced-motion: reduce) {
        .ga-identity-bio-display,
        .ga-identity-name-tag,
        .ga-identity-pill {
            animation: none;
        }
        
        .ga-avatar-glow,
        .ga-avatar {
            transition: none;
        }
        
        .ga-avatar-link:hover .ga-avatar {
            transform: none;
        }
    }
    </style>
    <?php
}
?>