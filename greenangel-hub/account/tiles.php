<?php
/**
 * GREEN ANGEL HUB v2.0 - BREATHTAKING NAVIGATION TILES
 * App-style navigation with immersive, alive UI that feels absolutely magical ✨
 * NOW WRAPPED IN GORGEOUS PREMIUM CONTAINER!
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function ga_render_navigation_tiles($user_id) {
    if (!$user_id) return '';
    
    // Get user data for modal
    $user = get_userdata($user_id);
    
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
    
    // Check if user is affiliate
    $is_affiliate = function_exists('slicewp_is_user_affiliate') && 
                   slicewp_is_user_affiliate($user_id);
    
    // Get Angel Wallet page URL
    $wallet_page_id = get_option('greenangel_angel_wallet_page_id');
    $wallet_url = $wallet_page_id ? get_permalink($wallet_page_id) : wc_get_account_endpoint_url('wallet');
    
    // Check if wallet page exists to determine layout
    $has_wallet_page = !empty($wallet_page_id);
    
    ob_start();
    ?>
    
    <!-- GORGEOUS TILES CONTAINER - PREMIUM CARD WRAPPER -->
    <div class="ga-tiles-section">
        <div class="ga-tiles-container">
            
            <!-- Navigation Header -->
            <div class="ga-tiles-header">
                <div class="ga-tiles-title">
                    <span class="ga-tiles-icon">🎯</span>
                    <span class="ga-tiles-text">Angel Hub</span>
                </div>
                <div class="ga-tiles-subtitle">
                    <span>Essential tools & features</span>
                </div>
            </div>
            
            <!-- 🚀 MAIN NAVIGATION TILES - ABSOLUTELY STUNNING -->
            <div class="ga-nav-tiles">
                
                <!-- 🛍️ Shop Now - Featured Tile -->
                <a href="/shop" class="ga-nav-tile shop ga-shimmer featured" 
                   data-tile="shop" aria-label="Browse our product catalog">
                    <div class="ga-tile-icon">🛍️</div>
                    <div class="ga-tile-content">
                        <h3 class="ga-tile-title">Shop Now</h3>
                        <p class="ga-tile-subtitle">Explore our products</p>
                    </div>
                </a>
                
                <!-- ✨ Halo Hub - Points & Rewards -->
                <a href="<?php echo wc_get_account_endpoint_url('loyalty_reward'); ?>" 
                   class="ga-nav-tile halo ga-shimmer" 
                   data-tile="halo" aria-label="Spend your Halo Points">
                    <div class="ga-tile-icon">✨</div>
                    <div class="ga-tile-content">
                        <h3 class="ga-tile-title">Halo Hub</h3>
                        <p class="ga-tile-subtitle"><?php echo ga_format_number($halo_points); ?> points available</p>
                    </div>
                </a>
                
                <!-- 📦 My Orders -->
                <a href="<?php echo wc_get_account_endpoint_url('orders'); ?>" 
                   class="ga-nav-tile orders ga-shimmer" 
                   data-tile="orders" aria-label="View and track your orders">
                    <div class="ga-tile-icon">📦</div>
                    <div class="ga-tile-content">
                        <h3 class="ga-tile-title">My Orders</h3>
                        <p class="ga-tile-subtitle"><?php echo $order_count; ?> order<?php echo $order_count !== 1 ? 's' : ''; ?> placed</p>
                    </div>
                </a>
                
                <!-- 💰 Angel Wallet (if available) -->
                <?php if ($has_wallet_page): ?>
                <a href="<?php echo esc_url($wallet_url); ?>" 
                   class="ga-nav-tile wallet ga-shimmer" 
                   data-tile="wallet" aria-label="Manage your Angel Wallet">
                    <div class="ga-tile-icon">💰</div>
                    <div class="ga-tile-content">
                        <h3 class="ga-tile-title">Angel Wallet</h3>
                        <p class="ga-tile-subtitle">£<?php echo number_format($wallet_balance, 2); ?> balance</p>
                    </div>
                </a>
                <?php else: ?>
                <!-- 👤 Profile (if no wallet) -->
                <a href="<?php echo wc_get_account_endpoint_url('edit-account'); ?>" 
                   class="ga-nav-tile profile ga-shimmer" 
                   data-tile="profile" aria-label="Update your profile">
                    <div class="ga-tile-icon">👤</div>
                    <div class="ga-tile-content">
                        <h3 class="ga-tile-title">My Profile</h3>
                        <p class="ga-tile-subtitle">Personal details</p>
                    </div>
                </a>
                <?php endif; ?>
                
                <!-- 🏠 Address Book -->
                <a href="<?php echo wc_get_account_endpoint_url('edit-address'); ?>" 
                   class="ga-nav-tile ga-shimmer" 
                   data-tile="addresses" aria-label="Manage delivery addresses">
                    <div class="ga-tile-icon">🏠</div>
                    <div class="ga-tile-content">
                        <h3 class="ga-tile-title">Address Book</h3>
                        <p class="ga-tile-subtitle">Delivery details</p>
                    </div>
                </a>
                
                <!-- ⚙️ Settings (if wallet exists) -->
                <?php if ($has_wallet_page): ?>
                <a href="<?php echo wc_get_account_endpoint_url('edit-account'); ?>" 
                   class="ga-nav-tile profile ga-shimmer" 
                   data-tile="settings" aria-label="Account settings">
                    <div class="ga-tile-icon">⚙️</div>
                    <div class="ga-tile-content">
                        <h3 class="ga-tile-title">Settings</h3>
                        <p class="ga-tile-subtitle">Account details</p>
                    </div>
                </a>
                <?php endif; ?>
                
                <!-- 💎 Affiliate Hub -->
                <a href="<?php echo $is_affiliate ? '/my-account/affiliate/' : '#locked'; ?>" 
                   class="ga-nav-tile affiliate ga-shimmer <?php echo !$is_affiliate ? 'ga-locked' : ''; ?>" 
                   data-tile="affiliate" 
                   aria-label="<?php echo $is_affiliate ? 'Access your affiliate dashboard' : 'Affiliate access locked'; ?>">
                    <div class="ga-tile-icon"><?php echo $is_affiliate ? '💎' : '🔒'; ?></div>
                    <div class="ga-tile-content">
                        <h3 class="ga-tile-title">Affiliate Hub</h3>
                        <p class="ga-tile-subtitle"><?php echo $is_affiliate ? 'Your dashboard' : 'Access locked'; ?></p>
                    </div>
                </a>
                
                <!-- ⛔ Logout Button - SAFE VERSION -->
                <div class="ga-nav-tile ga-shimmer" data-tile="logout" aria-label="Sign out of your account">
                    <div class="ga-tile-icon">⛔</div>
                    <div class="ga-tile-content">
                        <h3 class="ga-tile-title">LOG OUT</h3>
                        <p class="ga-tile-subtitle">Logout safely</p>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    <!-- 🚨 GORGEOUS LOGOUT CONFIRMATION MODAL -->
    <div id="logoutModal" class="ga-logout-modal">
        <div class="ga-logout-modal-content">
            <div class="ga-logout-modal-icon">⛔</div>
            <h3 class="ga-logout-modal-title">Ready to Leave?</h3>
            <p class="ga-logout-modal-message">
                Are you ready to log out, <span class="ga-logout-modal-username"><?php echo esc_html($user->display_name ?: $user->user_login); ?></span>?
            </p>
            <div class="ga-logout-modal-buttons">
                <button class="ga-modal-button cancel" onclick="hideLogoutModal()">
                    Stay Logged In
                </button>
                <a href="<?php echo wp_logout_url(home_url()); ?>" class="ga-modal-button confirm">
                    Yes, Log Out
                </a>
            </div>
        </div>
    </div>
    
    <script>
    // 🚨 LOGOUT MODAL FUNCTIONS - MOBILE OPTIMIZED
    function showLogoutModal(event) {
        event.preventDefault();
        event.stopPropagation(); // Prevent bubbling on mobile
        
        const modal = document.getElementById('logoutModal');
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Mobile-specific: ensure modal is visible
            modal.style.display = 'flex';
            
            // Add touch prevention for mobile
            document.addEventListener('touchmove', preventScroll, { passive: false });
        }
        return false;
    }
    
    function hideLogoutModal() {
        const modal = document.getElementById('logoutModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
            
            // Remove touch prevention
            document.removeEventListener('touchmove', preventScroll);
            
            // Hide after animation
            setTimeout(() => {
                modal.style.display = '';
            }, 300);
        }
    }
    
    function preventScroll(e) {
        e.preventDefault();
    }
    
    // Close modal on outside click (works for touch too)
    document.getElementById('logoutModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideLogoutModal();
        }
    });
    
    // Close modal on escape key (desktop)
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideLogoutModal();
        }
    });
    
    // 🔧 ENHANCED LOGOUT SAFETY - Only pill is clickable
    document.addEventListener('DOMContentLoaded', function() {
        const logoutTile = document.querySelector('[data-tile="logout"]');
        const logoutPill = document.querySelector('[data-tile="logout"] .ga-tile-subtitle');
        
        if (logoutTile && logoutPill) {
            // Remove any existing onclick from the main tile
            logoutTile.removeAttribute('onclick');
            
            // Add click handler only to the pill
            logoutPill.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                showLogoutModal(e);
            });
            
            // Also handle touch events for mobile
            logoutPill.addEventListener('touchend', function(e) {
                e.preventDefault();
                e.stopPropagation();
                showLogoutModal(e);
            });
            
            // Visual feedback for mobile users
            logoutPill.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.95)';
            });
            
            logoutPill.addEventListener('touchcancel', function() {
                this.style.transform = '';
            });
        }
    });
    </script>
    
    <script>
    // ✨ MAGICAL INTERACTIVE ENHANCEMENTS
    document.addEventListener('DOMContentLoaded', function() {
        const tiles = document.querySelectorAll('.ga-nav-tile, .ga-stat-tile.clickable');
        
        // Enhanced touch feedback for mobile
        tiles.forEach(tile => {
            // Skip logout tile since it has special handling
            if (tile.dataset.tile === 'logout') return;
            
            // Touch start feedback with haptic-like visual response
            tile.addEventListener('touchstart', function(e) {
                this.style.transform = 'scale(0.95)';
                this.style.transition = 'transform 0.1s ease';
                
                // Add ripple effect
                createRippleEffect(this, e.touches[0]);
            });
            
            // Touch end reset
            tile.addEventListener('touchend', function() {
                setTimeout(() => {
                    this.style.transform = '';
                    this.style.transition = '';
                }, 100);
            });
            
            // Mouse events for desktop
            tile.addEventListener('mousedown', function(e) {
                createRippleEffect(this, e);
            });
            
            // Mouse leave reset
            tile.addEventListener('mouseleave', function() {
                this.style.transform = '';
                this.style.transition = '';
            });
        });
        
        // Enhanced locked tile interaction
        const lockedTiles = document.querySelectorAll('.ga-nav-tile.ga-locked');
        lockedTiles.forEach(tile => {
            tile.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Shake animation for locked feedback
                this.style.animation = 'lockedShake 0.6s ease-in-out';
                
                // Show elegant tooltip
                showLockedTooltip(this);
                
                // Reset animation
                setTimeout(() => {
                    this.style.animation = '';
                }, 600);
            });
        });
        
        // Add tile-specific hover enhancements
        const tileMappings = {
            'shop': { color: '#4caf50', emoji: '🛍️' },
            'halo': { color: '#aed604', emoji: '✨' },
            'orders': { color: '#02a8d1', emoji: '📦' },
            'wallet': { color: '#ff9800', emoji: '💰' },
            'affiliate': { color: '#cf11a0', emoji: '💎' },
            'profile': { color: '#9c27b0', emoji: '👤' },
            'settings': { color: '#9c27b0', emoji: '⚙️' },
            'addresses': { color: '#607d8b', emoji: '🏠' },
            'logout': { color: '#f44336', emoji: '⛔' }
        };
        
        Object.keys(tileMappings).forEach(tileType => {
            const tile = document.querySelector(`[data-tile="${tileType}"]`);
            if (tile && !tile.classList.contains('ga-locked')) {
                const { color, emoji } = tileMappings[tileType];
                
                tile.addEventListener('mouseenter', function() {
                    // Add subtle color accent
                    this.style.setProperty('--hover-accent', color);
                });
            }
        });
        
        // Staggered animation on page load with improved timing
        const allTiles = document.querySelectorAll('.ga-nav-tile, .ga-stat-tile');
        
        // Check if we've already animated (prevent re-animation on scroll)
        if (!sessionStorage.getItem('tilesAnimated')) {
            allTiles.forEach((tile, index) => {
                tile.style.opacity = '0';
                tile.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    tile.style.transition = 'opacity 0.6s cubic-bezier(0.4, 0, 0.2, 1), transform 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    tile.style.opacity = '1';
                    tile.style.transform = 'translateY(0)';
                }, index * 80); // Slightly faster stagger
            });
            
            // Mark as animated
            sessionStorage.setItem('tilesAnimated', 'true');
        } else {
            // If already animated, just show them immediately
            allTiles.forEach(tile => {
                tile.style.opacity = '1';
                tile.style.transform = 'translateY(0)';
            });
        }
        
        // Remove will-change after animations complete to prevent mobile issues
        setTimeout(() => {
            tiles.forEach(tile => {
                tile.style.willChange = 'auto';
            });
        }, 2000);
    });
    
    // 🌊 Create beautiful ripple effect
    function createRippleEffect(element, event) {
        const ripple = document.createElement('div');
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;
        
        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
            background: radial-gradient(circle, rgba(174, 214, 4, 0.3) 0%, transparent 70%);
            border-radius: 50%;
            transform: scale(0);
            animation: rippleEffect 0.6s ease-out forwards;
            pointer-events: none;
            z-index: 10;
        `;
        
        element.style.position = 'relative';
        element.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    }
    
    // Show elegant locked tooltip
    function showLockedTooltip(element) {
        // Remove any existing tooltips
        const existingTooltip = element.querySelector('.ga-locked-tooltip');
        if (existingTooltip) {
            existingTooltip.remove();
        }
        
        const tooltip = document.createElement('div');
        tooltip.className = 'ga-locked-tooltip';
        tooltip.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.2rem;">🔒</span>
                <span>Become an affiliate to unlock this feature!</span>
            </div>
        `;
        tooltip.style.cssText = `
            position: absolute;
            top: -60px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #cf11a0 0%, #8e0e6f 100%);
            color: #ffffff;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            white-space: nowrap;
            z-index: 1000;
            box-shadow: 0 8px 25px rgba(207, 17, 160, 0.3);
            animation: tooltipSlideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        `;
        
        element.style.position = 'relative';
        element.appendChild(tooltip);
        
        // Add arrow
        const arrow = document.createElement('div');
        arrow.style.cssText = `
            position: absolute;
            bottom: -6px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-top: 6px solid #cf11a0;
        `;
        tooltip.appendChild(arrow);
        
        setTimeout(() => {
            tooltip.style.animation = 'tooltipSlideOut 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards';
            setTimeout(() => tooltip.remove(), 300);
        }, 3000);
    }
    
    // Add dynamic CSS animations with cache busting
    const enhancedStyles = document.createElement('style');
    enhancedStyles.setAttribute('data-version', 'v2-no-glow-' + Date.now());
    enhancedStyles.textContent = `
        /* FORCE REMOVE ALL SHADOWS - v2 */
        .ga-nav-tile,
        .ga-nav-tile * {
            box-shadow: none !important;
            -webkit-box-shadow: none !important;
            -moz-box-shadow: none !important;
            text-shadow: inherit !important;
        }
        
        .ga-nav-tile:hover,
        .ga-nav-tile:focus,
        .ga-nav-tile:active {
            box-shadow: none !important;
        }
        
        @keyframes rippleEffect {
            to { transform: scale(2); opacity: 0; }
        }
        
        @keyframes lockedShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-3px); }
            75% { transform: translateX(3px); }
        }
        
        @keyframes tooltipSlideIn {
            from { opacity: 0; transform: translateX(-50%) translateY(-10px); }
            to { opacity: 1; transform: translateX(-50%) translateY(0); }
        }
        
        @keyframes tooltipSlideOut {
            from { opacity: 1; transform: translateX(-50%) translateY(0); }
            to { opacity: 0; transform: translateX(-50%) translateY(-10px); }
        }
        
        /* Enhanced focus states for accessibility */
        .ga-nav-tile:focus {
            outline: 3px solid #aed604;
            outline-offset: 2px;
            box-shadow: none !important;
        }
        
        /* High contrast mode enhancements */
        @media (prefers-contrast: high) {
            .ga-nav-tile {
                border-width: 2px !important;
                background: #000000 !important;
            }
            
            .ga-tile-title {
                color: #ffffff !important;
                text-shadow: none !important;
            }
        }
        
        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            .ga-nav-tile {
                animation: none !important;
                transition: opacity 0.3s ease, transform 0.3s ease !important;
            }
            
            .ga-tile-icon {
                animation: none !important;
            }
        }
        
        /* Performance optimizations */
        .ga-nav-tile {
            contain: layout style paint;
        }
    `;
    document.head.appendChild(enhancedStyles);
    </script>
    
    <?php
    return ob_get_clean();
}

/**
 * HELPER: Get User Review Count
 */
function ga_get_user_review_count($user_id) {
    $reviews = get_comments([
        'user_id' => $user_id,
        'type' => 'review',
        'status' => 'approve',
        'count' => true
    ]);
    
    return $reviews ?: 0;
}

/**
 * HELPER: Format Large Numbers (from header.php)
 */
if (!function_exists('ga_format_number')) {
    function ga_format_number($number) {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return round($number / 1000, 1) . 'K';
        }
        return number_format($number);
    }
}

/**
 * HELPER: Get Navigation Tile Data (for future customization)
 */
function ga_get_tile_data() {
    return [
        'shop' => [
            'icon' => '🛍️',
            'title' => 'Shop Now',
            'subtitle' => 'Explore our products',
            'url' => '/shop',
            'class' => 'shop featured',
            'priority' => 1
        ],
        'halo' => [
            'icon' => '✨',
            'title' => 'Halo Hub',
            'subtitle' => 'Spend your points',
            'url' => wc_get_account_endpoint_url('loyalty_reward'),
            'class' => 'halo',
            'priority' => 2
        ],
        'orders' => [
            'icon' => '📦',
            'title' => 'My Orders',
            'subtitle' => 'Track & manage',
            'url' => wc_get_account_endpoint_url('orders'),
            'class' => 'orders',
            'priority' => 3
        ],
        'wallet' => [
            'icon' => '💰',
            'title' => 'Angel Wallet',
            'subtitle' => 'View balance',
            'url' => '#',
            'class' => 'wallet',
            'priority' => 4
        ],
        'profile' => [
            'icon' => '👤',
            'title' => 'My Profile',
            'subtitle' => 'Personal details',
            'url' => wc_get_account_endpoint_url('edit-account'),
            'class' => 'profile',
            'priority' => 5
        ],
        'settings' => [
            'icon' => '⚙️',
            'title' => 'Settings',
            'subtitle' => 'Account details',
            'url' => wc_get_account_endpoint_url('edit-account'),
            'class' => 'profile',
            'priority' => 5
        ],
        'addresses' => [
            'icon' => '🏠',
            'title' => 'Address Book',
            'subtitle' => 'Delivery details',
            'url' => wc_get_account_endpoint_url('edit-address'),
            'class' => 'addresses',
            'priority' => 6
        ],
        'affiliate' => [
            'icon' => '💎',
            'title' => 'Affiliate Hub',
            'subtitle' => 'Your dashboard',
            'url' => '/my-account/affiliate/',
            'class' => 'affiliate',
            'priority' => 7
        ],
        'logout' => [
            'icon' => '⛔',
            'title' => 'LOG OUT',
            'subtitle' => 'Logout safely',
            'url' => wp_logout_url(home_url()),
            'class' => 'logout',
            'priority' => 8
        ]
    ];
}

/**
 * 🎨 HELPER: Get Tile Color for Dynamic Styling
 */
function ga_get_tile_color($tile_type) {
    $colors = [
        'shop' => '#4caf50',
        'halo' => '#aed604',
        'orders' => '#02a8d1',
        'wallet' => '#ff9800',
        'affiliate' => '#cf11a0',
        'profile' => '#9c27b0',
        'settings' => '#9c27b0',
        'addresses' => '#607d8b',
        'logout' => '#f44336'
    ];
    
    return $colors[$tile_type] ?? '#aed604';
}
?>