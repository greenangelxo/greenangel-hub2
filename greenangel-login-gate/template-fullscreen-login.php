<?php defined('ABSPATH') || exit; ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#151515">
    
    <!-- Preload critical fonts for better performance - MOVED TO TOP -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <title>Angel Login - <?php bloginfo('name'); ?></title>
    
    <!-- LED Dark Mode Meta Tags -->
    <meta name="description" content="Enter the Green Angel portal - Your magical journey begins here">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo get_site_icon_url(32); ?>">
    
    <?php wp_head(); ?>
    
    <!-- LED Enhancement Styles -->
    <style>
        /* Critical CSS for immediate LED appearance */
        body {
            background: #151515 !important;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .angel-login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #151515;
        }
        
        /* Preload spinner with LED style */
        .angel-preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #151515;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }
        
        .angel-preloader.fade-out {
            opacity: 0;
            pointer-events: none;
        }
        
        .spinner-led {
            width: 60px;
            height: 60px;
            border: 3px solid rgba(255, 255, 255, 0.1);
            border-top: 3px solid #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Hide content until loaded */
        .angel-auth-wrapper {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease;
        }
        
        .angel-auth-wrapper.loaded {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body class="angel-login-body">
    
    <!-- LED Preloader -->
    <div class="angel-preloader" id="angel-preloader">
        <div class="spinner-led"></div>
    </div>
    
    <div class="angel-login-container">
        <?php
        while (have_posts()) {
            the_post();
            the_content();
        }
        ?>
    </div>
    
    <?php wp_footer(); ?>
    
    <!-- LED Enhancement Script -->
    <script>
        // Enhanced page load with LED effects
        document.addEventListener('DOMContentLoaded', function() {
            const preloader = document.getElementById('angel-preloader');
            const authWrapper = document.querySelector('.angel-auth-wrapper');
            
            // Simulate loading for smooth LED transition
            setTimeout(() => {
                if (preloader) {
                    preloader.classList.add('fade-out');
                    setTimeout(() => {
                        preloader.style.display = 'none';
                    }, 500);
                }
                
                if (authWrapper) {
                    authWrapper.classList.add('loaded');
                }
            }, 800);
            
            // Add LED cursor effect for desktop
            if (window.innerWidth > 768) {
                document.addEventListener('mousemove', function(e) {
                    // Create subtle LED trail on mouse movement
                    const trail = document.createElement('div');
                    trail.style.cssText = `
                        position: fixed;
                        left: ${e.clientX}px;
                        top: ${e.clientY}px;
                        width: 4px;
                        height: 4px;
                        background: rgba(255, 255, 255, 0.1);
                        border-radius: 50%;
                        pointer-events: none;
                        z-index: 1000;
                        transition: all 0.5s ease;
                    `;
                    
                    document.body.appendChild(trail);
                    
                    setTimeout(() => {
                        trail.style.opacity = '0';
                        trail.style.transform = 'scale(0)';
                        setTimeout(() => trail.remove(), 500);
                    }, 50);
                });
            }
            
            // Enhanced form field focus effects
            const inputs = document.querySelectorAll('.angel-input, .angel-select');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.transition = 'all 0.3s ease';
                    this.style.borderColor = 'rgba(255, 255, 255, 0.3)';
                    this.style.boxShadow = '0 0 20px rgba(255, 255, 255, 0.05)';
                });
                
                input.addEventListener('blur', function() {
                    this.style.borderColor = '';
                    this.style.boxShadow = '';
                });
            });
            
            // LED pulse effect for buttons on hover
            const buttons = document.querySelectorAll('.angel-button, .angel-tab');
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transition = 'all 0.3s ease';
                    this.style.boxShadow = '0 0 25px rgba(255, 255, 255, 0.1)';
                });
                
                button.addEventListener('mouseleave', function() {
                    this.style.boxShadow = '';
                });
            });
            
            // Enhanced accessibility
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Tab') {
                    document.body.classList.add('keyboard-navigation');
                }
            });
            
            document.addEventListener('mousedown', function() {
                document.body.classList.remove('keyboard-navigation');
            });
        });
        
        // LED CSS animations
        const ledStyles = document.createElement('style');
        ledStyles.textContent = `
            @keyframes slideInRight {
                from {
                    opacity: 0;
                    transform: translateX(100%);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            
            @keyframes slideOutRight {
                from {
                    opacity: 1;
                    transform: translateX(0);
                }
                to {
                    opacity: 0;
                    transform: translateX(100%);
                }
            }
            
            /* Enhanced keyboard navigation styles */
            .keyboard-navigation *:focus {
                outline: 2px solid rgba(255, 255, 255, 0.3) !important;
                outline-offset: 2px !important;
            }
            
            /* Smooth scrollbar for mobile */
            ::-webkit-scrollbar {
                width: 0px !important;
                display: none !important;
            }
            
            ::-webkit-scrollbar-track {
                display: none !important;
            }
            
            ::-webkit-scrollbar-thumb {
                display: none !important;
            }
            
            /* Mobile viewport fix */
            @supports (-webkit-touch-callout: none) {
                .angel-login-container {
                    min-height: -webkit-fill-available;
                }
            }
        `;
        document.head.appendChild(ledStyles);
    </script>
</body>
</html>