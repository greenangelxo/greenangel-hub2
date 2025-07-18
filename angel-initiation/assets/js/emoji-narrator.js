/**
 * EmojiNarrator - Animated emoji narrator system
 * Creates lifelike animations using timed emoji frame sequences
 */

class EmojiNarrator {
    constructor(options = {}) {
        this.container = options.container || null;
        this.speechBubble = options.speechBubble || null;
        
        // Animation state
        this.currentFrames = [];
        this.currentFrameIndex = 0;
        this.animationSpeed = 300; // Default 300ms per frame
        this.isAnimating = false;
        this.animationTimer = null;
        this.isPaused = false;
        
        // Speech state
        this.isSpeaking = false;
        this.currentScript = null;
        this.scriptIndex = 0;
        this.speechTimer = null;
        
        // Initialize if container provided
        if (this.container) {
            this.init();
        }
    }
    
    init() {
        this.setupElements();
        this.setupStyles();
        console.log('EmojiNarrator initialized');
    }
    
    setupElements() {
        // Create narrator layout if not exists
        if (!this.container.querySelector('.narrator-layout')) {
            const layout = document.createElement('div');
            layout.className = 'narrator-layout';
            
            // Central face - positioned inside the circle
            const face = document.createElement('div');
            face.className = 'narrator-face';
            face.textContent = '😴';
            layout.appendChild(face);
            
            this.container.appendChild(layout);
        }
        
        this.narratorFace = this.container.querySelector('.narrator-face');
        
        // Create speech bubble if not exists
        if (!this.speechBubble) {
            const bubble = document.createElement('div');
            bubble.className = 'narrator-speech-bubble';
            bubble.style.display = 'none';
            
            const text = document.createElement('div');
            text.className = 'speech-text';
            bubble.appendChild(text);
            
            const tail = document.createElement('div');
            tail.className = 'speech-tail';
            bubble.appendChild(tail);
            
            this.container.appendChild(bubble);
            this.speechBubble = bubble;
        }
        
        this.speechText = this.speechBubble.querySelector('.speech-text');
    }
    
    setupStyles() {
        // Add dynamic styles for narrator
        const style = document.createElement('style');
        style.textContent = `
            .narrator-container {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 20px;
                z-index: 10;
            }
            
            .narrator-face {
                position: relative !important;
                transform: none !important;
                font-size: 40px !important;
                line-height: 1 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                text-align: center !important;
                transition: all 0.2s ease;
                user-select: none;
                animation: narratorResting 6s ease-in-out infinite;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: 100% !important;
            }
            
            .narrator-speech-bubble {
                position: absolute;
                top: -80px;
                left: 50%;
                transform: translateX(-50%);
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border: 2px solid rgba(147, 51, 234, 0.3);
                border-radius: 15px;
                padding: 12px 18px;
                max-width: 300px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
                z-index: 100;
                animation: bubbleEntrance 0.3s ease-out;
            }
            
            .speech-text {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                font-size: 14px;
                color: #333;
                line-height: 1.4;
                text-align: center;
                font-weight: 500;
            }
            
            .speech-tail {
                position: absolute;
                bottom: -8px;
                left: 50%;
                transform: translateX(-50%);
                width: 0;
                height: 0;
                border-left: 8px solid transparent;
                border-right: 8px solid transparent;
                border-top: 8px solid rgba(255, 255, 255, 0.95);
            }
            
            @keyframes narratorFloat {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-3px); }
            }
            
            @keyframes bubbleEntrance {
                0% { 
                    opacity: 0; 
                    transform: translateX(-50%) scale(0.8) translateY(10px); 
                }
                100% { 
                    opacity: 1; 
                    transform: translateX(-50%) scale(1) translateY(0px); 
                }
            }
            
            @keyframes bubbleExit {
                0% { 
                    opacity: 1; 
                    transform: translateX(-50%) scale(1) translateY(0px); 
                }
                100% { 
                    opacity: 0; 
                    transform: translateX(-50%) scale(0.8) translateY(-10px); 
                }
            }
            
            /* Responsive adjustments */
            @media (max-width: 768px) {
                .narrator-face {
                    font-size: 36px;
                }
                
                .narrator-speech-bubble {
                    max-width: 250px;
                    padding: 10px 14px;
                }
                
                .speech-text {
                    font-size: 13px;
                }
            }
        `;
        
        if (!document.querySelector('#emoji-narrator-styles')) {
            style.id = 'emoji-narrator-styles';
            document.head.appendChild(style);
        }
    }
    
    startAnimation(frames, speed = 300) {
        this.stopAnimation();
        
        this.currentFrames = frames;
        this.currentFrameIndex = 0;
        this.animationSpeed = speed;
        this.isAnimating = true;
        this.isPaused = false;
        
        // Switch to active animation mode
        this.setActiveMode();
        
        this.animateFrame();
        
        console.log('Started animation with frames:', frames);
    }
    
    animateFrame() {
        if (!this.isAnimating || this.isPaused) return;
        
        if (this.currentFrames.length === 0) return;
        
        // Parse frame - now only handles single emoji
        const frame = this.currentFrames[this.currentFrameIndex];
        
        // Extract only the face emoji if it's a pattern
        let faceEmoji = frame;
        if (frame.length === 3) {
            // If it's a pattern like "🙌😄🙌", extract middle emoji
            faceEmoji = frame[1];
        }
        
        // Update only the face
        this.narratorFace.textContent = faceEmoji;
        
        // Move to next frame
        this.currentFrameIndex = (this.currentFrameIndex + 1) % this.currentFrames.length;
        
        // Schedule next frame
        this.animationTimer = setTimeout(() => {
            this.animateFrame();
        }, this.animationSpeed);
    }
    
    stopAnimation() {
        this.isAnimating = false;
        if (this.animationTimer) {
            clearTimeout(this.animationTimer);
            this.animationTimer = null;
        }
        
        // Return to resting mode
        this.setRestingMode();
    }
    
    setActiveMode() {
        if (this.narratorFace) {
            this.narratorFace.style.animation = 'narratorActive 0.8s ease-in-out infinite';
        }
    }
    
    setRestingMode() {
        if (this.narratorFace) {
            this.narratorFace.style.animation = 'narratorResting 6s ease-in-out infinite';
        }
    }
    
    pauseAnimation() {
        this.isPaused = true;
    }
    
    resumeAnimation() {
        this.isPaused = false;
        if (this.isAnimating) {
            this.animateFrame();
        }
    }
    
    setStatic(emoji) {
        this.stopAnimation();
        this.narratorFace.textContent = emoji;
        this.setRestingMode();
    }
    
    showSpeech(text, persistent = false) {
        // Hide any existing speech first
        if (this.isSpeaking) {
            this.hideSpeech();
        }
        
        // Clear any existing speech timer
        if (this.speechTimer) {
            clearTimeout(this.speechTimer);
            this.speechTimer = null;
        }
        
        // Show new speech
        this.speechText.textContent = text;
        this.speechBubble.style.display = 'block';
        this.speechBubble.style.animation = 'bubbleEntrance 0.3s ease-out';
        this.isSpeaking = true;
        this.isPersistentSpeech = persistent;
        
        // Switch to active mode when speaking
        this.setActiveMode();
        
        console.log('Showing speech:', text, persistent ? '(persistent)' : '');
    }
    
    hideSpeech() {
        if (!this.isSpeaking) return;
        
        this.speechBubble.style.animation = 'bubbleExit 0.3s ease-out';
        setTimeout(() => {
            this.speechBubble.style.display = 'none';
            this.isSpeaking = false;
            this.isPersistentSpeech = false;
            
            // Return to resting mode when not speaking
            if (!this.isAnimating) {
                this.setRestingMode();
            }
        }, 300);
    }
    
    speak(text, duration = 3000) {
        return new Promise((resolve) => {
            this.showSpeech(text, false);
            
            this.speechTimer = setTimeout(() => {
                this.hideSpeech();
                resolve();
            }, duration);
        });
    }
    
    speakPersistent(text) {
        this.showSpeech(text, true);
        return Promise.resolve();
    }
    
    async runScript(script) {
        this.currentScript = script;
        this.scriptIndex = 0;
        
        console.log('Running script:', script);
        
        for (const segment of script) {
            // Start animation for this segment
            if (segment.frames) {
                this.startAnimation(segment.frames, segment.speed || 300);
            }
            
            // Show speech if provided
            if (segment.text) {
                await this.speak(segment.text, segment.duration || 3000);
            }
            
            // Wait for pause if specified
            if (segment.pause) {
                await this.wait(segment.pause);
            }
        }
        
        console.log('Script completed');
    }
    
    wait(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    // Predefined animation sets
    static getAnimationSet(mood) {
        const sets = {
            sleeping: {
                frames: ["😴", "💤", "😴"],
                speed: 400
            },
            greeting: {
                frames: ["😄", "😁", "😊"],
                speed: 300
            },
            excited: {
                frames: ["😏", "😳", "😄"],
                speed: 300
            },
            shocked: {
                frames: ["😲", "🤯", "😱"],
                speed: 250
            },
            calm: {
                frames: ["😌", "😎", "🙂"],
                speed: 300
            },
            heartfelt: {
                frames: ["🥹", "😇", "😌"],
                speed: 300
            },
            thinking: {
                frames: ["🤔", "🤔", "🤔"],
                speed: 350
            },
            celebration: {
                frames: ["😄", "🤩", "😁"],
                speed: 200
            }
        };
        
        return sets[mood] || sets.greeting;
    }
    
    // Quick methods for common animations
    sleep() {
        const set = EmojiNarrator.getAnimationSet('sleeping');
        this.startAnimation(set.frames, set.speed);
    }
    
    greet() {
        const set = EmojiNarrator.getAnimationSet('greeting');
        this.startAnimation(set.frames, set.speed);
    }
    
    getExcited() {
        const set = EmojiNarrator.getAnimationSet('excited');
        this.startAnimation(set.frames, set.speed);
    }
    
    getShocked() {
        const set = EmojiNarrator.getAnimationSet('shocked');
        this.startAnimation(set.frames, set.speed);
    }
    
    stayCalm() {
        const set = EmojiNarrator.getAnimationSet('calm');
        this.startAnimation(set.frames, set.speed);
    }
    
    showLove() {
        const set = EmojiNarrator.getAnimationSet('heartfelt');
        this.startAnimation(set.frames, set.speed);
    }
    
    celebrate() {
        const set = EmojiNarrator.getAnimationSet('celebration');
        this.startAnimation(set.frames, set.speed);
    }
    
    destroy() {
        this.stopAnimation();
        if (this.speechTimer) {
            clearTimeout(this.speechTimer);
        }
        
        // Remove only the narrator elements, not the container
        const layout = this.container.querySelector('.narrator-layout');
        if (layout) {
            layout.remove();
        }
        
        const speechBubble = this.container.querySelector('.narrator-speech-bubble');
        if (speechBubble) {
            speechBubble.remove();
        }
        
        console.log('EmojiNarrator destroyed');
    }
}

// Make it globally available
window.EmojiNarrator = EmojiNarrator;