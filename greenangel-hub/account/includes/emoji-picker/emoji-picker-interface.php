<?php
/**
 * 🌟 GREEN ANGEL EMOJI IDENTITY PICKER v2.0 - MODULAR EDITION
 * The most gorgeous emoji selection experience ever created!
 * Mobile-first LED console aesthetic with premium feel
 * NOW FULLY MODULAR WITH SEPARATE CSS/JS FILES! 🔥
 * LOCKED SYSTEM: Choose once, lock for 30 days! 🔒
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// 🎨 Enhanced Emoji Picker Shortcode with Full Interface
function greenangel_emoji_picker_shortcode($atts) {
    if (is_admin()) {
        return '';
    }
    
    // Get current user
    $user_id = get_current_user_id();
    if (!$user_id) {
        return '<div class="emoji-picker-login-required"><p>Please log in to choose your Angel identity! 💫</p></div>';
    }
    
    $user = get_userdata($user_id);
    $display_name = $user->display_name ?: $user->first_name ?: $user->user_login;
    
    // Get current emoji and lock status - PROPER LOCK SYSTEM!
    $current_emoji = get_user_meta($user_id, 'angel_identity_emoji', true) ?: '💎';
    $current_identity_name = get_user_meta($user_id, 'angel_identity_name', true);
    $current_identity_bio = get_user_meta($user_id, 'angel_identity_bio', true);
    $identity_set_date = get_user_meta($user_id, 'angel_identity_set_date', true);
    $lock_timestamp = get_user_meta($user_id, 'angel_identity_lock', true);
    $is_locked = $lock_timestamp && (time() < $lock_timestamp);
    $days_remaining = $is_locked ? ceil(($lock_timestamp - time()) / DAY_IN_SECONDS) : 0;
    
    // Format the "since" date nicely
    $identity_since_text = 'Angel Identity since today';
    if ($identity_set_date) {
        $set_date = new DateTime($identity_set_date);
        $now = new DateTime();
        $diff = $now->diff($set_date);
        
        if ($diff->days > 0) {
            if ($diff->days == 1) {
                $identity_since_text = 'Angel Identity since yesterday';
            } else {
                $identity_since_text = 'Angel Identity since ' . $diff->days . ' days ago';
            }
        } else {
            $identity_since_text = 'Angel Identity since today';
        }
    }
    
    // 🔥 SPICY CURATED EMOJI COLLECTION - NO BORING STUFF!
    $emoji_categories = [
        'mystical' => [
            'name' => 'Mystical ✨',
            'color' => '#aed604',
            'emojis' => ['✨', '🌟', '💫', '⭐', '🌙', '🔮', '💎', '👑', '🪄', '🧿', '🌌', '🦄', '👻', '🔥', '⚡', '🌈']
        ],
        'nature' => [
            'name' => 'Nature 🌿', 
            'color' => '#4caf50',
            'emojis' => ['🌿', '🍃', '🌸', '🌺', '🌻', '🌷', '🌹', '🌵', '🌴', '🦋', '🐝', '🌼', '🍄', '🌳', '🌱', '🌊']
        ],
        'cosmic' => [
            'name' => 'Cosmic 🚀',
            'color' => '#02a8d1', 
            'emojis' => ['🚀', '🛸', '🌍', '🌎', '🌏', '☄️', '🌠', '🔭', '👽', '🌑', '🪐', '⭐', '🌌', '🌙', '☀️', '🌞']
        ],
        'vibes' => [
            'name' => 'Vibes 💫',
            'color' => '#cf11a0',
            'emojis' => ['😈', '😏', '🥵', '🤤', '😍', '🥰', '😘', '💋', '👅', '🍑', '🍒', '🍓', '🔥', '💦', '💕', '💖']
        ],
        'animals' => [
            'name' => 'Animals 🦋',
            'color' => '#ff9800',
            'emojis' => ['🦋', '🐍', '🦁', '🐅', '🐆', '🦄', '🐉', '🦊', '🐺', '🐙', '🦈', '🐰', '🐱', '🐸', '🐢', '🦀']
        ],
        'spicy' => [
            'name' => 'Spicy 🌶️',
            'color' => '#f44336',
            'emojis' => ['🌶️', '🍆', '🍌', '🥒', '🥕', '🌭', '💊', '🚬', '🍷', '🍸', '🍺', '🥃', '💉', '🔞', '🆘', '⚠️']
        ],
        'party' => [
            'name' => 'Party 🎉',
            'color' => '#9c27b0',
            'emojis' => ['🎉', '🎊', '🥳', '🍾', '🥂', '🍻', '🎭', '🎪', '🎨', '🎯', '🎲', '🃏', '💰', '💸', '🎰', '🔮']
        ],
        'energy' => [
            'name' => 'Energy ⚡',
            'color' => '#ffeb3b',
            'emojis' => ['⚡', '💥', '🔥', '💢', '💯', '🚨', '⚠️', '☢️', '☣️', '🎯', '💣', '🧨', '🔋', '⭐', '💫', '✨']
        ]
    ];
    
    // Get Angel Hub URL - find the actual Angel Hub page created by the plugin
    $back_url = home_url('/');
    
    // First try to find the Angel Hub page by slug
    $angel_hub_page = get_page_by_path('angel-hub');
    
    if ($angel_hub_page && $angel_hub_page->post_status === 'publish') {
        // Get the proper permalink (works with both pretty permalinks and ?page_id= format)
        $back_url = get_permalink($angel_hub_page->ID);
    } else {
        // Fallback: try to find any page with the Angel Hub shortcode
        $pages_with_shortcode = get_posts([
            'post_type' => 'page',
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_wp_page_template',
                    'compare' => 'EXISTS'
                ]
            ],
            's' => '[greenangel_account_dashboard]',
            'posts_per_page' => 1
        ]);
        
        if (!empty($pages_with_shortcode)) {
            $back_url = get_permalink($pages_with_shortcode[0]->ID);
        }
    }
    
    ob_start();
    ?>
    
    <div class="emoji-picker-app">
        <!-- LED Strip Header (matching our dashboard style) -->
        <div class="emoji-picker-led-strip"></div>
        
        <!-- 🔙 GORGEOUS FLOATING BACK BUTTON - SLEEK BOTTOM LEFT -->
        <div class="emoji-back-button-container">
            <a href="<?php echo esc_url($back_url); ?>" class="emoji-back-button">
                <span class="back-arrow">←</span>
            </a>
        </div>
        
        <!-- Remove Shop Header but Keep Staging Bar + Hide Footer + Hide Halo Points -->
        <style>
            .site-header { display: none !important; }
            .elementor-location-header { display: none !important; }
            header { display: none !important; }
            .header { display: none !important; }
            
            /* Hide footer on emoji picker page */
            .site-footer { display: none !important; }
            .elementor-location-footer { display: none !important; }
            footer { display: none !important; }
            .footer { display: none !important; }
            
            /* Hide halo points mini app */
            .wll-launcher-button-container,
            #wll-launcher,
            .wll-container,
            [class*="wll-"],
            [id*="wll-"],
            .halo-points,
            .points-launcher,
            .loyalty-launcher { 
                display: none !important; 
            }
            
            /* Keep staging bar visible */
            .staging-bar,
            .dev-bar,
            [class*="staging"],
            [class*="dev-mode"] {
                display: block !important;
                position: relative !important;
                z-index: 999999 !important;
            }
        </style>
        
        <!-- Main Container -->
        <div class="emoji-container">
            
            <!-- Gorgeous Header Section -->
            <div class="emoji-header">
                <div class="emoji-header-content">
                    <div class="emoji-title-section">
                        <h1 class="emoji-main-title">
                            <span class="emoji-title-sparkle emoji-title-sparkle-left">✨</span>
                            <span class="emoji-title-text">Choose Your Angel Identity</span>
                            <span class="emoji-title-sparkle emoji-title-sparkle-right">✨</span>
                        </h1>
                        <p class="emoji-subtitle">Select an emoji to represent you in the Green Angel universe</p>
                    </div>
                    
                    <!-- Current Identity Display -->
                    <div class="current-identity-card">
                        <div class="current-identity-header">
                            <span class="current-identity-label">Current Identity</span>
                            <?php if ($is_locked): ?>
                                <div class="lock-status locked">
                                    <span class="lock-icon">🔒</span>
                                    <span class="lock-text">Locked for <?php echo $days_remaining; ?> days</span>
                                </div>
                            <?php else: ?>
                                <div class="lock-status unlocked">
                                    <span class="lock-icon">🔓</span>
                                    <span class="lock-text">Can choose identity</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="current-identity-display">
                            <div class="current-emoji-large"><?php echo $current_emoji; ?></div>
                            <div class="current-identity-info">
                                <div class="current-user-name"><?php echo esc_html($display_name); ?></div>
                                <div class="current-identity-since"><?php echo esc_html($identity_since_text); ?></div>
                                <?php if ($current_identity_bio): ?>
                                <div class="current-identity-bio"><?php echo esc_html($current_identity_bio); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($is_locked): ?>
                            <div class="lock-countdown">
                                <div class="countdown-bar">
                                    <div class="countdown-fill" style="width: <?php echo (30 - $days_remaining) / 30 * 100; ?>%"></div>
                                </div>
                                <div class="countdown-text">Next change available in <?php echo $days_remaining; ?> days</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            

            
            <!-- Category Navigation -->
            <div class="emoji-categories">
                <div class="category-header">
                    <span class="category-header-icon">🎨</span>
                    <span class="category-header-text">Browse Categories</span>
                    <?php if ($is_locked): ?>
                        <span class="category-locked-badge">Demo Mode</span>
                    <?php endif; ?>
                </div>
                
                <!-- 🎲 FATE PICKER BUTTON WITH ENTICING WIGGLE -->
                <div class="fate-picker-container">
                    <button class="fate-picker-button" id="fate-picker-btn" <?php echo $is_locked ? 'data-demo-mode="true"' : ''; ?>>
                        <span class="fate-icon">🎲</span>
                        <span class="fate-text"><?php echo $is_locked ? "Try fate picker (demo mode)" : "Can't decide? Let fate pick for you!"; ?></span>
                        <span class="fate-sparkle">✨</span>
                    </button>
                </div>
                
                <div class="category-tabs">
                    <?php foreach ($emoji_categories as $key => $category): ?>
                        <button class="category-tab" data-category="<?php echo $key; ?>" data-color="<?php echo $category['color']; ?>">
                            <span class="category-tab-text"><?php echo $category['name']; ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Emoji Grid -->
            <div class="emoji-grid-container">
                <?php foreach ($emoji_categories as $key => $category): ?>
                    <div class="emoji-grid" data-category="<?php echo $key; ?>" style="display: none;">
                        <div class="emoji-grid-header">
                            <span class="grid-category-name"><?php echo $category['name']; ?></span>
                            <span class="grid-emoji-count"><?php echo count($category['emojis']); ?> options</span>
                        </div>
                        
                        <div class="emoji-options">
                            <?php foreach ($category['emojis'] as $emoji): ?>
                                <button class="emoji-option <?php echo $is_locked ? 'locked' : ''; ?>" 
                                        data-emoji="<?php echo $emoji; ?>" 
                                        data-category="<?php echo $key; ?>"
                                        <?php echo $is_locked ? 'disabled' : ''; ?>>
                                    <span class="emoji-char"><?php echo $emoji; ?></span>
                                    <div class="emoji-hover-effect"></div>
                                    <?php if ($is_locked): ?>
                                        <div class="emoji-lock-overlay">🔒</div>
                                    <?php endif; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Preview Section (only show if not locked) -->
            <?php if (!$is_locked): ?>
            <div class="emoji-preview-section" style="display: none;">
                <div class="preview-header">
                    <span class="preview-icon">👁️</span>
                    <span class="preview-title">Preview Your Identity</span>
                </div>
                
                <div class="preview-contexts">
                    <div class="preview-context">
                        <div class="preview-label">Dashboard Header</div>
                        <div class="preview-example dashboard-preview">
                            <span class="preview-emoji"></span>
                            <span class="preview-name"><?php echo esc_html($display_name); ?></span>
                        </div>
                    </div>
                    
                    <div class="preview-context">
                        <div class="preview-label">Activity Card</div>
                        <div class="preview-example activity-preview">
                            <span class="preview-emoji"></span>
                            <span class="preview-text"><?php echo esc_html($display_name); ?> earned 100 Halo Points</span>
                        </div>
                    </div>
                    
                    <div class="preview-context">
                        <div class="preview-label">Packing Slip</div>
                        <div class="preview-example packing-preview">
                            <span class="preview-text">Packed with love by Green Angel for <?php echo esc_html($display_name); ?></span>
                            <span class="preview-emoji"></span>
                        </div>
                    </div>
                </div>
                
                <div class="preview-actions">
                    <button class="preview-action cancel">
                        <span>← Back to Selection</span>
                    </button>
                    <button class="preview-action confirm" data-emoji="">
                        <span>Set This Identity (30-day lock)</span>
                        <span class="confirm-icon">🔒</span>
                    </button>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Success Celebration -->
            <div class="success-celebration" style="display: none;">
                <div class="success-content">
                    <div class="success-emoji-large"></div>
                    <h2 class="success-title">Identity Set! ✨</h2>
                    <p class="success-message">Your Angel identity has been locked for 30 days!</p>
                    <button class="success-close">Continue to Dashboard</button>
                    <button class="success-close-alt">Close</button>
                </div>
                <div class="celebration-particles"></div>
            </div>
            
        </div>
        
        <!-- 🎲 FATE PICKER OVERLAY -->
        <div class="fate-picker-overlay" style="display: none;">
            <div class="fate-picker-modal">
                <div class="fate-modal-header">
                    <h2 class="fate-title">🔮 Let Fate Decide</h2>
                    <p class="fate-subtitle"><?php echo $is_locked ? "Demo mode - your identity won't actually change" : "The universe will choose your perfect Angel emoji"; ?></p>
                </div>
                
                <?php if (!$is_locked): ?>
                <div class="fate-warning">
                    <div class="warning-icon">⚠️</div>
                    <div class="warning-text">
                        <p><strong>Once you press this button, there is no going back!</strong></p>
                        <p>Fate will decide your Angel identity and it will be locked for 30 days.</p>
                    </div>
                </div>
                <?php else: ?>
                <div class="fate-demo-notice">
                    <div class="demo-icon">🎭</div>
                    <div class="demo-text">
                        <p><strong>Demo Mode Active</strong></p>
                        <p>Your identity is currently locked, so this is just for fun! The spinner will work but won't change your actual identity.</p>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="fate-emoji-display">
                    <div class="fate-emoji-large">🎲</div>
                </div>
                
                <div class="fate-actions">
                    <button class="fate-action cancel-fate">
                        <span>← Never mind, I'll choose</span>
                    </button>
                    <button class="fate-action embrace-fate">
                        <span><?php echo $is_locked ? "🎭 TRY DEMO FATE 🎭" : "🔥 EMBRACE YOUR FATE 🔥"; ?></span>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- 🎰 FATE SPINNER OVERLAY -->
        <div class="fate-spinner-overlay" style="display: none;">
            <div class="fate-spinner-content">
                <div class="fate-spinner-header">
                    <h2><?php echo $is_locked ? "Demo Fate Spinning..." : "The Universe is Deciding..."; ?></h2>
                </div>
                
                <div class="emoji-roulette">
                    <div class="roulette-emoji">🎲</div>
                </div>
                
                <div class="fate-progress">
                    <div class="fate-progress-bar">
                        <div class="fate-progress-fill"></div>
                    </div>
                    <p class="fate-progress-text"><?php echo $is_locked ? "Running demo fate..." : "Channeling cosmic energy..."; ?></p>
                </div>
            </div>
        </div>
        
        <!-- 🎉 FATE RESULT OVERLAY -->
        <div class="fate-result-overlay" style="display: none;">
            <div class="fate-result-content">
                <div class="fate-result-header">
                    <h2>✨ <?php echo $is_locked ? "Demo Fate Complete!" : "Fate Has Spoken!"; ?> ✨</h2>
                    <p><?php echo $is_locked ? "This is what fate would choose for you" : "The universe has chosen your Angel identity"; ?></p>
                </div>
                
                <div class="fate-chosen-emoji">
                    <div class="chosen-emoji-large"></div>
                </div>
                
                <div class="fate-result-message">
                    <p><?php echo $is_locked ? "Pretty cool choice, right? Come back when your identity unlocks to try for real!" : "This is who you are meant to be in the Angel universe!"; ?></p>
                </div>
                
                <div class="fate-result-actions">
                    <?php if ($is_locked): ?>
                        <button class="fate-result-action demo-close">
                            <span>Cool Demo!</span>
                            <span>✨</span>
                        </button>
                    <?php else: ?>
                        <button class="fate-result-action accept">
                            <span>Accept & Lock for 30 Days</span>
                            <span>🔒</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Confirmation Modal (only show if not locked) -->
        <?php if (!$is_locked): ?>
        <div class="emoji-modal-overlay" style="display: none;">
            <div class="emoji-modal">
                <div class="modal-led-strip"></div>
                <div class="modal-content">
                    <div class="modal-header">
                        <span class="modal-icon">⚠️</span>
                        <h3 class="modal-title">Confirm Your Angel Identity</h3>
                    </div>
                    
                    <div class="modal-body">
                        <div class="modal-emoji-display">
                            <span class="modal-emoji-large"></span>
                        </div>
                        <p class="modal-message">
                            Are you sure you want to set <strong class="modal-emoji-name"></strong> as your Angel identity?
                            <br><br>
                            <span class="modal-warning">🔒 This will lock your identity for 30 days!</span>
                        </p>
                    </div>
                    
                    <div class="modal-actions">
                        <button class="modal-button cancel">
                            <span>Cancel</span>
                        </button>
                        <button class="modal-button confirm">
                            <span>Lock Identity for 30 Days</span>
                            <span class="modal-lock-icon">🔒</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Lock Warning Modal (only show if locked) -->
        <?php if ($is_locked): ?>
        <div class="lock-warning-overlay" style="display: none;">
            <div class="lock-warning-modal">
                <div class="modal-led-strip"></div>
                <div class="modal-content">
                    <div class="modal-header">
                        <span class="modal-icon">🔒</span>
                        <h3 class="modal-title">Identity Currently Locked</h3>
                    </div>
                    
                    <div class="modal-body">
                        <p class="modal-message">
                            Your Angel identity is currently locked for <strong><?php echo $days_remaining; ?> more days</strong>.
                            <br><br>
                            You can change it again on <strong><?php echo date('F j, Y', $lock_timestamp); ?></strong>.
                            <br><br>
                            Feel free to explore the categories and try the fate picker in demo mode!
                        </p>
                    </div>
                    
                    <div class="modal-actions">
                        <button class="modal-button close">
                            <span>Understood</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- ANGEL HUB BACK URL FOR SUCCESS REDIRECT -->
        <script>
            window.emojiPickerBackUrl = '<?php echo esc_js($back_url); ?>';
            window.emojiPickerIsLocked = <?php echo $is_locked ? 'true' : 'false'; ?>;
            window.emojiPickerDaysRemaining = <?php echo intval($days_remaining); ?>;
        </script>
        
    </div>
    
    <?php
    return ob_get_clean();
}

// Enhanced shortcode registration
add_shortcode('greenangel_emoji_picker', 'greenangel_emoji_picker_shortcode');

// AJAX handler for saving emoji selection
add_action('wp_ajax_save_emoji_identity', 'handle_save_emoji_identity');
function handle_save_emoji_identity() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'emoji_identity_nonce')) {
        wp_die('Security check failed');
    }
    
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error('User not logged in');
    }
    
    $emoji = sanitize_text_field($_POST['emoji']);
    if (empty($emoji)) {
        wp_send_json_error('No emoji selected');
    }
    
    // Check if currently locked - PROPER LOCK ENFORCEMENT!
    $lock_timestamp = get_user_meta($user_id, 'angel_identity_lock', true);
    if ($lock_timestamp && (time() < $lock_timestamp)) {
        $days_remaining = ceil(($lock_timestamp - time()) / DAY_IN_SECONDS);
        wp_send_json_error('Identity is currently locked for ' . $days_remaining . ' more days');
    }
    
    // Get identity info if provided
    $identity_name = sanitize_text_field($_POST['identity_name'] ?? '');
    $identity_bio = sanitize_text_field($_POST['identity_bio'] ?? '');
    
    // Save new emoji and identity info WITH 30-DAY LOCK
    update_user_meta($user_id, 'angel_identity_emoji', $emoji);
    if ($identity_name) {
        update_user_meta($user_id, 'angel_identity_name', $identity_name);
    }
    if ($identity_bio) {
        update_user_meta($user_id, 'angel_identity_bio', $identity_bio);
    }
    
    // SET 30-DAY LOCK!
    update_user_meta($user_id, 'angel_identity_lock', time() + (30 * DAY_IN_SECONDS));
    update_user_meta($user_id, 'angel_identity_set_date', current_time('mysql'));
    
    wp_send_json_success([
        'emoji' => $emoji,
        'identity_name' => $identity_name,
        'identity_bio' => $identity_bio,
        'locked_until' => time() + (30 * DAY_IN_SECONDS),
        'message' => 'Identity locked for 30 days!'
    ]);
}

// Add nonce for AJAX requests
add_action('wp_head', 'add_emoji_picker_nonce');
function add_emoji_picker_nonce() {
    if (is_page('emoji-picker')) {
        echo '<script>window.emojiPickerNonce = "' . wp_create_nonce('emoji_identity_nonce') . '";</script>';
    }
}

// 🎨 MODULAR ASSET LOADING - Load CSS and JS on emoji picker page
add_action('wp_enqueue_scripts', 'enqueue_modular_emoji_picker_assets');
function enqueue_modular_emoji_picker_assets() {
    if (!is_page('emoji-picker')) {
        return;
    }
    
    $plugin_url = plugin_dir_url(__FILE__);
    $version = time(); // For development - use proper versioning in production
    
    // 🎨 CORE CSS
    wp_enqueue_style(
        'emoji-picker-core-styles',
        $plugin_url . 'css/emoji-picker-core.css',
        array(),
        $version
    );
    
    // 🎨 FATE CSS  
    wp_enqueue_style(
        'emoji-picker-fate-styles',
        $plugin_url . 'css/emoji-picker-fate.css',
        array('emoji-picker-core-styles'), // Depends on core
        $version
    );
    
    // 🎭 IDENTITY CSS
    wp_enqueue_style(
        'emoji-picker-identities-styles',
        $plugin_url . 'css/emoji-picker-identities.css',
        array('emoji-picker-core-styles'), // Depends on core
        $version
    );
    
    // 🌟 CORE JAVASCRIPT
    wp_enqueue_script(
        'emoji-picker-core-script',
        $plugin_url . 'js/emoji-picker-core.js',
        array(),
        $version,
        true
    );
    
    // 🎲 FATE JAVASCRIPT
    wp_enqueue_script(
        'emoji-picker-fate-script',
        $plugin_url . 'js/emoji-picker-fate.js',
        array('emoji-picker-core-script'), // Depends on core
        $version,
        true
    );
    
    // 🎭 IDENTITY JAVASCRIPT
    wp_enqueue_script(
        'emoji-picker-identities-script',
        $plugin_url . 'js/emoji-picker-identities.js',
        array('emoji-picker-core-script'), // Depends on core
        $version,
        true
    );
    
    // Add AJAX URL for JavaScript
    wp_localize_script('emoji-picker-core-script', 'emojiPickerData', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('emoji_identity_nonce')
    ));
}
?>