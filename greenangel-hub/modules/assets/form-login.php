<?php
/**
 * Angel Hub Login/Register Form
 * Matches the new dashboard design aesthetic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$show_reg_form = ( ! is_checkout() && 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) ? true : false;

if ( isset( $is_popup ) ) {
	$popup_redirect_url = esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
	$popup_redirect_input = sprintf( '<input type="hidden" class="nm-login-popup-redirect-input" name="redirect" value="%s" />', $popup_redirect_url );
	$popup_form_action_escaped = sprintf( ' action="%s"', $popup_redirect_url );
} else {
	$popup_redirect_input = $popup_form_action_escaped = '';
}
?>

<style>
/* Angel Hub Login/Register Form Styling */
.nm-myaccount-login {
    background: transparent !important;
    padding: 0 !important;
    border-radius: 0 !important;
    max-width: 600px !important;
    margin: 1rem auto !important;
    border: none !important;
    width: 100% !important;
}

.nm-myaccount-login-inner {
    background: transparent !important;
}

/* Form Containers */
#nm-login-wrap,
#nm-register-wrap {
    background: linear-gradient(135deg, #2a2a2a 0%, #333333 100%) !important;
    padding: 2.5rem !important;
    border-radius: 16px !important;
    border: 1px solid #3a3a3a !important;
}

/* Headers - Pill Style like Dashboard */
#nm-login-wrap h2 {
    background: linear-gradient(135deg, #aed604 0%, #c6f731 100%) !important;
    color: #222222 !important;
    padding: 0.6rem 2rem !important;
    border-radius: 25px !important;
    font-size: 1.1rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    display: inline-block !important;
    margin: 0 auto 2rem !important;
    text-align: center !important;
    width: auto !important;
}

#nm-register-wrap h2 {
    background: linear-gradient(135deg, #aed604 0%, #c6f731 100%) !important;
    color: #222222 !important;
    padding: 0.6rem 2rem !important;
    border-radius: 25px !important;
    font-size: 1.1rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    display: inline-block !important;
    margin: 0 auto 2rem !important;
    text-align: center !important;
    width: auto !important;
}

/* Center the headers */
#nm-login-wrap,
#nm-register-wrap {
    text-align: center !important;
}

#nm-login-wrap form,
#nm-register-wrap form {
    text-align: left !important;
}

/* Angel Code Message Box */
.angel-code-message {
    background: rgba(26, 26, 26, 0.9) !important;
    border: 1px solid #333 !important;
    color: #ffffff !important;
    padding: 1.25rem 1.5rem !important;
    border-radius: 16px !important;
    margin-bottom: 2rem !important;
    font-size: 0.9rem !important;
    line-height: 1.5 !important;
    text-align: center !important;
    font-weight: 500 !important;
}

/* Form Row Styling - Two Column Layout */
.form-row {
    margin-bottom: 1.25rem !important;
    position: relative !important;
}

.form-row-first,
.form-row-last {
    width: calc(50% - 0.5rem) !important;
    display: inline-block !important;
    vertical-align: top !important;
}

.form-row-first {
    margin-right: 0.5rem !important;
}

.form-row-last {
    margin-left: 0.5rem !important;
}

.form-row-wide {
    clear: both !important;
    width: 100% !important;
}

/* Labels - Clean and Simple without Pills */
#nm-login-wrap label,
#nm-register-wrap label {
    color: #ffffff !important;
    font-size: 0.85rem !important;
    font-weight: 600 !important;
    display: inline-block !important;
    margin-bottom: 0.5rem !important;
    text-transform: none !important;
    letter-spacing: 0.3px !important;
    background: transparent !important;
    padding: 0 !important;
    border-radius: 0 !important;
}

/* Required asterisks */
.required {
    color: #ff6b6b !important;
    font-weight: 700 !important;
}

/* Input Fields - Dark like Dashboard */
#nm-login-wrap input[type="text"],
#nm-login-wrap input[type="email"],
#nm-login-wrap input[type="password"],
#nm-register-wrap input[type="text"],
#nm-register-wrap input[type="email"],
#nm-register-wrap input[type="password"],
#nm-register-wrap input[type="number"],
#nm-register-wrap select {
    background: #1a1a1a !important;
    color: #ffffff !important;
    border: 1px solid #333 !important;
    border-radius: 12px !important;
    padding: 1rem !important;
    padding-right: 3rem !important; /* Space for password toggle */
    width: 100% !important;
    font-size: 0.9rem !important;
    font-family: 'Poppins', sans-serif !important;
    transition: all 0.2s ease !important;
    box-shadow: none !important;
    outline: none !important;
    box-sizing: border-box !important;
}

/* Remove extra padding for non-password fields */
#nm-login-wrap input[type="text"]:not(#password),
#nm-login-wrap input[type="email"],
#nm-register-wrap input[type="text"]:not(#reg_password),
#nm-register-wrap input[type="email"],
#nm-register-wrap input[type="number"],
#nm-register-wrap select {
    padding-right: 1rem !important;
}

/* Focus States */
#nm-login-wrap input:focus,
#nm-register-wrap input:focus,
#nm-register-wrap select:focus {
    border-color: #aed604 !important;
    box-shadow: 0 0 0 2px rgba(174, 214, 4, 0.2) !important;
    outline: none !important;
}

/* Select Dropdown Styling */
#nm-register-wrap select {
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    appearance: none !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23aed604' d='M6 8L0 0h12z'/%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right 1rem center !important;
    padding-right: 2.5rem !important;
}

/* Select Options */
#nm-register-wrap select option {
    background: #1a1a1a !important;
    color: #ffffff !important;
}

/* Password Toggle Wrapper */
.password-input-wrapper {
    position: relative !important;
    width: 100% !important;
}

/* Password Strength Meter - HIDE IT */
#nm-register-wrap .woocommerce-password-strength,
#nm-register-wrap .woocommerce-password-hint,
#nm-register-wrap .password-strength,
#nm-register-wrap .password-hint,
#nm-register-wrap small.woocommerce-password-hint {
    display: none !important;
    height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    opacity: 0 !important;
    visibility: hidden !important;
}

/* Fix Password Field Container */
#nm-register-wrap .form-row-first,
#nm-register-wrap .form-row-last {
    position: relative !important;
    overflow: hidden !important;
}

/* Password Toggle Button */
.show-password-toggle {
    position: absolute !important;
    right: 1rem !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    background: transparent !important;
    border: none !important;
    color: #aed604 !important;
    cursor: pointer !important;
    font-size: 1.2rem !important;
    padding: 0 !important;
    z-index: 10 !important;
    transition: color 0.2s ease !important;
    height: auto !important;
    width: auto !important;
    line-height: 1 !important;
}

.show-password-toggle:hover {
    color: #c6f731 !important;
}

/* Buttons - Matching Dashboard Style */
.woocommerce-form-login__submit {
    background: #e1a003 !important;
    color: #222222 !important;
    border: none !important;
    border-radius: 12px !important;
    padding: 1rem 2rem !important;
    font-size: 1rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    width: 100% !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    margin-top: 1.5rem !important;
}

.woocommerce-form-register__submit {
    background: #02a8d1 !important;
    color: #222222 !important;
    border: none !important;
    border-radius: 12px !important;
    padding: 1rem 2rem !important;
    font-size: 1rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    width: 100% !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    margin-top: 1.5rem !important;
}

.woocommerce-form-login__submit:hover,
.woocommerce-form-register__submit:hover {
    transform: translateY(-2px) !important;
}

.woocommerce-form-login__submit:active,
.woocommerce-form-register__submit:active {
    transform: scale(0.98) !important;
}

/* Remember Me Checkbox - Clean Style */
.woocommerce-form-login__rememberme {
    display: flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    margin-bottom: 0.5rem !important; /* Reduced from default */
}

.woocommerce-form-login__rememberme label {
    color: #ffffff !important;
    font-size: 0.85rem !important;
    font-weight: 600 !important;
    margin: 0 !important;
    background: transparent !important;
    padding: 0 !important;
    border-radius: 0 !important;
}

/* Lost Password Link - Clean Style */
.lost_password a {
    color: #aed604 !important;
    font-size: 0.85rem !important;
    font-weight: 600 !important;
    text-decoration: none !important;
    transition: opacity 0.2s ease !important;
    background: transparent !important;
    padding: 0 !important;
    border-radius: 0 !important;
    display: inline-block !important;
}

.lost_password a:hover {
    opacity: 0.8 !important;
    text-decoration: none !important;
}

/* Checkbox Styling */
input[type="checkbox"] {
    width: auto !important;
    accent-color: #aed604 !important;
}

/* Links */
.lost_password a,
.nm-login-form-divider a {
    color: #aed604 !important;
    text-decoration: none !important;
    font-weight: 600 !important;
    transition: color 0.2s ease !important;
}

.lost_password a:hover,
.nm-login-form-divider a:hover {
    color: #c6f731 !important;
    text-decoration: underline !important;
}

/* Form Actions Container */
.form-actions {
    margin-top: 2rem !important;
}

/* Or Divider */
.nm-login-form-divider {
    text-align: center !important;
    margin: 1.5rem 0 !important;
    position: relative !important;
}

.nm-login-form-divider span {
    background: #444 !important;
    color: #aed604 !important;
    padding: 0.4rem 1rem !important;
    border-radius: 20px !important;
    font-size: 0.8rem !important;
    font-weight: 600 !important;
}

/* Create Account Button - NOW WITH GORGEOUS BLUE! */
#nm-show-register-button {
    background: #02a8d1 !important;
    color: #222222 !important;
    border: none !important;
    border-radius: 12px !important;
    padding: 1rem 2rem !important;
    font-size: 1rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    width: 100% !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    text-decoration: none !important;
    display: inline-block !important;
    text-align: center !important;
}

#nm-show-register-button:hover {
    background: #0297c0 !important;
    transform: translateY(-2px) !important;
}

/* Newsletter Checkbox - NUCLEAR OPTION */
#nm-register-wrap p:has(input[name="newsletter"]),
#nm-register-wrap .form-row:has(input[name="newsletter"]),
#nm-register-wrap label[for="newsletter"],
#nm-register-wrap input[name="newsletter"],
.woocommerce-privacy-policy-text + p,
p:contains("Subscribe to our newsletter"),
.newsletter-checkbox-wrapper {
    display: none !important;
    visibility: hidden !important;
    height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    position: absolute !important;
    left: -9999px !important;
    opacity: 0 !important;
    pointer-events: none !important;
}

/* CAPTCHA STYLING - Clean Approach */
/* CAPTCHA PARAGRAPH - TIGHT SPACING */
#nm-login-wrap p:has(label[for*="wpcaptcha_captcha"]) {
    margin-bottom: 0.75rem !important; /* Reduced gap after captcha */
}

/* Style the captcha paragraph wrapper */
#nm-login-wrap p:has(label[for*="wpcaptcha_captcha"]),
#nm-register-wrap p:has(label[for*="wpcaptcha_captcha"]) {
    text-align: center !important;
    margin-bottom: 1.25rem !important;
}

/* Keep the label but style it cleanly */
#nm-login-wrap label[for*="wpcaptcha_captcha"],
#nm-register-wrap label[for*="wpcaptcha_captcha"] {
    color: #ffffff !important;
    font-size: 0.85rem !important;
    font-weight: 600 !important;
    display: inline-block !important;
    margin-bottom: 0.5rem !important;
    margin-right: 0.5rem !important;
    background: transparent !important;
    padding: 0 !important;
    border-radius: 0 !important;
}

/* Style the math equation */
#nm-login-wrap label[for*="wpcaptcha_captcha"] + img,
#nm-login-wrap label[for*="wpcaptcha_captcha"] + strong,
#nm-register-wrap label[for*="wpcaptcha_captcha"] + img,
#nm-register-wrap label[for*="wpcaptcha_captcha"] + strong {
    color: #ffffff !important;
    font-size: 1rem !important;
    font-weight: 600 !important;
    vertical-align: middle !important;
}

/* Center and style the input */
#nm-login-wrap input.wpcaptcha_captcha,
#nm-login-wrap input[id*="wpcaptcha_captcha"],
#nm-register-wrap input.wpcaptcha_captcha,
#nm-register-wrap input[id*="wpcaptcha_captcha"] {
    background: #1a1a1a !important;
    color: #ffffff !important;
    border: 1px solid #333 !important;
    border-radius: 12px !important;
    padding: 1rem !important;
    width: 200px !important;
    font-size: 0.9rem !important;
    margin: 0.5rem auto 0 !important;
    display: block !important;
    box-sizing: border-box !important;
    -webkit-appearance: none !important;
    text-align: center !important;
}

/* Focus state for captcha input */
#nm-login-wrap .wp-hide-security-question input:focus,
#nm-register-wrap .wp-hide-security-question input:focus {
    border-color: #aed604 !important;
    box-shadow: 0 0 0 2px rgba(174, 214, 4, 0.2) !important;
    outline: none !important;
}

/* Force inline display for the equation part */
#nm-login-wrap .wp-hide-security-question br,
#nm-register-wrap .wp-hide-security-question br {
    display: none !important;
}

/* Mobile Responsive - Keep two columns on mobile for specific fields */
@media (max-width: 767px) {
    /* Fix centering on mobile */
    body {
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .nm-myaccount-login {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        border-radius: 0 !important;
        border: none !important;
        background: transparent !important;
        min-height: 100vh !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    .nm-myaccount-login-inner {
        width: 100% !important;
        max-width: 400px !important;
        margin: 0 auto !important;
        padding: 1rem !important;
    }
    
    #nm-login-wrap,
    #nm-register-wrap {
        padding: 1.5rem !important;
        margin: 0 auto !important;
        width: 100% !important;
    }
    
    /* Keep two-column layout for these specific fields on mobile */
    .form-row-first,
    .form-row-last {
        width: calc(50% - 0.25rem) !important;
        display: inline-block !important;
        vertical-align: top !important;
        margin-bottom: 1rem !important;
    }
    
    .form-row-first {
        margin-right: 0.25rem !important;
    }
    
    .form-row-last {
        margin-left: 0.25rem !important;
    }
    
    /* Fix password toggle position on mobile */
    .password-input-wrapper {
        position: relative !important;
        width: 100% !important;
        display: block !important;
    }
    
    .password-input-wrapper input {
        padding-right: 3rem !important;
    }
    
    .show-password-toggle {
        position: absolute !important;
        right: 0.75rem !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        width: 30px !important;
        height: 30px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    /* Smaller font sizes on mobile */
    #nm-login-wrap label,
    #nm-register-wrap label {
        font-size: 0.65rem !important;
        padding: 0.3rem 0.6rem !important;
    }
    
    #nm-login-wrap input,
    #nm-register-wrap input,
    #nm-register-wrap select {
        font-size: 0.85rem !important;
        padding: 0.75rem !important;
    }
}

/* Clear floats */
form.login:after,
form.register:after {
    content: "" !important;
    display: table !important;
    clear: both !important;
}
</style>

<?php do_action( 'woocommerce_before_customer_login_form' ); ?>

<div id="customer_login" class="nm-myaccount-login">
    <div class="nm-myaccount-login-inner">
		
        <div id="nm-login-wrap" class="inline slide-up fade-in">
            <h2><?php esc_html_e( 'Log in', 'woocommerce' ); ?></h2>
    
            <form<?php echo $popup_form_action_escaped; ?> method="post" class="login">
    			
                <?php echo $popup_redirect_input; ?>
                
                <?php do_action( 'woocommerce_login_form_start' ); ?>
    
                <p class="form-row form-row-wide">
                    <label for="username"><?php esc_html_e( 'Username or email address', 'woocommerce' ); ?> <span class="required">*</span></label>
                    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" />
                </p>
                
                <p class="form-row form-row-wide">
                    <label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?> <span class="required">*</span></label>
                    <span class="password-input-wrapper">
                        <input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required />
                        <button type="button" class="show-password-toggle" aria-label="Show password">
                            <span class="dashicons dashicons-visibility"></span>
                        </button>
                    </span>
                </p>
    
                <?php do_action( 'woocommerce_login_form' ); ?>
                
                <p class="form-row form-group">
                    <label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme inline">
                        <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
                    </label>
                    
                    <span class="woocommerce-LostPassword lost_password">
                        <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'woocommerce' ); ?></a>
                    </span>
                </p>
                
                <p class="form-actions">
                    <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
                    <button type="submit" class="woocommerce-button button woocommerce-form-login__submit<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"><?php esc_html_e( 'Log in', 'woocommerce' ); ?></button>
                    
                    <?php if ( $show_reg_form ) : ?>
                    <div class="nm-login-form-divider"><span><?php esc_html_e( 'Or', 'nm-framework' ); ?></span></div>
                    
                    <a href="#" id="nm-show-register-button" class="button border"><?php esc_html_e( 'Create an account', 'nm-framework' ); ?></a>
                    <?php endif; ?>
                </p>
                
                <?php do_action( 'woocommerce_login_form_end' ); ?>
    
            </form>
        </div>

        <?php if ( $show_reg_form ) : ?>

        <div id="nm-register-wrap">
            <h2><?php esc_html_e( 'Register', 'woocommerce' ); ?></h2>

            <!-- Angel Code Warning Message -->
            <p class="angel-code-message">
                <?php esc_html_e( 'Registration requires an Angel Code.', 'woocommerce' ); ?><br>
                <?php esc_html_e( 'This is an invitation-only community.', 'woocommerce' ); ?>
            </p>
    
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="register">
    			<input type="hidden" name="action" value="greenangel_register_user" />
                <?php wp_nonce_field('greenangel_registration_action', 'greenangel_registration_nonce'); ?>
                <?php echo $popup_redirect_input; ?>
                
                <?php do_action( 'woocommerce_register_form_start' ); ?>
    
                <!-- First Name and Last Name Fields - Same Row -->
                <p class="form-row form-row-first">
                    <label for="reg_first_name"><?php esc_html_e( 'First name', 'woocommerce' ); ?> <span class="required">*</span></label>
                    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="first_name" id="reg_first_name" value="<?php echo ( ! empty( $_POST['first_name'] ) ) ? esc_attr( wp_unslash( $_POST['first_name'] ) ) : ''; ?>" required />
                </p>
                
                <p class="form-row form-row-last">
                    <label for="reg_last_name"><?php esc_html_e( 'Last name', 'woocommerce' ); ?> <span class="required">*</span></label>
                    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="last_name" id="reg_last_name" value="<?php echo ( ! empty( $_POST['last_name'] ) ) ? esc_attr( wp_unslash( $_POST['last_name'] ) ) : ''; ?>" required />
                </p>
                
                <!-- Email Field - Own Row -->
                <p class="form-row form-row-wide">
                    <label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?> <span class="required">*</span></label>
                    <input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required />
                </p>
    
                <!-- Password and Angel Code - Same Row -->
                <?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
                    <p class="form-row form-row-first">
                        <label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?> <span class="required">*</span></label>
                        <span class="password-input-wrapper">
                            <input type="password" class="input-text" name="password" id="reg_password" required />
                            <button type="button" class="show-password-toggle" aria-label="Show password">
                                <span class="dashicons dashicons-visibility"></span>
                            </button>
                        </span>
                    </p>
                <?php endif; ?>
                
                <p class="form-row form-row-last">
                    <label for="angel_code"><?php esc_html_e( 'Angel Code', 'woocommerce' ); ?> <span class="required">*</span></label>
                    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="angel_code" id="angel_code" value="<?php echo ( ! empty( $_POST['angel_code'] ) ) ? esc_attr( wp_unslash( $_POST['angel_code'] ) ) : ''; ?>" required />
                </p>
                
                <!-- Birthday Fields - Same Row -->
                <p class="form-row form-row-first">
                    <label for="reg_birth_month"><?php esc_html_e( 'Birth Month', 'woocommerce' ); ?> <span class="required">*</span></label>
                    <select name="birth_month" id="reg_birth_month" class="woocommerce-Input woocommerce-Input--select input-text" required>
                        <option value="" disabled selected>Select Month</option>
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
                    <label for="reg_birth_year"><?php esc_html_e( 'Birth Year', 'woocommerce' ); ?> <span class="required">*</span></label>
                    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="birth_year" id="reg_birth_year" 
                           pattern="[0-9]{4}" maxlength="4" placeholder="YYYY" required />
                </p>
                
                <!-- Clear floats -->
                <div style="clear: both;"></div>
                
                <?php do_action( 'woocommerce_register_form' ); ?>
                
                <!-- Force hide WooCommerce injected elements -->
                <style>
                    #nm-register-wrap .wc-order-attribution-inputs,
                    #nm-register-wrap .woocommerce-privacy-policy-text,
                    #nm-register-wrap p:has(.wc-order-attribution-inputs),
                    #nm-register-wrap p:empty:not(.form-actions),
                    #nm-register-wrap br {
                        display: none !important;
                        height: 0 !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        visibility: hidden !important;
                        position: absolute !important;
                        left: -9999px !important;
                    }
                    
                    /* Force the last visible element before button to have less margin */
                    #nm-register-wrap > *:nth-last-child(2):not(.form-actions) {
                        margin-bottom: 1rem !important;
                    }
                </style>
                
                <p class="form-actions" style="margin-top: 1rem !important; margin-bottom: 0 !important;">
                    <?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
                    <button type="submit" class="woocommerce-Button woocommerce-button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?> woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"><?php esc_html_e( 'Register', 'woocommerce' ); ?></button>
                </p>
                
                <?php do_action( 'woocommerce_register_form_end' ); ?>
            </form>
        </div>
    
        <?php endif; ?>

        <?php do_action( 'woocommerce_after_customer_login_form' ); ?>
    
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle functionality
    const toggleButtons = document.querySelectorAll('.show-password-toggle');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const wrapper = this.closest('.password-input-wrapper');
            const input = wrapper.querySelector('input[type="password"], input[type="text"]');
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
    
    // NEWSLETTER NUCLEAR OPTION - Remove it completely
    const newsletterElements = document.querySelectorAll(
        'input[name="newsletter"], label[for="newsletter"], p:has(input[name="newsletter"]), .form-row:has(input[name="newsletter"])'
    );
    newsletterElements.forEach(el => {
        if (el && el.parentNode) {
            el.parentNode.removeChild(el);
        }
    });
    
    // Also check for any parent containers that might have the text
    const allParagraphs = document.querySelectorAll('#nm-register-wrap p');
    allParagraphs.forEach(p => {
        if (p.textContent && p.textContent.includes('Subscribe to our newsletter')) {
            p.remove();
        }
    });
    
    // Remove any privacy policy text or empty elements creating space
    const privacyElements = document.querySelectorAll('.woocommerce-privacy-policy-text, .woocommerce-privacy-policy-link');
    privacyElements.forEach(el => {
        if (el) el.remove();
    });
    
    // Remove WooCommerce order attribution inputs
    const orderAttribution = document.querySelectorAll('.wc-order-attribution-inputs');
    orderAttribution.forEach(el => {
        if (el) el.remove();
    });
    
    // Remove empty paragraphs
    const emptyElements = document.querySelectorAll('#nm-register-wrap p:empty, #nm-register-wrap div:empty');
    emptyElements.forEach(el => {
        if (el && !el.classList.contains('clear')) el.remove();
    });
    
    // Show/Hide Registration Form
    const showRegisterBtn = document.getElementById('nm-show-register-button');
    const loginWrap = document.getElementById('nm-login-wrap');
    const registerWrap = document.getElementById('nm-register-wrap');
    
    if (showRegisterBtn) {
        showRegisterBtn.addEventListener('click', function(e) {
            e.preventDefault();
            loginWrap.style.display = 'none';
            registerWrap.style.display = 'block';
        });
    }
    
    // Ensure form always submits with newsletter checked
    const registerForm = document.querySelector('form.register');
    if (registerForm) {
        // Always add hidden input
        const existingNewsletter = registerForm.querySelector('input[name="newsletter"]');
        if (!existingNewsletter) {
            const hiddenNewsletter = document.createElement('input');
            hiddenNewsletter.type = 'hidden';
            hiddenNewsletter.name = 'newsletter';
            hiddenNewsletter.value = '1';
            registerForm.appendChild(hiddenNewsletter);
        }
        
        registerForm.addEventListener('submit', function(e) {
            // Ensure newsletter is always set
            let newsletterInput = this.querySelector('input[name="newsletter"]');
            if (!newsletterInput) {
                newsletterInput = document.createElement('input');
                newsletterInput.type = 'hidden';
                newsletterInput.name = 'newsletter';
                newsletterInput.value = '1';
                this.appendChild(newsletterInput);
            } else {
                newsletterInput.value = '1';
            }
        });
    }
    
    // Fix captcha display
    setTimeout(function() {
        const captchaWrapper = document.querySelector('.wp-hide-security-question');
        if (captchaWrapper) {
            // Force remove any BR tags
            const brs = captchaWrapper.querySelectorAll('br');
            brs.forEach(br => br.remove());
            
            // Ensure label has correct styling
            const label = captchaWrapper.querySelector('label');
            if (label) {
                label.style.cssText = 'background: #1a1a1a !important; color: #aed604 !important; padding: 0.4rem 0.8rem !important; border-radius: 20px !important; font-size: 0.75rem !important; font-weight: 600 !important; display: inline-block !important;';
            }
        }
    }, 100);
    
    // Kill password strength meter
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            const passwordStrength = document.querySelectorAll('.woocommerce-password-strength, .woocommerce-password-hint, small.woocommerce-password-hint');
            passwordStrength.forEach(el => {
                if (el && el.parentNode) {
                    el.style.display = 'none';
                    el.remove();
                }
            });
        });
    });
    
    // Start observing the register form for password strength elements
    const registerWrap = document.querySelector('#nm-register-wrap');
    if (registerWrap) {
        observer.observe(registerWrap, {
            childList: true,
            subtree: true
        });
    }
});
</script>