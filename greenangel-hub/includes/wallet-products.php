<?php
// Exit if accessed directly
defined('ABSPATH') || exit;

function greenangel_create_wallet_topup_products() {
    // Make sure WooCommerce is active
    if (!class_exists('WC_Product_Simple')) return;
    
    // Prevent duplicate creation
    if (get_option('greenangel_wallet_products_created')) return;

    // Create "top-up" category if missing
    $category_id = null;
    if (!term_exists('top-up', 'product_cat')) {
        $result = wp_insert_term('Top-Up', 'product_cat', [
            'slug' => 'top-up',
            'description' => 'Angel Wallet top-up products for seamless checkout'
        ]);
        $category_id = $result['term_id'];
    } else {
        $category_id = get_term_by('slug', 'top-up', 'product_cat')->term_id;
    }

    $topups = [
        50  => '£50 Top-Up',
        100 => '£100 Top-Up', 
        250 => '£250 Top-Up',
        500 => '£500 Top-Up',
    ];

    // Short description for all products
    $short_description = "Tired of stressing over crypto every time you check out? 💅 Babe, top up your Angel Wallet instead — it's the quickest, chillest way to treat yourself 💸 ✨ Just pay with crypto ONCE. No future wallet addresses, no BTC drama, no faff. Just top up once, spend it when you're ready, and stay in full control of your goodies 😍 💚";

    // Long description with your branding
    $long_description = <<<HTML
<p>Gorgeous, getting your goodies just got <em>a whole lot easier</em>. 🛍️✨</p>

<p>Say hello to the Angel Wallet — your magical balance that lives right inside your account. 💰<br>
No more last-minute crypto panics, wrong wallet addresses, or sweating over Bitcoin fees.<br>
Just <strong>top up once</strong> and glide through that checkout each time. 🌿✨</p>

<h3>✨ Why It's Iconic:</h3>

<ul>
    <li>✅ <strong>One-click checkout vibes</strong> — pay with your wallet instead of BTC or LTC every time</li>
    <li>✅ <strong>No more fluctuating prices</strong> — your top-up stays put, stable and ready to spend 🧃</li>
    <li>✅ <strong>Instant processing</strong> — wallet credits land the moment your order clears</li>
    <li>✅ <strong>Perfect for gifts, pre-orders, or stocking up for your next sesh</strong> 🔥🛍️</li>
    <li>✅ <strong>Earns you Halo Points</strong> too! (Because yes, queen, you still get rewarded 💚 💎)</li>
</ul>

<p>Whether you're a spontaneous soul or just want to <em>lock in the vibe and go</em>, Angel Wallet top-ups make your ordering smoother than a fresh gummy drop 💚 🌿</p>

<p>So go on — load up, glow up, and treat your future self 🌿✨<br>
<strong>Green Angel XO</strong></p>
HTML;

    foreach ($topups as $price => $title) {
        // Check if product already exists by title
        $existing = get_page_by_title($title, OBJECT, 'product');
        if ($existing) continue;

        // Create the product
        $product = new WC_Product_Simple();
        $product->set_name($title);
        $product->set_slug('wallet-top-up-' . $price);
        $product->set_regular_price($price);
        $product->set_price($price);
        $product->set_description($long_description);
        $product->set_short_description($short_description);
        $product->set_virtual(true);
        $product->set_downloadable(false);
        $product->set_catalog_visibility('visible');
        $product->set_status('publish');
        $product->set_reviews_allowed(false);
        $product->set_sold_individually(false);
        $product->set_manage_stock(false);
        $product->set_stock_status('instock');
        
        // Set category if we have one
        if ($category_id) {
            $product->set_category_ids([$category_id]);
        }

        // Add custom meta to identify these as wallet products
        $product->update_meta_data('_greenangel_wallet_product', 'yes');
        $product->update_meta_data('_greenangel_wallet_amount', $price);
        

        
        // Save the product
        $product_id = $product->save();
        
        // Log successful creation
        if ($product_id) {
            error_log("Green Angel: Created wallet top-up product: {$title} (ID: {$product_id})");
        }
    }
    
    // Mark as completed so we don't run this again
    update_option('greenangel_wallet_products_created', true);
    
    // Optional: Clear any caches
    if (function_exists('wc_delete_product_transients')) {
        wc_delete_product_transients();
    }
}

// Hook to handle wallet top-up purchases (add this too!)
add_action('woocommerce_order_status_completed', 'greenangel_process_wallet_topup');
function greenangel_process_wallet_topup($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) return;
    
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        if (!$product) continue;
        
        // Check if this is a wallet top-up product
        if ($product->get_meta('_greenangel_wallet_product') === 'yes') {
            $amount = $product->get_meta('_greenangel_wallet_amount');
            $customer_id = $order->get_customer_id();
            
            if ($amount && $customer_id) {
                // Add to wallet (you'll need to implement this function based on your wallet system)
                greenangel_add_wallet_credit($customer_id, $amount, "Wallet top-up from order #{$order_id}");
                
                // Add order note
                $order->add_order_note("Added £{$amount} to customer wallet (User ID: {$customer_id})");
            }
        }
    }
}

// Placeholder for wallet credit function (implement based on your system)
function greenangel_add_wallet_credit($user_id, $amount, $note = '') {
    // This needs to connect to your actual wallet system
    // Example implementation:
    /*
    $current_balance = get_user_meta($user_id, 'wallet_balance', true) ?: 0;
    $new_balance = $current_balance + $amount;
    update_user_meta($user_id, 'wallet_balance', $new_balance);
    
    // Log the transaction
    $transaction_data = [
        'user_id' => $user_id,
        'amount' => $amount,
        'type' => 'credit',
        'note' => $note,
        'date' => current_time('mysql')
    ];
    // Save to your transactions table
    */
}
?>