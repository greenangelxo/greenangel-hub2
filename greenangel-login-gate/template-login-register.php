<?php defined('ABSPATH') || exit; ?>
<div class="angel-auth-wrapper">
    <div class="angel-auth-container">
        
        <!-- Logo/Brand Area -->
        <div class="angel-brand">
            <h1 class="angel-title">Welcome Angel</h1>
            <p class="angel-subtitle">Your magical journey begins here</p>
        </div>
        
        <!-- Tab Navigation -->
        <div class="angel-tabs">
            <button class="angel-tab active" data-tab="login">Sign In</button>
            <button class="angel-tab" data-tab="register">Join Us</button>
        </div>
        
        <!-- Login Form -->
        <div class="angel-form-container" id="login-form">
            <form class="angel-form" id="angel-login-form">
                <?php
                    // Generate unique nonce for login form
                    wp_nonce_field('angel_login_nonce_action', 'angel_login_nonce_login');
                ?>
                
                <div class="angel-field-group">
                    <label for="login-email" class="angel-label">Email</label>
                    <input type="email" id="login-email" name="email" class="angel-input" required autocomplete="email">
                </div>
                
                <div class="angel-field-group">
                    <label for="login-password" class="angel-label">Password</label>
                    <div class="angel-password-wrapper">
                        <input type="password" id="login-password" name="password" class="angel-input" required autocomplete="current-password">
                        <button type="button" class="angel-password-toggle" data-target="login-password" aria-label="Toggle password visibility">
                            <span class="toggle-show">👁️</span>
                            <span class="toggle-hide" style="display: none;">🙈</span>
                        </button>
                    </div>
                </div>
                
                <div class="angel-checkbox-group">
                    <label class="angel-checkbox-label">
                        <input type="checkbox" id="login-remember" name="remember" class="angel-checkbox">
                        <span class="angel-checkbox-custom"></span>
                        Remember me
                    </label>
                </div>
                
                <button type="submit" class="angel-button angel-button-primary">
                    <span class="button-text">Sign In</span>
                    <span class="button-loader" style="display: none;">✨</span>
                </button>
                
                <div class="angel-links">
                    <a href="#" class="angel-link" id="forgot-password-link">Forgot your password?</a>
                </div>
                
            </form>
        </div>
        
        <!-- Registration Form -->
        <div class="angel-form-container" id="register-form" style="display: none;">
            
            <!-- Invitation Only Notice -->
            <div class="angel-invitation-notice">
                <h3>Green Angel is an invitation-only community</h3>
                <p>You require an angel-access code to join our family</p>
                <div class="ip-display">
                    <span class="ip-label">Your IP:</span>
                    <span class="ip-address" id="user-ip">Loading...</span>
                </div>
            </div>
            
            <form class="angel-form" id="angel-register-form">
                <?php
                    // Generate unique nonce for register form
                    wp_nonce_field('angel_login_nonce_action', 'angel_register_nonce');
                ?>
                
                <div class="angel-field-group">
                    <label for="reg-angel-code" class="angel-label">Angel Code</label>
                    <input type="text" id="reg-angel-code" name="angel_code" class="angel-input" required autocomplete="off" maxlength="50">
                    <div class="angel-field-hint">Enter your special invitation code</div>
                    <div class="angel-code-validation" id="code-validation"></div>
                </div>
                
                <div class="angel-field-row">
                    <div class="angel-field-group">
                        <label for="reg-first-name" class="angel-label">First Name</label>
                        <input type="text" id="reg-first-name" name="first_name" class="angel-input" required autocomplete="given-name">
                    </div>
                    <div class="angel-field-group">
                        <label for="reg-last-name" class="angel-label">Last Name</label>
                        <input type="text" id="reg-last-name" name="last_name" class="angel-input" required autocomplete="family-name">
                    </div>
                </div>

                <div class="angel-field-row">
                    <div class="angel-field-group">
                        <label for="reg-birth-month" class="angel-label">Birth Month</label>
                        <select id="reg-birth-month" name="birth_month" class="angel-select" required autocomplete="bday-month">
                            <option value="">Select month</option>
                            <option value="Jan">January</option>
                            <option value="Feb">February</option>
                            <option value="Mar">March</option>
                            <option value="Apr">April</option>
                            <option value="May">May</option>
                            <option value="Jun">June</option>
                            <option value="Jul">July</option>
                            <option value="Aug">August</option>
                            <option value="Sep">September</option>
                            <option value="Oct">October</option>
                            <option value="Nov">November</option>
                            <option value="Dec">December</option>
                        </select>
                    </div>
                    <div class="angel-field-group">
                        <label for="reg-birth-year" class="angel-label">Birth Year</label>
                        <input type="text" id="reg-birth-year" name="birth_year" class="angel-input" placeholder="YYYY" maxlength="4" required autocomplete="bday-year" pattern="[0-9]{4}" inputmode="numeric">
                    </div>
                </div>
                
                <div class="angel-field-group">
                    <label for="reg-email" class="angel-label">Email</label>
                    <input type="email" id="reg-email" name="email" class="angel-input" required autocomplete="email">
                </div>
                
                <div class="angel-field-group">
                    <label for="reg-password" class="angel-label">Password</label>
                    <div class="angel-password-wrapper">
                        <input type="password" id="reg-password" name="password" class="angel-input" required autocomplete="new-password" minlength="8">
                        <button type="button" class="angel-password-toggle" data-target="reg-password" aria-label="Toggle password visibility">
                            <span class="toggle-show">👁️</span>
                            <span class="toggle-hide" style="display: none;">🙈</span>
                        </button>
                    </div>
                </div>
                
                <!-- Display name dice game -->
                <div class="angel-field-group">
                    <label for="roll-dice-btn" class="angel-label">Choose Your Display Name</label>
                    <div class="angel-name-dice-game">
                        <div class="dice-display-name" id="dice-display-name">
                            <span class="dice-name-text">Press the dice to generate!</span>
                        </div>
                        <button type="button" class="angel-dice-button" id="roll-dice-btn" aria-label="Generate random display name">
                            <span class="dice-icon">🎲</span>
                            <span class="dice-text">Roll the Dice!</span>
                            <span class="dice-count">5 rolls left</span>
                        </button>
                        <input type="hidden" id="reg-display-name-hidden" name="display_name" value="">
                        <div class="dice-hint">You can roll up to 5 times to find your perfect name!</div>
                    </div>
                </div>
                
                <!-- Hidden newsletter signup -->
                <input type="hidden" name="newsletter_signup" value="1">
                
                <button type="submit" class="angel-button angel-button-primary">
                    <span class="button-text">Join the Family ✨</span>
                    <span class="button-loader" style="display: none;">✨</span>
                </button>
                
            </form>
        </div>
        
        <!-- Messages -->
        <div class="angel-messages" id="angel-messages"></div>
        
    </div>
</div>