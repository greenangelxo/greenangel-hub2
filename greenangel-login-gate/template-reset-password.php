<?php
/**
 * Template for password reset form
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
?>

<div class="angel-auth-wrapper">
    <div class="angel-auth-container">
        <div class="angel-brand">
            <h1 class="angel-title">Create New Password</h1>
            <p class="angel-subtitle">Make it strong and magical ✨</p>
        </div>
        
        <?php if ($key_valid): ?>
            <div class="angel-form-container" id="reset-password-form">
                <form id="angel-reset-password-form" class="angel-form">
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
                            >
                            <button type="button" class="angel-password-toggle reset-password-toggle" data-target="reset-password">
                                <span class="toggle-show">👁️</span>
                                <span class="toggle-hide" style="display: none;">🙈</span>
                            </button>
                        </div>
                        <p class="angel-field-hint">Must be at least 8 characters</p>
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
                            >
                            <button type="button" class="angel-password-toggle reset-password-toggle" data-target="reset-password-confirm">
                                <span class="toggle-show">👁️</span>
                                <span class="toggle-hide" style="display: none;">🙈</span>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="angel-button">
                        <span class="button-text">Reset Password</span>
                        <span class="button-loader" style="display: none;">⏳</span>
                    </button>
                </form>
                
                <div class="angel-links">
                    <a href="<?php echo home_url('/angel-login/'); ?>" class="angel-link">← Back to login</a>
                </div>
            </div>
        <?php else: ?>
            <div class="angel-form-container">
                <div class="angel-message error">
                    <?php if ($user->get_error_code() === 'expired_key'): ?>
                        This reset link has expired. Please request a new one.
                    <?php else: ?>
                        This reset link is invalid. Please request a new one.
                    <?php endif; ?>
                </div>
                
                <div class="angel-links" style="margin-top: 20px;">
                    <a href="<?php echo home_url('/angel-login/'); ?>" class="angel-link">← Back to login</a>
                </div>
            </div>
        <?php endif; ?>
        
        <div id="angel-messages" class="angel-messages"></div>
    </div>
</div>