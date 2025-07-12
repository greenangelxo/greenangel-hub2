<?php
/**
 * 🌿 GREEN ANGEL HUB v2.0 - HEADER PROFILE BANNER
 * Mobile-first, compact, colorful and absolutely stunning
 * NOW WITH EMOJI IDENTITY SYSTEM! 🎭
 * ENHANCED: Full dashboard ↔ emoji picker integration!
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function ga_render_header_section($user_id) {
    // Force fresh user data
    wp_cache_delete($user_id, 'users');
    clean_user_cache($user_id);
    $user = get_userdata($user_id);
    
    if (!$user) return '';
    
    // Get display name with smart truncation
    $raw_display = $user->display_name;
    if (empty($raw_display)) {
        $raw_display = $user->first_name ?: $user->user_login;
    }
    
    $max_length = 12;
    $display_name = esc_html(strlen($raw_display) > $max_length ? 
        mb_substr($raw_display, 0, $max_length - 1) . '…' : $raw_display);
    
    // 🎭 ENHANCED EMOJI IDENTITY SYSTEM!
    $user_emoji = get_user_meta($user_id, 'angel_identity_emoji', true);
    $user_identity_name = get_user_meta($user_id, 'angel_identity_name', true);
    $user_identity_bio = get_user_meta($user_id, 'angel_identity_bio', true);
    $identity_set_date = get_user_meta($user_id, 'angel_identity_set_date', true);
    
    // 🎯 SMART IDENTITY DETECTION!
    $has_chosen_identity = !empty($user_emoji) && $user_emoji !== '💎';
    $current_emoji = $user_emoji ?: '💎'; // Default to diamond
    
    // 🎭 GET EMOJI PICKER URL - PLUGIN-WIRED LINK!
    $emoji_picker_url = ga_get_emoji_picker_url();
    
    // WooCommerce customer data
    $customer = new WC_Customer($user_id);
    $total_spent = $customer->get_total_spent();
    $all_orders = wc_get_orders(['customer' => $user_id, 'limit' => -1]);
    
    // Filter completed orders for accurate count
    $completed_orders = array_filter($all_orders, function($order) {
        return $order->get_status() === 'completed';
    });
    $order_count = count($completed_orders);
    
    // Get loyalty points
    $loyalty_points = greenangel_get_wp_loyalty_points_safe($user_id);
    $halo_points = $loyalty_points['available'];
    $redeemed_points = $loyalty_points['redeemed'];
    
    // Get wallet balance if function exists
    $wallet_balance = 0;
    if (function_exists('greenangel_get_wallet_balance')) {
        $wallet_balance = greenangel_get_wallet_balance($user_id);
    }
    
    // Determine Angel status and next level
    $angel_status = 'Member';
    $status_emoji = '💎';
    $next_level = 'VIP Angel';
    $next_threshold = 500;
    $progress_percent = 0;
    
    if ($total_spent >= 500) {
        $angel_status = 'VIP Angel';
        $status_emoji = '💎';
        $next_level = 'Elite Angel';
        $next_threshold = 1000;
        if ($total_spent < 1000) {
            $progress_percent = (($total_spent - 500) / 500) * 100;
        }
    }
    
    if ($total_spent >= 1000) {
        $angel_status = 'Elite Angel';
        $status_emoji = '👑';
        $next_level = 'Legend Status';
        $next_threshold = 2500;
        if ($total_spent < 2500) {
            $progress_percent = (($total_spent - 1000) / 1500) * 100;
        }
    }
    
    if ($total_spent >= 2500) {
        $angel_status = 'Legend';
        $status_emoji = '⭐';
        $next_level = 'Maximum Level';
        $progress_percent = 100;
    }
    
    // First order date
    $member_since = 'New Angel';
    if (!empty($completed_orders)) {
        $first = end($completed_orders);
        $member_since = $first->get_date_created()->format('M Y');
    }
    
    // Get last login time and IP address
    $last_login = get_user_meta($user_id, 'last_login_time', true);
    
    // Better login logic - if no previous login recorded, show "Active now"
    if (!$last_login) {
        $last_login_display = 'Active now';
        // Set initial login time
        update_user_meta($user_id, 'last_login_time', current_time('mysql'));
    } else {
        // Check if last login was more than 1 hour ago
        $time_diff = time() - strtotime($last_login);
        if ($time_diff < 3600) { // Less than 1 hour
            $last_login_display = 'Active now';
        } else {
            $last_login_display = ga_cute_time_ago($last_login);
        }
        // Update login time for next visit
        update_user_meta($user_id, 'last_login_time', current_time('mysql'));
    }
    
    // Get user's IP address (check for proxy headers)
    $user_ip = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $user_ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $user_ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $user_ip = $_SERVER['REMOTE_ADDR'];
    }
    $user_ip = filter_var(trim($user_ip), FILTER_VALIDATE_IP) ? trim($user_ip) : 'Local';
    
    ob_start();
    ?>
    
    <div class="ga-header-new">
        <!-- Main Header Card -->
        <div class="ga-header-card">
            <!-- Top Row: Avatar + Welcome + Quick Stats -->
            <div class="ga-header-top">
                <div class="ga-avatar-section">
                    <?php if ($emoji_picker_url): ?>
                    <a href="<?php echo esc_url($emoji_picker_url); ?>" class="ga-avatar-link" title="<?php echo $has_chosen_identity ? 'Change your Angel identity' : 'Choose your Angel identity'; ?>">
                        <div class="ga-avatar-glow <?php echo $has_chosen_identity ? 'ga-identity-chosen' : 'ga-identity-needed'; ?>">
                            <div class="ga-avatar <?php echo !$has_chosen_identity ? 'ga-avatar-needs-identity' : 'ga-avatar-has-identity'; ?>">
                                <?php echo $current_emoji; ?>
                            </div>
                            <?php if (!$has_chosen_identity): ?>
                            <div class="ga-identity-notification-badge" title="Choose your Angel identity!">!</div>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php else: ?>
                    <div class="ga-avatar-glow <?php echo $has_chosen_identity ? 'ga-identity-chosen' : 'ga-identity-needed'; ?>">
                        <div class="ga-avatar <?php echo !$has_chosen_identity ? 'ga-avatar-needs-identity' : 'ga-avatar-has-identity'; ?>">
                            <?php echo $current_emoji; ?>
                        </div>
                        <?php if (!$has_chosen_identity): ?>
                        <div class="ga-identity-notification-badge">!</div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="ga-welcome-section">
                    <div class="ga-greeting">
                        <span class="ga-username"><?php echo $display_name; ?></span>
                        <div class="ga-status-badge-inside">
                            <?php echo $status_emoji; ?> <?php echo $angel_status; ?>
                            <?php if ($has_chosen_identity && $user_identity_name): ?>
                            <span class="ga-identity-name-tag">• <?php echo esc_html($user_identity_name); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($has_chosen_identity && $user_identity_bio): ?>
                    <div class="ga-identity-bio-display">
                        <?php echo esc_html($user_identity_bio); ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="ga-quick-stats">
                    <a href="<?php echo wc_get_account_endpoint_url('halo-points'); ?>" class="ga-stat-item halo">
                        <div class="ga-stat-value"><?php echo ga_format_number($halo_points); ?></div>
                        <div class="ga-stat-label">HALO</div>
                    </a>
                    <a href="<?php echo wc_get_account_endpoint_url('wallet'); ?>" class="ga-stat-item wallet">
                        <div class="ga-stat-value">£<?php echo number_format($wallet_balance, 0); ?></div>
                        <div class="ga-stat-label">WALLET</div>
                    </a>
                </div>
            </div>
            
            <!-- Bottom Row: Progress + Member Info -->
            <div class="ga-header-bottom">
                <?php if ($progress_percent < 100): ?>
                <div class="ga-progress-section">
                    <div class="ga-progress-info">
                        <span class="ga-progress-text">Next Level: <?php echo $next_level; ?></span>
                        <span class="ga-progress-percent"><?php echo round($progress_percent); ?>%</span>
                    </div>
                    <div class="ga-progress-bar">
                        <div class="ga-progress-fill" style="width: <?php echo $progress_percent; ?>%"></div>
                        <div class="ga-progress-shine"></div>
                    </div>
                </div>
                <?php else: ?>
                <div class="ga-max-level">
                    <span class="ga-max-text">🏆 Maximum Level Achieved!</span>
                </div>
                <?php endif; ?>
                
                <div class="ga-member-info">
                    <!-- 🌟 GORGEOUS SHIMMERING PILLS -->
                    <div class="ga-login-pills">
                        <div class="ga-member-pill">
                            <span class="ga-member-pill-icon">💖</span>
                            <span class="ga-member-pill-text">Since <?php echo $member_since; ?></span>
                        </div>
                        
                        <div class="ga-login-pill">
                            <span class="ga-login-pill-icon">🔐</span>
                            <span class="ga-login-pill-text"><?php echo $last_login_display; ?></span>
                        </div>
                        
                        <div class="ga-ip-pill">
                            <span class="ga-ip-pill-icon">🌐</span>
                            <span class="ga-ip-pill-text"><?php echo esc_html($user_ip); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // 🎬 Enhanced progress bar animation on load
    document.addEventListener('DOMContentLoaded', function() {
        const progressFill = document.querySelector('.ga-progress-fill');
        if (progressFill) {
            const targetWidth = progressFill.style.width;
            progressFill.style.width = '0%';
            progressFill.style.opacity = '0';
            
            setTimeout(() => {
                progressFill.style.transition = 'width 2s cubic-bezier(0.4, 0, 0.2, 1), opacity 1s ease';
                progressFill.style.width = targetWidth;
                progressFill.style.opacity = '1';
            }, 1000);
        }
        
        // 🎨 Add subtle hover enhancement to stats
        const statItems = document.querySelectorAll('.ga-stat-item');
        statItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px) scale(1.02)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(-2px) scale(1)';
            });
        });
        
        // 🌟 Add magical click effect to avatar
        const avatar = document.querySelector('.ga-avatar');
        if (avatar) {
            avatar.addEventListener('click', function() {
                this.style.animation = 'none';
                this.offsetHeight; // Trigger reflow
                this.style.animation = 'identityBounce 0.6s ease';
                
                setTimeout(() => {
                    this.style.animation = '';
                }, 600);
            });
        }
        
        // 🎭 SMART TOOLTIP SYSTEM
        const avatarLink = document.querySelector('.ga-avatar-link');
        if (avatarLink) {
            const hasIdentity = <?php echo $has_chosen_identity ? 'true' : 'false'; ?>;
            const currentEmoji = '<?php echo $current_emoji; ?>';
            const identityName = <?php echo $user_identity_name ? '"' . esc_js($user_identity_name) . '"' : 'null'; ?>;
            
            // Update tooltip dynamically
            if (hasIdentity && identityName) {
                avatarLink.title = `${currentEmoji} ${identityName} - Click to change your identity`;
            } else {
                avatarLink.title = `${currentEmoji} Choose your Angel identity!`;
            }
        }
    });
    
    // 🎯 IDENTITY REFRESH FUNCTION - Called from emoji picker on return
    window.refreshDashboardIdentity = function(emoji, identityName, identityBio) {
        console.log('🎭 Refreshing dashboard identity:', { emoji, identityName, identityBio });
        
        // Update avatar emoji
        const avatarEmoji = document.querySelector('.ga-avatar');
        if (avatarEmoji) {
            avatarEmoji.textContent = emoji;
            
            // Remove needs-identity classes
            avatarEmoji.classList.remove('ga-avatar-needs-identity');
            avatarEmoji.classList.add('ga-avatar-has-identity');
        }
        
        // Update glow state
        const avatarGlow = document.querySelector('.ga-avatar-glow');
        if (avatarGlow) {
            avatarGlow.classList.remove('ga-identity-needed');
            avatarGlow.classList.add('ga-identity-chosen');
        }
        
        // Remove notification badge
        const notificationBadge = document.querySelector('.ga-identity-notification-badge');
        if (notificationBadge) {
            notificationBadge.style.animation = 'badgeDisappear 0.5s ease forwards';
            setTimeout(() => {
                notificationBadge.remove();
            }, 500);
        }
        
        // Update status badge if identity name provided
        if (identityName) {
            const statusBadge = document.querySelector('.ga-status-badge-inside');
            if (statusBadge && !statusBadge.querySelector('.ga-identity-name-tag')) {
                const nameTag = document.createElement('span');
                nameTag.className = 'ga-identity-name-tag';
                nameTag.textContent = '• ' + identityName;
                statusBadge.appendChild(nameTag);
            }
        }
        
        // Add or update bio display
        if (identityBio) {
            const welcomeSection = document.querySelector('.ga-welcome-section');
            let bioDisplay = welcomeSection.querySelector('.ga-identity-bio-display');
            
            if (!bioDisplay) {
                bioDisplay = document.createElement('div');
                bioDisplay.className = 'ga-identity-bio-display';
                welcomeSection.appendChild(bioDisplay);
            }
            
            bioDisplay.textContent = identityBio;
            bioDisplay.style.opacity = '0';
            bioDisplay.style.transform = 'translateY(10px)';
            
            setTimeout(() => {
                bioDisplay.style.transition = 'all 0.5s ease';
                bioDisplay.style.opacity = '1';
                bioDisplay.style.transform = 'translateY(0)';
            }, 100);
        }
        
        // Update tooltip
        const avatarLink = document.querySelector('.ga-avatar-link');
        if (avatarLink && identityName) {
            avatarLink.title = `${emoji} ${identityName} - Click to change your identity`;
        }
        
        // Add identity pill to member info
        if (identityName) {
            const loginPills = document.querySelector('.ga-login-pills');
            if (loginPills && !loginPills.querySelector('.ga-identity-pill')) {
                const identityPill = document.createElement('div');
                identityPill.className = 'ga-identity-pill';
                identityPill.innerHTML = `
                    <span class="ga-identity-pill-icon">🎭</span>
                    <span class="ga-identity-pill-text">Identity set just now</span>
                `;
                loginPills.appendChild(identityPill);
            }
        }
        
        // Celebration effect
        createIdentityUpdateCelebration();
    };
    
    // 🎉 IDENTITY UPDATE CELEBRATION
    function createIdentityUpdateCelebration() {
        const particles = ['✨', '🌟', '💫', '⭐'];
        const avatar = document.querySelector('.ga-avatar');
        
        if (!avatar) return;
        
        const rect = avatar.getBoundingClientRect();
        
        for (let i = 0; i < 8; i++) {
            const particle = document.createElement('div');
            particle.textContent = particles[Math.floor(Math.random() * particles.length)];
            particle.style.position = 'fixed';
            particle.style.left = rect.left + rect.width / 2 + 'px';
            particle.style.top = rect.top + rect.height / 2 + 'px';
            particle.style.fontSize = '1.2rem';
            particle.style.pointerEvents = 'none';
            particle.style.zIndex = '9999';
            particle.style.animation = 'identityParticle ' + (1.5 + Math.random() * 0.5) + 's ease-out forwards';
            
            // Random direction
            const angle = (Math.PI * 2 * i) / 8;
            const distance = 40 + Math.random() * 30;
            particle.style.setProperty('--end-x', Math.cos(angle) * distance + 'px');
            particle.style.setProperty('--end-y', Math.sin(angle) * distance + 'px');
            
            document.body.appendChild(particle);
            
            setTimeout(() => {
                particle.remove();
            }, 2000);
        }
    }
    
    // 🎭 Add dynamic CSS for enhanced animations
    const enhancedStyles = document.createElement('style');
    enhancedStyles.textContent = `
        @keyframes identityBounce {
            0%, 100% { transform: scale(1) rotate(0deg); }
            25% { transform: scale(1.1) rotate(-5deg); }
            50% { transform: scale(1.05) rotate(5deg); }
            75% { transform: scale(1.08) rotate(-2deg); }
        }
        
        @keyframes identityParticle {
            0% {
                transform: translate(0, 0) scale(1);
                opacity: 1;
            }
            100% {
                transform: translate(var(--end-x), var(--end-y)) scale(0);
                opacity: 0;
            }
        }
        
        @keyframes badgeDisappear {
            0% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.5;
                transform: scale(1.2);
            }
            100% {
                opacity: 0;
                transform: scale(0);
            }
        }
        
        .ga-stat-item {
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .ga-avatar {
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Enhanced focus states for accessibility */
        .ga-avatar:focus {
            outline: 3px solid #aed604;
            outline-offset: 3px;
        }
        
        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            .ga-progress-fill {
                transition: width 0.3s ease !important;
            }
            
            .ga-stat-item {
                transition: none !important;
            }
            
            .ga-avatar {
                transition: none !important;
            }
        }
    `;
    document.head.appendChild(enhancedStyles);
    </script>
    
    <?php
    return ob_get_clean();
}

/**
 * 🎭 GET EMOJI PICKER URL - PLUGIN-WIRED FUNCTION!
 */
function ga_get_emoji_picker_url() {
    // Try to get the page ID from the plugin's stored option
    $page_id = get_option('greenangel_emoji_picker_page_id');
    
    if ($page_id) {
        $page = get_post($page_id);
        if ($page && $page->post_status === 'publish') {
            return get_permalink($page_id);
        }
    }
    
    // Fallback: Find page by slug
    $emoji_page = get_page_by_path('emoji-picker');
    if ($emoji_page && $emoji_page->post_status === 'publish') {
        return get_permalink($emoji_page->ID);
    }
    
    // Last resort: Search for any page with the shortcode
    $pages_with_shortcode = get_posts([
        'post_type' => 'page',
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key' => '_wp_page_template',
                'compare' => 'EXISTS'
            ]
        ],
        's' => '[greenangel_emoji_picker]',
        'posts_per_page' => 1
    ]);
    
    if (!empty($pages_with_shortcode)) {
        return get_permalink($pages_with_shortcode[0]->ID);
    }
    
    // If nothing found, return null (button won't be clickable)
    return null;
}

/**
 * 🌿 HELPER: Format Large Numbers
 */
function ga_format_number($number) {
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return round($number / 1000, 1) . 'K';
    }
    return number_format($number);
}

/**
 * 💫 HELPER: Cute Time Ago for Notifications
 */
function ga_cute_time_ago($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time / 60) . 'min ago';
    if ($time < 86400) {
        $hours = floor($time / 3600);
        return $hours . 'hr' . ($hours != 1 ? 's' : '') . ' ago';
    }
    if ($time < 604800) {
        $days = floor($time / 86400);
        return $days . ' day' . ($days != 1 ? 's' : '') . ' ago';
    }
    if ($time < 2592000) {
        $weeks = floor($time / 604800);
        return $weeks . ' week' . ($weeks != 1 ? 's' : '') . ' ago';
    }
    
    $months = floor($time / 2592000);
    return $months . ' month' . ($months != 1 ? 's' : '') . ' ago';
}
?>