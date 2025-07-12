<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

/**
 * Auto-credit Angel Wallet balance when a top-up order is marked processing/completed
 */
add_action('woocommerce_order_status_processing', 'greenangel_handle_wallet_topup_order');
add_action('woocommerce_order_status_completed', 'greenangel_handle_wallet_topup_order');

function greenangel_handle_wallet_topup_order($order_id) {
    if (!$order_id) return;

    // Enhanced validation
    $order_id = absint($order_id);
    $order = wc_get_order($order_id);
    if (!$order || !$order->get_user_id()) return;

    // Prevent duplicate processing
    $processed_key = 'wallet_topup_processed_' . $order_id;
    if (get_transient($processed_key)) {
        return; // Already processed
    }

    $user_id = $order->get_user_id();
    
    // Validate user exists
    if (!get_userdata($user_id)) {
        error_log("Angel Wallet: Invalid user ID $user_id for order $order_id");
        return;
    }

    $items = $order->get_items();
    $total_credit = 0;
    $is_wallet_only = true;

    foreach ($items as $item) {
        $product = $item->get_product();
        if (!$product) continue;

        // Detect if the product belongs to the Top-Up category
        if (has_term('top-up', 'product_cat', $product->get_id())) {
            $item_total = floatval($item->get_total());
            
            // Validate reasonable top-up amounts
            if ($item_total <= 0 || $item_total > 10000) {
                error_log("Angel Wallet: Suspicious top-up amount $item_total for order $order_id");
                return;
            }
            
            $total_credit += $item_total;
        } else {
            $is_wallet_only = false;
            break;
        }
    }

    if (!$is_wallet_only || $total_credit <= 0) return;

    // Additional security checks
    $current_balance = greenangel_get_wallet_balance($user_id);
    $new_balance = $current_balance + $total_credit;
    
    // Prevent unrealistic balances
    if ($new_balance > 50000) {
        error_log("Angel Wallet: Balance would exceed maximum limit for user $user_id, order $order_id");
        return;
    }

    // Atomic transaction to prevent race conditions
    global $wpdb;
    $wpdb->query('START TRANSACTION');

    try {
        // Add to wallet using secure function
        if (!greenangel_add_to_wallet($user_id, $total_credit, "Wallet top-up via order #$order_id")) {
            throw new Exception("Failed to add wallet balance");
        }

        // Mark as completed if not already
        if ($order->get_status() !== 'completed') {
            $order->update_status('completed');
        }

        $wpdb->query('COMMIT');

        // Mark as processed to prevent duplicates
        set_transient($processed_key, true, DAY_IN_SECONDS);

        // Send confirmation email
        greenangel_send_wallet_credit_email($user_id, $total_credit, $order_id);

        // Log successful processing
        error_log("Angel Wallet: Successfully processed top-up of $total_credit for user $user_id, order $order_id");

    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        error_log('Angel Wallet top-up failed: ' . $e->getMessage());
    }
}

/**
 * Confirmation email to user when wallet is credited
 */
function greenangel_send_wallet_credit_email($user_id, $amount, $order_id) {
    // Validate inputs
    $user_id = absint($user_id);
    $amount = floatval($amount);
    $order_id = absint($order_id);
    
    if (!$user_id || $amount <= 0 || !$order_id) {
        return false;
    }
    
    $user = get_userdata($user_id);
    if (!$user) return false;

    // Sanitize user data
    $first_name = sanitize_text_field($user->first_name ?: 'Angel');
    $to = sanitize_email($user->user_email);
    
    if (!is_email($to)) {
        error_log("Angel Wallet: Invalid email for user $user_id");
        return false;
    }

    $subject = '💸 Your Angel Wallet Has Been Topped Up!';
    $message = sprintf(
        "<p>Hey %s,</p>
        <p>Thanks for your recent top-up! We've just added <strong>£%s</strong> to your Angel Wallet from order #%d.</p>
        <p>You can now spend your balance at checkout any time 💖</p>
        <p>To the firmament and back,<br>Green Angel XO</p>",
        esc_html($first_name),
        number_format($amount, 2),
        $order_id
    );
    
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    return wp_mail($to, $subject, $message, $headers);
}