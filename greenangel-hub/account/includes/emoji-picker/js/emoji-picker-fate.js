/**
 * 🎲 GREEN ANGEL EMOJI PICKER - FATE SPINNER WITH DEMO MODE SUPPORT
 * LOCKED SYSTEM: Demo mode when identity is locked, real fate when unlocked!
 * Slow start → Speed up → Dramatic slow down (perfect 8-second timing)
 */

(function() {
    'use strict';
    
    // 🎲 Fate picker state management
    let fatePickerActive = false;
    let allEmojis = [];
    let selectedEmoji = null;
    let isLocked = false;
    let daysRemaining = 0;
    let isDemoMode = false;
    
    // 🎰 Fate spinner variables - PERFECT TIMING
    let rouletteInterval = null;
    let currentRouletteIndex = 0;
    let isSpinning = false;
    
    // 🎨 Fate DOM Elements
    const fateElements = {
        fatePickerOverlay: null,
        fateSpinnerOverlay: null,
        fateResultOverlay: null,
        rouletteEmoji: null,
        chosenEmojiLarge: null
    };
    
    // 🚀 Initialize fate picker when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🎲 Starting Fate Picker with Demo Mode Support...');
        
        // Small delay to ensure everything is loaded
        setTimeout(function() {
            initializeFatePicker();
        }, 150);
    });
    
    /**
     * 🎪 FATE PICKER INITIALIZATION
     */
    function initializeFatePicker() {
        console.log('🎲 Initializing Fate Picker with Lock System...');
        
        // Check if we're on the emoji picker page
        if (!document.querySelector('.emoji-picker-app')) {
            console.log('❌ Emoji picker app not found for fate picker');
            return;
        }
        
        // Get lock status from PHP
        isLocked = window.emojiPickerIsLocked || false;
        daysRemaining = window.emojiPickerDaysRemaining || 0;
        
        console.log('🔒 Fate picker lock status:', { isLocked, daysRemaining });
        
        // Cache fate elements
        cacheFateElements();
        
        // Set up fate event listeners
        setupFatePickerListeners();
        
        // Collect all emojis for fate picker
        collectAllEmojis();
        
        console.log('✨ Fate Picker initialized with lock support!', isLocked ? 'DEMO MODE' : 'REAL MODE');
    }
    
    /**
     * 🎯 CACHE FATE DOM ELEMENTS
     */
    function cacheFateElements() {
        fateElements.fatePickerOverlay = document.querySelector('.fate-picker-overlay');
        fateElements.fateSpinnerOverlay = document.querySelector('.fate-spinner-overlay');
        fateElements.fateResultOverlay = document.querySelector('.fate-result-overlay');
        fateElements.rouletteEmoji = document.querySelector('.roulette-emoji');
        fateElements.chosenEmojiLarge = document.querySelector('.chosen-emoji-large');
        
        console.log('🎲 Cached fate elements:', {
            fatePickerOverlay: !!fateElements.fatePickerOverlay,
            fateSpinnerOverlay: !!fateElements.fateSpinnerOverlay,
            fateResultOverlay: !!fateElements.fateResultOverlay
        });
    }
    
    /**
     * 🎲 SETUP FATE PICKER EVENT LISTENERS
     */
    function setupFatePickerListeners() {
        console.log('🎲 Setting up fate picker listeners with lock support...');
        
        // Fate picker button
        const fateButton = document.querySelector('.fate-picker-button');
        
        if (fateButton) {
            // Check if it has demo mode attribute
            isDemoMode = fateButton.hasAttribute('data-demo-mode') || isLocked;
            
            fateButton.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('🎲 Fate button clicked!', isDemoMode ? 'DEMO MODE' : 'REAL MODE');
                showFatePickerModal();
            });
            console.log('✅ Fate button listener attached', isDemoMode ? '(Demo Mode)' : '(Real Mode)');
        } else {
            console.warn('⚠️ Fate button not found');
        }
        
        // Cancel fate button
        const cancelFateButton = document.querySelector('.cancel-fate');
        if (cancelFateButton) {
            cancelFateButton.addEventListener('click', closeFatePickerModal);
        }
        
        // Embrace fate button
        const embraceFateButton = document.querySelector('.embrace-fate');
        if (embraceFateButton) {
            embraceFateButton.addEventListener('click', startFateSpinner);
        }
        
        // Accept/Demo close button
        const acceptButton = document.querySelector('.fate-result-action.accept');
        const demoCloseButton = document.querySelector('.fate-result-action.demo-close');
        
        if (acceptButton && !isLocked) {
            acceptButton.addEventListener('click', acceptFateResultReal);
        }
        
        if (demoCloseButton && isLocked) {
            demoCloseButton.addEventListener('click', closeFateDemo);
        }
        
        // Close overlays on background click
        if (fateElements.fatePickerOverlay) {
            fateElements.fatePickerOverlay.addEventListener('click', function(e) {
                if (e.target === fateElements.fatePickerOverlay) {
                    closeFatePickerModal();
                }
            });
        }
        
        console.log('🎲 Fate picker listeners ready with lock support!');
    }
    
    /**
     * 📦 COLLECT ALL EMOJIS FOR FATE PICKER
     */
    function collectAllEmojis() {
        allEmojis = [];
        
        // Get all emoji options from the page
        const emojiOptions = document.querySelectorAll('.emoji-option');
        emojiOptions.forEach(function(option) {
            const emoji = option.getAttribute('data-emoji');
            if (emoji && allEmojis.indexOf(emoji) === -1) {
                allEmojis.push(emoji);
            }
        });
        
        console.log('📦 Collected emojis for fate picker:', allEmojis.length, isDemoMode ? '(Demo Mode)' : '(Real Mode)');
    }
    
    /**
     * 🔮 SHOW FATE PICKER MODAL
     */
    function showFatePickerModal() {
        console.log('🔮 Opening fate picker modal...', isDemoMode ? 'DEMO MODE' : 'REAL MODE');
        
        if (fateElements.fatePickerOverlay) {
            fateElements.fatePickerOverlay.style.display = 'flex';
            fateElements.fatePickerOverlay.style.opacity = '0';
            
            // Trigger reflow
            fateElements.fatePickerOverlay.offsetHeight;
            
            fateElements.fatePickerOverlay.style.transition = 'opacity 0.5s ease';
            fateElements.fatePickerOverlay.style.opacity = '1';
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
            
            // Add mobile-specific adjustments
            adjustForMobile();
            
            fatePickerActive = true;
        } else {
            console.error('❌ Fate picker overlay not found!');
        }
    }
    
    /**
     * ❌ CLOSE FATE PICKER MODAL
     */
    function closeFatePickerModal() {
        console.log('❌ Closing fate picker modal...');
        
        if (fateElements.fatePickerOverlay) {
            fateElements.fatePickerOverlay.style.opacity = '0';
            
            setTimeout(function() {
                fateElements.fatePickerOverlay.style.display = 'none';
                document.body.style.overflow = '';
                fatePickerActive = false;
            }, 500);
        }
    }
    
    /**
     * 🎰 START THE EPIC FATE SPINNER
     */
    function startFateSpinner() {
        console.log('🎰 STARTING FATE SPINNER!', isDemoMode ? 'DEMO MODE' : 'REAL MODE');
        
        // 🙈 HIDE BACK BUTTON FOR IMMERSION!
        const backButton = document.querySelector('.emoji-back-button-container');
        if (backButton) {
            backButton.style.display = 'none';
            console.log('🙈 Hidden back button for immersion');
        }
        
        // Close the modal
        closeFatePickerModal();
        
        // Wait for modal to close, then show spinner
        setTimeout(function() {
            if (fateElements.fateSpinnerOverlay) {
                fateElements.fateSpinnerOverlay.style.display = 'flex';
                fateElements.fateSpinnerOverlay.style.opacity = '0';
                
                // Trigger reflow
                fateElements.fateSpinnerOverlay.offsetHeight;
                
                fateElements.fateSpinnerOverlay.style.transition = 'opacity 0.8s ease';
                fateElements.fateSpinnerOverlay.style.opacity = '1';
                
                // Start the perfectly timed roulette
                setTimeout(function() {
                    startPerfectRoulette();
                }, 500);
            }
        }, 400);
    }
    
    /**
     * 🎲 START THE PERFECT ROULETTE WHEEL - 8 SECONDS WITH DEMO MODE
     */
    function startPerfectRoulette() {
        console.log('🎲 Starting 8-second perfect roulette wheel...', isDemoMode ? 'DEMO MODE' : 'REAL MODE');
        
        if (allEmojis.length === 0) {
            console.error('❌ No emojis available for roulette');
            return;
        }
        
        isSpinning = true;
        currentRouletteIndex = 0;
        
        // Reset roulette emoji
        if (fateElements.rouletteEmoji) {
            fateElements.rouletteEmoji.textContent = allEmojis[0];
        }
        
        // Start progress bar animation - 8 SECOND TIMING
        animatePerfectProgressBar();
        
        // PERFECT 8-SECOND SPINNER: Slow start → Speed up → Dramatic slow down
        let spinCount = 0;
        const totalSpins = 80;
        
        function perfectSpin() {
            if (!isSpinning) return;
            
            // Update emoji
            currentRouletteIndex = (currentRouletteIndex + 1) % allEmojis.length;
            if (fateElements.rouletteEmoji) {
                fateElements.rouletteEmoji.textContent = allEmojis[currentRouletteIndex];
            }
            
            spinCount++;
            
            // PERFECT 8-SECOND TIMING CALCULATION
            let spinSpeed;
            
            if (spinCount <= 10) {
                // SLOW START - You can see each emoji clearly (2 seconds)
                spinSpeed = 250 - (spinCount * 15); // 250ms → 100ms
                if (spinCount === 1) console.log('🐌 Slow start phase beginning...');
            } else if (spinCount <= 55) {
                // FAST MIDDLE - Exciting blur of options (4 seconds)
                spinSpeed = 70 + Math.sin(spinCount * 0.1) * 15; // 55-85ms with variation
                if (spinCount === 11) console.log('🚀 Fast phase started...');
            } else {
                // DRAMATIC SLOW DOWN - Building suspense (2 seconds)
                const slowdownProgress = (spinCount - 55) / (totalSpins - 55);
                spinSpeed = 70 + (slowdownProgress * 300); // 70ms → 370ms
                if (spinCount === 56) console.log('🎯 Dramatic slowdown phase...');
            }
            
            // Stop after total spins
            if (spinCount >= totalSpins) {
                console.log('🛑 Perfect 8-second spinner completed!');
                stopPerfectRoulette();
                return;
            }
            
            // Continue spinning with perfect timing
            setTimeout(perfectSpin, spinSpeed);
        }
        
        // Start the perfect spinning
        console.log('🎲 Starting perfect 8-second spin sequence...');
        perfectSpin();
    }
    
    /**
     * 📊 ANIMATE PERFECT PROGRESS BAR - 8 SECOND TIMING
     */
    function animatePerfectProgressBar() {
        const progressBar = document.querySelector('.fate-progress-fill');
        const progressText = document.querySelector('.fate-progress-text');
        
        if (!progressBar || !progressText) return;
        
        const messages = isDemoMode ? [
            'Running demo fate...',
            'Simulating cosmic energy...',
            'Demo spin in progress...',
            'Demo fate complete!'
        ] : [
            'Channeling cosmic energy...',
            'Reading your aura...',
            'Aligning the stars...',
            'Finalizing your fate...'
        ];
        
        let progress = 0;
        let messageIndex = 0;
        
        const progressInterval = setInterval(function() {
            progress += 1.5; // Faster progress for 8 seconds
            progressBar.style.width = progress + '%';
            
            // Update message every 25%
            if (progress % 25 === 0 && messageIndex < messages.length - 1) {
                messageIndex++;
                progressText.textContent = messages[messageIndex];
            }
            
            if (progress >= 100) {
                clearInterval(progressInterval);
                progressText.textContent = isDemoMode ? 'Demo fate decided!' : 'Fate has decided!';
            }
        }, 80); // Faster interval for 8 seconds total
    }
    
    /**
     * 🛑 STOP THE PERFECT ROULETTE AND SHOW RESULT
     */
    function stopPerfectRoulette() {
        console.log('🛑 Stopping perfect roulette...', isDemoMode ? 'DEMO MODE' : 'REAL MODE');
        
        isSpinning = false;
        
        // Pick a random final emoji (different from current)
        let finalEmoji;
        do {
            finalEmoji = allEmojis[Math.floor(Math.random() * allEmojis.length)];
        } while (finalEmoji === allEmojis[currentRouletteIndex] && allEmojis.length > 1);
        
        // Dramatic final animation
        if (fateElements.rouletteEmoji) {
            fateElements.rouletteEmoji.style.animation = 'none';
            fateElements.rouletteEmoji.style.transform = 'scale(1.3)';
            fateElements.rouletteEmoji.textContent = finalEmoji;
            
            // Pulse effect for drama
            setTimeout(function() {
                fateElements.rouletteEmoji.style.transition = 'transform 0.5s ease';
                fateElements.rouletteEmoji.style.transform = 'scale(1)';
            }, 300);
        }
        
        // Wait for perfect dramatic timing, then show result
        setTimeout(function() {
            showFateResult(finalEmoji);
        }, 800);
    }
    
    /**
     * 🎉 SHOW FATE RESULT
     */
    function showFateResult(chosenEmoji) {
        console.log('🎉 Showing fate result:', chosenEmoji, isDemoMode ? 'DEMO MODE' : 'REAL MODE');
        
        // 👀 BRING BACK BUTTON FOR NAVIGATION
        const backButton = document.querySelector('.emoji-back-button-container');
        if (backButton) {
            backButton.style.display = 'block';
            console.log('👀 Restored back button after fate sequence');
        }
        
        // Hide spinner
        if (fateElements.fateSpinnerOverlay) {
            fateElements.fateSpinnerOverlay.style.opacity = '0';
            
            setTimeout(function() {
                fateElements.fateSpinnerOverlay.style.display = 'none';
            }, 500);
        }
        
        // Update chosen emoji
        if (fateElements.chosenEmojiLarge) {
            fateElements.chosenEmojiLarge.textContent = chosenEmoji;
        }
        
        // Store the chosen emoji
        selectedEmoji = chosenEmoji;
        
        // Show result overlay
        setTimeout(function() {
            if (fateElements.fateResultOverlay) {
                fateElements.fateResultOverlay.style.display = 'flex';
                fateElements.fateResultOverlay.style.opacity = '0';
                
                // Trigger reflow
                fateElements.fateResultOverlay.offsetHeight;
                
                fateElements.fateResultOverlay.style.transition = 'opacity 0.8s ease';
                fateElements.fateResultOverlay.style.opacity = '1';
                
                // Create celebration particles
                createFateCelebrationParticles();
            }
        }, 600);
    }
    
    /**
     * ✅ ACCEPT FATE RESULT (REAL MODE - SAVES TO DATABASE)
     */
    function acceptFateResultReal() {
        console.log('✅ Accepting REAL fate result:', selectedEmoji);
        
        if (!selectedEmoji || isLocked) {
            console.error('❌ Cannot accept fate result - locked or no emoji');
            return;
        }
        
        // Hide result overlay
        if (fateElements.fateResultOverlay) {
            fateElements.fateResultOverlay.style.opacity = '0';
            
            setTimeout(function() {
                fateElements.fateResultOverlay.style.display = 'none';
                document.body.style.overflow = '';
                fatePickerActive = false;
                
                // Save the emoji with 30-day lock!
                setTimeout(function() {
                    saveEmojiIdentityReal(selectedEmoji);
                }, 300);
            }, 500);
        }
    }
    
    /**
     * ❌ CLOSE FATE DEMO (DEMO MODE - NO SAVING)
     */
    function closeFateDemo() {
        console.log('❌ Closing DEMO fate result - no saving');
        
        // Hide result overlay
        if (fateElements.fateResultOverlay) {
            fateElements.fateResultOverlay.style.opacity = '0';
            
            setTimeout(function() {
                fateElements.fateResultOverlay.style.display = 'none';
                document.body.style.overflow = '';
                fatePickerActive = false;
                
                // Show a demo completion message
                showDemoCompletionMessage();
            }, 500);
        }
    }
    
    /**
     * 🎭 SHOW DEMO COMPLETION MESSAGE
     */
    function showDemoCompletionMessage() {
        console.log('🎭 Showing demo completion message');
        
        const demoMessage = document.createElement('div');
        demoMessage.style.position = 'fixed';
        demoMessage.style.top = '20px';
        demoMessage.style.left = '50%';
        demoMessage.style.transform = 'translateX(-50%)';
        demoMessage.style.background = 'linear-gradient(135deg, #9c27b0 0%, #e91e63 100%)';
        demoMessage.style.color = '#ffffff';
        demoMessage.style.padding = '1rem 2rem';
        demoMessage.style.borderRadius = '50px';
        demoMessage.style.fontWeight = '600';
        demoMessage.style.fontSize = '0.9rem';
        demoMessage.style.zIndex = '100000';
        demoMessage.style.boxShadow = '0 8px 25px rgba(156, 39, 176, 0.3)';
        demoMessage.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span>🎭</span>
                <span>Demo complete! Come back in ${daysRemaining} days to try for real!</span>
            </div>
        `;
        
        document.body.appendChild(demoMessage);
        
        // Auto remove after 5 seconds
        setTimeout(function() {
            demoMessage.remove();
        }, 5000);
    }
    
    /**
     * 💾 SAVE EMOJI IDENTITY (REAL MODE WITH 30-DAY LOCK)
     */
    function saveEmojiIdentityReal(emoji) {
        console.log('💾 Saving REAL fate emoji identity with 30-day lock:', emoji);
        
        // Get nonce for security
        const nonce = window.emojiPickerData?.nonce || window.emojiPickerNonce || '';
        const ajaxurl = window.emojiPickerData?.ajaxurl || '/wp-admin/admin-ajax.php';
        
        console.log('🔐 Real fate AJAX details:', { nonce: nonce ? 'Present' : 'Missing', ajaxurl });
        
        if (!nonce) {
            console.error('❌ No nonce available for real fate picker!');
            showErrorMessage('Security nonce missing - please refresh the page');
            return;
        }
        
        // Try to get identity info from the identity system
        let identityName = '';
        let identityBio = '';
        
        if (window.EmojiIdentitySystem && window.EmojiIdentitySystem.getIdentity) {
            const identity = window.EmojiIdentitySystem.getIdentity(emoji);
            if (identity) {
                identityName = identity.name;
                identityBio = identity.bio;
                console.log('🎭 Found real fate identity for emoji:', emoji, identity);
            }
        }
        
        // Show loading state
        showLoadingState();
        
        // Prepare form data
        const formData = new FormData();
        formData.append('action', 'save_emoji_identity');
        formData.append('emoji', emoji);
        formData.append('identity_name', identityName);
        formData.append('identity_bio', identityBio);
        formData.append('nonce', nonce);
        
        // Make AJAX request
        fetch(ajaxurl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(function(response) {
            console.log('🌐 Real fate AJAX response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(function(data) {
            hideLoadingState();
            console.log('📦 Real fate AJAX response data:', data);
            
            if (data.success) {
                console.log('✅ Successfully saved real fate emoji identity with 30-day lock!');
                
                // Update global lock status
                isLocked = true;
                window.emojiPickerIsLocked = true;
                
                showRealFateSuccessCelebration(emoji, identityName, identityBio);
                updateCurrentIdentityFromFate(emoji, identityName, identityBio);
            } else {
                console.error('❌ Failed to save real fate emoji identity:', data.data);
                
                // Check if it's a lock-related error
                if (data.data && data.data.includes('locked')) {
                    showLockMessage();
                } else {
                    showErrorMessage(data.data || 'Failed to save emoji identity');
                }
            }
        })
        .catch(function(error) {
            hideLoadingState();
            console.error('❌ Real fate save network error:', error);
            showErrorMessage('Network error: ' + error.message);
        });
    }
    
    /**
     * 🎉 SHOW REAL FATE SUCCESS WITH LOCK CONFIRMATION
     */
    function showRealFateSuccessCelebration(emoji, identityName, identityBio) {
        console.log('🎉 Showing real fate success with 30-day lock:', { emoji, identityName, identityBio });
        
        // Update success emoji
        const successEmojiLarge = document.querySelector('.success-emoji-large');
        if (successEmojiLarge) {
            successEmojiLarge.textContent = emoji;
        }
        
        // Enhanced success message with fate and lock info
        const successMessage = document.querySelector('.success-message');
        if (successMessage) {
            let messageHTML = '';
            
            if (identityName && identityBio) {
                messageHTML = `
                    <div style="margin-bottom: 1rem;">
                        <strong style="color: var(--emoji-primary); font-size: 1.1rem;">${identityName}</strong>
                    </div>
                    <div style="font-style: italic; line-height: 1.5; color: rgba(255, 255, 255, 0.9); margin-bottom: 1rem;">
                        ${identityBio}
                    </div>
                `;
            }
            
            messageHTML += `
                <div style="font-size: 0.9rem; color: rgba(255, 255, 255, 0.7);">
                    <strong style="color: #aed604;">🎲 Fate has chosen your Angel identity!</strong>
                    <br><br>
                    <strong style="color: #ff9800;">🔒 Your identity is now locked for 30 days.</strong>
                    <br>You cannot change it until the lock expires.
                </div>
            `;
            
            successMessage.innerHTML = messageHTML;
        }
        
        const successCelebration = document.querySelector('.success-celebration');
        if (successCelebration) {
            successCelebration.style.display = 'flex';
            successCelebration.style.opacity = '0';
            
            // Trigger reflow
            successCelebration.offsetHeight;
            
            successCelebration.style.transition = 'opacity 0.5s ease';
            successCelebration.style.opacity = '1';
            
            // Add celebration particles
            createCelebrationParticles();
            
            // Play success sound
            playSuccessSound();
            
            // Set up fate success buttons
            setupRealFateSuccessButtons();
        }
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }
    
    /**
     * 🎯 REAL FATE SUCCESS BUTTONS
     */
    function setupRealFateSuccessButtons() {
        console.log('🎯 Setting up real fate success buttons...');
        
        const successClose = document.querySelector('.success-close');
        const successCloseAlt = document.querySelector('.success-close-alt');
        
        // Clean and fix primary button
        if (successClose) {
            const newSuccessClose = successClose.cloneNode(true);
            successClose.parentNode.replaceChild(newSuccessClose, successClose);
            
            newSuccessClose.textContent = 'Continue to Dashboard';
            
            newSuccessClose.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('🏠 Real fate redirect to Angel Hub');
                redirectToAngelHub();
            });
        }
        
        // Clean and fix secondary button
        if (successCloseAlt) {
            const newSuccessCloseAlt = successCloseAlt.cloneNode(true);
            successCloseAlt.parentNode.replaceChild(newSuccessCloseAlt, successCloseAlt);
            
            newSuccessCloseAlt.textContent = 'Close';
            
            newSuccessCloseAlt.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('❌ Real fate close only');
                closeModalOnly();
            });
        }
        
        // Background click handler
        const successCelebration = document.querySelector('.success-celebration');
        if (successCelebration) {
            successCelebration.addEventListener('click', function(e) {
                if (e.target === successCelebration) {
                    closeModalOnly();
                }
            });
        }
    }
    
    /**
     * 🏠 REDIRECT TO ANGEL HUB
     */
    function redirectToAngelHub() {
        console.log('🏠 Fate redirect to Angel Hub...');
        
        const successCelebration = document.querySelector('.success-celebration');
        if (successCelebration) {
            successCelebration.style.opacity = '0';
            
            setTimeout(function() {
                successCelebration.style.display = 'none';
                
                const angelHubUrl = window.emojiPickerBackUrl || '/';
                console.log('🏠 Fate redirect to:', angelHubUrl);
                window.location.href = angelHubUrl;
            }, 500);
        }
        
        document.body.style.overflow = '';
    }
    
    /**
     * ❌ CLOSE MODAL ONLY (NO REDIRECT)
     */
    function closeModalOnly() {
        console.log('❌ Fate close modal only...');
        
        const successCelebration = document.querySelector('.success-celebration');
        if (successCelebration) {
            successCelebration.style.opacity = '0';
            
            setTimeout(function() {
                successCelebration.style.display = 'none';
            }, 500);
        }
        
        document.body.style.overflow = '';
    }
    
    /**
     * ⏳ SHOW LOADING STATE
     */
    function showLoadingState() {
        const loader = document.createElement('div');
        loader.className = 'emoji-loading';
        loader.innerHTML = 'Fate is locking your Angel identity for 30 days...';
        loader.style.position = 'fixed';
        loader.style.top = '0';
        loader.style.left = '0';
        loader.style.right = '0';
        loader.style.bottom = '0';
        loader.style.background = 'rgba(0, 0, 0, 0.8)';
        loader.style.display = 'flex';
        loader.style.alignItems = 'center';
        loader.style.justifyContent = 'center';
        loader.style.zIndex = '99999';
        loader.style.backdropFilter = 'blur(5px)';
        loader.style.color = '#ffffff';
        loader.style.fontSize = '1.1rem';
        loader.style.fontWeight = '600';
        
        document.body.appendChild(loader);
    }
    
    /**
     * ✅ HIDE LOADING STATE
     */
    function hideLoadingState() {
        const loader = document.querySelector('.emoji-loading');
        if (loader) {
            loader.remove();
        }
    }
    
    /**
     * 🔒 SHOW LOCK MESSAGE
     */
    function showLockMessage() {
        const lockMessage = document.createElement('div');
        lockMessage.style.position = 'fixed';
        lockMessage.style.top = '20px';
        lockMessage.style.left = '50%';
        lockMessage.style.transform = 'translateX(-50%)';
        lockMessage.style.background = 'linear-gradient(135deg, #ff9800 0%, #f57c00 100%)';
        lockMessage.style.color = '#ffffff';
        lockMessage.style.padding = '1rem 2rem';
        lockMessage.style.borderRadius = '50px';
        lockMessage.style.fontWeight = '600';
        lockMessage.style.fontSize = '0.9rem';
        lockMessage.style.zIndex = '100000';
        lockMessage.style.boxShadow = '0 8px 25px rgba(255, 152, 0, 0.3)';
        lockMessage.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span>🔒</span>
                <span>Your identity is already locked! Come back in ${daysRemaining} days.</span>
            </div>
        `;
        
        document.body.appendChild(lockMessage);
        
        setTimeout(function() {
            lockMessage.remove();
        }, 5000);
    }
    
    /**
     * ❌ SHOW ERROR MESSAGE
     */
    function showErrorMessage(message) {
        const errorDiv = document.createElement('div');
        errorDiv.style.position = 'fixed';
        errorDiv.style.top = '20px';
        errorDiv.style.left = '50%';
        errorDiv.style.transform = 'translateX(-50%)';
        errorDiv.style.background = 'linear-gradient(135deg, #f44336 0%, #e53935 100%)';
        errorDiv.style.color = '#ffffff';
        errorDiv.style.padding = '1rem 2rem';
        errorDiv.style.borderRadius = '50px';
        errorDiv.style.fontWeight = '600';
        errorDiv.style.fontSize = '0.9rem';
        errorDiv.style.zIndex = '100000';
        errorDiv.style.boxShadow = '0 8px 25px rgba(244, 67, 54, 0.3)';
        errorDiv.textContent = message;
        
        document.body.appendChild(errorDiv);
        
        setTimeout(function() {
            errorDiv.remove();
        }, 5000);
    }
    
    /**
     * 🔄 UPDATE CURRENT IDENTITY FROM FATE
     */
    function updateCurrentIdentityFromFate(emoji, identityName, identityBio) {
        console.log('🔄 Updating current identity from fate:', { emoji, identityName, identityBio });
        
        const currentEmojiLarge = document.querySelector('.current-emoji-large');
        if (currentEmojiLarge) {
            currentEmojiLarge.textContent = emoji;
        }
        
        // Update status to show locked from fate
        const lockStatus = document.querySelector('.lock-status');
        if (lockStatus) {
            lockStatus.className = 'lock-status locked';
            lockStatus.innerHTML = '<span class="lock-icon">🔒</span><span class="lock-text">Locked by fate for 30 days</span>';
        }
        
        // Show countdown bar
        const lockCountdown = document.querySelector('.lock-countdown');
        if (lockCountdown) {
            lockCountdown.style.display = 'block';
            const countdownFill = lockCountdown.querySelector('.countdown-fill');
            if (countdownFill) {
                countdownFill.style.width = '0%';
            }
            const countdownText = lockCountdown.querySelector('.countdown-text');
            if (countdownText) {
                countdownText.textContent = 'Fate has locked your identity for 30 days';
            }
        }
        
        // Update bio display if available
        const currentIdentityInfo = document.querySelector('.current-identity-info');
        if (currentIdentityInfo && identityBio) {
            const existingBio = currentIdentityInfo.querySelector('.current-identity-bio');
            if (existingBio) {
                existingBio.remove();
            }
            
            const bioElement = document.createElement('div');
            bioElement.className = 'current-identity-bio';
            bioElement.textContent = identityBio;
            currentIdentityInfo.appendChild(bioElement);
        }
    }
    
    /**
     * 🎊 CREATE CELEBRATION PARTICLES
     */
    function createCelebrationParticles() {
        const particles = ['✨', '🌟', '💫', '⭐', '🎉', '🎊'];
        const container = document.querySelector('.celebration-particles');
        
        if (!container) return;
        
        container.innerHTML = '';
        
        for (let i = 0; i < 12; i++) {
            const particle = document.createElement('div');
            particle.textContent = particles[Math.floor(Math.random() * particles.length)];
            particle.style.position = 'absolute';
            particle.style.fontSize = '1.5rem';
            particle.style.left = Math.random() * 80 + 10 + '%';
            particle.style.top = Math.random() * 80 + 10 + '%';
            particle.style.animation = 'particleFloat ' + (2 + Math.random() * 2) + 's ease-in-out infinite';
            particle.style.animationDelay = Math.random() * 2 + 's';
            particle.style.pointerEvents = 'none';
            
            container.appendChild(particle);
        }
    }
    
    /**
     * 🎊 CREATE FATE CELEBRATION PARTICLES
     */
    function createFateCelebrationParticles() {
        const particles = isDemoMode ? ['🎭', '✨', '🌟', '💫'] : ['✨', '🌟', '💫', '⭐', '🎉', '🎊', '💖', '🔥'];
        const container = document.querySelector('.fate-result-overlay');
        
        if (!container) return;
        
        for (let i = 0; i < 20; i++) {
            const particle = document.createElement('div');
            particle.textContent = particles[Math.floor(Math.random() * particles.length)];
            particle.style.position = 'absolute';
            particle.style.fontSize = '1.5rem';
            particle.style.left = Math.random() * 90 + 5 + '%';
            particle.style.top = Math.random() * 90 + 5 + '%';
            particle.style.animation = 'fateParticleFloat ' + (2 + Math.random() * 3) + 's ease-in-out infinite';
            particle.style.animationDelay = Math.random() * 2 + 's';
            particle.style.pointerEvents = 'none';
            particle.style.zIndex = '1';
            
            container.appendChild(particle);
            
            setTimeout(function() {
                if (particle.parentNode) {
                    particle.remove();
                }
            }, 5000);
        }
    }
    
    /**
     * 🔊 PLAY SUCCESS SOUND
     */
    function playSuccessSound() {
        try {
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmUcBzSM2fDXeTEGIHfF8N6QQgwVWrTq674mFAwIAAAAAP///wAAAP///wAAAP///wAAAP///wAAAP///wAAAP///wAAAP///wAAAP///wAAAP///wAAAP///wAAAP///wAAAP///wAAAP///wAAAP///wAAAP///wAAAA==');
            audio.volume = 0.3;
            audio.play().catch(function() {});
        } catch (e) {}
    }
    
    /**
     * 📱 MOBILE OPTIMIZATIONS
     */
    function adjustForMobile() {
        const isMobile = window.innerWidth <= 768;
        
        if (isMobile) {
            const modals = document.querySelectorAll('.fate-picker-modal, .fate-result-content');
            modals.forEach(function(modal) {
                modal.style.width = '95%';
                modal.style.padding = '2rem 1.5rem';
            });
            
            const roulette = document.querySelector('.emoji-roulette');
            if (roulette) {
                roulette.style.width = '150px';
                roulette.style.height = '150px';
            }
            
            const rouletteEmoji = document.querySelector('.roulette-emoji');
            if (rouletteEmoji) {
                rouletteEmoji.style.fontSize = '3rem';
            }
            
            const chosenEmoji = document.querySelector('.chosen-emoji-large');
            if (chosenEmoji) {
                chosenEmoji.style.fontSize = '4.5rem';
            }
        }
    }
    
    // Handle mobile orientation changes
    window.addEventListener('orientationchange', function() {
        setTimeout(adjustForMobile, 100);
    });
    
    window.addEventListener('resize', function() {
        if (fatePickerActive) {
            adjustForMobile();
        }
    });
    
    // Expose API
    window.FateEmojiPicker = {
        showFatePickerModal: showFatePickerModal,
        closeFatePickerModal: closeFatePickerModal,
        startFateSpinner: startFateSpinner,
        acceptFateResultReal: acceptFateResultReal,
        closeFateDemo: closeFateDemo,
        isLocked: isLocked,
        isDemoMode: isDemoMode
    };
    
})();