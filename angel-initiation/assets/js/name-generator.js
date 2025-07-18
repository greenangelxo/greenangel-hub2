/**
 * Name Generator Step
 * Handles dice rolling animations and name generation for Step 1
 */

class NameGenerator {
    constructor() {
        this.rollCount = 0;
        this.maxRolls = 5;
        this.currentName = '';
        this.isRolling = false;
        this.isLocked = false;
        this.isLocking = false;
        
        this.diceEmojis = ['🎲', '🎯', '🌟', '⚡', '🔥', '💫'];
        this.rollSounds = ['✨', '🌟', '💫', '⭐', '🔥'];
        
        this.init();
    }
    
    init() {
        this.updateUI();
        this.setupEventListeners();
    }
    
    setupEventListeners() {
        const rollBtn = document.getElementById('roll-dice-btn');
        const lockBtn = document.getElementById('lock-name-btn');
        
        if (rollBtn) {
            console.log('[NameGenerator] Attaching roll dice event listener');
            rollBtn.addEventListener('click', () => this.rollDice());
        }
        
        if (lockBtn) {
            console.log('[NameGenerator] Attaching lock name event listener');
            lockBtn.addEventListener('click', () => this.lockName());
        }
    }
    
    updateUI() {
        const rollCountEl = document.getElementById('roll-count');
        const rollBtn = document.getElementById('roll-dice-btn');
        const lockBtn = document.getElementById('lock-name-btn');
        
        if (rollCountEl) {
            rollCountEl.textContent = this.rollCount;
        }
        
        if (rollBtn) {
            rollBtn.disabled = this.rollCount >= this.maxRolls || this.isRolling || this.isLocked;
            if (this.rollCount >= this.maxRolls) {
                rollBtn.textContent = 'All Rolls Complete ✨';
            } else {
                rollBtn.textContent = `Roll the Cosmic Dice ✨ (${this.rollCount}/${this.maxRolls})`;
            }
        }
        
        if (lockBtn) {
            lockBtn.style.display = this.rollCount > 0 && !this.isLocked ? 'block' : 'none';
        }
    }
    
    async rollDice() {
        if (this.isRolling || this.rollCount >= this.maxRolls || this.isLocked) return;
        
        this.isRolling = true;
        this.rollCount++;
        
        // Update UI
        this.updateUI();
        
        // Show rolling animation
        await this.animateDiceRoll();
        
        // Generate new name
        try {
            const response = await window.makeAjaxRequest('generate_name');
            this.currentName = response.name;
            this.displayName(this.currentName);
            this.createConfettiBurst();
            
            // Trigger narrator event
            if (window.triggerNarratorEvent) {
                window.triggerNarratorEvent('name_generated', { name: this.currentName });
            }
        } catch (error) {
            console.error('Error generating name:', error);
            window.showNotification('Failed to generate name. Please try again.', 'error');
        }
        
        this.isRolling = false;
        this.updateUI();
    }
    
    async animateDiceRoll() {
        const dice = document.querySelectorAll('.dice');
        const rollDuration = 800;
        
        return new Promise((resolve) => {
            // Start rolling animation
            dice.forEach((die, index) => {
                die.classList.add('rolling');
                
                // Animate emoji changes during roll
                const rollInterval = setInterval(() => {
                    die.textContent = this.diceEmojis[Math.floor(Math.random() * this.diceEmojis.length)];
                }, 100);
                
                setTimeout(() => {
                    clearInterval(rollInterval);
                    die.classList.remove('rolling');
                    die.classList.add('rolled');
                    
                    // Final emoji
                    die.textContent = this.rollSounds[Math.floor(Math.random() * this.rollSounds.length)];
                    
                    // Remove rolled class after animation
                    setTimeout(() => {
                        die.classList.remove('rolled');
                        // Reset to dice emoji
                        die.textContent = '🎲';
                    }, 500);
                }, rollDuration);
            });
            
            setTimeout(resolve, rollDuration);
        });
    }
    
    displayName(name) {
        const nameDisplay = document.getElementById('generated-name');
        if (!nameDisplay) return;
        
        // Clear previous name
        nameDisplay.textContent = '';
        nameDisplay.classList.add('name-generating');
        
        // Animate name appearance character by character
        setTimeout(() => {
            this.animateNameCharacters(nameDisplay, name);
        }, 200);
    }
    
    animateNameCharacters(element, text) {
        element.classList.remove('name-generating');
        element.classList.add('has-name');
        
        let index = 0;
        const intervalId = setInterval(() => {
            if (index < text.length) {
                const char = document.createElement('span');
                char.className = 'name-character';
                char.textContent = text[index];
                char.style.animationDelay = `${index * 0.05}s`;
                element.appendChild(char);
                index++;
            } else {
                clearInterval(intervalId);
                this.addNameSparkles(element);
            }
        }, 50);
    }
    
    addNameSparkles(element) {
        const sparkleCount = 6;
        
        for (let i = 0; i < sparkleCount; i++) {
            setTimeout(() => {
                const sparkle = document.createElement('div');
                sparkle.className = 'name-sparkle';
                sparkle.style.left = Math.random() * 100 + '%';
                sparkle.style.top = Math.random() * 100 + '%';
                sparkle.style.animationDelay = Math.random() * 1 + 's';
                
                element.appendChild(sparkle);
                
                setTimeout(() => {
                    if (sparkle.parentNode) {
                        sparkle.parentNode.removeChild(sparkle);
                    }
                }, 1500);
            }, i * 100);
        }
    }
    
    createConfettiBurst() {
        const diceContainer = document.querySelector('.dice-container');
        if (!diceContainer) return;
        
        const rect = diceContainer.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;
        
        // Create confetti burst
        if (window.createCelebrationBurst) {
            window.createCelebrationBurst(centerX, centerY);
        }
        
        // Create dice-specific confetti
        this.createDiceConfetti();
    }
    
    createDiceConfetti() {
        const dice = document.querySelectorAll('.dice');
        
        dice.forEach((die, index) => {
            setTimeout(() => {
                const confettiContainer = document.createElement('div');
                confettiContainer.className = 'dice-confetti';
                die.appendChild(confettiContainer);
                
                // Create confetti pieces
                for (let i = 0; i < 8; i++) {
                    const piece = document.createElement('div');
                    piece.className = 'confetti-piece';
                    
                    // Random direction and distance
                    const angle = (Math.PI * 2 * i) / 8;
                    const distance = 30 + Math.random() * 20;
                    const x = Math.cos(angle) * distance;
                    const y = Math.sin(angle) * distance;
                    
                    piece.style.setProperty('--x', `${x}px`);
                    piece.style.setProperty('--y', `${y}px`);
                    piece.style.backgroundColor = this.getRandomColor();
                    piece.style.animationDelay = `${Math.random() * 0.3}s`;
                    
                    confettiContainer.appendChild(piece);
                }
                
                // Remove confetti after animation
                setTimeout(() => {
                    if (confettiContainer.parentNode) {
                        confettiContainer.parentNode.removeChild(confettiContainer);
                    }
                }, 1000);
            }, index * 100);
        });
    }
    
    getRandomColor() {
        const colors = [
            '#9333ea', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#aed604'
        ];
        return colors[Math.floor(Math.random() * colors.length)];
    }
    
    async lockName() {
        if (!this.currentName || this.isLocked) return;
        
        // Prevent duplicate calls
        if (this.isLocking) {
            console.log('[lockName] Already locking, skipping duplicate call');
            return;
        }
        this.isLocking = true;
        
        const lockBtn = document.getElementById('lock-name-btn');
        const nameDisplay = document.getElementById('generated-name');
        
        if (lockBtn) {
            lockBtn.disabled = true;
            lockBtn.textContent = 'Locking Name...';
        }
        
        try {
            await window.makeAjaxRequest('lock_name', { chosen_name: this.currentName });
            
            // Update UI for locked state
            this.isLocked = true;
            
            if (nameDisplay) {
                nameDisplay.classList.add('locked', 'name-lock-animation', 'name-success');
                nameDisplay.classList.add('magical-glow');
            }
            
            if (lockBtn) {
                lockBtn.textContent = 'Name Locked! 🔒';
                lockBtn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
            }
            
            // Create celebration effects
            this.createLockCelebration();
            
            // Show success notification
            window.showNotification(`Name "${this.currentName}" locked successfully! Moving to next step...`, 'success');
            
            // Transition to next step after celebration
            console.log('[lockName] Scheduling transition to tribe_sorting in 3 seconds');
            setTimeout(() => {
                console.log('[lockName] Calling window.transitionToStep("tribe_sorting")');
                window.transitionToStep('tribe_sorting');
            }, 3000);
            
        } catch (error) {
            console.error('Error locking name:', error);
            window.showNotification(error.message || 'Failed to lock name. Please try again.', 'error');
            
            if (lockBtn) {
                lockBtn.disabled = false;
                lockBtn.textContent = 'Lock This Name 🔒';
            }
            
            // Reset locking flag on error
            this.isLocking = false;
        }
    }
    
    createLockCelebration() {
        // Create confetti explosion
        if (window.createConfettiExplosion) {
            window.createConfettiExplosion();
        }
        
        // Create RGB border effect
        const nameDisplay = document.getElementById('generated-name');
        if (nameDisplay) {
            nameDisplay.classList.add('name-generator-rgb');
        }
        
        // Create magical sparkles around the name
        this.createMagicalSparkles();
    }
    
    createMagicalSparkles() {
        const nameDisplay = document.getElementById('generated-name');
        if (!nameDisplay) return;
        
        const sparkleCount = 20;
        
        for (let i = 0; i < sparkleCount; i++) {
            setTimeout(() => {
                const sparkle = document.createElement('div');
                sparkle.textContent = '✨';
                sparkle.style.position = 'absolute';
                sparkle.style.fontSize = '16px';
                sparkle.style.pointerEvents = 'none';
                sparkle.style.zIndex = '1000';
                
                // Random position around the name display
                const rect = nameDisplay.getBoundingClientRect();
                const x = rect.left + Math.random() * rect.width;
                const y = rect.top + Math.random() * rect.height;
                
                sparkle.style.left = x + 'px';
                sparkle.style.top = y + 'px';
                sparkle.style.animation = 'sparkleFloat 2s ease-out forwards';
                
                document.body.appendChild(sparkle);
                
                setTimeout(() => {
                    if (sparkle.parentNode) {
                        sparkle.parentNode.removeChild(sparkle);
                    }
                }, 2000);
            }, i * 100);
        }
    }
    
    addDynamicStyles() {
        const style = document.createElement('style');
        style.textContent = `
            @keyframes sparkleFloat {
                0% { 
                    opacity: 0;
                    transform: translateY(0) scale(0);
                }
                50% { 
                    opacity: 1;
                    transform: translateY(-20px) scale(1);
                }
                100% { 
                    opacity: 0;
                    transform: translateY(-40px) scale(0);
                }
            }
        `;
        document.head.appendChild(style);
    }
}

// Global instance
window.nameGenerator = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('[NameGenerator] DOMContentLoaded fired');
    // Only initialize on name generator step
    if (document.getElementById('step-name-generator')) {
        console.log('[NameGenerator] Creating new NameGenerator instance');
        window.nameGenerator = new NameGenerator();
        window.nameGenerator.addDynamicStyles();
    }
});