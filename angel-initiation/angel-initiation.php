<?php
/**
 * Angel Initiation Ceremony
 * 
 * Transforms new users into certified Angels through a mystical 4-step ceremony
 * 
 * 🚧 TESTING MODE ACTIVE:
 * - user_needs_initiation() always returns true
 * - get_user_initiation_step() always returns 'name_generator'
 * - Final completion is not saved to user meta
 * - Users can test the ceremony unlimited times
 * 
 * TO DISABLE TESTING MODE:
 * 1. Comment out "return true;" in user_needs_initiation()
 * 2. Comment out "return 'name_generator';" in get_user_initiation_step()
 * 3. Uncomment the update_user_meta lines in handle_final_blessing()
 * 4. Remove the testing notice from render_initiation_page()
 * 
 * @package GreenAngel
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class AngelInitiation {
    
    private $plugin_url;
    private $plugin_path;
    
    public function __construct() {
        $this->plugin_url = plugin_dir_url(__FILE__);
        $this->plugin_path = plugin_dir_path(__FILE__);
        
        add_action('init', array($this, 'init'));
        add_action('template_redirect', array($this, 'handle_initiation_redirect'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_angel_initiation_step', array($this, 'handle_initiation_step'));
        add_action('wp_ajax_nopriv_angel_initiation_step', array($this, 'handle_initiation_step'));
        
        // Register shortcode for the page
        add_shortcode('angel_initiation_ceremony', array($this, 'render_initiation_page'));
        
        // Plugin lifecycle hooks
        register_activation_hook(__FILE__, array($this, 'activate_plugin'));
    }
    
    public function init() {
        // Hook into registration completion
        add_action('user_register', array($this, 'mark_user_for_initiation'));
    }
    
    public function activate_plugin() {
        // Create the angel initiation page
        $this->create_angel_initiation_page();
    }
    
    private function create_angel_initiation_page() {
        $page_data = array(
            'post_title'    => 'Angel Initiation',
            'post_content'  => '[angel_initiation_ceremony]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'angel-initiation',
            'post_excerpt'  => 'Mystical ceremony to transform new users into certified Angels',
            'meta_input'    => array(
                '_angel_initiation_page' => 'true'
            )
        );
        
        // Check if page already exists
        $existing_page = get_page_by_path('angel-initiation');
        if (!$existing_page) {
            $page_id = wp_insert_post($page_data);
            
            // Store page ID for future reference
            update_option('angel_initiation_page_id', $page_id);
        } else {
            update_option('angel_initiation_page_id', $existing_page->ID);
        }
    }
    
    public function handle_initiation_redirect() {
        if (!is_user_logged_in()) {
            return;
        }
        
        $user_id = get_current_user_id();
        
        // Check if user needs initiation
        if ($this->user_needs_initiation($user_id)) {
            // Redirect to initiation if not already on the page
            if (!is_page('angel-initiation') && !is_admin()) {
                $angel_initiation_page = get_page_by_path('angel-initiation');
                if ($angel_initiation_page && $angel_initiation_page->post_status === 'publish') {
                    wp_redirect(get_permalink($angel_initiation_page->ID));
                    exit;
                }
            }
        }
    }
    
    public function user_needs_initiation($user_id) {
        // 🚧 TESTING MODE: Always allow initiation for testing
        // Comment out this line when ready for production
        return true;
        
        // Check if user has completed initiation
        $initiation_complete = get_user_meta($user_id, '_angel_onboarding_complete', true);
        if ($initiation_complete) {
            return false;
        }
        
        // Check if user is new (registered within last 30 days)
        $user = get_user_by('id', $user_id);
        $registration_date = strtotime($user->user_registered);
        $thirty_days_ago = strtotime('-30 days');
        
        return $registration_date > $thirty_days_ago;
    }
    
    public function get_user_initiation_step($user_id) {
        // 🚧 TESTING MODE: Always start from step 1 for testing
        // Comment out this line when ready for production
        return 'name_generator';
        
        $user = get_user_by('id', $user_id);
        $display_name = $user->display_name;
        
        // Step 1: Name Generator
        if (empty($display_name)) {
            return 'name_generator';
        }
        
        // Step 2: Tribe Sorting
        if (!get_user_meta($user_id, '_angel_tribe', true)) {
            return 'tribe_sorting';
        }
        
        // Step 3: DOB Collection
        if (!get_user_meta($user_id, '_birth_month', true) || !get_user_meta($user_id, '_birth_year', true)) {
            return 'dob_ritual';
        }
        
        // Step 4: Final Blessing
        return 'final_blessing';
    }
    
    private function validate_r_format($name) {
        // Check if name contains 'r' (Green Angel naming convention)
        return strpos(strtolower($name), 'r') !== false;
    }
    
    public function mark_user_for_initiation($user_id) {
        // Mark user as needing initiation
        update_user_meta($user_id, '_angel_initiation_required', true);
        update_user_meta($user_id, '_angel_initiation_started', current_time('mysql'));
    }
    
    public function render_initiation_page() {
        if (!is_user_logged_in()) {
            return '<p>Please <a href="' . wp_login_url(get_permalink()) . '">login</a> to access the Angel Initiation ceremony.</p>';
        }
        
        $user_id = get_current_user_id();
        
        // Check if already completed (disabled in testing mode)
        if (!$this->user_needs_initiation($user_id)) {
            return '<p>You have already completed the Angel Initiation ceremony! <a href="' . home_url('/account') . '">Go to your dashboard</a></p>';
        }
        
        // 🚧 TESTING MODE: Add testing notice
        $testing_notice = '
        <div style="background: #ff6b35; color: white; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
            <strong>🚧 TESTING MODE ACTIVE</strong><br>
            Initiation will reset after each completion for testing purposes.
        </div>';
        
        // Load the initiation template
        ob_start();
        echo $testing_notice;
        include $this->plugin_path . 'templates/initiation-page.php';
        return ob_get_clean();
    }
    
    public function enqueue_scripts() {
        // Only load on initiation page
        if (is_page('angel-initiation') || $this->is_angel_initiation_page()) {
            
            // Core initiation styles and scripts
            wp_enqueue_style('angel-initiation-base', $this->plugin_url . 'assets/css/initiation-base.css', array(), time());
            wp_enqueue_style('emoji-narrator', $this->plugin_url . 'assets/css/emoji-narrator.css', array(), time());
            wp_enqueue_script('angel-initiation-core', $this->plugin_url . 'assets/js/initiation-core.js', array('jquery'), time(), true);
            wp_enqueue_script('cosmic-background', $this->plugin_url . 'assets/js/cosmic-background.js', array('jquery'), time(), true);
            
            // Emoji Narrator System
            wp_enqueue_script('emoji-narrator', $this->plugin_url . 'assets/js/emoji-narrator.js', array('jquery'), time(), true);
            wp_enqueue_script('narrator-scripts', $this->plugin_url . 'assets/js/narrator-scripts.js', array('emoji-narrator'), time(), true);
            wp_enqueue_script('narrator-integration', $this->plugin_url . 'assets/js/narrator-integration.js', array('narrator-scripts', 'angel-initiation-core'), time(), true);
            
            // Load ALL step assets (since we transition between them)
            $user_id = get_current_user_id();
            $current_step = $this->get_user_initiation_step($user_id);
            
            // Step 1: Name Generator
            wp_enqueue_style('step-name-generator', $this->plugin_url . 'assets/css/step-1-name-generator.css', array(), time());
            wp_enqueue_script('name-generator', $this->plugin_url . 'assets/js/name-generator.js', array('jquery'), time(), true);
            
            // Step 2: Tribe Sorting
            wp_enqueue_style('step-tribe-sorting', $this->plugin_url . 'assets/css/step-2-tribe-sorting.css', array(), time());
            wp_enqueue_script('tribe-sorting', $this->plugin_url . 'assets/js/tribe-sorting.js', array('jquery'), time(), true);
            
            // Step 3: DOB Ritual
            wp_enqueue_style('step-dob-ritual', $this->plugin_url . 'assets/css/step-3-dob-ritual.css', array(), time());
            wp_enqueue_script('dob-ritual', $this->plugin_url . 'assets/js/dob-ritual.js', array('jquery'), time(), true);
            
            // Step 4: Final Blessing
            wp_enqueue_style('step-blessing', $this->plugin_url . 'assets/css/step-4-blessing.css', array(), time());
            wp_enqueue_script('blessing-celebration', $this->plugin_url . 'assets/js/blessing-celebration.js', array('jquery'), time(), true);
            
            // Localize script for AJAX
            wp_localize_script('angel-initiation-core', 'angelInitiation', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('angel_initiation_nonce'),
                'currentStep' => $current_step,
                'userId' => $user_id
            ));
        }
    }
    
    public function handle_initiation_step() {
        // Debug logging (remove in production)
        error_log('Angel Initiation AJAX Request: ' . print_r($_POST, true));
        
        // Try to verify nonce and catch any errors
        $nonce_check = wp_verify_nonce($_POST['nonce'], 'angel_initiation_nonce');
        if (!$nonce_check) {
            error_log('Nonce verification failed. Nonce: ' . $_POST['nonce']);
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page and try again.'));
            return;
        }
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(array('message' => 'User not logged in'));
            return;
        }
        
        $step = isset($_POST['step']) ? sanitize_text_field($_POST['step']) : '';
        $action = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';
        
        error_log("Processing step: $step, action: $action");
        
        switch ($step) {
            case 'name_generator':
                $this->handle_name_generator($user_id, $action);
                break;
                
            case 'tribe_sorting':
                $this->handle_tribe_sorting($user_id, $action);
                break;
                
            case 'dob_ritual':
                $this->handle_dob_ritual($user_id, $action);
                break;
                
            case 'final_blessing':
                $this->handle_final_blessing($user_id, $action);
                break;
        }
        
        wp_die();
    }
    
    private function handle_name_generator($user_id, $action) {
        if ($action === 'generate_name') {
            $name = $this->generate_stoner_name();
            wp_send_json_success(array('name' => $name));
        }
        
        if ($action === 'lock_name') {
            $chosen_name = sanitize_text_field($_POST['chosen_name']);
            if (!empty($chosen_name)) {
                wp_update_user(array('ID' => $user_id, 'display_name' => $chosen_name));
                update_user_meta($user_id, '_stoner_tag_locked', true);
                wp_send_json_success(array('message' => 'Name locked successfully!'));
            } else {
                wp_send_json_error(array('message' => 'Please choose a name first'));
            }
        }
    }
    
    private function handle_tribe_sorting($user_id, $action) {
        if ($action === 'sort_tribe') {
            $tribe = $this->sort_into_tribe($user_id);
            wp_send_json_success(array('tribe' => $tribe));
        }
        
        if ($action === 'accept_tribe') {
            $tribe_key = sanitize_text_field($_POST['tribe']);
            if (in_array($tribe_key, ['dank_dynasty', 'blazed_ones', 'holy_smokes'])) {
                update_user_meta($user_id, '_angel_tribe', $tribe_key);
                wp_send_json_success(array('message' => 'Tribe accepted successfully!'));
            } else {
                wp_send_json_error(array('message' => 'Invalid tribe selection'));
            }
        }
    }
    
    private function handle_dob_ritual($user_id, $action) {
        if ($action === 'save_dob') {
            $birth_month = sanitize_text_field($_POST['birth_month']);
            $birth_year = sanitize_text_field($_POST['birth_year']);
            
            if ($birth_month && $birth_year) {
                update_user_meta($user_id, '_birth_month', $birth_month);
                update_user_meta($user_id, '_birth_year', $birth_year);
                wp_send_json_success(array('message' => 'Birth date saved successfully!'));
            } else {
                wp_send_json_error(array('message' => 'Please select both month and year'));
            }
        }
    }
    
    private function handle_final_blessing($user_id, $action) {
        if ($action === 'complete_initiation') {
            // Award wallet credit
            $this->award_initiation_credit($user_id);
            
            // 🚧 TESTING MODE: Comment out these lines to prevent saving completion
            // Uncomment when ready for production
            // update_user_meta($user_id, '_angel_onboarding_complete', true);
            // update_user_meta($user_id, '_angel_initiation_completed', current_time('mysql'));
            
            wp_send_json_success(array('message' => 'Initiation completed! Welcome, Angel!'));
        }
    }
    
    private function generate_stoner_name() {
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
    
    private function sort_into_tribe($user_id) {
        global $wpdb;
        
        $tribes = array(
            'dank_dynasty' => array(
                'name' => 'The Dank Dynasty',
                'emoji' => '🔥',
                'tagline' => 'Stoner royalty, loud luxury, golden chaos'
            ),
            'blazed_ones' => array(
                'name' => 'The Blazed Ones',
                'emoji' => '😇',
                'tagline' => 'OG angels, mellow & faded'
            ),
            'holy_smokes' => array(
                'name' => 'The Holy Smokes',
                'emoji' => '💨',
                'tagline' => 'Spiritual tokers, mystic puffers'
            )
        );
        
        // Get tribe distribution
        $tribe_counts = $wpdb->get_results("
            SELECT meta_value as tribe, COUNT(*) as count 
            FROM {$wpdb->usermeta} 
            WHERE meta_key = '_angel_tribe' 
            GROUP BY meta_value
        ");
        
        // Find tribe with lowest count
        $min_tribe = 'dank_dynasty';
        $min_count = PHP_INT_MAX;
        
        foreach ($tribe_counts as $tc) {
            if ($tc->count < $min_count) {
                $min_count = $tc->count;
                $min_tribe = $tc->tribe;
            }
        }
        
        // Add 20% randomness
        if (rand(1, 100) <= 20) {
            $tribe_keys = array_keys($tribes);
            $min_tribe = $tribe_keys[array_rand($tribe_keys)];
        }
        
        update_user_meta($user_id, '_angel_tribe', $min_tribe);
        
        return $tribes[$min_tribe];
    }
    
    private function award_initiation_credit($user_id) {
        // Check if Angel Wallet is available
        if (class_exists('AngelWallet')) {
            $wallet = new AngelWallet();
            $wallet->credit_user($user_id, 5.00, 'Angel initiation blessing');
        } else {
            // Fallback: Create a £5 coupon
            $coupon_code = 'ANGEL' . strtoupper(substr(md5($user_id . time()), 0, 6));
            
            $coupon = array(
                'post_title' => $coupon_code,
                'post_content' => 'Angel initiation blessing coupon',
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type' => 'shop_coupon'
            );
            
            $new_coupon_id = wp_insert_post($coupon);
            
            // Set coupon properties
            update_post_meta($new_coupon_id, 'discount_type', 'fixed_cart');
            update_post_meta($new_coupon_id, 'coupon_amount', 5);
            update_post_meta($new_coupon_id, 'usage_limit', 1);
            update_post_meta($new_coupon_id, 'customer_email', array(get_user_by('id', $user_id)->user_email));
            update_post_meta($new_coupon_id, 'expiry_date', date('Y-m-d', strtotime('+1 year')));
            
            // Store coupon code in user meta
            update_user_meta($user_id, '_angel_initiation_coupon', $coupon_code);
        }
    }
    
    private function is_angel_initiation_page() {
        $page_id = get_option('angel_initiation_page_id');
        return $page_id && is_page($page_id);
    }
}

// Initialize the plugin
new AngelInitiation();

// Activation hook to flush rewrite rules
register_activation_hook(__FILE__, function() {
    flush_rewrite_rules();
});