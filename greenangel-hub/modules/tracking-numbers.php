<?php
// 🌿 Green Angel Hub – Tracking Numbers Module

// 🚚 TRACKING COLUMN TAKEOVER - Using same pattern as delivery date column!
// Add Tracking column to orders list (USING MODERN WOOCOMMERCE HOOK)
add_filter('woocommerce_shop_order_list_table_columns', function($columns) {
    // Insert after order_total column (or wherever you want it)
    $new = [];
    foreach ($columns as $key => $value) {
        $new[$key] = $value;
        if ($key === 'order_total') {
            $new['ga_tracking'] = '📦 Tracking';
        }
    }
    return $new;
});

// Display tracking info in column (USING MODERN WOOCOMMERCE HOOK)
add_action('woocommerce_shop_order_list_table_custom_column', function($column, $order) {
    if ($column === 'ga_tracking') {
        $order_id = $order->get_id();
        
        // Check ALL possible locations for tracking
        $ga_tracking = $order->get_meta('_greenangel_tracking_number');
        if (!$ga_tracking) $ga_tracking = $order->get_meta('greenangel_tracking_number');
        if (!$ga_tracking) $ga_tracking = get_post_meta($order_id, '_greenangel_tracking_number', true);
        if (!$ga_tracking) $ga_tracking = get_post_meta($order_id, 'greenangel_tracking_number', true);
        
        // Also check AST tracking
        $ast_tracking_items = $order->get_meta('_wc_shipment_tracking_items');
        
        // For completed orders without tracking found, show what we checked
        if ($order->get_status() === 'completed' && !$ga_tracking && empty($ast_tracking_items)) {
            echo '<small style="color: #666;">No tracking found</small>';
            return;
        }
        
        // Display AST tracking if found
        if (!empty($ast_tracking_items) && is_array($ast_tracking_items) && !$ga_tracking) {
            try {
                $first_item = reset($ast_tracking_items);
                if (!empty($first_item['tracking_number'])) {
                    $tracking_number = $first_item['tracking_number'];
                    $date_shipped = isset($first_item['date_shipped']) ? date('j M', $first_item['date_shipped']) : '';
                    
                    echo '<div style="font-size: 13px;">';
                    echo '<a href="https://www.royalmail.com/track-your-item?trackNumber=' . esc_attr($tracking_number) . '" ';
                    echo 'target="_blank" style="color: #2271b1; text-decoration: none; font-weight: 600;">';
                    echo esc_html($tracking_number);
                    echo '</a>';
                    if ($date_shipped) {
                        echo '<div style="color: #666; font-size: 11px; margin-top: 2px;">🚚 Shipped ' . esc_html($date_shipped) . '</div>';
                    }
                    echo '</div>';
                    return;
                }
            } catch (Exception $e) {
                // Continue
            }
        }
        
        // Display Green Angel tracking if found
        if ($ga_tracking) {
            $tracking_date = $order->get_meta('_greenangel_tracking_completed_date');
            if (!$tracking_date) $tracking_date = $order->get_meta('greenangel_tracking_completed_date');
            if (!$tracking_date) $tracking_date = get_post_meta($order_id, '_greenangel_tracking_completed_date', true);
            if (!$tracking_date) $tracking_date = get_post_meta($order_id, 'greenangel_tracking_completed_date', true);
            
            $date_str = $tracking_date ? date('j M', strtotime($tracking_date)) : date('j M');
            
            echo '<div style="font-size: 13px;">';
            echo '<a href="https://www.royalmail.com/track-your-item?trackNumber=' . esc_attr($ga_tracking) . '" ';
            echo 'target="_blank" style="color: #2271b1; text-decoration: none; font-weight: 600;">';
            echo esc_html($ga_tracking);
            echo '</a>';
            echo '<div style="color: #666; font-size: 11px; margin-top: 2px;">🚚 Shipped ' . esc_html($date_str) . '</div>';
            echo '</div>';
        } else {
            echo '<span style="color: #999;">—</span>';
        }
    }
}, 10, 2);

// Enhanced process function that also updates old plugin format
function greenangel_process_tracking_submission_enhanced($order_id, $tracking_number) {
    $order = wc_get_order($order_id);
    if (!$order) return;
    
    // Save tracking number - USE BOTH update_post_meta AND order meta
    update_post_meta($order_id, '_greenangel_tracking_number', $tracking_number);
    update_post_meta($order_id, 'greenangel_tracking_number', $tracking_number); // Without underscore too
    
    // ALSO save using order object method
    $order->update_meta_data('_greenangel_tracking_number', $tracking_number);
    $order->update_meta_data('greenangel_tracking_number', $tracking_number);
    $order->save(); // CRITICAL - must save!
    
    // Save completion date
    update_post_meta($order_id, '_greenangel_tracking_completed_date', current_time('Y-m-d'));
    $order->update_meta_data('_greenangel_tracking_completed_date', current_time('Y-m-d'));
    $order->save();
    
    // Also update old plugin's format for compatibility
    update_post_meta($order_id, '_wcast_tracking_number', $tracking_number);
    
    // Create the HTML format the old plugin uses
    $date_str = date('j M');
    $html = sprintf(
        '<div class="tracking_meta_date">
            <a href="https://www.royalmail.com/track-your-item?trackNumber=%s" target="_blank" style="color: #2271b1;">%s</a>
            <div style="font-size: 12px; color: #666; margin-top: 2px;">Royal Mail<br>Shipped %s</div>
        </div>',
        esc_attr($tracking_number),
        esc_html($tracking_number),
        esc_html($date_str)
    );
    update_post_meta($order_id, 'tracking_meta_date', $html);
    
    // Update order status to completed
    $order->update_status('completed', 'Tracking number added: ' . $tracking_number);
}
// END OF TRACKING COLUMN TAKEOVER CODE

// 🔁 Save Individual Tracking Number
add_action('admin_post_greenangel_save_tracking', 'greenangel_save_tracking');
function greenangel_save_tracking() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Permission denied');
    }
    check_admin_referer('greenangel_save_tracking', 'greenangel_nonce');

    if (isset($_POST['order_id']) && isset($_POST['tracking_number'])) {
        $order_id = intval($_POST['order_id']);
        $tracking_number = sanitize_text_field($_POST['tracking_number']);
        
        if (!empty($tracking_number)) {
            greenangel_process_tracking_submission_enhanced($order_id, $tracking_number);
        }
    }

    wp_redirect(admin_url('admin.php?page=greenangel-hub&tab=tracking-numbers'));
    exit;
}

// 🔁 Bulk Save Tracking Numbers
add_action('admin_post_greenangel_bulk_save_tracking', 'greenangel_bulk_save_tracking');
function greenangel_bulk_save_tracking() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Permission denied');
    }
    check_admin_referer('greenangel_bulk_save_tracking', 'greenangel_bulk_nonce');

    if (isset($_POST['tracking_numbers']) && is_array($_POST['tracking_numbers'])) {
        foreach ($_POST['tracking_numbers'] as $order_id => $tracking_number) {
            $order_id = intval($order_id);
            $tracking_number = sanitize_text_field($tracking_number);
            
            if (!empty($tracking_number)) {
                greenangel_process_tracking_submission_enhanced($order_id, $tracking_number);
            }
        }
    }

    wp_redirect(admin_url('admin.php?page=greenangel-hub&tab=tracking-numbers'));
    exit;
}

// 🧠 Process Tracking Submission (Original - kept for compatibility)
function greenangel_process_tracking_submission($order_id, $tracking_number) {
    greenangel_process_tracking_submission_enhanced($order_id, $tracking_number);
}

// 📧 Inject Tracking into Emails
add_filter('woocommerce_email_order_meta_fields', 'greenangel_add_tracking_to_email', 10, 3);
function greenangel_add_tracking_to_email($fields, $sent_to_admin, $order) {
    $tracking = get_post_meta($order->get_id(), '_greenangel_tracking_number', true);
    
    if (!empty($tracking)) {
        $fields['tracking'] = array(
            'label' => __(''),
            'value' => sprintf(
                '<div style="background-color: #f0f0f0; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center;">
                    <p style="margin: 0 0 10px 0; font-size: 16px; color: #333;">Your parcel has shipped! 💌</p>
                    <p style="margin: 0 0 10px 0; font-size: 18px; font-weight: bold; color: #333;">Tracking Number: %s</p>
                    <p style="margin: 0;"><a href="https://www.royalmail.com/track-your-item#/tracking-results/%s" style="background-color: #aed604; color: #222; padding: 10px 25px; text-decoration: none; border-radius: 25px; display: inline-block; font-weight: bold;">Track Your Parcel</a></p>
                </div>',
                esc_html($tracking),
                esc_html($tracking)
            )
        );
    }
    
    return $fields;
}

// 💳 Main Renderer
function greenangel_render_tracking_numbers() {
    ?>
    
    <script>
        // Wait for DOM to be ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Tracking Numbers module loaded!');
            
            // Global variables
            window.autoFlowOrders = [];
            window.currentAutoIndex = 0;
            
            // Make functions globally accessible
            window.startAutoFlow = function() {
                try {
                    console.log('Starting auto flow...');
                    
                    // Get all order data from cards
                    const cards = document.querySelectorAll('.tracking-card');
                    console.log('Found cards:', cards.length);
                    
                    window.autoFlowOrders = [];
                    
                    cards.forEach(card => {
                        const orderId = card.dataset.orderId;
                        const customerNameEl = card.querySelector('.customer-name');
                        const postcodeEl = card.querySelector('.postcode-display');
                        const totalEl = card.querySelector('.total-value');
                        const input = card.querySelector('.tracking-input');
                        
                        if (!customerNameEl || !postcodeEl || !totalEl) {
                            console.warn('Missing elements in card:', orderId);
                            return;
                        }
                        
                        const customerName = customerNameEl.textContent;
                        const postcode = postcodeEl.textContent;
                        const total = totalEl.textContent;
                        
                        console.log('Processing card:', {orderId, customerName, postcode, total});
                        
                        if (input && !input.value.trim()) {
                            window.autoFlowOrders.push({
                                orderId,
                                customerName,
                                postcode,
                                total,
                                element: card
                            });
                        }
                    });
                    
                    console.log('Orders to process:', window.autoFlowOrders.length);
                    
                    if (window.autoFlowOrders.length === 0) {
                        alert('All orders already have tracking numbers!');
                        return;
                    }
                    
                    window.currentAutoIndex = 0;
                    showAutoFlowModal();
                    
                } catch (error) {
                    console.error('Error in startAutoFlow:', error);
                    alert('Oops! Something went wrong. Check the console for details.');
                }
            };
            
            window.showAutoFlowModal = function() {
                const modal = document.getElementById('autoFlowModal');
                if (!modal) {
                    console.error('Modal element not found!');
                    return;
                }
                modal.classList.add('active');
                updateAutoFlowDisplay();
                
                // Focus the input after a tiny delay
                setTimeout(() => {
                    const input = document.getElementById('autoFlowInput');
                    if (input) input.focus();
                }, 100);
            };
            
            window.closeAutoFlow = function() {
                const modal = document.getElementById('autoFlowModal');
                if (modal) modal.classList.remove('active');
            };
            
            window.updateAutoFlowDisplay = function() {
                if (window.currentAutoIndex >= window.autoFlowOrders.length) {
                    showAutoFlowComplete();
                    return;
                }
                
                const order = window.autoFlowOrders[window.currentAutoIndex];
                document.getElementById('autoFlowProgress').textContent = 
                    `Order ${window.currentAutoIndex + 1} of ${window.autoFlowOrders.length}`;
                document.getElementById('autoFlowPostcode').textContent = order.postcode;
                document.getElementById('autoFlowOrderId').textContent = `#${order.orderId}`;
                document.getElementById('autoFlowCustomer').textContent = order.customerName;
                document.getElementById('autoFlowTotal').textContent = order.total;
                document.getElementById('autoFlowInput').value = '';
                document.getElementById('autoFlowNext').disabled = true;
                document.getElementById('autoFlowInput').focus();
            };
            
            window.handleAutoFlowInput = function(e) {
                const value = e.target.value.trim();
                const nextBtn = document.getElementById('autoFlowNext');
                if (nextBtn) nextBtn.disabled = !value;
                
                // Auto-proceed on Enter key
                if (e.key === 'Enter' && value) {
                    nextAutoFlow();
                }
            };
            
            window.nextAutoFlow = function() {
                const trackingNumber = document.getElementById('autoFlowInput').value.trim();
                
                if (trackingNumber) {
                    // Fill in the tracking number in the card
                    const order = window.autoFlowOrders[window.currentAutoIndex];
                    const input = order.element.querySelector('.tracking-input');
                    if (input) {
                        input.value = trackingNumber;
                        
                        // Highlight the card briefly
                        order.element.style.borderColor = '#aed604';
                        setTimeout(() => {
                            order.element.style.borderColor = '';
                        }, 500);
                    }
                }
                
                window.currentAutoIndex++;
                updateAutoFlowDisplay();
            };
            
            window.skipAutoFlow = function() {
                window.currentAutoIndex++;
                updateAutoFlowDisplay();
            };
            
            window.showAutoFlowComplete = function() {
                const orderDiv = document.getElementById('autoFlowOrder');
                const completeDiv = document.getElementById('autoFlowComplete');
                if (orderDiv) orderDiv.style.display = 'none';
                if (completeDiv) completeDiv.style.display = 'block';
            };
            
            window.finishAutoFlow = function() {
                closeAutoFlow();
                // Optionally submit all tracking numbers
                if (confirm('Submit all tracking numbers now?')) {
                    const form = document.getElementById('bulk-tracking-form');
                    if (form) form.submit();
                }
            };
            
            window.sendTracking = function(orderId) {
                console.log('sendTracking called for order:', orderId);
                
                // Find the card
                const card = document.querySelector(`.tracking-card[data-order-id="${orderId}"]`);
                
                if (!card) {
                    console.error('Could not find card for order:', orderId);
                    return;
                }
                
                const input = card.querySelector('.tracking-input');
                
                if (!input || !input.value.trim()) {
                    if (input) input.focus();
                    return;
                }
                
                console.log('Tracking number:', input.value);
                
                // Get the hidden form and submit it
                const hiddenForm = document.getElementById(`individual-form-${orderId}`);
                if (!hiddenForm) {
                    console.error('Hidden form not found for order:', orderId);
                    alert('Error: Hidden form not found for order #' + orderId);
                    return;
                }
                
                const hiddenInput = hiddenForm.querySelector('.hidden-tracking-input');
                if (!hiddenInput) {
                    console.error('Hidden input not found in form');
                    alert('Error: Hidden input not found');
                    return;
                }
                
                // Copy value to hidden form
                hiddenInput.value = input.value;
                
                // Add sending state
                card.classList.add('sending');
                
                console.log('About to submit form with tracking:', hiddenInput.value);
                
                // Submit the hidden form
                hiddenForm.submit();
            };
            
            window.validateBulkSend = function() {
                const inputs = document.querySelectorAll('.tracking-input');
                let hasValues = false;
                
                inputs.forEach(input => {
                    if (input.value.trim()) {
                        hasValues = true;
                    }
                });
                
                if (!hasValues) {
                    alert('Please enter at least one tracking number before bulk sending.');
                    return false;
                }
                
                return confirm('Send all entered tracking numbers? This will mark orders as completed and send emails.');
            };
            
            window.toggleCompleted = function() {
                const section = document.getElementById('completedSection');
                if (section) section.classList.toggle('collapsed');
            };
            
            // Auto-uppercase tracking inputs
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('tracking-input') || e.target.id === 'autoFlowInput') {
                    e.target.value = e.target.value.toUpperCase();
                }
            });
            
            // Enter key to send
            document.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && e.target.classList.contains('tracking-input')) {
                    e.preventDefault();
                    const card = e.target.closest('[data-order-id]');
                    if (card) {
                        const orderId = card.dataset.orderId;
                        sendTracking(orderId);
                    }
                }
            });
            
            // Keyup for auto flow input
            document.addEventListener('keyup', function(e) {
                if (e.target.id === 'autoFlowInput') {
                    handleAutoFlowInput(e);
                }
            });
        });
    </script>
    
    <div class="tracking-header">
        <h2 class="tracking-title">📮 Tracking Numbers</h2>
        <p class="tracking-subtitle">Add Royal Mail tracking for Ship Today orders</p>
        <button type="button" class="auto-flow-btn" onclick="startAutoFlow()">
            ✨ Auto Mode
        </button>
    </div>

    <style>
        /* Auto Flow Button */
        .auto-flow-btn {
            background: #aed604;
            color: #222;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'Poppins', sans-serif;
            margin-top: 20px;
            box-shadow: 0 4px 12px rgba(174, 214, 4, 0.3);
        }
        
        .auto-flow-btn:hover {
            background: #9bc603;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(174, 214, 4, 0.4);
        }
        
        /* Auto Flow Modal */
        .auto-flow-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 999999 !important;
            padding: 0;
            overflow-y: auto;
        }
        
        .auto-flow-modal.active {
            display: flex !important;
        }
        
        .auto-flow-content {
            background: #1a1a1a;
            border: 3px solid #aed604;
            border-radius: 20px;
            padding: 30px 20px;
            max-width: 500px;
            width: calc(100% - 30px);
            max-height: calc(100vh - 40px);
            overflow-y: auto;
            text-align: center;
            position: relative;
            animation: slideIn 0.3s ease;
            margin: 20px auto;
        }
        
        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .auto-flow-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #333;
            border: none;
            color: #fff;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .auto-flow-close:hover {
            background: #444;
            transform: rotate(90deg);
        }
        
        .auto-flow-header {
            margin-bottom: 25px;
        }
        
        .auto-flow-title {
            color: #aed604;
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 8px 0;
        }
        
        .auto-flow-progress {
            color: #888;
            font-size: 14px;
            margin: 0;
        }
        
        .auto-flow-order {
            background: #222;
            border: 2px solid #333;
            border-radius: 16px;
            padding: 25px 20px;
            margin-bottom: 20px;
        }
        
        .auto-flow-postcode {
            font-size: 40px;
            font-weight: 700;
            color: #aed604;
            margin: 0 0 15px 0;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        .auto-flow-details {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
            gap: 10px;
        }
        
        .auto-flow-detail {
            text-align: center;
            flex: 1;
        }
        
        .auto-flow-label {
            color: #666;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            display: block;
        }
        
        .auto-flow-value {
            color: #fff;
            font-size: 15px;
            font-weight: 600;
        }
        
        .auto-flow-value.price {
            color: #aed604;
        }
        
        .auto-flow-input {
            width: 100%;
            background: #0a0a0a;
            color: #fff;
            border: 3px solid #333;
            border-radius: 12px;
            padding: 16px 18px;
            font-family: monospace;
            font-size: 16px;
            text-align: center;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            box-sizing: border-box;
        }
        
        .auto-flow-input:focus {
            border-color: #aed604;
            outline: none;
            background: #111;
        }
        
        .auto-flow-input::placeholder {
            color: #555;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            letter-spacing: 0;
        }
        
        .auto-flow-actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }
        
        .auto-flow-skip {
            flex: 1;
            background: #333;
            color: #888;
            border: none;
            padding: 12px 16px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .auto-flow-skip:hover {
            background: #444;
            color: #aaa;
        }
        
        .auto-flow-next {
            flex: 2;
            background: #aed604;
            color: #222;
            border: none;
            padding: 14px 24px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 4px 12px rgba(174, 214, 4, 0.3);
        }
        
        .auto-flow-next:hover {
            background: #9bc603;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(174, 214, 4, 0.4);
        }
        
        .auto-flow-next:disabled {
            background: #555;
            color: #888;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .auto-flow-complete {
            text-align: center;
            padding: 30px 20px;
        }
        
        .auto-flow-complete-icon {
            font-size: 56px;
            margin-bottom: 18px;
        }
        
        .auto-flow-complete-title {
            color: #aed604;
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 12px 0;
        }
        
        .auto-flow-complete-text {
            color: #888;
            font-size: 15px;
            margin: 0 0 25px 0;
        }
        
        .auto-flow-done-btn {
            background: #aed604;
            color: #222;
            border: none;
            padding: 14px 35px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .auto-flow-done-btn:hover {
            background: #9bc603;
            transform: translateY(-2px);
        }
        
        /* Desktop adjustments */
        @media (min-width: 481px) {
            .auto-flow-modal {
                padding: 20px;
            }
            
            .auto-flow-content {
                padding: 40px 30px;
            }
            
            .auto-flow-close {
                top: 20px;
                right: 20px;
                width: 40px;
                height: 40px;
                font-size: 20px;
            }
            
            .auto-flow-header {
                margin-bottom: 30px;
            }
            
            .auto-flow-title {
                font-size: 24px;
                margin-bottom: 10px;
            }
            
            .auto-flow-order {
                padding: 30px;
                margin-bottom: 25px;
            }
            
            .auto-flow-postcode {
                font-size: 48px;
                margin-bottom: 20px;
            }
            
            .auto-flow-details {
                margin-bottom: 25px;
                padding-bottom: 25px;
            }
            
            .auto-flow-label {
                font-size: 12px;
                letter-spacing: 1px;
                margin-bottom: 5px;
            }
            
            .auto-flow-value {
                font-size: 18px;
            }
            
            .auto-flow-input {
                padding: 18px 20px;
                font-size: 18px;
            }
            
            .auto-flow-input::placeholder {
                font-size: 14px;
            }
            
            .auto-flow-actions {
                gap: 15px;
                margin-top: 25px;
            }
            
            .auto-flow-skip {
                padding: 14px 20px;
            }
            
            .auto-flow-next {
                padding: 16px 30px;
                font-size: 16px;
            }
            
            .auto-flow-complete {
                padding: 40px;
            }
            
            .auto-flow-complete-icon {
                font-size: 64px;
                margin-bottom: 20px;
            }
            
            .auto-flow-complete-title {
                font-size: 28px;
                margin-bottom: 15px;
            }
            
            .auto-flow-complete-text {
                font-size: 16px;
                margin-bottom: 30px;
            }
            
            .auto-flow-done-btn {
                padding: 16px 40px;
                font-size: 16px;
            }
        }
        
        /* Tracking Numbers - Angel Hub Dark Theme */
        .tracking-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .tracking-title {
            font-size: 24px;
            font-weight: 600;
            color: #aed604;
            margin: 0 0 8px 0;
        }
        
        .tracking-subtitle {
            font-size: 14px;
            color: #888;
            margin: 0;
        }
        
        /* Mobile Cards View - Now used for ALL screen sizes! */
        .tracking-cards-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        
        /* Tablet: 2 cards per row */
        @media (min-width: 768px) {
            .tracking-cards-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        /* Desktop: 3 cards per row */
        @media (min-width: 1200px) {
            .tracking-cards-container {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        /* Wide desktop: 4 cards per row */
        @media (min-width: 1600px) {
            .tracking-cards-container {
                grid-template-columns: repeat(4, 1fr);
                gap: 25px;
            }
        }
        
        .tracking-card {
            background: #1a1a1a;
            border: 2px solid #333;
            border-radius: 14px;
            overflow: hidden;
            transition: all 0.3s ease;
            opacity: 1;
        }
        
        .tracking-card.sending {
            opacity: 0.5;
            transform: scale(0.98);
        }
        
        .tracking-card.sent {
            animation: fadeOutCard 0.5s ease forwards;
        }
        
        @keyframes fadeOutCard {
            to {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }
        }
        
        .tracking-card:hover {
            border-color: #444;
        }
        
        .tracking-card-header {
            background: #222;
            padding: 12px 18px;
            border-bottom: 2px solid #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-badge {
            background: rgba(174, 214, 4, 0.2);
            color: #aed604;
            padding: 5px 10px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
        }
        
        .customer-name {
            font-weight: 500;
            color: #999;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 150px;
        }
        
        .tracking-card-body {
            padding: 20px;
        }
        
        /* Postcode section */
        .postcode-section {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #0a0a0a;
            border-radius: 12px;
            border: 2px solid #333;
        }
        
        .postcode-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
            display: block;
        }
        
        .postcode-display {
            font-size: 28px;
            font-weight: 700;
            color: #aed604;
            letter-spacing: 2px;
            text-transform: uppercase;
            line-height: 1;
        }
        
        /* Smaller total price */
        .total-section {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #333;
        }
        
        .total-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            display: block;
        }
        
        .total-value {
            font-size: 16px;
            color: #aed604;
            font-weight: 600;
        }
        
        /* Tracking Input Section */
        .tracking-input-section {
            margin-top: 15px;
        }
        
        .tracking-input-wrapper {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .tracking-input {
            width: 100%;
            background: #0a0a0a;
            color: #fff;
            border: 2px solid #333;
            border-radius: 10px;
            padding: 12px 16px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .tracking-input:focus {
            border-color: #aed604;
            outline: none;
        }
        
        .tracking-input::placeholder {
            color: #666;
        }
        
        .action-row {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        
        .send-btn {
            background: #aed604;
            color: #222;
            border: none;
            padding: 12px 32px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'Poppins', sans-serif;
            white-space: nowrap;
            width: 100%;
        }
        
        .send-btn:hover {
            background: #9bc603;
            transform: translateY(-2px);
        }
        
        .send-btn:active {
            transform: translateY(0);
        }
        
        /* Desktop-specific card adjustments */
        @media (min-width: 1024px) {
            .customer-name {
                max-width: 200px;
            }
            
            .tracking-card-body {
                padding: 22px;
            }
            
            .postcode-display {
                font-size: 32px;
            }
        }
        
        /* Bulk Send Section - FIXED SPACING! */
        .bulk-send-section {
            background: #222;
            border: 2px solid #333;
            border-radius: 14px;
            padding: 30px;
            margin-top: 30px;
            text-align: center;
        }
        
        .bulk-send-btn {
            background: #aed604;
            color: #222;
            border: none;
            padding: 16px 40px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 4px 12px rgba(174, 214, 4, 0.3);
        }
        
        .bulk-send-btn:hover {
            background: #9bc603;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(174, 214, 4, 0.4);
        }
        
        .bulk-send-btn:active {
            transform: translateY(0);
        }
        
        /* Completed Today Section - FIXED SPACING! */
        .completed-section {
            margin-top: 30px;
            background: #1a1a1a;
            border: 2px solid #333;
            border-radius: 14px;
            overflow: hidden;
        }
        
        .completed-header {
            background: #222;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
            user-select: none;
        }
        
        .completed-header:hover {
            background: #282828;
        }
        
        .completed-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .completed-toggle {
            display: inline-block;
            transition: transform 0.3s ease;
            font-size: 20px;
            color: #aed604;
        }
        
        .completed-section.collapsed .completed-toggle {
            transform: rotate(-90deg);
        }
        
        .completed-title {
            color: #aed604;
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }
        
        .completed-count {
            background: rgba(174, 214, 4, 0.2);
            color: #aed604;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .completed-content {
            max-height: 500px;
            overflow: hidden;
            transition: max-height 0.3s ease;
            padding: 20px;
        }
        
        .completed-section.collapsed .completed-content {
            max-height: 0;
            padding: 0 20px;
        }
        
        .completed-cards {
            display: grid;
            gap: 12px;
        }
        
        .completed-card {
            background: #222;
            border: 1px solid #333;
            border-radius: 10px;
            padding: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s ease;
            animation: slideInComplete 0.5s ease;
        }
        
        @keyframes slideInComplete {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .completed-card:hover {
            background: #282828;
            border-color: #444;
        }
        
        .completed-info {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .completed-badge {
            background: rgba(174, 214, 4, 0.2);
            color: #aed604;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .completed-customer {
            color: #fff;
            font-weight: 500;
        }
        
        .completed-tracking {
            color: #aed604;
            font-family: monospace;
            font-size: 13px;
            font-weight: 600;
            background: #0a0a0a;
            padding: 6px 12px;
            border-radius: 8px;
            border: 1px solid #333;
        }
        
        /* Desktop: Grid layout for completed cards */
        @media (min-width: 768px) {
            .completed-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (min-width: 1200px) {
            .completed-cards {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #1a1a1a;
            border: 2px solid #333;
            border-radius: 14px;
            margin-top: 20px;
        }
        
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .empty-state-title {
            color: #fff;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .empty-state-text {
            color: #666;
            font-size: 14px;
        }
        
        /* Success Animation */
        @keyframes successPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .success-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #27ae60;
            color: #fff;
            padding: 16px 24px;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.4);
            animation: successPulse 0.5s ease;
            z-index: 9999;
        }
    </style>

    <!-- Auto Flow Modal -->
    <div id="autoFlowModal" class="auto-flow-modal">
        <div class="auto-flow-content">
            <button class="auto-flow-close" onclick="closeAutoFlow()">×</button>
            
            <div id="autoFlowOrder">
                <div class="auto-flow-header">
                    <h3 class="auto-flow-title">✨ Auto Mode</h3>
                    <p class="auto-flow-progress" id="autoFlowProgress">Order 1 of 5</p>
                </div>
                
                <div class="auto-flow-order">
                    <h2 class="auto-flow-postcode" id="autoFlowPostcode">W1T 3DL</h2>
                    
                    <div class="auto-flow-details">
                        <div class="auto-flow-detail">
                            <span class="auto-flow-label">Order</span>
                            <span class="auto-flow-value" id="autoFlowOrderId">#1234</span>
                        </div>
                        <div class="auto-flow-detail">
                            <span class="auto-flow-label">Customer</span>
                            <span class="auto-flow-value" id="autoFlowCustomer">Name</span>
                        </div>
                        <div class="auto-flow-detail">
                            <span class="auto-flow-label">Total</span>
                            <span class="auto-flow-value price" id="autoFlowTotal">£0.00</span>
                        </div>
                    </div>
                    
                    <input type="text" 
                           id="autoFlowInput"
                           class="auto-flow-input" 
                           placeholder="Paste tracking number here..."
                           maxlength="13"
                           onkeyup="handleAutoFlowInput(event)"
                           oninput="handleAutoFlowInput(event)">
                </div>
                
                <div class="auto-flow-actions">
                    <button class="auto-flow-skip" onclick="skipAutoFlow()">Skip</button>
                    <button class="auto-flow-next" id="autoFlowNext" onclick="nextAutoFlow()" disabled>
                        Next →
                    </button>
                </div>
            </div>
            
            <div id="autoFlowComplete" class="auto-flow-complete" style="display: none;">
                <div class="auto-flow-complete-icon">🎉</div>
                <h3 class="auto-flow-complete-title">All Done!</h3>
                <p class="auto-flow-complete-text">
                    All tracking numbers have been filled in. Ready to send them all?
                </p>
                <button class="auto-flow-done-btn" onclick="finishAutoFlow()">
                    Send All Tracking
                </button>
            </div>
        </div>
    </div>
    
    <?php
    // Fetch Ship Today orders
    $ship_today_orders = wc_get_orders([
        'status'  => 'ship-today',
        'limit'   => -1,
        'orderby' => 'date',
        'order'   => 'DESC',
    ]);
    
    // Fetch today's completed orders using our tracking meta
    $today = current_time('Y-m-d');
    $completed_today = wc_get_orders([
        'limit' => -1,
        'orderby' => 'date_completed',
        'order' => 'DESC',
        'meta_query' => [
            [
                'key' => '_greenangel_tracking_completed_date',
                'value' => $today,
                'compare' => '='
            ],
        ],
    ]);
    
    if (!empty($ship_today_orders)) : ?>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="bulk-tracking-form" onsubmit="return validateBulkSend();">
            <input type="hidden" name="action" value="greenangel_bulk_save_tracking">
            <?php wp_nonce_field('greenangel_bulk_save_tracking', 'greenangel_bulk_nonce'); ?>
            
            <div class="tracking-cards-container">
                <?php foreach ($ship_today_orders as $order) : 
                    $order_id = $order->get_id();
                    $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                    $postcode = $order->get_billing_postcode();
                    $total = $order->get_formatted_order_total();
                    $existing_tracking = get_post_meta($order_id, '_greenangel_tracking_number', true);
                ?>
                    <div class="tracking-card" data-order-id="<?php echo $order_id; ?>">
                        <div class="tracking-card-header">
                            <span class="order-badge">#<?php echo $order_id; ?></span>
                            <span class="customer-name"><?php echo esc_html($customer_name); ?></span>
                        </div>
                        
                        <div class="tracking-card-body">
                            <!-- BIG BEAUTIFUL POSTCODE! -->
                            <div class="postcode-section">
                                <span class="postcode-label">📍 Postcode</span>
                                <div class="postcode-display"><?php echo esc_html($postcode); ?></div>
                            </div>
                            
                            <!-- Smaller total -->
                            <div class="total-section">
                                <span class="total-label">Total</span>
                                <div class="total-value"><?php echo $total; ?></div>
                            </div>
                            
                            <div class="tracking-input-section">
                                <div class="tracking-input-wrapper">
                                    <input type="text" 
                                           name="tracking_numbers[<?php echo $order_id; ?>]" 
                                           class="tracking-input" 
                                           placeholder="Enter tracking number"
                                           value="<?php echo esc_attr($existing_tracking); ?>"
                                           maxlength="13"
                                           style="text-transform: uppercase;">
                                    
                                    <div class="action-row">
                                        <button type="button" class="send-btn" onclick="sendTracking(<?php echo $order_id; ?>)">
                                            Send 📮
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="bulk-send-section">
                <button type="submit" class="bulk-send-btn">
                    🚀 Send All Tracking Numbers
                </button>
            </div>
        </form>
        
        <!-- Hidden Individual Forms -->
        <?php foreach ($ship_today_orders as $order) : 
            $order_id = $order->get_id();
        ?>
            <form id="individual-form-<?php echo $order_id; ?>" method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: none;">
                <input type="hidden" name="action" value="greenangel_save_tracking">
                <?php wp_nonce_field('greenangel_save_tracking', 'greenangel_nonce'); ?>
                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                <input type="hidden" name="tracking_number" class="hidden-tracking-input" value="">
            </form>
        <?php endforeach; ?>
        
    <?php else : ?>
        <div class="empty-state">
            <div class="empty-state-icon">📦</div>
            <h3 class="empty-state-title">No Ship Today Orders</h3>
            <p class="empty-state-text">All caught up! Orders will appear here when marked as Ship Today.</p>
        </div>
    <?php endif; ?>
    
    <?php
    // Completed Today Section - NOW AT THE BOTTOM WITH FIXED SPACING!
    if (!empty($completed_today)) {
        ?>
        <div class="completed-section collapsed" id="completedSection">
            <div class="completed-header" onclick="toggleCompleted()">
                <div class="completed-header-left">
                    <span class="completed-toggle">▼</span>
                    <h3 class="completed-title">✅ Completed Today</h3>
                </div>
                <span class="completed-count"><?php echo count($completed_today); ?> orders</span>
            </div>
            
            <div class="completed-content">
                <div class="completed-cards">
                    <?php foreach ($completed_today as $order): 
                        $order_id = $order->get_id();
                        $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                        $tracking = get_post_meta($order_id, '_greenangel_tracking_number', true);
                    ?>
                        <div class="completed-card">
                            <div class="completed-info">
                                <span class="completed-badge">#<?php echo $order_id; ?></span>
                                <span class="completed-customer"><?php echo esc_html($customer_name); ?></span>
                            </div>
                            <span class="completed-tracking"><?php echo esc_html($tracking); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
    }
    ?>

    <?php
}