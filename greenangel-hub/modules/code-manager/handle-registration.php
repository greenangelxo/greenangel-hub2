<?php
if (!defined('ABSPATH')) {
    exit;
}
// 🌿 Green Angel – Registration Handler via Angel Code
function greenangel_handle_registration() {
    error_log("💥 Running registration handler...");

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    global $wpdb;

    // 🧼 Sanitize inputs
    $email         = sanitize_email($_POST['email'] ?? '');
    $password      = $_POST['password'] ?? '';
    $angel_code    = sanitize_text_field($_POST['angel_code'] ?? '');
    $birth_month   = sanitize_text_field($_POST['birth_month'] ?? '');
    $birth_year    = absint($_POST['birth_year'] ?? 0);
    $first_name    = sanitize_text_field($_POST['first_name'] ?? '');
    $last_name     = sanitize_text_field($_POST['last_name'] ?? '');
    $display_name  = sanitize_text_field($_POST['display_name'] ?? ($first_name . ' ' . $last_name)); // 💫 New line

    // Store the email to persist across redirect
    setcookie('greenangel_reg_email', $email, time() + 300, '/');

    // ✅ Validate inputs
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

    // 🔎 Check Angel Code in DB
    $table = $wpdb->prefix . 'greenangel_codes';
    $code = $wpdb->get_row($wpdb->prepare("
        SELECT * FROM $table
        WHERE code = %s AND active = 1
        AND (expires_at IS NULL OR expires_at > NOW())
        LIMIT 1
    ", $angel_code));

    if (!$code) {
        // Log the failed attempt
        $wpdb->insert($wpdb->prefix . 'greenangel_failed_code_attempts', [
            'email' => $email,
            'code_tried' => $angel_code,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'timestamp' => current_time('mysql')
        ]);

        error_log("❌ Invalid code used: $angel_code");
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

    // 📜 Log Angel Code usage
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

    error_log("✅ Registration successful! User created with ID $user_id, email $email, using code $angel_code");
    // ✅ Redirect to Woo My Account with success message
    wp_safe_redirect(add_query_arg('greenangel_success', '1', wc_get_page_permalink('myaccount')));
    exit;

    // 🔄 Final failsafe redirect (shouldn't ever reach here, but just in case!)
    wp_safe_redirect(add_query_arg('greenangel_error', 'unknown', wc_get_page_permalink('myaccount')));
    exit;
}