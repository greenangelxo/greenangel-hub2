<?php
/**
 * Angel Wallet Thank You Page Template
 * Custom thank you page for Angel Wallet payments
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

// Enhanced security validation
$order_id = absint($order_id ?? 0);
if (!$order_id) {
    wp_redirect(home_url());
    exit;
}

$order = wc_get_order($order_id);
if (!$order) {
    wp_redirect(home_url());
    exit;
}

// Verify user has access to this order
$user_id = $order->get_user_id();
if (!$user_id || $user_id !== get_current_user_id()) {
    wp_redirect(home_url());
    exit;
}

$user = get_userdata($user_id);
if (!$user) {
    wp_redirect(home_url());
    exit;
}

// Get the real amount customer paid (not the £0 internal total)
$order_total = $order->get_meta('_angel_wallet_paid_total');
if (!$order_total) {
    $order_total = $order->get_total();
}

// Validate and sanitize order total
$order_total = floatval($order_total);
if ($order_total <= 0) {
    wp_redirect(home_url());
    exit;
}

$remaining_balance = floatval(get_user_meta($user_id, 'angel_wallet_balance', true));

// Get user initials for avatar with proper sanitization
$display_name = sanitize_text_field($user->display_name);
$name_parts = explode(' ', $display_name);
$initials = '';
foreach ($name_parts as $part) {
    $clean_part = sanitize_text_field($part);
    if ($clean_part) {
        $initials .= strtoupper(substr($clean_part, 0, 1));
    }
}
$initials = substr($initials, 0, 2) ?: '👤';

// Get order items for display
$items = $order->get_items();
$item_count = count($items);

get_header();
?>

<style>
/* Angel Wallet Thank You Styles */
.angel-thankyou-container {
    position: relative;
    z-index: 1;
    max-width: 1000px;
    margin: 2rem auto;
    padding: 2rem;
    min-height: 80vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0a0a0a 100%);
    border-radius: 24px;
    position: relative;
    overflow: hidden;
}

/* Animated Background */
.angel-thankyou-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
        radial-gradient(circle at 20% 30%, rgba(174, 214, 4, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(174, 214, 4, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 50% 20%, rgba(174, 214, 4, 0.08) 0%, transparent 50%);
    animation: float 20s ease-in-out infinite;
    pointer-events: none;
    z-index: 0;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    33% { transform: translateY(-20px) rotate(1deg); }
    66% { transform: translateY(-10px) rotate(-1deg); }
}

/* Success Animation */
.success-animation {
    text-align: center;
    margin-bottom: 3rem;
    animation: slideInUp 0.8s ease-out;
    position: relative;
    z-index: 2;
}

.success-icon {
    font-size: 5rem;
    margin-bottom: 1rem;
    animation: bounce 2s infinite;
    display: block;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-20px); }
    60% { transform: translateY(-10px); }
}

.success-title {
    font-size: 3rem;
    font-weight: 900;
    background: linear-gradient(45deg, #aed604, #cfff00, #aed604);
    background-size: 300% 300%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: shimmer 3s ease-in-out infinite, slideInUp 0.8s ease-out 0.2s both;
    margin-bottom: 0.5rem;
    line-height: 1.1;
    font-family: 'Poppins', sans-serif;
}

@keyframes shimmer {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.success-subtitle {
    font-size: 1.2rem;
    color: #888;
    font-weight: 500;
    animation: slideInUp 0.8s ease-out 0.4s both;
    font-family: 'Poppins', sans-serif;
}

/* Payment Success Card */
.payment-success-card {
    background: linear-gradient(135deg, #1a1a1a, #222);
    border: 2px solid #333;
    border-radius: 24px;
    padding: 2.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    position: relative;
    overflow: hidden;
    animation: slideInUp 0.8s ease-out 0.6s both;
    width: 100%;
    max-width: 600px;
    z-index: 2;
}

.payment-success-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #aed604, #cfff00, #aed604);
    background-size: 300% 300%;
    animation: shimmer 3s ease-in-out infinite;
}

/* User Info */
.user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #333;
}

.user-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #aed604, #cfff00);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: bold;
    color: #1a1a1a;
    box-shadow: 0 8px 32px rgba(174, 214, 4, 0.3);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 8px 32px rgba(174, 214, 4, 0.3); }
    50% { box-shadow: 0 8px 40px rgba(174, 214, 4, 0.5); }
    100% { box-shadow: 0 8px 32px rgba(174, 214, 4, 0.3); }
}

.user-details h3 {
    color: #aed604;
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
    font-family: 'Poppins', sans-serif;
}

.user-details p {
    color: #888;
    font-size: 0.9rem;
    font-family: 'Poppins', sans-serif;
}

/* Payment Details */
.payment-details {
    margin-bottom: 2rem;
}

.payment-amount {
    text-align: center;
    margin-bottom: 1.5rem;
}

.amount-label {
    color: #888;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
}

.amount-value {
    font-size: 3rem;
    font-weight: 900;
    color: #aed604;
    text-shadow: 0 4px 12px rgba(174, 214, 4, 0.3);
    animation: countUp 1s ease-out 1s both;
    font-family: 'Poppins', sans-serif;
}

@keyframes countUp {
    from { opacity: 0; transform: scale(0.5); }
    to { opacity: 1; transform: scale(1); }
}

.payment-method {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    background: rgba(174, 214, 4, 0.1);
    border: 1px solid rgba(174, 214, 4, 0.3);
    border-radius: 50px;
    padding: 0.75rem 1.5rem;
    margin: 0 auto;
    width: fit-content;
}

.payment-method-icon {
    font-size: 1.2rem;
}

.payment-method-text {
    color: #aed604;
    font-weight: 600;
    font-size: 0.9rem;
    font-family: 'Poppins', sans-serif;
}

/* Order Summary */
.order-summary {
    background: #0a0a0a;
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #333;
}

.order-number {
    color: #aed604;
    font-weight: 700;
    font-size: 1.1rem;
    font-family: 'Poppins', sans-serif;
}

.order-date {
    color: #888;
    font-size: 0.9rem;
    font-family: 'Poppins', sans-serif;
}

.order-items {
    margin-bottom: 1rem;
}

.item-count {
    color: #aed604;
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-family: 'Poppins', sans-serif;
}

.order-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    color: #ccc;
    font-size: 0.9rem;
    font-family: 'Poppins', sans-serif;
}

/* Balance Display */
.remaining-balance {
    background: linear-gradient(135deg, #2a2a2a, #1a1a1a);
    border: 1px solid #444;
    border-radius: 16px;
    padding: 1.5rem;
    text-align: center;
    margin-bottom: 2rem;
}

.balance-label {
    color: #888;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
}

.balance-amount {
    font-size: 2rem;
    font-weight: 800;
    color: #aed604;
    text-shadow: 0 2px 8px rgba(174, 214, 4, 0.3);
    font-family: 'Poppins', sans-serif;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    justify-content: center;
    position: relative;
    z-index: 2;
}

.action-btn {
    background: linear-gradient(135deg, #aed604, #cfff00);
    color: #1a1a1a;
    padding: 1rem 2rem;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(174, 214, 4, 0.3);
    font-family: 'Poppins', sans-serif;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(174, 214, 4, 0.4);
    text-decoration: none;
    color: #1a1a1a;
}

.action-btn.secondary {
    background: transparent;
    color: #aed604;
    border: 2px solid #aed604;
    box-shadow: none;
}

.action-btn.secondary:hover {
    background: rgba(174, 214, 4, 0.1);
    color: #aed604;
}

/* Floating Confetti */
.confetti {
    position: fixed;
    width: 10px;
    height: 10px;
    background: #aed604;
    animation: confettiFall 3s linear infinite;
    z-index: 1000;
}

.confetti:nth-child(odd) {
    background: #cfff00;
    border-radius: 50%;
}

@keyframes confettiFall {
    0% {
        transform: translateY(-100vh) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translateY(100vh) rotate(720deg);
        opacity: 0;
    }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .angel-thankyou-container {
        margin: 1rem;
        padding: 1rem;
        border-radius: 16px;
    }
    
    .success-title {
        font-size: 2rem;
    }
    
    .payment-success-card {
        padding: 1.5rem;
    }
    
    .amount-value {
        font-size: 2rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .action-btn {
        width: 100%;
        text-align: center;
    }
    
    .user-info {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .order-header {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
    }
    
    .order-item {
        flex-direction: column;
        gap: 0.25rem;
        text-align: center;
    }
}

/* Slide-in Animations */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.slide-in {
    animation: slideInUp 0.6s ease-out both;
}

.slide-in-1 { animation-delay: 0.1s; }
.slide-in-2 { animation-delay: 0.2s; }
.slide-in-3 { animation-delay: 0.3s; }
.slide-in-4 { animation-delay: 0.4s; }
.slide-in-5 { animation-delay: 0.5s; }

/* Font Override */
* {
    font-family: 'Poppins', sans-serif !important;
}
</style>

<!-- Import Poppins Font -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<!-- Floating Confetti -->
<div class="confetti" style="left: 10%; animation-delay: 0s;"></div>
<div class="confetti" style="left: 20%; animation-delay: 0.5s;"></div>
<div class="confetti" style="left: 30%; animation-delay: 1s;"></div>
<div class="confetti" style="left: 40%; animation-delay: 1.5s;"></div>
<div class="confetti" style="left: 50%; animation-delay: 2s;"></div>
<div class="confetti" style="left: 60%; animation-delay: 0.3s;"></div>
<div class="confetti" style="left: 70%; animation-delay: 0.8s;"></div>
<div class="confetti" style="left: 80%; animation-delay: 1.3s;"></div>
<div class="confetti" style="left: 90%; animation-delay: 1.8s;"></div>

<div class="angel-thankyou-container">
    <!-- Success Animation -->
    <div class="success-animation">
        <div class="success-icon">🎉</div>
        <h1 class="success-title">Order Confirmed!</h1>
        <p class="success-subtitle">Your Angel Wallet payment was processed instantly</p>
    </div>

    <!-- Payment Success Card -->
    <div class="payment-success-card">
        <!-- User Info -->
        <div class="user-info slide-in-1">
            <div class="user-avatar"><?php echo esc_html($initials); ?></div>
            <div class="user-details">
                <h3><?php echo esc_html($display_name); ?></h3>
                <p>Angel Wallet Payment • <?php echo esc_html(date('F j, Y \a\t g:i A')); ?></p>
            </div>
        </div>

        <!-- Payment Amount -->
        <div class="payment-details slide-in-2">
            <div class="payment-amount">
                <div class="amount-label">Amount Charged</div>
                <div class="amount-value">£<?php echo number_format($order_total, 2); ?></div>
            </div>
            
            <div class="payment-method">
                <span class="payment-method-icon">💸</span>
                <span class="payment-method-text">Paid with Angel Wallet</span>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="order-summary slide-in-3">
            <div class="order-header">
                <div class="order-number">Order #<?php echo esc_html($order->get_order_number()); ?></div>
                <div class="order-date"><?php echo esc_html($order->get_date_created()->format('M j, Y')); ?></div>
            </div>
            
            <div class="order-items">
                <div class="item-count"><?php echo intval($item_count); ?> item<?php echo $item_count > 1 ? 's' : ''; ?> ordered</div>
                <?php foreach ($items as $item): 
                    $item_name = sanitize_text_field($item->get_name());
                    $item_quantity = absint($item->get_quantity());
                    $item_total = floatval($item->get_total());
                ?>
                <div class="order-item">
                    <span><?php echo esc_html($item_name); ?> × <?php echo $item_quantity; ?></span>
                    <span>£<?php echo number_format($item_total, 2); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Remaining Balance -->
        <div class="remaining-balance slide-in-4">
            <div class="balance-label">Remaining Angel Wallet Balance</div>
            <div class="balance-amount">£<?php echo number_format($remaining_balance, 2); ?></div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons slide-in-5">
        <a href="<?php echo esc_url(home_url('/shop')); ?>" class="action-btn">
            🛍️ Continue Shopping
        </a>
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('dashboard')); ?>" class="action-btn secondary">
            💳 Back to Dashboard
        </a>
    </div>
</div>

<script>
/**
 * Dynamic confetti generation
 */
function createConfetti() {
    const confetti = document.createElement('div');
    confetti.classList.add('confetti');
    confetti.style.left = Math.random() * 100 + '%';
    confetti.style.animationDelay = Math.random() * 3 + 's';
    confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
    document.body.appendChild(confetti);
    
    setTimeout(() => {
        confetti.remove();
    }, 5000);
}

// Generate confetti for first 10 seconds
const confettiInterval = setInterval(createConfetti, 500);
setTimeout(() => {
    clearInterval(confettiInterval);
}, 10000);

/**
 * Success sound effect
 */
try {
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();
    
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    
    oscillator.frequency.setValueAtTime(523.25, audioContext.currentTime);
    oscillator.frequency.setValueAtTime(659.25, audioContext.currentTime + 0.1);
    oscillator.frequency.setValueAtTime(783.99, audioContext.currentTime + 0.2);
    
    gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
    
    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.5);
} catch (e) {
    // Silent fail for browsers without Web Audio API support
}
</script>

<?php get_footer(); ?>