<?php
/**
 * 🌑 GREEN ANGEL HUB v2.0 - NOTIFICATIONS SYSTEM DARK EDITION
 * Premium LED-inspired notifications with dark theme
 * Enhanced with message types, urgency levels, and sound alerts
 * NOW POSITIONED BEAUTIFULLY AFTER THE HEADER SECTION
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function ga_render_notifications_section($user_id) {
    if (!$user_id) return '';
    
    // Get all notifications for this user
    $notifications = ga_get_user_notifications($user_id);
    $unread_count = count(array_filter($notifications, function($n) { return !$n['read']; }));
    
    // Check for urgent notifications
    $has_urgent = false;
    foreach ($notifications as $notification) {
        if ($notification['type'] === 'urgent' && !$notification['read']) {
            $has_urgent = true;
            break;
        }
    }
    
    // Get last notification count from user meta
    $last_count = get_user_meta($user_id, 'ga_last_notification_count', true) ?: 0;
    $has_new = $unread_count > $last_count;
    
    // Update last count
    update_user_meta($user_id, 'ga_last_notification_count', $unread_count);
    
    // Only show if there are notifications OR if there are unread messages
    if (empty($notifications) && $unread_count === 0) {
        return '';
    }
    
    ob_start();
    ?>
    
    <!-- 🌟 GORGEOUS NOTIFICATIONS SECTION - NOW POSITIONED AFTER HEADER -->
    <div class="ga-notifications-section ga-notifications-top-position">
        <!-- 🌑 DARK NOTIFICATIONS HEADER -->
        <div class="ga-notifications-header <?php echo $has_urgent ? 'expanded' : ''; ?>" onclick="toggleNotifications()" id="notificationsHeader">
            <div class="ga-notifications-title">
                <span class="ga-notifications-icon <?php echo $unread_count > 0 ? 'has-unread' : ''; ?>" id="notificationBell">🔔</span>
                <span class="ga-notifications-text">Notifications</span>
                <?php if ($unread_count > 0): ?>
                <span class="ga-notifications-count"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="ga-notifications-toggle">
                <span class="ga-notification-count-text"><?php echo count($notifications); ?> total</span>
                <span class="ga-toggle-chevron">▼</span>
            </div>
        </div>
        
        <!-- 🖤 DARK NOTIFICATIONS FEED -->
        <div class="ga-notifications-feed <?php echo $has_urgent ? 'expanded' : ''; ?>" id="notificationsFeed">
            <div class="ga-notifications-list">
                <?php if (!empty($notifications)): ?>
                    <?php foreach ($notifications as $notification): ?>
                    <div class="ga-notification <?php echo $notification['read'] ? 'read' : 'unread'; ?> type-<?php echo $notification['type']; ?>" 
                         data-id="<?php echo $notification['id']; ?>">
                         
                        <span class="ga-notification-icon"><?php echo $notification['icon']; ?></span>
                        
                        <div class="ga-notification-content">
                            <h4 class="ga-notification-title"><?php echo esc_html($notification['title']); ?></h4>
                            <p class="ga-notification-message"><?php echo $notification['message']; ?></p>
                            
                            <div class="ga-notification-meta">
                                <div>
                                    <span class="ga-notification-time"><?php echo ga_time_ago($notification['created_at']); ?></span>
                                    <span class="ga-notification-source">BY <?php echo $notification['source'] ?? 'GREEN ANGEL'; ?></span>
                                </div>
                                
                                <?php if (!empty($notification['badge'])): ?>
                                <span class="ga-notification-badge"><?php echo esc_html($notification['badge']); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($notification['actions'])): ?>
                            <div class="ga-notification-actions">
                                <?php foreach ($notification['actions'] as $action): ?>
                                <a href="<?php echo esc_url($action['url']); ?>" 
                                   class="ga-notification-action <?php echo $action['class'] ?? ''; ?>"
                                   onclick="markNotificationRead(<?php echo $notification['id']; ?>)">
                                    <?php if (!empty($action['icon'])): ?>
                                    <span><?php echo $action['icon']; ?></span>
                                    <?php endif; ?>
                                    <?php echo esc_html($action['text']); ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <button class="ga-notification-dismiss" 
                                onclick="confirmDismiss('<?php echo $notification['id']; ?>')"
                                title="Dismiss notification">
                            ×
                        </button>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="ga-notifications-empty">
                        <div class="ga-notifications-empty-icon">💚</div>
                        <h3 class="ga-notifications-empty-title">You're all caught up, gorgeous!</h3>
                        <p class="ga-notifications-empty-description">
                            No notifications right now. We'll let you know when something magical happens!
                        </p>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (count($notifications) > 0): ?>
            <div class="ga-notifications-footer">
                <button class="ga-clear-all-btn" onclick="clearAllNotifications()">
                    Clear All Notifications
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- 🌟 DISMISSAL CONFIRMATION MODAL -->
    <div class="ga-modal-overlay" id="dismissModal">
        <div class="ga-modal">
            <div class="ga-modal-header">
                <span class="ga-modal-icon">🗑️</span>
                <h3 class="ga-modal-title">Remove Notification?</h3>
            </div>
            <p class="ga-modal-message">
                Are you sure you want to dismiss this notification? You won't see it again.
            </p>
            <div class="ga-modal-actions">
                <button class="ga-modal-btn ga-modal-btn-cancel" onclick="closeDismissModal()">
                    Keep It
                </button>
                <button class="ga-modal-btn ga-modal-btn-confirm" id="confirmDismissBtn">
                    Remove
                </button>
            </div>
        </div>
    </div>
    
    <?php if ($has_new): ?>
    <!-- 🔊 SOUND ALERT FOR NEW NOTIFICATIONS -->
    <audio id="notificationSound" preload="auto">
        <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZijMJFWm98OScTgwOUa" type="audio/wav">
    </audio>
    <?php endif; ?>
    
    <script>
    // 🌑 DARK THEME NOTIFICATION CONTROLS
    
    <?php if ($has_new): ?>
    // Play notification sound once for new notifications
    document.addEventListener('DOMContentLoaded', function() {
        const sound = document.getElementById('notificationSound');
        if (sound) {
            sound.volume = 0.3;
            sound.play().catch(e => console.log('Sound autoplay prevented'));
        }
    });
    <?php endif; ?>
    
    <?php if ($has_urgent): ?>
    // Auto-open for urgent notifications
    document.addEventListener('DOMContentLoaded', function() {
        const feed = document.getElementById('notificationsFeed');
        const header = document.getElementById('notificationsHeader');
        if (!feed.classList.contains('expanded')) {
            feed.classList.add('expanded');
            header.classList.add('expanded');
        }
    });
    <?php endif; ?>
    
    // 🔔 Toggle Notifications Display
    function toggleNotifications() {
        const header = document.getElementById('notificationsHeader');
        const feed = document.getElementById('notificationsFeed');
        
        if (feed.classList.contains('expanded')) {
            feed.classList.remove('expanded');
            header.classList.remove('expanded');
        } else {
            feed.classList.add('expanded');
            header.classList.add('expanded');
            
            // Mark all as read when opened
            markAllNotificationsRead();
            
            // Add subtle animation to unread notifications
            const unreadNotifications = document.querySelectorAll('.ga-notification.unread');
            unreadNotifications.forEach((notification, index) => {
                setTimeout(() => {
                    notification.style.animation = 'ledPulse 0.5s ease';
                }, index * 100);
            });
        }
    }
    
    // ✅ Mark Notification as Read
    function markNotificationRead(notificationId) {
        const notification = document.querySelector(`[data-id="${notificationId}"]`);
        if (notification) {
            notification.classList.remove('unread');
            notification.classList.add('read');
            
            // Update unread count
            updateUnreadCount();
            
            // Send AJAX request to mark as read
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'ga_mark_notification_read',
                    notification_id: notificationId,
                    nonce: '<?php echo wp_create_nonce('ga_notification_nonce'); ?>'
                })
            });
        }
    }
    
    // 👁️ Mark All Notifications as Read
    function markAllNotificationsRead() {
        const unreadNotifications = document.querySelectorAll('.ga-notification.unread');
        unreadNotifications.forEach(notification => {
            notification.classList.remove('unread');
            notification.classList.add('read');
        });
        
        updateUnreadCount();
        
        // Send AJAX request
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'ga_mark_all_notifications_read',
                user_id: <?php echo $user_id; ?>,
                nonce: '<?php echo wp_create_nonce('ga_notification_nonce'); ?>'
            })
        });
    }
    
    // ❌ Dismiss Individual Notification with Confirmation
    let notificationToRemove = null;
    
    function confirmDismiss(notificationId) {
        notificationToRemove = notificationId;
        const modal = document.getElementById('dismissModal');
        modal.classList.add('active');
    }
    
    function closeDismissModal() {
        const modal = document.getElementById('dismissModal');
        modal.classList.remove('active');
        notificationToRemove = null;
    }
    
    // Confirm dismissal
    document.getElementById('confirmDismissBtn').addEventListener('click', function() {
        if (notificationToRemove) {
            dismissNotification(notificationToRemove);
            closeDismissModal();
        }
    });
    
    // Close modal on overlay click
    document.getElementById('dismissModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDismissModal();
        }
    });
    
    function dismissNotification(notificationId) {
        const notification = document.querySelector(`[data-id="${notificationId}"]`);
        if (notification) {
            notification.classList.add('dismissing');
            
            setTimeout(() => {
                notification.remove();
                updateUnreadCount();
                
                // Check if notifications list is empty
                const remainingNotifications = document.querySelectorAll('.ga-notification');
                if (remainingNotifications.length === 0) {
                    showEmptyState();
                }
            }, 300);
            
            // Send AJAX request to dismiss
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'ga_dismiss_notification',
                    notification_id: notificationId,
                    nonce: '<?php echo wp_create_nonce('ga_notification_nonce'); ?>'
                })
            });
        }
    }
    
    // 🧹 Clear All Notifications with Cascade Effect
    function clearAllNotifications() {
        if (!confirm('Are you sure you want to clear all notifications?')) {
            return;
        }
        
        const notifications = document.querySelectorAll('.ga-notification');
        notifications.forEach((notification, index) => {
            setTimeout(() => {
                notification.classList.add('dismissing');
                setTimeout(() => notification.remove(), 300);
            }, index * 50);
        });
        
        setTimeout(() => {
            showEmptyState();
            updateUnreadCount();
        }, notifications.length * 50 + 300);
        
        // Send AJAX request
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'ga_clear_all_notifications',
                user_id: <?php echo $user_id; ?>,
                nonce: '<?php echo wp_create_nonce('ga_notification_nonce'); ?>'
            })
        });
    }
    
    // 📊 Update Unread Count
    function updateUnreadCount() {
        const unreadNotifications = document.querySelectorAll('.ga-notification.unread');
        const countElement = document.querySelector('.ga-notifications-count');
        const totalElement = document.querySelector('.ga-notification-count-text');
        const bellIcon = document.getElementById('notificationBell');
        
        if (unreadNotifications.length === 0) {
            if (countElement) {
                countElement.style.display = 'none';
            }
            if (bellIcon) {
                bellIcon.classList.remove('has-unread');
            }
        } else {
            if (countElement) {
                countElement.textContent = unreadNotifications.length;
                countElement.style.display = 'inline-block';
            }
            if (bellIcon) {
                bellIcon.classList.add('has-unread');
            }
        }
        
        // Update total count
        const totalNotifications = document.querySelectorAll('.ga-notification').length;
        if (totalElement) {
            totalElement.textContent = `${totalNotifications} total`;
        }
        
        // Hide entire section if no notifications remain
        if (totalNotifications === 0) {
            const notificationsSection = document.querySelector('.ga-notifications-section');
            if (notificationsSection) {
                notificationsSection.style.animation = 'slideOutDark 0.5s ease forwards';
                setTimeout(() => {
                    notificationsSection.style.display = 'none';
                }, 500);
            }
        }
    }
    
    // 🚫 Show Empty State - Dark Theme
    function showEmptyState() {
        const notificationsList = document.querySelector('.ga-notifications-list');
        notificationsList.innerHTML = `
            <div class="ga-notifications-empty">
                <div class="ga-notifications-empty-icon">💚</div>
                <h3 class="ga-notifications-empty-title">You're all caught up, gorgeous!</h3>
                <p class="ga-notifications-empty-description">
                    No notifications right now. We'll let you know when something magical happens!
                </p>
            </div>
        `;
        
        // Hide footer
        const footer = document.querySelector('.ga-notifications-footer');
        if (footer) {
            footer.style.display = 'none';
        }
        
        // Update header to show 0 notifications
        const totalElement = document.querySelector('.ga-notification-count-text');
        if (totalElement) {
            totalElement.textContent = '0 total';
        }
        
        // Remove any count badges
        const countElement = document.querySelector('.ga-notifications-count');
        if (countElement) {
            countElement.style.display = 'none';
        }
    }
    
    // 🎭 Premium LED Effects on Load
    document.addEventListener('DOMContentLoaded', function() {
        // Add LED pulse to new notifications
        const newNotifications = document.querySelectorAll('.ga-notification.unread');
        newNotifications.forEach((notification, index) => {
            notification.style.animationDelay = `${index * 0.1}s`;
        });
        
        // Auto-dismiss promotional notifications after 5 seconds if opened
        const promoNotifications = document.querySelectorAll('.ga-notification.type-promo');
        promoNotifications.forEach(notification => {
            notification.addEventListener('mouseenter', function() {
                if (!this.classList.contains('read')) {
                    setTimeout(() => {
                        if (this.classList.contains('read')) {
                            dismissNotification(this.dataset.id);
                        }
                    }, 5000);
                }
            });
        });
        
        // Add hover sound effect (optional - can be removed if not wanted)
        const notificationItems = document.querySelectorAll('.ga-notification');
        notificationItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                // Subtle visual feedback on hover
                this.style.transition = 'all 0.2s ease';
            });
        });
        
        // 🌟 Add beautiful slide-in animation on page load
        const notificationsSection = document.querySelector('.ga-notifications-section');
        if (notificationsSection) {
            notificationsSection.style.opacity = '0';
            notificationsSection.style.transform = 'translateY(-20px)';
            
            setTimeout(() => {
                notificationsSection.style.transition = 'all 0.6s cubic-bezier(0.16, 1, 0.3, 1)';
                notificationsSection.style.opacity = '1';
                notificationsSection.style.transform = 'translateY(0)';
            }, 300);
        }
    });
    </script>
    
    <?php
    return ob_get_clean();
}

/**
 * 🌑 Get User Notifications - Enhanced with Message Types
 */
function ga_get_user_notifications($user_id) {
    $notifications = [];
    
    // Get recent activities for smart notifications
    $loyalty_points = greenangel_get_wp_loyalty_points_safe($user_id);
    $recent_activities = greenangel_get_recent_activities($user_id, 5);
    $recent_orders = wc_get_orders(['customer' => $user_id, 'limit' => 3]);
    
    // GET ALL ADMIN BROADCAST MESSAGES
    $admin_messages = get_option('greenangel_notification_messages', []);
    
    // Add each active admin message
    if (!empty($admin_messages)) {
        foreach ($admin_messages as $msg_id => $message) {
            if (isset($message['active']) && $message['active'] === 'yes') {
                $type_emojis = [
                    'system' => '📢',
                    'urgent' => '🚨',
                    'love' => '💚',
                    'achievement' => '🏆',
                    'promo' => '🌟',
                    'reminder' => '⏰'
                ];
                
                $notifications[] = [
                    'id' => 'admin_' . $msg_id,
                    'type' => $message['type'] ?? 'system',
                    'icon' => $type_emojis[$message['type'] ?? 'system'] ?? '💌',
                    'title' => strtoupper($message['type'] ?? 'SYSTEM') . ' MESSAGE',
                    'message' => $message['text'],
                    'badge' => $message['badge'] ?? ucfirst($message['type'] ?? 'system'),
                    'created_at' => $message['created_at'] ?? time(),
                    'read' => false,
                    'source' => 'GREEN ANGEL',
                    'actions' => []
                ];
            }
        }
    }
    
    // LEGACY: Check old single notification format
    $legacy_text = get_option('greenangel_notification_text', '');
    $legacy_active = get_option('greenangel_notification_active', 'no');
    
    if ($legacy_active === 'yes' && !empty($legacy_text)) {
        // Check if this legacy notification isn't already in our new format
        $legacy_exists = false;
        foreach ($notifications as $notif) {
            if ($notif['message'] === strip_tags($legacy_text)) {
                $legacy_exists = true;
                break;
            }
        }
        
        if (!$legacy_exists) {
            $notifications[] = [
                'id' => 'legacy_notification',
                'type' => 'system',
                'icon' => '📢',
                'title' => 'SYSTEM MESSAGE',
                'message' => strip_tags($legacy_text),
                'badge' => 'Important',
                'created_at' => time() - 1800,
                'read' => false,
                'source' => 'GREEN ANGEL',
                'actions' => []
            ];
        }
    }
    
    // Points milestone notifications
    if ($loyalty_points['available'] >= 1000 && $loyalty_points['available'] < 1100) {
        $notifications[] = [
            'id' => 'points_milestone_1000',
            'type' => 'achievement',
            'icon' => '🎉',
            'title' => 'MILESTONE ACHIEVED!',
            'message' => 'You\'ve earned over 1,000 Halo Points! Time to treat yourself.',
            'badge' => 'Achievement',
            'created_at' => time() - 3600,
            'read' => false,
            'source' => 'HALO SYSTEM',
            'actions' => [
                ['text' => 'Visit Halo Hub', 'url' => wc_get_account_endpoint_url('loyalty_reward'), 'icon' => '✨']
            ]
        ];
    }
    
    // Recent order notifications
    foreach ($recent_orders as $order) {
        if ($order->get_status() === 'completed' && 
            $order->get_date_completed() && 
            $order->get_date_completed()->getTimestamp() > (time() - 86400)) {
            
            $notifications[] = [
                'id' => 'order_completed_' . $order->get_id(),
                'type' => 'order',
                'icon' => '📦',
                'title' => 'ORDER DELIVERED',
                'message' => 'Order #' . $order->get_order_number() . ' has been completed successfully.',
                'badge' => 'Complete',
                'created_at' => $order->get_date_completed()->getTimestamp(),
                'read' => false,
                'source' => 'ORDER SYSTEM',
                'actions' => [
                    ['text' => 'Review', 'url' => '/my-account/orders/', 'icon' => '⭐'],
                    ['text' => 'View', 'url' => $order->get_view_order_url(), 'class' => 'secondary']
                ]
            ];
        }
    }
    
    // Wallet reminders (if wallet balance is low)
    if (function_exists('greenangel_get_wallet_balance')) {
        $wallet_balance = greenangel_get_wallet_balance($user_id);
        if ($wallet_balance > 0 && $wallet_balance < 10) {
            $notifications[] = [
                'id' => 'wallet_low_balance',
                'type' => 'wallet',
                'icon' => '💰',
                'title' => 'LOW WALLET BALANCE',
                'message' => 'Only £' . number_format($wallet_balance, 2) . ' remaining in your Angel Wallet.',
                'badge' => 'Alert',
                'created_at' => time() - 7200,
                'read' => false,
                'source' => 'WALLET SYSTEM',
                'actions' => [
                    ['text' => 'Top Up', 'url' => '/angel-wallet/', 'icon' => '💸']
                ]
            ];
        }
    }
    
    // Special promotions
    if (date('w') == 5) { // Friday
        $notifications[] = [
            'id' => 'friday_special',
            'type' => 'promo',
            'icon' => '🌟',
            'title' => 'FLASH FRIDAY',
            'message' => 'Double points on all orders today only!',
            'badge' => 'Limited',
            'created_at' => time() - 900,
            'read' => false,
            'source' => 'PROMO ENGINE',
            'actions' => [
                ['text' => 'Shop Now', 'url' => '/shop/', 'icon' => '🛍️']
            ]
        ];
    }
    
    // Sort by newest first
    usort($notifications, function($a, $b) {
        return $b['created_at'] - $a['created_at'];
    });
    
    return array_slice($notifications, 0, 10); // Limit to 10 notifications
}

/**
 * 🌑 Time Ago Helper - Enhanced Format
 */
function ga_time_ago($timestamp) {
    $time_diff = time() - $timestamp;
    
    if ($time_diff < 60) return 'NOW';
    if ($time_diff < 3600) return floor($time_diff / 60) . 'M AGO';
    if ($time_diff < 86400) return floor($time_diff / 3600) . 'H AGO';
    if ($time_diff < 604800) return floor($time_diff / 86400) . 'D AGO';
    
    return date('M J', $timestamp);
}

// 🔧 AJAX Handlers for notification actions
add_action('wp_ajax_ga_mark_notification_read', 'ga_handle_mark_notification_read');
add_action('wp_ajax_ga_mark_all_notifications_read', 'ga_handle_mark_all_notifications_read');
add_action('wp_ajax_ga_dismiss_notification', 'ga_handle_dismiss_notification');
add_action('wp_ajax_ga_clear_all_notifications', 'ga_handle_clear_all_notifications');

function ga_handle_mark_notification_read() {
    check_ajax_referer('ga_notification_nonce', 'nonce');
    
    $notification_id = sanitize_text_field($_POST['notification_id']);
    // Store read status in user meta or custom table
    // For now, just return success
    wp_die('success');
}

function ga_handle_mark_all_notifications_read() {
    check_ajax_referer('ga_notification_nonce', 'nonce');
    
    $user_id = intval($_POST['user_id']);
    // Mark all notifications as read for this user
    // Implementation depends on your notification storage system
    wp_die('success');
}

function ga_handle_dismiss_notification() {
    check_ajax_referer('ga_notification_nonce', 'nonce');
    
    $notification_id = sanitize_text_field($_POST['notification_id']);
    // Remove notification from user's list
    wp_die('success');
}

function ga_handle_clear_all_notifications() {
    check_ajax_referer('ga_notification_nonce', 'nonce');
    
    $user_id = intval($_POST['user_id']);
    // Clear all notifications for this user
    wp_die('success');
}
?>