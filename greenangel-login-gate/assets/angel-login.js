jQuery(document).ready(function($) {
    
    // 🛡️ BASIC ERROR PREVENTION
    try {
        // Stop any existing animations that might be causing conflicts
        $('*').stop(true, true);
    } catch (e) {
        console.warn('Animation cleanup failed:', e);
    }

    // Nonces from forms
    const loginNonce = $('#angel-login-form input[name="angel_login_nonce_login"]').val() || angel_ajax.nonce;
    const registerNonce = $('#angel-register-form input[name="angel_register_nonce"]').val() || angel_ajax.nonce;
    const resetNonce = $('#angel-reset-password-form input[name="angel_login_nonce"]').val() || loginNonce;

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
                    // Silently try next service - no popup for this
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
    
    // 🚀 COSMIC LOADING OVERLAY SYSTEM
    function createCosmicLoadingOverlay(messages, isRegistration = false) {
        const overlay = $(`
            <div class="cosmic-loading-overlay">
                <div class="cosmic-background">
                    <div class="cosmic-particles"></div>
                    <div class="cosmic-led-strip"></div>
                </div>
                <div class="cosmic-loading-content">
                    <div class="cosmic-logo">
                        <div class="cosmic-logo-ring"></div>
                        <div class="cosmic-logo-center">✨</div>
                    </div>
                    <div class="cosmic-loading-text">
                        <div class="cosmic-main-text">Connecting to the Angel Network...</div>
                        <div class="cosmic-sub-text">Please wait while we work our magic</div>
                    </div>
                    <div class="cosmic-progress-bar">
                        <div class="cosmic-progress-fill"></div>
                        <div class="cosmic-progress-glow"></div>
                    </div>
                </div>
            </div>
        `);
        
        $('body').append(overlay);
        
        // Initialize particle system
        createCosmicParticles();
        
        // Start the loading sequence
        startCosmicLoadingSequence(messages, isRegistration);
        
        return overlay;
    }
    
    // 🌌 COSMIC PARTICLE SYSTEM
    function createCosmicParticles() {
        const particleContainer = $('.cosmic-particles');
        const particles = ['✨', '🌟', '💫', '⭐', '💎', '🔮'];
        
        for (let i = 0; i < 25; i++) {
            const particle = $(`<div class="cosmic-particle">${particles[Math.floor(Math.random() * particles.length)]}</div>`);
            
            particle.css({
                left: Math.random() * 100 + '%',
                top: Math.random() * 100 + '%',
                animationDelay: Math.random() * 5 + 's',
                animationDuration: (3 + Math.random() * 4) + 's'
            });
            
            particleContainer.append(particle);
        }
    }
    
    // 🎭 COSMIC LOADING SEQUENCE
    function startCosmicLoadingSequence(messages, isRegistration) {
        const mainText = $('.cosmic-main-text');
        const subText = $('.cosmic-sub-text');
        const progressFill = $('.cosmic-progress-fill');
        const logo = $('.cosmic-logo-center');
        
        let currentStep = 0;
        let progress = 0;
        
        // Animate progress and messages
        const interval = setInterval(() => {
            progress += Math.random() * 15 + 5; // Random progress increments
            
            if (progress > 100) progress = 100;
            
            progressFill.css('width', progress + '%');
            
            // Update messages based on progress
            if (progress > 20 && currentStep === 0) {
                mainText.fadeOut(300, () => {
                    mainText.text(messages[1] || 'Verifying your cosmic signature...').fadeIn(300);
                });
                currentStep = 1;
            } else if (progress > 50 && currentStep === 1) {
                mainText.fadeOut(300, () => {
                    mainText.text(messages[2] || 'Loading your diamond profile...').fadeIn(300);
                });
                logo.text('💎');
                currentStep = 2;
            } else if (progress > 80 && currentStep === 2) {
                mainText.fadeOut(300, () => {
                    mainText.text(messages[3] || 'Preparing your magical gateway...').fadeIn(300);
                });
                logo.text('🌟');
                currentStep = 3;
            }
            
            if (progress >= 100) {
                clearInterval(interval);
                // Complete the loading sequence
                setTimeout(() => {
                    completeCosmicLoading(isRegistration);
                }, 800);
            }
        }, 150);
    }
    
    // 🎉 COSMIC LOADING COMPLETION
    function completeCosmicLoading(isRegistration) {
        const overlay = $('.cosmic-loading-overlay');
        const content = $('.cosmic-loading-content');
        const mainText = $('.cosmic-main-text');
        const subText = $('.cosmic-sub-text');
        const logo = $('.cosmic-logo-center');
        
        // Success transformation
        logo.text('🎉').addClass('cosmic-success-pulse');
        
        if (isRegistration) {
            mainText.text('Welcome to the Green Angel universe!');
            subText.text('Your magical journey begins now ✨');
        } else {
            mainText.text('Welcome home, angel!');
            subText.text('Your magic awaits ✨');
        }
        
        // Create success explosion
        createSuccessExplosion();
        
        // Store the overlay reference for the success callback
        window.currentCosmicOverlay = overlay;
    }
    
    // 💥 SUCCESS EXPLOSION EFFECT
    function createSuccessExplosion() {
        const particles = ['✨', '🌟', '💫', '⭐', '🎉', '🎊', '💎', '🔮'];
        const center = $('.cosmic-logo');
        
        for (let i = 0; i < 20; i++) {
            const particle = $(`<div class="success-particle">${particles[Math.floor(Math.random() * particles.length)]}</div>`);
            
            const angle = (Math.PI * 2 * i) / 20;
            const distance = 150 + Math.random() * 100;
            const endX = Math.cos(angle) * distance;
            const endY = Math.sin(angle) * distance;
            
            particle.css({
                position: 'absolute',
                left: '50%',
                top: '50%',
                transform: 'translate(-50%, -50%)',
                fontSize: (1 + Math.random()) + 'rem',
                zIndex: 1001
            });
            
            center.append(particle);
            
            particle.animate({
                left: `calc(50% + ${endX}px)`,
                top: `calc(50% + ${endY}px)`,
                opacity: 0,
                fontSize: '0.5rem'
            }, 2000, function() {
                particle.remove();
            });
        }
    }
    
    // 🌈 FADE OUT COSMIC OVERLAY - SMOOTH TRANSITION TO NEW PAGE
    function fadeOutCosmicOverlay(redirectUrl) {
        const overlay = $('.cosmic-loading-overlay');
        
        if (redirectUrl) {
            // Navigate immediately but keep overlay visible
            window.location.href = redirectUrl;
            
            // Keep overlay visible during navigation
            // The overlay will naturally disappear when the new page loads
            setTimeout(() => {
                // Fallback cleanup in case navigation fails
                if (overlay.length) {
                    overlay.addClass('cosmic-fade-out');
                    setTimeout(() => overlay.remove(), 1000);
                }
            }, 5000); // Only cleanup if navigation takes too long
        } else {
            // Standard fade out if no redirect
            overlay.addClass('cosmic-fade-out');
            setTimeout(() => overlay.remove(), 1000);
        }
    }
    
    // 🎮 ENHANCED TAB SWITCHING WITH LED EFFECTS - SAFE VERSION
    $('.angel-tab').on('click', function() {
        try {
            const targetTab = $(this).data('tab');
            
            // Update active tab with smooth transition
            $('.angel-tab').removeClass('active');
            $(this).addClass('active');
            
            // Show/hide forms with safe animation
            $('.angel-form-container').hide();
            const targetForm = $(`#${targetTab}-form`);
            
            if (targetForm.length) {
                targetForm.show().css({
                    opacity: 0,
                    transform: 'translateY(20px)'
                });
                
                // Use safe animation
                setTimeout(() => {
                    targetForm.css({
                        opacity: 1,
                        transform: 'translateY(0)',
                        transition: 'all 0.3s ease'
                    });
                }, 50);
            }
            
            // Check for VPN when switching to register tab
            if (targetTab === 'register' && isVPN) {
                showVPNWarning();
            }
            
            // Clear any messages
            clearMessages();
            
            // Clear dice session if switching away from registration
            if (targetTab === 'login') {
                sessionStorage.removeItem('angelDiceGame');
            }
            
        } catch (e) {
            console.error('Tab switching error:', e);
        }
    });
    
    // 💫 ENHANCED PASSWORD VISIBILITY TOGGLE
    $('.angel-password-toggle').on('click', function() {
        const targetId = $(this).data('target');
        const passwordField = $(`#${targetId}`);
        const showIcon = $(this).find('.toggle-show');
        const hideIcon = $(this).find('.toggle-hide');
        
        // Add click animation
        $(this).css('transform', 'scale(0.9)');
        setTimeout(() => {
            $(this).css('transform', 'scale(1)');
        }, 100);
        
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
    
    // ✨ ENHANCED ANGEL CODE VALIDATION WITH LED FEEDBACK
    let codeValidationTimeout;
    $('#reg-angel-code').on('input', function() {
        const code = $(this).val().trim();
        const validationDiv = $('#code-validation');
        const inputField = $(this);
        
        // Clear previous timeout
        clearTimeout(codeValidationTimeout);
        
        // Remove any existing border effects
        inputField.removeClass('code-checking code-valid code-invalid');
        
        // Don't check if code is too short - wait until they've typed more
        if (code.length < 6) {
            validationDiv.removeClass('valid invalid').empty();
            return;
        }
        
        // Add checking state with LED effect
        inputField.addClass('code-checking');
        validationDiv.removeClass('valid invalid').html('<span style="opacity: 0.6;">🔍 Checking...</span>');
        
        // Wait 1 second after they stop typing before checking
        codeValidationTimeout = setTimeout(function() {
            validateAngelCode(code);
        }, 1000);
    });
    
    function validateAngelCode(code) {
        const validationDiv = $('#code-validation');
        const inputField = $('#reg-angel-code');
        
        $.ajax({
            url: angel_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'angel_validate_code',
                code: code,
                nonce: angel_ajax.nonce
            },
            beforeSend: function() {
                validationDiv.removeClass('valid invalid').html('<span style="opacity: 0.7;">⚡ Validating...</span>');
            },
            success: function(response) {
                inputField.removeClass('code-checking');
                if (response.success) {
                    inputField.addClass('code-valid');
                    validationDiv.removeClass('invalid').addClass('valid').html('✨ Valid angel code!');
                    
                    // Success pulse effect
                    inputField.css({
                        'box-shadow': '0 0 20px rgba(144, 238, 144, 0.3)',
                        'border-color': 'rgba(144, 238, 144, 0.5)'
                    });
                    setTimeout(() => {
                        inputField.css({
                            'box-shadow': '',
                            'border-color': ''
                        });
                    }, 2000);
                } else {
                    inputField.addClass('code-invalid');
                    validationDiv.removeClass('valid').addClass('invalid').html('❌ ' + (response.data.message || 'Invalid angel code.'));
                    
                    // Error shake effect
                    inputField.css('animation', 'shake 0.5s ease-in-out');
                    setTimeout(() => {
                        inputField.css('animation', '');
                    }, 500);
                }
            },
            error: function(xhr, status, error) {
                console.error('Validation error:', error);
                console.error('Response:', xhr.responseText);
                inputField.removeClass('code-checking').addClass('code-invalid');
                validationDiv.removeClass('valid').addClass('invalid').text('⚠️ Error checking code');
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
    
    // 🚀 ENHANCED LOGIN FORM SUBMISSION WITH COSMIC MAGIC
    $('#angel-login-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const formData = {
            action: 'angel_login',
            email: form.find('[name="email"]').val(),
            password: form.find('[name="password"]').val(),
            remember: form.find('[name="remember"]').is(':checked'),
            nonce: loginNonce
        };
        
        // Create cosmic loading overlay
        const loadingMessages = [
            'Connecting to the Angel Network...',
            'Verifying your cosmic signature...',
            'Loading your diamond profile...',
            'Welcome home, angel! ✨'
        ];
        
        createCosmicLoadingOverlay(loadingMessages, false);
        
        // Simulate realistic loading time
        setTimeout(() => {
            $.ajax({
                url: angel_ajax.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // Let the cosmic loading complete
                        setTimeout(() => {
                            // Additional celebration before redirect
                            setTimeout(() => {
                                // Navigate directly without fading overlay first
                                fadeOutCosmicOverlay(response.data.redirect);
                            }, 1500);
                        }, 1000);
                    } else {
                        // Remove overlay and show error
                        $('.cosmic-loading-overlay').remove();
                        showMessage(response.data.message, 'error');
                    }
                },
                error: function() {
                    $('.cosmic-loading-overlay').remove();
                    showMessage('Something went wrong. Please try again.', 'error');
                }
            });
        }, 2000); // Minimum loading time for effect
    });
    
    // 🎲 ENHANCED DISPLAY NAME DICE GAME WITH LED EFFECTS
    let rollsLeft = 5;
    let selectedName = '';

    // Check if we have existing session data - with defensive checks
    const sessionKey = 'angelDiceSession_' + Date.now().toString(36);
    let diceSession = null;
    
    try {
        diceSession = sessionStorage.getItem('angelDiceGame');
    } catch (error) {
        console.warn('⚠️ Cannot access sessionStorage:', error);
    }

    if (diceSession) {
        try {
            const sessionData = JSON.parse(diceSession);
            
            // Validate session data structure
            if (sessionData && typeof sessionData === 'object') {
                // Safely restore rollsLeft with fallback
                if (typeof sessionData.rollsLeft === 'number' && sessionData.rollsLeft >= 0) {
                    rollsLeft = sessionData.rollsLeft;
                }
                
                // Safely restore selectedName with validation
                if (typeof sessionData.selectedName === 'string' && sessionData.selectedName.length > 0) {
                    selectedName = sessionData.selectedName;
                }
                
                // Update UI if we have a selected name
                if (selectedName) {
                    const displayDiv = $('#dice-display-name');
                    const nameText = displayDiv.find('.dice-name-text');
                    const hiddenInput = $('#reg-display-name-hidden');
                    
                    if (displayDiv.length > 0) {
                        displayDiv.addClass('has-name');
                    }
                    
                    if (nameText.length > 0) {
                        nameText.text(selectedName);
                    }
                    
                    if (hiddenInput.length > 0) {
                        hiddenInput.val(selectedName);
                    }
                }
                
                // Update button state
                updateDiceButton();
            } else {
                console.warn('⚠️ Invalid session data structure');
                sessionStorage.removeItem('angelDiceGame');
            }
        } catch (e) {
            console.warn('⚠️ Error parsing dice session data:', e);
            sessionStorage.removeItem('angelDiceGame');
        }
    }

    // Enhanced Stoner Angel Name Arrays
    const emotions = [
        'Happy', 'Giggly', 'Blissful', 'Chill', 'Mellow', 'Peaceful',
        'Groovy', 'Jazzy', 'Funky', 'Bouncy', 'Bubbly', 'Jolly',
        'Sleepy', 'Dreamy', 'Drowsy', 'Cozy', 'Lazy', 'Snoozy',
        'Silly', 'Goofy', 'Wonky', 'Wiggly', 'Dizzy', 'Loopy',
        'Trippy', 'Spacey', 'Zesty', 'Quirky', 'Wacky', 'Derpy',
        'Blazed', 'Baked', 'Fried', 'Toasted', 'Roasted', 'Cooked',
        'Lit', 'Faded', 'Zonked', 'Blitzed', 'Ripped', 'Lifted',
        'Cosmic', 'Mystic', 'Angelic', 'Divine', 'Blessed', 'Sacred',
        'Magical', 'Sparkly', 'Twinkly', 'Glittery', 'Shimmery', 'Starry',
        'Hungry', 'Munchy', 'Snacky', 'Crispy', 'Crunchy', 'Chewy',
        'Fluffy', 'Squishy', 'Cuddly', 'Fuzzy', 'Soft', 'Gentle',
        'Cloudy', 'Misty', 'Smoky', 'Steamy', 'Vapory', 'Airy',
        'Wild', 'Crazy', 'Smooth', 'Silky', 'Sassy', 'Cheeky'
    ];

    const cannabisWords = [
        'Stoner', 'Blazer', 'Toker', 'Smoker', 'Puffer', 'Roller',
        'Bud', 'Nug', 'Flower', 'Herb', 'Leaf', 'Green',
        'Ganja', 'Mary', 'Jane', 'Chronic', 'Dank', 'Loud',
        'Joint', 'Blunt', 'Spliff', 'Doobie', 'Fatty', 'Cone',
        'Bowl', 'Bong', 'Pipe', 'Rig', 'Vape', 'Edible',
        'Kush', 'Haze', 'Diesel', 'Cookie', 'Cake', 'Widow',
        'Hash', 'Keef', 'Wax', 'Shatter', 'Rosin', 'Dab',
        'Munchie', 'Sesh', 'Wake', 'Bake', 'Puff', 'Toke',
        'THC', 'CBD', 'Terp', 'Grinder', 'Papers', 'Stash'
    ];

    // Generate random name with defensive checks
    function generateRandomName() {
        try {
            // Defensive checks for arrays
            if (!emotions || !Array.isArray(emotions) || emotions.length === 0) {
                console.warn('⚠️ Emotions array is not available, using fallback');
                return 'MagicalStoner';
            }
            
            if (!cannabisWords || !Array.isArray(cannabisWords) || cannabisWords.length === 0) {
                console.warn('⚠️ Cannabis words array is not available, using fallback');
                return 'HappyToker';
            }
            
            const emotion = emotions[Math.floor(Math.random() * emotions.length)];
            const weedWord = cannabisWords[Math.floor(Math.random() * cannabisWords.length)];
            return emotion + weedWord;
        } catch (error) {
            console.error('❌ Error generating random name:', error);
            // Return a safe fallback name
            return 'CosmicAngel';
        }
    }

    // Update dice button state with LED effects - with defensive checks
    function updateDiceButton() {
        try {
            const btn = $('#roll-dice-btn');
            
            // Check if button exists
            if (!btn || btn.length === 0) {
                console.warn('⚠️ Dice button not found in DOM');
                return;
            }
            
            const countSpan = btn.find('.dice-count');
            
            if (rollsLeft <= 0) {
                btn.addClass('exhausted');
                btn.find('.dice-text').text('No More Rolls!');
                
                // Check if count span exists before updating
                if (countSpan.length > 0) {
                    countSpan.text('Name locked in');
                }
                
                btn.prop('disabled', true);
                
                // Add exhausted LED effect
                btn.css({
                    'background': 'linear-gradient(135deg, #333333 0%, #444444 100%)',
                    'border-color': 'rgba(255, 255, 255, 0.1)'
                });
            } else {
                // Check if count span exists before updating
                if (countSpan.length > 0) {
                    countSpan.text(rollsLeft + ' rolls left');
                }
            }
        } catch (error) {
            console.error('❌ Error updating dice button:', error);
        }
    }

    // Save session data - with defensive checks
    function saveSession() {
        try {
            const sessionData = {
                rollsLeft: rollsLeft,
                selectedName: selectedName,
                timestamp: Date.now()
            };
            
            // Validate sessionData before saving
            if (sessionData && typeof sessionData === 'object') {
                sessionStorage.setItem('angelDiceGame', JSON.stringify(sessionData));
            } else {
                console.warn('⚠️ Invalid session data, not saving');
            }
        } catch (error) {
            console.warn('⚠️ Error saving dice session:', error);
        }
    }

    // 🎲 ENHANCED DICE ROLL WITH LED EFFECTS - WITH DEFENSIVE CHECKS
    $('#roll-dice-btn').on('click', function() {
        try {
            if (rollsLeft <= 0) return;
            
            const btn = $(this);
            const displayDiv = $('#dice-display-name');
            const nameText = displayDiv.find('.dice-name-text');
            const countSpan = btn.find('.dice-count');
            
            // Check if required DOM elements exist
            if (!btn || btn.length === 0) {
                console.warn('⚠️ Dice button not found');
                return;
            }
            
            if (!displayDiv || displayDiv.length === 0) {
                console.warn('⚠️ Dice display div not found');
                return;
            }
            
            if (!nameText || nameText.length === 0) {
                console.warn('⚠️ Dice name text element not found');
                return;
            }
            
            // Enhanced rolling animation with LED effects
            btn.addClass('rolling');
            displayDiv.addClass('rolling');
            
            // Add LED pulse effect during roll
            btn.css({
                'box-shadow': '0 0 30px rgba(255, 255, 255, 0.2)',
                'border-color': 'rgba(255, 255, 255, 0.4)'
            });
            
            // Simulate rolling effect with enhanced visuals
            let rollCount = 0;
            const rollInterval = setInterval(function() {
                try {
                    const tempName = generateRandomName();
                    nameText.text(tempName);
                    rollCount++;
                    
                    // Add flicker effect during rolling
                    displayDiv.css('opacity', 0.7 + Math.random() * 0.3);
                    
                    if (rollCount >= 12) { // Longer roll animation
                        clearInterval(rollInterval);
                        
                        // Generate final name
                        selectedName = generateRandomName();
                        nameText.text(selectedName);
                        
                        // Check if hidden input exists before setting value
                        const hiddenInput = $('#reg-display-name-hidden');
                        if (hiddenInput.length > 0) {
                            hiddenInput.val(selectedName);
                        }
                        
                        // Enhanced success animation
                        displayDiv.removeClass('rolling').addClass('has-name selected');
                        displayDiv.css({
                            'opacity': 1,
                            'border-color': 'rgba(255, 255, 255, 0.3)',
                            'box-shadow': '0 0 20px rgba(255, 255, 255, 0.1)'
                        });
                        
                        btn.removeClass('rolling');
                        btn.css({
                            'box-shadow': '',
                            'border-color': ''
                        });
                        
                        // Decrement rolls
                        rollsLeft--;
                        
                        // Save to session
                        saveSession();
                        
                        // Update button
                        updateDiceButton();
                        
                        // Enhanced flash effect
                        setTimeout(function() {
                            displayDiv.removeClass('selected');
                            displayDiv.css({
                                'box-shadow': '',
                                'border-color': 'rgba(255, 255, 255, 0.1)'
                            });
                        }, 500);
                        
                        // Success particles effect
                        createDiceSuccessEffect();
                    }
                } catch (error) {
                    console.error('❌ Error in dice roll animation:', error);
                    clearInterval(rollInterval);
                    btn.removeClass('rolling');
                    displayDiv.removeClass('rolling');
                }
            }, 80); // Slower for more dramatic effect
            
        } catch (error) {
            console.error('❌ Error in dice button click handler:', error);
        }
    });

    // 🎉 DICE SUCCESS PARTICLE EFFECT - WITH DEFENSIVE CHECKS
    function createDiceSuccessEffect() {
        try {
            const particles = ['✨', '🌟', '💫', '⭐'];
            const diceButton = $('#roll-dice-btn');
            
            // Check if particles array exists and has content
            if (!particles || !Array.isArray(particles) || particles.length === 0) {
                console.warn('⚠️ Particles array not available for dice success effect');
                return;
            }
            
            // Check if dice button exists
            if (!diceButton || diceButton.length === 0) {
                console.warn('⚠️ Dice button not found for success effect');
                return;
            }
            
            // Check if getBoundingClientRect is available
            if (!diceButton[0] || typeof diceButton[0].getBoundingClientRect !== 'function') {
                console.warn('⚠️ Cannot get dice button position for success effect');
                return;
            }
            
            const rect = diceButton[0].getBoundingClientRect();
            
            for (let i = 0; i < 6; i++) {
                const particle = $('<div>');
                particle.text(particles[Math.floor(Math.random() * particles.length)]);
                
                // Random direction
                const angle = (Math.PI * 2 * i) / 6;
                const distance = 40 + Math.random() * 30;
                const endX = Math.cos(angle) * distance;
                const endY = Math.sin(angle) * distance;
                
                particle.css({
                    'position': 'fixed',
                    'left': rect.left + rect.width / 2 + 'px',
                    'top': rect.top + rect.height / 2 + 'px',
                    'font-size': '1.2rem',
                    'pointer-events': 'none',
                    'z-index': '9999',
                    'color': 'rgba(255, 255, 255, 0.8)',
                    'text-shadow': '0 0 10px rgba(255, 255, 255, 0.5)',
                    'transition': 'all 1.5s ease-out',
                    'transform': 'translate(-50%, -50%)'
                });
                
                $('body').append(particle);
                
                // Use CSS transitions instead of jQuery animation
                setTimeout(() => {
                    particle.css({
                        'transform': `translate(calc(-50% + ${endX}px), calc(-50% + ${endY}px)) scale(0.5)`,
                        'opacity': '0',
                        'font-size': '0.8rem'
                    });
                }, 50);
                
                // Clean up after animation completes
                setTimeout(() => {
                    particle.remove();
                }, 1600);
            }
        } catch (error) {
            console.error('❌ Error creating dice success effect:', error);
        }
    }

    // Clear session when form is successfully submitted
    $(document).on('angelRegistrationSuccess', function() {
        sessionStorage.removeItem('angelDiceGame');
    });
    
    // 🌟 ENHANCED REGISTRATION FORM SUBMISSION WITH COSMIC MAGIC
    $('#angel-register-form').off('submit').on('submit', function(e) {
        e.preventDefault();
        console.log('🌟 Registration form submitted - starting cosmic magic!');
        
        const form = $(this);
        
        // Check if display name was selected
        if (!selectedName || selectedName === '') {
            selectedName = generateRandomName();
            $('#reg-display-name-hidden').val(selectedName);
            saveSession();
        }
        
        // Validate birth year
        const birthYear = form.find('[name="birth_year"]').val();
        if (birthYear.length !== 4 || isNaN(birthYear)) {
            showMessage('Please enter a valid 4-digit birth year.', 'error');
            return;
        }
        
        // Enhanced form validation
        const angelCode = form.find('[name="angel_code"]').val().trim();
        const email = form.find('[name="email"]').val().trim();
        const password = form.find('[name="password"]').val();
        const firstName = form.find('[name="first_name"]').val().trim();
        const lastName = form.find('[name="last_name"]').val().trim();
        const birthMonth = form.find('[name="birth_month"]').val();
        
        if (!angelCode || !email || !password || !firstName || !lastName || !birthMonth) {
            showMessage('Please fill in all required fields.', 'error');
            return;
        }
        
        // Get form data with proper nonce handling
        const formData = {
            action: 'angel_register',
            first_name: firstName,
            last_name: lastName,
            email: email,
            password: password,
            angel_code: angelCode,
            birth_month: birthMonth,
            birth_year: birthYear,
            newsletter_signup: form.find('[name="newsletter_signup"]').val() || '1',
            display_name: selectedName || generateRandomName(),
            nonce: registerNonce || angel_ajax.nonce
        };
        
        console.log('📦 Registration form data:', formData);
        
        // Create cosmic loading overlay for registration
        const registrationMessages = [
            'Creating your Angel identity...',
            'Mixing the magical recipe...',
            'Preparing your cosmic profile...',
            'Welcome to the Green Angel universe! ✨'
        ];
        
        createCosmicLoadingOverlay(registrationMessages, true);
        
        // Make AJAX request immediately (no artificial delay that could cause issues)
        $.ajax({
            url: angel_ajax.ajax_url,
            type: 'POST',
            data: formData,
            timeout: 30000, // 30 second timeout
            beforeSend: function(xhr) {
                console.log('🚀 Sending registration request...');
            },
            success: function(response) {
                console.log('✅ Registration response received:', response);
                
                if (response.success) {
                    console.log('🎉 Registration successful!');
                    
                    // Clear session on success
                    sessionStorage.removeItem('angelDiceGame');
                    $(document).trigger('angelRegistrationSuccess');
                    
                    // Let the cosmic loading complete with proper timing
                    setTimeout(() => {
                        console.log('🌟 Starting success celebration...');
                        // Extended celebration for new users
                        setTimeout(() => {
                            console.log('🚀 Redirecting to:', response.data.redirect);
                            // Navigate directly without fading overlay first
                            fadeOutCosmicOverlay(response.data.redirect);
                        }, 2000); // Slightly shorter to ensure it works
                    }, 1500);
                } else {
                    console.error('❌ Registration failed:', response.data);
                    // Remove overlay and show error
                    $('.cosmic-loading-overlay').remove();
                    showMessage(response.data.message || 'Registration failed. Please try again.', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Registration AJAX error:', status, error);
                console.error('❌ Response:', xhr.responseText);
                
                $('.cosmic-loading-overlay').remove();
                
                let errorMessage = 'Something went wrong. Please try again.';
                if (status === 'timeout') {
                    errorMessage = 'Request timed out. Please check your connection and try again.';
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.data && response.data.message) {
                            errorMessage = response.data.message;
                        }
                    } catch (e) {
                        // Use default error message
                    }
                }
                
                showMessage(errorMessage, 'error');
            }
        });
    });
    
    // 🔒 ENHANCED FORGOT PASSWORD FUNCTIONALITY - SAFE VERSION
    $('#forgot-password-link').on('click', function(e) {
        e.preventDefault();
        
        try {
            // Enhanced transition effect
            $('.angel-auth-container').addClass('forgot-password-mode');
            $('.angel-tabs, .angel-form-container').fadeOut(300);
            
            setTimeout(() => {
                const forgotPasswordHtml = `
                    <div id="forgot-password-form" class="angel-form-container" style="opacity: 0;">
                        <div class="angel-brand">
                            <h1 class="angel-title">Reset Your Password</h1>
                            <p class="angel-subtitle">We'll send you a magic link ✨</p>
                        </div>

                        <form id="angel-forgot-password-form" class="angel-form">
                            <input type="hidden" name="angel_login_nonce" value="${loginNonce}">
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
                
                // Safe CSS animation
                const forgotForm = $('#forgot-password-form');
                forgotForm.css({
                    opacity: 0,
                    transition: 'opacity 0.3s ease'
                });
                
                setTimeout(() => {
                    forgotForm.css('opacity', 1);
                    $('#forgot-email').focus();
                }, 50);
                
            }, 300);
        } catch (e) {
            console.error('Forgot password error:', e);
        }
    });
    
    // Handle back to login with enhanced animation - SAFE VERSION
    $(document).on('click', '#back-to-login', function(e) {
        e.preventDefault();
        
        try {
            const forgotForm = $('#forgot-password-form');
            forgotForm.css({
                opacity: 0,
                transition: 'opacity 0.3s ease'
            });
            
            setTimeout(() => {
                forgotForm.remove();
                $('.angel-auth-container').removeClass('forgot-password-mode');
                $('.angel-tabs, #login-form').fadeIn(300);
                clearMessages();
            }, 300);
        } catch (e) {
            console.error('Back to login error:', e);
        }
    });
    
    // Enhanced forgot password form submission
    $(document).on('submit', '#angel-forgot-password-form', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const buttonText = submitBtn.find('.button-text');
        const buttonLoader = submitBtn.find('.button-loader');
        
        const formData = {
            action: 'angel_forgot_password',
            email: form.find('[name="email"]').val(),
            nonce: form.find('[name="angel_login_nonce"]').val()
        };
        
        // Enhanced loading state
        submitBtn.prop('disabled', true).addClass('loading');
        buttonText.hide();
        buttonLoader.show();
        
        submitBtn.css({
            'border-color': 'rgba(255, 255, 255, 0.4)',
            'box-shadow': '0 0 20px rgba(255, 255, 255, 0.1)'
        });
        
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
                resetButtonEnhanced(submitBtn, buttonText, buttonLoader);
            },
            error: function() {
                showForgotMessage('Something went wrong. Please try again.', 'error');
                resetButtonEnhanced(submitBtn, buttonText, buttonLoader);
            }
        });
    });
    
    // Enhanced helper functions
    function showMessage(message, type) {
        const messagesContainer = $('#angel-messages');
        const messageDiv = $(`<div class="angel-message ${type}" style="opacity: 0; transform: translateY(-10px);">${message}</div>`);
        
        messagesContainer.empty().append(messageDiv);
        
        // Animate in
        messageDiv.animate({
            opacity: 1,
            transform: 'translateY(0)'
        }, 300);
        
        // Auto-hide success messages with enhanced animation
        if (type === 'success') {
            setTimeout(function() {
                messageDiv.animate({
                    opacity: 0,
                    transform: 'translateY(-10px)'
                }, 300, function() {
                    messageDiv.remove();
                });
            }, 3000);
        }
    }
    
    function showForgotMessage(message, type) {
        const messagesContainer = $('#forgot-messages');
        const messageDiv = $(`<div class="angel-message ${type}" style="opacity: 0; transform: translateY(-10px);">${message}</div>`);
        
        messagesContainer.empty().append(messageDiv);
        
        messageDiv.animate({
            opacity: 1,
            transform: 'translateY(0)'
        }, 300);
        
        if (type === 'success') {
            setTimeout(function() {
                messageDiv.animate({
                    opacity: 0,
                    transform: 'translateY(-10px)'
                }, 300, function() {
                    messageDiv.remove();
                });
            }, 5000);
        }
    }
    
    function clearMessages() {
        $('.angel-messages').empty();
    }
    
    function resetButtonEnhanced(button, textEl, loaderEl) {
        button.prop('disabled', false).removeClass('loading');
        textEl.show();
        loaderEl.hide();
        button.css({
            'border-color': '',
            'box-shadow': '',
            'background': ''
        });
    }
    
    // 🎨 ADD DYNAMIC CSS FOR ENHANCED ANIMATIONS INCLUDING COSMIC LOADING
    const enhancedStyles = $('<style>');
    enhancedStyles.text(`
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .angel-tab.active {
            animation: tabActivate 0.3s ease;
        }
        
        @keyframes tabActivate {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
        
        .angel-tabs.tab-switched::before {
            animation: tabSwitchGlow 0.6s ease;
        }
        
        @keyframes tabSwitchGlow {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.6; }
        }
        
        .angel-button.loading {
            animation: buttonPulse 1.5s ease-in-out infinite;
        }
        
        @keyframes buttonPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.01); }
        }
        
        .code-checking {
            border-color: rgba(255, 255, 255, 0.3) !important;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.1) !important;
        }
        
        .code-valid {
            border-color: rgba(144, 238, 144, 0.5) !important;
        }
        
        .code-invalid {
            border-color: rgba(255, 204, 203, 0.5) !important;
        }
        
        /* Enhanced focus states */
        .angel-input:focus,
        .angel-select:focus {
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1) !important;
        }
        
        /* 🌌 COSMIC LOADING OVERLAY */
        .cosmic-loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: radial-gradient(circle at center, 
                rgba(20, 20, 20, 0.98) 0%, 
                rgba(10, 10, 10, 0.99) 50%, 
                rgba(5, 5, 5, 1) 100%
            );
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            animation: cosmicFadeIn 0.8s ease-out;
        }

        @keyframes cosmicFadeIn {
            from {
                opacity: 0;
                backdrop-filter: blur(0px);
            }
            to {
                opacity: 1;
                backdrop-filter: blur(10px);
            }
        }

        .cosmic-loading-overlay.cosmic-fade-out {
            animation: cosmicFadeOut 1s ease-in-out forwards;
        }

        @keyframes cosmicFadeOut {
            to {
                opacity: 0;
                backdrop-filter: blur(0px);
                transform: scale(1.1);
            }
        }

        /* 🌟 COSMIC BACKGROUND EFFECTS */
        .cosmic-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .cosmic-led-strip {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, 
                #00ffff 0%,     /* Electric cyan */
                #ff1493 16%,    /* Deep pink */
                #aed604 32%,    /* Iconic green */
                #ff4500 48%,    /* Electric orange */
                #9932cc 64%,    /* Electric purple */
                #ff69b4 80%,    /* Hot pink */
                #00ffff 100%    /* Back to cyan */
            );
            background-size: 400% 100%;
            animation: cosmicLedFlow 8s linear infinite;
            opacity: 0.8;
        }

        @keyframes cosmicLedFlow {
            0% { background-position: 0% 50%; }
            100% { background-position: 400% 50%; }
        }

        /* ✨ COSMIC PARTICLES */
        .cosmic-particles {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .cosmic-particle {
            position: absolute;
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.6);
            animation: cosmicFloat linear infinite;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }

        @keyframes cosmicFloat {
            0% {
                transform: translateY(100vh) rotate(0deg) scale(0);
                opacity: 0;
            }
            10% {
                opacity: 1;
                transform: translateY(90vh) rotate(36deg) scale(1);
            }
            90% {
                opacity: 1;
                transform: translateY(-10vh) rotate(324deg) scale(1);
            }
            100% {
                opacity: 0;
                transform: translateY(-20vh) rotate(360deg) scale(0);
            }
        }

        /* 🌟 COSMIC LOADING CONTENT */
        .cosmic-loading-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            z-index: 1001;
            max-width: 400px;
            padding: 0 20px;
        }

        /* 💎 COSMIC LOGO */
        .cosmic-logo {
            position: relative;
            width: 120px;
            height: 120px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cosmic-logo-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 3px solid transparent;
            border-radius: 50%;
            background: linear-gradient(45deg, 
                #00ffff 0%,
                #ff1493 25%,
                #aed604 50%,
                #ff4500 75%,
                #00ffff 100%
            );
            background-size: 400% 400%;
            animation: cosmicRingSpin 3s linear infinite, cosmicRingGlow 2s ease-in-out infinite alternate;
            mask: radial-gradient(circle, transparent 45px, black 48px);
            -webkit-mask: radial-gradient(circle, transparent 45px, black 48px);
        }

        @keyframes cosmicRingSpin {
            to { transform: rotate(360deg); }
        }

        @keyframes cosmicRingGlow {
            from { 
                background-size: 400% 400%;
                background-position: 0% 50%;
            }
            to { 
                background-size: 600% 600%;
                background-position: 100% 50%;
            }
        }

        .cosmic-logo-center {
            font-size: 3rem;
            animation: cosmicLogoPulse 2s ease-in-out infinite;
            filter: drop-shadow(0 0 20px rgba(255, 255, 255, 0.5));
            z-index: 1002;
        }

        @keyframes cosmicLogoPulse {
            0%, 100% { 
                transform: scale(1);
                filter: drop-shadow(0 0 20px rgba(255, 255, 255, 0.3));
            }
            50% { 
                transform: scale(1.1);
                filter: drop-shadow(0 0 30px rgba(174, 214, 4, 0.6));
            }
        }

        .cosmic-logo-center.cosmic-success-pulse {
            animation: cosmicSuccessPulse 0.6s ease-in-out infinite;
        }

        @keyframes cosmicSuccessPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.3); }
        }

        /* 📝 COSMIC LOADING TEXT */
        .cosmic-loading-text {
            margin-bottom: 2rem;
        }

        .cosmic-main-text {
            font-size: 1.3rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.5rem;
            background: linear-gradient(90deg, 
                #00ffff 0%, 
                #ff1493 50%, 
                #aed604 100%
            );
            background-size: 200% 100%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: cosmicTextFlow 3s ease-in-out infinite;
            line-height: 1.2;
        }

        @keyframes cosmicTextFlow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .cosmic-sub-text {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
            line-height: 1.4;
        }

        /* 📊 COSMIC PROGRESS BAR */
        .cosmic-progress-bar {
            width: 100%;
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            overflow: hidden;
            position: relative;
            margin-bottom: 1rem;
        }

        .cosmic-progress-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, 
                #00ffff 0%,
                #ff1493 50%,
                #aed604 100%
            );
            background-size: 200% 100%;
            border-radius: 3px;
            transition: width 0.3s ease;
            animation: cosmicProgressFlow 2s linear infinite;
        }

        @keyframes cosmicProgressFlow {
            0% { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }

        .cosmic-progress-glow {
            position: absolute;
            top: -2px;
            left: 0;
            width: 100%;
            height: 10px;
            background: linear-gradient(90deg, 
                transparent 0%,
                rgba(174, 214, 4, 0.3) 50%,
                transparent 100%
            );
            border-radius: 5px;
            opacity: 0.6;
            animation: cosmicProgressGlow 1.5s ease-in-out infinite;
        }

        @keyframes cosmicProgressGlow {
            0%, 100% { 
                transform: translateX(-100%);
                opacity: 0;
            }
            50% { 
                transform: translateX(100%);
                opacity: 0.8;
            }
        }

        /* 💥 SUCCESS PARTICLES */
        .success-particle {
            position: absolute;
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
            pointer-events: none;
            z-index: 1003;
        }

        /* 📱 MOBILE RESPONSIVE FOR COSMIC LOADING */
        @media (max-width: 640px) {
            .cosmic-loading-content {
                padding: 0 15px;
                max-width: 300px;
            }
            
            .cosmic-logo {
                width: 100px;
                height: 100px;
                margin-bottom: 1.5rem;
            }
            
            .cosmic-logo-center {
                font-size: 2.5rem;
            }
            
            .cosmic-main-text {
                font-size: 1.1rem;
            }
            
            .cosmic-sub-text {
                font-size: 0.8rem;
            }
            
            .cosmic-particle {
                font-size: 0.8rem;
            }
        }
        
        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            .angel-auth-container,
            .angel-form-container,
            .angel-message,
            .angel-button,
            .angel-tab,
            .cosmic-logo-ring,
            .cosmic-logo-center,
            .cosmic-particle,
            .cosmic-led-strip,
            .cosmic-progress-fill,
            .cosmic-progress-glow,
            .cosmic-main-text {
                animation: none !important;
                transition: none !important;
            }
            
            .cosmic-loading-overlay {
                animation: none !important;
                backdrop-filter: blur(5px);
            }
        }

        /* 🎨 HIGH CONTRAST MODE */
        @media (prefers-contrast: high) {
            .cosmic-loading-overlay {
                background: rgba(0, 0, 0, 0.95);
            }
            
            .cosmic-main-text {
                -webkit-text-fill-color: #ffffff;
                color: #ffffff;
            }
            
            .cosmic-progress-bar {
                border: 1px solid rgba(255, 255, 255, 0.5);
            }
        }
    `);
    $('head').append(enhancedStyles);
    
    // 🌟 INITIALIZE ENHANCED EFFECTS
    setTimeout(() => {
        $('.angel-auth-container').addClass('initialized');
    }, 100);
    
});