/**
 * 🌟 MAINTENANCE FRONTEND JAVASCRIPT
 * Magical starfield and interactive animations
 */

(function() {
    'use strict';
    
    // 🌟 Global variables
    let canvas, ctx;
    let stars = [];
    let animationId;
    let mouseX = 0, mouseY = 0;
    let progressValue = 0;
    
    // 🎨 Configuration
    const config = {
        starCount: window.innerWidth < 768 ? 80 : 150,
        starSpeed: 0.1,
        starSize: { min: 1, max: 3 },
        twinkleSpeed: 0.02,
        mouseInfluence: 50,
        progressSpeed: 0.5
    };
    
    /**
     * 🚀 Initialize everything when DOM loads
     */
    document.addEventListener('DOMContentLoaded', function() {
        initStarfield();
        initInteractions();
        initProgressAnimation();
        initFloatingOrbs();
        initAudioEffects();
        
        console.log('✨ Maintenance page magic initialized');
    });
    
    /**
     * 🌌 Initialize the magical starfield
     */
    function initStarfield() {
        canvas = document.getElementById('starfield-canvas');
        if (!canvas) return;
        
        ctx = canvas.getContext('2d');
        resizeCanvas();
        createStars();
        animateStarfield();
        
        // Handle window resize
        window.addEventListener('resize', debounce(resizeCanvas, 250));
    }
    
    /**
     * 📐 Resize canvas to full screen
     */
    function resizeCanvas() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        
        // Adjust star count based on screen size
        const newStarCount = window.innerWidth < 768 ? 80 : 150;
        if (newStarCount !== config.starCount) {
            config.starCount = newStarCount;
            createStars(); // Recreate stars with new count
        }
    }
    
    /**
     * ⭐ Create the star field
     */
    function createStars() {
        stars = [];
        
        for (let i = 0; i < config.starCount; i++) {
            stars.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                size: Math.random() * (config.starSize.max - config.starSize.min) + config.starSize.min,
                alpha: Math.random(),
                twinkleSpeed: Math.random() * config.twinkleSpeed + 0.005,
                direction: Math.random() * Math.PI * 2,
                speed: Math.random() * config.starSpeed + 0.05,
                color: getStarColor()
            });
        }
    }
    
    /**
     * 🎨 Get random star color (mostly green angel theme)
     */
    function getStarColor() {
        const colors = [
            'rgba(174, 214, 4, 0.8)',    // Main green
            'rgba(174, 214, 4, 0.6)',    // Lighter green
            'rgba(255, 255, 255, 0.7)',  // White
            'rgba(174, 214, 4, 0.9)',    // Bright green
            'rgba(200, 230, 50, 0.6)'    // Yellow-green
        ];
        return colors[Math.floor(Math.random() * colors.length)];
    }
    
    /**
     * 🎬 Animate the starfield
     */
    function animateStarfield() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Create subtle gradient background
        const gradient = ctx.createRadialGradient(
            canvas.width / 2, canvas.height / 2, 0,
            canvas.width / 2, canvas.height / 2, Math.max(canvas.width, canvas.height) / 2
        );
        gradient.addColorStop(0, 'rgba(34, 34, 34, 0.3)');
        gradient.addColorStop(1, 'rgba(17, 17, 17, 0.8)');
        
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        // Update and draw stars
        stars.forEach(updateStar);
        
        animationId = requestAnimationFrame(animateStarfield);
    }
    
    /**
     * ⭐ Update individual star
     */
    function updateStar(star) {
        // Twinkling effect
        star.alpha += Math.sin(Date.now() * star.twinkleSpeed) * 0.02;
        star.alpha = Math.max(0.2, Math.min(1, star.alpha));
        
        // Gentle floating movement
        star.x += Math.cos(star.direction) * star.speed;
        star.y += Math.sin(star.direction) * star.speed;
        
        // Mouse interaction (subtle)
        const dx = mouseX - star.x;
        const dy = mouseY - star.y;
        const distance = Math.sqrt(dx * dx + dy * dy);
        
        if (distance < config.mouseInfluence) {
            const force = (config.mouseInfluence - distance) / config.mouseInfluence;
            star.x -= dx * force * 0.01;
            star.y -= dy * force * 0.01;
        }
        
        // Wrap around screen edges
        if (star.x < 0) star.x = canvas.width;
        if (star.x > canvas.width) star.x = 0;
        if (star.y < 0) star.y = canvas.height;
        if (star.y > canvas.height) star.y = 0;
        
        // Draw star
        drawStar(star);
    }
    
    /**
     * ✨ Draw individual star with glow effect
     */
    function drawStar(star) {
        ctx.save();
        
        // Create glow effect
        ctx.shadowColor = star.color;
        ctx.shadowBlur = star.size * 3;
        
        ctx.globalAlpha = star.alpha;
        ctx.fillStyle = star.color;
        
        ctx.beginPath();
        ctx.arc(star.x, star.y, star.size, 0, Math.PI * 2);
        ctx.fill();
        
        // Extra bright center
        ctx.shadowBlur = 0;
        ctx.globalAlpha = star.alpha * 0.8;
        ctx.fillStyle = 'rgba(255, 255, 255, 0.9)';
        ctx.beginPath();
        ctx.arc(star.x, star.y, star.size * 0.3, 0, Math.PI * 2);
        ctx.fill();
        
        ctx.restore();
    }
    
    /**
     * 🖱️ Initialize mouse interactions
     */
    function initInteractions() {
        // Track mouse movement
        document.addEventListener('mousemove', function(e) {
            mouseX = e.clientX;
            mouseY = e.clientY;
        });
        
        // Angel logo interaction
        const angelLogo = document.getElementById('angel-logo');
        if (angelLogo) {
            angelLogo.addEventListener('click', angelLogoClick);
            angelLogo.addEventListener('mouseenter', angelLogoHover);
        }
        
        // Touch support for mobile
        document.addEventListener('touchmove', function(e) {
            if (e.touches[0]) {
                mouseX = e.touches[0].clientX;
                mouseY = e.touches[0].clientY;
            }
        });
    }
    
    /**
     * 🪽 Angel logo click effect
     */
    function angelLogoClick() {
        // Create sparkle burst effect
        createSparkles(this);
        
        // Add rotation animation
        this.style.transform = 'scale(1.2) rotate(360deg)';
        this.style.transition = 'transform 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
        
        setTimeout(() => {
            this.style.transform = '';
            this.style.transition = '';
        }, 800);
        
        // Play sound effect if available
        playSound('angel-chime');
    }
    
    /**
     * 🪽 Angel logo hover effect
     */
    function angelLogoHover() {
        // Subtle scale and glow enhancement
        this.style.transform = 'scale(1.05)';
        this.style.transition = 'transform 0.3s ease';
        
        this.addEventListener('mouseleave', function() {
            this.style.transform = '';
        }, { once: true });
    }
    
    /**
     * ✨ Create sparkle effect
     */
    function createSparkles(element) {
        const rect = element.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;
        
        for (let i = 0; i < 12; i++) {
            const sparkle = document.createElement('div');
            sparkle.className = 'sparkle';
            sparkle.style.cssText = `
                position: fixed;
                left: ${centerX}px;
                top: ${centerY}px;
                width: 4px;
                height: 4px;
                background: #aed604;
                border-radius: 50%;
                pointer-events: none;
                z-index: 1000;
            `;
            
            document.body.appendChild(sparkle);
            
            // Animate sparkle
            const angle = (i / 12) * Math.PI * 2;
            const distance = 80 + Math.random() * 40;
            const duration = 800 + Math.random() * 400;
            
            sparkle.animate([
                {
                    transform: 'translate(0, 0) scale(1)',
                    opacity: 1
                },
                {
                    transform: `translate(${Math.cos(angle) * distance}px, ${Math.sin(angle) * distance}px) scale(0)`,
                    opacity: 0
                }
            ], {
                duration: duration,
                easing: 'cubic-bezier(0.4, 0, 0.2, 1)'
            }).onfinish = () => sparkle.remove();
        }
    }
    
    /**
     * 📊 Initialize progress animation
     */
    function initProgressAnimation() {
        const progressFill = document.getElementById('progress-fill');
        const progressPercent = document.getElementById('progress-percent');
        
        if (!progressFill || !progressPercent) return;
        
        // Animate progress bar
        function updateProgress() {
            progressValue += config.progressSpeed;
            
            // Simulate realistic progress with plateaus
            let displayValue;
            if (progressValue < 30) {
                displayValue = progressValue;
            } else if (progressValue < 60) {
                displayValue = 30 + (progressValue - 30) * 0.5;
            } else if (progressValue < 90) {
                displayValue = 45 + (progressValue - 60) * 0.3;
            } else {
                displayValue = Math.min(87, 54 + (progressValue - 90) * 0.1);
            }
            
            progressFill.style.width = displayValue + '%';
            progressPercent.textContent = Math.floor(displayValue) + '%';
            
            // Reset after reaching near completion
            if (progressValue > 200) {
                progressValue = 0;
            }
            
            requestAnimationFrame(updateProgress);
        }
        
        updateProgress();
    }
    
    /**
     * 🪽 Initialize floating orbs with enhanced physics
     */
    function initFloatingOrbs() {
        const orbs = document.querySelectorAll('.floating-orb');
        
        orbs.forEach((orb, index) => {
            const speed = parseFloat(orb.dataset.speed) || 0.5;
            let time = index * 1000; // Stagger start times
            
            function animateOrb() {
                time += 16; // ~60fps
                
                const baseX = parseFloat(orb.style.left || getComputedStyle(orb).left);
                const baseY = parseFloat(orb.style.top || getComputedStyle(orb).top);
                
                const offsetX = Math.sin(time * speed * 0.001) * 20;
                const offsetY = Math.cos(time * speed * 0.0008) * 15;
                const scale = 1 + Math.sin(time * speed * 0.0012) * 0.1;
                const opacity = 0.3 + Math.sin(time * speed * 0.0015) * 0.2;
                
                orb.style.transform = `translate(${offsetX}px, ${offsetY}px) scale(${scale})`;
                orb.style.opacity = opacity;
                
                requestAnimationFrame(animateOrb);
            }
            
            animateOrb();
        });
    }
    
    /**
     * 🎵 Initialize audio effects (optional)
     */
    function initAudioEffects() {
        // Placeholder for ambient sound initialization
        // Could add gentle ambient tones here
    }
    
    /**
     * 🔊 Play sound effect
     */
    function playSound(soundName) {
        // Placeholder for sound effects
        // Could add subtle chimes or magical sounds
        console.log(`🔊 Playing sound: ${soundName}`);
    }
    
    /**
     * 🎯 Utility function for debouncing
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    /**
     * 🎭 Add special effects for different times of day
     */
    function addTimeBasedEffects() {
        const hour = new Date().getHours();
        
        if (hour >= 22 || hour <= 6) {
            // Night mode - more stars, deeper colors
            config.starCount *= 1.3;
            document.body.classList.add('night-mode');
        } else if (hour >= 6 && hour <= 9) {
            // Dawn mode - warmer colors
            document.body.classList.add('dawn-mode');
        } else if (hour >= 17 && hour <= 22) {
            // Dusk mode - purple tints
            document.body.classList.add('dusk-mode');
        }
    }
    
    /**
     * 🎮 Easter egg - Konami code
     */
    function initEasterEgg() {
        const konamiCode = [
            'ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown',
            'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight',
            'KeyB', 'KeyA'
        ];
        let konamiIndex = 0;
        
        document.addEventListener('keydown', function(e) {
            if (e.code === konamiCode[konamiIndex]) {
                konamiIndex++;
                if (konamiIndex === konamiCode.length) {
                    activateEasterEgg();
                    konamiIndex = 0;
                }
            } else {
                konamiIndex = 0;
            }
        });
    }
    
    /**
     * 🎉 Activate easter egg effect
     */
    function activateEasterEgg() {
        // Rainbow mode!
        document.body.classList.add('rainbow-mode');
        
        // Create rainbow stars
        stars.forEach(star => {
            star.color = `hsl(${Math.random() * 360}, 70%, 60%)`;
        });
        
        // Show special message
        const message = document.createElement('div');
        message.textContent = '🌈 Rainbow Angel Mode Activated! ✨';
        message.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.8);
            color: #fff;
            padding: 20px 30px;
            border-radius: 25px;
            font-weight: 600;
            z-index: 10000;
            animation: rainbow-pulse 2s ease-in-out;
        `;
        
        document.body.appendChild(message);
        
        setTimeout(() => message.remove(), 3000);
        setTimeout(() => {
            document.body.classList.remove('rainbow-mode');
            createStars(); // Reset to normal colors
        }, 10000);
    }
    
    // Initialize time-based effects and easter egg
    document.addEventListener('DOMContentLoaded', function() {
        addTimeBasedEffects();
        initEasterEgg();
    });
    
    // Clean up on page unload
    window.addEventListener('beforeunload', function() {
        if (animationId) {
            cancelAnimationFrame(animationId);
        }
    });
    
})();