/**
 * Blessing Celebration Step
 * Handles the final blessing ceremony and completion of the initiation
 */

class BlessingCelebration {
    constructor() {
        this.angelData = {
            name: '',
            tribe: '',
            alignment: '',
            reward: '£5 Angel Credit'
        };
        
        this.isCompleting = false;
        this.completionMessages = [
            'Channeling divine energy...',
            'Blessing your angel essence...',
            'Activating cosmic rewards...',
            'Finalizing your transformation...',
            'Welcome to the Angel Realm!'
        ];
        
        this.init();
    }
    
    init() {
        this.loadAngelData();
        this.setupEventListeners();
        this.createBlessingEffects();
        this.animateAngelSummary();
    }
    
    setupEventListeners() {
        const completeBtn = document.getElementById('complete-initiation-btn');
        
        if (completeBtn) {
            completeBtn.addEventListener('click', () => this.completeInitiation());
        }
    }
    
    loadAngelData() {
        // This would normally load from user data or previous steps
        // For demo purposes, we'll use placeholder data
        this.angelData = {
            name: 'Loading...',
            tribe: 'Loading...',
            alignment: 'Loading...',
            reward: '£5 Angel Credit'
        };
        
        // Simulate loading user data
        this.updateAngelSummary();
        
        // Simulate data loading delay
        setTimeout(() => {
            this.angelData = {
                name: 'Cosmic Roller', // This would come from Step 1
                tribe: 'The Blazed Ones 😇', // This would come from Step 2
                alignment: 'Aquarius Rising', // This would come from Step 3
                reward: '£5 Angel Credit'
            };
            this.updateAngelSummary();
        }, 2000);
    }
    
    updateAngelSummary() {
        const nameEl = document.getElementById('summary-name');
        const tribeEl = document.getElementById('summary-tribe');
        const alignmentEl = document.getElementById('summary-alignment');
        
        if (nameEl) {
            nameEl.textContent = this.angelData.name;
            nameEl.classList.toggle('loading', this.angelData.name === 'Loading...');
        }
        
        if (tribeEl) {
            tribeEl.textContent = this.angelData.tribe;
            tribeEl.classList.toggle('loading', this.angelData.tribe === 'Loading...');
        }
        
        if (alignmentEl) {
            alignmentEl.textContent = this.angelData.alignment;
            alignmentEl.classList.toggle('loading', this.angelData.alignment === 'Loading...');
        }
    }
    
    animateAngelSummary() {
        const summaryItems = document.querySelectorAll('.summary-item');
        summaryItems.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-30px)';
            
            setTimeout(() => {
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
            }, (index + 1) * 200);
        });
    }
    
    createBlessingEffects() {
        const blessingContent = document.querySelector('.blessing-content');
        if (!blessingContent) return;
        
        // Create sparkles container
        const sparklesContainer = document.createElement('div');
        sparklesContainer.className = 'blessing-sparkles';
        blessingContent.appendChild(sparklesContainer);
        
        // Create floating sparkles
        this.createFloatingSparkles(sparklesContainer);
        
        // Create blessing aura
        const aura = document.createElement('div');
        aura.className = 'blessing-aura';
        blessingContent.appendChild(aura);
        
        // Start continuous sparkle generation
        this.startSparkleGeneration();
    }
    
    createFloatingSparkles(container) {
        const sparkleCount = 15;
        
        for (let i = 0; i < sparkleCount; i++) {
            const sparkle = document.createElement('div');
            sparkle.className = 'blessing-sparkle';
            sparkle.style.left = Math.random() * 100 + '%';
            sparkle.style.top = Math.random() * 100 + '%';
            sparkle.style.animationDelay = Math.random() * 3 + 's';
            sparkle.style.animationDuration = (2 + Math.random() * 2) + 's';
            
            container.appendChild(sparkle);
        }
    }
    
    startSparkleGeneration() {
        setInterval(() => {
            this.createRandomSparkle();
        }, 500);
    }
    
    createRandomSparkle() {
        const sparklesContainer = document.querySelector('.blessing-sparkles');
        if (!sparklesContainer) return;
        
        const sparkle = document.createElement('div');
        sparkle.className = 'blessing-sparkle';
        sparkle.style.left = Math.random() * 100 + '%';
        sparkle.style.top = Math.random() * 100 + '%';
        sparkle.style.animationDuration = (2 + Math.random() * 2) + 's';
        
        sparklesContainer.appendChild(sparkle);
        
        // Remove after animation
        setTimeout(() => {
            if (sparkle.parentNode) {
                sparkle.parentNode.removeChild(sparkle);
            }
        }, 4000);
    }
    
    async completeInitiation() {
        if (this.isCompleting) return;
        
        this.isCompleting = true;
        
        const completeBtn = document.getElementById('complete-initiation-btn');
        if (completeBtn) {
            completeBtn.classList.add('completing');
            completeBtn.disabled = true;
            completeBtn.textContent = 'Completing Initiation...';
        }
        
        try {
            // Show completion process
            await this.showCompletionProcess();
            
            // Make API call to complete initiation
            await window.makeAjaxRequest('complete_initiation');
            
            // Show transformation effect
            this.createTransformationEffect();
            
            // Show final blessing
            await this.showFinalBlessing();
            
            // Transition to completion screen
            setTimeout(() => {
                window.showCompletion();
            }, 2000);
            
        } catch (error) {
            console.error('Error completing initiation:', error);
            window.showNotification('Initiation ceremony failed. Please try again.', 'error');
            
            if (completeBtn) {
                completeBtn.classList.remove('completing');
                completeBtn.disabled = false;
                completeBtn.textContent = 'Complete My Initiation 🚀';
            }
            
            this.isCompleting = false;
        }
    }
    
    async showCompletionProcess() {
        const messages = this.completionMessages;
        
        for (let i = 0; i < messages.length; i++) {
            window.showLoading(messages[i]);
            await this.wait(1000);
        }
        
        window.hideLoading();
    }
    
    wait(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    createTransformationEffect() {
        const transformation = document.createElement('div');
        transformation.className = 'angel-transformation';
        document.body.appendChild(transformation);
        
        // Remove after animation
        setTimeout(() => {
            if (transformation.parentNode) {
                transformation.parentNode.removeChild(transformation);
            }
        }, 3000);
    }
    
    async showFinalBlessing() {
        const blessingContent = document.querySelector('.blessing-content');
        if (blessingContent) {
            blessingContent.classList.add('blessing-complete', 'blessing-success');
        }
        
        // Show reward details
        this.showRewardDetails();
        
        // Create celebration effects
        this.createFinalCelebration();
        
        // Show success message
        window.showNotification('🎉 Initiation Complete! You are now a certified Angel! 🦋', 'success');
    }
    
    showRewardDetails() {
        const rewardDetails = document.querySelector('.reward-details');
        if (rewardDetails) {
            rewardDetails.classList.add('visible');
        }
        
        // If there's a coupon code, display it
        this.displayCouponCode();
    }
    
    displayCouponCode() {
        // This would typically come from the server response
        const couponCode = 'ANGEL' + Math.random().toString(36).substr(2, 6).toUpperCase();
        
        const rewardDetails = document.querySelector('.reward-details');
        if (rewardDetails) {
            rewardDetails.innerHTML = `
                <div class="reward-code">
                    Coupon Code: ${couponCode}
                </div>
                <div style="font-size: 14px; color: var(--angel-gray);">
                    Use this code for £5 off your next order!
                </div>
            `;
        }
    }
    
    createFinalCelebration() {
        // Create massive confetti explosion
        if (window.createConfettiExplosion) {
            window.createConfettiExplosion();
            
            // Multiple waves of confetti
            setTimeout(() => window.createConfettiExplosion(), 1000);
            setTimeout(() => window.createConfettiExplosion(), 2000);
        }
        
        // Create floating blessings
        this.createFloatingBlessings();
        
        // Create angel emoji burst
        this.createAngelEmojiBurst();
    }
    
    createFloatingBlessings() {
        const blessings = ['🦋', '✨', '🌟', '💫', '🎉', '🎊', '⭐'];
        
        for (let i = 0; i < 25; i++) {
            setTimeout(() => {
                const blessing = document.createElement('div');
                blessing.className = 'floating-blessing';
                blessing.textContent = blessings[Math.floor(Math.random() * blessings.length)];
                blessing.style.left = Math.random() * 100 + '%';
                blessing.style.top = '110%';
                blessing.style.fontSize = (16 + Math.random() * 16) + 'px';
                blessing.style.animationDelay = Math.random() * 2 + 's';
                
                document.body.appendChild(blessing);
                
                setTimeout(() => {
                    if (blessing.parentNode) {
                        blessing.parentNode.removeChild(blessing);
                    }
                }, 4000);
            }, i * 100);
        }
    }
    
    createAngelEmojiBurst() {
        const angelEmojis = ['🦋', '😇', '👼', '🌟', '✨', '💫'];
        const burstCount = 20;
        
        for (let i = 0; i < burstCount; i++) {
            setTimeout(() => {
                const angel = document.createElement('div');
                angel.textContent = angelEmojis[Math.floor(Math.random() * angelEmojis.length)];
                angel.style.position = 'fixed';
                angel.style.fontSize = '24px';
                angel.style.pointerEvents = 'none';
                angel.style.zIndex = '1000';
                
                // Random position around center
                const angle = (Math.PI * 2 * i) / burstCount;
                const radius = 100 + Math.random() * 50;
                const centerX = window.innerWidth / 2;
                const centerY = window.innerHeight / 2;
                const x = centerX + Math.cos(angle) * radius;
                const y = centerY + Math.sin(angle) * radius;
                
                angel.style.left = x + 'px';
                angel.style.top = y + 'px';
                angel.style.animation = 'angelBurst 2s ease-out forwards';
                
                document.body.appendChild(angel);
                
                setTimeout(() => {
                    if (angel.parentNode) {
                        angel.parentNode.removeChild(angel);
                    }
                }, 2000);
            }, i * 50);
        }
    }
    
    addDynamicStyles() {
        const style = document.createElement('style');
        style.textContent = `
            @keyframes angelBurst {
                0% { 
                    transform: scale(0) rotate(0deg);
                    opacity: 1;
                }
                50% { 
                    transform: scale(1.2) rotate(180deg);
                    opacity: 1;
                }
                100% { 
                    transform: scale(0.8) rotate(360deg);
                    opacity: 0;
                }
            }
            
            .blessing-content {
                transition: all 0.5s ease;
            }
            
            .reward-details {
                transition: all 0.3s ease;
            }
            
            .summary-item {
                transition: all 0.3s ease;
            }
        `;
        document.head.appendChild(style);
    }
}

// Global instance
window.blessingCelebration = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize on blessing step
    if (document.getElementById('step-final-blessing')) {
        window.blessingCelebration = new BlessingCelebration();
        window.blessingCelebration.addDynamicStyles();
    }
});