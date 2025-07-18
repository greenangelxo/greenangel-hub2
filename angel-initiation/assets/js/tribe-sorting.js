/**
 * Cosmic Shell Game - Tribe Sorting
 * Where fate is shuffled and destiny is chosen
 */

class CosmicShellGame {
    constructor() {
        this.tribes = {
            'dank_dynasty': {
                name: 'The Dank Dynasty',
                emoji: '🔥',
                tagline: 'Stoner royalty, loud luxury, golden chaos',
                colors: ['#ff6b35', '#f7931e', '#ffd700']
            },
            'blazed_ones': {
                name: 'The Blazed Ones',
                emoji: '😇',
                tagline: 'OG angels, mellow & faded',
                colors: ['#9333ea', '#a855f7', '#c084fc']
            },
            'holy_smokes': {
                name: 'The Holy Smokes',
                emoji: '💨',
                tagline: 'Spiritual tokers, mystic puffers',
                colors: ['#10b981', '#34d399', '#6ee7b7']
            }
        };
        
        this.selectedTribe = null;
        this.isShuffling = false;
        this.shuffleComplete = false;
        this.cups = [];
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.setupCups();
        this.createDynamicStyles();
    }
    
    setupEventListeners() {
        const shuffleBtn = document.getElementById('start-shuffle-btn');
        if (shuffleBtn) {
            console.log('[CosmicShellGame] Attaching shuffle button event listener');
            shuffleBtn.addEventListener('click', () => this.startShuffleSequence());
        }
        
        const acceptBtn = document.getElementById('accept-tribe-btn');
        if (acceptBtn) {
            console.log('[CosmicShellGame] Attaching accept button event listener');
            acceptBtn.addEventListener('click', () => this.acceptTribe());
        }
    }
    
    setupCups() {
        this.cups = [
            document.getElementById('cup-1'),
            document.getElementById('cup-2'),
            document.getElementById('cup-3')
        ];
        
        // Add click event to each cup
        this.cups.forEach((cup, index) => {
            if (cup) {
                cup.addEventListener('click', () => this.selectCup(index));
            }
        });
    }
    
    async startShuffleSequence() {
        if (this.isShuffling) return;
        
        console.log('[CosmicShellGame] Starting shuffle sequence');
        this.isShuffling = true;
        
        const shuffleBtn = document.getElementById('start-shuffle-btn');
        if (shuffleBtn) {
            shuffleBtn.disabled = true;
            shuffleBtn.textContent = 'Channeling cosmic energy...';
        }
        
        // Show shuffle stage
        const shuffleStage = document.getElementById('shuffle-stage');
        if (shuffleStage) {
            shuffleStage.style.display = 'block';
        }
        
        // Add cosmic energy effects
        this.createCosmicEnergy();
        
        // Start the shuffle animation
        await this.performShuffle();
        
        // Hide shuffle stage
        if (shuffleStage) {
            shuffleStage.style.display = 'none';
        }
        
        // Show choice prompt
        const choicePrompt = document.getElementById('choice-prompt');
        if (choicePrompt) {
            choicePrompt.style.display = 'block';
            choicePrompt.classList.add('fade-in-prompt');
        }
        
        // Enable cup selection
        this.enableCupSelection();
        
        this.shuffleComplete = true;
        this.isShuffling = false;
        
        if (shuffleBtn) {
            shuffleBtn.style.display = 'none';
        }
    }
    
    async performShuffle() {
        // First, show all tribes briefly
        await this.revealAllTribes();
        
        // Then cover them up
        await this.coverAllTribes();
        
        // Perform the magical shuffle
        await this.shuffleAnimation();
        
        // Add mystical completion effect
        await this.shuffleCompletion();
    }
    
    async revealAllTribes() {
        console.log('[CosmicShellGame] Revealing all tribes');
        
        this.cups.forEach((cup, index) => {
            if (cup) {
                const lid = cup.querySelector('.cup-lid');
                const hiddenTribe = cup.querySelector('.hidden-tribe');
                
                if (lid && hiddenTribe) {
                    lid.style.animation = 'liftLid 0.8s ease-out forwards';
                    hiddenTribe.style.animation = 'revealTribe 0.8s ease-out forwards';
                }
            }
        });
        
        await this.delay(1500);
    }
    
    async coverAllTribes() {
        console.log('[CosmicShellGame] Covering all tribes');
        
        this.cups.forEach((cup, index) => {
            if (cup) {
                const lid = cup.querySelector('.cup-lid');
                const hiddenTribe = cup.querySelector('.hidden-tribe');
                
                if (lid && hiddenTribe) {
                    lid.style.animation = 'coverLid 0.6s ease-out forwards';
                    hiddenTribe.style.animation = 'hideTribe 0.6s ease-out forwards';
                }
            }
        });
        
        await this.delay(800);
    }
    
    async shuffleAnimation() {
        console.log('[CosmicShellGame] Starting shuffle animation');
        
        const shuffleSequence = [
            [0, 1], [1, 2], [0, 2], [1, 0], [2, 1], [0, 1], [2, 0], [1, 2]
        ];
        
        for (const [cup1, cup2] of shuffleSequence) {
            await this.swapCups(cup1, cup2);
            await this.delay(400);
        }
        
        // Final mystical spin
        await this.finalSpin();
    }
    
    async swapCups(index1, index2) {
        const cup1 = this.cups[index1];
        const cup2 = this.cups[index2];
        
        if (cup1 && cup2) {
            cup1.style.animation = `swapLeft 0.8s ease-in-out forwards`;
            cup2.style.animation = `swapRight 0.8s ease-in-out forwards`;
            
            await this.delay(800);
            
            // Reset animations
            cup1.style.animation = '';
            cup2.style.animation = '';
            
            // Swap the data-tribe attributes
            const tribe1 = cup1.dataset.tribe;
            const tribe2 = cup2.dataset.tribe;
            cup1.dataset.tribe = tribe2;
            cup2.dataset.tribe = tribe1;
            
            // Swap the hidden tribe emojis
            const hiddenTribe1 = cup1.querySelector('.hidden-tribe');
            const hiddenTribe2 = cup2.querySelector('.hidden-tribe');
            if (hiddenTribe1 && hiddenTribe2) {
                const emoji1 = hiddenTribe1.textContent;
                const emoji2 = hiddenTribe2.textContent;
                hiddenTribe1.textContent = emoji2;
                hiddenTribe2.textContent = emoji1;
            }
        }
    }
    
    async finalSpin() {
        console.log('[CosmicShellGame] Final cosmic spin');
        
        this.cups.forEach((cup, index) => {
            if (cup) {
                cup.style.animation = `cosmicSpin 1.2s ease-in-out forwards`;
                cup.style.animationDelay = `${index * 0.2}s`;
            }
        });
        
        await this.delay(1600);
        
        // Reset animations
        this.cups.forEach(cup => {
            if (cup) {
                cup.style.animation = '';
                cup.style.animationDelay = '';
            }
        });
    }
    
    async shuffleCompletion() {
        console.log('[CosmicShellGame] Shuffle completion effects');
        
        // Create cosmic burst effect
        this.createCosmicBurst();
        
        // Make cups glow
        this.cups.forEach(cup => {
            if (cup) {
                cup.classList.add('ready-to-choose');
            }
        });
        
        await this.delay(500);
    }
    
    enableCupSelection() {
        this.cups.forEach(cup => {
            if (cup) {
                cup.classList.add('selectable');
                cup.addEventListener('mouseenter', this.cupHoverEffect);
                cup.addEventListener('mouseleave', this.cupHoverEnd);
            }
        });
    }
    
    cupHoverEffect(event) {
        event.target.classList.add('hovering');
    }
    
    cupHoverEnd(event) {
        event.target.classList.remove('hovering');
    }
    
    async selectCup(cupIndex) {
        if (!this.shuffleComplete || this.selectedTribe) return;
        
        console.log('[CosmicShellGame] Cup selected:', cupIndex);
        
        const selectedCup = this.cups[cupIndex];
        if (!selectedCup) return;
        
        // Disable all cups
        this.cups.forEach(cup => {
            if (cup) {
                cup.classList.remove('selectable');
                cup.removeEventListener('mouseenter', this.cupHoverEffect);
                cup.removeEventListener('mouseleave', this.cupHoverEnd);
            }
        });
        
        // Hide choice prompt
        const choicePrompt = document.getElementById('choice-prompt');
        if (choicePrompt) {
            choicePrompt.style.display = 'none';
        }
        
        // Get the tribe
        const tribeKey = selectedCup.dataset.tribe;
        this.selectedTribe = this.tribes[tribeKey];
        
        // Reveal the chosen tribe
        await this.revealChosenTribe(selectedCup);
        
        // Show the epic revelation
        await this.showTribeRevelation();
        
        // Enable accept button
        const acceptBtn = document.getElementById('accept-tribe-btn');
        if (acceptBtn) {
            acceptBtn.style.display = 'block';
        }
    }
    
    async revealChosenTribe(cup) {
        console.log('[CosmicShellGame] Revealing chosen tribe');
        
        // Dramatic pause
        await this.delay(500);
        
        // Epic lid lift
        const lid = cup.querySelector('.cup-lid');
        const hiddenTribe = cup.querySelector('.hidden-tribe');
        
        if (lid && hiddenTribe) {
            lid.style.animation = 'epicLidLift 1.2s ease-out forwards';
            hiddenTribe.style.animation = 'epicTribeReveal 1.2s ease-out forwards';
        }
        
        // Add epic glow to the cup
        cup.classList.add('chosen-cup');
        
        // Create explosion of sparkles
        this.createRevelationSparkles(cup);
        
        await this.delay(1500);
    }
    
    async showTribeRevelation() {
        console.log('[CosmicShellGame] Showing tribe revelation');
        
        const revelation = document.getElementById('tribe-revelation');
        const emojiEl = document.getElementById('tribe-emoji-result');
        const nameEl = document.getElementById('tribe-name-result');
        const taglineEl = document.getElementById('tribe-tagline-result');
        
        if (revelation && emojiEl && nameEl && taglineEl && this.selectedTribe) {
            // Set the content
            emojiEl.textContent = this.selectedTribe.emoji;
            nameEl.textContent = this.selectedTribe.name;
            taglineEl.textContent = this.selectedTribe.tagline;
            
            // Apply tribe colors
            const card = revelation.querySelector('.tribe-card-epic');
            if (card) {
                card.style.background = `linear-gradient(135deg, ${this.selectedTribe.colors.join(', ')})`;
            }
            
            // Show with epic animation
            revelation.style.display = 'block';
            revelation.classList.add('revelation-entrance');
            
            // Create massive cosmic burst
            this.createMassiveCosmicBurst();
            
            // Show notification
            window.showNotification(`Welcome to ${this.selectedTribe.name}! 🎉`, 'success');
        }
    }
    
    async acceptTribe() {
        console.log('[CosmicShellGame] Accepting tribe');
        
        const acceptBtn = document.getElementById('accept-tribe-btn');
        if (acceptBtn) {
            acceptBtn.disabled = true;
            acceptBtn.textContent = 'Joining your tribe...';
        }
        
        try {
            // Save the tribe choice
            await window.makeAjaxRequest('accept_tribe', { 
                tribe: Object.keys(this.tribes).find(key => this.tribes[key] === this.selectedTribe) 
            });
            
            window.showNotification('Tribe membership confirmed! Moving to next step...', 'success');
            
            // Transition to next step
            setTimeout(() => {
                window.transitionToStep('dob_ritual');
            }, 2000);
            
        } catch (error) {
            console.error('Error accepting tribe:', error);
            window.showNotification(error.message || 'Failed to join tribe. Please try again.', 'error');
            
            if (acceptBtn) {
                acceptBtn.disabled = false;
                acceptBtn.textContent = 'Accept My Tribe ✨';
            }
        }
    }
    
    createCosmicEnergy() {
        const energyEl = document.querySelector('.cosmic-energy');
        if (!energyEl) return;
        
        // Create swirling energy particles
        for (let i = 0; i < 20; i++) {
            const particle = document.createElement('div');
            particle.className = 'energy-particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.top = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 2 + 's';
            energyEl.appendChild(particle);
            
            // Remove after animation
            setTimeout(() => {
                if (particle.parentNode) {
                    particle.parentNode.removeChild(particle);
                }
            }, 3000);
        }
    }
    
    createCosmicBurst() {
        const container = document.querySelector('.cosmic-shells');
        if (!container) return;
        
        // Create burst particles
        for (let i = 0; i < 30; i++) {
            const particle = document.createElement('div');
            particle.className = 'cosmic-burst-particle';
            particle.style.left = '50%';
            particle.style.top = '50%';
            
            const angle = (Math.PI * 2 * i) / 30;
            const distance = 100 + Math.random() * 50;
            particle.style.setProperty('--burst-x', `${Math.cos(angle) * distance}px`);
            particle.style.setProperty('--burst-y', `${Math.sin(angle) * distance}px`);
            
            container.appendChild(particle);
            
            setTimeout(() => {
                if (particle.parentNode) {
                    particle.parentNode.removeChild(particle);
                }
            }, 1000);
        }
    }
    
    createRevelationSparkles(cup) {
        const rect = cup.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;
        
        for (let i = 0; i < 50; i++) {
            const sparkle = document.createElement('div');
            sparkle.className = 'revelation-sparkle';
            sparkle.textContent = '✨';
            sparkle.style.position = 'fixed';
            sparkle.style.left = centerX + (Math.random() - 0.5) * 200 + 'px';
            sparkle.style.top = centerY + (Math.random() - 0.5) * 200 + 'px';
            sparkle.style.fontSize = Math.random() * 20 + 16 + 'px';
            sparkle.style.pointerEvents = 'none';
            sparkle.style.zIndex = '9999';
            sparkle.style.animation = `sparkleExplode 2s ease-out forwards`;
            sparkle.style.animationDelay = Math.random() * 0.5 + 's';
            
            document.body.appendChild(sparkle);
            
            setTimeout(() => {
                if (sparkle.parentNode) {
                    sparkle.parentNode.removeChild(sparkle);
                }
            }, 2500);
        }
    }
    
    createMassiveCosmicBurst() {
        const revelation = document.getElementById('tribe-revelation');
        if (!revelation) return;
        
        const burst = revelation.querySelector('.cosmic-burst');
        if (!burst) return;
        
        // Create massive burst effect
        for (let i = 0; i < 100; i++) {
            const particle = document.createElement('div');
            particle.className = 'massive-burst-particle';
            particle.style.left = '50%';
            particle.style.top = '50%';
            
            const angle = (Math.PI * 2 * i) / 100;
            const distance = 200 + Math.random() * 100;
            particle.style.setProperty('--burst-x', `${Math.cos(angle) * distance}px`);
            particle.style.setProperty('--burst-y', `${Math.sin(angle) * distance}px`);
            particle.style.backgroundColor = this.selectedTribe.colors[Math.floor(Math.random() * this.selectedTribe.colors.length)];
            
            burst.appendChild(particle);
            
            setTimeout(() => {
                if (particle.parentNode) {
                    particle.parentNode.removeChild(particle);
                }
            }, 2000);
        }
    }
    
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    createDynamicStyles() {
        const style = document.createElement('style');
        style.textContent = `
            /* Cosmic Shell Game Styles */
            .shell-game-container {
                position: relative;
                width: 100%;
                max-width: 500px;
                margin: 0 auto;
                padding: 20px;
            }
            
            .cosmic-shells {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 20px;
                margin: 40px 0;
                position: relative;
            }
            
            .shell-cup {
                position: relative;
                width: 120px;
                height: 120px;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .shell-cup.selectable:hover {
                transform: translateY(-5px);
            }
            
            .shell-cup.hovering {
                filter: drop-shadow(0 0 20px rgba(147, 51, 234, 0.6));
            }
            
            .shell-cup.ready-to-choose {
                animation: readyGlow 2s ease-in-out infinite;
            }
            
            .shell-cup.chosen-cup {
                animation: chosenGlow 1s ease-in-out infinite;
                transform: scale(1.1);
            }
            
            .cup-lid {
                position: absolute;
                top: 0;
                left: 50%;
                transform: translateX(-50%);
                font-size: 3rem;
                z-index: 10;
                filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.5));
            }
            
            .cup-base {
                position: absolute;
                bottom: 0;
                left: 50%;
                transform: translateX(-50%);
                width: 100px;
                height: 60px;
                background: linear-gradient(135deg, #1a1a2e, #16213e);
                border-radius: 50px 50px 20px 20px;
                border: 2px solid rgba(147, 51, 234, 0.3);
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .hidden-tribe {
                font-size: 2.5rem;
                opacity: 0;
                transform: scale(0);
                transition: all 0.3s ease;
            }
            
            .shuffle-stage {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                text-align: center;
                background: rgba(0, 0, 0, 0.9);
                padding: 30px;
                border-radius: 20px;
                border: 2px solid rgba(147, 51, 234, 0.5);
                z-index: 100;
                display: none;
            }
            
            .cosmic-energy {
                position: relative;
                width: 100px;
                height: 100px;
                margin: 0 auto 20px;
                background: radial-gradient(circle, rgba(147, 51, 234, 0.3), transparent);
                border-radius: 50%;
                animation: energyPulse 2s ease-in-out infinite;
            }
            
            .energy-particle {
                position: absolute;
                width: 4px;
                height: 4px;
                background: #9333ea;
                border-radius: 50%;
                animation: particleSwirl 3s linear infinite;
            }
            
            .shuffle-text {
                color: #9333ea;
                font-size: 1.2rem;
                font-weight: bold;
                animation: textGlow 2s ease-in-out infinite;
            }
            
            .choice-prompt {
                text-align: center;
                margin: 20px 0;
                opacity: 0;
                transform: translateY(20px);
            }
            
            .choice-prompt.fade-in-prompt {
                animation: fadeInPrompt 1s ease-out forwards;
            }
            
            .prompt-text {
                font-size: 1.4rem;
                color: #9333ea;
                font-weight: bold;
                margin-bottom: 10px;
                animation: promptGlow 2s ease-in-out infinite;
            }
            
            .prompt-subtitle {
                font-size: 1rem;
                color: rgba(147, 51, 234, 0.7);
                font-style: italic;
            }
            
            .tribe-revelation {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                z-index: 1000;
                opacity: 0;
                scale: 0;
            }
            
            .tribe-revelation.revelation-entrance {
                animation: revelationEntrance 1.5s ease-out forwards;
            }
            
            .revelation-container {
                position: relative;
                padding: 40px;
                background: rgba(0, 0, 0, 0.95);
                border-radius: 30px;
                border: 3px solid rgba(147, 51, 234, 0.6);
                text-align: center;
                min-width: 400px;
                box-shadow: 0 0 50px rgba(147, 51, 234, 0.5);
            }
            
            .cosmic-burst {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 100%;
                height: 100%;
                pointer-events: none;
            }
            
            .tribe-card-epic {
                position: relative;
                z-index: 10;
                padding: 30px;
                border-radius: 20px;
                background: linear-gradient(135deg, #9333ea, #a855f7, #c084fc);
                color: white;
                text-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            }
            
            .tribe-emoji-massive {
                font-size: 4rem;
                margin-bottom: 20px;
                animation: emojiFloat 3s ease-in-out infinite;
            }
            
            .tribe-name-epic {
                font-size: 2rem;
                font-weight: bold;
                margin-bottom: 15px;
                animation: nameGlow 2s ease-in-out infinite;
            }
            
            .tribe-tagline-epic {
                font-size: 1.2rem;
                font-style: italic;
                opacity: 0.9;
            }
            
            .cosmic-burst-particle {
                position: absolute;
                width: 6px;
                height: 6px;
                background: #9333ea;
                border-radius: 50%;
                animation: burstParticle 1s ease-out forwards;
            }
            
            .massive-burst-particle {
                position: absolute;
                width: 8px;
                height: 8px;
                border-radius: 50%;
                animation: massiveBurstParticle 2s ease-out forwards;
            }
            
            .revelation-sparkle {
                animation: sparkleExplode 2s ease-out forwards;
            }
            
            /* Animations */
            @keyframes liftLid {
                0% { transform: translateX(-50%) translateY(0); }
                100% { transform: translateX(-50%) translateY(-40px); opacity: 0.7; }
            }
            
            @keyframes coverLid {
                0% { transform: translateX(-50%) translateY(-40px); opacity: 0.7; }
                100% { transform: translateX(-50%) translateY(0); opacity: 1; }
            }
            
            @keyframes revealTribe {
                0% { opacity: 0; transform: scale(0); }
                100% { opacity: 1; transform: scale(1); }
            }
            
            @keyframes hideTribe {
                0% { opacity: 1; transform: scale(1); }
                100% { opacity: 0; transform: scale(0); }
            }
            
            @keyframes swapLeft {
                0% { transform: translateX(0); }
                50% { transform: translateX(-140px) translateY(-20px); }
                100% { transform: translateX(0); }
            }
            
            @keyframes swapRight {
                0% { transform: translateX(0); }
                50% { transform: translateX(140px) translateY(-20px); }
                100% { transform: translateX(0); }
            }
            
            @keyframes cosmicSpin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(720deg); }
            }
            
            @keyframes readyGlow {
                0%, 100% { box-shadow: 0 0 20px rgba(147, 51, 234, 0.3); }
                50% { box-shadow: 0 0 40px rgba(147, 51, 234, 0.6); }
            }
            
            @keyframes chosenGlow {
                0%, 100% { box-shadow: 0 0 30px rgba(255, 215, 0, 0.8); }
                50% { box-shadow: 0 0 60px rgba(255, 215, 0, 1); }
            }
            
            @keyframes epicLidLift {
                0% { transform: translateX(-50%) translateY(0); }
                100% { transform: translateX(-50%) translateY(-80px) scale(1.5); opacity: 0; }
            }
            
            @keyframes epicTribeReveal {
                0% { opacity: 0; transform: scale(0); }
                50% { opacity: 1; transform: scale(1.2); }
                100% { opacity: 1; transform: scale(1); }
            }
            
            @keyframes energyPulse {
                0%, 100% { transform: scale(1); opacity: 0.5; }
                50% { transform: scale(1.2); opacity: 1; }
            }
            
            @keyframes particleSwirl {
                0% { transform: rotate(0deg) translateX(30px) rotate(0deg); }
                100% { transform: rotate(360deg) translateX(30px) rotate(-360deg); }
            }
            
            @keyframes textGlow {
                0%, 100% { text-shadow: 0 0 10px rgba(147, 51, 234, 0.5); }
                50% { text-shadow: 0 0 20px rgba(147, 51, 234, 1); }
            }
            
            @keyframes fadeInPrompt {
                0% { opacity: 0; transform: translateY(20px); }
                100% { opacity: 1; transform: translateY(0); }
            }
            
            @keyframes promptGlow {
                0%, 100% { text-shadow: 0 0 10px rgba(147, 51, 234, 0.7); }
                50% { text-shadow: 0 0 20px rgba(147, 51, 234, 1); }
            }
            
            @keyframes revelationEntrance {
                0% { opacity: 0; scale: 0; transform: translate(-50%, -50%) rotate(180deg); }
                100% { opacity: 1; scale: 1; transform: translate(-50%, -50%) rotate(0deg); }
            }
            
            @keyframes emojiFloat {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-10px); }
            }
            
            @keyframes nameGlow {
                0%, 100% { text-shadow: 0 0 10px rgba(255, 255, 255, 0.5); }
                50% { text-shadow: 0 0 20px rgba(255, 255, 255, 1); }
            }
            
            @keyframes burstParticle {
                0% { transform: translate(0, 0) scale(1); opacity: 1; }
                100% { transform: translate(var(--burst-x), var(--burst-y)) scale(0); opacity: 0; }
            }
            
            @keyframes massiveBurstParticle {
                0% { transform: translate(0, 0) scale(1); opacity: 1; }
                100% { transform: translate(var(--burst-x), var(--burst-y)) scale(0); opacity: 0; }
            }
            
            @keyframes sparkleExplode {
                0% { opacity: 0; transform: scale(0); }
                50% { opacity: 1; transform: scale(1); }
                100% { opacity: 0; transform: scale(0) translateY(-100px); }
            }
            
            /* Responsive Design */
            @media (max-width: 768px) {
                .cosmic-shells {
                    gap: 10px;
                }
                
                .shell-cup {
                    width: 80px;
                    height: 80px;
                }
                
                .cup-lid {
                    font-size: 2rem;
                }
                
                .hidden-tribe {
                    font-size: 1.5rem;
                }
                
                .revelation-container {
                    min-width: 300px;
                    padding: 20px;
                }
                
                .tribe-emoji-massive {
                    font-size: 3rem;
                }
                
                .tribe-name-epic {
                    font-size: 1.5rem;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

// Global instance
window.cosmicShellGame = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('[CosmicShellGame] DOMContentLoaded fired');
    // Only initialize on tribe sorting step
    if (document.getElementById('step-tribe-sorting')) {
        console.log('[CosmicShellGame] Creating new CosmicShellGame instance');
        window.cosmicShellGame = new CosmicShellGame();
    }
});