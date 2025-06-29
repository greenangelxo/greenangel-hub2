<?php
// 🌿 Green Angel – Enforce postcode rules at checkout

add_action('woocommerce_after_checkout_validation', 'greenangel_enforce_postcode_rules', 10, 2);
function greenangel_enforce_postcode_rules($fields, $errors) {
    $postcode = strtoupper(str_replace(' ', '', $fields['shipping_postcode']));
    $cart_total = WC()->cart->subtotal;
    
    global $wpdb;
    $table = $wpdb->prefix . 'greenangel_postcode_rules';
    $rules = $wpdb->get_results("SELECT * FROM $table WHERE active = 'yes'");
    
    foreach ($rules as $rule) {
        if (strpos($postcode, strtoupper($rule->postcode_prefix)) === 0) {
            if ($rule->type === 'block') {
                $errors->add('validation', $rule->message);
                return;
            }
            
            if ($rule->type === 'minimum_spend' && $cart_total < floatval($rule->value)) {
                $errors->add('validation', $rule->message);
                return;
            }
        }
    }
}