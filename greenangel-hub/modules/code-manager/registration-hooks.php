<?php
// 🌿 Green Angel – Server-side enforcement for Angel Code on all registrations

// Store validated code for later logging
$GLOBALS['greenangel_verified_code'] = null;

/**
 * Validate Angel Code and log failed attempts.
 */
function greenangel_validate_angel_code($email, $angel_code) {
    global $wpdb;
    $angel_code = sanitize_text_field($angel_code);
    $table      = $wpdb->prefix . 'greenangel_codes';
    $code = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE code = %s AND active = 1 AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1",
        $angel_code
    ));

    if (!$code) {
        $wpdb->insert($wpdb->prefix . 'greenangel_failed_code_attempts', [
            'email'      => $email,
            'code_tried' => $angel_code,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'timestamp'  => current_time('mysql'),
        ]);
        return false;
    }
    return true;
}

// 🚫 Validate WooCommerce registration
add_filter('woocommerce_registration_errors', function($errors, $username, $email) {
    $angel_code = $_POST['angel_code'] ?? '';
    if (!$angel_code) {
        $errors->add('angel_code', __('Registration requires an Angel Code.', 'greenangel'));
        return $errors;
    }

    if (!greenangel_validate_angel_code($email, $angel_code)) {
        $errors->add('angel_code', __('Invalid or expired Angel Code.', 'greenangel'));
    } else {
        $GLOBALS['greenangel_verified_code'] = $angel_code;
    }
    return $errors;
}, 10, 3);

// ✅ Log WooCommerce registrations that passed validation
add_action('woocommerce_created_customer', function($customer_id, $data, $password_generated) {
    if (empty($GLOBALS['greenangel_verified_code'])) return;
    global $wpdb;
    $wpdb->insert($wpdb->prefix . 'greenangel_code_logs', [
        'user_id'   => $customer_id,
        'email'     => $data['user_email'],
        'code_used' => $GLOBALS['greenangel_verified_code'],
        'timestamp' => current_time('mysql'),
    ]);
    $GLOBALS['greenangel_verified_code'] = null;
}, 10, 3);

// 🚫 Block default WordPress registrations without a valid Angel Code
add_filter('registration_errors', function($errors, $sanitized_user_login, $user_email) {
    $angel_code = $_POST['angel_code'] ?? '';
    if (!$angel_code) {
        $errors->add('angel_code', __('Registration requires an Angel Code.', 'greenangel'));
        return $errors;
    }

    if (!greenangel_validate_angel_code($user_email, $angel_code)) {
        $errors->add('angel_code', __('Invalid or expired Angel Code.', 'greenangel'));
    } else {
        $GLOBALS['greenangel_verified_code'] = $angel_code;
    }
    return $errors;
}, 10, 3);

// ✅ Log WordPress registrations that passed validation
add_action('user_register', function($user_id) {
    if (empty($GLOBALS['greenangel_verified_code'])) return;
    global $wpdb;
    $user = get_user_by('ID', $user_id);
    $wpdb->insert($wpdb->prefix . 'greenangel_code_logs', [
        'user_id'   => $user_id,
        'email'     => $user ? $user->user_email : '',
        'code_used' => $GLOBALS['greenangel_verified_code'],
        'timestamp' => current_time('mysql'),
    ]);
    $GLOBALS['greenangel_verified_code'] = null;
}, 10);
