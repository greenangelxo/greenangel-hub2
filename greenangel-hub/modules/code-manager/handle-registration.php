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
    $email       = sanitize_email($_POST['email'] ?? '');
    $password    = $_POST['password'] ?? '';
    $angel_code  = sanitize_text_field($_POST['angel_code'] ?? '');
    
    // Store the email to persist across redirect
    setcookie('greenangel_reg_email', $email, time() + 300, '/');
    
    // ✅ Validate inputs
    if (!$email || !$password || !$angel_code) {
        error_log("❌ Missing fields: email=" . ($email ? 'yes' : 'no') . ", password=" . ($password ? 'yes' : 'no') . ", code=" . ($angel_code ? 'yes' : 'no'));
        wp_safe_redirect(add_query_arg('greenangel_error', 'missing_fields', wc_get_page_permalink('myaccount')));
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
    
    // 📜 Log Angel Code usage
    $wpdb->insert($wpdb->prefix . 'greenangel_code_logs', [
        'user_id'    => $user_id,
        'email'      => $email,
        'code_used'  => $angel_code,
        'timestamp'  => current_time('mysql')
    ]);
    
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

// 🎭 Display custom error/success messages 
// We need to inject the message directly into the page since this theme has custom handling
add_action('wp_footer', 'greenangel_show_popup_messages');
function greenangel_show_popup_messages() {
    if (!isset($_GET['greenangel_error']) && !isset($_GET['greenangel_success'])) {
        return;
    }
    
    $message = '';
    $type = 'error';
    
    if (isset($_GET['greenangel_error'])) {
        $error_type = $_GET['greenangel_error'];
        
        switch ($error_type) {
            case 'missing_fields':
                $message = 'Please fill in all required fields.';
                break;
            case 'invalid_code':
                $message = 'Oops! The Angel Code you entered is invalid or expired. Please check and try again.';
                break;
            case 'exists':
                $message = 'An account with this email already exists. Please log in instead.';
                break;
            case 'failed':
                $message = 'Sorry, we couldn\'t create your account. Please try again.';
                break;
            default:
                $message = 'Something unexpected happened. Please try again.';
        }
    } else if (isset($_GET['greenangel_success'])) {
        $message = 'Welcome to Green Angel! Your account has been created.';
        $type = 'success';
    }
    
    if (!empty($message)) {
        // Force register form to show if there's an error
        if ($type == 'error') {
            echo '<script>
                jQuery(document).ready(function($) {
                    // Force the register form to show instead of login
                    $("#nm-login-wrap").hide();
                    $("#nm-register-wrap").show();
                    
                    // Show error message
                    $(".nm-myaccount-login-inner").prepend("<div class=\"woocommerce-error\" role=\"alert\">' . esc_js($message) . '</div>");
                    
                    // Fill in the email if we saved it
                    var savedEmail = "' . (isset($_COOKIE['greenangel_reg_email']) ? esc_js($_COOKIE['greenangel_reg_email']) : '') . '";
                    if (savedEmail) {
                        $("#reg_email").val(savedEmail);
                    }
                });
            </script>';
        } else {
            echo '<script>
                jQuery(document).ready(function($) {
                    $(".nm-myaccount-login-inner").prepend("<div class=\"woocommerce-message\" role=\"alert\">' . esc_js($message) . '</div>");
                });
            </script>';
        }
    }
}

// Trigger the register form to show when error is present
add_action('wp_head', 'greenangel_show_register_form');
function greenangel_show_register_form() {
    if (isset($_GET['greenangel_error'])) {
        echo '<style>
            #nm-login-wrap { display: none !important; }
            #nm-register-wrap { display: block !important; }
        </style>';
    }
}

// 🌟 Add a helpful "Try Again" button to Angel Code error pages
add_action('wp_footer', 'greenangel_enhance_error_page');
function greenangel_enhance_error_page() {
    // Only run on error pages
    if (!isset($_GET['greenangel_error'])) {
        return;
    }
    
    // Use JavaScript to insert the button right after the error message
    echo '<script>
        jQuery(document).ready(function($) {
            // Find the error message
            var errorMessage = $(".woocommerce-error, .woocommerce-notice.woocommerce-notice--error");
            
            if (errorMessage.length) {
                // Insert our button and explanation right after the error message
                errorMessage.after(`
                    <div style="text-align: center; margin: 30px auto;">
                        <a href="' . esc_url(wc_get_page_permalink('myaccount')) . '" class="button" style="display: inline-block; padding: 12px 24px; background-color: #b5e61d; color: #000; text-decoration: none; border-radius: 4px; font-weight: bold;">
                            Try Again
                        </a>
                    </div>
                    <div style="text-align: center; margin: 20px auto; max-width: 600px; color: #888;">
                        <p>Green Angel is an invitation-only community. If you have an Angel Code, please return to the registration form to try again.</p>
                    </div>
                `);
                
                // Hide any duplicate buttons at the bottom if they exist
                $(".button:contains(\'Try Again\')").parent().not(":has(a:first-child)").hide();
            }
        });
    </script>';
}