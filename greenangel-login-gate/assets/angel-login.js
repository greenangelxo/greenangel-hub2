jQuery(document).ready(function($) {
    
    // Get user's IP address and check for VPN
    let userIP = '';
    let isVPN = false;
    fetchUserIP();
    
    function fetchUserIP() {
        // Try multiple IP services for reliability
        const ipServices = [
            'https://api.ipify.org?format=json',
            'https://ipapi.co/json/',
            'https://jsonip.com'
        ];
        
        function tryNextService(index = 0) {
            if (index >= ipServices.length) {
                $('#user-ip').text('Unable to detect');
                // If we can't detect IP at all, likely VPN/private network
                isVPN = true;
                if ($('#register-form').is(':visible')) {
                    showVPNWarning();
                }
                return;
            }
            
            $.ajax({
                url: ipServices[index],
                method: 'GET',
                timeout: 3000,
                success: function(data) {
                    let ip = '';
                    if (data.ip) {
                        ip = data.ip;
                        userIP = ip;
                        
                        // Check additional data for VPN indicators from ipapi.co
                        if (index === 1 && data.org) {
                            checkForVPN(data);
                        }
                    } else if (data.IPv4) {
                        ip = data.IPv4;
                        userIP = ip;
                    } else if (typeof data === 'string') {
                        ip = data.replace(/[^0-9.]/g, '');
                        userIP = ip;
                    }
                    
                    if (ip) {
                        $('#user-ip').text(ip);
                        
                        // If we got IP but no VPN check yet, do a secondary check
                        if (index !== 1) {
                            performVPNCheck(ip);
                        }
                    } else {
                        tryNextService(index + 1);
                    }
                },
                error: function() {
                    tryNextService(index + 1);
                }
            });
        }
        
        tryNextService();
    }
    
    function checkForVPN(data) {
        // Check for common VPN indicators
        const vpnIndicators = [
            'vpn', 'proxy', 'hosting', 'datacenter', 'cloud', 'server',
            'digital ocean', 'amazon', 'microsoft', 'google cloud',
            'linode', 'vultr', 'ovh', 'hetzner'
        ];
        
        const org = (data.org || '').toLowerCase();
        const isp = (data.isp || '').toLowerCase();
        
        isVPN = vpnIndicators.some(indicator => 
            org.includes(indicator) || isp.includes(indicator)
        );
        
        if (isVPN) {
            showVPNWarning();
        }
    }
    
    function performVPNCheck(ip) {
        // Additional VPN check using a different service
        $.ajax({
            url: `https://ipapi.co/${ip}/json/`,
            method: 'GET',
            timeout: 5000,
            success: function(data) {
                checkForVPN(data);
            },
            error: function() {
                // Fail silently for VPN check
            }
        });
    }
    
    function showVPNWarning() {
        const registerForm = $('#register-form');
        if (registerForm.is(':visible')) {
            const isUnableToDetect = $('#user-ip').text() === 'Unable to detect';
            const warningHtml = `
                <div class="angel-vpn-warning">
                    <div class="vpn-warning-icon">⚠️</div>
                    <h4>${isUnableToDetect ? 'Private Network Detected' : 'VPN/Proxy Detected'}</h4>
                    <p>${isUnableToDetect ? 
                        'Registration is not available through private networks, VPNs, or proxy connections. Please disconnect and try again with your real IP address.' :
                        'Registration is not available through VPN or proxy connections. Please disconnect and try again with your real IP address.'
                    }</p>
                </div>
            `;
            
            // Add warning before the form
            if (!$('.angel-vpn-warning').length) {
                registerForm.prepend(warningHtml);
                registerForm.find('input, select, button').prop('disabled', true);
                registerForm.find('.angel-form').css('opacity', '0.5');
            }
        }
    }
    
    // Tab switching functionality
    $('.angel-tab').on('click', function() {
        const targetTab = $(this).data('tab');
        
        // Update active tab
        $('.angel-tab').removeClass('active');
        $(this).addClass('active');
        
        // Show/hide forms
        $('.angel-form-container').hide();
        $(`#${targetTab}-form`).show();
        
        // Check for VPN when switching to register tab
        if (targetTab === 'register' && isVPN) {
            showVPNWarning();
        }
        
        // Clear any messages
        clearMessages();
    });
    
    // Password visibility toggle
    $('.angel-password-toggle').on('click', function() {
        const targetId = $(this).data('target');
        const passwordField = $(`#${targetId}`);
        const showIcon = $(this).find('.toggle-show');
        const hideIcon = $(this).find('.toggle-hide');
        
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            showIcon.hide();
            hideIcon.show();
        } else {
            passwordField.attr('type', 'password');
            showIcon.show();
            hideIcon.hide();
        }
    });
    
    // Angel code validation (real-time)
    let codeValidationTimeout;
    $('#reg-angel-code').on('input', function() {
        const code = $(this).val().trim();
        const validationDiv = $('#code-validation');
        
        // Clear previous timeout
        clearTimeout(codeValidationTimeout);
        
        if (code.length < 3) {
            validationDiv.removeClass('valid invalid').empty();
            return;
        }
        
        // Add a small delay to avoid excessive requests
        codeValidationTimeout = setTimeout(function() {
            validateAngelCode(code);
        }, 500);
    });
    
    function validateAngelCode(code) {
        const validationDiv = $('#code-validation');
        
        $.ajax({
            url: angel_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'angel_validate_code',
                code: code,
                nonce: angel_ajax.nonce
            },
            beforeSend: function() {
                validationDiv.removeClass('valid invalid').text('Checking...');
            },
            success: function(response) {
                if (response.success) {
                    validationDiv.removeClass('invalid').addClass('valid').text('✨ Valid angel code!');
                } else {
                    validationDiv.removeClass('valid').addClass('invalid').text(response.data.message);
                }
            },
            error: function() {
                validationDiv.removeClass('valid').addClass('invalid').text('Error checking code');
            }
        });
    }
    
    // Birth year validation (only allow 4 digits)
    $('#reg-birth-year').on('input', function() {
        let value = $(this).val().replace(/\D/g, ''); // Remove non-digits
        if (value.length > 4) {
            value = value.slice(0, 4);
        }
        $(this).val(value);
    });
    
    // Login form submission
    $('#angel-login-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const buttonText = submitBtn.find('.button-text');
        const buttonLoader = submitBtn.find('.button-loader');
        
        // Get form data
        const formData = {
            action: 'angel_login',
            email: form.find('[name="email"]').val(),
            password: form.find('[name="password"]').val(),
            remember: form.find('[name="remember"]').is(':checked'),
            nonce: angel_ajax.nonce
        };
        
        // Show loading state
        submitBtn.prop('disabled', true);
        buttonText.hide();
        buttonLoader.show();
        
        $.ajax({
            url: angel_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showMessage('Welcome back, angel! ✨', 'success');
                    // Redirect after a short delay
                    setTimeout(function() {
                        window.location.href = response.data.redirect;
                    }, 1000);
                } else {
                    showMessage(response.data.message, 'error');
                    resetButton(submitBtn, buttonText, buttonLoader);
                }
            },
            error: function() {
                showMessage('Something went wrong. Please try again.', 'error');
                resetButton(submitBtn, buttonText, buttonLoader);
            }
        });
    });
    
    // Registration form submission
    $('#angel-register-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const buttonText = submitBtn.find('.button-text');
        const buttonLoader = submitBtn.find('.button-loader');
        
        // Validate birth year
        const birthYear = form.find('[name="birth_year"]').val();
        if (birthYear.length !== 4 || isNaN(birthYear)) {
            showMessage('Please enter a valid 4-digit birth year.', 'error');
            return;
        }
        
        // Get form data
        const formData = {
            action: 'angel_register',
            first_name: form.find('[name="first_name"]').val(),
            last_name: form.find('[name="last_name"]').val(),
            email: form.find('[name="email"]').val(),
            password: form.find('[name="password"]').val(),
            angel_code: form.find('[name="angel_code"]').val(),
            birth_month: form.find('[name="birth_month"]').val(),
            birth_year: birthYear,
            newsletter_signup: form.find('[name="newsletter_signup"]').val(),
            nonce: angel_ajax.nonce
        };
        
        // Show loading state
        submitBtn.prop('disabled', true);
        buttonText.hide();
        buttonLoader.show();
        
        $.ajax({
            url: angel_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showMessage('Welcome to the family, angel! ✨', 'success');
                    // Redirect after a short delay
                    setTimeout(function() {
                        window.location.href = response.data.redirect;
                    }, 1500);
                } else {
                    showMessage(response.data.message, 'error');
                    resetButton(submitBtn, buttonText, buttonLoader);
                }
            },
            error: function() {
                showMessage('Something went wrong. Please try again.', 'error');
                resetButton(submitBtn, buttonText, buttonLoader);
            }
        });
    });
    
    // Forgot password link handler
    $('#forgot-password-link').on('click', function(e) {
        e.preventDefault();
        
        // Switch to forgot password view
        $('.angel-auth-container').addClass('forgot-password-mode');
        
        // Hide the tabs and forms
        $('.angel-tabs').hide();
        $('.angel-form-container').hide();
        
        // Create and show forgot password form
        const forgotPasswordHtml = `
            <div id="forgot-password-form" class="angel-form-container">
                <div class="angel-brand">
                    <h1 class="angel-title">Reset Your Password</h1>
                    <p class="angel-subtitle">We'll send you a magic link ✨</p>
                </div>
                
                <form id="angel-forgot-password-form" class="angel-form">
                    <div class="angel-field-group">
                        <label class="angel-label" for="forgot-email">Email</label>
                        <input type="email" id="forgot-email" name="email" class="angel-input" placeholder="your@email.com" required>
                    </div>
                    
                    <button type="submit" class="angel-button">
                        <span class="button-text">Send Reset Link</span>
                        <span class="button-loader" style="display: none;">⏳</span>
                    </button>
                </form>
                
                <div class="angel-links">
                    <a href="#" id="back-to-login" class="angel-link">← Back to login</a>
                </div>
                
                <div id="forgot-messages" class="angel-messages"></div>
            </div>
        `;
        
        $('.angel-auth-wrapper').append(forgotPasswordHtml);
        
        // Focus on email field
        $('#forgot-email').focus();
    });
    
    // Handle back to login
    $(document).on('click', '#back-to-login', function(e) {
        e.preventDefault();
        
        // Remove forgot password mode
        $('.angel-auth-container').removeClass('forgot-password-mode');
        
        // Show tabs and login form
        $('.angel-tabs').show();
        $('#login-form').show();
        
        // Remove forgot password form
        $('#forgot-password-form').remove();
        
        // Clear messages
        clearMessages();
    });
    
    // Handle forgot password form submission
    $(document).on('submit', '#angel-forgot-password-form', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const buttonText = submitBtn.find('.button-text');
        const buttonLoader = submitBtn.find('.button-loader');
        
        const formData = {
            action: 'angel_forgot_password',
            email: form.find('[name="email"]').val(),
            nonce: angel_ajax.nonce
        };
        
        // Show loading state
        submitBtn.prop('disabled', true);
        buttonText.hide();
        buttonLoader.show();
        
        $.ajax({
            url: angel_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showForgotMessage(response.data.message, 'success');
                    form[0].reset();
                } else {
                    showForgotMessage(response.data.message, 'error');
                }
                resetButton(submitBtn, buttonText, buttonLoader);
            },
            error: function() {
                showForgotMessage('Something went wrong. Please try again.', 'error');
                resetButton(submitBtn, buttonText, buttonLoader);
            }
        });
    });
    
    // Helper function for forgot password messages
    function showForgotMessage(message, type) {
        const messagesContainer = $('#forgot-messages');
        const messageDiv = $(`<div class="angel-message ${type}">${message}</div>`);
        
        messagesContainer.empty().append(messageDiv);
        
        if (type === 'success') {
            setTimeout(function() {
                messageDiv.fadeOut();
            }, 5000);
        }
    }
    
    // Handle password reset form (when user clicks the reset link)
    if (window.location.search.includes('action=rp')) {
        // Password reset form submission
        $('#angel-reset-password-form').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const buttonText = submitBtn.find('.button-text');
            const buttonLoader = submitBtn.find('.button-loader');
            
            // Get URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            
            const formData = {
                action: 'angel_reset_password',
                key: urlParams.get('key'),
                login: urlParams.get('login'),
                password: form.find('[name="password"]').val(),
                password_confirm: form.find('[name="password_confirm"]').val(),
                nonce: angel_ajax.nonce
            };
            
            // Show loading state
            submitBtn.prop('disabled', true);
            buttonText.hide();
            buttonLoader.show();
            
            $.ajax({
                url: angel_ajax.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        showMessage(response.data.message, 'success');
                        addSparkles();
                        
                        // Redirect after a short delay
                        setTimeout(function() {
                            window.location.href = response.data.redirect;
                        }, 1500);
                    } else {
                        showMessage(response.data.message, 'error');
                        resetButton(submitBtn, buttonText, buttonLoader);
                    }
                },
                error: function() {
                    showMessage('Something went wrong. Please try again.', 'error');
                    resetButton(submitBtn, buttonText, buttonLoader);
                }
            });
        });
        
        // Password visibility toggle for reset form
        $('.reset-password-toggle').on('click', function() {
            const targetId = $(this).data('target');
            const passwordField = $(`#${targetId}`);
            const showIcon = $(this).find('.toggle-show');
            const hideIcon = $(this).find('.toggle-hide');
            
            if (passwordField.attr('type') === 'password') {
                passwordField.attr('type', 'text');
                showIcon.hide();
                hideIcon.show();
            } else {
                passwordField.attr('type', 'password');
                showIcon.show();
                hideIcon.hide();
            }
        });
    }
    
    // Helper functions
    function showMessage(message, type) {
        const messagesContainer = $('#angel-messages');
        const messageDiv = $(`<div class="angel-message ${type}">${message}</div>`);
        
        messagesContainer.empty().append(messageDiv);
        
        // Auto-hide success messages
        if (type === 'success') {
            setTimeout(function() {
                messageDiv.fadeOut();
            }, 3000);
        }
    }
    
    function clearMessages() {
        $('#angel-messages').empty();
    }
    
    function resetButton(button, textEl, loaderEl) {
        button.prop('disabled', false);
        textEl.show();
        loaderEl.hide();
    }
    
    // Add some sparkle effects on successful actions
    function addSparkles() {
        const container = $('.angel-auth-container');
        for (let i = 0; i < 5; i++) {
            const sparkle = $('<div class="sparkle">✨</div>');
            sparkle.css({
                position: 'absolute',
                top: Math.random() * container.height() + 'px',
                left: Math.random() * container.width() + 'px',
                animation: 'sparkle 2s ease-out forwards',
                pointerEvents: 'none',
                zIndex: 1000
            });
            container.append(sparkle);
            
            setTimeout(function() {
                sparkle.remove();
            }, 2000);
        }
    }
    
    // Add sparkle animation to CSS dynamically
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            @keyframes sparkle {
                0% { opacity: 1; transform: scale(0) rotate(0deg); }
                50% { opacity: 1; transform: scale(1) rotate(180deg); }
                100% { opacity: 0; transform: scale(0) rotate(360deg); }
            }
        `)
        .appendTo('head');
    
    // Trigger sparkles on successful form submissions
    $(document).on('ajaxSuccess', function(event, xhr, settings) {
        if (settings.data && settings.data.includes('angel_login') || settings.data.includes('angel_register')) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                addSparkles();
            }
        }
    });
    
});