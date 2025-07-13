/**
 * 🌟 GREEN ANGEL EMOJI PICKER - IDENTITY DESCRIPTIONS WITH LOCK ENFORCEMENT
 * Manages emoji bios with lock enforcement
 * Mobile-first with animations
 * Gender-neutral identity descriptions
 * Enforces a 30-day selection lock
 */

(function() {
    'use strict';
    
    // 🔒 Lock state management
    let isLocked = false;
    let daysRemaining = 0;
    
    // 🎭 Emoji identity database (gender neutral)
    const emojiIdentities = {
        // 🧿 MYSTICAL EMOJIS ✨
        '✨': { name: 'Sparklecore', bio: 'You leave glitter in your wake and chaos in your step. Literally can\'t go unnoticed.' },
        '🌟': { name: 'Starseed', bio: 'Probably an alien. Definitely a vibe. You *feel* things before they happen.' },
        '💫': { name: 'Orbit Bender', bio: 'Reality? Optional. You\'re vibing 3 dimensions ahead of everyone else.' },
        '⭐': { name: 'Starbaby', bio: 'You believe in signs, serendipity, and main character moments. Always glowing.' },
        '🌙': { name: 'Midnight Muse', bio: 'Hottest at 3AM. Whispered secrets to the moon. Manifesting in whispers.' },
        '🔮': { name: 'Crystal Seer', bio: 'Sees through lies, shade, and bad energy. 99% intuition, 1% drama.' },
        '💎': { name: 'Diamond Soul', bio: 'Pure pressure-to-glow pipeline. You shine hard because you\'ve survived harder.' },
        '👑': { name: 'Crowned by Chaos', bio: 'You\'re royalty with a reputation. Rebellious, radiant, slightly unhinged.' },
        '🪄': { name: 'Wand Wielder', bio: 'You don\'t chase — you enchant. Things just... happen for you. Always have.' },
        '🧿': { name: 'Evil Eye Icon', bio: 'Protected, petty, powerful. You curse with your side-eye. Blessings optional.' },
        '🌌': { name: 'Galaxy Brain', bio: 'Thoughts are nebulae. Emotions are supernovas. You\'re a walking spiral of wonder.' },
        '🦄': { name: 'Unicorn Frequency', bio: 'Majestic, chaotic, high-vibrational. Believes in magic and also eats glitter.' },
        '👻': { name: 'Spooky Sweetheart', bio: 'Paranormally adorable. Might haunt dreams, but in the cuddliest way possible.' },
        '🔥': { name: 'Blaze Spirit', bio: 'Born of fire. Burns with love and passion to the point of recklessness.' },
        '⚡': { name: 'Static Icon', bio: 'Buzzing. Electric. Slightly unstable but in the most magnetic way possible.' },
        '🌈': { name: 'Rainbow Rider', bio: 'A walking spectrum of joy, chaos, and serotonin. You *are* the vibe.' },
        
        // 🌿 NATURE EMOJIS 🌸
        '🌿': { name: 'Leaf Mystic', bio: 'Photosynthesis royalty. You literally glow when you touch grass. Earth child energy.' },
        '🍃': { name: 'Breeze Whisperer', bio: 'Gentle chaos incarnate. You blow through lives leaving everything beautifully rearranged.' },
        '🌸': { name: 'Bloom Spirit', bio: 'Soft power personified. You blossom everywhere you go, unstoppably gorgeous.' },
        '🌺': { name: 'Tropical Storm', bio: 'Paradise with an edge. Beautiful, bold, and slightly dangerous to approach.' },
        '🌻': { name: 'Sunshine Dealer', bio: 'Your smile could power cities. Aggressively optimistic, impossibly warm.' },
        '🌷': { name: 'Tulip Royalty', bio: 'Elegant chaos. You bloom once a year but when you do, it\'s absolutely legendary.' },
        '🌹': { name: 'Thorn Sovereign', bio: 'Beautiful but handle with care. Love you or fear you — no in between.' },
        '🌵': { name: 'Desert Diamond', bio: 'Thrives where others wither. Prickly exterior, surprisingly soft center.' },
        '🌴': { name: 'Palm Paradise', bio: 'Vacation vibes personified. You make everywhere feel like a tropical getaway.' },
        '🦋': { name: 'Metamorphosis Muse', bio: 'Constantly evolving. Your glow-ups are legendary, your transformations epic.' },
        '🐝': { name: 'Honey Hustler', bio: 'Sweet with a sting. You pollinate everything you touch with pure magic.' },
        '🌼': { name: 'Daisy Dreamer', bio: 'Innocent with an edge. Underestimate you once — never again.' },
        '🍄': { name: 'Fungi Mystic', bio: 'You grow in the dark and emerge fabulous. Mysterious, earthy, absolutely iconic.' },
        '🌳': { name: 'Ancient Wisdom', bio: 'Old soul energy. Your roots run deep, your branches reach everything.' },
        '🌱': { name: 'Sprout Star', bio: 'New beginnings incarnate. You make fresh starts look absolutely effortless.' },
        '🌊': { name: 'Wave Rider', bio: 'Fluid, powerful, impossible to contain. You reshape everything in your path.' },
        
        // 🚀 COSMIC EMOJIS 🌌
        '🚀': { name: 'Launch Legend', bio: 'You don\'t just reach for stars — you collect them. Ambition with rocket fuel.' },
        '🛸': { name: 'UFO Royalty', bio: 'Not from this planet, definitely above this drama. Abduct hearts, leave no trace.' },
        '🌍': { name: 'Earth Guardian', bio: 'Planetary protector with style. You hold the world together, effortlessly.' },
        '🌎': { name: 'Globe Trotter', bio: 'The world is your runway. You collect countries like accessories.' },
        '🌏': { name: 'Eastern Enigma', bio: 'Mystical from every angle. Your energy literally shifts time zones.' },
        '☄️': { name: 'Comet Crasher', bio: 'Once-in-a-lifetime energy. When you appear, everything changes forever.' },
        '🌠': { name: 'Wish Granter', bio: 'People see you and start manifesting. You\'re literally a shooting star.' },
        '🔭': { name: 'Vision Quest', bio: 'You see what others miss. Clarity is your superpower, foresight your gift.' },
        '👽': { name: 'Alien Royalty', bio: 'Extraterrestrial elegance. Your vibe is from another dimension entirely.' },
        '🌑': { name: 'New Moon Mystery', bio: 'Dark energy specialist. You reset everything you touch, powerfully.' },
        '🪐': { name: 'Saturn Return', bio: 'You bring structure to chaos and lessons to the lost. Cosmic teacher vibes.' },
        '☀️': { name: 'Solar Sovereign', bio: 'Main character energy. Everything orbits around your gravitational pull.' },
        '🌞': { name: 'Sun Child', bio: 'Warmth incarnate. You melt hearts and ice walls with equal ease.' },
        
        // 💫 VIBES EMOJIS 😈
        '😈': { name: 'Divine Menace', bio: 'Angelic face, devilish thoughts. You\'re trouble in the most magnetic way.' },
        '😏': { name: 'Smirk Sovereign', bio: 'That smile says everything and nothing. Mysteriously irresistible energy.' },
        '🥵': { name: 'Heat Wave', bio: 'You raise the temperature just by existing. Dangerously hot energy.' },
        '🤤': { name: 'Temptation Station', bio: 'You\'re what people daydream about. Desire personified, irresistibly chaotic.' },
        '😍': { name: 'Heart Snatcher', bio: 'You collect hearts like trophies. Love at first sight is your specialty.' },
        '🥰': { name: 'Warm Chaos', bio: 'Soft but deadly. You kill with kindness and resurrect with cuddles.' },
        '😘': { name: 'Kiss Collector', bio: 'Affection dealer. You spread love like it\'s going out of style.' },
        '💋': { name: 'Lip Service', bio: 'Your words are spells, your energy is intoxicating. Pure seduction.' },
        '👅': { name: 'Taste Maker', bio: 'You set trends and break hearts. Flavor of the month, every month.' },
        '🍑': { name: 'Peach Perfect', bio: 'Sweet, soft, and absolutely irresistible. You\'re summer personified.' },
        '🍒': { name: 'Cherry Bomb', bio: 'Small but explosive. You pack maximum impact in the cutest package.' },
        '🍓': { name: 'Berry Seductive', bio: 'Sweet with seeds of chaos. You\'re dessert and destruction combined.' },
        '💦': { name: 'Splash Zone', bio: 'You make waves wherever you go. Refreshing, cleansing, absolutely essential.' },
        '💕': { name: 'Love Dealer', bio: 'You traffic in feelings and deal in emotions. Hearts are your currency.' },
        '💖': { name: 'Sparkle Heart', bio: 'Pure concentrated affection. You love hard and shine brighter.' },
        
        // 🦋 ANIMALS EMOJIS 🐍
        '🐍': { name: 'Serpent Sage', bio: 'Wisdom with a bite. You shed old skins and emerge more powerful.' },
        '🦁': { name: 'Mane Character', bio: 'Natural born leader. Your roar commands attention, your presence demands respect.' },
        '🐅': { name: 'Stripe Life', bio: 'Wild and untameable. You hunt your dreams with predator precision.' },
        '🐆': { name: 'Spot Check', bio: 'Fast, fierce, and impossible to catch. You move at your own speed.' },
        '🐉': { name: 'Dragon Energy', bio: 'Mythical power personified. You breathe fire and collect treasure.' },
        '🦊': { name: 'Fox Trickster', bio: 'Clever with a cute face. You outsmart everyone while looking adorable.' },
        '🐺': { name: 'Pack Leader', bio: 'Loyalty is your language, the hunt is your calling. Wild at heart.' },
        '🐙': { name: 'Tentacle Tactician', bio: 'You\'ve got your hands in everything. Mysterious depths, surprising reach.' },
        '🦈': { name: 'Apex Predator', bio: 'You smell opportunity from miles away. Efficient, deadly, absolutely iconic.' },
        '🐰': { name: 'Bunny Hop', bio: 'Soft exterior, survival instincts. You multiply joy wherever you land.' },
        '🐱': { name: 'Cat Attitude', bio: 'Independent, mysterious, and absolutely done with everyone\'s nonsense.' },
        '🐸': { name: 'Frog Prince', bio: 'You transform under the right conditions. Patience is your superpower.' },
        '🐢': { name: 'Slow Motion', bio: 'Steady wins the race. Your persistence is legendary, your timing perfect.' },
        '🦀': { name: 'Crab Walks', bio: 'You move sideways through life and somehow always reach your destination.' },
        
        // 🌶️ Spicy emojis
        '🌶️': { name: 'Spice Master', bio: 'You add heat to everything you touch. Life\'s too bland without you.' },
        '🍆': { name: 'Eggplant Royalty', bio: 'Mysterious, versatile, and absolutely essential. You know what you\'re about.' },
        '🍌': { name: 'Banana Drama', bio: 'Sweet, curved, and slightly unhinged. You\'re comedy gold with appeal.' },
        '🥒': { name: 'Cool Cucumber', bio: 'Crisp, fresh, and surprisingly dirty. You keep it cool while stirring chaos.' },
        '🥕': { name: 'Carrot Top', bio: 'Good for you and impossible to resist. Health goals with a twist.' },
        '🌭': { name: 'Hot Dog Energy', bio: 'You\'re the main event at every gathering. Always ready for action.' },
        '💊': { name: 'Pill Popper', bio: 'You\'re the cure everyone\'s looking for. Healing energy with side effects.' },
        '🚬': { name: 'Smoke Signal', bio: 'Mysterious, addictive, and definitely bad for health. Irresistibly dangerous.' },
        '🍷': { name: 'Wine Time', bio: 'You get better with age and make everything more fun. Sophisticated chaos.' },
        '🍸': { name: 'Cocktail Hour', bio: 'Mixed signals, mixed drinks, unmixed feelings. You\'re the party.' },
        '🍺': { name: 'Beer Buddy', bio: 'Casual, approachable, and surprisingly deep. You\'re everyone\'s favorite.' },
        '🥃': { name: 'Whiskey Business', bio: 'Smooth operator with a burn. You\'re aged to perfection.' },
        '💉': { name: 'Shot Caller', bio: 'You deliver exactly what people need, exactly when they need it.' },
        '🔞': { name: 'Adults Only', bio: 'Not for everyone, definitely for the brave. You require mature handling.' },
        '🆘': { name: 'Emergency Contact', bio: 'You\'re who people call in crisis. Reliable in chaos, steady in storms.' },
        '⚠️': { name: 'Warning Label', bio: 'Proceed with caution. You\'re exactly as dangerous as you appear.' },
        
        // 🎉 PARTY EMOJIS 🥳
        '🎉': { name: 'Celebration Station', bio: 'You turn ordinary moments into events. Life\'s a party when you\'re around.' },
        '🎊': { name: 'Confetti Cannon', bio: 'You explode with joy and cover everything in sparkle. Pure festive energy.' },
        '🥳': { name: 'Party Animal', bio: 'Born to celebrate, destined to dance. You make every day feel like a birthday.' },
        '🍾': { name: 'Champagne Problems', bio: 'Bubbly personality with expensive taste. You pop off in the best way.' },
        '🥂': { name: 'Toast Master', bio: 'You raise glasses and raise spirits. Every moment with you is worth celebrating.' },
        '🍻': { name: 'Cheers Champion', bio: 'You bring people together and make strangers into friends. Social magic.' },
        '🎭': { name: 'Drama Royalty', bio: 'Life\'s a stage and you\'re the star. You perform emotions like fine art.' },
        '🎪': { name: 'Circus Master', bio: 'You run the greatest show on earth. Chaos is your specialty, wonder your gift.' },
        '🎨': { name: 'Art Attack', bio: 'You paint outside the lines and color outside reality. Creative chaos personified.' },
        '🎯': { name: 'Target Practice', bio: 'You hit bullseyes in life and love. Your aim is legendary, your focus fierce.' },
        '🎲': { name: 'Lucky Roller', bio: 'You take chances and somehow always land on your feet. Fortune favors you.' },
        '🃏': { name: 'Wild Card', bio: 'Unpredictable, powerful, and absolutely essential. You change the entire game.' },
        '💰': { name: 'Money Magnet', bio: 'You attract abundance and prosperity. Golden touch, diamond mindset.' },
        '💸': { name: 'Cash Flow', bio: 'Money moves through you like water. You understand the art of circulation.' },
        '🎰': { name: 'Jackpot Energy', bio: 'You\'re what everyone\'s hoping for. When you show up, everybody wins.' },
        
        // ⚡ ENERGY EMOJIS 💥
        '💥': { name: 'Explosion Expert', bio: 'You arrive with impact and leave with aftershocks. Dynamite personality.' },
        '💢': { name: 'Anger Management', bio: 'You channel rage into power. Your fury is your fuel, your passion your strength.' },
        '💯': { name: 'Perfect Score', bio: 'You don\'t do anything halfway. Excellence is your baseline, perfection your goal.' },
        '🚨': { name: 'Alert System', bio: 'You sense danger before it arrives. Your warnings save lives, your instincts legendary.' },
        '☢️': { name: 'Radioactive', bio: 'You glow with power that changes everything around you. Handle with respect.' },
        '☣️': { name: 'Biohazard', bio: 'You\'re infectious in the best way. Your energy spreads and transforms everything.' },
        '🎯': { name: 'Bullseye', bio: 'You hit targets others can\'t even see. Precision is your art, accuracy your gift.' },
        '💣': { name: 'Time Bomb', bio: 'Powerful, explosive, and beautifully dangerous. You detonate with purpose.' },
        '🧨': { name: 'Dynamite', bio: 'Small package, massive impact. You blow minds and shatter expectations.' },
        '🔋': { name: 'Power Source', bio: 'You energize everything around you. Others charge up just being near you.' }
    };
    
    // 🎭 Identity modal state
    let identityOverlay = null;
    let currentEmoji = null;
    let currentCategory = null;
    
    // 🚀 Initialize identity system when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🎭 Initializing Identity Descriptions System with Lock Enforcement...');
        
        setTimeout(function() {
            initializeIdentitySystem();
        }, 200);
    });
    
    /**
     * 🎪 INITIALIZE IDENTITY SYSTEM
     */
    function initializeIdentitySystem() {
        // Get lock status from PHP
        isLocked = window.emojiPickerIsLocked || false;
        daysRemaining = window.emojiPickerDaysRemaining || 0;
        
        console.log('🔒 Identity system lock status:', { isLocked, daysRemaining });
        
        // Create identity overlay if it doesn't exist
        createIdentityOverlay();
        
        // Override the core emoji selection to show identity first (if not locked)
        overrideEmojiSelection();
        
        console.log('✨ Identity system initialized with', Object.keys(emojiIdentities).length, 'spicy identities!', isLocked ? 'LOCKED MODE' : 'UNLOCKED MODE');
    }
    
    /**
     * 🎨 CREATE IDENTITY OVERLAY
     */
    function createIdentityOverlay() {
        if (document.querySelector('.identity-description-overlay')) {
            return; // Already exists
        }
        
        const overlay = document.createElement('div');
        overlay.className = 'identity-description-overlay';
        overlay.style.display = 'none';
        overlay.innerHTML = `
            <div class="identity-description-modal">
                <div class="identity-modal-content">
                    <div class="identity-emoji-display">
                        <div class="identity-emoji-large"></div>
                    </div>
                    <div class="identity-name"></div>
                    <div class="identity-bio"></div>
                    <div class="identity-actions">
                        <button class="identity-action back">
                            <span>← Back to Grid</span>
                        </button>
                        <button class="identity-action choose" style="display: none;">
                            <span>Choose This Vibe</span>
                            <span>✨</span>
                        </button>
                        <div class="identity-locked-notice" style="display: none;">
                            <div class="locked-notice-content">
                                <span class="locked-notice-icon">🔒</span>
                                <div class="locked-notice-text">
                                    <strong>Identity Locked</strong>
                                    <p>Your Angel identity is locked for ${daysRemaining} more days. You can explore identities but cannot choose a new one.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(overlay);
        identityOverlay = overlay;
        
        // Add event listeners
        const backButton = overlay.querySelector('.identity-action.back');
        const chooseButton = overlay.querySelector('.identity-action.choose');
        
        if (backButton) {
            backButton.addEventListener('click', closeIdentityModal);
        }
        
        if (chooseButton && !isLocked) {
            chooseButton.addEventListener('click', handleIdentityChoice);
        }
        
        // Close on overlay click
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                closeIdentityModal();
            }
        });
    }
    
    /**
     * 🎯 OVERRIDE EMOJI SELECTION WITH LOCK AWARENESS
     */
    function overrideEmojiSelection() {
        // Wait longer for core system to fully load and show category
        setTimeout(function() {
            const emojiOptions = document.querySelectorAll('.emoji-option');
            console.log('🎭 Found', emojiOptions.length, 'emoji options to override with lock enforcement');
            
            if (emojiOptions.length === 0) {
                console.log('⏳ No emoji options found yet, retrying...');
                setTimeout(overrideEmojiSelection, 500);
                return;
            }
            
            emojiOptions.forEach(function(option, index) {
                const emoji = option.getAttribute('data-emoji');
                
                if (!emoji) {
                    console.warn('⚠️ Emoji option', index, 'has no data-emoji attribute');
                    return;
                }
                
                // Don't override if already overridden
                if (option.hasAttribute('data-identity-override')) {
                    return;
                }
                
                // Mark as overridden
                option.setAttribute('data-identity-override', 'true');
                
                // Remove existing click listeners by cloning
                const newOption = option.cloneNode(true);
                option.parentNode.replaceChild(newOption, option);
                
                // Add our identity-first listener with lock awareness
                newOption.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const emoji = newOption.getAttribute('data-emoji');
                    const category = newOption.getAttribute('data-category');
                    
                    console.log('🎭 Emoji clicked:', emoji, 'in category:', category, isLocked ? 'LOCKED' : 'UNLOCKED');
                    
                    if (emoji && emojiIdentities[emoji]) {
                        // Show identity modal regardless of lock status (for browsing)
                        showIdentityModal(emoji, category);
                    } else {
                        // Fallback to original behavior
                        console.log('❌ No identity found for', emoji, '- using fallback');
                        if (isLocked) {
                            // Show lock warning for fallback emojis when locked
                            showLockWarningForFallback();
                        } else {
                            if (window.EmojiIdentityPicker && window.EmojiIdentityPicker.selectEmoji) {
                                window.EmojiIdentityPicker.selectEmoji(emoji, newOption);
                            } else {
                                console.error('❌ Core emoji picker not available for fallback');
                            }
                        }
                    }
                });
                
                // Re-add hover effects with lock awareness
                newOption.addEventListener('mouseenter', function(e) {
                    const emojiChar = newOption.querySelector('.emoji-char');
                    if (emojiChar) {
                        if (isLocked) {
                            emojiChar.style.transform = 'scale(1.05)';
                            emojiChar.style.filter = 'grayscale(0.8) drop-shadow(0 2px 4px rgba(255, 152, 0, 0.3))';
                        } else {
                            emojiChar.style.transform = 'scale(1.1) rotate(5deg)';
                            emojiChar.style.filter = 'drop-shadow(0 4px 8px rgba(174, 214, 4, 0.3))';
                        }
                    }
                });
                
                newOption.addEventListener('mouseleave', function(e) {
                    const emojiChar = newOption.querySelector('.emoji-char');
                    if (emojiChar && !newOption.classList.contains('selected')) {
                        emojiChar.style.transform = 'scale(1) rotate(0deg)';
                        if (isLocked) {
                            emojiChar.style.filter = 'grayscale(0.5) drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3))';
                        } else {
                            emojiChar.style.filter = 'drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3))';
                        }
                    }
                });
            });
            
            console.log('✅ Overrode', emojiOptions.length, 'emoji click handlers with lock enforcement');
        }, 1000);
        
        // Also listen for category changes and re-override
        listenForCategoryChanges();
    }
    
    /**
     * 🔒 SHOW LOCK WARNING FOR FALLBACK EMOJIS
     */
    function showLockWarningForFallback() {
        console.log('🔒 Showing lock warning for fallback emoji');
        
        // Try to trigger the core system's lock warning
        if (window.EmojiIdentityPicker && window.EmojiIdentityPicker.showLockWarning) {
            window.EmojiIdentityPicker.showLockWarning();
        } else {
            // Fallback lock warning
            showSimpleLockWarning();
        }
    }
    
    /**
     * 🔒 SIMPLE LOCK WARNING
     */
    function showSimpleLockWarning() {
        const lockMessage = document.createElement('div');
        lockMessage.style.position = 'fixed';
        lockMessage.style.top = '20px';
        lockMessage.style.left = '50%';
        lockMessage.style.transform = 'translateX(-50%)';
        lockMessage.style.background = 'linear-gradient(135deg, #ff9800 0%, #f57c00 100%)';
        lockMessage.style.color = '#ffffff';
        lockMessage.style.padding = '1rem 2rem';
        lockMessage.style.borderRadius = '50px';
        lockMessage.style.fontWeight = '600';
        lockMessage.style.fontSize = '0.9rem';
        lockMessage.style.zIndex = '100000';
        lockMessage.style.boxShadow = '0 8px 25px rgba(255, 152, 0, 0.3)';
        lockMessage.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span>🔒</span>
                <span>Your identity is locked for ${daysRemaining} more days!</span>
            </div>
        `;
        
        document.body.appendChild(lockMessage);
        
        setTimeout(function() {
            lockMessage.remove();
        }, 4000);
    }
    
    /**
     * 👂 LISTEN FOR CATEGORY CHANGES
     */
    function listenForCategoryChanges() {
        const categoryTabs = document.querySelectorAll('.category-tab');
        
        categoryTabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                setTimeout(function() {
                    const visibleOptions = document.querySelectorAll('.emoji-grid.active .emoji-option');
                    console.log('🔄 Category switched, re-overriding', visibleOptions.length, 'visible emojis with lock awareness');
                    
                    visibleOptions.forEach(function(option) {
                        if (option.hasAttribute('data-identity-override')) {
                            return;
                        }
                        
                        const emoji = option.getAttribute('data-emoji');
                        const category = option.getAttribute('data-category');
                        
                        if (!emoji) return;
                        
                        option.setAttribute('data-identity-override', 'true');
                        
                        const newOption = option.cloneNode(true);
                        option.parentNode.replaceChild(newOption, option);
                        
                        newOption.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            if (emojiIdentities[emoji]) {
                                showIdentityModal(emoji, category);
                            } else {
                                if (isLocked) {
                                    showLockWarningForFallback();
                                } else {
                                    if (window.EmojiIdentityPicker && window.EmojiIdentityPicker.selectEmoji) {
                                        window.EmojiIdentityPicker.selectEmoji(emoji, newOption);
                                    }
                                }
                            }
                        });
                        
                        // Re-add hover effects with lock awareness
                        newOption.addEventListener('mouseenter', function(e) {
                            const emojiChar = newOption.querySelector('.emoji-char');
                            if (emojiChar) {
                                if (isLocked) {
                                    emojiChar.style.transform = 'scale(1.05)';
                                    emojiChar.style.filter = 'grayscale(0.8) drop-shadow(0 2px 4px rgba(255, 152, 0, 0.3))';
                                } else {
                                    emojiChar.style.transform = 'scale(1.1) rotate(5deg)';
                                    emojiChar.style.filter = 'drop-shadow(0 4px 8px rgba(174, 214, 4, 0.3))';
                                }
                            }
                        });
                        
                        newOption.addEventListener('mouseleave', function(e) {
                            const emojiChar = newOption.querySelector('.emoji-char');
                            if (emojiChar && !newOption.classList.contains('selected')) {
                                emojiChar.style.transform = 'scale(1) rotate(0deg)';
                                if (isLocked) {
                                    emojiChar.style.filter = 'grayscale(0.5) drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3))';
                                } else {
                                    emojiChar.style.filter = 'drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3))';
                                }
                            }
                        });
                    });
                }, 500);
            });
        });
    }
    
    /**
     * 🎭 SHOW IDENTITY MODAL WITH LOCK AWARENESS
     */
    function showIdentityModal(emoji, category) {
        if (!identityOverlay || !emojiIdentities[emoji]) {
            console.error('❌ Cannot show identity modal for', emoji);
            return;
        }
        
        currentEmoji = emoji;
        currentCategory = category;
        
        const identity = emojiIdentities[emoji];
        const modal = identityOverlay.querySelector('.identity-description-modal');
        const emojiDisplay = identityOverlay.querySelector('.identity-emoji-large');
        const nameDisplay = identityOverlay.querySelector('.identity-name');
        const bioDisplay = identityOverlay.querySelector('.identity-bio');
        const chooseButton = identityOverlay.querySelector('.identity-action.choose');
        const lockedNotice = identityOverlay.querySelector('.identity-locked-notice');
        
        // Update content
        emojiDisplay.textContent = emoji;
        nameDisplay.textContent = identity.name;
        bioDisplay.textContent = identity.bio;
        
        // Show or hide action button based on lock status
        if (isLocked) {
            console.log('🔒 Showing identity modal in LOCKED mode - browse only');
            if (chooseButton) chooseButton.style.display = 'none';
            if (lockedNotice) {
                lockedNotice.style.display = 'block';
                // Update the days remaining in the notice
                const lockedText = lockedNotice.querySelector('.locked-notice-text p');
                if (lockedText) {
                    lockedText.textContent = `Your Angel identity is locked for ${daysRemaining} more days. You can explore identities but cannot choose a new one.`;
                }
            }
        } else {
            console.log('🔓 Showing identity modal in UNLOCKED mode - can choose');
            if (chooseButton) chooseButton.style.display = 'flex';
            if (lockedNotice) lockedNotice.style.display = 'none';
        }
        
        // Add category class for theming
        modal.className = 'identity-description-modal';
        if (category) {
            modal.classList.add(category);
        }
        
        // Show modal
        identityOverlay.style.display = 'flex';
        identityOverlay.style.opacity = '0';
        
        // Trigger reflow
        identityOverlay.offsetHeight;
        
        identityOverlay.style.transition = 'opacity 0.5s ease';
        identityOverlay.style.opacity = '1';
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        
        console.log('🎭 Showing identity modal for', identity.name, isLocked ? '(Locked - Browse Only)' : '(Unlocked - Can Choose)');
    }
    
    /**
     * ❌ CLOSE IDENTITY MODAL
     */
    function closeIdentityModal() {
        if (!identityOverlay) return;
        
        identityOverlay.style.opacity = '0';
        
        setTimeout(function() {
            identityOverlay.style.display = 'none';
            document.body.style.overflow = '';
        }, 500);
        
        currentEmoji = null;
        currentCategory = null;
    }
    
    /**
     * ✅ HANDLE IDENTITY CHOICE WITH LOCK ENFORCEMENT
     */
    function handleIdentityChoice() {
        if (!currentEmoji) return;
        
        // Final lock check before proceeding
        if (isLocked) {
            console.log('🔒 Identity choice blocked by lock enforcement');
            closeIdentityModal();
            showLockWarningForFallback();
            return;
        }
        
        console.log('✅ Processing identity choice for unlocked user:', currentEmoji);
        confirmIdentityChoice();
    }
    
    /**
     * ✅ CONFIRM IDENTITY CHOICE (UNLOCKED USERS ONLY)
     */
    function confirmIdentityChoice() {
        if (!currentEmoji || isLocked) {
            console.log('🔒 Identity confirmation blocked - locked or no emoji');
            return;
        }
        
        console.log('✅ Confirmed identity choice:', currentEmoji);
        
        // Store the emoji before closing modal
        const selectedEmoji = currentEmoji;
        const selectedCategory = currentCategory;
        
        // Close identity modal first
        closeIdentityModal();
        
        // Find the emoji element that was clicked
        const emojiElement = document.querySelector(`.emoji-option[data-emoji="${selectedEmoji}"]`);
        
        if (emojiElement) {
            // Mark emoji as selected visually
            document.querySelectorAll('.emoji-option').forEach(function(option) {
                option.classList.remove('selected');
            });
            emojiElement.classList.add('selected');
            
            // Play selection animation
            emojiElement.style.transform = 'scale(1.2) rotate(10deg)';
            emojiElement.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            
            setTimeout(function() {
                emojiElement.style.transform = 'scale(1) rotate(0deg)';
            }, 300);
            
            console.log('🎯 Marked emoji as selected:', selectedEmoji);
        } else {
            console.warn('⚠️ Could not find emoji element for:', selectedEmoji);
        }
        
        // Wait for modal close animation, then enter preview mode
        setTimeout(function() {
            // Set up the core system's selectedEmoji variable
            if (window.EmojiIdentityPicker) {
                if (typeof window.EmojiIdentityPicker.selectedEmoji !== 'undefined') {
                    window.EmojiIdentityPicker.selectedEmoji = selectedEmoji;
                    console.log('✅ Set core selectedEmoji via property:', selectedEmoji);
                }
                
                if (window.EmojiIdentityPicker.setSelectedEmoji) {
                    window.EmojiIdentityPicker.setSelectedEmoji(selectedEmoji);
                    console.log('✅ Set core selectedEmoji via method:', selectedEmoji);
                }
                
                if (typeof window.EmojiIdentityPicker.previewMode !== 'undefined') {
                    window.EmojiIdentityPicker.previewMode = true;
                    console.log('✅ Set core previewMode to true');
                }
            }
            
            // Set the global selectedEmoji variable
            window.globalSelectedEmoji = selectedEmoji;
            console.log('✅ Set globalSelectedEmoji:', selectedEmoji);
            
            // Update all preview emojis manually
            const previewEmojis = document.querySelectorAll('.preview-emoji');
            previewEmojis.forEach(function(previewEmoji) {
                previewEmoji.textContent = selectedEmoji;
                console.log('🔄 Updated preview emoji to:', selectedEmoji);
            });
            
            // Update modal emoji for confirmation
            const modalEmojiLarge = document.querySelector('.modal-emoji-large');
            if (modalEmojiLarge) {
                modalEmojiLarge.textContent = selectedEmoji;
                console.log('🔄 Updated modal emoji to:', selectedEmoji);
            }
            
            // Update confirm button data
            const confirmButton = document.querySelector('.preview-action.confirm');
            if (confirmButton) {
                confirmButton.setAttribute('data-emoji', selectedEmoji);
                console.log('🔄 Updated confirm button data-emoji to:', selectedEmoji);
            }
            
            // Show preview section (only if not locked)
            const previewSection = document.querySelector('.emoji-preview-section');
            if (previewSection && !isLocked) {
                previewSection.style.display = 'block';
                previewSection.style.opacity = '0';
                previewSection.style.transform = 'translateY(30px)';
                
                // Trigger reflow
                previewSection.offsetHeight;
                
                previewSection.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                previewSection.style.opacity = '1';
                previewSection.style.transform = 'translateY(0)';
                
                // Scroll to preview section smoothly
                setTimeout(function() {
                    previewSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }, 300);
                
                console.log('✅ Entered preview mode with emoji:', selectedEmoji);
            } else if (isLocked) {
                console.log('🔒 Preview section blocked - identity is locked');
            } else {
                console.error('❌ Could not find preview section');
            }
            
        }, 500);
    }
    
    // 🎯 Expose identity system globally
    window.EmojiIdentitySystem = {
        showIdentityModal: showIdentityModal,
        closeIdentityModal: closeIdentityModal,
        getIdentity: function(emoji) {
            return emojiIdentities[emoji] || null;
        },
        getAllIdentities: function() {
            return emojiIdentities;
        },
        getCurrentSelectedEmoji: function() {
            return window.globalSelectedEmoji || null;
        },
        isLocked: isLocked,
        daysRemaining: daysRemaining
    };
    
    console.log('🎭 Identity system loaded with', Object.keys(emojiIdentities).length, 'identities!', isLocked ? 'LOCKED MODE' : 'UNLOCKED MODE');
    
})();