/**
 * 🎮 Customer Module JavaScript
 * Interactive functionality for the customer management interface
 */

jQuery(document).ready(function($) {
    'use strict';

    // ═══════════════════════════════════════════════════════════════════════
    // 🎯 CUSTOMER CARD INTERACTIONS
    // ═══════════════════════════════════════════════════════════════════════

    // Navigate to customer profile when card is clicked
    $('.customer-card').on('click', function(e) {
        // Don't navigate if clicking on action buttons
        if ($(e.target).closest('.action-btn').length) {
            return;
        }
        
        const customerId = $(this).data('customer-id');
        if (customerId) {
            viewCustomerProfile(customerId);
        }
    });

    // Add hover effects and animations
    $('.customer-card').on('mouseenter', function() {
        $(this).addClass('card-hovered');
    }).on('mouseleave', function() {
        $(this).removeClass('card-hovered');
    });

    // ═══════════════════════════════════════════════════════════════════════
    // 💰 WALLET MANAGEMENT FUNCTIONS
    // ═══════════════════════════════════════════════════════════════════════

    // Quick amount button functionality for profile page
    $('.amount-btn').on('click', function() {
        const amount = $(this).data('amount');
        $('#add-amount').val(amount);
        $('.amount-btn').removeClass('selected');
        $(this).addClass('selected');
    });

    // Add funds validation
    $('#add-amount, #remove-amount').on('input', function() {
        const value = parseFloat($(this).val());
        const button = $(this).closest('.action-group').find('.action-button');
        
        if (isNaN(value) || value <= 0) {
            button.prop('disabled', true).addClass('disabled');
        } else {
            button.prop('disabled', false).removeClass('disabled');
        }
    });

    // Remove funds validation (requires reason)
    $('#remove-reason').on('input', function() {
        const reason = $(this).val().trim();
        const amount = parseFloat($('#remove-amount').val());
        const button = $('.action-button.remove');
        
        if (!reason || isNaN(amount) || amount <= 0) {
            button.prop('disabled', true).addClass('disabled');
        } else {
            button.prop('disabled', false).removeClass('disabled');
        }
    });

    // ═══════════════════════════════════════════════════════════════════════
    // ✨ ANGEL IDENTITY MANAGEMENT FUNCTIONS - NEW!
    // ═══════════════════════════════════════════════════════════════════════

    // Reset reason input validation
    $('#reset-reason').on('input', function() {
        const reason = $(this).val().trim();
        const button = $('.reset-identity-btn');
        
        // Update button text based on reason
        if (reason.length > 0) {
            button.find('.btn-text').text('Reset Angel Identity');
        } else {
            button.find('.btn-text').text('Reset Angel Identity');
        }
        
        // Add visual feedback for reason input
        if (reason.length > 10) {
            $(this).addClass('reason-valid');
        } else {
            $(this).removeClass('reason-valid');
        }
    });

    // Add visual enhancements to identity management section
    function enhanceIdentityManagement() {
        // Add glow effect to current emoji
        $('.current-emoji').each(function() {
            const emoji = $(this).text();
            if (emoji && emoji !== '👤') {
                $(this).addClass('has-identity');
            }
        });

        // Animate lock overlay if present
        if ($('.lock-overlay').length) {
            $('.lock-overlay').addClass('animated-lock');
        }

        // Add hover effects to meta items
        $('.meta-item').on('mouseenter', function() {
            $(this).addClass('meta-hover');
        }).on('mouseleave', function() {
            $(this).removeClass('meta-hover');
        });

        // Warning box interaction
        $('.reset-warning').on('mouseenter', function() {
            $(this).addClass('warning-focused');
        }).on('mouseleave', function() {
            $(this).removeClass('warning-focused');
        });
    }

    // Initialize identity management enhancements
    enhanceIdentityManagement();

    // ═══════════════════════════════════════════════════════════════════════
    // 🔍 SEARCH AND FILTER FUNCTIONALITY
    // ═══════════════════════════════════════════════════════════════════════

    let searchTimeout;
    
    // Real-time search with debouncing
    $('#customer-search').on('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = $(this).val();
        
        // Visual feedback while typing
        if (searchTerm.length > 0) {
            $(this).addClass('searching');
        } else {
            $(this).removeClass('searching');
        }
        
        // Debounced search
        searchTimeout = setTimeout(function() {
            filterCustomers();
        }, 500);
    });

    // Filter change handler
    $('#customer-filter').on('change', function() {
        filterCustomers();
    });

    // Enter key search
    $('#customer-search').on('keypress', function(e) {
        if (e.which === 13) {
            clearTimeout(searchTimeout);
            filterCustomers();
        }
    });

    // ═══════════════════════════════════════════════════════════════════════
    // 📱 RESPONSIVE AND UI ENHANCEMENTS
    // ═══════════════════════════════════════════════════════════════════════

    // Add loading states to buttons
    function addLoadingState(button) {
        const $btn = $(button);
        $btn.addClass('loading')
            .prop('disabled', true)
            .data('original-text', $btn.text())
            .text('Loading...');
    }

    function removeLoadingState(button) {
        const $btn = $(button);
        $btn.removeClass('loading')
            .prop('disabled', false)
            .text($btn.data('original-text') || $btn.text().replace('Loading...', ''));
    }

    // Smooth scroll to top when navigating
    function smoothScrollToTop() {
        $('html, body').animate({
            scrollTop: 0
        }, 500);
    }

    // Add ripple effect to buttons
    $('.action-btn, .profile-action-btn, .action-button, .reset-identity-btn').on('click', function(e) {
        const button = $(this);
        const ripple = $('<span class="ripple"></span>');
        
        button.append(ripple);
        
        const rect = this.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.css({
            width: size + 'px',
            height: size + 'px',
            left: x + 'px',
            top: y + 'px'
        }).addClass('animate');
        
        setTimeout(() => ripple.remove(), 600);
    });

    // ═══════════════════════════════════════════════════════════════════════
    // 🎨 VISUAL ENHANCEMENTS AND ANIMATIONS
    // ═══════════════════════════════════════════════════════════════════════

    // Stagger animation for customer cards on page load
    function animateCustomerCards() {
        $('.customer-card').each(function(index) {
            $(this).css({
                'opacity': '0',
                'transform': 'translateY(20px)'
            }).delay(index * 100).animate({
                'opacity': '1',
                'transform': 'translateY(0)'
            }, 500);
        });
    }

    // Parallax effect for hero avatar glow
    if ($('.avatar-glow').length) {
        $(window).on('scroll', function() {
            const scrolled = $(window).scrollTop();
            const parallax = scrolled * 0.2;
            $('.avatar-glow').css('transform', 
                `translate(-50%, -50%) scale(${1 + parallax / 1000})`);
        });
    }

    // Dynamic tier badge glow
    $('.tier-badge, .tier-badge-large').each(function() {
        const badge = $(this);
        if (badge.hasClass('vip-angel')) {
            badge.css('box-shadow', '0 0 20px rgba(255, 107, 53, 0.4)');
        } else if (badge.hasClass('gold-angel')) {
            badge.css('box-shadow', '0 0 20px rgba(255, 215, 0, 0.4)');
        }
    });

    // Animate identity management elements
    function animateIdentityElements() {
        // Animate the current emoji with a subtle bounce
        $('.current-emoji.has-identity').each(function() {
            $(this).addClass('emoji-bounce');
        });

        // Add shimmer effect to reset button
        $('.reset-identity-btn:not(:disabled)').addClass('reset-shimmer');

        // Animate warning box entrance
        $('.reset-warning').addClass('warning-slide-in');

        // Stagger animate meta items
        $('.meta-item').each(function(index) {
            $(this).css('animation-delay', (index * 100) + 'ms').addClass('meta-slide-in');
        });
    }

    // Initialize animations with delay
    setTimeout(function() {
        animateCustomerCards();
        animateIdentityElements();
    }, 200);

    // Initialize animations with delay
    setTimeout(function() {
        animateCustomerCards();
        animateIdentityElements();
    }, 200);
});

// ═══════════════════════════════════════════════════════════════════════
// 🎭 RESET CONFIRMATION AND SUCCESS ANIMATIONS - GLOBAL SCOPE
// ═══════════════════════════════════════════════════════════════════════

/**
 * Enhanced reset confirmation with beautiful modal-style overlay
 */
function showResetConfirmation(customerId, customerName, callback) {
    const confirmationHTML = `
        <div class="reset-confirmation-overlay">
            <div class="reset-confirmation-modal">
                <div class="modal-header">
                    <h3>🔄 Reset Angel Identity</h3>
                    <button class="modal-close">&times;</button>
                </div>
                
                <div class="modal-content">
                    <div class="confirmation-avatar">
                        <span class="warning-emoji">⚠️</span>
                    </div>
                    
                    <div class="confirmation-text">
                        <h4>Are you absolutely sure?</h4>
                        <p>You're about to reset <strong>${customerName}</strong>'s Angel identity.</p>
                        
                        <div class="reset-effects">
                            <div class="effect-item">
                                <span class="effect-icon">🗑️</span>
                                <span>Clear current emoji and identity name</span>
                            </div>
                            <div class="effect-item">
                                <span class="effect-icon">🔓</span>
                                <span>Remove 30-day lock immediately</span>
                            </div>
                            <div class="effect-item">
                                <span class="effect-icon">✨</span>
                                <span>Allow instant new identity selection</span>
                            </div>
                            <div class="effect-item">
                                <span class="effect-icon">📝</span>
                                <span>Log this action in admin records</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button class="confirm-reset-btn" data-customer-id="${customerId}">
                        <span class="btn-icon">🔄</span>
                        Yes, Reset Identity
                    </button>
                    <button class="cancel-reset-btn">
                        <span class="btn-icon">❌</span>
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    `;

    // Add confirmation modal to page
    jQuery('body').append(confirmationHTML);
    
    // Animate in
    setTimeout(() => {
        jQuery('.reset-confirmation-overlay').addClass('show');
    }, 10);

    // Handle confirmation
    jQuery('.confirm-reset-btn').on('click', function() {
        hideResetConfirmation();
        if (callback) callback();
    });

    // Handle cancel/close
    jQuery('.cancel-reset-btn, .modal-close').on('click', hideResetConfirmation);
    
    // Close on overlay click
    jQuery('.reset-confirmation-overlay').on('click', function(e) {
        if (e.target === this) {
            hideResetConfirmation();
        }
    });

    // ESC key to close
    jQuery(document).on('keydown.resetModal', function(e) {
        if (e.key === 'Escape') {
            hideResetConfirmation();
        }
    });
}

/**
 * Hide reset confirmation modal
 */
function hideResetConfirmation() {
    jQuery('.reset-confirmation-overlay').removeClass('show');
    setTimeout(() => {
        jQuery('.reset-confirmation-overlay').remove();
        jQuery(document).off('keydown.resetModal');
    }, 300);
}

/**
 * Success animation for completed reset
 */
function showResetSuccess(data) {
    // Create success notification
    const successHTML = `
        <div class="reset-success-notification">
            <div class="success-content">
                <div class="success-icon">🎉</div>
                <div class="success-text">
                    <h4>Identity Reset Complete!</h4>
                    <p>${data.message}</p>
                    <div class="success-details">
                        <small>Previous identity: ${data.reset_data.previous_emoji} ${data.reset_data.previous_name}</small>
                        <small>Reset by: ${data.reset_data.reset_by} at ${data.reset_data.reset_time}</small>
                    </div>
                </div>
            </div>
        </div>
    `;

    jQuery('body').append(successHTML);
    
    // Animate success notification
    setTimeout(() => {
        jQuery('.reset-success-notification').addClass('show');
    }, 10);

    // Auto-hide success notification
    setTimeout(() => {
        jQuery('.reset-success-notification').removeClass('show');
        setTimeout(() => {
            jQuery('.reset-success-notification').remove();
        }, 500);
    }, 4000);

    // Animate the identity panel with success effect
    jQuery('.current-identity').addClass('identity-reset-success');
    
    // Remove success class after animation
    setTimeout(() => {
        jQuery('.current-identity').removeClass('identity-reset-success');
    }, 1000);
}

// ═══════════════════════════════════════════════════════════════════════
// 🌐 GLOBAL FUNCTIONS (callable from PHP)
// ═══════════════════════════════════════════════════════════════════════

/**
 * Navigate to customer profile page
 */
function viewCustomerProfile(customerId) {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('customer_id', customerId);
    window.location.href = currentUrl.toString();
}

/**
 * Quick wallet actions from customer cards
 */
function quickWalletAction(customerId, action) {
    if (action === 'add') {
        const amount = prompt('Enter amount to add to wallet (£):');
        if (amount && parseFloat(amount) > 0) {
            adjustWalletBalance(customerId, 'add', parseFloat(amount), 'Quick add from customer grid');
        }
    }
}

/**
 * Email customer (redirect to WooCommerce customer page)
 */
function emailCustomer(customerId) {
    const emailUrl = `/wp-admin/admin.php?page=wc-admin&path=/customers/${customerId}`;
    window.open(emailUrl, '_blank');
}

/**
 * View customer orders
 */
function viewCustomerOrders(customerId) {
    const ordersUrl = `/wp-admin/edit.php?post_type=shop_order&customer_id=${customerId}`;
    window.open(ordersUrl, '_blank');
}

/**
 * Edit customer profile
 */
function editCustomer(customerId) {
    const editUrl = `/wp-admin/user-edit.php?user_id=${customerId}`;
    window.open(editUrl, '_blank');
}

/**
 * 🔄 NEW: Reset Angel Identity with beautiful confirmation
 */
function resetAngelIdentity(customerId) {
    // Get customer name for confirmation
    const customerName = jQuery('.hero-name').text() || 'this customer';
    
    // Show beautiful confirmation modal
    showResetConfirmation(customerId, customerName, function() {
        performAngelIdentityReset(customerId);
    });
}

/**
 * 🔧 Perform the actual identity reset via AJAX
 */
function performAngelIdentityReset(customerId) {
    const reason = jQuery('#reset-reason').val().trim();
    const button = jQuery('.reset-identity-btn');
    
    // Add loading state with enhanced animation
    button.addClass('loading reset-processing')
          .prop('disabled', true)
          .find('.btn-icon').addClass('spinning');
    
    // Show processing message
    showNotification('Processing identity reset...', 'info');
    
    // AJAX request to reset identity
    jQuery.ajax({
        url: greenangel_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'greenangel_reset_angel_identity',
            customer_id: customerId,
            reason: reason || 'Admin reset via customer module',
            nonce: greenangel_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                // Show success notification
                showNotification(response.data.message, 'success');
                
                // Show detailed success animation
                showResetSuccess(response.data);
                
                // Wait for animations, then refresh page
                setTimeout(function() {
                    window.location.reload();
                }, 2000);
                
            } else {
                showNotification(response.data || 'Reset failed. Please try again.', 'error');
                removeResetLoadingState(button);
            }
        },
        error: function(xhr, status, error) {
            console.error('Reset Identity Error:', error);
            showNotification('Network error during reset. Please try again.', 'error');
            removeResetLoadingState(button);
        }
    });
}

/**
 * 🎨 Remove loading state from reset button
 */
function removeResetLoadingState(button) {
    button.removeClass('loading reset-processing')
          .prop('disabled', false)
          .find('.btn-icon').removeClass('spinning');
}

/**
 * Adjust wallet balance with AJAX
 */
function adjustWallet(customerId, actionType) {
    const isAdd = actionType === 'add';
    const amountInput = isAdd ? '#add-amount' : '#remove-amount';
    const reasonInput = isAdd ? '#add-reason' : '#remove-reason';
    const button = isAdd ? '.action-button.add' : '.action-button.remove';
    
    const amount = parseFloat(jQuery(amountInput).val());
    const reason = jQuery(reasonInput).val().trim();
    
    // Validation
    if (isNaN(amount) || amount <= 0) {
        showNotification('Please enter a valid amount', 'error');
        return;
    }
    
    if (!isAdd && !reason) {
        showNotification('Please provide a reason for removing funds', 'error');
        return;
    }
    
    // Add loading state
    jQuery(button).addClass('loading').prop('disabled', true);
    
    // AJAX request
    jQuery.ajax({
        url: greenangel_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'greenangel_adjust_wallet',
            customer_id: customerId,
            action_type: actionType,
            amount: amount,
            reason: reason || `Wallet ${actionType} via admin`,
            nonce: greenangel_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                // Show success message
                showNotification(response.data.message, 'success');
                
                // Wait for notification to show, then refresh page
                setTimeout(function() {
                    window.location.reload();
                }, 1500); // Give time to see the success message
                
            } else {
                showNotification(response.data || 'An error occurred', 'error');
            }
        },
        error: function() {
            showNotification('Network error. Please try again.', 'error');
        },
        complete: function() {
            // Remove loading state
            jQuery(button).removeClass('loading').prop('disabled', false);
        }
    });
}

/**
 * Filter customers based on search and filter criteria
 */
function filterCustomers() {
    const search = jQuery('#customer-search').val();
    const filter = jQuery('#customer-filter').val();
    
    // Update URL without reloading
    const url = new URL(window.location);
    if (search) {
        url.searchParams.set('search', search);
    } else {
        url.searchParams.delete('search');
    }
    url.searchParams.set('filter', filter);
    url.searchParams.delete('paged'); // Reset to page 1
    
    // Update browser history
    window.history.pushState({}, '', url.toString());
    
    // Reload the page with new parameters
    window.location.reload();
}

/**
 * Show notification messages (enhanced for reset system)
 */
function showNotification(message, type = 'info') {
    // Remove existing notifications
    jQuery('.angel-notification').remove();
    
    // Create notification element
    const notification = jQuery(`
        <div class="angel-notification ${type}">
            <div class="notification-content">
                <span class="notification-icon">${getNotificationIcon(type)}</span>
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        </div>
    `);
    
    // Add to page
    jQuery('body').append(notification);
    
    // Show with animation
    setTimeout(() => notification.addClass('show'), 100);
    
    // Auto-hide after 5 seconds (except for success messages which stay longer)
    const hideDelay = type === 'success' ? 7000 : 5000;
    setTimeout(() => hideNotification(notification), hideDelay);
    
    // Click to close
    notification.find('.notification-close').on('click', () => hideNotification(notification));
}

/**
 * Hide notification with animation
 */
function hideNotification(notification) {
    notification.removeClass('show');
    setTimeout(() => notification.remove(), 300);
}

/**
 * Get icon for notification type
 */
function getNotificationIcon(type) {
    const icons = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };
    return icons[type] || icons.info;
}

// ═══════════════════════════════════════════════════════════════════════
// 🎨 CSS ANIMATIONS AND STYLES FOR NOTIFICATIONS AND MODALS
// ═══════════════════════════════════════════════════════════════════════

// Add enhanced styles dynamically
jQuery(document).ready(function() {
    const enhancedStyles = `
        <style>
        /* Enhanced notification styles */
        .angel-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
            border: 2px solid #333;
            border-radius: 12px;
            padding: 16px 20px;
            min-width: 300px;
            max-width: 400px;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }
        
        .angel-notification.show {
            transform: translateX(0);
            opacity: 1;
        }
        
        .angel-notification.success {
            border-color: #4ade80;
            background: linear-gradient(135deg, #1a1a1a 0%, #1a2e1a 100%);
        }
        
        .angel-notification.error {
            border-color: #ef4444;
            background: linear-gradient(135deg, #1a1a1a 0%, #2e1a1a 100%);
        }
        
        .angel-notification.warning {
            border-color: #fbbf24;
            background: linear-gradient(135deg, #1a1a1a 0%, #2e261a 100%);
        }
        
        .angel-notification.info {
            border-color: #60a5fa;
            background: linear-gradient(135deg, #1a1a1a 0%, #1a1e2e 100%);
        }
        
        .notification-content {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            font-family: 'Poppins', sans-serif;
        }
        
        .notification-icon {
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .notification-message {
            flex: 1;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.4;
        }
        
        .notification-close {
            background: none;
            border: none;
            color: #888;
            font-size: 20px;
            cursor: pointer;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        
        .notification-close:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        /* Reset Confirmation Modal */
        .reset-confirmation-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            z-index: 15000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .reset-confirmation-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .reset-confirmation-modal {
            background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
            border: 3px solid #ff6b35;
            border-radius: 20px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            transform: scale(0.8) translateY(20px);
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .reset-confirmation-overlay.show .reset-confirmation-modal {
            transform: scale(1) translateY(0);
        }

        .modal-header {
            padding: 25px 30px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid rgba(255, 107, 53, 0.2);
            margin-bottom: 25px;
        }

        .modal-header h3 {
            color: #ff6b35;
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            color: #888;
            font-size: 28px;
            cursor: pointer;
            padding: 5px;
            line-height: 1;
            transition: all 0.2s ease;
        }

        .modal-close:hover {
            color: #ff6b35;
            transform: rotate(90deg);
        }

        .modal-content {
            padding: 0 30px;
        }

        .confirmation-avatar {
            text-align: center;
            margin-bottom: 20px;
        }

        .warning-emoji {
            font-size: 64px;
            filter: drop-shadow(0 0 20px rgba(251, 191, 36, 0.4));
            animation: warningPulse 2s ease-in-out infinite;
        }

        @keyframes warningPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .confirmation-text h4 {
            color: #ff6b35;
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 10px;
            text-align: center;
        }

        .confirmation-text p {
            color: #ccc;
            font-size: 16px;
            text-align: center;
            margin: 0 0 25px;
            line-height: 1.5;
        }

        .reset-effects {
            background: rgba(255, 107, 53, 0.05);
            border: 1px solid rgba(255, 107, 53, 0.2);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .effect-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            color: #ccc;
            font-size: 14px;
        }

        .effect-item:last-child {
            margin-bottom: 0;
        }

        .effect-icon {
            font-size: 18px;
            width: 28px;
            text-align: center;
            color: #ff6b35;
        }

        .modal-actions {
            padding: 20px 30px 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .confirm-reset-btn {
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            border: none;
            border-radius: 12px;
            padding: 14px 20px;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .confirm-reset-btn:hover {
            background: linear-gradient(135deg, #f7931e, #ff6b35);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 53, 0.3);
        }

        .cancel-reset-btn {
            background: #444;
            border: 2px solid #666;
            border-radius: 12px;
            padding: 14px 20px;
            color: #ccc;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .cancel-reset-btn:hover {
            background: #555;
            border-color: #888;
            color: #fff;
        }

        /* Reset Success Notification */
        .reset-success-notification {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.8);
            z-index: 20000;
            background: linear-gradient(135deg, #1a1a1a 0%, #1a2e1a 100%);
            border: 3px solid #4ade80;
            border-radius: 20px;
            padding: 30px;
            max-width: 450px;
            width: 90%;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .reset-success-notification.show {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }

        .success-content {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .success-icon {
            font-size: 48px;
            flex-shrink: 0;
            animation: successBounce 0.8s ease-out;
        }

        @keyframes successBounce {
            0% { transform: scale(0) rotate(180deg); }
            50% { transform: scale(1.2) rotate(10deg); }
            100% { transform: scale(1) rotate(0deg); }
        }

        .success-text h4 {
            color: #4ade80;
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 8px;
        }

        .success-text p {
            color: #ccc;
            margin: 0 0 12px;
            font-size: 14px;
            line-height: 1.4;
        }

        .success-details {
            display: grid;
            gap: 4px;
        }

        .success-details small {
            color: #888;
            font-size: 12px;
            line-height: 1.3;
        }

        /* Enhanced animations */
        .spinning {
            animation: resetSpin 1s linear infinite !important;
        }

        .reset-processing {
            background: linear-gradient(135deg, #666 0%, #888 100%) !important;
            cursor: not-allowed !important;
        }

        .has-identity {
            animation: identityGlow 3s ease-in-out infinite;
        }

        @keyframes identityGlow {
            0%, 100% { filter: drop-shadow(0 0 10px rgba(255, 107, 53, 0.3)); }
            50% { filter: drop-shadow(0 0 25px rgba(255, 107, 53, 0.6)); }
        }

        .emoji-bounce {
            animation: emojiBounce 2s ease-in-out infinite;
        }

        @keyframes emojiBounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        .meta-slide-in {
            animation: metaSlideIn 0.6s ease-out forwards;
            opacity: 0;
            transform: translateX(-20px);
        }

        @keyframes metaSlideIn {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .warning-slide-in {
            animation: warningSlideIn 0.8s ease-out;
        }

        @keyframes warningSlideIn {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .reason-valid {
            border-color: #4ade80 !important;
            box-shadow: 0 0 15px rgba(76, 175, 80, 0.2) !important;
        }

        .meta-hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.1);
        }

        .warning-focused {
            transform: scale(1.02);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.15);
        }

        @media (max-width: 768px) {
            .angel-notification {
                right: 10px;
                left: 10px;
                min-width: auto;
                max-width: none;
            }
            
            .modal-actions {
                grid-template-columns: 1fr;
            }
            
            .reset-confirmation-modal {
                margin: 20px;
                width: calc(100% - 40px);
            }
        }
        </style>
    `;
    
    jQuery('head').append(enhancedStyles);
});

// ═══════════════════════════════════════════════════════════════════════
// 🚀 PERFORMANCE OPTIMIZATIONS
// ═══════════════════════════════════════════════════════════════════════

// Lazy load customer avatars for better performance
function lazyLoadAvatars() {
    const avatars = document.querySelectorAll('.emoji-avatar[data-emoji]');
    
    if ('IntersectionObserver' in window) {
        const avatarObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const avatar = entry.target;
                    avatar.textContent = avatar.dataset.emoji;
                    avatarObserver.unobserve(avatar);
                }
            });
        });
        
        avatars.forEach(avatar => avatarObserver.observe(avatar));
    } else {
        // Fallback for browsers without IntersectionObserver
        avatars.forEach(avatar => {
            avatar.textContent = avatar.dataset.emoji;
        });
    }
}

// Throttled scroll handler for performance
let scrollTimeout;
jQuery(window).on('scroll', function() {
    if (scrollTimeout) {
        clearTimeout(scrollTimeout);
    }
    
    scrollTimeout = setTimeout(function() {
        // Handle scroll-based animations here
        const scrolled = jQuery(window).scrollTop();
        
        // Parallax effects
        jQuery('.avatar-glow').each(function() {
            const parallax = scrolled * 0.1;
            jQuery(this).css('transform', 
                `translate(-50%, -50%) scale(${1 + parallax / 2000})`);
        });
    }, 16); // ~60fps
});

// Initialize performance optimizations
jQuery(document).ready(function() {
    lazyLoadAvatars();
});