<?php
/**
 * Angel Initiation Integration
 * 
 * Hooks the Angel Initiation module into the main GreenAngel Hub plugin
 * 
 * @package GreenAngel
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Integration class for Angel Initiation
 */
class AngelInitiationIntegration {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Initialize for frontend and AJAX requests
        if (!is_admin() || wp_doing_ajax()) {
            $this->loadAngelInitiation();
        }
        
        // Add admin hooks
        add_action('admin_menu', array($this, 'addAdminMenu'));
        add_action('admin_init', array($this, 'addAdminSettings'));
    }
    
    private function loadAngelInitiation() {
        // Include the main Angel Initiation class
        require_once dirname(__FILE__) . '/angel-initiation.php';
        
        // Initialize the Angel Initiation
        new AngelInitiation();
        
        // Add to the force create system
        add_action('init', array($this, 'force_create_angel_initiation_page'));
    }
    
    public function force_create_angel_initiation_page() {
        // Don't run on frontend or during AJAX
        if (!is_admin() || wp_doing_ajax()) {
            return;
        }
        
        // Security: Only allow admin users to force page creation
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Check if we already have the page
        $page_id = get_option('angel_initiation_page_id');
        if (!$page_id || !get_post($page_id)) {
            $this->create_angel_initiation_page();
        }
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
    
    public function addAdminMenu() {
        add_submenu_page(
            'greenangel-hub',
            'Angel Initiation',
            'Angel Initiation',
            'manage_options',
            'angel-initiation-settings',
            array($this, 'adminPage')
        );
    }
    
    public function adminPage() {
        ?>
        <div class="wrap">
            <h1>Angel Initiation Settings</h1>
            
            <div class="notice notice-success">
                <p><strong>🌟 Angel Initiation is Active!</strong> New users will be redirected to the initiation ceremony after registration.</p>
            </div>
            
            <div class="card">
                <h2>Initiation Flow</h2>
                <p>The Angel Initiation ceremony includes:</p>
                <ul>
                    <li>✨ <strong>Step 1:</strong> Stoner Name Generator (5 dice rolls)</li>
                    <li>🔮 <strong>Step 2:</strong> Tribe Sorting Ritual (fair distribution)</li>
                    <li>🌙 <strong>Step 3:</strong> DOB Collection (zodiac styling)</li>
                    <li>💫 <strong>Step 4:</strong> Final Blessing (£5 reward)</li>
                </ul>
            </div>
            
            <div class="card">
                <h2>User Statistics</h2>
                <?php $this->displayUserStats(); ?>
            </div>
            
            <div class="card">
                <h2>Tribe Distribution</h2>
                <?php $this->displayTribeStats(); ?>
            </div>
            
            <div class="card">
                <h2>Manual Actions</h2>
                <p>
                    <a href="<?php echo admin_url('admin.php?page=angel-initiation-settings&action=reset_user_initiation'); ?>" 
                       class="button button-secondary"
                       onclick="return confirm('Are you sure you want to reset initiation status for all users?');">
                        Reset All User Initiations
                    </a>
                </p>
                <p>
                    <a href="<?php echo home_url('/angel-initiation'); ?>" 
                       class="button button-primary" 
                       target="_blank">
                        Preview Initiation Flow
                    </a>
                </p>
            </div>
        </div>
        
        <style>
            .card {
                background: white;
                border: 1px solid #c3c4c7;
                border-radius: 4px;
                padding: 20px;
                margin: 20px 0;
            }
            
            .card h2 {
                margin-top: 0;
                color: #1d2327;
            }
            
            .stats-table {
                width: 100%;
                border-collapse: collapse;
                margin: 10px 0;
            }
            
            .stats-table th,
            .stats-table td {
                padding: 10px;
                border: 1px solid #ddd;
                text-align: left;
            }
            
            .stats-table th {
                background: #f9f9f9;
                font-weight: bold;
            }
            
            .tribe-dank { color: #ef4444; }
            .tribe-blazed { color: #3b82f6; }
            .tribe-holy { color: #10b981; }
        </style>
        <?php
    }
    
    private function displayUserStats() {
        global $wpdb;
        
        $total_users = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}");
        $initiated_users = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->usermeta} 
            WHERE meta_key = '_angel_onboarding_complete' 
            AND meta_value = '1'
        ");
        $pending_users = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->usermeta} 
            WHERE meta_key = '_angel_initiation_required' 
            AND meta_value = '1'
        ");
        
        echo '<table class="stats-table">';
        echo '<tr><th>Metric</th><th>Count</th></tr>';
        echo '<tr><td>Total Users</td><td>' . $total_users . '</td></tr>';
        echo '<tr><td>Initiated Angels</td><td>' . $initiated_users . '</td></tr>';
        echo '<tr><td>Pending Initiation</td><td>' . $pending_users . '</td></tr>';
        echo '</table>';
    }
    
    private function displayTribeStats() {
        global $wpdb;
        
        $tribes = $wpdb->get_results("
            SELECT meta_value as tribe, COUNT(*) as count 
            FROM {$wpdb->usermeta} 
            WHERE meta_key = '_angel_tribe' 
            GROUP BY meta_value
        ");
        
        $tribe_names = array(
            'dank_dynasty' => 'The Dank Dynasty 🔥',
            'blazed_ones' => 'The Blazed Ones 😇',
            'holy_smokes' => 'The Holy Smokes 💨'
        );
        
        echo '<table class="stats-table">';
        echo '<tr><th>Tribe</th><th>Members</th></tr>';
        
        foreach ($tribes as $tribe) {
            $tribe_name = isset($tribe_names[$tribe->tribe]) ? $tribe_names[$tribe->tribe] : $tribe->tribe;
            $class = 'tribe-' . str_replace('_', '-', $tribe->tribe);
            echo '<tr><td class="' . $class . '">' . $tribe_name . '</td><td>' . $tribe->count . '</td></tr>';
        }
        
        echo '</table>';
    }
    
    public function addAdminSettings() {
        // Handle admin actions
        if (isset($_GET['action']) && $_GET['action'] === 'reset_user_initiation') {
            $this->resetUserInitiations();
            wp_redirect(admin_url('admin.php?page=angel-initiation-settings&reset=success'));
            exit;
        }
        
        // Show reset success message
        if (isset($_GET['reset']) && $_GET['reset'] === 'success') {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p>All user initiations have been reset successfully!</p>';
                echo '</div>';
            });
        }
    }
    
    private function resetUserInitiations() {
        global $wpdb;
        
        // Remove all initiation-related meta
        $wpdb->delete($wpdb->usermeta, array('meta_key' => '_angel_onboarding_complete'));
        $wpdb->delete($wpdb->usermeta, array('meta_key' => '_angel_initiation_required'));
        $wpdb->delete($wpdb->usermeta, array('meta_key' => '_angel_initiation_started'));
        $wpdb->delete($wpdb->usermeta, array('meta_key' => '_angel_initiation_completed'));
        $wpdb->delete($wpdb->usermeta, array('meta_key' => '_angel_tribe'));
        $wpdb->delete($wpdb->usermeta, array('meta_key' => '_stoner_tag_locked'));
        $wpdb->delete($wpdb->usermeta, array('meta_key' => '_birth_month'));
        $wpdb->delete($wpdb->usermeta, array('meta_key' => '_birth_year'));
        $wpdb->delete($wpdb->usermeta, array('meta_key' => '_angel_initiation_coupon'));
    }
}

// Initialize the integration
AngelInitiationIntegration::getInstance();

/**
 * Helper function to check if user needs initiation
 */
function user_needs_angel_initiation($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
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

/**
 * Helper function to get user's initiation step
 */
function get_user_initiation_step($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return 'name_generator';
    }
    
    $user = get_user_by('id', $user_id);
    $display_name = $user->display_name;
    
    // Step 1: Name Generator
    if (empty($display_name) || !validate_r_format($display_name)) {
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

/**
 * Helper function to validate R format
 */
function validate_r_format($name) {
    return strpos(strtolower($name), 'r') !== false;
}

/**
 * Shortcode for displaying initiation status
 */
function angel_initiation_status_shortcode($atts) {
    $user_id = get_current_user_id();
    
    if (!$user_id) {
        return '<p>Please log in to view your initiation status.</p>';
    }
    
    $needs_initiation = user_needs_angel_initiation($user_id);
    
    if (!$needs_initiation) {
        $tribe = get_user_meta($user_id, '_angel_tribe', true);
        $completed = get_user_meta($user_id, '_angel_initiation_completed', true);
        
        $tribe_names = array(
            'dank_dynasty' => 'The Dank Dynasty 🔥',
            'blazed_ones' => 'The Blazed Ones 😇',
            'holy_smokes' => 'The Holy Smokes 💨'
        );
        
        $tribe_name = isset($tribe_names[$tribe]) ? $tribe_names[$tribe] : 'Unknown Tribe';
        
        return '<div class="angel-status-complete">
            <h3>🦋 Angel Initiation Complete</h3>
            <p><strong>Tribe:</strong> ' . $tribe_name . '</p>
            <p><strong>Completed:</strong> ' . date('F j, Y', strtotime($completed)) . '</p>
        </div>';
    }
    
    $current_step = get_user_initiation_step($user_id);
    $step_names = array(
        'name_generator' => 'Name Generator',
        'tribe_sorting' => 'Tribe Sorting',
        'dob_ritual' => 'DOB Ritual',
        'final_blessing' => 'Final Blessing'
    );
    
    $step_name = isset($step_names[$current_step]) ? $step_names[$current_step] : 'Unknown Step';
    
    return '<div class="angel-status-pending">
        <h3>✨ Angel Initiation in Progress</h3>
        <p><strong>Current Step:</strong> ' . $step_name . '</p>
        <p><a href="' . home_url('/angel-initiation') . '" class="button">Continue Initiation</a></p>
    </div>';
}

add_shortcode('angel_initiation_status', 'angel_initiation_status_shortcode');