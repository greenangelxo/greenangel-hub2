<?php
/**
 * Plugin Name: Green Angel Login - LED Edition
 * Description: Custom branded login and registration system with premium LED dark mode experience
 * Version: 2.0.0
 * Author: Green Angel XO Team
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * Text Domain: greenangel-login
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('GREENANGEL_LOGIN_VERSION', '2.0.0');
define('GREENANGEL_LOGIN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('GREENANGEL_LOGIN_PLUGIN_PATH', plugin_dir_path(__FILE__));

class GreenAngelLogin {
    
    private $plugin_url;
    private $plugin_path;
    private $version;
    
    public function __construct() {
        $this->plugin_url = GREENANGEL_LOGIN_PLUGIN_URL;
        $this->plugin_path = GREENANGEL_LOGIN_PLUGIN_PATH;
        $this->version = GREENANGEL_LOGIN_VERSION;
        
        // Initialize plugin
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('template_redirect', array($this, 'force_login_redirect'));
        
        // AJAX handlers
        add_action('wp_ajax_nopriv_angel_login', array($this, 'handle_login'));
        add_action('wp_ajax_nopriv_angel_register', array($this, 'handle_registration'));
        add_action('wp_ajax_nopriv_angel_validate_code', array($this, 'validate_angel_code'));
        add_action('wp_ajax_nopriv_angel_forgot_password', array($this, 'handle_forgot_password'));
        add_action('wp_ajax_nopriv_angel_reset_password', array($this, 'handle_reset_password'));
        
        // Plugin lifecycle hooks
        register_activation_hook(__FILE__, array($this, 'activate_plugin'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate_plugin'));
        
        // Shortcode and template hooks
        add_shortcode('greenangel_login_register_form', array($this, 'render_login_form'));
        add_filter('template_include', array($this, 'custom_template'));
        
        // Enhanced security and performance hooks
        add_action('wp_login', array($this, 'track_user_login'), 10, 2);
        add_action('wp_logout', array($this, 'track_user_logout'));
        add_filter('wp_login_errors', array($this, 'enhance_login_errors'), 10, 2);
        
        // LED enhancement hooks
        add_action('wp_head', array($this, 'add_led_meta_tags'));
        add_action('login_head', array($this, 'disable_default_login_styles'));
    }
    
    /**
     * Initialize plugin functionality
     */
    public function init() {
        // Load text domain for internationalization
        load_plugin_textdomain('greenangel-login', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Create database tables
        $this->create_angel_codes_table();
        $this->create_login_logs_table();
        
        // Schedule cleanup tasks
        if (!wp_next_scheduled('greenangel_cleanup_expired_codes')) {
            wp_schedule_event(time(), 'daily', 'greenangel_cleanup_expired_codes');
        }
        add_action('greenangel_cleanup_expired_codes', array($this, 'cleanup_expired_codes'));
    }
    
    /**
     * Plugin activation - Enhanced LED setup
     */
    public function activate_plugin() {
        // Create the angel login page with enhanced metadata
        $page_data = array(
            'post_title'    => 'Angel Login',
            'post_content'  => '[greenangel_login_register_form]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'angel-login',
            'post_excerpt'  => 'Premium LED login portal for Green Angel community',
            'meta_input'    => array(
                '_greenangel_login_page' => 'true',
                '_wp_page_template' => 'angel-login'
            )
        );
        
        // Check if page already exists
        $existing_page = get_page_by_path('angel-login');
        if (!$existing_page) {
            $page_id = wp_insert_post($page_data);
            
            // Store page ID for future reference
            update_option('greenangel_login_page_id', $page_id);
        } else {
            update_option('greenangel_login_page_id', $existing_page->ID);
        }
        
        // Create database tables
        $this->create_angel_codes_table();
        $this->create_login_logs_table();
        
        // Set default options
        $default_options = array(
            'enable_led_effects' => true,
            'require_strong_passwords' => true,
            'max_login_attempts' => 5,
            'lockout_duration' => 30, // minutes
            'enable_registration' => true,
            'default_user_role' => 'customer'
        );
        
        foreach ($default_options as $option => $value) {
            if (get_option("greenangel_login_{$option}") === false) {
                update_option("greenangel_login_{$option}", $value);
            }
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Log activation
        error_log('Green Angel Login LED Edition activated successfully');
    }
    
    /**
     * Plugin deactivation cleanup
     */
    public function deactivate_plugin() {
        // Clear scheduled events
        wp_clear_scheduled_hook('greenangel_cleanup_expired_codes');
        
        // Clean up transients
        $this->cleanup_plugin_transients();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        error_log('Green Angel Login LED Edition deactivated');
    }
    
    /**
     * Enhanced database table creation with better indexing
     */
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
            expires_date datetime DEFAULT NULL,
            created_by int(11) DEFAULT NULL,
            max_uses int(11) DEFAULT 1,
            current_uses int(11) DEFAULT 0,
            notes text DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY code (code),
            KEY is_active (is_active),
            KEY used_by (used_by),
            KEY expires_date (expires_date),
            KEY created_by (created_by)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Create login logs table for enhanced security tracking
     */
    private function create_login_logs_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'greenangel_login_logs';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT NULL,
            email varchar(100) NOT NULL,
            ip_address varchar(45) NOT NULL,
            user_agent text DEFAULT NULL,
            action varchar(50) NOT NULL,
            status varchar(20) NOT NULL,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            additional_data longtext DEFAULT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY email (email),
            KEY ip_address (ip_address),
            KEY action (action),
            KEY status (status),
            KEY timestamp (timestamp)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Enhanced asset enqueuing with LED optimizations
     */
    public function enqueue_assets() {
        if (is_page('angel-login') || $this->is_angel_login_page()) {
            // Enqueue LED-enhanced CSS with cache busting
            wp_enqueue_style(
                'angel-login-led-css', 
                $this->plugin_url . 'assets/angel-login.css', 
                array(), 
                $this->version . '-' . filemtime($this->plugin_path . 'assets/angel-login.css')
            );
            
            // Enqueue enhanced JavaScript with dependencies
            wp_enqueue_script(
                'angel-login-led-js', 
                $this->plugin_url . 'assets/angel-login.js', 
                array('jquery'), 
                $this->version . '-' . filemtime($this->plugin_path . 'assets/angel-login.js'), 
                true
            );
            
            // Enhanced localization with security nonces and settings
            wp_localize_script('angel-login-led-js', 'angel_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('angel_login_nonce_action'),
                'settings' => array(
                    'led_effects' => get_option('greenangel_login_enable_led_effects', true),
                    'strong_passwords' => get_option('greenangel_login_require_strong_passwords', true),
                    'debug' => defined('WP_DEBUG') && WP_DEBUG
                ),
                'strings' => array(
                    'loading' => __('Loading...', 'greenangel-login'),
                    'success' => __('Success!', 'greenangel-login'),
                    'error' => __('Error occurred', 'greenangel-login'),
                    'invalid_code' => __('Invalid angel code', 'greenangel-login'),
                    'weak_password' => __('Password is too weak', 'greenangel-login')
                )
            ));
            
            // Add LED-specific inline styles for dynamic theming
            $led_inline_css = $this->generate_led_inline_css();
            wp_add_inline_style('angel-login-led-css', $led_inline_css);
        }
    }
    
    /**
     * Generate dynamic LED CSS based on settings
     */
    private function generate_led_inline_css() {
        $css = '';
        
        // Check if LED effects are enabled
        if (!get_option('greenangel_login_enable_led_effects', true)) {
            $css .= '
                .angel-auth-container::before,
                .angel-tabs::before,
                .angel-name-dice-game::before {
                    display: none !important;
                }
            ';
        }
        
        // Add custom brand colors if set
        $primary_color = get_option('greenangel_login_primary_color', '');
        if ($primary_color) {
            $css .= "
                .angel-button:hover {
                    border-color: {$primary_color} !important;
                }
            ";
        }
        
        return $css;
    }
    
    /**
     * Enhanced login redirect with better logic
     */
    public function force_login_redirect() {
        // Skip if user is logged in
        if (is_user_logged_in()) {
            return;
        }
        
        // Skip for admin, login pages, AJAX, REST API, and cron
        if (is_admin() || 
            is_page('angel-login') || 
            $GLOBALS['pagenow'] === 'wp-login.php' ||
            (defined('DOING_AJAX') && DOING_AJAX) ||
            (defined('REST_REQUEST') && REST_REQUEST) ||
            (defined('DOING_CRON') && DOING_CRON)) {
            return;
        }
        
        // Skip for specific file requests
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $skip_extensions = array('.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.ico', '.woff', '.woff2', '.ttf');
        foreach ($skip_extensions as $ext) {
            if (strpos($request_uri, $ext) !== false) {
                return;
            }
        }
        
        // Store the original request for redirect after login
        if (!session_id()) {
            session_start();
        }
        $_SESSION['angel_redirect_after_login'] = home_url($_SERVER['REQUEST_URI'] ?? '');
        
        // Redirect to our custom login page
        wp_redirect(home_url('/angel-login/'));
        exit;
    }
    
    /**
     * Enhanced custom template loading
     */
    public function custom_template($template) {
        if (is_page('angel-login') || $this->is_angel_login_page()) {
            $custom_template = $this->plugin_path . 'template-fullscreen-login.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }
    
    /**
     * Enhanced login form rendering with context detection
     */
    public function render_login_form() {
        // Check if this is a password reset request
        if (isset($_GET['action']) && $_GET['action'] === 'rp' && 
            isset($_GET['key']) && isset($_GET['login'])) {
            ob_start();
            include($this->plugin_path . 'template-reset-password.php');
            return ob_get_clean();
        }
        
        ob_start();
        include($this->plugin_path . 'template-login-register.php');
        return ob_get_clean();
    }
    
    /**
     * Enhanced angel code validation with rate limiting
     */
    public function validate_angel_code() {
        // Enhanced nonce verification
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'angel_login_nonce_action')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'greenangel-login')));
            return;
        }
        
        // Rate limiting check
        $ip_address = $this->get_client_ip();
        $rate_limit_key = 'angel_code_check_' . md5($ip_address);
        $attempts = get_transient($rate_limit_key) ?: 0;
        
        if ($attempts >= 10) { // Max 10 checks per hour
            wp_send_json_error(array('message' => __('Too many attempts. Please try again later.', 'greenangel-login')));
            return;
        }
        
        $code = isset($_POST['code']) ? strtoupper(trim(sanitize_text_field($_POST['code']))) : '';
        
        if (empty($code)) {
            wp_send_json_error(array('message' => __('Please enter a code.', 'greenangel-login')));
            return;
        }
        
        // Enhanced code validation
        if (strlen($code) < 3 || strlen($code) > 50) {
            wp_send_json_error(array('message' => __('Invalid code format.', 'greenangel-login')));
            return;
        }
        
        // Increment rate limiting counter
        set_transient($rate_limit_key, $attempts + 1, HOUR_IN_SECONDS);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'greenangel_codes';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            error_log("Angel codes table does not exist!");
            wp_send_json_error(array('message' => __('System error. Please contact support.', 'greenangel-login')));
            return;
        }
        
        // Enhanced code lookup with expiration check
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE UPPER(code) = %s 
             AND is_active = 1 
             AND (expires_date IS NULL OR expires_date > NOW())
             AND (max_uses = 0 OR current_uses < max_uses)",
            $code
        ));
        
        if ($result) {
            // Log successful validation
            $this->log_action('code_validation', 'success', array(
                'code' => $code,
                'ip' => $ip_address
            ));
            
            wp_send_json_success(array('message' => __('✨ Valid angel code!', 'greenangel-login')));
        } else {
            // Log failed validation
            $this->log_action('code_validation', 'failed', array(
                'code' => $code,
                'ip' => $ip_address
            ));
            
            wp_send_json_error(array('message' => __('Invalid angel code.', 'greenangel-login')));
        }
    }
    
    /**
     * Enhanced registration handling with comprehensive validation
     */
    public function handle_registration() {
        check_ajax_referer('angel_login_nonce_action', 'nonce');
        
        // Rate limiting for registration attempts
        $ip_address = $this->get_client_ip();
        $rate_limit_key = 'angel_register_' . md5($ip_address);
        $attempts = get_transient($rate_limit_key) ?: 0;
        
        if ($attempts >= 3) { // Max 3 registration attempts per hour
            wp_send_json_error(array('message' => __('Too many registration attempts. Please try again later.', 'greenangel-login')));
            return;
        }
        
        // Sanitize and validate input data
        $first_name = sanitize_text_field($_POST['first_name'] ?? '');
        $last_name = sanitize_text_field($_POST['last_name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $angel_code = strtoupper(trim(sanitize_text_field($_POST['angel_code'] ?? '')));
        $birth_month = sanitize_text_field($_POST['birth_month'] ?? '');
        $birth_year = sanitize_text_field($_POST['birth_year'] ?? '');
        $display_name = sanitize_text_field($_POST['display_name'] ?? '');
        
        // Enhanced validation
        $validation_errors = array();
        
        if (empty($first_name) || strlen($first_name) < 2) {
            $validation_errors[] = __('First name must be at least 2 characters.', 'greenangel-login');
        }
        
        if (empty($last_name) || strlen($last_name) < 2) {
            $validation_errors[] = __('Last name must be at least 2 characters.', 'greenangel-login');
        }
        
        if (!is_email($email)) {
            $validation_errors[] = __('Please enter a valid email address.', 'greenangel-login');
        }
        
        // Enhanced password validation
        if (!$this->validate_password_strength($password)) {
            $validation_errors[] = __('Password must be at least 8 characters with uppercase, lowercase, number, and special character.', 'greenangel-login');
        }
        
        if (!preg_match('/^\d{4}$/', $birth_year) || $birth_year < 1900 || $birth_year > date('Y')) {
            $validation_errors[] = __('Please enter a valid birth year.', 'greenangel-login');
        }
        
        if (!empty($validation_errors)) {
            wp_send_json_error(array('message' => implode(' ', $validation_errors)));
            return;
        }
        
        // Increment rate limiting counter
        set_transient($rate_limit_key, $attempts + 1, HOUR_IN_SECONDS);
        
        // Validate angel code
        global $wpdb;
        $table_name = $wpdb->prefix . 'greenangel_codes';
        
        $code_result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE UPPER(code) = %s 
             AND is_active = 1 
             AND (expires_date IS NULL OR expires_date > NOW())
             AND (max_uses = 0 OR current_uses < max_uses)",
            $angel_code
        ));
        
        if (!$code_result) {
            $this->log_action('registration', 'failed_code', array(
                'email' => $email,
                'code' => $angel_code,
                'ip' => $ip_address
            ));
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
            $this->log_action('registration', 'failed_create', array(
                'email' => $email,
                'error' => $user_id->get_error_message(),
                'ip' => $ip_address
            ));
            wp_send_json_error(array('message' => esc_html($user_id->get_error_message())));
            return;
        }
        
        // Generate display name if not provided
        if (empty($display_name)) {
            $display_name = $this->generate_random_display_name();
        }
        
        // Ensure display name is unique
        $display_name = $this->ensure_unique_display_name($display_name);
        
        // Update user meta with enhanced data
        wp_update_user(array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $display_name,
            'role' => get_option('greenangel_login_default_user_role', 'customer')
        ));
        
        // Add comprehensive custom meta
        $user_meta = array(
            'birth_month' => $birth_month,
            'birth_year' => $birth_year,
            'angel_code_used' => $angel_code,
            'registration_ip' => $ip_address,
            'registration_date' => current_time('mysql'),
            'led_preferences' => array(
                'theme' => 'dark',
                'effects_enabled' => true
            )
        );
        
        foreach ($user_meta as $key => $value) {
            update_user_meta($user_id, $key, $value);
        }
        
        // Update angel code usage
        $wpdb->update(
            $table_name,
            array(
                'current_uses' => $code_result->current_uses + 1,
                'used_date' => current_time('mysql'),
                'used_by' => $user_id
            ),
            array('id' => $code_result->id),
            array('%d', '%s', '%d'),
            array('%d')
        );
        
        // Create WooCommerce customer if WooCommerce is active
        if (class_exists('WooCommerce')) {
            try {
                $customer = new WC_Customer($user_id);
                $customer->set_email($email);
                $customer->set_first_name($first_name);
                $customer->set_last_name($last_name);
                $customer->save();
            } catch (Exception $e) {
                error_log('WooCommerce customer creation failed: ' . $e->getMessage());
            }
        }
        
        // Log the user in
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);
        
        // Enhanced logging
        $this->log_action('registration', 'success', array(
            'user_id' => $user_id,
            'email' => $email,
            'code_used' => $angel_code,
            'ip' => $ip_address,
            'display_name' => $display_name
        ));
        
        // Newsletter subscription with enhanced integration
        $this->handle_newsletter_subscription($email, $first_name, $last_name, $user_id);
        
        // Trigger custom hooks for extensibility
        do_action('greenangel_user_registered', $user_id, $email, $angel_code);
        
        // Determine redirect URL with session handling
        $redirect_url = $this->get_post_login_redirect_url();
        
        // Clear rate limiting on successful registration
        delete_transient($rate_limit_key);
        
        wp_send_json_success(array(
            'message' => esc_html__('Registration successful! Welcome to the family! ✨', 'greenangel-login'),
            'redirect' => $redirect_url
        ));
    }
    
    /**
     * Enhanced login handling with security features
     */
    public function handle_login() {
        check_ajax_referer('angel_login_nonce_action', 'nonce');
        
        $email = sanitize_email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        $ip_address = $this->get_client_ip();
        
        // Rate limiting for login attempts
        $rate_limit_key = 'angel_login_' . md5($ip_address . $email);
        $attempts = get_transient($rate_limit_key) ?: 0;
        $max_attempts = get_option('greenangel_login_max_login_attempts', 5);
        
        if ($attempts >= $max_attempts) {
            $lockout_duration = get_option('greenangel_login_lockout_duration', 30);
            wp_send_json_error(array(
                'message' => sprintf(
                    __('Too many failed attempts. Please try again in %d minutes.', 'greenangel-login'),
                    $lockout_duration
                )
            ));
            return;
        }
        
        // Attempt authentication
        $user = wp_authenticate($email, $password);
        
        if (is_wp_error($user)) {
            // Increment failed attempts
            set_transient($rate_limit_key, $attempts + 1, get_option('greenangel_login_lockout_duration', 30) * MINUTE_IN_SECONDS);
            
            // Log failed attempt
            $this->log_action('login', 'failed', array(
                'email' => $email,
                'ip' => $ip_address,
                'attempts' => $attempts + 1
            ));
            
            wp_send_json_error(array('message' => esc_html__('Invalid email or password.', 'greenangel-login')));
            return;
        }
        
        // Clear rate limiting on successful login
        delete_transient($rate_limit_key);
        
        // Log the user in
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, $remember);
        
        // Update login tracking
        $this->track_user_login($user->user_login, $user);
        
        // Log successful login
        $this->log_action('login', 'success', array(
            'user_id' => $user->ID,
            'email' => $email,
            'ip' => $ip_address
        ));
        
        // Get redirect URL
        $redirect_url = $this->get_post_login_redirect_url();
        
        wp_send_json_success(array(
            'message' => esc_html__('Login successful! Welcome back, angel! ✨', 'greenangel-login'),
            'redirect' => $redirect_url
        ));
    }
    
    /**
     * Enhanced forgot password handling
     */
    public function handle_forgot_password() {
        check_ajax_referer('angel_login_nonce_action', 'nonce');
        
        $email = sanitize_email($_POST['email'] ?? '');
        $ip_address = $this->get_client_ip();
        
        if (empty($email)) {
            wp_send_json_error(array('message' => esc_html__('Please enter your email address.', 'greenangel-login')));
            return;
        }
        
        // Rate limiting for password reset requests
        $rate_limit_key = 'angel_reset_' . md5($ip_address . $email);
        $attempts = get_transient($rate_limit_key) ?: 0;
        
        if ($attempts >= 3) { // Max 3 reset attempts per hour
            wp_send_json_error(array('message' => esc_html__('Too many reset attempts. Please try again later.', 'greenangel-login')));
            return;
        }
        
        // Increment rate limiting counter
        set_transient($rate_limit_key, $attempts + 1, HOUR_IN_SECONDS);
        
        // Check if user exists
        $user = get_user_by('email', $email);
        
        if (!$user) {
            // Don't reveal if email exists for security, but log the attempt
            $this->log_action('password_reset', 'invalid_email', array(
                'email' => $email,
                'ip' => $ip_address
            ));
            
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
        
        // Create enhanced reset link
        $reset_link = add_query_arg(array(
            'action' => 'rp',
            'key' => $key,
            'login' => rawurlencode($user->user_login)
        ), home_url('/angel-login/'));
        
        // Send enhanced email
        $sent = $this->send_password_reset_email($user, $reset_link);
        
        // Log password reset request
        $this->log_action('password_reset', 'requested', array(
            'user_id' => $user->ID,
            'email' => $email,
            'ip' => $ip_address
        ));
        
        if ($sent) {
            wp_send_json_success(array(
                'message' => esc_html__('Password reset link sent! Check your email (and spam folder just in case). ✨', 'greenangel-login')
            ));
        } else {
            wp_send_json_error(array('message' => esc_html__('Error sending email. Please contact support.', 'greenangel-login')));
        }
    }
    
    /**
     * Enhanced password reset handling
     */
    public function handle_reset_password() {
        check_ajax_referer('angel_login_nonce_action', 'nonce');
        
        $key = sanitize_text_field($_POST['key'] ?? '');
        $login = sanitize_text_field($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $ip_address = $this->get_client_ip();
        
        // Enhanced validation
        if (empty($password) || empty($password_confirm)) {
            wp_send_json_error(array('message' => esc_html__('Please enter a new password.', 'greenangel-login')));
            return;
        }
        
        if ($password !== $password_confirm) {
            wp_send_json_error(array('message' => esc_html__('Passwords do not match.', 'greenangel-login')));
            return;
        }
        
        // Enhanced password strength check
        if (!$this->validate_password_strength($password)) {
            wp_send_json_error(array('message' => esc_html__('Password must be at least 8 characters with uppercase, lowercase, number, and special character.', 'greenangel-login')));
            return;
        }
        
        // Verify the reset key
        $user = check_password_reset_key($key, $login);
        
        if (is_wp_error($user)) {
            $error_code = $user->get_error_code();
            
            $this->log_action('password_reset', 'failed_key', array(
                'login' => $login,
                'error_code' => $error_code,
                'ip' => $ip_address
            ));
            
            if ($error_code === 'expired_key') {
                wp_send_json_error(array('message' => esc_html__('This reset link has expired. Please request a new one.', 'greenangel-login')));
            } else {
                wp_send_json_error(array('message' => esc_html__('Invalid reset link. Please request a new one.', 'greenangel-login')));
            }
            return;
        }
        
        // Reset the password
        reset_password($user, $password);
        
        // Log successful password reset
        $this->log_action('password_reset', 'success', array(
            'user_id' => $user->ID,
            'ip' => $ip_address
        ));
        
        // Log the user in automatically
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        
        // Update login tracking
        $this->track_user_login($user->user_login, $user);
        
        // Get redirect URL
        $redirect_url = $this->get_post_login_redirect_url();
        
        wp_send_json_success(array(
            'message' => esc_html__('Password reset successful! Welcome back, angel! ✨', 'greenangel-login'),
            'redirect' => $redirect_url
        ));
    }
    
    /**
     * Enhanced password strength validation
     */
    private function validate_password_strength($password) {
        if (!get_option('greenangel_login_require_strong_passwords', true)) {
            return strlen($password) >= 8;
        }
        
        // Strong password requirements
        $checks = array(
            'length' => strlen($password) >= 8,
            'lowercase' => preg_match('/[a-z]/', $password),
            'uppercase' => preg_match('/[A-Z]/', $password),
            'number' => preg_match('/[0-9]/', $password),
            'special' => preg_match('/[^A-Za-z0-9]/', $password)
        );
        
        return array_sum($checks) >= 4; // Require at least 4 out of 5 criteria
    }
    
    /**
     * Enhanced user login tracking
     */
    public function track_user_login($user_login, $user) {
        $user_id = $user->ID;
        $current_time = current_time('mysql');
        $ip_address = $this->get_client_ip();
        
        // Update user meta with login info
        update_user_meta($user_id, 'last_login_time', $current_time);
        update_user_meta($user_id, 'last_login_ip', $ip_address);
        
        // Update login count
        $login_count = get_user_meta($user_id, 'total_logins', true) ?: 0;
        update_user_meta($user_id, 'total_logins', $login_count + 1);
        
        // Store login history (last 10 logins)
        $login_history = get_user_meta($user_id, 'login_history', true) ?: array();
        array_unshift($login_history, array(
            'time' => $current_time,
            'ip' => $ip_address,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ));
        
        // Keep only last 10 logins
        $login_history = array_slice($login_history, 0, 10);
        update_user_meta($user_id, 'login_history', $login_history);
    }
    
    /**
     * Track user logout
     */
    public function track_user_logout($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        if ($user_id) {
            update_user_meta($user_id, 'last_logout_time', current_time('mysql'));
            update_user_meta($user_id, 'last_logout_ip', $this->get_client_ip());
        }
    }
    
    /**
     * Enhanced newsletter subscription handling
     */
    private function handle_newsletter_subscription($email, $first_name, $last_name, $user_id) {
        // Multiple newsletter plugin integrations
        $newsletter_plugins = array(
            'newsletter_subscribe' => function($email, $name) {
                return newsletter_subscribe($email, array(
                    'name' => $name,
                    'status' => 'C'
                ));
            },
            'Newsletter::instance' => function($email, $name) {
                $newsletter = Newsletter::instance();
                return $newsletter->save_user(array(
                    'email' => $email,
                    'name' => $name,
                    'status' => 'C'
                ));
            },
            'tnp_subscribe' => function($email, $name) {
                return tnp_subscribe(array(
                    'email' => $email,
                    'name' => $name,
                    'status' => 'C'
                ));
            }
        );
        
        $full_name = trim($first_name . ' ' . $last_name);
        $subscribed = false;
        
        foreach ($newsletter_plugins as $function_name => $callback) {
            if (function_exists($function_name) || (strpos($function_name, '::') && class_exists(explode('::', $function_name)[0]))) {
                try {
                    $result = $callback($email, $full_name);
                    if ($result) {
                        $subscribed = true;
                        break;
                    }
                } catch (Exception $e) {
                    error_log('Newsletter subscription failed: ' . $e->getMessage());
                }
            }
        }
        
        // Custom newsletter action hook
        do_action('greenangel_newsletter_signup', $email, $first_name, $last_name, $user_id, $subscribed);
        
        return $subscribed;
    }
    
    /**
     * Enhanced display name generation and uniqueness
     */
    private function generate_random_display_name() {
        $emotions = array(
            'Happy', 'Giggly', 'Blissful', 'Chill', 'Mellow', 'Peaceful',
            'Groovy', 'Jazzy', 'Funky', 'Bouncy', 'Bubbly', 'Jolly',
            'Sleepy', 'Dreamy', 'Drowsy', 'Cozy', 'Lazy', 'Snoozy',
            'Silly', 'Goofy', 'Wonky', 'Wiggly', 'Dizzy', 'Loopy',
            'Trippy', 'Spacey', 'Zesty', 'Quirky', 'Wacky', 'Derpy',
            'Blazed', 'Baked', 'Fried', 'Toasted', 'Roasted', 'Cooked',
            'Lit', 'Faded', 'Zonked', 'Blitzed', 'Ripped', 'Lifted',
            'Cosmic', 'Mystic', 'Angelic', 'Divine', 'Blessed', 'Sacred',
            'Magical', 'Sparkly', 'Twinkly', 'Glittery', 'Shimmery', 'Starry',
            'Hungry', 'Munchy', 'Snacky', 'Crispy', 'Crunchy', 'Chewy',
            'Fluffy', 'Squishy', 'Cuddly', 'Fuzzy', 'Soft', 'Gentle',
            'Cloudy', 'Misty', 'Smoky', 'Steamy', 'Vapory', 'Airy',
            'Wild', 'Crazy', 'Smooth', 'Silky', 'Sassy', 'Cheeky'
        );
        
        $words = array(
            'Stoner', 'Blazer', 'Toker', 'Smoker', 'Puffer', 'Roller',
            'Bud', 'Nug', 'Flower', 'Herb', 'Leaf', 'Green',
            'Ganja', 'Mary', 'Jane', 'Chronic', 'Dank', 'Loud',
            'Joint', 'Blunt', 'Spliff', 'Doobie', 'Fatty', 'Cone',
            'Bowl', 'Bong', 'Pipe', 'Rig', 'Vape', 'Edible',
            'Kush', 'Haze', 'Diesel', 'Cookie', 'Cake', 'Widow',
            'Hash', 'Keef', 'Wax', 'Shatter', 'Rosin', 'Dab',
            'Munchie', 'Sesh', 'Wake', 'Bake', 'Puff', 'Toke',
            'THC', 'CBD', 'Terp', 'Grinder', 'Papers', 'Stash'
        );
        
        $emotion = $emotions[array_rand($emotions)];
        $word = $words[array_rand($words)];
        
        return $emotion . $word;
    }
    
    /**
     * Ensure display name uniqueness
     */
    private function ensure_unique_display_name($display_name) {
        $base_name = $display_name;
        $counter = 1;
        
        while ($this->display_name_exists($display_name)) {
            $counter++;
            $display_name = $base_name . $counter;
            
            // Prevent infinite loops
            if ($counter > 9999) {
                $display_name = $base_name . rand(10000, 99999);
                break;
            }
        }
        
        return $display_name;
    }
    
    /**
     * Check if display name exists
     */
    private function display_name_exists($display_name) {
        $user_query = new WP_User_Query(array(
            'search' => $display_name,
            'search_columns' => array('display_name'),
            'fields' => 'ID',
            'number' => 1
        ));
        
        return !empty($user_query->get_results());
    }
    
    /**
     * Enhanced redirect URL determination
     */
    private function get_post_login_redirect_url() {
        // Check for stored redirect URL from session
        if (session_id()) {
            $stored_redirect = $_SESSION['angel_redirect_after_login'] ?? '';
            if (!empty($stored_redirect)) {
                unset($_SESSION['angel_redirect_after_login']);
                return $stored_redirect;
            }
        }
        
        // Priority order for redirect
        $redirect_pages = array(
            get_page_by_path('angel-hub'),
            get_page_by_path('dashboard'),
            get_page_by_path('account')
        );
        
        foreach ($redirect_pages as $page) {
            if ($page && $page->post_status === 'publish') {
                return get_permalink($page->ID);
            }
        }
        
        // WooCommerce fallback
        if (function_exists('wc_get_page_id')) {
            $wc_account_page_id = wc_get_page_id('myaccount');
            if ($wc_account_page_id > 0) {
                return get_permalink($wc_account_page_id);
            }
        }
        
        // Final fallback
        return home_url('/');
    }
    
    /**
     * Enhanced password reset email
     */
    private function send_password_reset_email($user, $reset_link) {
        $site_name = get_bloginfo('name');
        $user_email = $user->user_email;
        $user_display_name = $user->display_name ?: $user->user_login;
        
        $subject = sprintf(__('%s - Password Reset Request', 'greenangel-login'), $site_name);
        
        // Enhanced HTML email template
        $message = $this->get_password_reset_email_template($user_display_name, $reset_link, $site_name);
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <' . get_option('admin_email') . '>'
        );
        
        return wp_mail($user_email, $subject, $message, $headers);
    }
    
    /**
     * Get password reset email template
     */
    private function get_password_reset_email_template($user_name, $reset_link, $site_name) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Password Reset - <?php echo esc_html($site_name); ?></title>
        </head>
        <body style="margin: 0; padding: 0; background-color: #151515; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #151515; padding: 40px 20px;">
                <tr>
                    <td align="center">
                        <table width="600" cellpadding="0" cellspacing="0" style="background: linear-gradient(145deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%); border-radius: 24px; border: 1px solid rgba(255, 255, 255, 0.1); overflow: hidden;">
                            <!-- LED Strip -->
                            <tr>
                                <td style="height: 3px; background: linear-gradient(90deg, #ff0080 0%, #ff8c00 14%, #ffd700 28%, #00ff00 42%, #00ffff 56%, #0080ff 70%, #8000ff 84%, #ff0080 100%);"></td>
                            </tr>
                            
                            <!-- Header -->
                            <tr>
                                <td style="padding: 40px 40px 20px; text-align: center;">
                                    <h1 style="color: #ffffff; font-size: 28px; font-weight: 700; margin: 0 0 8px 0;"><?php echo esc_html($site_name); ?></h1>
                                    <p style="color: rgba(255, 255, 255, 0.6); font-size: 16px; margin: 0;">Password Reset Request</p>
                                </td>
                            </tr>
                            
                            <!-- Content -->
                            <tr>
                                <td style="padding: 0 40px 40px; color: rgba(255, 255, 255, 0.8); line-height: 1.6;">
                                    <p>Hey <?php echo esc_html($user_name); ?>! ✨</p>
                                    
                                    <p>Someone requested a password reset for your account. If this was you, click the button below to create a new password:</p>
                                    
                                    <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                        <tr>
                                            <td align="center">
                                                <a href="<?php echo esc_url($reset_link); ?>" 
                                                   style="display: inline-block; background: linear-gradient(135deg, #2a2a2a 0%, #404040 100%); color: #ffffff; text-decoration: none; padding: 16px 32px; border-radius: 12px; font-weight: 600; border: 1px solid rgba(255, 255, 255, 0.2);">
                                                    Reset My Password
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <p>This link will expire in 24 hours for security reasons.</p>
                                    
                                    <p>If you didn't request this reset, just ignore this email and your password will remain unchanged.</p>
                                    
                                    <p>Stay magical! 🌟<br>
                                    The <?php echo esc_html($site_name); ?> Team</p>
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style="padding: 20px 40px; background: rgba(0, 0, 0, 0.2); border-top: 1px solid rgba(255, 255, 255, 0.1); text-align: center;">
                                    <p style="color: rgba(255, 255, 255, 0.4); font-size: 12px; margin: 0;">
                                        If the button doesn't work, copy and paste this link:<br>
                                        <a href="<?php echo esc_url($reset_link); ?>" style="color: rgba(255, 255, 255, 0.6); word-break: break-all;"><?php echo esc_url($reset_link); ?></a>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Enhanced logging system
     */
    private function log_action($action, $status, $additional_data = array()) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'greenangel_login_logs';
        
        $log_data = array(
            'user_id' => $additional_data['user_id'] ?? null,
            'email' => $additional_data['email'] ?? '',
            'ip_address' => $additional_data['ip'] ?? $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'action' => $action,
            'status' => $status,
            'timestamp' => current_time('mysql'),
            'additional_data' => maybe_serialize($additional_data)
        );
        
        $wpdb->insert($table_name, $log_data);
    }
    
    /**
     * Get client IP address with proxy support
     */
    private function get_client_ip() {
        $ip_keys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) && !empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                
                // Handle comma-separated IPs
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Check if current page is angel login page
     */
    private function is_angel_login_page() {
        $page_id = get_option('greenangel_login_page_id');
        return $page_id && is_page($page_id);
    }
    
    /**
     * Add LED-specific meta tags
     */
    public function add_led_meta_tags() {
        if ($this->is_angel_login_page()) {
            echo '<meta name="theme-color" content="#151515">' . "\n";
            echo '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">' . "\n";
            echo '<meta name="msapplication-navbutton-color" content="#151515">' . "\n";
        }
    }
    
    /**
     * Disable default WordPress login styles
     */
    public function disable_default_login_styles() {
        if ($this->is_angel_login_page()) {
            wp_dequeue_style('login');
            wp_dequeue_style('wp-admin');
        }
    }
    
    /**
     * Enhanced login error messages
     */
    public function enhance_login_errors($errors, $redirect_to) {
        // Only enhance errors on our custom login page
        if (!$this->is_angel_login_page()) {
            return $errors;
        }
        
        // Customize error messages for better UX
        $custom_errors = new WP_Error();
        
        foreach ($errors->get_error_codes() as $code) {
            $message = $errors->get_error_message($code);
            
            switch ($code) {
                case 'invalid_username':
                case 'invalid_email':
                case 'incorrect_password':
                    $custom_errors->add('login_failed', __('Invalid email or password. Please try again.', 'greenangel-login'));
                    break;
                    
                case 'empty_username':
                case 'empty_email':
                    $custom_errors->add('empty_credentials', __('Please enter your email address.', 'greenangel-login'));
                    break;
                    
                case 'empty_password':
                    $custom_errors->add('empty_credentials', __('Please enter your password.', 'greenangel-login'));
                    break;
                    
                default:
                    $custom_errors->add($code, $message);
                    break;
            }
        }
        
        return $custom_errors;
    }
    
    /**
     * Cleanup expired codes and old logs
     */
    public function cleanup_expired_codes() {
        global $wpdb;
        
        // Clean up expired codes
        $codes_table = $wpdb->prefix . 'greenangel_codes';
        $wpdb->query(
            "UPDATE $codes_table 
             SET is_active = 0 
             WHERE expires_date IS NOT NULL 
             AND expires_date < NOW() 
             AND is_active = 1"
        );
        
        // Clean up old logs (keep last 30 days)
        $logs_table = $wpdb->prefix . 'greenangel_login_logs';
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $logs_table 
                 WHERE timestamp < %s",
                date('Y-m-d H:i:s', strtotime('-30 days'))
            )
        );
        
        error_log('Green Angel Login: Cleanup completed');
    }
    
    /**
     * Cleanup plugin transients
     */
    private function cleanup_plugin_transients() {
        global $wpdb;
        
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_angel_%' 
             OR option_name LIKE '_transient_timeout_angel_%'"
        );
    }
}

// Initialize the plugin
new GreenAngelLogin();