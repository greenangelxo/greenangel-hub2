/**
 * DOB Ritual Step
 * Handles date of birth collection with zodiac styling and cosmic alignment
 */

class DOBRitual {
    constructor() {
        this.birthMonth = '';
        this.birthYear = '';
        this.isAligned = false;
        
        this.zodiacSigns = {
            '01': { name: 'Aquarius/Capricorn', emoji: '♒', message: 'The water bearer brings innovation and wisdom' },
            '02': { name: 'Pisces/Aquarius', emoji: '♓', message: 'The fish swims in cosmic depths of intuition' },
            '03': { name: 'Aries/Pisces', emoji: '♈', message: 'The ram charges forward with fiery determination' },
            '04': { name: 'Taurus/Aries', emoji: '♉', message: 'The bull grounds you with earthly strength' },
            '05': { name: 'Gemini/Taurus', emoji: '♊', message: 'The twins dance with duality and communication' },
            '06': { name: 'Cancer/Gemini', emoji: '♋', message: 'The crab protects with lunar sensitivity' },
            '07': { name: 'Leo/Cancer', emoji: '♌', message: 'The lion roars with solar magnificence' },
            '08': { name: 'Virgo/Leo', emoji: '♍', message: 'The maiden weaves perfection from chaos' },
            '09': { name: 'Libra/Virgo', emoji: '♎', message: 'The scales balance all cosmic forces' },
            '10': { name: 'Scorpio/Libra', emoji: '♏', message: 'The scorpion delves into transformative mysteries' },
            '11': { name: 'Sagittarius/Scorpio', emoji: '♐', message: 'The archer aims for infinite horizons' },
            '12': { name: 'Capricorn/Sagittarius', emoji: '♑', message: 'The goat climbs mountains of ambition' }
        };
        
        this.cosmicMessages = [
            'The stars whisper of your arrival...',
            'Cosmic forces align to your timeline...',
            'The universe acknowledges your presence...',
            'Ancient celestials recognize your journey...',
            'Time and space bend to your essence...'
        ];
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.createConstellations();
        this.createBirthChart();
        this.startCosmicMessages();
    }
    
    setupEventListeners() {
        const monthSelect = document.getElementById('birth-month');
        const yearSelect = document.getElementById('birth-year');
        const alignBtn = document.getElementById('align-cosmos-btn');
        
        if (monthSelect) {
            monthSelect.addEventListener('change', (e) => this.handleMonthChange(e));
        }
        
        if (yearSelect) {
            yearSelect.addEventListener('change', (e) => this.handleYearChange(e));
        }
        
        if (alignBtn) {
            alignBtn.addEventListener('click', () => this.alignCosmos());
        }
    }
    
    handleMonthChange(event) {
        this.birthMonth = event.target.value;
        event.target.classList.add('selected');
        
        this.updateZodiacSign();
        this.updateCosmicMessage();
        this.checkAlignment();
    }
    
    handleYearChange(event) {
        this.birthYear = event.target.value;
        event.target.classList.add('selected');
        
        this.updateCosmicMessage();
        this.checkAlignment();
    }
    
    updateZodiacSign() {
        if (!this.birthMonth) return;
        
        const zodiac = this.zodiacSigns[this.birthMonth];
        if (!zodiac) return;
        
        const zodiacElement = document.querySelector('.zodiac-sign');
        if (zodiacElement) {
            zodiacElement.textContent = zodiac.emoji;
            zodiacElement.classList.add('visible');
        }
        
        const messageEl = document.querySelector('.zodiac-message');
        if (messageEl) {
            messageEl.textContent = zodiac.message;
            messageEl.classList.add('active');
        }
    }
    
    updateCosmicMessage() {
        if (!this.birthMonth && !this.birthYear) return;
        
        const messageEl = document.querySelector('.zodiac-message');
        if (!messageEl) return;
        
        let message = '';
        
        if (this.birthMonth && this.birthYear) {
            const zodiac = this.zodiacSigns[this.birthMonth];
            message = `${zodiac.message} • Born in ${this.birthYear}`;
        } else if (this.birthMonth) {
            const zodiac = this.zodiacSigns[this.birthMonth];
            message = zodiac.message;
        } else if (this.birthYear) {
            message = `The year ${this.birthYear} holds cosmic significance...`;
        }
        
        this.animateMessageChange(messageEl, message);
    }
    
    animateMessageChange(element, newMessage) {
        element.style.opacity = '0';
        element.style.transform = 'translateY(10px)';
        
        setTimeout(() => {
            element.textContent = newMessage;
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, 300);
    }
    
    checkAlignment() {
        const alignBtn = document.getElementById('align-cosmos-btn');
        if (!alignBtn) return;
        
        if (this.birthMonth && this.birthYear) {
            alignBtn.classList.add('can-align');
            alignBtn.textContent = 'Align with the Cosmos 🌌';
            this.showBirthChart();
        } else {
            alignBtn.classList.remove('can-align');
            alignBtn.textContent = 'Select Your Birth Date First';
        }
    }
    
    showBirthChart() {
        const birthChart = document.querySelector('.birth-chart');
        if (birthChart) {
            birthChart.classList.add('visible');
        }
        
        const formContainer = document.querySelector('.dob-form-container');
        if (formContainer) {
            formContainer.classList.add('cosmic-alignment');
        }
    }
    
    async alignCosmos() {
        if (!this.birthMonth || !this.birthYear) {
            window.showNotification('Please select both month and year first', 'error');
            return;
        }
        
        const alignBtn = document.getElementById('align-cosmos-btn');
        if (alignBtn) {
            alignBtn.disabled = true;
            alignBtn.textContent = 'Aligning with cosmic forces...';
        }
        
        // Show cosmic alignment animation
        this.createAlignmentAnimation();
        
        // Show loading
        window.showLoading('Consulting the celestial archives...');
        
        try {
            await window.makeAjaxRequest('save_dob', {
                birth_month: this.birthMonth,
                birth_year: this.birthYear
            });
            
            window.hideLoading();
            
            // Mark as aligned
            this.isAligned = true;
            
            // Update UI
            this.showAlignmentSuccess();
            
            // Show success message
            const zodiac = this.zodiacSigns[this.birthMonth];
            window.showNotification(
                `Cosmic alignment complete! ${zodiac.emoji} Your celestial profile has been recorded.`,
                'success'
            );
            
            // Transition to next step
            setTimeout(() => {
                window.transitionToStep('final_blessing');
            }, 3000);
            
        } catch (error) {
            console.error('Error saving DOB:', error);
            window.hideLoading();
            window.showNotification(error.message || 'Cosmic alignment failed. Please try again.', 'error');
            
            if (alignBtn) {
                alignBtn.disabled = false;
                alignBtn.textContent = 'Align with the Cosmos 🌌';
            }
        }
    }
    
    showAlignmentSuccess() {
        const formContainer = document.querySelector('.dob-form-container');
        const alignBtn = document.getElementById('align-cosmos-btn');
        
        if (formContainer) {
            formContainer.classList.add('dob-success');
        }
        
        if (alignBtn) {
            alignBtn.textContent = 'Alignment Complete! ✨';
            alignBtn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
        }
        
        // Create success celebration
        this.createSuccessCelebration();
    }
    
    createAlignmentAnimation() {
        const formContainer = document.querySelector('.dob-form-container');
        if (!formContainer) return;
        
        // Create cosmic validation effect
        const validation = document.createElement('div');
        validation.className = 'cosmic-validation';
        validation.textContent = '⭐ COSMIC ALIGNMENT ACHIEVED ⭐';
        formContainer.appendChild(validation);
        
        setTimeout(() => {
            validation.classList.add('visible');
        }, 500);
        
        // Create energy rings
        this.createEnergyRings();
    }
    
    createEnergyRings() {
        const formContainer = document.querySelector('.dob-form-container');
        if (!formContainer) return;
        
        for (let i = 0; i < 3; i++) {
            const ring = document.createElement('div');
            ring.style.position = 'absolute';
            ring.style.top = '50%';
            ring.style.left = '50%';
            ring.style.transform = 'translate(-50%, -50%)';
            ring.style.width = (100 + i * 50) + 'px';
            ring.style.height = (100 + i * 50) + 'px';
            ring.style.borderRadius = '50%';
            ring.style.border = '2px solid rgba(174, 214, 4, 0.3)';
            ring.style.animation = `energyRing ${2 + i * 0.5}s ease-out infinite`;
            ring.style.animationDelay = `${i * 0.3}s`;
            ring.style.pointerEvents = 'none';
            ring.style.zIndex = '5';
            
            formContainer.appendChild(ring);
            
            setTimeout(() => {
                if (ring.parentNode) {
                    ring.parentNode.removeChild(ring);
                }
            }, 5000);
        }
    }
    
    createSuccessCelebration() {
        // Create constellation burst
        if (window.createCelebrationBurst) {
            window.createCelebrationBurst();
        }
        
        // Create zodiac specific celebration
        this.createZodiacCelebration();
    }
    
    createZodiacCelebration() {
        const zodiac = this.zodiacSigns[this.birthMonth];
        if (!zodiac) return;
        
        // Create floating zodiac symbols
        for (let i = 0; i < 10; i++) {
            setTimeout(() => {
                const symbol = document.createElement('div');
                symbol.textContent = zodiac.emoji;
                symbol.style.position = 'fixed';
                symbol.style.fontSize = '24px';
                symbol.style.pointerEvents = 'none';
                symbol.style.zIndex = '1000';
                symbol.style.left = Math.random() * 100 + '%';
                symbol.style.top = '110%';
                symbol.style.animation = 'zodiacFloat 4s ease-out forwards';
                
                document.body.appendChild(symbol);
                
                setTimeout(() => {
                    if (symbol.parentNode) {
                        symbol.parentNode.removeChild(symbol);
                    }
                }, 4000);
            }, i * 150);
        }
    }
    
    createConstellations() {
        const zodiacBg = document.querySelector('.zodiac-background');
        if (!zodiacBg) return;
        
        // Create constellation points
        const constellationCount = 20;
        
        for (let i = 0; i < constellationCount; i++) {
            const star = document.createElement('div');
            star.className = 'constellation';
            star.style.left = Math.random() * 100 + '%';
            star.style.top = Math.random() * 100 + '%';
            star.style.animationDelay = Math.random() * 3 + 's';
            
            zodiacBg.appendChild(star);
        }
        
        // Create constellation lines
        this.createConstellationLines();
    }
    
    createConstellationLines() {
        const zodiacBg = document.querySelector('.zodiac-background');
        if (!zodiacBg) return;
        
        // Create some connecting lines
        for (let i = 0; i < 6; i++) {
            const line = document.createElement('div');
            line.className = 'constellation-line';
            line.style.left = Math.random() * 80 + '%';
            line.style.top = Math.random() * 80 + '%';
            line.style.width = (20 + Math.random() * 40) + 'px';
            line.style.transform = `rotate(${Math.random() * 360}deg)`;
            line.style.animationDelay = Math.random() * 5 + 's';
            
            zodiacBg.appendChild(line);
        }
    }
    
    createBirthChart() {
        const formContainer = document.querySelector('.dob-form-container');
        if (!formContainer) return;
        
        const birthChart = document.createElement('div');
        birthChart.className = 'birth-chart';
        
        // Add planets
        const planets = ['mercury', 'venus', 'mars', 'jupiter'];
        planets.forEach(planet => {
            const planetEl = document.createElement('div');
            planetEl.className = `planet ${planet}`;
            birthChart.appendChild(planetEl);
        });
        
        formContainer.appendChild(birthChart);
    }
    
    startCosmicMessages() {
        const messageEl = document.querySelector('.zodiac-message');
        if (!messageEl) return;
        
        let messageIndex = 0;
        
        const messageInterval = setInterval(() => {
            if (this.birthMonth || this.birthYear) {
                clearInterval(messageInterval);
                return;
            }
            
            const message = this.cosmicMessages[messageIndex];
            this.animateMessageChange(messageEl, message);
            
            messageIndex = (messageIndex + 1) % this.cosmicMessages.length;
        }, 4000);
    }
    
    addDynamicStyles() {
        const style = document.createElement('style');
        style.textContent = `
            @keyframes zodiacFloat {
                0% { 
                    transform: translateY(0) rotate(0deg);
                    opacity: 1;
                }
                100% { 
                    transform: translateY(-110vh) rotate(360deg);
                    opacity: 0;
                }
            }
            
            @keyframes energyRing {
                0% { 
                    transform: translate(-50%, -50%) scale(0.5);
                    opacity: 1;
                }
                100% { 
                    transform: translate(-50%, -50%) scale(2);
                    opacity: 0;
                }
            }
            
            .zodiac-message {
                transition: all 0.3s ease;
            }
            
            .cosmic-select {
                transition: all 0.3s ease;
            }
            
            .cosmic-select:hover {
                transform: translateY(-2px);
            }
        `;
        document.head.appendChild(style);
    }
}

// Global instance
window.dobRitual = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize on DOB ritual step
    if (document.getElementById('step-dob-ritual')) {
        window.dobRitual = new DOBRitual();
        window.dobRitual.addDynamicStyles();
    }
});