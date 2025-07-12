<?php
/**
 * 🌿 GREEN ANGEL HUB v2.0 - ELEGANT ACTIVITY TABS SECTION
 * Mobile-optimized activity with smart pagination and clean design
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function ga_render_activity_section($user_id) {
    if (!$user_id) return '';
    
    // Get all data we need
    $orders = wc_get_orders(['customer' => $user_id, 'limit' => 50]);
    $halo_activities = greenangel_get_recent_activities($user_id, 50);
    $wallet_activities = ga_get_wallet_activities($user_id, 50);
    
    // Count items for tabs
    $order_count = count($orders);
    $halo_count = count($halo_activities);
    $wallet_count = count($wallet_activities);
    
    ob_start();
    ?>
    
    <div class="ga-activity-section">
        <div class="ga-activity-container">
            
            <!-- Activity Header -->
            <div class="ga-activity-header">
                <div class="ga-activity-title">
                    <span class="ga-activity-icon">📱</span>
                    <span class="ga-activity-text">Recent Activity</span>
                </div>
                <div class="ga-activity-subtitle">
                    Track your journey
                </div>
            </div>
            
            <!-- Tab Navigation -->
            <div class="ga-tab-nav">
                <button class="ga-tab-button active" onclick="switchTab('orders')" id="tab-orders">
                    <span class="ga-tab-emoji">📦</span>
                    <div class="ga-tab-content">
                        <span class="ga-tab-label">Orders</span>
                        <?php if ($order_count > 0): ?>
                        <span class="ga-tab-count"><?php echo $order_count; ?></span>
                        <?php endif; ?>
                    </div>
                </button>
                
                <button class="ga-tab-button" onclick="switchTab('halo')" id="tab-halo">
                    <span class="ga-tab-emoji">✨</span>
                    <div class="ga-tab-content">
                        <span class="ga-tab-label">Halo Points</span>
                        <?php if ($halo_count > 0): ?>
                        <span class="ga-tab-count"><?php echo $halo_count; ?></span>
                        <?php endif; ?>
                    </div>
                </button>
                
                <button class="ga-tab-button" onclick="switchTab('wallet')" id="tab-wallet">
                    <span class="ga-tab-emoji">💰</span>
                    <div class="ga-tab-content">
                        <span class="ga-tab-label">Wallet</span>
                        <?php if ($wallet_count > 0): ?>
                        <span class="ga-tab-count"><?php echo $wallet_count; ?></span>
                        <?php endif; ?>
                    </div>
                </button>
            </div>
            
            <!-- Orders Tab Content -->
            <div class="ga-tab-content active" id="content-orders">
                <?php if (!empty($orders)): ?>
                <div class="ga-activity-grid" id="ordersGrid">
                    <?php 
                    $order_counter = 0;
                    foreach ($orders as $order): 
                        $order_counter++;
                        $hidden_class = $order_counter > 4 ? 'ga-hidden' : ''; // Show 4 initially
                        
                        $status_class = 'status-' . $order->get_status();
                        $order_total = wc_price($order->get_total());
                        $order_date = $order->get_date_created()->format('M j, Y');
                        $item_count = $order->get_item_count();
                        
                        // Get status display
                        $status_display = ucfirst($order->get_status());
                        $status_emoji = ga_get_order_status_emoji($order->get_status());
                    ?>
                    <div class="ga-activity-card order-card <?php echo $hidden_class; ?>">
                        <div class="ga-card-header">
                            <div class="ga-card-primary">
                                <div class="ga-order-number">#<?php echo $order->get_order_number(); ?></div>
                                <div class="ga-order-total"><?php echo $order_total; ?></div>
                            </div>
                            <div class="ga-card-date"><?php echo $order_date; ?></div>
                        </div>
                        
                        <div class="ga-card-content">
                            <div class="ga-order-details">
                                <span class="ga-order-items"><?php echo $item_count; ?> item<?php echo $item_count !== 1 ? 's' : ''; ?></span>
                            </div>
                            
                            <a href="<?php echo esc_url($order->get_view_order_url()); ?>" 
                               class="ga-order-status <?php echo $status_class; ?>">
                                <span class="ga-status-emoji"><?php echo $status_emoji; ?></span>
                                <span><?php echo $status_display; ?></span>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (count($orders) > 4): ?>
                <div class="ga-load-more">
                    <button class="ga-load-more-btn" onclick="loadMoreItems('orders', 4)">
                        <span class="ga-load-more-text">Load More Orders</span>
                        <span class="ga-load-more-icon">↓</span>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="ga-empty-state">
                    <div class="ga-empty-icon">📦</div>
                    <h3 class="ga-empty-title">No orders yet</h3>
                    <p class="ga-empty-description">
                        Ready to start shopping? Browse our amazing collection of natural products!
                    </p>
                    <a href="/shop" class="ga-empty-action">
                        Start Shopping 🛍️
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Halo Points Tab Content -->
            <div class="ga-tab-content" id="content-halo">
                <?php if (!empty($halo_activities)): ?>
                <div class="ga-activity-grid" id="haloGrid">
                    <?php 
                    $activity_counter = 0;
                    foreach ($halo_activities as $activity): 
                        $activity_counter++;
                        $hidden_class = $activity_counter > 4 ? 'ga-hidden' : ''; // Show 4 initially
                        
                        // Determine activity type and styling
                        $activity_data = ga_get_activity_data($activity);
                        
                        // Format date
                        $activity_date = is_numeric($activity->created_at) ? 
                            date('M j, Y', $activity->created_at) : 
                            date('M j, Y', strtotime($activity->created_at));
                        
                        // Format points
                        $points_value = isset($activity->points) ? intval($activity->points) : 0;
                        $points_prefix = $points_value > 0 ? '+' : '';
                        $points_display = $points_value != 0 ? $points_prefix . number_format(abs($points_value)) : '—';
                        $amount_class = $points_value > 0 ? 'positive' : ($points_value < 0 ? 'negative' : 'neutral');
                    ?>
                    <div class="ga-activity-card halo-card <?php echo $activity_data['class']; ?> <?php echo $hidden_class; ?>">
                        <div class="ga-card-header">
                            <div class="ga-card-primary">
                                <div class="ga-activity-type">
                                    <span class="ga-activity-emoji"><?php echo $activity_data['icon']; ?></span>
                                    <span><?php echo $activity_data['title']; ?></span>
                                </div>
                                <div class="ga-activity-amount <?php echo $amount_class; ?>">
                                    <?php echo $points_display; ?> pts
                                </div>
                            </div>
                            <div class="ga-card-date"><?php echo $activity_date; ?></div>
                        </div>
                        
                        <?php if (!empty($activity->note)): ?>
                        <div class="ga-card-content">
                            <div class="ga-activity-description">
                                <?php echo esc_html(ga_clean_activity_note($activity->note)); ?>
                            </div>
                            
                            <?php if (!empty($activity->order_id) && $activity->order_id > 0): ?>
                            <div class="ga-activity-badge">
                                Order #<?php echo $activity->order_id; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (count($halo_activities) > 4): ?>
                <div class="ga-load-more">
                    <button class="ga-load-more-btn" onclick="loadMoreItems('halo', 4)">
                        <span class="ga-load-more-text">Load More Activities</span>
                        <span class="ga-load-more-icon">↓</span>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="ga-empty-state">
                    <div class="ga-empty-icon">✨</div>
                    <h3 class="ga-empty-title">No Halo Points activity yet</h3>
                    <p class="ga-empty-description">
                        Start earning Halo Points by shopping, referring friends, and leaving reviews!
                    </p>
                    <a href="/shop" class="ga-empty-action">
                        Start Earning Points ✨
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Wallet Tab Content -->
            <div class="ga-tab-content" id="content-wallet">
                <?php if (!empty($wallet_activities)): ?>
                <div class="ga-activity-grid" id="walletGrid">
                    <?php 
                    $wallet_counter = 0;
                    foreach ($wallet_activities as $transaction): 
                        $wallet_counter++;
                        $hidden_class = $wallet_counter > 4 ? 'ga-hidden' : ''; // Show 4 initially
                        
                        $amount = floatval($transaction->amount ?? 0);
                        $transaction_type = $transaction->type ?? 'adjustment';
                        $description = $transaction->description ?? 'Wallet transaction';
                        $date = isset($transaction->created_at) ? 
                            date('M j, Y', strtotime($transaction->created_at)) : 
                            date('M j, Y');
                        
                        $amount_class = $amount >= 0 ? 'credit' : 'debit';
                        $amount_prefix = $amount >= 0 ? '+' : '';
                        $wallet_emoji = $amount >= 0 ? '💰' : '💸';
                    ?>
                    <div class="ga-activity-card wallet-card <?php echo $hidden_class; ?>">
                        <div class="ga-card-header">
                            <div class="ga-card-primary">
                                <div class="ga-wallet-type">
                                    <span class="ga-activity-emoji"><?php echo $wallet_emoji; ?></span>
                                    <span><?php echo ucfirst($transaction_type); ?></span>
                                </div>
                                <div class="ga-wallet-amount <?php echo $amount_class; ?>">
                                    <?php echo $amount_prefix; ?>£<?php echo number_format(abs($amount), 2); ?>
                                </div>
                            </div>
                            <div class="ga-card-date"><?php echo $date; ?></div>
                        </div>
                        
                        <div class="ga-card-content">
                            <div class="ga-activity-description">
                                <?php echo esc_html($description); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (count($wallet_activities) > 4): ?>
                <div class="ga-load-more">
                    <button class="ga-load-more-btn" onclick="loadMoreItems('wallet', 4)">
                        <span class="ga-load-more-text">Load More Transactions</span>
                        <span class="ga-load-more-icon">↓</span>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="ga-empty-state">
                    <div class="ga-empty-icon">💰</div>
                    <h3 class="ga-empty-title">No wallet activity yet</h3>
                    <p class="ga-empty-description">
                        Your wallet transactions will appear here once you start using Angel Credits!
                    </p>
                </div>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
    
    <script>
    // 🎯 Enhanced Tab Switching Function
    function switchTab(tabName) {
        // Remove active from all content and buttons
        document.querySelectorAll('.ga-tab-content').forEach(content => {
            content.classList.remove('active');
        });
        
        document.querySelectorAll('.ga-tab-button').forEach(button => {
            button.classList.remove('active');
        });
        
        // Add loading state briefly for smooth transition
        const targetContent = document.getElementById('content-' + tabName);
        const targetButton = document.getElementById('tab-' + tabName);
        
        if (targetContent && targetButton) {
            // Small delay for smooth transition
            setTimeout(() => {
                targetContent.classList.add('active');
                targetButton.classList.add('active');
            }, 50);
        }
        
        // Store active tab in localStorage if available
        try {
            localStorage.setItem('ga_active_tab', tabName);
        } catch (e) {
            // Ignore localStorage errors
        }
    }
    
    // 📱 Enhanced Load More Items Function
    function loadMoreItems(tabType, itemsPerLoad = 4) {
        const gridId = tabType + 'Grid';
        const grid = document.getElementById(gridId);
        if (!grid) return;
        
        const hiddenItems = grid.querySelectorAll('.ga-hidden');
        const loadMoreBtn = event.target.closest('.ga-load-more-btn');
        const loadMoreText = loadMoreBtn.querySelector('.ga-load-more-text');
        const loadMoreIcon = loadMoreBtn.querySelector('.ga-load-more-icon');
        
        // Add loading state
        loadMoreBtn.classList.add('loading');
        loadMoreText.textContent = 'Loading...';
        loadMoreIcon.style.animation = 'spin 1s linear infinite';
        
        // Simulate loading delay for smooth UX
        setTimeout(() => {
            let count = 0;
            hiddenItems.forEach((item, index) => {
                if (index < itemsPerLoad) { // Show next batch of items
                    item.classList.remove('ga-hidden');
                    
                    // Add entrance animation
                    item.style.opacity = '0';
                    item.style.transform = 'translateY(20px)';
                    
                    setTimeout(() => {
                        item.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    }, index * 100);
                    
                    count++;
                }
            });
            
            // Check if there are more hidden items
            const remainingHidden = grid.querySelectorAll('.ga-hidden').length;
            
            loadMoreBtn.classList.remove('loading');
            loadMoreIcon.style.animation = '';
            
            if (remainingHidden === 0) {
                // No more items to load - hide button with animation
                loadMoreBtn.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                loadMoreBtn.style.opacity = '0';
                loadMoreBtn.style.transform = 'scale(0.9)';
                
                setTimeout(() => {
                    loadMoreBtn.parentElement.style.display = 'none';
                }, 300);
            } else {
                // Update button text based on tab type
                const itemType = tabType === 'orders' ? 'Orders' : 
                               tabType === 'halo' ? 'Activities' : 'Transactions';
                loadMoreText.textContent = `Load More ${itemType}`;
            }
        }, 400);
    }
    
    // 🎭 Enhanced Initialization
    document.addEventListener('DOMContentLoaded', function() {
        // Restore active tab from localStorage if available
        try {
            const savedTab = localStorage.getItem('ga_active_tab');
            if (savedTab && document.getElementById('tab-' + savedTab)) {
                switchTab(savedTab);
            }
        } catch (e) {
            // Ignore localStorage errors, default to first tab
        }
        
        // Add enhanced touch feedback for mobile
        const interactiveElements = document.querySelectorAll('.ga-tab-button, .ga-load-more-btn, .ga-empty-action');
        
        interactiveElements.forEach(element => {
            // Touch feedback
            element.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.97)';
                this.style.transition = 'transform 0.1s ease';
            });
            
            element.addEventListener('touchend', function() {
                this.style.transform = '';
                this.style.transition = '';
            });
            
            // Mouse feedback for desktop
            element.addEventListener('mousedown', function() {
                this.style.transform = 'scale(0.97)';
                this.style.transition = 'transform 0.1s ease';
            });
            
            element.addEventListener('mouseup', function() {
                this.style.transform = '';
                this.style.transition = '';
            });
            
            element.addEventListener('mouseleave', function() {
                this.style.transform = '';
                this.style.transition = '';
            });
        });
        
        // Add smooth scrolling for mobile tab navigation
        const tabNav = document.querySelector('.ga-tab-nav');
        if (tabNav) {
            tabNav.addEventListener('scroll', function() {
                // Add subtle visual feedback when scrolling tabs on mobile
                this.style.background = 'rgba(20, 20, 20, 0.98)';
                
                clearTimeout(this.scrollTimeout);
                this.scrollTimeout = setTimeout(() => {
                    this.style.background = '';
                }, 150);
            });
        }
        
        // Staggered entrance animation for visible cards
        const visibleCards = document.querySelectorAll('.ga-activity-card:not(.ga-hidden)');
        visibleCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
    
    // 🎨 Add dynamic CSS for animations
    const activityStyles = document.createElement('style');
    activityStyles.textContent = `
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .ga-activity-card {
            will-change: transform, opacity;
        }
        
        .ga-load-more-btn.loading {
            pointer-events: none;
        }
        
        .ga-tab-content {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    `;
    document.head.appendChild(activityStyles);
    </script>
    
    <?php
    return ob_get_clean();
}

/**
 * 🌿 HELPER: Get Activity Data for Styling
 */
function ga_get_activity_data($activity) {
    $action_type = strtolower($activity->activity_type ?? $activity->action_type ?? '');
    
    if (strpos($action_type, 'purchase') !== false) {
        return [
            'icon' => '🛍️',
            'title' => 'Purchase Points',
            'class' => 'type-purchase'
        ];
    } elseif (strpos($action_type, 'referral') !== false) {
        return [
            'icon' => '🤝',
            'title' => 'Referral Bonus',
            'class' => 'type-referral'
        ];
    } elseif (strpos($action_type, 'review') !== false) {
        return [
            'icon' => '⭐',
            'title' => 'Review Reward',
            'class' => 'type-review'
        ];
    } elseif (strpos($action_type, 'redeem') !== false) {
        return [
            'icon' => '🎁',
            'title' => 'Points Redeemed',
            'class' => 'type-redeem'
        ];
    } else {
        return [
            'icon' => '✨',
            'title' => 'Points Activity',
            'class' => 'type-general'
        ];
    }
}

/**
 * 🌿 HELPER: Get Order Status Emoji
 */
function ga_get_order_status_emoji($status) {
    $emojis = [
        'pending' => '⏳',
        'processing' => '📋',
        'completed' => '✅',
        'cancelled' => '❌',
        'refunded' => '↩️',
        'on-hold' => '⏸️',
        'failed' => '⚠️'
    ];
    
    return $emojis[$status] ?? '📦';
}

/**
 * 🌿 HELPER: Clean Activity Note
 */
function ga_clean_activity_note($note) {
    // Remove admin email and clean up
    $note = str_replace('(admin@greenangelshop.com)', '', $note);
    $note = strip_tags($note);
    $note = trim($note);
    
    // Truncate if too long for mobile
    if (strlen($note) > 80) {
        $note = substr($note, 0, 77) . '...';
    }
    
    return $note;
}

/**
 * 🌿 HELPER: Get Wallet Activities (Enhanced)
 */
function ga_get_wallet_activities($user_id, $limit = 50) {
    // If Angel Wallet plugin has its own function
    if (function_exists('greenangel_get_wallet_transactions')) {
        return greenangel_get_wallet_transactions($user_id, $limit);
    }
    
    // Mock data for demo purposes - remove in production
    if (defined('WP_DEBUG') && WP_DEBUG) {
        return [
            (object) [
                'amount' => 25.00,
                'type' => 'credit',
                'description' => 'Referral bonus received',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
            ],
            (object) [
                'amount' => -15.50,
                'type' => 'debit',
                'description' => 'Used for order discount',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 week'))
            ]
        ];
    }
    
    return [];
}
?>