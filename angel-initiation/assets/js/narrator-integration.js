/**
 * Narrator Integration
 * Connects the emoji narrator with the Angel Initiation flow
 */

class NarratorIntegration {
    constructor() {
        this.narrator = null;
        this.isInitialized = false;
        this.currentPhase = null;
        this.hasPlayedGreeting = false;
        
        // Wait for DOM and other dependencies
        this.init();
    }
    
    init() {
        // Wait for all dependencies to be ready
        if (typeof EmojiNarrator === 'undefined' || typeof NarratorScripts === 'undefined') {
            console.log('Waiting for narrator dependencies...');
            setTimeout(() => this.init(), 100);
            return;
        }
        
        // Initialize the narrator
        this.setupNarrator();
        
        // Hook into the existing initiation system
        this.setupInitiationHooks();
        
        // Start the boot sequence
        this.startBootSequence();
        
        this.isInitialized = true;
        console.log('NarratorIntegration initialized');
    }
    
    setupNarrator() {
        // Set up narrator for the current step
        this.setupNarratorForStep('name_generator');
        
        // Make narrator globally available for debugging
        window.angelNarrator = this.narrator;
    }
    
    setupNarratorForStep(stepName) {
        let containerId = 'narrator-container';
        
        // Map step names to container IDs
        switch(stepName) {
            case 'name_generator':
                containerId = 'narrator-container';
                break;
            case 'tribe_sorting':
                containerId = 'narrator-container-2';
                break;
            case 'dob_ritual':
                containerId = 'narrator-container-3';
                break;
            case 'final_blessing':
                containerId = 'narrator-container-4';
                break;
        }
        
        const container = document.getElementById(containerId);
        if (!container) {
            console.error(`Narrator container not found: ${containerId}`);
            return;
        }
        
        // Clean up existing narrator if switching
        if (this.narrator) {
            this.narrator.destroy();
        }
        
        this.narrator = new EmojiNarrator({
            container: container,
            speechBubble: null // Will be created automatically
        });
        
        // Set initial emoji and start resting animation
        this.narrator.setStatic('😊');
        this.narrator.setRestingMode();
        
        console.log(`Narrator set up for step: ${stepName} (${containerId})`);
    }
    
    setupInitiationHooks() {
        // Override the original step transition to include narrator
        const originalTransitionToStep = window.transitionToStep;
        
        window.transitionToStep = (nextStep) => {
            console.log('[Narrator] Step transition:', nextStep);
            
            // Run the original transition
            if (originalTransitionToStep) {
                originalTransitionToStep(nextStep);
            }
            
            // Handle narrator for this step
            this.handleStepTransition(nextStep);
        };
        
        // Hook into step-specific actions
        this.setupStepHooks();
    }
    
    setupStepHooks() {
        // Name Generator hooks
        document.addEventListener('click', (e) => {
            if (e.target.id === 'roll-dice-btn') {
                this.handleDiceRoll();
            }
            
            if (e.target.id === 'lock-name-btn') {
                this.handleNameLock();
            }
            
            if (e.target.id === 'start-shuffle-btn') {
                this.handleShuffleStart();
            }
            
            if (e.target.id === 'accept-tribe-btn') {
                this.handleTribeAccept();
            }
            
            if (e.target.id === 'align-cosmos-btn') {
                this.handleCosmicAlignment();
            }
            
            if (e.target.id === 'complete-initiation-btn') {
                this.handleFinalCompletion();
            }
        });
        
        // Listen for custom events from the step modules
        document.addEventListener('angel-initiation-event', (e) => {
            this.handleCustomEvent(e.detail);
        });
    }
    
    async startBootSequence() {
        console.log('[Narrator] Starting boot sequence');
        
        // Start with sleeping animation
        this.narrator.sleep();
        
        // Wait a moment, then wake up
        setTimeout(async () => {
            const bootScript = NarratorScripts.getScript('boot');
            await this.narrator.runScript(bootScript);
            
            // Show greeting after boot
            if (!this.hasPlayedGreeting) {
                await this.showGreeting();
                this.hasPlayedGreeting = true;
            }
        }, 3000);
    }
    
    async showGreeting() {
        console.log('[Narrator] Showing greeting');
        
        const greetingScript = NarratorScripts.getScript('greeting');
        await this.narrator.runScript(greetingScript);
        
        // Automatically start the initiation flow after greeting
        this.startInitiationFlow();
    }
    
    startInitiationFlow() {
        console.log('[Narrator] Starting initiation flow');
        
        // Transition to first step
        window.transitionToStep('name_generator');
    }
    
    async handleStepTransition(stepName) {
        console.log('[Narrator] Handling step transition to:', stepName);
        
        this.currentPhase = stepName;
        
        // Set up narrator for the new step
        this.setupNarratorForStep(stepName);
        
        // Different intro scripts for each step
        switch(stepName) {
            case 'name_generator':
                await this.showAngelIdentityIntro();
                break;
                
            case 'tribe_sorting':
                await this.showTribeSortingIntro();
                break;
                
            case 'dob_ritual':
                await this.showCosmicAlignmentIntro();
                break;
                
            case 'final_blessing':
                await this.showFinalBlessingIntro();
                break;
        }
    }
    
    async showAngelIdentityIntro() {
        const script = NarratorScripts.getScript('angelIdentity');
        await this.narrator.runScript(script);
        
        // Switch to excited animation while user interacts
        this.narrator.getExcited();
    }
    
    async showTribeSortingIntro() {
        const script = NarratorScripts.getScript('tribeSorting');
        await this.narrator.runScript(script);
        
        // Switch to shocked animation
        this.narrator.getShocked();
    }
    
    async showCosmicAlignmentIntro() {
        const script = NarratorScripts.getScript('cosmicAlignment');
        await this.narrator.runScript(script);
        
        // Switch to calm animation
        this.narrator.stayCalm();
    }
    
    async showFinalBlessingIntro() {
        const script = NarratorScripts.getScript('finalBlessing');
        await this.narrator.runScript(script);
        
        // Switch to heartfelt animation
        this.narrator.showLove();
    }
    
    async handleDiceRoll() {
        console.log('[Narrator] Dice roll triggered');
        
        const script = NarratorScripts.getScript('rolling');
        await this.narrator.runScript(script);
        
        // Return to excited state
        this.narrator.getExcited();
    }
    
    async handleNameLock() {
        console.log('[Narrator] Name lock triggered');
        
        const script = NarratorScripts.getScript('nameResult');
        await this.narrator.runScript(script);
        
        // Celebrate!
        this.narrator.celebrate();
    }
    
    async handleShuffleStart() {
        console.log('[Narrator] Shuffle start triggered');
        
        const script = NarratorScripts.getScript('shuffling');
        await this.narrator.runScript(script);
        
        // Stay in shocked state
        this.narrator.getShocked();
    }
    
    async handleTribeAccept() {
        console.log('[Narrator] Tribe accept triggered');
        
        // Get the tribe name from the UI
        const tribeNameEl = document.getElementById('tribe-name-result');
        const tribeName = tribeNameEl ? tribeNameEl.textContent : 'Your Tribe';
        
        const script = NarratorScripts.getTribeResultScript(tribeName, '✨');
        await this.narrator.runScript(script);
        
        // Celebrate the tribe selection
        this.narrator.celebrate();
    }
    
    async handleCosmicAlignment() {
        console.log('[Narrator] Cosmic alignment triggered');
        
        await this.narrator.speak('Perfect! The stars have aligned! 🌟', 2000);
        
        // Stay calm
        this.narrator.stayCalm();
    }
    
    async handleFinalCompletion() {
        console.log('[Narrator] Final completion triggered');
        
        await this.narrator.speak('Your journey is complete, beautiful angel! 🚀', 3000);
        
        // Final celebration
        this.narrator.celebrate();
    }
    
    handleCustomEvent(eventData) {
        console.log('[Narrator] Custom event:', eventData);
        
        // Handle specific events from the step modules
        switch(eventData.type) {
            case 'name_generated':
                this.narrator.speak(`${eventData.name}... I love it! 💫`, 2000);
                break;
                
            case 'tribe_revealed':
                this.narrator.speak(`${eventData.tribe}! YES! 🔥`, 2000);
                break;
                
            case 'dob_selected':
                this.narrator.speak('Your cosmic signature is noted! ✨', 2000);
                break;
                
            case 'celebration_start':
                this.narrator.celebrate();
                break;
        }
    }
    
    // Public methods for manual control
    async playGreeting() {
        if (!this.hasPlayedGreeting) {
            await this.showGreeting();
            this.hasPlayedGreeting = true;
        }
    }
    
    async playScript(scriptName) {
        const script = NarratorScripts.getScript(scriptName);
        if (script.length > 0) {
            await this.narrator.runScript(script);
        }
    }
    
    getCurrentPhase() {
        return this.currentPhase;
    }
    
    isReady() {
        return this.isInitialized && this.narrator !== null;
    }
    
    destroy() {
        if (this.narrator) {
            this.narrator.destroy();
            this.narrator = null;
        }
        
        this.isInitialized = false;
        console.log('NarratorIntegration destroyed');
    }
}

// Global instance
window.angelNarratorIntegration = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Wait for the main initiation system to be ready
    setTimeout(() => {
        if (!window.angelNarratorIntegration) {
            window.angelNarratorIntegration = new NarratorIntegration();
        }
    }, 1000);
});

// Expose useful functions globally
window.playNarratorScript = function(scriptName) {
    if (window.angelNarratorIntegration && window.angelNarratorIntegration.isReady()) {
        window.angelNarratorIntegration.playScript(scriptName);
    }
};

window.triggerNarratorEvent = function(eventType, data = {}) {
    const event = new CustomEvent('angel-initiation-event', {
        detail: { type: eventType, ...data }
    });
    document.dispatchEvent(event);
};

// Debug helpers
window.testNarrator = function() {
    if (window.angelNarratorIntegration && window.angelNarratorIntegration.narrator) {
        window.angelNarratorIntegration.narrator.speak('Testing, testing... 1, 2, 3! 🎤', 2000);
    }
};

window.narratorStatus = function() {
    if (window.angelNarratorIntegration) {
        console.log('Narrator Ready:', window.angelNarratorIntegration.isReady());
        console.log('Current Phase:', window.angelNarratorIntegration.getCurrentPhase());
        console.log('Has Played Greeting:', window.angelNarratorIntegration.hasPlayedGreeting);
    }
};