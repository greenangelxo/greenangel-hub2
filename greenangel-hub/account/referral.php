<?php
/**
 * 🌿 GREEN ANGEL HUB v2.0 - ELEGANT REFERRAL CONSOLE
 * Clean, premium sharing hub with sophisticated dark styling
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function ga_render_referral_section($user_id) {
    if (!$user_id) return '';
    
    $user = get_userdata($user_id);
    if (!$user) return '';
    
    // Get WP Loyalty referral code
    $ref_code = null;
    if (function_exists('greenangel_get_loyalty_referral_code')) {
        $ref_code = greenangel_get_loyalty_referral_code($user->user_email);
    }
    
    // Build full referral URL
    $ref_url = $ref_code ? esc_url(home_url('?wlr_ref=' . $ref_code)) : '';
    
    // Get active Angel Access Code
    $angel_code = greenangel_get_main_angel_code();
    
    ob_start();
    ?>
    
    <div class="ga-referral-console">
        <div class="ga-referral-container">
            
            <!-- Console Header -->
            <div class="ga-console-header">
                <div class="ga-console-title">
                    <span class="ga-console-icon">🎁</span>
                    <span class="ga-console-text">Share & Earn</span>
                </div>
                <div class="ga-console-subtitle">
                    Grow the Angel community
                </div>
            </div>
            
            <!-- Premium Cards Grid -->
            <div class="ga-sharing-grid">
                
                <!-- Referral Link Card -->
                <div class="ga-share-card referral">
                    <div class="ga-card-header">
                        <div class="ga-card-header-content">
                            <span class="ga-card-icon">🔗</span>
                            <span class="ga-card-title">Referral Link</span>
                        </div>
                    </div>
                    
                    <?php if ($ref_url): ?>
                    <div class="ga-link-display ga-reveal-box" onclick="revealReferralLink()" id="referralContainer">
                        <span class="ga-placeholder-text" id="referralPlaceholder">Click to reveal your referral link</span>
                        <span class="ga-hidden-content" id="referralContent"><?php echo esc_url($ref_url); ?></span>
                        <div class="ga-countdown-timer" id="referralTimer">6</div>
                    </div>
                    
                    <div class="ga-action-row">
                        <button class="ga-copy-btn primary" onclick="copyReferralLink()" id="copyReferralBtn">
                            <span class="ga-btn-icon">📋</span>
                            <span class="ga-btn-text">Copy Link</span>
                        </button>
                        
                        <div class="ga-share-mini">
                            <button class="ga-mini-btn whatsapp" onclick="shareWhatsApp('referral')" title="Share via WhatsApp">
                                <span>📱</span>
                            </button>
                            <button class="ga-mini-btn email" onclick="shareEmail('referral')" title="Share via Email">
                                <span>📧</span>
                            </button>
                        </div>
                    </div>
                    
                    <?php else: ?>
                    <div class="ga-link-display empty">
                        <span class="ga-empty-text">No referral link available</span>
                    </div>
                    
                    <div class="ga-action-row">
                        <button class="ga-copy-btn disabled" disabled>
                            <span class="ga-btn-icon">🔒</span>
                            <span class="ga-btn-text">Locked</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="ga-card-desc">
                        Earn Halo Points when friends make their first purchase
                    </div>
                </div>
                
                <!-- Angel Access Code Card -->
                <div class="ga-share-card angel">
                    <div class="ga-card-header">
                        <div class="ga-card-header-content">
                            <span class="ga-card-icon">👑</span>
                            <span class="ga-card-title">Angel Code</span>
                        </div>
                    </div>
                    
                    <?php if ($angel_code): ?>
                    <div class="ga-code-display ga-reveal-box angel" onclick="revealAngelCode()" id="angelContainer">
                        <span class="ga-placeholder-text" id="angelPlaceholder">Click to reveal Angel Code</span>
                        <span class="ga-hidden-content" id="angelContent"><?php echo esc_html($angel_code); ?></span>
                        <div class="ga-countdown-timer" id="angelTimer">6</div>
                    </div>
                    
                    <div class="ga-action-row">
                        <button class="ga-copy-btn accent" onclick="copyAngelCode()" id="copyAngelBtn">
                            <span class="ga-btn-icon">📋</span>
                            <span class="ga-btn-text">Copy Code</span>
                        </button>
                        
                        <div class="ga-share-mini">
                            <button class="ga-mini-btn whatsapp" onclick="shareWhatsApp('angel')" title="Share via WhatsApp">
                                <span>📱</span>
                            </button>
                            <button class="ga-mini-btn email" onclick="shareEmail('angel')" title="Share via Email">
                                <span>📧</span>
                            </button>
                        </div>
                    </div>
                    
                    <?php else: ?>
                    <div class="ga-code-display empty">
                        <span class="ga-empty-text">No active code</span>
                    </div>
                    
                    <div class="ga-action-row">
                        <button class="ga-copy-btn disabled" disabled>
                            <span class="ga-btn-icon">🔒</span>
                            <span class="ga-btn-text">Locked</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="ga-card-desc">
                        Exclusive invitation code for new Angels
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    <script>
    // 🎁 Referral Link Functions
    function copyReferralLink() {
        const container = document.getElementById('referralContainer');
        const btnElement = document.getElementById('copyReferralBtn');
        const textElement = btnElement.querySelector('.ga-btn-text');
        const iconElement = btnElement.querySelector('.ga-btn-icon');
        
        if (!container || !container.classList.contains('revealed')) {
            // If not revealed yet, reveal first
            revealReferralLink();
            return;
        }
        
        const urlElement = document.getElementById('referralContent');
        if (!urlElement) return;
        
        const url = urlElement.textContent.trim();
        
        navigator.clipboard.writeText(url).then(() => {
            // Success feedback
            btnElement.classList.add('success');
            textElement.textContent = 'Copied!';
            iconElement.textContent = '✅';
            
            setTimeout(() => {
                btnElement.classList.remove('success');
                textElement.textContent = 'Copy Link';
                iconElement.textContent = '📋';
            }, 2000);
        }).catch(() => {
            // Fallback for older browsers
            fallbackCopy(url, btnElement, textElement, iconElement);
        });
    }
    
    // 👑 Angel Code Functions
    function copyAngelCode() {
        const container = document.getElementById('angelContainer');
        const btnElement = document.getElementById('copyAngelBtn');
        const textElement = btnElement.querySelector('.ga-btn-text');
        const iconElement = btnElement.querySelector('.ga-btn-icon');
        
        if (!container || !container.classList.contains('revealed')) {
            // If not revealed yet, reveal first
            revealAngelCode();
            return;
        }
        
        const codeElement = document.getElementById('angelContent');
        if (!codeElement) return;
        
        const code = codeElement.textContent.trim();
        
        navigator.clipboard.writeText(code).then(() => {
            // Success feedback
            btnElement.classList.add('success');
            textElement.textContent = 'Copied!';
            iconElement.textContent = '✅';
            
            setTimeout(() => {
                btnElement.classList.remove('success');
                textElement.textContent = 'Copy Code';
                iconElement.textContent = '📋';
            }, 2000);
        }).catch(() => {
            // Fallback for older browsers
            fallbackCopy(code, btnElement, textElement, iconElement);
        });
    }
    
    // ✨ Enhanced Reveal Functions with Auto-Hide + Timer
    function revealReferralLink() {
        const container = document.getElementById('referralContainer');
        const placeholder = document.getElementById('referralPlaceholder');
        const content = document.getElementById('referralContent');
        const timer = document.getElementById('referralTimer');
        
        if (!container || container.classList.contains('revealed')) return;
        
        // Clear any existing timeout and interval
        if (container.hideTimeout) {
            clearTimeout(container.hideTimeout);
        }
        if (container.countdownInterval) {
            clearInterval(container.countdownInterval);
        }
        
        // Add revealing class
        container.classList.add('revealing');
        
        setTimeout(() => {
            placeholder.style.opacity = '0';
            placeholder.style.transform = 'translateY(-8px)';
            
            setTimeout(() => {
                placeholder.style.display = 'none';
                content.style.display = 'block';
                content.style.opacity = '0';
                content.style.transform = 'translateY(8px)';
                
                setTimeout(() => {
                    content.style.opacity = '1';
                    content.style.transform = 'translateY(0)';
                    container.classList.remove('revealing');
                    container.classList.add('revealed');
                    
                    // Show and start countdown timer
                    timer.textContent = '6';
                    timer.style.opacity = '1';
                    timer.style.transform = 'scale(1)';
                    
                    let timeLeft = 6;
                    container.countdownInterval = setInterval(() => {
                        timeLeft--;
                        timer.textContent = timeLeft;
                        
                        if (timeLeft <= 2) {
                            timer.classList.add('warning');
                        }
                        
                        if (timeLeft <= 0) {
                            clearInterval(container.countdownInterval);
                        }
                    }, 1000);
                    
                    // Auto-hide after 6 seconds
                    container.hideTimeout = setTimeout(() => {
                        hideReferralLink();
                    }, 6000);
                }, 50);
            }, 150);
        }, 100);
    }
    
    function revealAngelCode() {
        const container = document.getElementById('angelContainer');
        const placeholder = document.getElementById('angelPlaceholder');
        const content = document.getElementById('angelContent');
        const timer = document.getElementById('angelTimer');
        
        if (!container || container.classList.contains('revealed')) return;
        
        // Clear any existing timeout and interval
        if (container.hideTimeout) {
            clearTimeout(container.hideTimeout);
        }
        if (container.countdownInterval) {
            clearInterval(container.countdownInterval);
        }
        
        // Add revealing class
        container.classList.add('revealing');
        
        setTimeout(() => {
            placeholder.style.opacity = '0';
            placeholder.style.transform = 'translateY(-8px)';
            
            setTimeout(() => {
                placeholder.style.display = 'none';
                content.style.display = 'block';
                content.style.opacity = '0';
                content.style.transform = 'translateY(8px)';
                
                setTimeout(() => {
                    content.style.opacity = '1';
                    content.style.transform = 'translateY(0)';
                    container.classList.remove('revealing');
                    container.classList.add('revealed');
                    
                    // Show and start countdown timer
                    timer.textContent = '6';
                    timer.style.opacity = '1';
                    timer.style.transform = 'scale(1)';
                    
                    let timeLeft = 6;
                    container.countdownInterval = setInterval(() => {
                        timeLeft--;
                        timer.textContent = timeLeft;
                        
                        if (timeLeft <= 2) {
                            timer.classList.add('warning');
                        }
                        
                        if (timeLeft <= 0) {
                            clearInterval(container.countdownInterval);
                        }
                    }, 1000);
                    
                    // Auto-hide after 6 seconds
                    container.hideTimeout = setTimeout(() => {
                        hideAngelCode();
                    }, 6000);
                }, 50);
            }, 150);
        }, 100);
    }
    
    // 🔒 Auto-Hide Functions
    function hideReferralLink() {
        const container = document.getElementById('referralContainer');
        const placeholder = document.getElementById('referralPlaceholder');
        const content = document.getElementById('referralContent');
        const timer = document.getElementById('referralTimer');
        
        if (!container || !container.classList.contains('revealed')) return;
        
        // Clear intervals
        if (container.countdownInterval) {
            clearInterval(container.countdownInterval);
        }
        
        // Add hiding class
        container.classList.add('hiding');
        container.classList.remove('revealed');
        
        // Hide timer
        timer.style.opacity = '0';
        timer.style.transform = 'scale(0.8)';
        timer.classList.remove('warning');
        
        // Fade out content
        content.style.opacity = '0';
        content.style.transform = 'translateY(-8px)';
        
        setTimeout(() => {
            content.style.display = 'none';
            placeholder.style.display = 'flex';
            placeholder.style.opacity = '0';
            placeholder.style.transform = 'translateY(8px)';
            
            setTimeout(() => {
                placeholder.style.opacity = '1';
                placeholder.style.transform = 'translateY(0)';
                container.classList.remove('hiding');
            }, 50);
        }, 150);
    }
    
    function hideAngelCode() {
        const container = document.getElementById('angelContainer');
        const placeholder = document.getElementById('angelPlaceholder');
        const content = document.getElementById('angelContent');
        const timer = document.getElementById('angelTimer');
        
        if (!container || !container.classList.contains('revealed')) return;
        
        // Clear intervals
        if (container.countdownInterval) {
            clearInterval(container.countdownInterval);
        }
        
        // Add hiding class
        container.classList.add('hiding');
        container.classList.remove('revealed');
        
        // Hide timer
        timer.style.opacity = '0';
        timer.style.transform = 'scale(0.8)';
        timer.classList.remove('warning');
        
        // Fade out content
        content.style.opacity = '0';
        content.style.transform = 'translateY(-8px)';
        
        setTimeout(() => {
            content.style.display = 'none';
            placeholder.style.display = 'flex';
            placeholder.style.opacity = '0';
            placeholder.style.transform = 'translateY(8px)';
            
            setTimeout(() => {
                placeholder.style.opacity = '1';
                placeholder.style.transform = 'translateY(0)';
                container.classList.remove('hiding');
            }, 50);
        }, 150);
    }
    
    // 📋 Fallback Copy Function
    function fallbackCopy(text, btnElement, textElement, iconElement) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            btnElement.classList.add('success');
            textElement.textContent = 'Copied!';
            iconElement.textContent = '✅';
            
            setTimeout(() => {
                btnElement.classList.remove('success');
                textElement.textContent = btnElement.classList.contains('primary') ? 'Copy Link' : 'Copy Code';
                iconElement.textContent = '📋';
            }, 2000);
        } catch (err) {
            textElement.textContent = 'Failed';
            setTimeout(() => {
                textElement.textContent = btnElement.classList.contains('primary') ? 'Copy Link' : 'Copy Code';
            }, 2000);
        }
        
        document.body.removeChild(textArea);
    }
    
    // 📱 Share Functions
    function shareWhatsApp(type) {
        let message, url;
        
        if (type === 'referral') {
            const container = document.getElementById('referralContainer');
            if (!container?.classList.contains('revealed')) {
                revealReferralLink();
                return;
            }
            url = document.getElementById('referralContent')?.textContent.trim();
            if (!url) return;
            message = `Hey! 🌿 Check out Green Angel Shop - they have amazing natural products! Use my referral link: ${url}`;
        } else {
            const container = document.getElementById('angelContainer');
            if (!container?.classList.contains('revealed')) {
                revealAngelCode();
                return;
            }
            const code = document.getElementById('angelContent')?.textContent.trim();
            if (!code) return;
            message = `You're invited to join Green Angel Shop! 👑 Use my exclusive Angel Access Code: ${code} when you visit greenangelshop.com`;
        }
        
        if (message) {
            const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }
    }
    
    function shareEmail(type) {
        let subject, body;
        
        if (type === 'referral') {
            const container = document.getElementById('referralContainer');
            if (!container?.classList.contains('revealed')) {
                revealReferralLink();
                return;
            }
            const url = document.getElementById('referralContent')?.textContent.trim();
            if (!url) return;
            subject = '🌿 Discover Green Angel Shop - Natural Products You\'ll Love!';
            body = `Hi there!\n\nI wanted to share Green Angel Shop with you - they have incredible natural and sustainable products!\n\nUse my referral link: ${url}\n\nHappy shopping! 💚`;
        } else {
            const container = document.getElementById('angelContainer');
            if (!container?.classList.contains('revealed')) {
                revealAngelCode();
                return;
            }
            const code = document.getElementById('angelContent')?.textContent.trim();
            if (!code) return;
            subject = '👑 You\'re Invited to Join Green Angel Shop!';
            body = `Hi!\n\nYou're invited to join the exclusive Green Angel community!\n\nUse this special Angel Access Code: ${code}\n\nVisit: greenangelshop.com\n\nWelcome to the family! 🌿✨`;
        }
        
        const mailtoUrl = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
        window.location.href = mailtoUrl;
    }
    
    // ✨ Enhanced interactions
    document.addEventListener('DOMContentLoaded', function() {
        // Add smooth touch feedback
        const buttons = document.querySelectorAll('.ga-copy-btn, .ga-mini-btn');
        
        buttons.forEach(button => {
            button.addEventListener('touchstart', function() {
                if (!this.disabled && !this.classList.contains('disabled')) {
                    this.style.transform = 'scale(0.96)';
                }
            });
            
            button.addEventListener('touchend', function() {
                this.style.transform = '';
            });
            
            button.addEventListener('mousedown', function() {
                if (!this.disabled && !this.classList.contains('disabled')) {
                    this.style.transform = 'scale(0.96)';
                }
            });
            
            button.addEventListener('mouseup', function() {
                this.style.transform = '';
            });
            
            button.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}

/**
 * 🌿 HELPER: Get Share Message Templates
 */
function ga_get_share_templates() {
    return [
        'referral' => [
            'whatsapp' => "Hey! 🌿 Check out Green Angel Shop - amazing natural products! Use my referral link: [URL]",
            'email_subject' => "🌿 Discover Green Angel Shop - Natural Products You'll Love!",
            'email_body' => "Hi there!\n\nI wanted to share Green Angel Shop with you - incredible natural and sustainable products!\n\nUse my referral link: [URL]\n\nHappy shopping! 💚",
        ],
        'angel' => [
            'whatsapp' => "You're invited to join Green Angel Shop! 👑 Use my exclusive Angel Access Code: [CODE] at greenangelshop.com",
            'email_subject' => "👑 You're Invited to Join Green Angel Shop!",
            'email_body' => "Hi!\n\nYou're invited to join the exclusive Green Angel community!\n\nUse this special Angel Access Code: [CODE]\n\nVisit: greenangelshop.com\n\nWelcome to the family! 🌿✨",
        ]
    ];
}
?>