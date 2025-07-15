<?php
if (!defined('ABSPATH')) {
    exit;
}
// Green Angel – Registration Handler via Angel Code
function greenangel_handle_registration() {
    error_log("Running registration handler...");

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    
    // Security: Verify nonce to prevent CSRF attacks
    if (!wp_verify_nonce($_POST['greenangel_registration_nonce'] ?? '', 'greenangel_registration_action')) {
        error_log("❌ Registration nonce verification failed");
        wp_safe_redirect(add_query_arg('greenangel_error', 'security_failed', wc_get_page_permalink('myaccount')));
        exit;
    }
    
    // Security: Rate limiting - max 3 registration attempts per IP in 10 minutes
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $rate_limit_key = 'greenangel_reg_attempts_' . md5($ip_address);
    $attempts = get_transient($rate_limit_key) ?: 0;
    
    if ($attempts >= 3) {
        error_log("❌ Rate limit exceeded for IP: {$ip_address}");
        wp_safe_redirect(add_query_arg('greenangel_error', 'rate_limit', wc_get_page_permalink('myaccount')));
        exit;
    }
    
    // Increment attempt counter
    set_transient($rate_limit_key, $attempts + 1, 10 * MINUTE_IN_SECONDS);
    
    global $wpdb;

    // Enhanced input sanitization and validation
    $email         = filter_var(sanitize_email($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password      = $_POST['password'] ?? '';
    $angel_code    = sanitize_text_field(trim($_POST['angel_code'] ?? ''));
    $birth_month   = sanitize_text_field($_POST['birth_month'] ?? '');
    $birth_year    = absint($_POST['birth_year'] ?? 0);
    $first_name    = sanitize_text_field(trim($_POST['first_name'] ?? ''));
    $last_name     = sanitize_text_field(trim($_POST['last_name'] ?? ''));
    
    // Validate email format
    if (!$email) {
        error_log("❌ Invalid email format provided");
        wp_safe_redirect(add_query_arg('greenangel_error', 'invalid_email', wc_get_page_permalink('myaccount')));
        exit;
    }
    
    // Validate names contain only letters, spaces, hyphens, apostrophes
    if (!preg_match('/^[a-zA-Z\s\-\']+$/', $first_name) || !preg_match('/^[a-zA-Z\s\-\']+$/', $last_name)) {
        error_log("❌ Invalid name format provided");
        wp_safe_redirect(add_query_arg('greenangel_error', 'invalid_name', wc_get_page_permalink('myaccount')));
        exit;
    }
    
    // Validate angel code format (alphanumeric only)
    if (!preg_match('/^[a-zA-Z0-9]+$/', $angel_code)) {
        error_log("❌ Invalid Angel Code format provided");
        wp_safe_redirect(add_query_arg('greenangel_error', 'invalid_code_format', wc_get_page_permalink('myaccount')));
        exit;
    }
    
    // Validate birth month
    if (!in_array($birth_month, ['01','02','03','04','05','06','07','08','09','10','11','12'])) {
        error_log("❌ Invalid birth month provided");
        wp_safe_redirect(add_query_arg('greenangel_error', 'invalid_birth_month', wc_get_page_permalink('myaccount')));
        exit;
    }
    
    $display_name = sanitize_text_field($first_name . ' ' . $last_name);

    // Store the email to persist across redirect
    setcookie('greenangel_reg_email', $email, time() + 300, '/');

    // Validate inputs
    if (!$email || !$password || !$angel_code || !$birth_month || !$birth_year || !$first_name || !$last_name) {
        error_log("❌ Missing fields: email=" . ($email ? 'yes' : 'no') . ", password=" . ($password ? 'yes' : 'no') . ", code=" . ($angel_code ? 'yes' : 'no') . ", birth_month=" . ($birth_month ? 'yes' : 'no') . ", birth_year=" . ($birth_year ? 'yes' : 'no') . ", first_name=" . ($first_name ? 'yes' : 'no') . ", last_name=" . ($last_name ? 'yes' : 'no'));
        wp_safe_redirect(add_query_arg('greenangel_error', 'missing_fields', wc_get_page_permalink('myaccount')));
        exit;
    }

    // Validate birth year (18-100 years old)
    $current_year = date('Y');
    if ($birth_year < ($current_year - 100) || $birth_year > ($current_year - 18)) {
        error_log("❌ Invalid birth year: $birth_year");
        wp_safe_redirect(add_query_arg('greenangel_error', 'invalid_birthdate', wc_get_page_permalink('myaccount')));
        exit;
    }

    // Security: Check Angel Code in DB using unified structure with enhanced validation
    $table = $wpdb->prefix . 'greenangel_codes';
    
    // Validate table exists
    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) !== $table) {
        error_log("❌ Database table missing: {$table}");
        wp_safe_redirect(add_query_arg('greenangel_error', 'system_error', wc_get_page_permalink('myaccount')));
        exit;
    }
    
    $code = $wpdb->get_row($wpdb->prepare("
        SELECT id, code, active, expires_at, created_at 
        FROM $table 
        WHERE code = %s AND active = 1 
        AND (expires_at IS NULL OR expires_at > NOW())
        LIMIT 1
    ", $angel_code));

    if (!$code) {
        // Security: Enhanced logging of failed attempts
        $failed_table = $wpdb->prefix . 'greenangel_failed_code_attempts';
        $user_agent = substr(sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
        
        $log_result = $wpdb->insert($failed_table, [
            'email' => $email,
            'code_tried' => $angel_code,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'timestamp' => current_time('mysql')
        ]);
        
        if ($log_result === false) {
            error_log("❌ Failed to log security attempt to database");
        }

        error_log("❌ Invalid Angel Code attempt: {$angel_code} from IP: {$_SERVER['REMOTE_ADDR']}");
        wp_safe_redirect(add_query_arg('greenangel_error', 'invalid_code', wc_get_page_permalink('myaccount')));
        exit;
    }

    // 👥 Check if user already exists
    if (email_exists($email)) {
        error_log("❌ Email already exists: $email");
        wp_safe_redirect(add_query_arg('greenangel_error', 'exists', wc_get_page_permalink('myaccount')));
        exit;
    }

    // 🪄 Create user
    $user_id = wp_create_user($email, $password, $email);
    if (is_wp_error($user_id)) {
        error_log("❌ User creation failed for $email: " . $user_id->get_error_message());
        wp_safe_redirect(add_query_arg('greenangel_error', 'failed', wc_get_page_permalink('myaccount')));
        exit;
    }

    // 👤 Update user details
    wp_update_user([
        'ID' => $user_id,
        'first_name'   => $first_name,
        'last_name'    => $last_name,
        'display_name' => $display_name // 💫 This now supports your custom dice name
    ]);

    // Also save as user meta for WooCommerce
    update_user_meta($user_id, 'billing_first_name', $first_name);
    update_user_meta($user_id, 'billing_last_name', $last_name);
    update_user_meta($user_id, 'shipping_first_name', $first_name);
    update_user_meta($user_id, 'shipping_last_name', $last_name);

    // 📜 Log Angel Code usage (unlimited use - no tracking needed!)
    $wpdb->insert($wpdb->prefix . 'greenangel_code_logs', [
        'user_id'    => $user_id,
        'email'      => $email,
        'code_used'  => $angel_code,
        'timestamp'  => current_time('mysql')
    ]);

    // 🎂 Save birthday data (one-time only)
    update_user_meta($user_id, 'birth_month', $birth_month);
    update_user_meta($user_id, 'birth_year', $birth_year);
    error_log("✅ Birthday saved: Month $birth_month, Year $birth_year");

    // 🔐 Auto-login
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, true); // true for "remember me"
    do_action('wp_login', $email, get_user_by('ID', $user_id));

    error_log("✅ Registration successful! User created with ID $user_id, email $email, using code $angel_code (UNLIMITED USE)");
    // ✅ Redirect to Woo My Account with success message
    wp_safe_redirect(add_query_arg('greenangel_success', '1', wc_get_page_permalink('myaccount')));
    exit;

    // 🔄 Final failsafe redirect (shouldn't ever reach here, but just in case!)
    wp_safe_redirect(add_query_arg('greenangel_error', 'unknown', wc_get_page_permalink('myaccount')));
    exit;
}