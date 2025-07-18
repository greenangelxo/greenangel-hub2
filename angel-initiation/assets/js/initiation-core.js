/**
 * Angel Initiation Core
 * Handles step navigation, progress tracking, and AJAX communication
 */

class AngelInitiationCore {
    constructor() {
        this.currentStep = '';
        this.userId = 0;
        this.ajaxUrl = '';
        this.nonce = '';
        this.isTransitioning = false;
        
        this.stepOrder = ['name_generator', 'tribe_sorting', 'dob_ritual', 'final_blessing'];
        this.stepNames = {
            'name_generator': 'Name Generator',
            'tribe_sorting': 'Tribe Sorting',
            'dob_ritual': 'DOB Ritual',
            'final_blessing': 'Final Blessing'
        };
        
        this.init();
    }
    
    init() {
        // Get config from global object
        console.log('AngelInitiationCore initializing...');
        console.log('window.angelInitiation:', window.angelInitiation);
        
        if (window.angelInitiation) {
            this.currentStep = window.angelInitiation.currentStep;
            this.userId = window.angelInitiation.userId;
            this.ajaxUrl = window.angelInitiation.ajaxUrl;
            this.nonce = window.angelInitiation.nonce;
            console.log('Configuration loaded:', {
                currentStep: this.currentStep,
                userId: this.userId,
                ajaxUrl: this.ajaxUrl,
                nonce: this.nonce
            });
        } else {
            console.error('window.angelInitiation not found!');
        }
        
        this.setupEventListeners();
        this.showCurrentStep();
        this.showStep(this.currentStep);
    }
    
    setupEventListeners() {
        // Core-level event listeners only (individual steps handle their own events)
        // Completion Events
        const enterDashboardBtn = document.getElementById('enter-dashboard-btn');
        
        if (enterDashboardBtn) {
            enterDashboardBtn.addEventListener('click', () => this.handleEnterDashboard());
        }
        
        // Add testing navigation buttons
        this.createTestingNavigation();
    }
    
    showCurrentStep() {
        this.showStep(this.currentStep);
    }
    
    showStep(stepName) {
        if (this.isTransitioning) return;
        
        console.log('[showStep] Showing step:', stepName);
        
        // Hide all steps
        const steps = document.querySelectorAll('.initiation-step');
        console.log('[showStep] Found', steps.length, 'step elements to hide');
        steps.forEach(step => step.style.display = 'none');
        
        // Hide completion celebration
        const completion = document.getElementById('completion-celebration');
        if (completion) {
            completion.style.display = 'none';
        }
        
        // Show current step
        const stepId = 'step-' + stepName.replace(/_/g, '-');
        console.log('[showStep] Looking for element with ID:', stepId);
        const currentStepEl = document.getElementById(stepId);
        
        if (currentStepEl) {
            console.log('[showStep] Found step element, showing it');
            currentStepEl.style.display = 'block';
            currentStepEl.style.animation = 'fadeInCard 0.8s ease-out';
        } else {
            console.error('[showStep] Step element not found!', stepId);
        }
        
        // Update progress
        this.updateProgress(stepName);
        
        // Update step counter
        const currentIndex = this.stepOrder.indexOf(stepName);
        const currentStepSpan = document.querySelector('.current-step');
        if (currentStepSpan) {
            currentStepSpan.textContent = currentIndex + 1;
        }
    }
    
    updateProgress(stepName) {
        const stepIndex = this.stepOrder.indexOf(stepName);
        const progress = ((stepIndex + 1) / this.stepOrder.length) * 100;
        
        const progressFill = document.getElementById('progress-fill');
        if (progressFill) {
            progressFill.style.width = progress + '%';
        }
    }
    
    showLoading(message = 'Channeling cosmic energy...') {
        const loadingOverlay = document.getElementById('cosmic-loading-overlay');
        const loadingText = document.querySelector('.loading-text');
        
        if (loadingOverlay) {
            loadingOverlay.style.display = 'flex';
            loadingOverlay.style.animation = 'fadeIn 0.3s ease-out';
        }
        
        if (loadingText) {
            loadingText.textContent = message;
        }
    }
    
    hideLoading() {
        const loadingOverlay = document.getElementById('cosmic-loading-overlay');
        if (loadingOverlay) {
            loadingOverlay.style.animation = 'fadeOut 0.3s ease-out';
            setTimeout(() => {
                loadingOverlay.style.display = 'none';
            }, 300);
        }
    }
    
    transitionToStep(nextStep) {
        console.log('[transitionToStep] Called with nextStep:', nextStep);
        console.log('[transitionToStep] Current step:', this.currentStep);
        console.log('[transitionToStep] Is transitioning:', this.isTransitioning);
        
        if (this.isTransitioning) {
            console.log('[transitionToStep] Already transitioning, skipping');
            return;
        }
        
        this.isTransitioning = true;
        
        // Find currently visible step
        const allSteps = document.querySelectorAll('.initiation-step');
        let currentStepEl = null;
        allSteps.forEach(step => {
            const computed = window.getComputedStyle(step);
            if (step.style.display === 'block' || computed.display === 'block') {
                currentStepEl = step;
            }
        });
        
        console.log('[transitionToStep] Current visible step element:', currentStepEl);
        
        if (currentStepEl) {
            console.log('[transitionToStep] Fading out current step');
            currentStepEl.style.animation = 'fadeOutCard 0.5s ease-out';
            
            // Use arrow function to preserve 'this' context
            setTimeout(() => {
                console.log('[transitionToStep] Fade out complete, hiding element');
                if (currentStepEl) {
                    currentStepEl.style.display = 'none';
                }
                
                console.log('[transitionToStep] Setting currentStep to:', nextStep);
                this.currentStep = nextStep;
                
                // IMPORTANT: Set isTransitioning to false BEFORE calling showStep
                console.log('[transitionToStep] Setting isTransitioning to false');
                this.isTransitioning = false;
                
                console.log('[transitionToStep] Now calling showStep with:', nextStep);
                try {
                    this.showStep(nextStep);
                } catch (error) {
                    console.error('[transitionToStep] Error in showStep:', error);
                }
            }, 500);
        } else {
            console.log('[transitionToStep] No current step visible, showing next step directly');
            this.currentStep = nextStep;
            this.isTransitioning = false;
            this.showStep(nextStep);
        }
    }
    
    // Also, let's add a direct transition function that bypasses the animation for testing:
    forceTransitionToStep(nextStep) {
        console.log('[forceTransitionToStep] Forcing transition to:', nextStep);
        
        // Hide all steps
        const steps = document.querySelectorAll('.initiation-step');
        steps.forEach(step => {
            step.style.display = 'none';
        });
        
        // Update current step
        this.currentStep = nextStep;
        this.isTransitioning = false;
        
        // Show the next step
        this.showStep(nextStep);
    }
    
    // Add this debugging function to manually trigger transitions:
    debugTransition(stepName) {
        console.log('[debugTransition] Manually transitioning to:', stepName);
        this.transitionToStep(stepName);
    }
    
    createTestingNavigation() {
        // Create testing navigation container
        const testingNav = document.createElement('div');
        testingNav.id = 'testing-navigation';
        testingNav.style.cssText = `
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(0, 0, 0, 0.9);
            padding: 15px;
            border-radius: 10px;
            border: 2px solid #9333ea;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        `;
        
        // Add title
        const title = document.createElement('div');
        title.textContent = '🧪 Testing Navigation';
        title.style.cssText = `
            color: #9333ea;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
            text-align: center;
        `;
        testingNav.appendChild(title);
        
        // Add step buttons
        this.stepOrder.forEach((step, index) => {
            const button = document.createElement('button');
            button.textContent = `${index + 1}. ${this.stepNames[step]}`;
            button.style.cssText = `
                background: ${this.currentStep === step ? '#9333ea' : 'rgba(147, 51, 234, 0.3)'};
                color: white;
                border: 1px solid #9333ea;
                padding: 8px 12px;
                border-radius: 5px;
                cursor: pointer;
                font-size: 12px;
                transition: all 0.2s ease;
            `;
            
            button.addEventListener('click', () => {
                this.goToTestingStep(step);
            });
            
            button.addEventListener('mouseenter', () => {
                if (this.currentStep !== step) {
                    button.style.background = 'rgba(147, 51, 234, 0.6)';
                }
            });
            
            button.addEventListener('mouseleave', () => {
                if (this.currentStep !== step) {
                    button.style.background = 'rgba(147, 51, 234, 0.3)';
                }
            });
            
            testingNav.appendChild(button);
        });
        
        // Add reset button
        const resetBtn = document.createElement('button');
        resetBtn.textContent = '🔄 Reset All';
        resetBtn.style.cssText = `
            background: #ef4444;
            color: white;
            border: 1px solid #ef4444;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            margin-top: 5px;
            transition: all 0.2s ease;
        `;
        
        resetBtn.addEventListener('click', () => {
            this.resetAllSteps();
        });
        
        resetBtn.addEventListener('mouseenter', () => {
            resetBtn.style.background = '#dc2626';
        });
        
        resetBtn.addEventListener('mouseleave', () => {
            resetBtn.style.background = '#ef4444';
        });
        
        testingNav.appendChild(resetBtn);
        
        // Add to body
        document.body.appendChild(testingNav);
    }
    
    goToTestingStep(stepName) {
        console.log('[Testing] Navigating to step:', stepName);
        
        // Update current step
        this.currentStep = stepName;
        
        // Show the step
        this.showStep(stepName);
        
        // Update navigation buttons
        this.updateTestingNavigation();
        
        // Reset step-specific state
        this.resetStepState(stepName);
    }
    
    resetStepState(stepName) {
        console.log('[Testing] Resetting state for step:', stepName);
        
        switch(stepName) {
            case 'name_generator':
                if (window.nameGenerator) {
                    // Reset name generator state
                    window.nameGenerator.rollCount = 0;
                    window.nameGenerator.currentName = '';
                    window.nameGenerator.isLocked = false;
                    window.nameGenerator.isLocking = false;
                    window.nameGenerator.isRolling = false;
                    window.nameGenerator.updateUI();
                    
                    // Reset display
                    const nameDisplay = document.getElementById('generated-name');
                    if (nameDisplay) {
                        nameDisplay.textContent = 'Roll the dice to discover your name...';
                        nameDisplay.className = 'generated-name';
                    }
                }
                break;
                
            case 'tribe_sorting':
                if (window.cosmicShellGame) {
                    // Reset shell game state
                    window.cosmicShellGame.selectedTribe = null;
                    window.cosmicShellGame.isShuffling = false;
                    window.cosmicShellGame.shuffleComplete = false;
                    
                    // Reset UI
                    const shuffleBtn = document.getElementById('start-shuffle-btn');
                    if (shuffleBtn) {
                        shuffleBtn.style.display = 'block';
                        shuffleBtn.disabled = false;
                        shuffleBtn.textContent = 'Begin the Cosmic Shuffle 🪄';
                    }
                    
                    const acceptBtn = document.getElementById('accept-tribe-btn');
                    if (acceptBtn) {
                        acceptBtn.style.display = 'none';
                    }
                    
                    const revelation = document.getElementById('tribe-revelation');
                    if (revelation) {
                        revelation.style.display = 'none';
                        revelation.classList.remove('revelation-entrance');
                    }
                    
                    const choicePrompt = document.getElementById('choice-prompt');
                    if (choicePrompt) {
                        choicePrompt.style.display = 'none';
                        choicePrompt.classList.remove('fade-in-prompt');
                    }
                    
                    // Reset cups
                    window.cosmicShellGame.cups.forEach(cup => {
                        if (cup) {
                            cup.classList.remove('selectable', 'hovering', 'ready-to-choose', 'chosen-cup');
                            cup.style.animation = '';
                            cup.style.animationDelay = '';
                            cup.style.transform = '';
                            
                            const lid = cup.querySelector('.cup-lid');
                            const hiddenTribe = cup.querySelector('.hidden-tribe');
                            
                            if (lid) {
                                lid.style.animation = '';
                                lid.style.transform = 'translateX(-50%)';
                                lid.style.opacity = '1';
                            }
                            
                            if (hiddenTribe) {
                                hiddenTribe.style.animation = '';
                                hiddenTribe.style.opacity = '0';
                                hiddenTribe.style.transform = 'scale(0)';
                            }
                        }
                    });
                }
                break;
                
            case 'dob_ritual':
                // Reset DOB form
                const monthSelect = document.getElementById('birth-month');
                const yearSelect = document.getElementById('birth-year');
                const alignBtn = document.getElementById('align-cosmos-btn');
                
                if (monthSelect) monthSelect.selectedIndex = 0;
                if (yearSelect) yearSelect.selectedIndex = 0;
                if (alignBtn) {
                    alignBtn.disabled = false;
                    alignBtn.textContent = 'Align with the Cosmos 🌙';
                }
                break;
                
            case 'final_blessing':
                // Reset final blessing
                const completeBtn = document.getElementById('complete-initiation-btn');
                if (completeBtn) {
                    completeBtn.disabled = false;
                    completeBtn.textContent = 'Complete My Initiation ✨';
                }
                break;
        }
    }
    
    updateTestingNavigation() {
        const testingNav = document.getElementById('testing-navigation');
        if (!testingNav) return;
        
        const buttons = testingNav.querySelectorAll('button');
        buttons.forEach((button, index) => {
            // Skip the reset button (last button)
            if (index === buttons.length - 1) return;
            
            const stepName = this.stepOrder[index];
            if (stepName === this.currentStep) {
                button.style.background = '#9333ea';
            } else {
                button.style.background = 'rgba(147, 51, 234, 0.3)';
            }
        });
    }
    
    resetAllSteps() {
        console.log('[Testing] Resetting all steps');
        
        // Reset each step
        this.stepOrder.forEach(step => {
            this.resetStepState(step);
        });
        
        // Go back to first step
        this.goToTestingStep('name_generator');
        
        // Show notification
        window.showNotification('All steps reset! 🔄', 'success');
    }
    
    makeAjaxRequest(action, data = {}) {
        return new Promise((resolve, reject) => {
            const requestData = {
                action: 'angel_initiation_step',
                nonce: this.nonce,
                step: this.currentStep,
                action_type: action,
                ...data
            };
            
            console.log('Making AJAX request with data:', requestData);
            console.log('AJAX URL:', this.ajaxUrl);
            console.log('Nonce:', this.nonce);
            
            jQuery.ajax({
                url: this.ajaxUrl,
                type: 'POST',
                data: requestData,
                success: (response) => {
                    console.log('AJAX response:', response);
                    if (response.success) {
                        resolve(response.data);
                    } else {
                        reject(response.data || { message: 'Unknown error' });
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AJAX error:', xhr.status, xhr.statusText);
                    console.error('Response text:', xhr.responseText);
                    reject({ message: 'Network error: ' + error + ' (Status: ' + xhr.status + ')' });
                }
            });
        });
    }
    
    showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `angel-notification ${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? 'linear-gradient(135deg, #10b981, #059669)' : 'linear-gradient(135deg, #ef4444, #dc2626)'};
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            z-index: 1001;
            animation: slideInRight 0.3s ease-out;
            max-width: 300px;
            word-wrap: break-word;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
    
    // Step-specific handlers (delegated to individual step classes)
    
    handleEnterDashboard() {
        this.showLoading('Preparing your Angel Realm...');
        
        setTimeout(() => {
            window.location.href = '/account';
        }, 2000);
    }
    
    showCompletion() {
        // Hide all steps
        const steps = document.querySelectorAll('.initiation-step');
        steps.forEach(step => step.style.display = 'none');
        
        // Show completion celebration
        const completion = document.getElementById('completion-celebration');
        if (completion) {
            completion.style.display = 'block';
            completion.style.animation = 'celebrationEntrance 1s ease-out';
        }
        
        // Update progress to 100%
        const progressFill = document.getElementById('progress-fill');
        if (progressFill) {
            progressFill.style.width = '100%';
        }
        
        // Trigger celebration effects
        if (window.createConfettiExplosion) {
            setTimeout(() => {
                window.createConfettiExplosion();
            }, 500);
        }
    }
    
    addDynamicStyles() {
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
            
            @keyframes fadeInCard {
                from { 
                    opacity: 0; 
                    transform: translateY(20px) scale(0.95); 
                }
                to { 
                    opacity: 1; 
                    transform: translateY(0) scale(1); 
                }
            }
            
            @keyframes fadeOutCard {
                from { 
                    opacity: 1; 
                    transform: translateY(0) scale(1); 
                }
                to { 
                    opacity: 0; 
                    transform: translateY(-20px) scale(0.95); 
                }
            }
            
            @keyframes slideInRight {
                from { 
                    opacity: 0; 
                    transform: translateX(100px); 
                }
                to { 
                    opacity: 1; 
                    transform: translateX(0); 
                }
            }
            
            @keyframes slideOutRight {
                from { 
                    opacity: 1; 
                    transform: translateX(0); 
                }
                to { 
                    opacity: 0; 
                    transform: translateX(100px); 
                }
            }
        `;
        document.head.appendChild(style);
    }
}

// Global instance
window.angelInitiationCore = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (!window.angelInitiationCore) {
        window.angelInitiationCore = new AngelInitiationCore();
        window.angelInitiationCore.addDynamicStyles();
    }
});

// Expose core functions globally
window.showStep = function(stepName) {
    if (window.angelInitiationCore) {
        window.angelInitiationCore.showStep(stepName);
    }
};

window.transitionToStep = function(nextStep) {
    if (window.angelInitiationCore) {
        window.angelInitiationCore.transitionToStep(nextStep);
    }
};

window.showLoading = function(message) {
    if (window.angelInitiationCore) {
        window.angelInitiationCore.showLoading(message);
    }
};

window.hideLoading = function() {
    if (window.angelInitiationCore) {
        window.angelInitiationCore.hideLoading();
    }
};

window.showNotification = function(message, type) {
    if (window.angelInitiationCore) {
        window.angelInitiationCore.showNotification(message, type);
    }
};

window.showCompletion = function() {
    if (window.angelInitiationCore) {
        window.angelInitiationCore.showCompletion();
    }
};

window.makeAjaxRequest = function(action, data) {
    if (window.angelInitiationCore) {
        return window.angelInitiationCore.makeAjaxRequest(action, data);
    }
    return Promise.reject({ message: 'Core not initialized' });
};

// Debug helper to force transition
window.forceNextStep = function(stepName) {
    console.log('[Debug] Forcing transition to:', stepName);
    if (window.angelInitiationCore) {
        window.angelInitiationCore.forceTransitionToStep(stepName);
    } else {
        console.error('AngelInitiationCore not initialized!');
    }
};

// Debug helper to check current state
window.checkInitiationState = function() {
    if (window.angelInitiationCore) {
        console.log('Current Step:', window.angelInitiationCore.currentStep);
        console.log('Is Transitioning:', window.angelInitiationCore.isTransitioning);
        console.log('Step Order:', window.angelInitiationCore.stepOrder);
        
        // Check which step is visible
        const steps = document.querySelectorAll('.initiation-step');
        steps.forEach(step => {
            if (step.style.display === 'block' || window.getComputedStyle(step).display === 'block') {
                console.log('Visible Step Element:', step.id);
            }
        });
    } else {
        console.error('AngelInitiationCore not initialized!');
    }
};

// Debug helper to manually trigger next step
window.goToNextStep = function() {
    if (window.angelInitiationCore) {
        const current = window.angelInitiationCore.currentStep;
        const stepOrder = window.angelInitiationCore.stepOrder;
        const currentIndex = stepOrder.indexOf(current);
        
        if (currentIndex < stepOrder.length - 1) {
            const nextStep = stepOrder[currentIndex + 1];
            console.log('[Debug] Going from', current, 'to', nextStep);
            window.transitionToStep(nextStep);
        } else {
            console.log('[Debug] Already at last step');
        }
    }
};