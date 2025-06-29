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

// ====================================================================
// 🎂 GREEN ANGEL BIRTHDAY FIELDS - EDIT ACCOUNT PAGE
// ====================================================================

// Add birthday fields to account edit form
add_action('woocommerce_edit_account_form', 'greenangel_add_birthday_fields_to_account');
function greenangel_add_birthday_fields_to_account() {
    $user_id = get_current_user_id();
    $birth_month = get_user_meta($user_id, 'birth_month', true);
    $birth_year = get_user_meta($user_id, 'birth_year', true);
    
    ?>
    <div class="greenangel-birthday-section">
        <h3 style="color: #aed604; margin-top: 2rem;">Birthday Information</h3>
        
        <?php if ($birth_month && $birth_year) : ?>
            <!-- Already set - show as read-only -->
            <p class="form-row form-row-first">
                <label>Birth Month</label>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" value="<?php echo date('F', mktime(0, 0, 0, $birth_month, 1)); ?>" readonly style="background: #333; cursor: not-allowed; opacity: 0.7;" />
            </p>
            
            <p class="form-row form-row-last">
                <label>Birth Year</label>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" value="<?php echo esc_attr($birth_year); ?>" readonly style="background: #333; cursor: not-allowed; opacity: 0.7;" />
            </p>
            
            <p style="color: #999; font-size: 0.9em; clear: both;">
                <em>Birthday information cannot be changed once set.</em>
            </p>
        <?php else : ?>
            <!-- Not set - allow entry -->
            <p class="form-row form-row-first">
                <label for="account_birth_month">Birth Month <span class="required">*</span></label>
                <select name="birth_month" id="account_birth_month" class="woocommerce-Input woocommerce-Input--select input-text">
                    <option value="">Select Month</option>
                    <option value="01">January</option>
                    <option value="02">February</option>
                    <option value="03">March</option>
                    <option value="04">April</option>
                    <option value="05">May</option>
                    <option value="06">June</option>
                    <option value="07">July</option>
                    <option value="08">August</option>
                    <option value="09">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>
            </p>
            
            <p class="form-row form-row-last">
                <label for="account_birth_year">Birth Year <span class="required">*</span></label>
                <input type="number" class="woocommerce-Input woocommerce-Input--text input-text" name="birth_year" id="account_birth_year" 
                       min="<?php echo date('Y') - 100; ?>" max="<?php echo date('Y') - 18; ?>" 
                       placeholder="YYYY" />
            </p>
            
            <p style="color: #aed604; font-size: 0.9em; clear: both;">
                <em>⚠️ Birthday information can only be set once and cannot be changed later.</em>
            </p>
        <?php endif; ?>
    </div>
    
    <style>
    /* Birthday Section Styling */
    .greenangel-birthday-section {
        margin-top: 2rem;
        padding: 1.5rem;
        background: rgba(26, 26, 26, 0.5);
        border-radius: 12px;
        border: 1px solid #333;
    }
    
    .greenangel-birthday-section h3 {
        margin-top: 0 !important;
    }
    
    .greenangel-birthday-section select,
    .greenangel-birthday-section input[type="number"] {
        background: #222;
        border: 1px solid #444;
        color: #fff;
        border-radius: 25px;
    }
    
    .greenangel-birthday-section select:focus,
    .greenangel-birthday-section input[type="number"]:focus {
        border-color: #aed604;
        outline: none;
    }
    </style>
    <?php
}

// Save birthday fields from account edit
add_action('woocommerce_save_account_details', 'greenangel_save_birthday_fields');
function greenangel_save_birthday_fields($user_id) {
    // Only save if fields are not already set
    $existing_month = get_user_meta($user_id, 'birth_month', true);
    $existing_year = get_user_meta($user_id, 'birth_year', true);
    
    if (!$existing_month && !empty($_POST['birth_month'])) {
        $birth_month = sanitize_text_field($_POST['birth_month']);
        if (in_array($birth_month, ['01','02','03','04','05','06','07','08','09','10','11','12'])) {
            update_user_meta($user_id, 'birth_month', $birth_month);
        }
    }
    
    if (!$existing_year && !empty($_POST['birth_year'])) {
        $birth_year = absint($_POST['birth_year']);
        $current_year = date('Y');
        if ($birth_year >= ($current_year - 100) && $birth_year <= ($current_year - 18)) {
            update_user_meta($user_id, 'birth_year', $birth_year);
        }
    }
}

// Add validation for required birthday fields on account edit
add_filter('woocommerce_save_account_details_errors', 'greenangel_validate_birthday_fields', 10, 2);
function greenangel_validate_birthday_fields($errors, $user) {
    $user_id = $user->ID;
    $existing_month = get_user_meta($user_id, 'birth_month', true);
    $existing_year = get_user_meta($user_id, 'birth_year', true);
    
    // Only validate if fields don't already exist
    if (!$existing_month || !$existing_year) {
        if (empty($_POST['birth_month'])) {
            $errors->add('birth_month_required', __('Birth month is required.', 'woocommerce'));
        }
        if (empty($_POST['birth_year'])) {
            $errors->add('birth_year_required', __('Birth year is required.', 'woocommerce'));
        }
    }
    
    return $errors;
}

// 🎂 Helper functions for birthday data
function greenangel_get_user_age($user_id) {
    $birth_year = get_user_meta($user_id, 'birth_year', true);
    if ($birth_year) {
        return date('Y') - $birth_year;
    }
    return null;
}

function greenangel_user_has_birthday($user_id) {
    $month = get_user_meta($user_id, 'birth_month', true);
    $year = get_user_meta($user_id, 'birth_year', true);
    return ($month && $year);
}

function greenangel_get_formatted_birthday($user_id) {
    $month = get_user_meta($user_id, 'birth_month', true);
    $year = get_user_meta($user_id, 'birth_year', true);
    
    if ($month && $year) {
        return date('F Y', mktime(0, 0, 0, $month, 1, $year));
    }
    return null;
}