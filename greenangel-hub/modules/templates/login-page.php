<?php
/**
 * Template Name: Angel Login Page
 * Description: Custom login page for Green Angel - no more theme fights!
 */

// If user is already logged in, redirect to Angel Hub
if (is_user_logged_in()) {
    $angel_hub = get_page_by_path('angel-hub');
    if ($angel_hub) {
        wp_redirect(get_permalink($angel_hub->ID));
        exit;
    }
}

// Handle login form submission
if (isset($_POST['angel_login'])) {
    $username = sanitize_text_field($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['rememberme']) ? true : false;
    
    // Attempt to log the user in
    $creds = array(
        'user_login'    => $username,
        'user_password' => $password,
        'remember'      => $remember
    );
    
    $user = wp_signon($creds, false);
    
    if (!is_wp_error($user)) {
        // Success! Redirect to Angel Hub
        $angel_hub = get_page_by_path('angel-hub');
        if ($angel_hub) {
            wp_redirect(get_permalink($angel_hub->ID));
        } else {
            wp_redirect(home_url('/my-account/'));
        }
        exit;
    } else {
        $login_error = $user->get_error_message();
    }
}

// Handle registration form submission
if (isset($_POST['angel_register'])) {
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password'];
    $angel_code = sanitize_text_field($_POST['angel_code']);
    $birth_month = sanitize_text_field($_POST['birth_month']);
    $birth_year = sanitize_text_field($_POST['birth_year']);
    
    // Validate Angel Code
    global $wpdb;
    $table = $wpdb->prefix . 'greenangel_codes';
    $valid_code = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE code = %s AND type = 'angel' AND active = 1 AND (expires_at IS NULL OR expires_at > NOW())",
        $angel_code
    ));
    
    if (!$valid_code) {
        $register_error = "Invalid or expired Angel Code.";
    } else {
        // Check if email already exists
        if (email_exists($email)) {
            $register_error = "An account with this email already exists.";
        } else {
            // Create the user
            $user_id = wp_create_user($email, $password, $email);
            
            if (!is_wp_error($user_id)) {
                // Update user meta
                update_user_meta($user_id, 'first_name', $first_name);
                update_user_meta($user_id, 'last_name', $last_name);
                update_user_meta($user_id, 'birth_month', $birth_month);
                update_user_meta($user_id, 'birth_year', $birth_year);
                
                // Update display name
                wp_update_user(array(
                    'ID' => $user_id,
                    'display_name' => $first_name . ' ' . $last_name
                ));
                
                // Log them in automatically
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);
                
                // Redirect to Angel Hub
                $angel_hub = get_page_by_path('angel-hub');
                if ($angel_hub) {
                    wp_redirect(get_permalink($angel_hub->ID));
                } else {
                    wp_redirect(home_url('/my-account/'));
                }
                exit;
            } else {
                $register_error = $user_id->get_error_message();
            }
        }
    }
}

get_header();
?>

<!-- Your Beautiful Login Page HTML -->
<div class="angel-login-page">
    <style>
    /* Page specific resets */
    .angel-login-page {
        min-height: 100vh;
        background: #1a1a1a;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
        margin: -2rem -1rem 0;
        width: calc(100% + 2rem);
    }
    
    /* Hide any theme elements we don't want */
    .site-header,
    .site-footer,
    .breadcrumbs,
    .page-title {
        display: none !important;
    }
    
    /* Your beautiful styles */
    .nm-myaccount-login {
        background: transparent;
        max-width: 600px;
        width: 100%;
        margin: 0 auto;
    }

    #nm-login-wrap,
    #nm-register-wrap {
        background: linear-gradient(135deg, #2a2a2a 0%, #333333 100%);
        padding: 2.5rem;
        border-radius: 16px;
        border: 1px solid #3a3a3a;
        text-align: center;
    }

    #nm-login-wrap h2,
    #nm-register-wrap h2 {
        background: linear-gradient(135deg, #aed604 0%, #c6f731 100%);
        color: #222222;
        padding: 0.6rem 2rem;
        border-radius: 25px;
        font-size: 1.1rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
        margin: 0 auto 2rem;
    }

    .angel-code-message {
        background: rgba(26, 26, 26, 0.9);
        border: 1px solid #333;
        color: #ffffff;
        padding: 1.25rem 1.5rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        font-size: 0.9rem;
        line-height: 1.5;
        text-align: center;
        font-weight: 500;
    }

    form {
        text-align: left;
    }

    .form-row {
        margin-bottom: 1.25rem;
        position: relative;
    }

    .form-row-first,
    .form-row-last {
        width: calc(50% - 0.5rem);
        display: inline-block;
        vertical-align: top;
    }

    .form-row-first {
        margin-right: 0.5rem;
    }

    .form-row-last {
        margin-left: 0.5rem;
    }

    .form-row-wide {
        clear: both;
        width: 100%;
    }

    label {
        color: #ffffff;
        font-size: 0.85rem;
        font-weight: 600;
        display: block;
        margin-bottom: 0.5rem;
        letter-spacing: 0.3px;
    }

    .required {
        color: #ff6b6b;
        font-weight: 700;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    select {
        background: #1a1a1a;
        color: #ffffff;
        border: 1px solid #333;
        border-radius: 12px;
        padding: 1rem;
        width: 100%;
        font-size: 0.9rem;
        font-family: 'Poppins', sans-serif;
        transition: all 0.2s ease;
        box-sizing: border-box;
    }

    input:focus,
    select:focus {
        border-color: #aed604;
        box-shadow: 0 0 0 2px rgba(174, 214, 4, 0.2);
        outline: none;
    }

    select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23aed604' d='M6 8L0 0h12z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        padding-right: 2.5rem;
        -webkit-appearance: none;
        appearance: none;
    }

    .password-input-wrapper {
        position: relative;
    }

    .password-input-wrapper input {
        padding-right: 3rem;
    }

    .show-password-toggle {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: #aed604;
        cursor: pointer;
        font-size: 1.2rem;
        padding: 0;
        height: 30px;
        width: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .login-submit,
    .register-submit {
        background: #e1a003;
        color: #222222;
        border: none;
        border-radius: 12px;
        padding: 1rem 2rem;
        font-size: 1rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        width: 100%;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-top: 1.5rem;
        font-family: 'Poppins', sans-serif;
    }

    .register-submit {
        background: #02a8d1;
    }

    .login-submit:hover,
    .register-submit:hover {
        transform: translateY(-2px);
    }

    .remember-me {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .lost-password {
        display: inline-block;
        margin-top: 0.5rem;
    }

    .lost-password a {
        color: #aed604;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
    }

    .form-divider {
        text-align: center;
        margin: 1.5rem 0;
    }

    .form-divider span {
        background: #444;
        color: #aed604;
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    #show-register-button {
        background: #02a8d1;
        color: #222222;
        border: none;
        border-radius: 12px;
        padding: 1rem 2rem;
        font-size: 1rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        width: 100%;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: block;
        text-align: center;
    }

    #show-register-button:hover {
        background: #0297c0;
        transform: translateY(-2px);
    }

    .error-message {
        background: rgba(244, 67, 54, 0.1);
        border: 1px solid #f44336;
        color: #f44336;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
        text-align: center;
    }

    #nm-register-wrap {
        display: none;
    }

    /* Mobile responsive */
    @media (max-width: 767px) {
        .angel-login-page {
            padding: 1rem;
        }
        
        #nm-login-wrap,
        #nm-register-wrap {
            padding: 1.5rem;
        }
        
        .form-row-first,
        .form-row-last {
            width: 100%;
            margin: 0 0 1rem 0;
        }
    }
    </style>

    <div class="nm-myaccount-login">
        <div class="nm-myaccount-login-inner">
            
            <!-- Login Form -->
            <div id="nm-login-wrap">
                <h2>Log in</h2>
                
                <?php if (isset($login_error)) : ?>
                    <div class="error-message"><?php echo esc_html($login_error); ?></div>
                <?php endif; ?>
                
                <form method="post" class="login-form">
                    <p class="form-row form-row-wide">
                        <label for="username">Username or email address <span class="required">*</span></label>
                        <input type="text" name="username" id="username" required />
                    </p>
                    
                    <p class="form-row form-row-wide">
                        <label for="password">Password <span class="required">*</span></label>
                        <span class="password-input-wrapper">
                            <input type="password" name="password" id="password" required />
                            <button type="button" class="show-password-toggle" aria-label="Show password">
                                <span class="dashicons dashicons-visibility"></span>
                            </button>
                        </span>
                    </p>
                    
                    <p class="form-row">
                        <label class="remember-me">
                            <input name="rememberme" type="checkbox" value="1" /> 
                            <span>Remember me</span>
                        </label>
                        
                        <span class="lost-password">
                            <a href="<?php echo esc_url(wp_lostpassword_url()); ?>">Lost your password?</a>
                        </span>
                    </p>
                    
                    <p class="form-actions">
                        <button type="submit" class="login-submit" name="angel_login" value="1">Log in</button>
                        
                        <div class="form-divider"><span>Or</span></div>
                        
                        <button type="button" id="show-register-button">Create an account</button>
                    </p>
                </form>
            </div>

            <!-- Registration Form -->
            <div id="nm-register-wrap">
                <h2>Register</h2>
                
                <p class="angel-code-message">
                    Registration requires an Angel Code.<br>
                    This is an invitation-only community.
                </p>
                
                <?php if (isset($register_error)) : ?>
                    <div class="error-message"><?php echo esc_html($register_error); ?></div>
                <?php endif; ?>
                
                <form method="post" class="register-form">
                    <p class="form-row form-row-first">
                        <label for="reg_first_name">First name <span class="required">*</span></label>
                        <input type="text" name="first_name" id="reg_first_name" required />
                    </p>
                    
                    <p class="form-row form-row-last">
                        <label for="reg_last_name">Last name <span class="required">*</span></label>
                        <input type="text" name="last_name" id="reg_last_name" required />
                    </p>
                    
                    <p class="form-row form-row-wide">
                        <label for="reg_email">Email address <span class="required">*</span></label>
                        <input type="email" name="email" id="reg_email" required />
                    </p>
                    
                    <p class="form-row form-row-first">
                        <label for="reg_password">Password <span class="required">*</span></label>
                        <span class="password-input-wrapper">
                            <input type="password" name="password" id="reg_password" required />
                            <button type="button" class="show-password-toggle" aria-label="Show password">
                                <span class="dashicons dashicons-visibility"></span>
                            </button>
                        </span>
                    </p>
                    
                    <p class="form-row form-row-last">
                        <label for="angel_code">Angel Code <span class="required">*</span></label>
                        <input type="text" name="angel_code" id="angel_code" required />
                    </p>
                    
                    <p class="form-row form-row-first">
                        <label for="reg_birth_month">Birth Month <span class="required">*</span></label>
                        <select name="birth_month" id="reg_birth_month" required>
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
                        <label for="reg_birth_year">Birth Year <span class="required">*</span></label>
                        <input type="text" name="birth_year" id="reg_birth_year" pattern="[0-9]{4}" maxlength="4" placeholder="YYYY" required />
                    </p>
                    
                    <div style="clear: both;"></div>
                    
                    <p class="form-actions">
                        <button type="submit" class="register-submit" name="angel_register" value="1">Register</button>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password toggle
        const toggleButtons = document.querySelectorAll('.show-password-toggle');
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const wrapper = this.closest('.password-input-wrapper');
                const input = wrapper.querySelector('input');
                const icon = this.querySelector('.dashicons');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('dashicons-visibility');
                    icon.classList.add('dashicons-hidden');
                } else {
                    input.type = 'password';
                    icon.classList.remove('dashicons-hidden');
                    icon.classList.add('dashicons-visibility');
                }
            });
        });
        
        // Show/Hide Registration
        const showRegisterBtn = document.getElementById('show-register-button');
        const loginWrap = document.getElementById('nm-login-wrap');
        const registerWrap = document.getElementById('nm-register-wrap');
        
        if (showRegisterBtn) {
            showRegisterBtn.addEventListener('click', function(e) {
                e.preventDefault();
                loginWrap.style.display = 'none';
                registerWrap.style.display = 'block';
            });
        }
    });
    </script>
</div>

<?php get_footer(); ?>