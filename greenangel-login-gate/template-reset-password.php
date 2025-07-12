<?php
/**
 * Template for password reset form - LED Dark Mode Enhanced
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Get reset key and login from URL
$key = isset($_GET['key']) ? sanitize_text_field($_GET['key']) : '';
$login = isset($_GET['login']) ? sanitize_text_field($_GET['login']) : '';

// Verify the reset key is valid before showing form
$user = check_password_reset_key($key, $login);
$key_valid = !is_wp_error($user);

// Enhanced error handling for different scenarios
$error_type = '';
$error_message = '';

if (!$key_valid && is_wp_error($user)) {
    $error_type = $user->get_error_code();
    switch ($error_type) {
        case 'expired_key':
            $error_message = 'This reset link has expired. Please request a new one.';
            break;
        case 'invalid_key':
            $error_message = 'This reset link is invalid. Please request a new one.';
            break;
        default:
            $error_message = 'This reset link is no longer valid. Please request a new one.';
            break;
    }
}
?>

<div class="angel-auth-wrapper">
    <div class="angel-auth-container angel-reset-container">
        
        <!-- Enhanced Brand Section for Reset -->
        <div class="angel-brand">
            <h1 class="angel-title">
                <?php echo $key_valid ? 'Create New Password' : 'Reset Link Issue'; ?>
            </h1>
            <p class="angel-subtitle">
                <?php echo $key_valid ? 'Make it strong and magical ✨' : 'Let\'s get you back on track 🔧'; ?>
            </p>
        </div>
        
        <?php if ($key_valid): ?>
            <!-- Valid Reset Key - Show Password Reset Form -->
            <div class="angel-form-container" id="reset-password-form">
                
                <!-- Password Strength Indicator -->
                <div class="password-strength-container" style="margin-bottom: 20px;">
                    <div class="password-strength-info">
                        <span class="strength-label">Password Strength:</span>
                        <span class="strength-indicator" id="strength-indicator">Not Set</span>
                    </div>
                    <div class="strength-bar">
                        <div class="strength-fill" id="strength-fill"></div>
                    </div>
                </div>
                
                <form id="angel-reset-password-form" class="angel-form">
                    <?php wp_nonce_field('angel_login_nonce_action', 'angel_login_nonce'); ?>
                    
                    <!-- Hidden fields for reset data -->
                    <input type="hidden" name="key" value="<?php echo esc_attr($key); ?>">
                    <input type="hidden" name="login" value="<?php echo esc_attr($login); ?>">
                    
                    <div class="angel-field-group">
                        <label class="angel-label" for="reset-password">New Password</label>
                        <div class="angel-password-wrapper">
                            <input 
                                type="password" 
                                id="reset-password" 
                                name="password" 
                                class="angel-input" 
                                placeholder="Enter new password" 
                                required
                                minlength="8"
                                autocomplete="new-password"
                                aria-describedby="password-requirements"
                            >
                            <button type="button" class="angel-password-toggle" data-target="reset-password" aria-label="Toggle password visibility">
                                <span class="toggle-show">👁️</span>
                                <span class="toggle-hide" style="display: none;">🙈</span>
                            </button>
                        </div>
                        <div class="angel-field-hint" id="password-requirements">
                            Must be at least 8 characters with a mix of letters, numbers, and symbols
                        </div>
                    </div>
                    
                    <div class="angel-field-group">
                        <label class="angel-label" for="reset-password-confirm">Confirm Password</label>
                        <div class="angel-password-wrapper">
                            <input 
                                type="password" 
                                id="reset-password-confirm" 
                                name="password_confirm" 
                                class="angel-input" 
                                placeholder="Confirm new password" 
                                required
                                autocomplete="new-password"
                                aria-describedby="password-match-status"
                            >
                            <button type="button" class="angel-password-toggle" data-target="reset-password-confirm" aria-label="Toggle password confirmation visibility">
                                <span class="toggle-show">👁️</span>
                                <span class="toggle-hide" style="display: none;">🙈</span>
                            </button>
                        </div>
                        <div class="password-match-indicator" id="password-match-status"></div>
                    </div>
                    
                    <!-- Enhanced Security Tips -->
                    <div class="security-tips">
                        <h4>💡 Security Tips:</h4>
                        <ul>
                            <li>Use a unique password you haven't used elsewhere</li>
                            <li>Include uppercase, lowercase, numbers, and symbols</li>
                            <li>Avoid personal information like names or dates</li>
                            <li>Consider using a password manager</li>
                        </ul>
                    </div>
                    
                    <button type="submit" class="angel-button angel-reset-submit">
                        <span class="button-text">Reset Password</span>
                        <span class="button-loader" style="display: none;">🔄</span>
                    </button>
                </form>
                
                <div class="angel-links">
                    <a href="<?php echo home_url('/angel-login/'); ?>" class="angel-link">← Back to login</a>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Invalid/Expired Reset Key - Show Error State -->
            <div class="angel-form-container angel-error-state">
                
                <!-- Enhanced Error Display -->
                <div class="angel-reset-error">
                    <div class="error-icon">
                        <?php if ($error_type === 'expired_key'): ?>
                            ⏰
                        <?php else: ?>
                            🔒
                        <?php endif; ?>
                    </div>
                    
                    <div class="error-content">
                        <h3 class="error-title">
                            <?php if ($error_type === 'expired_key'): ?>
                                Link Expired
                            <?php else: ?>
                                Invalid Link
                            <?php endif; ?>
                        </h3>
                        
                        <p class="error-message">
                            <?php echo esc_html($error_message); ?>
                        </p>
                        
                        <div class="error-help">
                            <p><strong>What can you do?</strong></p>
                            <ul>
                                <li>Request a new password reset link</li>
                                <li>Check your email for a more recent reset link</li>
                                <li>Contact support if you continue having issues</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="error-actions">
                    <a href="<?php echo home_url('/angel-login/'); ?>" class="angel-button angel-button-primary">
                        <span class="button-text">Request New Reset Link</span>
                    </a>
                    
                    <div class="angel-links" style="margin-top: 16px;">
                        <a href="<?php echo home_url('/angel-login/'); ?>" class="angel-link">← Back to login</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Messages Container -->
        <div id="angel-messages" class="angel-messages"></div>
        
        <!-- Enhanced User Info Display -->
        <?php if ($key_valid && $user): ?>
        <div class="reset-user-info">
            <div class="user-avatar">
                <?php echo get_avatar($user->ID, 40, '', '', array('class' => 'avatar-led')); ?>
            </div>
            <div class="user-details">
                <span class="user-name"><?php echo esc_html($user->display_name ?: $user->user_login); ?></span>
                <span class="user-email"><?php echo esc_html($user->user_email); ?></span>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Enhanced LED Styles for Reset Form -->
<style>
/* Password Reset Specific Enhancements */
.angel-reset-container {
    max-width: 520px;
}

/* Password Strength Indicator */
.password-strength-container {
    background: #0f0f0f;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 20px;
}

.password-strength-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.strength-label {
    font-size: 14px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.8);
}

.strength-indicator {
    font-size: 12px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.6);
    transition: all 0.3s ease;
}

.strength-bar {
    height: 6px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 3px;
    overflow: hidden;
    position: relative;
}

.strength-fill {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, #ff6b6b 0%, #ffd93d 50%, #6bcf7f 100%);
    border-radius: 3px;
    transition: all 0.3s ease;
    position: relative;
}

.strength-fill::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    animation: strength-shimmer 2s ease-in-out infinite;
}

@keyframes strength-shimmer {
    0% { left: -100%; }
    100% { left: 100%; }
}

/* Strength levels */
.strength-weak .strength-indicator {
    background: rgba(255, 107, 107, 0.2);
    color: #ff6b6b;
}

.strength-medium .strength-indicator {
    background: rgba(255, 217, 61, 0.2);
    color: #ffd93d;
}

.strength-strong .strength-indicator {
    background: rgba(107, 207, 127, 0.2);
    color: #6bcf7f;
}

/* Password Match Indicator */
.password-match-indicator {
    font-size: 12px;
    margin-top: 4px;
    min-height: 16px;
    transition: all 0.3s ease;
}

.password-match-indicator.match {
    color: #6bcf7f;
}

.password-match-indicator.no-match {
    color: #ff6b6b;
}

/* Security Tips */
.security-tips {
    background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 100%);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 16px;
    margin: 20px 0;
}

.security-tips h4 {
    font-size: 14px;
    font-weight: 600;
    color: #ffffff;
    margin: 0 0 12px 0;
}

.security-tips ul {
    margin: 0;
    padding-left: 20px;
    list-style: none;
}

.security-tips li {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 6px;
    position: relative;
    line-height: 1.4;
}

.security-tips li::before {
    content: '→';
    position: absolute;
    left: -16px;
    color: rgba(255, 255, 255, 0.4);
}

/* Enhanced Error State */
.angel-error-state {
    text-align: center;
}

.angel-reset-error {
    background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
    border: 1px solid rgba(255, 204, 203, 0.2);
    border-radius: 16px;
    padding: 30px 24px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.angel-reset-error::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, 
        #ff6b6b 0%, 
        #ff5252 50%, 
        #ff6b6b 100%
    );
    background-size: 200% 100%;
    animation: error-pulse 2s ease-in-out infinite;
}

@keyframes error-pulse {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.error-icon {
    font-size: 48px;
    margin-bottom: 16px;
    filter: grayscale(0.3);
}

.error-title {
    font-size: 20px;
    font-weight: 700;
    color: #ffffff;
    margin: 0 0 12px 0;
}

.error-message {
    font-size: 16px;
    color: rgba(255, 255, 255, 0.8);
    margin: 0 0 20px 0;
    line-height: 1.5;
}

.error-help {
    text-align: left;
    background: rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    padding: 16px;
    margin-top: 20px;
}

.error-help p {
    font-size: 14px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.9);
    margin: 0 0 8px 0;
}

.error-help ul {
    margin: 0;
    padding-left: 20px;
    list-style: none;
}

.error-help li {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 4px;
    position: relative;
}

.error-help li::before {
    content: '•';
    position: absolute;
    left: -12px;
    color: rgba(255, 255, 255, 0.4);
}

/* Error Actions */
.error-actions {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

/* User Info Display */
.reset-user-info {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-top: 24px;
    padding: 16px;
    background: rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
}

.user-avatar .avatar-led {
    border-radius: 50%;
    border: 2px solid rgba(255, 255, 255, 0.2);
    filter: grayscale(0.2);
}

.user-details {
    display: flex;
    flex-direction: column;
    text-align: left;
}

.user-name {
    font-size: 14px;
    font-weight: 600;
    color: #ffffff;
    line-height: 1.2;
}

.user-email {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.6);
    line-height: 1.2;
}

/* Enhanced Reset Button */
.angel-reset-submit {
    background: linear-gradient(135deg, #2a2a2a 0%, #404040 100%);
    border: 1px solid rgba(255, 255, 255, 0.2);
    position: relative;
    overflow: hidden;
}

.angel-reset-submit::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, 
        transparent, 
        rgba(107, 207, 127, 0.2), 
        transparent
    );
    transition: left 0.6s ease;
}

.angel-reset-submit:hover::before {
    left: 100%;
}

.angel-reset-submit:hover {
    border-color: rgba(107, 207, 127, 0.4);
    box-shadow: 0 0 20px rgba(107, 207, 127, 0.1);
}

/* Mobile Optimizations */
@media (max-width: 640px) {
    .angel-reset-container {
        padding: 24px 20px;
    }
    
    .security-tips {
        padding: 12px;
    }
    
    .security-tips h4 {
        font-size: 13px;
    }
    
    .security-tips li {
        font-size: 11px;
    }
    
    .reset-user-info {
        flex-direction: column;
        text-align: center;
        gap: 8px;
    }
    
    .user-details {
        align-items: center;
    }
}

/* Accessibility Enhancements */
@media (prefers-reduced-motion: reduce) {
    .strength-fill::after,
    .angel-reset-error::before {
        animation: none;
    }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .angel-reset-error {
        border-color: #ff6b6b;
    }
    
    .strength-bar {
        border: 1px solid rgba(255, 255, 255, 0.5);
    }
}
</style>

<!-- Enhanced JavaScript for Password Reset -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('reset-password');
    const confirmInput = document.getElementById('reset-password-confirm');
    const strengthIndicator = document.getElementById('strength-indicator');
    const strengthFill = document.getElementById('strength-fill');
    const matchStatus = document.getElementById('password-match-status');
    const strengthContainer = document.querySelector('.password-strength-container');
    
    // Enhanced password strength checker
    function checkPasswordStrength(password) {
        let score = 0;
        let feedback = [];
        
        // Length check
        if (password.length >= 8) score += 1;
        else feedback.push('At least 8 characters');
        
        // Complexity checks
        if (/[a-z]/.test(password)) score += 1;
        else feedback.push('Lowercase letter');
        
        if (/[A-Z]/.test(password)) score += 1;
        else feedback.push('Uppercase letter');
        
        if (/[0-9]/.test(password)) score += 1;
        else feedback.push('Number');
        
        if (/[^A-Za-z0-9]/.test(password)) score += 1;
        else feedback.push('Special character');
        
        // Determine strength level
        let strength = 'weak';
        let percentage = 0;
        let label = 'Very Weak';
        
        if (score >= 5) {
            strength = 'strong';
            percentage = 100;
            label = 'Strong';
        } else if (score >= 3) {
            strength = 'medium';
            percentage = 60;
            label = 'Medium';
        } else if (score >= 1) {
            percentage = 30;
            label = 'Weak';
        }
        
        return { strength, percentage, label, feedback, score };
    }
    
    // Update password strength display
    function updatePasswordStrength() {
        const password = passwordInput.value;
        const result = checkPasswordStrength(password);
        
        // Update strength indicator
        strengthIndicator.textContent = result.label;
        strengthFill.style.width = result.percentage + '%';
        
        // Update container class
        strengthContainer.className = 'password-strength-container strength-' + result.strength;
        
        // Check password match
        checkPasswordMatch();
    }
    
    // Check if passwords match
    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirm = confirmInput.value;
        
        if (confirm === '') {
            matchStatus.textContent = '';
            matchStatus.className = 'password-match-indicator';
            return;
        }
        
        if (password === confirm) {
            matchStatus.textContent = '✓ Passwords match';
            matchStatus.className = 'password-match-indicator match';
        } else {
            matchStatus.textContent = '✗ Passwords do not match';
            matchStatus.className = 'password-match-indicator no-match';
        }
    }
    
    // Event listeners
    if (passwordInput) {
        passwordInput.addEventListener('input', updatePasswordStrength);
        passwordInput.addEventListener('keyup', updatePasswordStrength);
    }
    
    if (confirmInput) {
        confirmInput.addEventListener('input', checkPasswordMatch);
        confirmInput.addEventListener('keyup', checkPasswordMatch);
    }
    
    // Enhanced form validation
    const resetForm = document.getElementById('angel-reset-password-form');
    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirm = confirmInput.value;
            const result = checkPasswordStrength(password);
            
            // Prevent submission if password is too weak
            if (result.score < 3) {
                e.preventDefault();
                
                // Show enhanced error message
                const message = 'Please choose a stronger password. Missing: ' + result.feedback.join(', ');
                showResetMessage(message, 'error');
                
                // Add shake effect to password field
                passwordInput.style.animation = 'shake 0.5s ease-in-out';
                setTimeout(() => {
                    passwordInput.style.animation = '';
                }, 500);
                
                return false;
            }
            
            // Check passwords match
            if (password !== confirm) {
                e.preventDefault();
                showResetMessage('Passwords do not match. Please try again.', 'error');
                confirmInput.focus();
                return false;
            }
        });
    }
    
    // Enhanced message display
    function showResetMessage(message, type) {
        const messagesContainer = document.getElementById('angel-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `angel-message ${type}`;
        messageDiv.style.opacity = '0';
        messageDiv.style.transform = 'translateY(-10px)';
        messageDiv.textContent = message;
        
        messagesContainer.innerHTML = '';
        messagesContainer.appendChild(messageDiv);
        
        // Animate in
        setTimeout(() => {
            messageDiv.style.transition = 'all 0.3s ease';
            messageDiv.style.opacity = '1';
            messageDiv.style.transform = 'translateY(0)';
        }, 10);
        
        // Auto-hide after delay
        if (type === 'success') {
            setTimeout(() => {
                messageDiv.style.opacity = '0';
                messageDiv.style.transform = 'translateY(-10px)';
                setTimeout(() => messageDiv.remove(), 300);
            }, 3000);
        }
    }
    
    // Add enhanced CSS animations
    const resetStyles = document.createElement('style');
    resetStyles.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    `;
    document.head.appendChild(resetStyles);
});
</script>