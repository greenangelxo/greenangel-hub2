<div class="angel-auth-wrapper">
    <div class="angel-auth-container">
        
        <!-- Logo/Brand Area -->
        <div class="angel-brand">
            <h1 class="angel-title">Welcome, Angel ✨</h1>
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
                
                <div class="angel-field-group">
                    <label for="login-email" class="angel-label">Email</label>
                    <input type="email" id="login-email" name="email" class="angel-input" required>
                </div>
                
                <div class="angel-field-group">
                    <label for="login-password" class="angel-label">Password</label>
                    <div class="angel-password-wrapper">
                        <input type="password" id="login-password" name="password" class="angel-input" required>
                        <button type="button" class="angel-password-toggle" data-target="login-password">
                            <span class="toggle-show">👁️</span>
                            <span class="toggle-hide" style="display: none;">🙈</span>
                        </button>
                    </div>
                </div>
                
                <div class="angel-checkbox-group">
                    <label class="angel-checkbox-label">
                        <input type="checkbox" name="remember" class="angel-checkbox">
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
                
                <div class="angel-field-row">
                    <div class="angel-field-group">
                        <label for="reg-first-name" class="angel-label">First Name</label>
                        <input type="text" id="reg-first-name" name="first_name" class="angel-input" required>
                    </div>
                    <div class="angel-field-group">
                        <label for="reg-last-name" class="angel-label">Last Name</label>
                        <input type="text" id="reg-last-name" name="last_name" class="angel-input" required>
                    </div>
                </div>
                
                <div class="angel-field-group">
                    <label for="reg-email" class="angel-label">Email</label>
                    <input type="email" id="reg-email" name="email" class="angel-input" required>
                </div>
                
                <div class="angel-field-group">
                    <label for="reg-password" class="angel-label">Password</label>
                    <div class="angel-password-wrapper">
                        <input type="password" id="reg-password" name="password" class="angel-input" required>
                        <button type="button" class="angel-password-toggle" data-target="reg-password">
                            <span class="toggle-show">👁️</span>
                            <span class="toggle-hide" style="display: none;">🙈</span>
                        </button>
                    </div>
                </div>
                
                <div class="angel-field-group">
                    <label for="reg-angel-code" class="angel-label">Angel Code</label>
                    <input type="text" id="reg-angel-code" name="angel_code" class="angel-input" required>
                    <div class="angel-field-hint">Enter your special invitation code</div>
                    <div class="angel-code-validation" id="code-validation"></div>
                </div>
                
                <div class="angel-field-row">
                    <div class="angel-field-group">
                        <label for="reg-birth-month" class="angel-label">Birth Month</label>
                        <select id="reg-birth-month" name="birth_month" class="angel-select" required>
                            <option value="">Select month</option>
                            <option value="Jan">Jan</option>
                            <option value="Feb">Feb</option>
                            <option value="Mar">Mar</option>
                            <option value="Apr">Apr</option>
                            <option value="May">May</option>
                            <option value="Jun">Jun</option>
                            <option value="Jul">Jul</option>
                            <option value="Aug">Aug</option>
                            <option value="Sep">Sep</option>
                            <option value="Oct">Oct</option>
                            <option value="Nov">Nov</option>
                            <option value="Dec">Dec</option>
                        </select>
                    </div>
                    <div class="angel-field-group">
                        <label for="reg-birth-year" class="angel-label">Birth Year</label>
                        <input type="text" id="reg-birth-year" name="birth_year" class="angel-input" placeholder="YYYY" maxlength="4" required>
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