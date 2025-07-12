<?php
/**
 * 🌿 Green Angel - Address Edit Pages
 * Location: plugins/greenangel-hub/account/includes/address-edit.php
 */

// Remove default WooCommerce address handling
remove_action('woocommerce_account_edit-address_endpoint', 'woocommerce_account_edit_address', 10);

// Override the main addresses page (selection page)
add_action('woocommerce_account_edit-address_endpoint', 'greenangel_custom_addresses_page', 1);
function greenangel_custom_addresses_page($type) {
    // If no type specified, show address selection
    if (!$type) {
        $user_id = get_current_user_id();
        ?>
        <div class="greenangel-dashboard-wrapper">
            <div class="ga-panel">
                <h3 class="ga-panel-title">
                    <span class="ga-title-pill" style="background:#aed604;">📍 MY ADDRESSES</span>
                </h3>
                
                <div class="ga-address-cards">
                    <!-- Only Shipping Address Card -->
                    <div class="ga-address-card">
                        <h3>📦 Shipping Address</h3>
                        <address>
                            <?php
                            $shipping_address = WC()->countries->get_formatted_address([
                                'first_name' => get_user_meta($user_id, 'shipping_first_name', true),
                                'last_name'  => get_user_meta($user_id, 'shipping_last_name', true),
                                'company'    => get_user_meta($user_id, 'shipping_company', true),
                                'address_1'  => get_user_meta($user_id, 'shipping_address_1', true),
                                'address_2'  => get_user_meta($user_id, 'shipping_address_2', true),
                                'city'       => get_user_meta($user_id, 'shipping_city', true),
                                'state'      => get_user_meta($user_id, 'shipping_state', true),
                                'postcode'   => get_user_meta($user_id, 'shipping_postcode', true),
                                'country'    => get_user_meta($user_id, 'shipping_country', true),
                            ]);
                            
                            echo $shipping_address ? wp_kses_post($shipping_address) : 'No shipping address set.';
                            ?>
                        </address>
                        <a href="<?php echo esc_url(wc_get_endpoint_url('edit-address', 'shipping')); ?>" class="ga-edit-link">Edit</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return;
    }
    
    // For billing, redirect to shipping
    if ($type === 'billing') {
        wp_redirect(wc_get_endpoint_url('edit-address', 'shipping'));
        exit;
    }
    
    // Only handle shipping address
    if ($type !== 'shipping') {
        echo '<p class="greenangel-notice">Invalid address type.</p>';
        return;
    }
    
    // Get address fields
    $user_id = get_current_user_id();
    $address = WC()->countries->get_address_fields(get_user_meta($user_id, 'shipping_country', true), 'shipping_');
    
    // Remove company field - we don't need it!
    unset($address['shipping_company']);
    
    // Load current values
    foreach ($address as $key => $field) {
        $address[$key]['value'] = get_user_meta($user_id, $key, true);
    }
    
    ?>
    <div class="greenangel-dashboard-wrapper">
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-address')); ?>" class="ga-back-button">← Back to Addresses</a>
        
        <div class="ga-panel">
            <h3 class="ga-panel-title">
                <span class="ga-title-pill" style="background:#aed604;">📦 EDIT SHIPPING ADDRESS</span>
            </h3>
            
            <div class="ga-inner-panel">
                <form method="post" class="ga-address-form">
                    <?php 
                    // Manually output fields in the order we want
                    woocommerce_form_field('shipping_first_name', $address['shipping_first_name'], $address['shipping_first_name']['value']);
                    woocommerce_form_field('shipping_last_name', $address['shipping_last_name'], $address['shipping_last_name']['value']);
                    woocommerce_form_field('shipping_address_1', $address['shipping_address_1'], $address['shipping_address_1']['value']);
                    woocommerce_form_field('shipping_address_2', $address['shipping_address_2'], $address['shipping_address_2']['value']);
                    woocommerce_form_field('shipping_city', $address['shipping_city'], $address['shipping_city']['value']);
                    woocommerce_form_field('shipping_state', $address['shipping_state'], $address['shipping_state']['value']);
                    woocommerce_form_field('shipping_country', $address['shipping_country'], $address['shipping_country']['value']);
                    woocommerce_form_field('shipping_postcode', $address['shipping_postcode'], $address['shipping_postcode']['value']);
                    ?>
                    
                    <p>
                        <button type="submit" class="ga-address-submit" name="save_address" value="<?php esc_attr_e('Save address', 'woocommerce'); ?>">
                            <?php esc_html_e('Save address', 'woocommerce'); ?>
                        </button>
                        <?php wp_nonce_field('woocommerce-edit_address', 'woocommerce-edit-address-nonce'); ?>
                        <input type="hidden" name="action" value="edit_address" />
                    </p>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    // Hide any leftover WooCommerce titles
    document.addEventListener('DOMContentLoaded', function() {
        const titles = document.querySelectorAll('h2');
        titles.forEach(title => {
            if (title.textContent.trim() === 'Shipping address') {
                title.style.display = 'none';
            }
        });
    });
    </script>
    <?php
}