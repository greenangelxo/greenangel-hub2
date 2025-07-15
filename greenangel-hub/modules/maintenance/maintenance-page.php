<?php
/**
 * 🌈 MAINTENANCE PAGE - ICONIC LED CONSOLE
 * The most jaw-dropping LED maintenance page
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 🌟 Show the ICONIC LED maintenance console
 */
function greenangel_show_maintenance_page() {
    status_header(503);
    header('Retry-After: 3600');
    
    $message = greenangel_get_maintenance_message();
    $eta = greenangel_get_maintenance_eta();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>✨ Green Angel is Crafting Magic</title>
        
        <!-- 🎨 Preload critical resources -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        
        <!-- 📱 Mobile optimizations -->
        <meta name="theme-color" content="#aed604">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        
        <!-- 🔍 SEO and sharing -->
        <meta name="description" content="Green Angel is currently crafting some next-level magic. We'll be back soon with even more sparkles!">
        <meta property="og:title" content="✨ Green Angel is Crafting Magic">
        <meta property="og:description" content="We're cooking up something incredible behind the scenes">
        <meta property="og:type" content="website">
        
        <!-- 🚫 Prevent indexing during maintenance -->
        <meta name="robots" content="noindex, nofollow">
        
        <style>
            /**
             * 🌈 ICONIC LED MAINTENANCE CONSOLE
             * Electric neon paradise with RGB magic
             */

            /* 🌌 Reset and base styles */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            html, body {
                height: 100%;
                font-family: 'Poppins', sans-serif;
                background: #2a2a2a;
                color: #fff;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
                overflow-x: hidden;
            }

            /* 🌌 BACKGROUND WITH SUBTLE PARTICLES */
            .background-container {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 1;
                background: 
                    radial-gradient(ellipse at 20% 30%, rgba(174, 214, 4, 0.08) 0%, transparent 40%),
                    radial-gradient(ellipse at 80% 70%, rgba(0, 255, 255, 0.06) 0%, transparent 40%),
                    radial-gradient(ellipse at 40% 80%, rgba(255, 20, 147, 0.05) 0%, transparent 40%),
                    linear-gradient(135deg, #2a2a2a 0%, #1f1f1f 100%);
                animation: subtle-shift 30s ease-in-out infinite;
            }

            @keyframes subtle-shift {
                0%, 100% { filter: hue-rotate(0deg); }
                33% { filter: hue-rotate(30deg); }
                66% { filter: hue-rotate(-30deg); }
            }

            /* ⚡ ELECTRIC FLOATING EMOJIS */
            .floating-particle {
                position: absolute;
                font-size: 18px;
                animation: emoji-float 20s ease-in-out infinite;
                opacity: 0.7;
                pointer-events: none;
                transition: all 0.8s ease;
                user-select: none;
                filter: drop-shadow(0 0 8px rgba(174, 214, 4, 0.3));
            }

            /* LED ELECTRIC PARADISE EMOJIS */
            .floating-particle:nth-child(1)::before { content: '💎'; }
            .floating-particle:nth-child(2)::before { content: '⚡'; }
            .floating-particle:nth-child(3)::before { content: '🌈'; }
            .floating-particle:nth-child(4)::before { content: '🌟'; }
            .floating-particle:nth-child(5)::before { content: '💡'; }
            .floating-particle:nth-child(6)::before { content: '🔥'; }
            .floating-particle:nth-child(7)::before { content: '✨'; }
            .floating-particle:nth-child(8)::before { content: '💫'; }
            .floating-particle:nth-child(9)::before { content: '🎆'; }
            .floating-particle:nth-child(10)::before { content: '🔋'; }
            .floating-particle:nth-child(11)::before { content: '🌀'; }
            .floating-particle:nth-child(12)::before { content: '💚'; }
            .floating-particle:nth-child(13)::before { content: '🎯'; }
            .floating-particle:nth-child(14)::before { content: '🎨'; }
            .floating-particle:nth-child(15)::before { content: '👑'; }

            @keyframes emoji-float {
                0%, 100% { 
                    transform: translateY(0px) translateX(0px) rotate(0deg); 
                    opacity: 0.5; 
                }
                25% { 
                    transform: translateY(-50px) translateX(40px) rotate(25deg); 
                    opacity: 0.9; 
                }
                50% { 
                    transform: translateY(-30px) translateX(-35px) rotate(-15deg); 
                    opacity: 0.7; 
                }
                75% { 
                    transform: translateY(40px) translateX(45px) rotate(30deg); 
                    opacity: 1; 
                }
            }

            /* ✨ MAIN CONTAINER */
            .maintenance-wrapper {
                position: relative;
                z-index: 10;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }

            /* 🎭 MAIN CONSOLE CARD */
            .maintenance-console {
                background: linear-gradient(145deg, #1a1a1a 0%, #2a2a2a 50%, #1a1a1a 100%);
                border-radius: 24px;
                padding: 40px;
                max-width: 500px;
                width: 100%;
                position: relative;
                overflow: hidden;
                box-shadow: 
                    0 20px 60px rgba(0, 0, 0, 0.8),
                    inset 0 1px 0 rgba(255, 255, 255, 0.1),
                    0 0 0 1px rgba(255, 255, 255, 0.03);
                backdrop-filter: blur(20px);
            }

            /* ⚡ ELECTRIC RGB BORDER */
            .maintenance-console::before {
                content: '';
                position: absolute;
                inset: 0;
                border-radius: 24px;
                padding: 3px;
                background: linear-gradient(90deg, 
                    #00ffff 0%,
                    #ff1493 16%,
                    #aed604 32%,
                    #ff4500 48%,
                    #9932cc 64%,
                    #ff69b4 80%,
                    #00ffff 100%
                );
                background-size: 300% 100%;
                mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
                mask-composite: exclude;
                -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
                -webkit-mask-composite: xor;
                animation: border-flow 12s linear infinite;
            }

            /* ✨ METALLIC SHIMMER */
            .maintenance-console::after {
                content: '';
                position: absolute;
                top: -100%;
                left: -100%;
                width: 400%;
                height: 400%;
                background: linear-gradient(45deg, 
                    transparent 30%, 
                    rgba(255, 255, 255, 0.02) 40%, 
                    rgba(255, 255, 255, 0.06) 50%, 
                    rgba(255, 255, 255, 0.02) 60%, 
                    transparent 70%
                );
                transform: rotate(45deg);
                animation: metallic-shimmer 10s ease-in-out infinite;
                pointer-events: none;
                z-index: 1;
            }

            @keyframes border-flow {
                0% { background-position: 0% 50%; }
                100% { background-position: 300% 50%; }
            }

            @keyframes metallic-shimmer {
                0%, 100% { transform: rotate(45deg) translateX(-100%); opacity: 0; }
                50% { transform: rotate(45deg) translateX(100%); opacity: 1; }
            }

            /* 🌟 CONTENT INSIDE CONSOLE */
            .console-content {
                position: relative;
                z-index: 2;
                text-align: center;
            }

            /* 🎰 EMOJI ROULETTE */
            .emoji-roulette {
                width: 80px;
                height: 80px;
                margin: 0 auto 25px;
                position: relative;
                background: linear-gradient(135deg, rgba(174, 214, 4, 0.15), rgba(0, 255, 255, 0.1));
                border: 2px solid rgba(255, 255, 255, 0.1);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.3s ease;
                backdrop-filter: blur(10px);
            }

            .emoji-roulette:hover {
                transform: scale(1.05);
                box-shadow: 0 0 30px rgba(174, 214, 4, 0.4);
            }

            .emoji-display {
                font-size: 2.5rem;
                animation: emoji-glow 3s ease-in-out infinite alternate;
                transition: all 0.3s ease;
            }

            @keyframes emoji-glow {
                0% { 
                    filter: drop-shadow(0 0 15px rgba(174, 214, 4, 0.6));
                    transform: scale(1);
                }
                100% { 
                    filter: drop-shadow(0 0 25px rgba(0, 255, 255, 0.8));
                    transform: scale(1.1);
                }
            }

            /* 🌟 BRAND TITLE */
            .brand-title {
                font-size: 2rem;
                font-weight: 900;
                margin-bottom: 15px;
                background: linear-gradient(135deg, 
                    #00ffff 0%, 
                    #aed604 50%,
                    #ff1493 100%
                );
                background-size: 200% 100%;
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                animation: title-flow 4s ease-in-out infinite;
                letter-spacing: -0.5px;
                text-shadow: 0 0 30px rgba(174, 214, 4, 0.3);
                filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
                position: relative;
            }

            .brand-title::before {
                content: '';
                position: absolute;
                inset: -10px;
                background: radial-gradient(ellipse, rgba(174, 214, 4, 0.1) 0%, transparent 70%);
                border-radius: 20px;
                z-index: -1;
                animation: title-glow 4s ease-in-out infinite;
            }

            @keyframes title-flow {
                0%, 100% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
            }

            @keyframes title-glow {
                0%, 100% { opacity: 0.3; transform: scale(1); }
                50% { opacity: 0.6; transform: scale(1.05); }
            }

            /* 🚧 MAINTENANCE BADGE */
            .maintenance-badge {
                display: inline-block;
                margin-bottom: 20px;
                padding: 8px 20px;
                background: linear-gradient(135deg, rgba(174, 214, 4, 0.15), rgba(0, 255, 255, 0.1));
                border: 1px solid rgba(174, 214, 4, 0.3);
                border-radius: 25px;
                position: relative;
                overflow: hidden;
            }

            .maintenance-badge::before {
                content: '';
                position: absolute;
                inset: 0;
                background: linear-gradient(90deg, 
                    transparent 0%, 
                    rgba(174, 214, 4, 0.1) 50%, 
                    transparent 100%
                );
                animation: badge-shimmer 3s ease-in-out infinite;
            }

            @keyframes badge-shimmer {
                0% { transform: translateX(-100%); }
                100% { transform: translateX(100%); }
            }

            .badge-text {
                font-size: 0.85rem;
                font-weight: 700;
                letter-spacing: 0.1em;
                background: linear-gradient(135deg, #aed604 0%, #00ffff 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                position: relative;
                z-index: 2;
                text-shadow: 0 0 20px rgba(174, 214, 4, 0.3);
            }

            /* 💫 STATUS MESSAGE */
            .status-message {
                font-size: 1rem;
                color: #ccc;
                margin-bottom: 30px;
                min-height: 24px;
                opacity: 0;
                transform: translateY(10px);
                animation: message-appear 0.6s ease-out forwards;
                line-height: 1.4;
            }

            @keyframes message-appear {
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* 🎨 LOADING BARS */
            .loading-section {
                margin: 25px 0;
            }

            .loading-bars {
                display: flex;
                gap: 6px;
                justify-content: center;
                margin-bottom: 15px;
            }

            .loading-bar {
                width: 4px;
                height: 25px;
                background: linear-gradient(0deg, #333, #aed604);
                border-radius: 2px;
                animation: bar-pulse 1.5s ease-in-out infinite;
            }

            .loading-bar:nth-child(1) { animation-delay: 0s; }
            .loading-bar:nth-child(2) { animation-delay: 0.1s; }
            .loading-bar:nth-child(3) { animation-delay: 0.2s; }
            .loading-bar:nth-child(4) { animation-delay: 0.3s; }
            .loading-bar:nth-child(5) { animation-delay: 0.4s; }

            @keyframes bar-pulse {
                0%, 100% { 
                    transform: scaleY(0.4);
                    background: linear-gradient(0deg, #333, #666);
                }
                50% { 
                    transform: scaleY(1);
                    background: linear-gradient(0deg, #aed604, #00ffff);
                }
            }

            /* 📊 PROGRESS BAR */
            .progress-section {
                margin: 25px 0;
            }

            .progress-text {
                font-size: 0.9rem;
                color: #aed604;
                font-weight: 600;
                margin-bottom: 12px;
                text-align: center;
            }

            .progress-bar-container {
                width: 100%;
                height: 8px;
                background: rgba(255, 255, 255, 0.08);
                border-radius: 4px;
                overflow: hidden;
                position: relative;
                box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
            }

            .progress-bar-fill {
                height: 100%;
                background: linear-gradient(90deg, #aed604, #00ffff, #ff1493);
                background-size: 200% 100%;
                border-radius: 4px;
                width: 0%;
                animation: progress-smooth 12s ease-in-out infinite, progress-flow 3s linear infinite;
                box-shadow: 0 0 12px rgba(174, 214, 4, 0.4);
                position: relative;
            }

            .progress-bar-fill::after {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                width: 20px;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4));
                animation: progress-shine 2s ease-in-out infinite;
            }

            @keyframes progress-smooth {
                0% { width: 5%; }
                25% { width: 35%; }
                50% { width: 65%; }
                75% { width: 85%; }
                100% { width: 5%; }
            }

            @keyframes progress-flow {
                0% { background-position: 0% 50%; }
                100% { background-position: 200% 50%; }
            }

            @keyframes progress-shine {
                0%, 100% { opacity: 0; }
                50% { opacity: 1; }
            }

            /* 💬 CUSTOM MESSAGE CARD */
            .custom-message-card {
                background: linear-gradient(135deg, rgba(174, 214, 4, 0.08), rgba(0, 255, 255, 0.04));
                border: 1px solid rgba(174, 214, 4, 0.2);
                border-radius: 16px;
                padding: 20px;
                margin: 25px 0;
                backdrop-filter: blur(10px);
            }

            .custom-message-text {
                font-size: 1rem;
                color: #e0e0e0;
                margin-bottom: 8px;
                line-height: 1.5;
            }

            .eta-info {
                font-size: 0.9rem;
                background: linear-gradient(135deg, #aed604 0%, #00ffff 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                font-weight: 600;
            }

            /* 🌈 THEME TRANSITION */
            .maintenance-console,
            .brand-title,
            .loading-bar,
            .progress-bar-fill,
            .emoji-roulette,
            .progress-text {
                transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            }

            /* ✨ SPARKLE EFFECTS */
            .sparkle {
                position: fixed;
                pointer-events: none;
                width: 4px;
                height: 4px;
                background: #aed604;
                border-radius: 50%;
                z-index: 1000;
            }

            /* 🌊 GLITCH EFFECT */
            .glitch {
                animation: glitch-effect 0.3s ease-in-out;
            }

            @keyframes glitch-effect {
                0% { transform: translate(0); }
                20% { transform: translate(-1px, 1px); }
                40% { transform: translate(-1px, -1px); }
                60% { transform: translate(1px, 1px); }
                80% { transform: translate(1px, -1px); }
                100% { transform: translate(0); }
            }

            /* 🎵 HEARTBEAT ANIMATION */
            .heartbeat {
                animation: heartbeat-pulse 1.2s ease-in-out infinite;
            }

            @keyframes heartbeat-pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.03); }
            }

            /* 🎭 MAGICAL EFFECT ANIMATIONS */
            @keyframes float-up {
                0% {
                    transform: translateY(0) scale(0);
                    opacity: 0;
                }
                50% {
                    opacity: 1;
                    transform: translateY(-20px) scale(1);
                }
                100% {
                    transform: translateY(-40px) scale(0.8);
                    opacity: 0;
                }
            }

            @keyframes diamond-scatter-0 {
                0% { transform: translate(-50%, -50%) scale(1); }
                100% { transform: translate(-80%, -80%) scale(0.6) rotate(180deg); opacity: 0; }
            }

            @keyframes diamond-scatter-1 {
                0% { transform: translate(-50%, -50%) scale(1); }
                100% { transform: translate(-50%, -100%) scale(0.6) rotate(-180deg); opacity: 0; }
            }

            @keyframes diamond-scatter-2 {
                0% { transform: translate(-50%, -50%) scale(1); }
                100% { transform: translate(-20%, -80%) scale(0.6) rotate(180deg); opacity: 0; }
            }

            @keyframes grow-up {
                0% {
                    transform: scale(0) translateY(10px);
                    opacity: 0;
                }
                50% {
                    transform: scale(1.2) translateY(-5px);
                    opacity: 1;
                }
                100% {
                    transform: scale(1) translateY(-10px);
                    opacity: 0.8;
                }
            }

            @keyframes electric-pulse {
                0%, 100% {
                    transform: translate(-50%, -50%) scale(1);
                    opacity: 0.6;
                }
                50% {
                    transform: translate(-50%, -50%) scale(1.3);
                    opacity: 1;
                }
            }

            @keyframes bubble-rise {
                0% {
                    transform: translateY(0) translateX(0);
                    opacity: 0;
                }
                20% {
                    opacity: 0.8;
                }
                100% {
                    transform: translateY(-40px) translateX(10px);
                    opacity: 0;
                }
            }

            @keyframes matrix-cascade {
                0% {
                    transform: translateY(-20px) scale(0);
                    opacity: 0;
                }
                50% {
                    transform: translateY(0) scale(1.2);
                    opacity: 1;
                }
                100% {
                    transform: translateY(20px) scale(0);
                    opacity: 0;
                }
            }

            @keyframes wave-expand {
                0% {
                    transform: translate(-50%, -50%) scale(1);
                    opacity: 1;
                }
                100% {
                    transform: translate(-50%, -50%) scale(3);
                    opacity: 0;
                }
            }

            @keyframes heart-pulse-grow {
                0%, 100% {
                    transform: translate(-50%, -50%) scale(1);
                }
                50% {
                    transform: translate(-50%, -50%) scale(1.3);
                    filter: drop-shadow(0 0 10px #aed604);
                }
            }

            @keyframes heartbeat-sync {
                0%, 100% {
                    transform: translateY(-50%) scale(1);
                    opacity: 0.6;
                }
                40% {
                    transform: translateY(-50%) scale(1.2);
                    opacity: 1;
                }
                60% {
                    transform: translateY(-50%) scale(0.9);
                    opacity: 0.8;
                }
            }

            @keyframes data-fall {
                0% {
                    transform: translateY(0);
                    opacity: 0;
                }
                20% {
                    opacity: 0.8;
                }
                100% {
                    transform: translateY(40px);
                    opacity: 0;
                }
            }

            @keyframes code-type {
                0% {
                    width: 0;
                    opacity: 0;
                }
                10% {
                    opacity: 1;
                }
                100% {
                    width: 100%;
                    opacity: 1;
                }
            }

            /* 📱 MOBILE OPTIMIZATIONS */
            @media (max-width: 480px) {
                .maintenance-console {
                    padding: 30px 25px;
                    margin: 10px;
                }
                
                .brand-title {
                    font-size: 1.8rem;
                }
                
                .emoji-roulette {
                    width: 70px;
                    height: 70px;
                }
                
                .emoji-display {
                    font-size: 2.2rem;
                }
                
                .status-message {
                    font-size: 0.95rem;
                }
                
                .custom-message-card {
                    padding: 18px;
                }
            }

            /* 💻 TABLET STYLES */
            @media (min-width: 768px) {
                .maintenance-console {
                    max-width: 600px;
                    padding: 50px;
                }
                
                .brand-title {
                    font-size: 2.5rem;
                }
                
                .emoji-roulette {
                    width: 100px;
                    height: 100px;
                }
                
                .emoji-display {
                    font-size: 3rem;
                }
            }

            /* 🎭 ACCESSIBILITY */
            @media (prefers-reduced-motion: reduce) {
                * {
                    animation-duration: 0.01ms !important;
                    animation-iteration-count: 1 !important;
                }
            }

            @media (prefers-contrast: high) {
                .maintenance-console {
                    border: 3px solid #aed604;
                }
                
                .status-message,
                .custom-message-text {
                    color: #fff;
                }
            }
        </style>
    </head>
    <body>
        <!-- 🌌 Subtle Background -->
        <div class="background-container" id="background"></div>
        
        <!-- ✨ Main Maintenance Wrapper -->
        <div class="maintenance-wrapper">
            <!-- 🎭 Main Console Card -->
            <div class="maintenance-console">
                <div class="console-content">
                    <!-- 🎰 Emoji Roulette -->
                    <div class="emoji-roulette" id="emojiRoulette">
                        <div class="emoji-display" id="emojiDisplay">💎</div>
                    </div>
                    
                    <!-- 🌟 Brand Title -->
                    <h1 class="brand-title">GREEN ANGEL</h1>
                    
                    <!-- 🚧 Maintenance Mode Badge -->
                    <div class="maintenance-badge">
                        <span class="badge-text">MAINTENANCE MODE</span>
                    </div>
                    
                    <!-- 💫 Dynamic Status Message -->
                    <div class="status-message" id="statusMessage">
                        Initializing magic systems...
                    </div>
                    
                    <!-- 🎨 Loading Section -->
                    <div class="loading-section">
                        <div class="loading-bars">
                            <div class="loading-bar"></div>
                            <div class="loading-bar"></div>
                            <div class="loading-bar"></div>
                            <div class="loading-bar"></div>
                            <div class="loading-bar"></div>
                        </div>
                    </div>
                    
                    <!-- 📊 Progress Section -->
                    <div class="progress-section">
                        <div class="progress-text" id="progressText">Magic Progress: 0%</div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill"></div>
                        </div>
                    </div>
                    
                    <!-- 💬 Custom Message (if set) -->
                    <?php if (!empty($message) || !empty($eta)): ?>
                    <div class="custom-message-card">
                        <?php if (!empty($message)): ?>
                        <div class="custom-message-text"><?php echo esc_html($message); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($eta)): ?>
                        <div class="eta-info">✨ Expected back: <span><?php echo esc_html($eta); ?></span></div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <script>
            /**
             * 🌟 LED MAINTENANCE MAGIC
             * Electric animations and interactions
             */

            class LEDMaintenance {
                constructor() {
                    this.currentEmojiIndex = 0;
                    this.currentMessageIndex = 0;
                    this.progressValue = 0;
                    
                    this.emojis = ['💎', '✨', '🔥', '💚', '🌟', '⚡', '🎭', '🦄', '👑', '💫'];
                    
                    this.statusMessages = [
                        'polishing diamonds...',
                        'wrapping heartbreakerz...',
                        'breaking diamonds into minis...',
                        'tending to the greenery...',
                        'cooking up magic...',
                        'sprinkling green angel dust...',
                        'charging crystal cores...',
                        'weaving rainbow threads...',
                        'mixing celestial potions...',
                        'calibrating sparkle matrix...',
                        'downloading angel frequencies...',
                        'activating love protocols...',
                        'synchronizing heartbeats...',
                        'updating vibe database...',
                        'optimizing magic algorithms...'
                    ];
                    
                    this.init();
                }
                
                init() {
                    this.createBackgroundParticles();
                    this.startEmojiRoulette();
                    this.startMessageCycle();
                    this.startProgressAnimation();
                    this.initInteractions();
                    
                    console.log('🌈 LED maintenance magic activated!');
                }
                
                createBackgroundParticles() {
                    const background = document.getElementById('background');
                    const particleCount = window.innerWidth < 768 ? 12 : 15;
                    
                    for (let i = 0; i < particleCount; i++) {
                        const particle = document.createElement('div');
                        particle.className = 'floating-particle';
                        
                        particle.style.left = Math.random() * 100 + '%';
                        particle.style.top = Math.random() * 100 + '%';
                        particle.style.animationDelay = Math.random() * 20 + 's';
                        particle.style.animationDuration = (Math.random() * 10 + 15) + 's';
                        
                        background.appendChild(particle);
                    }
                }
                
                startEmojiRoulette() {
                    const emojiDisplay = document.getElementById('emojiDisplay');
                    
                    setInterval(() => {
                        // Spin effect
                        emojiDisplay.style.transform = 'scale(0) rotateY(180deg)';
                        
                        setTimeout(() => {
                            this.currentEmojiIndex = (this.currentEmojiIndex + 1) % this.emojis.length;
                            emojiDisplay.textContent = this.emojis[this.currentEmojiIndex];
                            emojiDisplay.style.transform = 'scale(1) rotateY(0deg)';
                            
                            // Sparkle effect
                            this.createEmojiSparkles();
                        }, 200);
                    }, 4500);
                }
                
                startMessageCycle() {
                    const messageEl = document.getElementById('statusMessage');
                    
                    const messageEffects = {
                        'polishing diamonds...': () => this.createDiamondShine(messageEl),
                        'wrapping heartbreakerz...': () => this.createHeartFloat(messageEl),
                        'breaking diamonds into minis...': () => this.createDiamondBreak(messageEl),
                        'tending to the greenery...': () => this.createGrowingLeaves(messageEl),
                        'cooking up magic...': () => this.createFireEffect(messageEl),
                        'sprinkling green angel dust...': () => this.createSparkDust(messageEl),
                        'charging crystal cores...': () => this.createElectricCharge(messageEl),
                        'weaving rainbow threads...': () => this.createRainbowThreads(messageEl),
                        'mixing celestial potions...': () => this.createBubbles(messageEl),
                        'calibrating sparkle matrix...': () => this.createMatrixEffect(messageEl),
                        'downloading angel frequencies...': () => this.createWaveEffect(messageEl),
                        'activating love protocols...': () => this.createHeartPulse(messageEl),
                        'synchronizing heartbeats...': () => this.createHeartbeat(messageEl),
                        'updating vibe database...': () => this.createDataFlow(messageEl),
                        'optimizing magic algorithms...': () => this.createCodeFlow(messageEl)
                    };
                    
                    const updateMessage = () => {
                        messageEl.style.opacity = '0';
                        messageEl.style.transform = 'translateY(10px)';
                        
                        setTimeout(() => {
                            this.currentMessageIndex = (this.currentMessageIndex + 1) % this.statusMessages.length;
                            const newMessage = this.statusMessages[this.currentMessageIndex];
                            messageEl.textContent = newMessage;
                            
                            // Reset animation
                            messageEl.style.animation = 'none';
                            messageEl.offsetHeight;
                            messageEl.style.animation = 'message-appear 0.6s ease-out forwards';
                            
                            // Trigger special effect for this message
                            if (messageEffects[newMessage]) {
                                setTimeout(() => messageEffects[newMessage](), 600);
                            }
                        }, 300);
                    };
                    
                    setTimeout(updateMessage, 2000);
                    setInterval(updateMessage, 5500);
                }
                
                // 💎 Diamond shine effect
                createDiamondShine(container) {
                    const shine = document.createElement('div');
                    shine.className = 'diamond-shine';
                    container.appendChild(shine);
                    setTimeout(() => shine.remove(), 2000);
                }
                
                // 💚 Heart float effect
                createHeartFloat(container) {
                    for (let i = 0; i < 3; i++) {
                        setTimeout(() => {
                            const heart = document.createElement('div');
                            heart.textContent = '💚';
                            heart.style.cssText = `
                                position: absolute;
                                left: ${30 + i * 20}%;
                                bottom: 0;
                                font-size: 16px;
                                animation: float-up 2s ease-out forwards;
                                opacity: 0.8;
                            `;
                            container.appendChild(heart);
                            setTimeout(() => heart.remove(), 2000);
                        }, i * 200);
                    }
                }
                
                // 💎 Diamond break effect
                createDiamondBreak(container) {
                    const positions = ['-20px', '0px', '20px'];
                    positions.forEach((pos, i) => {
                        const mini = document.createElement('div');
                        mini.textContent = '💎';
                        mini.style.cssText = `
                            position: absolute;
                            left: 50%;
                            top: 50%;
                            font-size: 12px;
                            transform: translate(-50%, -50%);
                            animation: diamond-scatter-${i} 1.5s ease-out forwards;
                        `;
                        container.appendChild(mini);
                        setTimeout(() => mini.remove(), 1500);
                    });
                }
                
                // 🌱 Growing leaves effect
                createGrowingLeaves(container) {
                    const leaves = ['🌱', '🌿', '🍃'];
                    leaves.forEach((leaf, i) => {
                        setTimeout(() => {
                            const leafEl = document.createElement('div');
                            leafEl.textContent = leaf;
                            leafEl.style.cssText = `
                                position: absolute;
                                left: ${40 + i * 10}%;
                                bottom: -5px;
                                font-size: 16px;
                                animation: grow-up 1.5s ease-out forwards;
                            `;
                            container.appendChild(leafEl);
                            setTimeout(() => leafEl.remove(), 1500);
                        }, i * 150);
                    });
                }
                
                // 🔥 Fire cooking effect
                createFireEffect(container) {
                    const fire = document.createElement('div');
                    fire.textContent = '🔥';
                    fire.className = 'fire-cooking';
                    container.appendChild(fire);
                    setTimeout(() => fire.remove(), 1500);
                }
                
                // ✨ Sparkle dust effect
                createSparkDust(container) {
                    for (let i = 0; i < 8; i++) {
                        setTimeout(() => {
                            const dust = document.createElement('div');
                            dust.className = 'sparkle-dust';
                            dust.style.left = (20 + Math.random() * 60) + '%';
                            dust.style.animationDelay = Math.random() * 0.5 + 's';
                            container.appendChild(dust);
                            setTimeout(() => dust.remove(), 2000);
                        }, i * 100);
                    }
                }
                
                // ⚡ Electric charge effect
                createElectricCharge(container) {
                    const bolt = document.createElement('div');
                    bolt.textContent = '⚡';
                    bolt.style.cssText = `
                        position: absolute;
                        left: 50%;
                        top: 50%;
                        transform: translate(-50%, -50%);
                        font-size: 24px;
                        animation: electric-pulse 1s ease-out 3;
                        filter: drop-shadow(0 0 10px #00ffff);
                    `;
                    container.appendChild(bolt);
                    setTimeout(() => bolt.remove(), 3000);
                }
                
                // 🌈 Rainbow threads effect
                createRainbowThreads(container) {
                    const rainbow = document.createElement('div');
                    rainbow.className = 'rainbow-threads';
                    container.appendChild(rainbow);
                    setTimeout(() => rainbow.remove(), 3000);
                }
                
                // 🧪 Bubble effect
                createBubbles(container) {
                    for (let i = 0; i < 5; i++) {
                        const bubble = document.createElement('div');
                        bubble.style.cssText = `
                            position: absolute;
                            width: ${8 + Math.random() * 8}px;
                            height: ${8 + Math.random() * 8}px;
                            background: rgba(174, 214, 4, 0.3);
                            border: 1px solid rgba(174, 214, 4, 0.6);
                            border-radius: 50%;
                            left: ${30 + Math.random() * 40}%;
                            bottom: -10px;
                            animation: bubble-rise ${1.5 + Math.random()}s ease-out forwards;
                        `;
                        container.appendChild(bubble);
                        setTimeout(() => bubble.remove(), 2000);
                    }
                }
                
                // ✨ Matrix effect
                createMatrixEffect(container) {
                    const matrix = document.createElement('div');
                    matrix.textContent = '✨';
                    matrix.style.cssText = `
                        position: absolute;
                        width: 100%;
                        text-align: center;
                        font-size: 20px;
                        animation: matrix-cascade 2s ease-out;
                    `;
                    container.appendChild(matrix);
                    setTimeout(() => matrix.remove(), 2000);
                }
                
                // 📡 Wave effect
                createWaveEffect(container) {
                    const wave = document.createElement('div');
                    wave.style.cssText = `
                        position: absolute;
                        width: 30px;
                        height: 30px;
                        border: 2px solid rgba(174, 214, 4, 0.6);
                        border-radius: 50%;
                        left: 50%;
                        top: 50%;
                        transform: translate(-50%, -50%);
                        animation: wave-expand 1.5s ease-out;
                    `;
                    container.appendChild(wave);
                    setTimeout(() => wave.remove(), 1500);
                }
                
                // 💚 Heart pulse effect
                createHeartPulse(container) {
                    const heart = document.createElement('div');
                    heart.textContent = '💚';
                    heart.style.cssText = `
                        position: absolute;
                        left: 50%;
                        top: 50%;
                        transform: translate(-50%, -50%);
                        font-size: 20px;
                        animation: heart-pulse-grow 1.5s ease-in-out 2;
                    `;
                    container.appendChild(heart);
                    setTimeout(() => heart.remove(), 3000);
                }
                
                // 💓 Heartbeat effect
                createHeartbeat(container) {
                    const beat1 = document.createElement('div');
                    const beat2 = document.createElement('div');
                    [beat1, beat2].forEach((beat, i) => {
                        beat.textContent = '💓';
                        beat.style.cssText = `
                            position: absolute;
                            left: ${45 + i * 10}%;
                            top: 50%;
                            transform: translateY(-50%);
                            font-size: 16px;
                            animation: heartbeat-sync 1s ease-in-out ${i * 0.2}s 2;
                        `;
                        container.appendChild(beat);
                        setTimeout(() => beat.remove(), 2200);
                    });
                }
                
                // 📊 Data flow effect
                createDataFlow(container) {
                    const data = ['0', '1'];
                    for (let i = 0; i < 8; i++) {
                        setTimeout(() => {
                            const bit = document.createElement('div');
                            bit.textContent = data[Math.floor(Math.random() * 2)];
                            bit.style.cssText = `
                                position: absolute;
                                left: ${20 + Math.random() * 60}%;
                                top: -10px;
                                color: #aed604;
                                font-family: monospace;
                                font-size: 12px;
                                animation: data-fall 1.5s linear forwards;
                                opacity: 0.8;
                            `;
                            container.appendChild(bit);
                            setTimeout(() => bit.remove(), 1500);
                        }, i * 100);
                    }
                }
                
                // 🔤 Code flow effect
                createCodeFlow(container) {
                    const code = document.createElement('div');
                    code.textContent = '{ magic: true }';
                    code.style.cssText = `
                        position: absolute;
                        left: 50%;
                        top: 50%;
                        transform: translate(-50%, -50%);
                        color: #aed604;
                        font-family: monospace;
                        font-size: 12px;
                        animation: code-type 2s steps(15);
                        white-space: nowrap;
                        overflow: hidden;
                    `;
                    container.appendChild(code);
                    setTimeout(() => code.remove(), 2000);
                }
                
                startProgressAnimation() {
                    const progressText = document.getElementById('progressText');
                    
                    const updateProgress = () => {
                        this.progressValue += 0.3;
                        if (this.progressValue > 100) this.progressValue = 0;
                        
                        progressText.textContent = `Magic Progress: ${Math.floor(this.progressValue)}%`;
                        
                        requestAnimationFrame(updateProgress);
                    };
                    
                    updateProgress();
                }
                
                createEmojiSparkles() {
                    const roulette = document.getElementById('emojiRoulette');
                    const rect = roulette.getBoundingClientRect();
                    const centerX = rect.left + rect.width / 2;
                    const centerY = rect.top + rect.height / 2;
                    
                    for (let i = 0; i < 6; i++) {
                        const sparkle = document.createElement('div');
                        sparkle.className = 'sparkle';
                        sparkle.style.left = centerX + 'px';
                        sparkle.style.top = centerY + 'px';
                        
                        document.body.appendChild(sparkle);
                        
                        const angle = (i / 6) * Math.PI * 2;
                        const distance = 40 + Math.random() * 20;
                        
                        sparkle.animate([
                            { transform: 'translate(0, 0) scale(1)', opacity: 1 },
                            { transform: `translate(${Math.cos(angle) * distance}px, ${Math.sin(angle) * distance}px) scale(0)`, opacity: 0 }
                        ], {
                            duration: 1200,
                            easing: 'cubic-bezier(0.4, 0, 0.2, 1)'
                        }).onfinish = () => sparkle.remove();
                    }
                }
                
                initInteractions() {
                    // Emoji roulette click
                    document.getElementById('emojiRoulette').addEventListener('click', () => {
                        this.triggerEmojiExplosion();
                    });
                    
                    // Title hover
                    const title = document.querySelector('.brand-title');
                    title.addEventListener('mouseenter', () => {
                        title.classList.add('heartbeat');
                    });
                    title.addEventListener('mouseleave', () => {
                        title.classList.remove('heartbeat');
                    });
                    
                    // Touch support
                    document.addEventListener('touchstart', (e) => {
                        if (e.touches.length === 2) {
                            this.secretRainbowMode();
                        }
                    });
                }
                
                triggerEmojiExplosion() {
                    const roulette = document.getElementById('emojiRoulette');
                    const rect = roulette.getBoundingClientRect();
                    const centerX = rect.left + rect.width / 2;
                    const centerY = rect.top + rect.height / 2;
                    
                    const currentEmoji = this.emojis[this.currentEmojiIndex];
                    
                    for (let i = 0; i < 8; i++) {
                        const emojiParticle = document.createElement('div');
                        emojiParticle.textContent = currentEmoji;
                        emojiParticle.style.cssText = `
                            position: fixed;
                            left: ${centerX}px;
                            top: ${centerY}px;
                            font-size: ${Math.random() * 20 + 15}px;
                            pointer-events: none;
                            z-index: 1000;
                        `;
                        
                        document.body.appendChild(emojiParticle);
                        
                        const angle = Math.random() * Math.PI * 2;
                        const distance = Math.random() * 100 + 60;
                        
                        emojiParticle.animate([
                            { transform: 'translate(0, 0) scale(1) rotate(0deg)', opacity: 1 },
                            { transform: `translate(${Math.cos(angle) * distance}px, ${Math.sin(angle) * distance}px) scale(0) rotate(360deg)`, opacity: 0 }
                        ], {
                            duration: 1500,
                            easing: 'cubic-bezier(0.4, 0, 0.2, 1)'
                        }).onfinish = () => emojiParticle.remove();
                    }
                }
                
                secretRainbowMode() {
                    document.body.classList.add('glitch');
                    setTimeout(() => document.body.classList.remove('glitch'), 300);
                }
            }
            
            // 🚀 Initialize
            document.addEventListener('DOMContentLoaded', () => {
                window.maintenanceInstance = new LEDMaintenance();
            });
        </script>
        
        <!-- 📊 Pass data to JavaScript -->
        <script>
            window.maintenanceConfig = {
                startTime: <?php echo time(); ?>,
                hasETA: <?php echo !empty($eta) ? 'true' : 'false'; ?>,
                customMessage: <?php echo json_encode($message); ?>,
                siteUrl: '<?php echo home_url(); ?>'
            };
        </script>
    </body>
    </html>
    <?php
}
?>