<?php
/**
 * Plugin Name: Green Angel Login
 * Description: Custom branded login and registration system with full-screen experience
 * Version: 1.0.0
 * Author: Green Angel XO Team
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class GreenAngelLogin {
    
    private $plugin_url;
    private $plugin_path;
    
    public function __construct() {
        $this->plugin_url = plugin_dir_url(__FILE__);
        $this->plugin_path = plugin_dir_path(__FILE__);
        
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('template_redirect', array($this, 'force_login_redirect'));
        add_action('wp_ajax_nopriv_angel_login', array($this, 'handle_login'));
        add_action('wp_ajax_nopriv_angel_register', array($this, 'handle_registration'));
        add_action('wp_ajax_nopriv_angel_validate_code', array($this, 'validate_angel_code'));
        add_action('wp_ajax_nopriv_angel_forgot_password', array($this, 'handle_forgot_password'));
        add_action('wp_ajax_nopriv_angel_reset_password', array($this, 'handle_reset_password'));
        
        register_activation_hook(__FILE__, array($this, 'activate_plugin'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate_plugin'));
        
        add_shortcode('greenangel_login_register_form', array($this, 'render_login_form'));
        add_filter('template_include', array($this, 'custom_template'));
    }
    
    public function init() {
        // Create the angel codes table if it doesn't exist
        $this->create_angel_codes_table();
    }
    
    public function activate_plugin() {
        // Create the angel login page
        $page_data = array(
            'post_title'    => 'Angel Login',
            'post_content'  => '[greenangel_login_register_form]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'angel-login'
        );
        
        // Check if page already exists
        $existing_page = get_page_by_path('angel-login');
        if (!$existing_page) {
            wp_insert_post($page_data);
        }
        
        // Create angel codes table
        $this->create_angel_codes_table();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate_plugin() {
        // Clean up if needed
        flush_rewrite_rules();
    }
    
    private function create_angel_codes_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'greenangel_codes';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            code varchar(100) NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            used_by int(11) DEFAULT NULL,
            created_date datetime DEFAULT CURRENT_TIMESTAMP,
            used_date datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY code (code)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function enqueue_assets() {
        if (is_page('angel-login')) {
            wp_enqueue_style('angel-login-css', $this->plugin_url . 'assets/angel-login.css', array(), '1.0.0');
            wp_enqueue_script('angel-login-js', $this->plugin_url . 'assets/angel-login.js', array('jquery'), '1.0.0', true);
            
            // Localize script for AJAX
            wp_localize_script('angel-login-js', 'angel_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('angel_login_nonce_action')
            ));
        }
    }
    
    public function force_login_redirect() {
        // Skip if user is logged in
        if (is_user_logged_in()) {
            return;
        }
        
        // Skip for admin, login, and our custom login page
        if (is_admin() || is_page('angel-login') || $GLOBALS['pagenow'] === 'wp-login.php') {
            return;
        }
        
        // Skip for AJAX requests
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }
        
        // Redirect to our custom login page
        wp_redirect(home_url('/angel-login/'));
        exit;
    }
    
    public function custom_template($template) {
        if (is_page('angel-login')) {
            $custom_template = $this->plugin_path . 'template-fullscreen-login.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }
    
    public function render_login_form() {
        // Check if this is a password reset request
        if (isset($_GET['action']) && $_GET['action'] === 'rp' && isset($_GET['key']) && isset($_GET['login'])) {
            ob_start();
            include($this->plugin_path . 'template-reset-password.php');
            return ob_get_clean();
        }
        
        ob_start();
        include($this->plugin_path . 'template-login-register.php');
        return ob_get_clean();
    }
    
    public function validate_angel_code() {
        check_ajax_referer('angel_login_nonce_action', 'nonce');
        
        $code = strtoupper(trim(sanitize_text_field($_POST['code'])));
        error_log("Submitted code: " . $code);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'greenangel_codes';
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE UPPER(code) = %s AND is_active = 1",
            $code
        ));
        error_log("Validation result: " . print_r($result, true));
        
        if ($result) {
            wp_send_json_success(array('message' => esc_html__('Valid angel code!', 'greenangel-login')));
        } else {
            wp_send_json_error(array('message' => esc_html__('Invalid angel code.', 'greenangel-login')));
        }
    }
    
    public function handle_registration() {
        check_ajax_referer('angel_login_nonce_action', 'nonce');
        
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $angel_code = sanitize_text_field($_POST['angel_code']);
        $birth_month = sanitize_text_field($_POST['birth_month']);
        $birth_year = sanitize_text_field($_POST['birth_year']);
        
        // Validate angel code first
        global $wpdb;
        $table_name = $wpdb->prefix . 'greenangel_codes';
        
        $code_result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE code = %s AND is_active = 1",
            $angel_code
        ));
        
        if (!$code_result) {
            wp_send_json_error(array('message' => esc_html__('Invalid angel code.', 'greenangel-login')));
            return;
        }
        
        // Check if user already exists
        if (email_exists($email)) {
            wp_send_json_error(array('message' => esc_html__('An account with this email already exists.', 'greenangel-login')));
            return;
        }
        
        // Create the user
        $user_id = wp_create_user($email, $password, $email);
        
        if (is_wp_error($user_id)) {
            wp_send_json_error(array('message' => esc_html($user_id->get_error_message())));
            return;
        }
        
        // Update user meta
        wp_update_user(array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $first_name . ' ' . $last_name
        ));
        
        // Add custom meta
        update_user_meta($user_id, 'birth_month', $birth_month);
        update_user_meta($user_id, 'birth_year', $birth_year);
        update_user_meta($user_id, 'angel_code_used', $angel_code);

        // Create WooCommerce customer if WooCommerce is active
        if (class_exists('WooCommerce')) {
            $customer = new WC_Customer($user_id);
            $customer->set_email($email);
            $customer->set_first_name($first_name);
            $customer->set_last_name($last_name);
            $customer->save();
        }
        
        // Log the user in
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);
        
        // Newsletter subscription hook - you can customize this
        do_action('angel_user_registered', $user_id, $email);
        
        // Newsletter subscription - THE Newsletter Plugin integration
        if (function_exists('newsletter_subscribe')) {
            // Method 1: Direct newsletter_subscribe function
            newsletter_subscribe($email, array(
                'name' => $first_name . ' ' . $last_name,
                'status' => 'C'
            ));
        } elseif (class_exists('Newsletter')) {
            // Method 2: Newsletter class integration
            $newsletter = Newsletter::instance();
            $user_data = array(
                'email' => $email,
                'name' => $first_name . ' ' . $last_name,
                'status' => 'C' // Confirmed
            );
            $newsletter->save_user($user_data);
        } elseif (function_exists('tnp_subscribe')) {
            // Method 3: TNP (The Newsletter Plugin) function
            tnp_subscribe(array(
                'email' => $email,
                'name' => $first_name . ' ' . $last_name,
                'status' => 'C'
            ));
        }
        
        // Generic newsletter hook for any other newsletter plugins
        do_action('greenangel_newsletter_signup', $email, $first_name, $last_name, $user_id);
        
        // Find Angel Hub page and redirect there, or fallback to account page
        $angel_hub_page = get_page_by_path('angel-hub');
        $redirect_url = home_url('/angel-hub/'); // Default
        
        if ($angel_hub_page && $angel_hub_page->post_status === 'publish') {
            $redirect_url = get_permalink($angel_hub_page->ID);
        } else {
            // Fallback to WooCommerce account page if Angel Hub doesn't exist
            $redirect_url = get_permalink(wc_get_page_id('myaccount'));
        }
        
        wp_send_json_success(array(
            'message' => esc_html__('Registration successful!', 'greenangel-login'),
            'redirect' => $redirect_url
        ));
    }
    
    public function handle_login() {
        check_ajax_referer('angel_login_nonce_action', 'nonce');
        
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']) ? true : false;
        
        $user = wp_authenticate($email, $password);
        
        if (is_wp_error($user)) {
            wp_send_json_error(array('message' => esc_html__('Invalid email or password.', 'greenangel-login')));
            return;
        }
        
        // Log the user in
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, $remember);
        
        // Find Angel Hub page and redirect there, or fallback to account page
        $angel_hub_page = get_page_by_path('angel-hub');
        $redirect_url = home_url('/angel-hub/'); // Default
        
        if ($angel_hub_page && $angel_hub_page->post_status === 'publish') {
            $redirect_url = get_permalink($angel_hub_page->ID);
        } else {
            // Fallback to WooCommerce account page if Angel Hub doesn't exist
            $redirect_url = get_permalink(wc_get_page_id('myaccount'));
        }
        
        wp_send_json_success(array(
            'message' => esc_html__('Login successful!', 'greenangel-login'),
            'redirect' => $redirect_url
        ));
    }
    
    public function handle_forgot_password() {
        check_ajax_referer('angel_login_nonce_action', 'nonce');
        
        $email = sanitize_email($_POST['email']);
        
        if (empty($email)) {
            wp_send_json_error(array('message' => esc_html__('Please enter your email address.', 'greenangel-login')));
            return;
        }
        
        // Check if user exists
        $user = get_user_by('email', $email);
        
        if (!$user) {
            // Don't reveal if email exists for security
            wp_send_json_success(array(
                'message' => esc_html__('If an account exists with this email, you will receive password reset instructions shortly. ✨', 'greenangel-login')
            ));
            return;
        }
        
        // Generate password reset key
        $key = get_password_reset_key($user);
        
        if (is_wp_error($key)) {
            wp_send_json_error(array('message' => esc_html__('Unable to generate reset link. Please try again.', 'greenangel-login')));
            return;
        }
        
        // Get the user login
        $user_login = $user->user_login;
        
        // Create reset link
        $reset_link = add_query_arg(array(
            'action' => 'rp',
            'key' => $key,
            'login' => rawurlencode($user_login)
        ), home_url('/angel-login/'));
        
        // Send the email
        $subject = get_bloginfo('name') . ' - Password Reset Request';
        
        $message = "Hey angel! ✨\n\n";
        $message .= "Someone requested a password reset for your account. If this was you, click the link below:\n\n";
        $message .= $reset_link . "\n\n";
        $message .= "If you didn't request this, just ignore this email and your password will remain unchanged.\n\n";
        $message .= "Stay magical! 🌟\n";
        $message .= get_bloginfo('name');
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        
        $sent = wp_mail($email, $subject, $message, $headers);
        
        if ($sent) {
            wp_send_json_success(array(
                'message' => esc_html__('Password reset link sent! Check your email (and spam folder just in case). ✨', 'greenangel-login')
            ));
        } else {
            wp_send_json_error(array('message' => esc_html__('Error sending email. Please contact support.', 'greenangel-login')));
        }
    }
    
    public function handle_reset_password() {
        check_ajax_referer('angel_login_nonce_action', 'nonce');
        
        $key = sanitize_text_field($_POST['key']);
        $login = sanitize_text_field($_POST['login']);
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];
        
        if (empty($password) || empty($password_confirm)) {
            wp_send_json_error(array('message' => esc_html__('Please enter a new password.', 'greenangel-login')));
            return;
        }
        
        if ($password !== $password_confirm) {
            wp_send_json_error(array('message' => esc_html__('Passwords do not match.', 'greenangel-login')));
            return;
        }
        
        // Check password strength
        if (strlen($password) < 8) {
            wp_send_json_error(array('message' => esc_html__('Password must be at least 8 characters long.', 'greenangel-login')));
            return;
        }
        
        // Verify the reset key
        $user = check_password_reset_key($key, $login);
        
        if (is_wp_error($user)) {
            if ($user->get_error_code() === 'expired_key') {
                wp_send_json_error(array('message' => esc_html__('This reset link has expired. Please request a new one.', 'greenangel-login')));
            } else {
                wp_send_json_error(array('message' => esc_html__('Invalid reset link. Please request a new one.', 'greenangel-login')));
            }
            return;
        }
        
        // Reset the password
        reset_password($user, $password);
        
        // Log the user in automatically
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        
        // Find redirect URL
        $angel_hub_page = get_page_by_path('angel-hub');
        $redirect_url = home_url('/angel-hub/');
        
        if ($angel_hub_page && $angel_hub_page->post_status === 'publish') {
            $redirect_url = get_permalink($angel_hub_page->ID);
        } else {
            $redirect_url = get_permalink(wc_get_page_id('myaccount'));
        }
        
        wp_send_json_success(array(
            'message' => esc_html__('Password reset successful! Welcome back, angel! ✨', 'greenangel-login'),
            'redirect' => $redirect_url
        ));
    }
}

// Initialize the plugin
new GreenAngelLogin();