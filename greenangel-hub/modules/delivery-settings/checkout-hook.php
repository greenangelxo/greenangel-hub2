<?php
// Show delivery date field at checkout
add_action('woocommerce_after_order_notes', function($checkout) {
    echo '<div id="greenangel_delivery_date_wrap">';
    woocommerce_form_field('delivery_date', [
        'type' => 'text',
        'class' => ['form-row-wide'],
        'label' => 'Preferred Delivery Date',
        'placeholder' => 'Select your delivery date',
        'required' => true,
    ], $checkout->get_value('delivery_date'));
    echo '</div>';
});

// Force validation of delivery date field
add_action('woocommerce_checkout_process', function() {
    if (empty($_POST['delivery_date'])) {
        wc_add_notice(__('Please select a delivery date before completing your order.'), 'error');
    }
});

// Save the delivery date to the order
add_action('woocommerce_checkout_create_order', function($order, $data) {
    if (!empty($_POST['delivery_date'])) {
        $date = sanitize_text_field($_POST['delivery_date']);
        $order->update_meta_data('_delivery_date', $date);
    }
}, 10, 2);

// Show the delivery date in the admin panel
add_action('woocommerce_admin_order_data_after_shipping_address', function($order) {
    $date = $order->get_meta('_delivery_date');
    if ($date) {
        echo '<p><strong>📦 Delivery Date:</strong> ' . esc_html($date) . '</p>';
    }
});

// Add editable field in the admin order page
add_action('woocommerce_admin_order_data_after_order_details', function($order){
    $date = $order->get_meta('_delivery_date');
    echo '<div class="order_data_column">';
    echo '<h4>📦 Delivery Date</h4>';
    echo '<input type="date" name="greenangel_admin_delivery_date" value="' . esc_attr($date) . '" style="width: 100%; margin-bottom: 10px;">';
    echo '<label><input type="checkbox" name="greenangel_notify_customer"> Notify customer of change</label>';
    echo '<p style="font-size: 12px; color: #666; margin-top: 5px;">Use the main "Update" button below to save changes</p>';
    echo '</div>';
});

// Save admin changes + optionally notify
add_action('woocommerce_process_shop_order_meta', function($order_id, $post) {
    if (! isset($_POST['greenangel_admin_delivery_date'])) {
        return;
    }
    
    $date = sanitize_text_field($_POST['greenangel_admin_delivery_date']);
    
    // Use WooCommerce order object instead of direct post meta
    $order = wc_get_order($order_id);
    $order->update_meta_data('_delivery_date', $date);
    $order->save(); // This is crucial!
    
    if (!empty($_POST['greenangel_notify_customer'])) {
        $order = wc_get_order($order_id);
        $to = $order->get_billing_email();
        $first_name = $order->get_billing_first_name();
        $subject = '💚 Your Green Angel Delivery Date Has Been Updated';
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        
        ob_start();
        ?>
        <html>
        <body style="background: #222222; color: #aed604; font-family: 'Poppins', 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 30px;">
            <h2 style="color: #aed604;">😇 Hello <?php echo esc_html($first_name); ?></h2>
            <p>Your delivery date has been updated. You can expect your parcel on:</p>
            <p style="font-size: 20px; font-weight: bold; color: #000000;"><?php echo esc_html(date('l, F jS, Y', strtotime($date))); ?></p>
            <p>Thank you for choosing Green Angel XO 💎</p>
            <hr style="border: none; border-top: 1px solid #444;">
            <p style="font-size: 13px; color: #888;">This message was sent by our order update system. No action is required.</p>
        </body>
        </html>
        <?php
        $message = ob_get_clean();
        
        wp_mail($to, $subject, $message, $headers);
    }
}, 10, 2);

// Add delivery date column - CORRECT HOOKS for modern WooCommerce
add_filter('woocommerce_shop_order_list_table_columns', function($columns) {
    // Insert after order status
    $new = [];
    foreach ($columns as $key => $value) {
        $new[$key] = $value;
        if ($key === 'order_status') {
            $new['delivery_date'] = 'Delivery Date';
        }
    }
    return $new;
});

add_action('woocommerce_shop_order_list_table_custom_column', function($column, $order) {
    if ($column === 'delivery_date') {
        $date = $order->get_meta('_delivery_date');
        if ($date) {
            echo '<strong style="color: #2271b1;">' . date('j M Y', strtotime($date)) . '</strong>';
        } else {
            echo '<span style="color: #999;">—</span>';
        }
    }
}, 10, 2);

// Function to count orders by delivery date (processing orders only)
function greenangel_count_orders_by_date($date) {
    $args = [
        'status' => 'processing',
        'meta_query' => [
            [
                'key' => '_delivery_date',
                'value' => $date,
                'compare' => '='
            ]
        ],
        'return' => 'ids'
    ];
    
    $orders = wc_get_orders($args);
    return count($orders);
}

// AJAX endpoint for checking delivery date availability
add_action('wp_ajax_greenangel_check_delivery_availability', 'greenangel_check_delivery_availability');
add_action('wp_ajax_nopriv_greenangel_check_delivery_availability', 'greenangel_check_delivery_availability');

function greenangel_check_delivery_availability() {
    $dates = isset($_POST['dates']) ? (array) $_POST['dates'] : [];
    $daily_limit = get_option('greenangel_daily_delivery_limit', 20);
    
    $availability = [];
    foreach ($dates as $date) {
        $date = sanitize_text_field($date);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $count = greenangel_count_orders_by_date($date);
            $availability[$date] = [
                'count' => $count,
                'available' => $count < $daily_limit,
                'limit' => $daily_limit
            ];
        }
    }
    
    wp_send_json($availability);
}

// Inject delivery date into ALL WooCommerce emails with beautiful styling
add_filter('woocommerce_email_order_meta_fields', function($fields, $sent_to_admin, $order) {
    $delivery_date = $order->get_meta('_delivery_date');
    if ($delivery_date) {
        $fields['greenangel_delivery_date'] = [
            'label' => '<span style="font-size: 16px; font-weight: bold; color: #2271b1;">📦 Delivery Date:</span>',
            'value' => '<span style="color: #2271b1; font-size: 18px; font-weight: bold; display: inline-block; padding: 8px 12px; background: #f0f6ff; border-radius: 6px; border-left: 4px solid #2271b1;">' . date('l, F j, Y', strtotime($delivery_date)) . '</span>'
        ];
    }
    return $fields;
}, 10, 3);



// Enqueue Flatpickr + inject delivery settings
add_action('wp_enqueue_scripts', function() {
    if (!is_checkout()) return;
    wp_enqueue_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', [], null, true);
    wp_enqueue_style('flatpickr-style', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
    
    // Pass admin settings to frontend
    $settings = [
        'days' => get_option('greenangel_delivery_days', ['tue','wed','thu','fri','sat']),
        'blackout' => get_option('greenangel_blackout_dates', []),
        'cutoff' => get_option('greenangel_cutoff_time', '09:00'),
        'max_days' => intval(get_option('greenangel_max_advance_days', 14)),
        'daily_limit' => intval(get_option('greenangel_daily_delivery_limit', 20)),
        'ajax_url' => admin_url('admin-ajax.php')
    ];
    wp_add_inline_script('flatpickr', 'window.greenAngelDelivery = ' . json_encode($settings) . ';', 'before');
    
    // Initialise Flatpickr with delivery limit checking
    wp_add_inline_script('flatpickr', <<<JS
document.addEventListener("DOMContentLoaded", function() {
    console.log('=== GREEN ANGEL DELIVERY DEBUG ===');
    
    if (!window.greenAngelDelivery) {
        console.error('greenAngelDelivery data not found!');
        return;
    }
    
    const { days, blackout, cutoff, max_days, daily_limit, ajax_url } = window.greenAngelDelivery;
    
    console.log('Delivery settings received:', {
        days: days,
        blackout: blackout,
        cutoff: cutoff,
        max_days: max_days,
        daily_limit: daily_limit
    });
    
    const allowedWeekdays = {
        'tue': 2,
        'wed': 3,
        'thu': 4,
        'fri': 5,
        'sat': 6
    };
    
    const now = new Date();
    const cutoffParts = cutoff.split(':');
    const cutoffDate = new Date();
    cutoffDate.setHours(parseInt(cutoffParts[0]), parseInt(cutoffParts[1]), 0);
    
    // Calculate proper earliest delivery date
    function calculateEarliestDelivery() {
        // Determine shipping date
        let shippingDate = new Date();
        if (now >= cutoffDate) {
            // After cutoff - ships next day
            shippingDate.setDate(shippingDate.getDate() + 1);
        }
        // Before cutoff - ships today
        
        // Calculate earliest delivery date (day after shipping)
        let earliestDelivery = new Date(shippingDate);
        earliestDelivery.setDate(earliestDelivery.getDate() + 1);
        
        const validWeekdays = days.map(day => allowedWeekdays[day]);
        
        // Find next valid delivery day
        let attempts = 0;
        while (attempts < 14) { // Prevent infinite loop
            const weekday = earliestDelivery.getDay();
            const dateStr = earliestDelivery.toISOString().split('T')[0];
            
            // Check if this day is valid and not blacked out
            if (validWeekdays.includes(weekday) && !blackout.includes(dateStr)) {
                return earliestDelivery;
            }
            
            // Move to next day
            earliestDelivery.setDate(earliestDelivery.getDate() + 1);
            attempts++;
        }
        
        return earliestDelivery; // fallback
    }
    
    const earliestDelivery = calculateEarliestDelivery();
    const validWeekdays = days.map(day => allowedWeekdays[day]);
    
    console.log('Current time:', now.toISOString());
    console.log('Cutoff time today:', cutoffDate.toISOString());
    console.log('After cutoff?', now >= cutoffDate);
    console.log('Earliest delivery date calculated:', earliestDelivery.toISOString().split('T')[0]);
    console.log('Valid weekdays (0=Sun, 6=Sat):', validWeekdays);
    console.log('Blackout dates to block:', blackout);
    console.log('Daily delivery limit:', daily_limit);
    
    // Store capacity data
    let capacityData = {};
    
    // Function to check delivery capacity via AJAX
    async function checkDeliveryCapacity(dates) {
        try {
            const formData = new FormData();
            formData.append('action', 'greenangel_check_delivery_availability');
            formData.append('dates', JSON.stringify(dates));
            
            const response = await fetch(ajax_url, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            capacityData = { ...capacityData, ...data };
            console.log('Capacity check results:', data);
            return data;
        } catch (error) {
            console.error('Error checking delivery capacity:', error);
            return {};
        }
    }
    
    // Generate date range for initial capacity check
    const datesToCheck = [];
    const checkStart = new Date(earliestDelivery);
    const checkEnd = new Date();
    checkEnd.setDate(checkEnd.getDate() + max_days);
    
    for (let d = new Date(checkStart); d <= checkEnd; d.setDate(d.getDate() + 1)) {
        const weekday = d.getDay();
        if (validWeekdays.includes(weekday)) {
            datesToCheck.push(d.toISOString().split('T')[0]);
        }
    }
    
    // Set the default date IMMEDIATELY and aggressively, before any async operations
    const defaultDateStr = earliestDelivery.toISOString().split('T')[0];
    const deliveryInput = document.querySelector("input[name='delivery_date']");
    
    console.log('🎯 FORCING DEFAULT DATE:', defaultDateStr);
    
    // Force set the value right now, aggressively
    if (deliveryInput) {
        deliveryInput.value = defaultDateStr;
        deliveryInput.setAttribute('value', defaultDateStr);
        console.log('✅ Set input value to:', deliveryInput.value);
        
        // Trigger change event so any listeners know about it
        deliveryInput.dispatchEvent(new Event('change', { bubbles: true }));
        deliveryInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    
    // Initial capacity check - wait for it to complete before initializing Flatpickr
    checkDeliveryCapacity(datesToCheck).then(() => {
        initializeFlatpickr();
        
        // FORCE the value again after Flatpickr loads, just to be extra sure
        setTimeout(() => {
            const input = document.querySelector("input[name='delivery_date']");
            if (input && !input.value) {
                console.log('🔥 EMERGENCY FALLBACK: Setting date after timeout');
                input.value = defaultDateStr;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }, 500);
    });
    
    function initializeFlatpickr() {
        const fp = flatpickr("input[name='delivery_date']", {
        dateFormat: "Y-m-d", // Keep backend format for processing
        altInput: true,
        altFormat: "l, j F Y", // Beautiful text format: "Wednesday, 25 June 2025"
        defaultDate: defaultDateStr,
        minDate: earliestDelivery, // Use calculated earliest delivery date
        maxDate: new Date().fp_incr(max_days),
        disable: [
            // Add blackout dates as static entries
            ...blackout,
            function(date) {
                const weekday = date.getDay();
                const ymd = date.toISOString().split('T')[0];
                
                // Check if weekday is disabled
                const weekdayDisabled = !validWeekdays.includes(weekday);
                
                // Check if date is in blackout list
                const blackoutDisabled = blackout.includes(ymd);
                
                // Check if delivery limit reached
                const capacityInfo = capacityData[ymd];
                const capacityDisabled = capacityInfo && !capacityInfo.available;
                
                const shouldDisable = weekdayDisabled || blackoutDisabled || capacityDisabled;
                
                // Force console log for debugging
                console.log('DISABLE CHECK:', ymd, {
                    weekday: weekdayDisabled ? 'invalid day' : 'ok',
                    blackout: blackoutDisabled ? 'blackout' : 'ok', 
                    capacity: capacityDisabled ? 'full' : 'ok',
                    FINAL_RESULT: shouldDisable ? 'BLOCKED' : 'ALLOWED',
                    RETURN_VALUE: shouldDisable
                });
                
                // Flatpickr expects true to DISABLE the date
                return shouldDisable;
            }
        ],
        onOpen: function() {
            // Refresh capacity data when calendar opens
            checkDeliveryCapacity(datesToCheck);
        },
        onReady: function() {
            // AFTER Flatpickr is ready, IMMEDIATELY set the default date 
            console.log('✅ Flatpickr ready! FORCE setting default date:', defaultDateStr);
            
            // Use a tiny delay to ensure Flatpickr has fully rendered
            setTimeout(() => {
                fp.setDate(defaultDateStr, true); // Set the date properly
                console.log('🎯 Default date FORCED through Flatpickr API');
                
                // Extra aggressive - manually set the alt input if it exists
                const altInput = fp.altInput;
                if (altInput) {
                    const formattedDate = fp.formatDate(new Date(defaultDateStr), "l, j F Y");
                    altInput.value = formattedDate;
                    console.log('💪 MANUALLY set alt input to:', formattedDate);
                }
            }, 50); // Tiny delay to let Flatpickr finish setup
        }
        });
    }
    
    console.log('=== END GREEN ANGEL DEBUG ===');
});
JS);
});