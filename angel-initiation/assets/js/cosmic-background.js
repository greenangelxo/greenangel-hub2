/**
 * Cosmic Background Effects
 * Creates twinkling stars and floating emojis for the Angel Initiation ceremony
 */

class CosmicBackground {
    constructor() {
        this.cosmicEmojis = [
            '⭐', '⭐', '💫', '✨', '🌟', '☄️', '🛰️', '🚀'
        ];
        
        this.celebrationEmojis = [
            '✨', '🌟', '💫', '⭐', '⭐', '✨', '💫', '🌟'
        ];
        
        this.mouseX = 0;
        this.mouseY = 0;
        this.isInitialized = false;
        
        this.init();
    }
    
    init() {
        if (this.isInitialized) return;
        
        this.createBackgroundStars();
        this.createEmojiUniverse();
        this.setupMouseInteraction();
        this.startShootingStars();
        this.startEmojiSpawning();
        
        this.isInitialized = true;
    }
    
    createBackgroundStars() {
        const starCount = window.innerWidth < 768 ? 75 : 150;
        
        for (let i = 0; i < starCount; i++) {
            const star = document.createElement('div');
            star.className = 'star';
            star.style.left = Math.random() * 100 + '%';
            star.style.top = Math.random() * 100 + '%';
            star.style.animation = `twinkle ${2 + Math.random() * 4}s ease-in-out ${Math.random() * 5}s infinite`;
            document.body.appendChild(star);
        }
    }
    
    createCosmicEmoji() {
        const emoji = document.createElement('div');
        emoji.className = 'cosmic-emoji';
        emoji.textContent = this.cosmicEmojis[Math.floor(Math.random() * this.cosmicEmojis.length)];
        
        // Random starting position and direction
        const directions = ['up', 'down', 'left', 'right', 'diagonal-1', 'diagonal-2'];
        const direction = directions[Math.floor(Math.random() * directions.length)];
        
        this.setEmojiAnimation(emoji, direction);
        this.setEmojiDepth(emoji);
        
        document.body.appendChild(emoji);
        
        // Remove emoji after animation completes
        setTimeout(() => {
            if (emoji.parentNode) {
                emoji.parentNode.removeChild(emoji);
            }
        }, 30000);
    }
    
    setEmojiAnimation(emoji, direction) {
        const duration = 15 + Math.random() * 15;
        const delay = Math.random() * 5;
        
        switch(direction) {
            case 'up':
                emoji.style.animation = `float-up ${duration}s linear ${delay}s infinite`;
                emoji.style.left = Math.random() * 100 + '%';
                emoji.style.top = '110%';
                break;
                
            case 'down':
                emoji.style.animation = `float-down ${duration}s linear ${delay}s infinite`;
                emoji.style.left = Math.random() * 100 + '%';
                emoji.style.top = '-10%';
                break;
                
            case 'left':
                emoji.style.animation = `float-left ${duration}s linear ${delay}s infinite`;
                emoji.style.left = '110%';
                emoji.style.top = Math.random() * 100 + '%';
                break;
                
            case 'right':
                emoji.style.animation = `float-right ${duration}s linear ${delay}s infinite`;
                emoji.style.left = '-10%';
                emoji.style.top = Math.random() * 100 + '%';
                break;
                
            case 'diagonal-1':
                emoji.style.animation = `float-diagonal-1 ${duration}s linear ${delay}s infinite`;
                emoji.style.left = '-10%';
                emoji.style.top = '-10%';
                break;
                
            case 'diagonal-2':
                emoji.style.animation = `float-diagonal-2 ${duration}s linear ${delay}s infinite`;
                emoji.style.left = '110%';
                emoji.style.top = '-10%';
                break;
        }
    }
    
    setEmojiDepth(emoji) {
        const depth = Math.random();
        if (depth < 0.3) {
            emoji.classList.add('far');
        } else if (depth > 0.7) {
            emoji.classList.add('near');
        }
    }
    
    createEmojiUniverse() {
        const initialCount = window.innerWidth < 768 ? 20 : 40;
        
        for (let i = 0; i < initialCount; i++) {
            setTimeout(() => {
                this.createCosmicEmoji();
            }, i * 200);
        }
    }
    
    setupMouseInteraction() {
        document.addEventListener('mousemove', (e) => {
            this.mouseX = e.clientX;
            this.mouseY = e.clientY;
            
            this.applyMouseRepel();
        });
    }
    
    applyMouseRepel() {
        const emojis = document.querySelectorAll('.cosmic-emoji');
        emojis.forEach(emoji => {
            const rect = emoji.getBoundingClientRect();
            const emojiX = rect.left + rect.width / 2;
            const emojiY = rect.top + rect.height / 2;
            const distance = Math.sqrt(
                Math.pow(this.mouseX - emojiX, 2) + 
                Math.pow(this.mouseY - emojiY, 2)
            );
            
            if (distance < 100) {
                const angle = Math.atan2(emojiY - this.mouseY, emojiX - this.mouseX);
                const pushDistance = (100 - distance) / 2;
                const pushX = Math.cos(angle) * pushDistance;
                const pushY = Math.sin(angle) * pushDistance;
                
                emoji.style.transform = `translate(${pushX}px, ${pushY}px)`;
                emoji.style.transition = 'transform 0.3s ease';
            } else {
                emoji.style.transform = '';
            }
        });
    }
    
    createShootingStar() {
        const star = document.createElement('div');
        star.className = 'cosmic-emoji';
        star.style.position = 'fixed';
        star.style.fontSize = '25px';
        star.style.zIndex = '5';
        star.textContent = '⭐';
        star.style.left = Math.random() * 50 + '%';
        star.style.top = Math.random() * 50 + '%';
        
        // Shooting star animation
        star.style.animation = 'shoot 3s ease-out';
        
        document.body.appendChild(star);
        
        setTimeout(() => {
            if (star.parentNode) {
                star.parentNode.removeChild(star);
            }
        }, 3000);
    }
    
    startShootingStars() {
        setInterval(() => {
            if (Math.random() > 0.7) {
                this.createShootingStar();
            }
        }, 3000);
    }
    
    startEmojiSpawning() {
        setInterval(() => {
            const currentEmojis = document.querySelectorAll('.cosmic-emoji').length;
            const maxEmojis = window.innerWidth < 768 ? 25 : 50;
            
            if (currentEmojis < maxEmojis) {
                this.createCosmicEmoji();
            }
        }, 2000);
    }
    
    createCelebrationBurst(centerX = null, centerY = null) {
        const burstCount = 30;
        const targetX = centerX || window.innerWidth / 2;
        const targetY = centerY || window.innerHeight / 2;
        
        for (let i = 0; i < burstCount; i++) {
            setTimeout(() => {
                const celebrationEmoji = document.createElement('div');
                celebrationEmoji.className = 'cosmic-emoji celebration-burst';
                celebrationEmoji.textContent = this.celebrationEmojis[
                    Math.floor(Math.random() * this.celebrationEmojis.length)
                ];
                
                // Random position around the target
                const angle = (Math.PI * 2 * i) / burstCount;
                const radius = 50 + Math.random() * 100;
                const startX = targetX + Math.cos(angle) * radius;
                const startY = targetY + Math.sin(angle) * radius;
                
                celebrationEmoji.style.left = startX + 'px';
                celebrationEmoji.style.top = startY + 'px';
                celebrationEmoji.style.position = 'fixed';
                celebrationEmoji.style.fontSize = '20px';
                celebrationEmoji.style.zIndex = '1000';
                celebrationEmoji.style.animation = 'celebrationBurst 2s ease-out';
                
                document.body.appendChild(celebrationEmoji);
                
                setTimeout(() => {
                    if (celebrationEmoji.parentNode) {
                        celebrationEmoji.parentNode.removeChild(celebrationEmoji);
                    }
                }, 2000);
            }, i * 50);
        }
    }
    
    createConfettiExplosion() {
        const confettiCount = 50;
        
        for (let i = 0; i < confettiCount; i++) {
            setTimeout(() => {
                const confetti = document.createElement('div');
                confetti.className = 'cosmic-emoji confetti';
                confetti.textContent = this.celebrationEmojis[
                    Math.floor(Math.random() * this.celebrationEmojis.length)
                ];
                
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.top = '-10%';
                confetti.style.position = 'fixed';
                confetti.style.fontSize = (15 + Math.random() * 15) + 'px';
                confetti.style.zIndex = '1000';
                confetti.style.animation = `confettiFall ${3 + Math.random() * 2}s ease-out`;
                
                document.body.appendChild(confetti);
                
                setTimeout(() => {
                    if (confetti.parentNode) {
                        confetti.parentNode.removeChild(confetti);
                    }
                }, 5000);
            }, i * 30);
        }
    }
    
    addDynamicStyles() {
        const style = document.createElement('style');
        style.textContent = `
            @keyframes float-up {
                from { transform: translateY(110vh) rotate(0deg); }
                to { transform: translateY(-10vh) rotate(360deg); }
            }
            
            @keyframes float-down {
                from { transform: translateY(-10vh) rotate(0deg); }
                to { transform: translateY(110vh) rotate(-360deg); }
            }
            
            @keyframes float-left {
                from { transform: translateX(110vw) rotate(0deg); }
                to { transform: translateX(-10vw) rotate(360deg); }
            }
            
            @keyframes float-right {
                from { transform: translateX(-10vw) rotate(0deg); }
                to { transform: translateX(110vw) rotate(-360deg); }
            }
            
            @keyframes float-diagonal-1 {
                from { transform: translate(-10vw, -10vh) rotate(0deg); }
                to { transform: translate(110vw, 110vh) rotate(720deg); }
            }
            
            @keyframes float-diagonal-2 {
                from { transform: translate(110vw, -10vh) rotate(0deg); }
                to { transform: translate(-10vw, 110vh) rotate(-720deg); }
            }
            
            @keyframes shoot {
                0% { 
                    transform: translate(0, 0) rotate(45deg);
                    opacity: 0;
                }
                10% { opacity: 1; }
                90% { opacity: 1; }
                100% { 
                    transform: translate(300px, 300px) rotate(45deg);
                    opacity: 0;
                }
            }
            
            @keyframes celebrationBurst {
                0% { 
                    transform: scale(0.5) rotate(0deg);
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
            
            @keyframes confettiFall {
                0% { 
                    transform: translateY(0) rotate(0deg);
                    opacity: 1;
                }
                100% { 
                    transform: translateY(110vh) rotate(720deg);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    destroy() {
        // Clean up all cosmic elements
        const cosmicElements = document.querySelectorAll('.cosmic-emoji, .star');
        cosmicElements.forEach(element => {
            if (element.parentNode) {
                element.parentNode.removeChild(element);
            }
        });
        
        this.isInitialized = false;
    }
}

// Global cosmic background instance
window.cosmicBackground = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (!window.cosmicBackground) {
        window.cosmicBackground = new CosmicBackground();
        window.cosmicBackground.addDynamicStyles();
    }
});

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (window.cosmicBackground) {
        window.cosmicBackground.destroy();
    }
});

// Expose functions globally for use in other scripts
window.createCelebrationBurst = function(x, y) {
    if (window.cosmicBackground) {
        window.cosmicBackground.createCelebrationBurst(x, y);
    }
};

window.createConfettiExplosion = function() {
    if (window.cosmicBackground) {
        window.cosmicBackground.createConfettiExplosion();
    }
};