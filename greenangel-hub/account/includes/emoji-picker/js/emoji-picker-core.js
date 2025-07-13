/**
 * 🌟 GREEN ANGEL EMOJI IDENTITY PICKER - CORE FUNCTIONALITY WITH PROPER LOCKING
 * Handles main emoji picker interactions and animations
 * Mobile-first with premium LED effects
 * MODULAR DESIGN - Core features only
 * LOCKED SYSTEM: Respects 30-day lock, shows proper warnings!
 */

(function() {
    'use strict';
    
    // 🎯 Core State Management
    let currentCategory = 'mystical';
    let selectedEmoji = null;
    let isLocked = false;
    let daysRemaining = 0;
    let previewMode = false;
    
    // 🎨 Core DOM Elements
    const elements = {
        categoryTabs: null,
        emojiGrids: null,
        emojiOptions: null,
        previewSection: null,
        previewEmojis: null,
        modalOverlay: null,
        lockWarningOverlay: null,
        successCelebration: null,
        confirmModal: null
    };
    
    // 🚀 Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🌟 Starting Emoji Identity Picker Core initialization with proper locking...');
        
        // Small delay to ensure everything is loaded
        setTimeout(function() {
            initializeEmojiPicker();
        }, 100);
    });
    
    /**
     * 🎪 MAIN INITIALIZATION FUNCTION
     */
    function initializeEmojiPicker() {
        console.log('🌟 Initializing Emoji Identity Picker Core with locking system...');
        
        // Check if we're on the emoji picker page
        if (!document.querySelector('.emoji-picker-app')) {
            console.log('❌ Emoji picker app not found on this page');
            return;
        }
        
        // Get lock status from PHP
        isLocked = window.emojiPickerIsLocked || false;
        daysRemaining = window.emojiPickerDaysRemaining || 0;
        
        console.log('🔒 Lock status:', { isLocked, daysRemaining });
        
        // Cache DOM elements
        cacheElements();
        
        // Debug: Check if elements were found
        console.log('📍 Core elements found:', {
            categoryTabs: elements.categoryTabs?.length || 0,
            emojiGrids: elements.emojiGrids?.length || 0,
            emojiOptions: elements.emojiOptions?.length || 0,
            isLocked: isLocked
        });
        
        // Set up event listeners
        setupEventListeners();
        
        // Initialize default state
        initializeDefaultState();
        
        // Add stunning entrance animations
        addEntranceAnimations();
        
        console.log('✨ Emoji Identity Picker Core initialized successfully with proper locking!');
    }
    
    /**
     * 🎯 CACHE DOM ELEMENTS
     */
    function cacheElements() {
        elements.categoryTabs = document.querySelectorAll('.category-tab');
        elements.emojiGrids = document.querySelectorAll('.emoji-grid');
        elements.emojiOptions = document.querySelectorAll('.emoji-option');
        elements.previewSection = document.querySelector('.emoji-preview-section');
        elements.previewEmojis = document.querySelectorAll('.preview-emoji');
        elements.modalOverlay = document.querySelector('.emoji-modal-overlay');
        elements.lockWarningOverlay = document.querySelector('.lock-warning-overlay');
        elements.successCelebration = document.querySelector('.success-celebration');
        elements.confirmModal = document.querySelector('.emoji-modal');
        
        console.log('🔍 Cached core elements:', {
            categoryTabs: elements.categoryTabs.length,
            emojiGrids: elements.emojiGrids.length,
            emojiOptions: elements.emojiOptions.length,
            lockStatus: isLocked ? 'LOCKED' : 'UNLOCKED'
        });
    }
    
    /**
     * 🎮 SETUP EVENT LISTENERS
     */
    function setupEventListeners() {
        console.log('🎮 Setting up core event listeners with lock enforcement...');
        
        // Category tab switching
        if (elements.categoryTabs.length > 0) {
            elements.categoryTabs.forEach(function(tab, index) {
                console.log('🔗 Adding listeners to tab ' + index + ':', tab.getAttribute('data-category'));
                tab.addEventListener('click', handleCategorySwitch);
                tab.addEventListener('keydown', handleCategoryKeydown);
            });
        } else {
            console.warn('⚠️ No category tabs found!');
        }
        
        // Emoji selection (with lock enforcement)
        if (elements.emojiOptions.length > 0) {
            elements.emojiOptions.forEach(function(option) {
                if (isLocked) {
                    // For locked state, show warning instead of selection
                    option.addEventListener('click', handleLockedEmojiClick);
                } else {
                    // Normal selection for unlocked state
                    option.addEventListener('click', handleEmojiSelect);
                    option.addEventListener('keydown', handleEmojiKeydown);
                }
                
                // Always add hover effects (even when locked for visual feedback)
                option.addEventListener('mouseenter', addHoverEffect);
                option.addEventListener('mouseleave', removeHoverEffect);
            });
        } else {
            console.warn('⚠️ No emoji options found!');
        }
        
        // Preview actions (only if not locked)
        if (!isLocked) {
            const backButton = document.querySelector('.preview-action.cancel');
            const confirmButton = document.querySelector('.preview-action.confirm');
            
            if (backButton) {
                backButton.addEventListener('click', exitPreviewMode);
            }
            
            if (confirmButton) {
                confirmButton.addEventListener('click', showConfirmationModal);
            }
        }
        
        // Modal interactions
        setupModalListeners();
        
        // Success celebration buttons + click outside to close
        const successClose = document.querySelector('.success-close');
        const successCloseAlt = document.querySelector('.success-close-alt');
        if (successClose) {
            successClose.addEventListener('click', closeSuccessCelebration);
        }
        if (successCloseAlt) {
            successCloseAlt.addEventListener('click', closeSuccessCelebrationOnly);
        }
        
        // Add click outside to close functionality
        if (elements.successCelebration) {
            elements.successCelebration.addEventListener('click', function(e) {
                if (e.target === elements.successCelebration) {
                    closeSuccessCelebrationOnly();
                }
            });
        }
        
        // Keyboard navigation
        document.addEventListener('keydown', handleGlobalKeydown);
        
        // Mobile touch enhancements
        addTouchEnhancements();
        
        console.log('✅ Core event listeners set up complete with lock enforcement!');
    }
    
    /**
     * 🔒 HANDLE LOCKED EMOJI CLICK
     */
    function handleLockedEmojiClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('🔒 Locked emoji clicked - showing warning');
        
        // Add a visual "blocked" effect
        const option = e.currentTarget;
        option.style.transform = 'scale(0.95)';
        option.style.filter = 'grayscale(1)';
        
        setTimeout(function() {
            option.style.transform = '';
            option.style.filter = '';
        }, 200);
        
        // Show lock warning modal
        showLockWarning();
    }
    
    /**
     * 🎭 MODAL EVENT LISTENERS
     */
    function setupModalListeners() {
        // Confirmation modal (only exists if not locked)
        if (!isLocked) {
            const confirmModalCancel = document.querySelector('.emoji-modal .modal-button.cancel');
            const confirmModalConfirm = document.querySelector('.emoji-modal .modal-button.confirm');
            
            if (confirmModalCancel) {
                confirmModalCancel.addEventListener('click', closeConfirmationModal);
            }
            
            if (confirmModalConfirm) {
                console.log('✅ Setting up confirmation button listener for unlocked state');
                
                // Remove any existing listeners by cloning the button
                const newConfirmButton = confirmModalConfirm.cloneNode(true);
                confirmModalConfirm.parentNode.replaceChild(newConfirmButton, confirmModalConfirm);
                
                // Add our WORKING listener
                newConfirmButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('🎯 Confirmation button clicked with emoji:', selectedEmoji);
                    
                    if (!selectedEmoji) {
                        console.error('❌ No emoji selected for confirmation!');
                        return;
                    }
                    
                    confirmEmojiSelection();
                });
                
                console.log('✅ Confirmation button listener attached for unlocked state');
            } else {
                console.log('ℹ️ No confirmation button found (locked state)');
            }
            
            // Close modals on overlay click
            if (elements.modalOverlay) {
                elements.modalOverlay.addEventListener('click', function(e) {
                    if (e.target === elements.modalOverlay) {
                        closeConfirmationModal();
                    }
                });
            }
        }
        
        // Lock warning modal (only exists if locked)
        if (isLocked) {
            const lockWarningClose = document.querySelector('.lock-warning-modal .modal-button.close');
            if (lockWarningClose) {
                lockWarningClose.addEventListener('click', closeLockWarning);
            }
            
            if (elements.lockWarningOverlay) {
                elements.lockWarningOverlay.addEventListener('click', function(e) {
                    if (e.target === elements.lockWarningOverlay) {
                        closeLockWarning();
                    }
                });
            }
        }
    }
    
    /**
     * 🎨 INITIALIZE DEFAULT STATE
     */
    function initializeDefaultState() {
        console.log('🎨 Initializing default state with lock status:', isLocked ? 'LOCKED' : 'UNLOCKED');
        
        // Show first category by default
        setTimeout(function() {
            showCategory('mystical');
        }, 200);
        
        if (isLocked) {
            console.log('🔒 Locked state: Disabling all emoji interactions for', daysRemaining, 'days');
            
            // Additional locked state styling
            elements.emojiOptions.forEach(function(option) {
                option.style.cursor = 'not-allowed';
                option.style.opacity = '0.6';
            });
        }
        
        console.log('🎨 Default state initialized with proper lock handling');
    }
    
    /**
     * ✨ CATEGORY SWITCHING MAGIC
     */
    function handleCategorySwitch(e) {
        e.preventDefault();
        console.log('🎯 Category switch triggered');
        
        const category = e.currentTarget.getAttribute('data-category');
        const categoryColor = e.currentTarget.getAttribute('data-color');
        
        console.log('🎯 Switching to category:', category, 'with color:', categoryColor);
        
        if (category) {
            showCategory(category, categoryColor);
            playTabSwitchAnimation(e.currentTarget);
        } else {
            console.error('❌ No category attribute found on tab');
        }
    }
    
    /**
     * 🎯 SHOW CATEGORY WITH SMOOTH TRANSITION
     */
    function showCategory(category, categoryColor) {
        if (!categoryColor) {
            categoryColor = '#aed604';
        }
        
        console.log('🎯 showCategory called with:', category);
        
        // Update tab states
        elements.categoryTabs.forEach(function(tab) {
            tab.classList.remove('active');
            if (tab.getAttribute('data-category') === category) {
                tab.classList.add('active');
                console.log('✅ Tab marked as active:', category);
            }
        });
        
        // Hide all grids first
        elements.emojiGrids.forEach(function(grid) {
            grid.style.display = 'none';
            grid.classList.remove('active');
            console.log('🙈 Hiding grid:', grid.getAttribute('data-category'));
        });
        
        // Find and show target grid
        const targetGrid = document.querySelector('[data-category="' + category + '"].emoji-grid');
        console.log('🎯 Looking for grid with category:', category);
        console.log('🎯 Found target grid:', targetGrid);
        
        if (targetGrid) {
            console.log('✅ Showing grid for category:', category);
            
            // Show the grid
            targetGrid.style.display = 'block';
            targetGrid.classList.add('active');
            
            // Add smooth animation
            targetGrid.style.opacity = '0';
            targetGrid.style.transform = 'translateY(10px)';
            
            // Trigger reflow
            targetGrid.offsetHeight;
            
            // Animate in
            setTimeout(function() {
                targetGrid.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                targetGrid.style.opacity = '1';
                targetGrid.style.transform = 'translateY(0)';
            }, 50);
            
            // Update category color accent
            updateCategoryAccent(categoryColor);
            
            console.log('🌟 Category switch complete!');
        } else {
            console.error('❌ Target grid not found for category:', category);
            console.log('🔍 Available grids:', document.querySelectorAll('.emoji-grid'));
        }
        
        currentCategory = category;
    }
    
    /**
     * 🌈 UPDATE CATEGORY COLOR ACCENT
     */
    function updateCategoryAccent(color) {
        const activeTab = document.querySelector('.category-tab.active');
        if (activeTab) {
            activeTab.style.borderColor = color;
            activeTab.style.boxShadow = '0 0 20px ' + color + '20';
        }
    }
    
    /**
     * 😊 EMOJI SELECTION HANDLER
     */
    function handleEmojiSelect(e) {
        // Check lock status before allowing selection
        if (isLocked) {
            console.log('🔒 Emoji selection blocked - identity is locked');
            handleLockedEmojiClick(e);
            return;
        }
        
        console.log('🎯 Emoji selected in unlocked state');
        
        const emoji = e.currentTarget.getAttribute('data-emoji');
        if (emoji) {
            selectEmoji(emoji, e.currentTarget);
        }
    }
    
    /**
     * ✨ Handle emoji selection animations
     */
    function selectEmoji(emoji, element) {
        // Double-check lock status
        if (isLocked) {
            console.log('🔒 Selection blocked by lock check');
            return;
        }
        
        // Remove previous selections
        elements.emojiOptions.forEach(function(option) {
            option.classList.remove('selected');
        });
        
        // Add selection to current emoji
        element.classList.add('selected');
        selectedEmoji = emoji;
        
        console.log('✨ Selected emoji:', selectedEmoji);
        
        // Play selection animation
        playEmojiSelectionAnimation(element);
        
        // Show preview after animation
        setTimeout(function() {
            enterPreviewMode(emoji);
        }, 600);
    }
    
    /**
     * 👁️ ENTER PREVIEW MODE
     */
    function enterPreviewMode(emoji) {
        // Block preview mode if locked
        if (isLocked) {
            console.log('🔒 Preview mode blocked - identity is locked');
            return;
        }
        
        previewMode = true;
        selectedEmoji = emoji; // Make sure it's set
        
        console.log('👁️ Entering preview mode with emoji:', emoji);
        
        // Update all preview emojis
        elements.previewEmojis.forEach(function(previewEmoji) {
            previewEmoji.textContent = emoji;
        });
        
        // Update modal emoji
        const modalEmojiLarge = document.querySelector('.modal-emoji-large');
        if (modalEmojiLarge) {
            modalEmojiLarge.textContent = emoji;
        }
        
        // Update confirm button data
        const confirmButton = document.querySelector('.preview-action.confirm');
        if (confirmButton) {
            confirmButton.setAttribute('data-emoji', emoji);
        }
        
        // Show preview section with animation
        if (elements.previewSection) {
            elements.previewSection.style.display = 'block';
            elements.previewSection.style.opacity = '0';
            elements.previewSection.style.transform = 'translateY(30px)';
            
            // Trigger reflow
            elements.previewSection.offsetHeight;
            
            elements.previewSection.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
            elements.previewSection.style.opacity = '1';
            elements.previewSection.style.transform = 'translateY(0)';
        }
        
        // Scroll to preview section smoothly
        setTimeout(function() {
            if (elements.previewSection) {
                elements.previewSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }, 300);
    }
    
    /**
     * ← EXIT PREVIEW MODE
     */
    function exitPreviewMode() {
        previewMode = false;
        
        // Hide preview section with animation
        if (elements.previewSection) {
            elements.previewSection.style.opacity = '0';
            elements.previewSection.style.transform = 'translateY(30px)';
            
            setTimeout(function() {
                elements.previewSection.style.display = 'none';
            }, 300);
        }
        
        // Clear selection
        elements.emojiOptions.forEach(function(option) {
            option.classList.remove('selected');
        });
        
        selectedEmoji = null;
    }
    
    /**
     * 🔒 SHOW CONFIRMATION MODAL
     */
    function showConfirmationModal() {
        // Block confirmation if locked
        if (isLocked) {
            console.log('🔒 Confirmation blocked - identity is locked');
            showLockWarning();
            return;
        }
        
        // Check for globally set emoji from identity system
        if (!selectedEmoji && window.globalSelectedEmoji) {
            selectedEmoji = window.globalSelectedEmoji;
            console.log('✅ Retrieved selectedEmoji from global:', selectedEmoji);
        }
        
        if (!selectedEmoji) {
            console.error('❌ No emoji selected for confirmation modal!');
            return;
        }
        
        console.log('🔒 Showing confirmation modal for emoji:', selectedEmoji);
        
        // Update modal content
        const modalEmojiLarge = document.querySelector('.modal-emoji-large');
        const modalEmojiName = document.querySelector('.modal-emoji-name');
        
        if (modalEmojiLarge) {
            modalEmojiLarge.textContent = selectedEmoji;
        }
        
        if (modalEmojiName) {
            modalEmojiName.textContent = selectedEmoji;
        }
        
        // Show modal with animation
        if (elements.modalOverlay) {
            elements.modalOverlay.style.display = 'flex';
            elements.modalOverlay.style.opacity = '0';
            
            // Trigger reflow
            elements.modalOverlay.offsetHeight;
            
            elements.modalOverlay.style.transition = 'opacity 0.3s ease';
            elements.modalOverlay.style.opacity = '1';
        }
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }
    
    /**
     * ❌ CLOSE CONFIRMATION MODAL
     */
    function closeConfirmationModal() {
        if (elements.modalOverlay) {
            elements.modalOverlay.style.opacity = '0';
            
            setTimeout(function() {
                elements.modalOverlay.style.display = 'none';
            }, 300);
        }
        
        // Restore body scroll
        document.body.style.overflow = '';
    }
    
    /**
     * ✅ CONFIRM EMOJI SELECTION
     */
    function confirmEmojiSelection() {
        // Final lock check before confirming
        if (isLocked) {
            console.log('🔒 Confirmation blocked by final lock check');
            closeConfirmationModal();
            showLockWarning();
            return;
        }
        
        if (!selectedEmoji) {
            console.error('❌ Cannot confirm - no emoji selected!');
            return;
        }
        
        console.log('✅ CONFIRMING EMOJI SELECTION WITH 30-DAY LOCK:', selectedEmoji);
        
        // Close modal first
        closeConfirmationModal();
        
        // Show loading state
        showLoadingState();
        
        // Save the emoji identity with proper lock
        setTimeout(function() {
            saveEmojiIdentityWithLock(selectedEmoji);
        }, 1000);
    }
    
    /**
     * 💾 SAVE EMOJI IDENTITY WITH 30-DAY LOCK
     */
    function saveEmojiIdentityWithLock(emoji) {
        console.log('💾 Saving emoji identity with 30-day lock:', emoji);
        
        // Get nonce for security
        const nonce = window.emojiPickerData?.nonce || window.emojiPickerNonce || '';
        const ajaxurl = window.emojiPickerData?.ajaxurl || '/wp-admin/admin-ajax.php';
        
        console.log('🔐 AJAX details:', { nonce: nonce ? 'Present' : 'Missing', ajaxurl });
        
        if (!nonce) {
            console.error('❌ No nonce available!');
            hideLoadingState();
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
                console.log('🎭 Found identity for emoji:', emoji, identity);
            }
        }
        
        // Prepare form data
        const formData = new FormData();
        formData.append('action', 'save_emoji_identity');
        formData.append('emoji', emoji);
        formData.append('identity_name', identityName);
        formData.append('identity_bio', identityBio);
        formData.append('nonce', nonce);
        
        // Make AJAX request with proper error handling
        fetch(ajaxurl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(function(response) {
            console.log('🌐 AJAX response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(function(data) {
            hideLoadingState();
            console.log('📦 AJAX response data:', data);
            
            if (data.success) {
                console.log('✅ Successfully saved emoji identity with 30-day lock!');
                
                // Update global lock status
                isLocked = true;
                window.emojiPickerIsLocked = true;
                
                showSuccessCelebrationWithLock(emoji, identityName, identityBio);
                updateCurrentIdentityWithLock(emoji, identityName, identityBio);
            } else {
                console.error('❌ Failed to save emoji identity:', data.data);
                
                // Check if it's a lock-related error
                if (data.data && data.data.includes('locked')) {
                    showLockWarning();
                } else {
                    showErrorMessage(data.data || 'Failed to save emoji identity');
                }
            }
        })
        .catch(function(error) {
            hideLoadingState();
            console.error('❌ Save network error:', error);
            showErrorMessage('Network error: ' + error.message);
        });
    }
    
    /**
     * 🎉 SHOW SUCCESS CELEBRATION WITH LOCK CONFIRMATION
     */
    function showSuccessCelebrationWithLock(emoji, identityName, identityBio) {
        console.log('🎉 Showing success with 30-day lock confirmation:', { emoji, identityName, identityBio });
        
        // Update success emoji
        const successEmojiLarge = document.querySelector('.success-emoji-large');
        if (successEmojiLarge) {
            successEmojiLarge.textContent = emoji;
        }
        
        // Enhanced success message with lock info
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
                    <strong style="color: #ff9800;">🔒 Your Angel identity is now locked for 30 days!</strong>
                    <br>You can change it again after the lock period expires.
                </div>
            `;
            
            successMessage.innerHTML = messageHTML;
        }
        
        if (elements.successCelebration) {
            elements.successCelebration.style.display = 'flex';
            elements.successCelebration.style.opacity = '0';
            
            // Trigger reflow
            elements.successCelebration.offsetHeight;
            
            elements.successCelebration.style.transition = 'opacity 0.5s ease';
            elements.successCelebration.style.opacity = '1';
            
            // Add celebration particles
            createCelebrationParticles();
            
            // Play success sound
            playSuccessSound();
            
            // Set up success buttons
            setupSuccessButtons();
        }
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }
    
    /**
     * 🎯 SUCCESS BUTTONS SETUP
     */
    function setupSuccessButtons() {
        console.log('🎯 Setting up success buttons...');
        
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
                console.log('🏠 Redirect to Angel Hub');
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
                console.log('❌ Close only');
                closeModalOnly();
            });
        }
        
        // Background click handler
        if (elements.successCelebration) {
            elements.successCelebration.addEventListener('click', function(e) {
                if (e.target === elements.successCelebration) {
                    closeModalOnly();
                }
            });
        }
    }
    
    /**
     * 🔒 SHOW LOCK WARNING
     */
    function showLockWarning() {
        console.log('🔒 Showing lock warning for', daysRemaining, 'days remaining');
        
        if (elements.lockWarningOverlay) {
            elements.lockWarningOverlay.style.display = 'flex';
            elements.lockWarningOverlay.style.opacity = '0';
            
            // Trigger reflow
            elements.lockWarningOverlay.offsetHeight;
            
            elements.lockWarningOverlay.style.transition = 'opacity 0.3s ease';
            elements.lockWarningOverlay.style.opacity = '1';
        }
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }
    
    /**
     * ❌ CLOSE LOCK WARNING
     */
    function closeLockWarning() {
        if (elements.lockWarningOverlay) {
            elements.lockWarningOverlay.style.opacity = '0';
            
            setTimeout(function() {
                elements.lockWarningOverlay.style.display = 'none';
            }, 300);
        }
        
        // Restore body scroll
        document.body.style.overflow = '';
    }
    
    /**
     * 🏠 REDIRECT TO ANGEL HUB
     */
    function redirectToAngelHub() {
        console.log('🏠 Redirect to Angel Hub...');
        
        if (elements.successCelebration) {
            elements.successCelebration.style.opacity = '0';
            
            setTimeout(function() {
                elements.successCelebration.style.display = 'none';
                
                const angelHubUrl = window.emojiPickerBackUrl || '/';
                console.log('🏠 Redirect to:', angelHubUrl);
                window.location.href = angelHubUrl;
            }, 500);
        }
        
        document.body.style.overflow = '';
    }
    
    /**
     * ❌ CLOSE MODAL ONLY (NO REDIRECT)
     */
    function closeModalOnly() {
        console.log('❌ Close modal only...');
        
        if (elements.successCelebration) {
            elements.successCelebration.style.opacity = '0';
            
            setTimeout(function() {
                elements.successCelebration.style.display = 'none';
            }, 500);
        }
        
        document.body.style.overflow = '';
    }
    
    /**
     * ✅ CLOSE SUCCESS CELEBRATION
     */
    function closeSuccessCelebration() {
        return redirectToAngelHub();
    }
    
    /**
     * ❌ CLOSE SUCCESS CELEBRATION ONLY
     */
    function closeSuccessCelebrationOnly() {
        return closeModalOnly();
    }
    
    /**
     * 🔄 UPDATE CURRENT IDENTITY WITH LOCK STATUS
     */
    function updateCurrentIdentityWithLock(emoji, identityName, identityBio) {
        console.log('🔄 Updating current identity with lock:', { emoji, identityName, identityBio });
        
        const currentEmojiLarge = document.querySelector('.current-emoji-large');
        if (currentEmojiLarge) {
            currentEmojiLarge.textContent = emoji;
        }
        
        // Update lock status to show locked
        const lockStatus = document.querySelector('.lock-status');
        if (lockStatus) {
            lockStatus.className = 'lock-status locked';
            lockStatus.innerHTML = '<span class="lock-icon">🔒</span><span class="lock-text">Locked for 30 days</span>';
        }
        
        // Show countdown bar
        const lockCountdown = document.querySelector('.lock-countdown');
        if (lockCountdown) {
            lockCountdown.style.display = 'block';
            // Start countdown at 0% (just locked)
            const countdownFill = lockCountdown.querySelector('.countdown-fill');
            if (countdownFill) {
                countdownFill.style.width = '0%';
            }
            const countdownText = lockCountdown.querySelector('.countdown-text');
            if (countdownText) {
                countdownText.textContent = 'Next change available in 30 days';
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
    
    // Rest of the utility functions remain the same...
    
    /**
     * ⏳ SHOW LOADING STATE
     */
    function showLoadingState() {
        const container = document.querySelector('.emoji-container');
        if (container) {
            const loader = document.createElement('div');
            loader.className = 'emoji-loading';
            loader.innerHTML = 'Locking your Angel identity for 30 days...';
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
            
            document.body.appendChild(loader);
        }
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
     * 🎨 PLAY TAB SWITCH ANIMATION
     */
    function playTabSwitchAnimation(tab) {
        const ripple = document.createElement('div');
        ripple.style.position = 'absolute';
        ripple.style.borderRadius = '50%';
        ripple.style.background = 'rgba(174, 214, 4, 0.4)';
        ripple.style.transform = 'scale(0)';
        ripple.style.animation = 'ripple 0.6s linear';
        ripple.style.left = '50%';
        ripple.style.top = '50%';
        ripple.style.width = '20px';
        ripple.style.height = '20px';
        ripple.style.marginLeft = '-10px';
        ripple.style.marginTop = '-10px';
        
        tab.style.position = 'relative';
        tab.appendChild(ripple);
        
        setTimeout(function() {
            ripple.remove();
        }, 600);
        
        if (!document.querySelector('#ripple-animation-css')) {
            const style = document.createElement('style');
            style.id = 'ripple-animation-css';
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    /**
     * ✨ PLAY EMOJI SELECTION ANIMATION
     */
    function playEmojiSelectionAnimation(element) {
        element.style.transform = 'scale(1.2) rotate(10deg)';
        element.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        
        setTimeout(function() {
            element.style.transform = 'scale(1) rotate(0deg)';
        }, 300);
        
        createSelectionParticles(element);
    }
    
    /**
     * 🎊 CREATE SELECTION PARTICLES
     */
    function createSelectionParticles(element) {
        const rect = element.getBoundingClientRect();
        const particles = ['✨', '💫', '⭐'];
        
        for (let i = 0; i < 6; i++) {
            const particle = document.createElement('div');
            particle.textContent = particles[Math.floor(Math.random() * particles.length)];
            particle.style.position = 'fixed';
            particle.style.left = rect.left + rect.width / 2 + 'px';
            particle.style.top = rect.top + rect.height / 2 + 'px';
            particle.style.fontSize = '1rem';
            particle.style.pointerEvents = 'none';
            particle.style.zIndex = '9999';
            particle.style.animation = 'selectionParticle ' + (0.8 + Math.random() * 0.4) + 's ease-out forwards';
            
            const angle = (Math.PI * 2 * i) / 6;
            const distance = 30 + Math.random() * 20;
            particle.style.setProperty('--end-x', Math.cos(angle) * distance + 'px');
            particle.style.setProperty('--end-y', Math.sin(angle) * distance + 'px');
            
            document.body.appendChild(particle);
            
            setTimeout(function() {
                particle.remove();
            }, 1200);
        }
        
        if (!document.querySelector('#selection-particle-css')) {
            const style = document.createElement('style');
            style.id = 'selection-particle-css';
            style.textContent = `
                @keyframes selectionParticle {
                    0% {
                        transform: translate(0, 0) scale(1);
                        opacity: 1;
                    }
                    100% {
                        transform: translate(var(--end-x), var(--end-y)) scale(0);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    /**
     * 🎨 ADD HOVER EFFECTS
     */
    function addHoverEffect(e) {
        const element = e.currentTarget;
        const emoji = element.querySelector('.emoji-char');
        
        if (emoji) {
            if (isLocked) {
                // Subtle hover for locked state
                emoji.style.transform = 'scale(1.05)';
                emoji.style.filter = 'grayscale(0.8) drop-shadow(0 2px 4px rgba(255, 152, 0, 0.3))';
            } else {
                // Full hover for unlocked state
                emoji.style.transform = 'scale(1.1) rotate(5deg)';
                emoji.style.filter = 'drop-shadow(0 4px 8px rgba(174, 214, 4, 0.3))';
            }
        }
    }
    
    function removeHoverEffect(e) {
        const element = e.currentTarget;
        const emoji = element.querySelector('.emoji-char');
        
        if (emoji && !element.classList.contains('selected')) {
            emoji.style.transform = 'scale(1) rotate(0deg)';
            if (isLocked) {
                emoji.style.filter = 'grayscale(0.5) drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3))';
            } else {
                emoji.style.filter = 'drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3))';
            }
        }
    }
    
    /**
     * ⌨️ KEYBOARD NAVIGATION
     */
    function handleGlobalKeydown(e) {
        switch (e.key) {
            case 'Escape':
                if (elements.modalOverlay && elements.modalOverlay.style.display === 'flex') {
                    closeConfirmationModal();
                } else if (elements.lockWarningOverlay && elements.lockWarningOverlay.style.display === 'flex') {
                    closeLockWarning();
                } else if (previewMode && !isLocked) {
                    exitPreviewMode();
                }
                break;
                
            case 'Enter':
                if (previewMode && selectedEmoji && !isLocked) {
                    showConfirmationModal();
                }
                break;
                
            case 'ArrowLeft':
            case 'ArrowRight':
                if (!previewMode) {
                    handleTabNavigation(e.key === 'ArrowRight');
                }
                break;
        }
    }
    
    function handleCategoryKeydown(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            handleCategorySwitch(e);
        }
    }
    
    function handleEmojiKeydown(e) {
        if ((e.key === 'Enter' || e.key === ' ') && !isLocked) {
            e.preventDefault();
            handleEmojiSelect(e);
        } else if (isLocked) {
            e.preventDefault();
            handleLockedEmojiClick(e);
        }
    }
    
    function handleTabNavigation(forward) {
        const tabs = Array.from(elements.categoryTabs);
        const currentIndex = tabs.findIndex(function(tab) {
            return tab.classList.contains('active');
        });
        let newIndex;
        
        if (forward) {
            newIndex = (currentIndex + 1) % tabs.length;
        } else {
            newIndex = currentIndex === 0 ? tabs.length - 1 : currentIndex - 1;
        }
        
        tabs[newIndex].click();
        tabs[newIndex].focus();
    }
    
    /**
     * 📱 MOBILE TOUCH ENHANCEMENTS
     */
    function addTouchEnhancements() {
        elements.emojiOptions.forEach(function(option) {
            option.addEventListener('touchend', function(e) {
                e.preventDefault();
                if (isLocked) {
                    handleLockedEmojiClick(e);
                } else {
                    option.click();
                }
            });
        });
        
        let touchTimeout;
        elements.emojiOptions.forEach(function(option) {
            option.addEventListener('touchstart', function() {
                clearTimeout(touchTimeout);
                option.style.transform = 'scale(0.95)';
            });
            
            option.addEventListener('touchend', function() {
                touchTimeout = setTimeout(function() {
                    option.style.transform = '';
                }, 150);
            });
        });
    }
    
    /**
     * ✨ ADD ENTRANCE ANIMATIONS
     */
    function addEntranceAnimations() {
        elements.categoryTabs.forEach(function(tab, index) {
            tab.style.opacity = '0';
            tab.style.transform = 'translateY(20px)';
            
            setTimeout(function() {
                tab.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                tab.style.opacity = '1';
                tab.style.transform = 'translateY(0)';
            }, 100 + (index * 100));
        });
        
        setTimeout(function() {
            const visibleOptions = document.querySelectorAll('.emoji-grid.active .emoji-option');
            visibleOptions.forEach(function(option, index) {
                option.style.opacity = '0';
                option.style.transform = 'scale(0.8) translateY(20px)';
                
                setTimeout(function() {
                    option.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                    option.style.opacity = '1';
                    option.style.transform = 'scale(1) translateY(0)';
                }, 50 + (index * 30));
            });
        }, 500);
    }
    
    // 🎯 Expose public API for external use
    window.EmojiIdentityPicker = {
        selectEmoji: selectEmoji,
        showCategory: showCategory,
        enterPreviewMode: enterPreviewMode,
        exitPreviewMode: exitPreviewMode,
        selectedEmoji: selectedEmoji,
        previewMode: previewMode,
        isLocked: isLocked,
        daysRemaining: daysRemaining
    };
    
    // 🔧 DEBUG: Add manual trigger for testing
    window.debugEmojiPicker = function() {
        console.log('🔍 Debug info:', {
            elements: elements,
            currentCategory: currentCategory,
            selectedEmoji: selectedEmoji,
            isLocked: isLocked,
            daysRemaining: daysRemaining,
            previewMode: previewMode
        });
        
        showCategory('mystical');
    };
    
})();