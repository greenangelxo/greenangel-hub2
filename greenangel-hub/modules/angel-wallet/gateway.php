<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

add_filter('woocommerce_payment_gateways', 'greenangel_register_wallet_gateway');
function greenangel_register_wallet_gateway($gateways) {
    $gateways[] = 'WC_Gateway_Angel_Wallet';
    return $gateways;
}

/**
 * Custom thank you page for Angel Wallet payments
 */
add_action('template_redirect', 'greenangel_intercept_thankyou_page');

function greenangel_intercept_thankyou_page() {
    global $wp_query;
    
    if (!is_wc_endpoint_url('order-received')) {
        return;
    }
    
    $order_id = get_query_var('order-received');
    if (!$order_id) {
        return;
    }
    
    $order = wc_get_order($order_id);
    if (!$order || $order->get_payment_method() !== 'angel_wallet') {
        return;
    }
    
    $template = plugin_dir_path(__FILE__) . 'templates/thankyou-angel-wallet.php';
    if (file_exists($template)) {
        include $template;
        exit;
    }
}

function greenangel_override_thankyou_text($text, $order) {
    if (is_a($order, 'WC_Order') && $order->get_payment_method() === 'angel_wallet') {
        return '';
    }
    return $text;
}

add_action('plugins_loaded', 'greenangel_load_wallet_gateway');
function greenangel_load_wallet_gateway() {
    if (!class_exists('WC_Payment_Gateway')) return;
    
    class WC_Gateway_Angel_Wallet extends WC_Payment_Gateway {
        public function __construct() {
            $this->id = 'angel_wallet';
            $this->has_fields = true;
            $this->method_title = 'Angel Wallet';
            $this->method_description = 'Pay using your Angel Wallet balance';
            
            $user_id = get_current_user_id();
            $balance = is_user_logged_in() ? floatval(get_user_meta($user_id, 'angel_wallet_balance', true)) : 0;
            $this->title = 'Pay with Angel Wallet';
            
            $this->icon = '';
            $this->supports = ['products'];
            
            $this->init_settings();
            $this->init_form_fields();
            
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
            add_action('wp_enqueue_scripts', [$this, 'add_angel_wallet_styles'], 999);
        }
        
        public function add_angel_wallet_styles() {
            if (is_checkout()) {
                ?>
                <style>
                /* Angel Wallet Checkout Styles */
                .payment_method_angel_wallet {
                    position: relative;
                    overflow: hidden;
                    border-radius: 16px !important;
                    background: #1a1a1a !important;
                    border: 2px solid #333 !important;
                    margin: 20px 0 !important;
                    padding: 4px !important;
                    transition: all 0.3s ease !important;
                }
                
                .payment_method_angel_wallet::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: linear-gradient(45deg, #2a2a2a, #aed604, #3a3a3a, #aed604, #2a2a2a);
                    background-size: 300% 300%;
                    animation: subtleShimmer 6s ease-in-out infinite;
                    z-index: 0;
                    border-radius: 16px;
                    opacity: 0.3;
                }
                
                @keyframes subtleShimmer {
                    0%, 100% { background-position: 0% 50%; }
                    50% { background-position: 100% 50%; }
                }
                
                .payment_method_angel_wallet:hover {
                    transform: translateY(-3px) !important;
                    border-color: #aed604 !important;
                    box-shadow: 0 8px 32px rgba(174, 214, 4, 0.3) !important;
                }
                
                .payment_method_angel_wallet input[type="radio"] {
                    position: absolute;
                    opacity: 0;
                    width: 0;
                    height: 0;
                }
                
                .payment_method_angel_wallet label {
                    display: flex !important;
                    align-items: center !important;
                    padding: 20px 24px !important;
                    cursor: pointer !important;
                    color: #ffffff !important;
                    font-weight: 700 !important;
                    font-size: 16px !important;
                    position: relative !important;
                    z-index: 2 !important;
                    margin: 0 !important;
                    background: #1a1a1a !important;
                    border-radius: 16px !important;
                }
                
                .payment_method_angel_wallet label::before {
                    content: '';
                    width: 24px;
                    height: 24px;
                    border: 3px solid #aed604;
                    border-radius: 50%;
                    margin-right: 16px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: transparent;
                    transition: all 0.2s ease;
                    flex-shrink: 0;
                }
                
                .payment_method_angel_wallet input[type="radio"]:checked + label::before {
                    background: #aed604;
                    box-shadow: 0 0 16px rgba(174, 214, 4, 0.4);
                }
                
                .payment_method_angel_wallet input[type="radio"]:checked + label::after {
                    content: '✓';
                    position: absolute;
                    left: 30px;
                    color: #000000;
                    font-weight: bold;
                    font-size: 14px;
                    z-index: 3;
                }
                
                .payment_method_angel_wallet input[type="radio"]:checked + label {
                    background: #222222 !important;
                }
                
                .payment_method_angel_wallet .payment_box {
                    background: #1a1a1a !important;
                    border: none !important;
                    border-radius: 0 0 16px 16px !important;
                    padding: 24px !important;
                    margin: 0 !important;
                    position: relative !important;
                    z-index: 2 !important;
                }
                
                .angel-wallet-checkout-content {
                    display: flex;
                    flex-direction: column;
                    gap: 16px;
                    color: #ffffff;
                }
                
                .angel-wallet-info {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 16px 20px;
                    background: #1a1a1a !important;
                    border: 2px solid #333 !important;
                    border-radius: 12px !important;
                    margin-bottom: 16px !important;
                }
                
                .payment_method_angel_wallet .angel-balance-section .angel-wallet-status {
                    color: #aed604 !important;
                    font-weight: 600 !important;
                    font-size: 16px !important;
                    font-family: 'Poppins', sans-serif !important;
                    text-transform: none !important;
                    letter-spacing: normal !important;
                }
                
                .payment_method_angel_wallet .angel-balance-section .angel-wallet-balance-large {
                    color: #ffffff !important;
                    font-weight: 700 !important;
                    font-size: 24px !important;
                    font-family: 'Poppins', sans-serif !important;
                    text-shadow: 0 1px 2px rgba(0,0,0,0.3) !important;
                }
                
                .payment_method_angel_wallet .angel-status-section .angel-wallet-status {
                    color: #aed604 !important;
                    font-weight: 600 !important;
                    font-size: 16px !important;
                    font-family: 'Poppins', sans-serif !important;
                    text-transform: none !important;
                    letter-spacing: normal !important;
                }
                
                .payment_method_angel_wallet .angel-status-section .angel-wallet-status:last-child {
                    color: #ffffff !important;
                    font-weight: 700 !important;
                    font-size: 24px !important;
                    font-family: 'Poppins', sans-serif !important;
                    text-shadow: 0 1px 2px rgba(0,0,0,0.3) !important;
                }
                
                .payment_method_angel_wallet .angel-wallet-info *,
                .payment_method_angel_wallet .angel-wallet-checkout-content * {
                    font-family: 'Poppins', sans-serif !important;
                }
                
                .payment_method_angel_wallet .angel-wallet-info div:first-child {
                    color: #aed604 !important;
                    font-weight: 600 !important;
                }
                
                .payment_method_angel_wallet .angel-wallet-info div:last-child {
                    color: #ffffff !important;
                    font-weight: 700 !important;
                }
                
                .payment_method_angel_wallet .angel-wallet-info div,
                .payment_method_angel_wallet .angel-wallet-info span,
                .payment_method_angel_wallet .angel-balance-section div,
                .payment_method_angel_wallet .angel-status-section div,
                .payment_method_angel_wallet .angel-wallet-balance-large,
                .payment_method_angel_wallet .angel-wallet-status {
                    color: #ffffff !important;
                    font-weight: 900 !important;
                }
                
                .payment_method_angel_wallet .angel-wallet-info::before,
                .payment_method_angel_wallet .angel-wallet-info::after,
                .payment_method_angel_wallet .angel-balance-section *,
                .payment_method_angel_wallet .angel-status-section * {
                    color: #ffffff !important;
                    font-weight: 900 !important;
                }
                
                .angel-wallet-balance-large {
                    font-size: 24px;
                    font-weight: 800;
                    color: #ffffff;
                }
                
                .angel-wallet-status {
                    font-size: 13px;
                    color: #aed604;
                    font-weight: 500;
                }
                
                .angel-wallet-icon {
                    font-size: 28px;
                }
                
                .payment_method_angel_wallet .angel-wallet-checkout-content .woocommerce-Price-amount,
                .payment_method_angel_wallet .angel-wallet-info .woocommerce-Price-amount,
                .payment_method_angel_wallet .angel-wallet-balance-large .woocommerce-Price-amount,
                .payment_method_angel_wallet .angel-wallet-status .woocommerce-Price-amount,
                .payment_method_angel_wallet span.woocommerce-Price-amount,
                .payment_method_angel_wallet .woocommerce-Price-amount.amount,
                .payment_method_angel_wallet .woocommerce-Price-currencySymbol {
                    display: inline !important;
                    visibility: visible !important;
                    color: #ffffff !important;
                    font-weight: 900 !important;
                    font-size: inherit !important;
                    opacity: 1 !important;
                }
                
                .payment_method_angel_wallet .woocommerce-Price-amount span,
                .payment_method_angel_wallet .angel-wallet-balance-large span,
                .payment_method_angel_wallet .angel-wallet-status span {
                    color: #ffffff !important;
                    font-weight: 900 !important;
                    display: inline !important;
                }
                
                body.woocommerce-checkout .payment_method_angel_wallet .woocommerce-Price-amount,
                body.woocommerce-checkout .payment_method_angel_wallet span.woocommerce-Price-amount,
                body.woocommerce-checkout .payment_method_angel_wallet .price {
                    display: inline !important;
                    color: #ffffff !important;
                    font-weight: 900 !important;
                }
                
                /* Mobile responsiveness */
                @media (max-width: 768px) {
                    .payment_method_angel_wallet {
                        margin: 16px 0 !important;
                    }
                    
                    .payment_method_angel_wallet label {
                        padding: 18px 20px !important;
                        font-size: 15px !important;
                    }
                    
                    .angel-wallet-info {
                        flex-direction: column;
                        gap: 12px;
                        text-align: center;
                        padding: 16px;
                    }
                    
                    .angel-wallet-balance {
                        font-size: 12px !important;
                        padding: 3px 8px !important;
                    }
                }
                
                .payment_method_angel_wallet:focus-within {
                    border-color: #aed604 !important;
                }
                </style>
                <?php
            }
        }
        
        public function payment_fields() {
            if (!is_user_logged_in()) {
                echo '<p style="color: #ff6b6b; font-weight: 500;">Please log in to use your Angel Wallet.</p>';
                return;
            }
            
            $user_id = get_current_user_id();
            $balance = floatval(get_user_meta($user_id, 'angel_wallet_balance', true));
            $cart_total = floatval(WC()->cart->get_total('edit'));
            
            $formatted_balance = '£' . number_format($balance, 2);
            $formatted_total = '£' . number_format($cart_total, 2);
            $formatted_needed = '£' . number_format($cart_total - $balance, 2);
            
            ?>
            <div class="angel-wallet-checkout-content">
                <div class="angel-wallet-info">
                    <div class="angel-balance-section">
                        <div class="angel-wallet-status">Balance:</div>
                        <div class="angel-wallet-balance-large"><?php echo $formatted_balance; ?></div>
                    </div>
                    <div class="angel-status-section">
                        <?php if ($balance >= $cart_total): ?>
                            <div class="angel-wallet-status">Ready to pay</div>
                            <div class="angel-wallet-status"><?php echo $formatted_total; ?></div>
                        <?php else: ?>
                            <div class="angel-wallet-status">⚠️ Need more:</div>
                            <div class="angel-wallet-status"><?php echo $formatted_needed; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($balance >= $cart_total): ?>
                    <p style="color: #aed604; font-size: 14px; margin: 8px 0 0 0; opacity: 0.9; font-weight: 600;">
                        🎉 Your order will be paid instantly from your Angel Wallet balance!
                    </p>
                <?php endif; ?>
            </div>
            <?php
        }
        
        public function init_form_fields() {
            $this->form_fields = [
                'enabled' => [
                    'title' => 'Enable/Disable',
                    'type' => 'checkbox',
                    'label' => 'Enable Angel Wallet Payment',
                    'default' => 'yes'
                ],
                'title' => [
                    'title' => 'Title',
                    'type' => 'text',
                    'default' => '💸 Pay with Angel Wallet'
                ]
            ];
        }
        
        public function is_available() {
            if (!is_user_logged_in()) return false;
            
            $user_id = get_current_user_id();
            $balance = floatval(get_user_meta($user_id, 'angel_wallet_balance', true));
            $cart_total = floatval(WC()->cart->get_total('edit'));
            
            return ($balance >= $cart_total);
        }
        
        public function process_payment($order_id) {
            // Enhanced validation
            $order_id = absint($order_id);
            if (!$order_id) {
                wc_add_notice('Invalid order ID.', 'error');
                greenangel_log_security_event('Invalid order ID in payment', null, "Order ID: $order_id");
                return ['result' => 'fail'];
            }

            $order = wc_get_order($order_id);
            if (!$order) {
                wc_add_notice('Order not found.', 'error');
                greenangel_log_security_event('Order not found in payment', null, "Order ID: $order_id");
                return ['result' => 'fail'];
            }

            $user_id = $order->get_user_id();
            if (!$user_id || $user_id !== get_current_user_id()) {
                wc_add_notice('Unauthorized order access.', 'error');
                greenangel_log_security_event('Unauthorized order access', $user_id, "Order ID: $order_id");
                return ['result' => 'fail'];
            }

            // Rate limiting for payment attempts
            $payment_attempts_key = 'wallet_payment_attempts_' . $user_id;
            $attempts = get_transient($payment_attempts_key) ?: 0;
            if ($attempts >= 5) {
                wc_add_notice('Too many payment attempts. Please try again later.', 'error');
                greenangel_log_security_event('Payment rate limit exceeded', $user_id, "Attempts: $attempts");
                return ['result' => 'fail'];
            }
            set_transient($payment_attempts_key, $attempts + 1, 300);

            $balance = floatval(get_user_meta($user_id, 'angel_wallet_balance', true));
            $total = floatval($order->get_total());

            // Enhanced balance validation
            if ($total <= 0) {
                wc_add_notice('Invalid order total.', 'error');
                greenangel_log_security_event('Invalid order total', $user_id, "Total: $total");
                return ['result' => 'fail'];
            }

            if ($balance < $total) {
                wc_add_notice('Insufficient wallet balance.', 'error');
                return ['result' => 'fail'];
            }

            // Atomic transaction to prevent race conditions
            global $wpdb;
            $wpdb->query('START TRANSACTION');

            try {
                // Double-check balance hasn't changed
                $current_balance = floatval(get_user_meta($user_id, 'angel_wallet_balance', true));
                if ($current_balance < $total) {
                    throw new Exception('Balance changed during payment processing.');
                }

                // Save the real amount customer paid
                $order->update_meta_data('_angel_wallet_paid_total', $total);

                // Deduct wallet balance
                $new_balance = $current_balance - $total;
                update_user_meta($user_id, 'angel_wallet_balance', $new_balance);

                // Log transaction
                if (function_exists('greenangel_log_wallet_transaction')) {
                    greenangel_log_wallet_transaction($user_id, -$total, 'spend', $order_id, "Paid for order #$order_id");
                }

                // Set order total to £0 for clean records
                $order->set_total(0);
                $order->save();

                // Mark order as paid
                $order->payment_complete();

                $wpdb->query('COMMIT');

                // Clear rate limiting on successful payment
                delete_transient($payment_attempts_key);

                // Log successful payment
                greenangel_log_security_event('Successful wallet payment', $user_id, "Order: $order_id, Amount: $total");

                return [
                    'result' => 'success',
                    'redirect' => $this->get_return_url($order)
                ];

            } catch (Exception $e) {
                $wpdb->query('ROLLBACK');
                greenangel_log_security_event('Payment transaction failed', $user_id, $e->getMessage());
                wc_add_notice('Payment processing failed. Please try again.', 'error');
                return ['result' => 'fail'];
            }
        }
    }
}

/**
 * Security event logging
 */
function greenangel_log_security_event($event, $user_id = null, $details = '') {
    $user_id = $user_id ?: get_current_user_id();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 100);
    
    error_log(sprintf(
        'Angel Wallet Security: %s | User: %d | IP: %s | Details: %s | UA: %s',
        $event,
        $user_id,
        $ip,
        $details,
        $user_agent
    ));
}
?>