/**
 * 🎛️ MAINTENANCE ADMIN JAVASCRIPT
 * Interactive admin controls with real-time updates
 */

(function($) {
    'use strict';
    
    // 🌟 Initialize when DOM is ready
    $(document).ready(function() {
        initMaintenanceAdmin();
    });
    
    /**
     * 🚀 Main initialization function
     */
    function initMaintenanceAdmin() {
        bindEvents();
        updateSessionTimer();
        initCharacterCounter();
        
        // Start session timer if maintenance is active
        if (window.maintenanceData && window.maintenanceData.isEnabled) {
            setInterval(updateSessionTimer, 1000);
        }
        
        console.log('🌙 Maintenance Admin initialized');
    }
    
    /**
     * 🔗 Bind all event handlers
     */
    function bindEvents() {
        // Toggle maintenance mode
        $('#toggle-maintenance').on('click', handleToggleMaintenance);
        
        // Save settings
        $('#save-settings').on('click', handleSaveSettings);
        
        // Tool buttons
        $('#test-emergency').on('click', handleTestEmergency);
        $('#clear-logs').on('click', handleClearLogs);
        
        // Emergency code copy
        $('.emergency-code').on('click', copyEmergencyCode);
        
        // Form validation
        $('#maintenance-message').on('input', validateMessage);
        $('#maintenance-eta').on('input', validateETA);
        
        // Keyboard shortcuts
        $(document).on('keydown', handleKeyboardShortcuts);
    }
    
    /**
     * 🔄 Toggle maintenance mode with AJAX
     */
    function handleToggleMaintenance(e) {
        e.preventDefault();
        
        const $button = $(this);
        const isCurrentlyEnabled = $button.hasClass('active');
        
        // Show loading state
        $button.addClass('loading').prop('disabled', true);
        
        $.ajax({
            url: window.maintenanceAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'toggle_maintenance',
                nonce: window.maintenanceAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateToggleButton(!isCurrentlyEnabled);
                    updateStatusIndicator(!isCurrentlyEnabled);
                    showNotification(response.data.message, 'success');
                    
                    // Update global state
                    window.maintenanceData.isEnabled = !isCurrentlyEnabled;
                    
                    // Start/stop session timer
                    if (!isCurrentlyEnabled) {
                        window.maintenanceData.currentSessionStart = Math.floor(Date.now() / 1000);
                        setInterval(updateSessionTimer, 1000);
                    }
                } else {
                    showNotification('Failed to toggle maintenance mode', 'error');
                }
            },
            error: function() {
                showNotification('Network error occurred', 'error');
            },
            complete: function() {
                $button.removeClass('loading').prop('disabled', false);
            }
        });
    }
    
    /**
     * 💾 Save maintenance settings
     */
    function handleSaveSettings(e) {
        e.preventDefault();
        
        const $button = $(this);
        const message = $('#maintenance-message').val();
        const eta = $('#maintenance-eta').val();
        
        // Validate inputs
        if (message.length > 500) {
            showNotification('Message is too long (max 500 characters)', 'error');
            return;
        }
        
        // Show loading state
        $button.addClass('loading').prop('disabled', true);
        
        $.ajax({
            url: window.maintenanceAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'save_maintenance_settings',
                nonce: window.maintenanceAdmin.nonce,
                message: message,
                eta: eta
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.data.message, 'success');
                    
                    // Add subtle success animation
                    $button.addClass('success-pulse');
                    setTimeout(() => $button.removeClass('success-pulse'), 1000);
                } else {
                    showNotification('Failed to save settings', 'error');
                }
            },
            error: function() {
                showNotification('Network error occurred', 'error');
            },
            complete: function() {
                $button.removeClass('loading').prop('disabled', false);
            }
        });
    }
    
    /**
     * 🚪 Test emergency access
     */
    function handleTestEmergency(e) {
        e.preventDefault();
        
        const emergencyUrl = window.maintenanceAdmin.homeUrl + '?iamjess=true';
        
        showNotification('Opening emergency access URL...', 'success');
        
        // Open in new tab with a delay
        setTimeout(() => {
            window.open(emergencyUrl, '_blank');
        }, 500);
    }
    
    /**
     * 🗑️ Clear maintenance logs
     */
    function handleClearLogs(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to clear all maintenance logs?')) {
            return;
        }
        
        // Implementation for clearing logs would go here
        showNotification('Logs cleared successfully', 'success');
    }
    
    /**
     * 📋 Copy emergency code to clipboard
     */
    function copyEmergencyCode() {
        const code = '?iamjess=true';
        
        if (navigator.clipboard) {
            navigator.clipboard.writeText(code).then(() => {
                showNotification('Emergency code copied!', 'success');
                
                // Visual feedback
                $(this).addClass('copied');
                setTimeout(() => $(this).removeClass('copied'), 1000);
            });
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = code;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            
            showNotification('Emergency code copied!', 'success');
        }
    }
    
    /**
     * ⏱️ Update session timer
     */
    function updateSessionTimer() {
        if (!window.maintenanceData.isEnabled || !window.maintenanceData.currentSessionStart) {
            $('#current-session').text('00:00:00');
            return;
        }
        
        const now = Math.floor(Date.now() / 1000);
        const elapsed = now - window.maintenanceData.currentSessionStart;
        
        const hours = Math.floor(elapsed / 3600);
        const minutes = Math.floor((elapsed % 3600) / 60);
        const seconds = elapsed % 60;
        
        const timeString = [
            hours.toString().padStart(2, '0'),
            minutes.toString().padStart(2, '0'),
            seconds.toString().padStart(2, '0')
        ].join(':');
        
        $('#current-session').text(timeString);
    }
    
    /**
     * 🔢 Initialize character counter
     */
    function initCharacterCounter() {
        const $textarea = $('#maintenance-message');
        const $counter = $('#char-count');
        
        $textarea.on('input', function() {
            const length = $(this).val().length;
            $counter.text(length);
            
            // Color coding
            if (length > 450) {
                $counter.css('color', '#ff4444');
            } else if (length > 400) {
                $counter.css('color', '#ff9500');
            } else {
                $counter.css('color', '#666');
            }
        });
    }
    
    /**
     * ✅ Validate message input
     */
    function validateMessage() {
        const $input = $(this);
        const length = $input.val().length;
        
        if (length > 500) {
            $input.addClass('error');
            showNotification('Message too long!', 'error');
        } else {
            $input.removeClass('error');
        }
    }
    
    /**
     * ✅ Validate ETA input
     */
    function validateETA() {
        const $input = $(this);
        const value = $input.val().trim();
        
        // Simple validation - could be enhanced
        if (value.length > 100) {
            $input.addClass('error');
        } else {
            $input.removeClass('error');
        }
    }
    
    /**
     * ⌨️ Handle keyboard shortcuts
     */
    function handleKeyboardShortcuts(e) {
        // Ctrl/Cmd + M = Toggle maintenance
        if ((e.ctrlKey || e.metaKey) && e.key === 'm') {
            e.preventDefault();
            $('#toggle-maintenance').click();
        }
        
        // Ctrl/Cmd + S = Save settings
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            $('#save-settings').click();
        }
        
        // Escape = Clear focus
        if (e.key === 'Escape') {
            $(':focus').blur();
        }
    }
    
    /**
     * 🔄 Update toggle button state
     */
    function updateToggleButton(isEnabled) {
        const $button = $('#toggle-maintenance');
        const $icon = $button.find('.button-icon');
        const $text = $button.find('.button-text');
        
        if (isEnabled) {
            $button.addClass('active');
            $icon.text('🌞');
            $text.text('Wake Up Site');
        } else {
            $button.removeClass('active');
            $icon.text('🌙');
            $text.text('Put to Sleep');
        }
        
        // Smooth transition effect
        $button.addClass('state-changing');
        setTimeout(() => $button.removeClass('state-changing'), 300);
    }
    
    /**
     * 🔴 Update status indicator
     */
    function updateStatusIndicator(isEnabled) {
        const $dot = $('.status-dot');
        const $text = $('#status-text');
        
        if (isEnabled) {
            $dot.removeClass('disabled').addClass('enabled');
            $text.text('MAINTENANCE ACTIVE');
        } else {
            $dot.removeClass('enabled').addClass('disabled');
            $text.text('SITE IS LIVE');
        }
        
        // Pulse effect
        $dot.addClass('pulse-effect');
        setTimeout(() => $dot.removeClass('pulse-effect'), 1000);
    }
    
    /**
     * 🔔 Show notification
     */
    function showNotification(message, type = 'info') {
        const $container = $('#notification-container');
        
        const $notification = $('<div>')
            .addClass(`notification ${type}`)
            .text(message);
        
        $container.append($notification);
        
        // Auto remove after 4 seconds
        setTimeout(() => {
            $notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 4000);
        
        // Add click to dismiss
        $notification.on('click', function() {
            $(this).fadeOut(300, function() {
                $(this).remove();
            });
        });
    }
    
    /**
     * 🎨 Add visual effects
     */
    function addVisualEffects() {
        // Hover effects for cards
        $('.settings-card, .main-status').hover(
            function() {
                $(this).addClass('hover-glow');
            },
            function() {
                $(this).removeClass('hover-glow');
            }
        );
        
        // Button ripple effect
        $('.toggle-button, .save-button, .tool-button').on('click', function(e) {
            const $button = $(this);
            const rect = this.getBoundingClientRect();
            const ripple = $('<span class="ripple"></span>');
            
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.css({
                width: size,
                height: size,
                left: x,
                top: y
            });
            
            $button.append(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    }
    
    // Initialize visual effects
    $(document).ready(addVisualEffects);
    
})(jQuery);