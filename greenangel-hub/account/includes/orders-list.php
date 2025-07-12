<?php
/**
 * 🌿 Green Angel - Orders List Page
 * Location: plugins/greenangel-hub/account/includes/orders-list.php
 */

// Remove Woo's default orders table
remove_action('woocommerce_account_orders_endpoint', 'woocommerce_account_orders', 10);

// Now create the custom orders page
add_action('woocommerce_account_orders_endpoint', 'greenangel_override_orders_page', 1);
function greenangel_override_orders_page() {
    if (!is_user_logged_in()) {
        echo '<p class="greenangel-notice">Please log in to view your orders.</p>';
        return;
    }
    
    $user_id = get_current_user_id();
    $orders = wc_get_orders([
        'customer_id' => $user_id,
        'limit' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);
    
    // Group orders by year
    $orders_by_year = [];
    foreach ($orders as $order) {
        $year = $order->get_date_created()->date_i18n('Y');
        if (!isset($orders_by_year[$year])) {
            $orders_by_year[$year] = [];
        }
        $orders_by_year[$year][] = $order;
    }
    
    ?>
    <div class="greenangel-orders-wrapper">
        <div class="greenangel-orders-container">
            <!-- 🌿 Order Status Guide Panel -->
            <div class="ga-panel">
                <div class="greenangel-status-guide">
                    <div class="guide-title-pill">📋 ORDER STATUS GUIDE</div>
                    <div class="guide-grid">
                        <div class="guide-item">
                            <div class="status-circle pending-payment"></div>
                            <span class="status-label">PENDING PAYMENT</span>
                        </div>
                        <div class="guide-item">
                            <div class="status-circle processing"></div>
                            <span class="status-label">PROCESSING</span>
                        </div>
                        <div class="guide-item">
                            <div class="status-circle ship-today"></div>
                            <span class="status-label">SHIP TODAY</span>
                        </div>
                        <div class="guide-item">
                            <div class="status-circle completed"></div>
                            <span class="status-label">COMPLETED</span>
                        </div>
                        <div class="guide-item">
                            <div class="status-circle cancelled"></div>
                            <span class="status-label">CANCELLED</span>
                        </div>
                    </div>
                    <div class="guide-footer">
                        📧 Important: Emails are sent for each status update. Check your spam folder if you don't see them.
                    </div>
                </div>
            </div>
            
            <!-- 🌿 Orders List Panel -->
            <div class="ga-orders-panel">
                <?php if (empty($orders)) : ?>
                    <p class="greenangel-no-orders">No orders found.</p>
                <?php else : ?>
                    <div class="orders-grid" id="greenangel-orders-grid">
                    <?php 
                        $global_index = 0;
                        foreach ($orders_by_year as $year => $year_orders) : 
                    ?>
                        <!-- Year Separator -->
                        <div class="greenangel-year-separator" data-index="<?php echo $global_index; ?>" style="<?php echo $global_index >= 10 ? 'display:none;' : ''; ?>">
                            <div class="year-pill"><?php echo esc_html($year); ?></div>
                        </div>
                        <?php $global_index++; ?>
                        
                        <?php foreach ($year_orders as $order) : ?>
                        <?php
                            $order_id = $order->get_id();
                            $order_date = $order->get_date_created()->date_i18n('jS M');
                            $status = wc_get_order_status_name($order->get_status());
                            $status_slug = $order->get_status();
                            $view_url = esc_url($order->get_view_order_url());
                            
                            // Delivery date detection
                            $delivery_date = null;
                            $possible_keys = ['_delivery_date', 'delivery_date', '_jckwds_delivery_date', 'jckwds_delivery_date'];
                            foreach ($possible_keys as $key) {
                                $delivery_date = $order->get_meta($key);
                                if ($delivery_date) break;
                            }
                            
                            if (!$delivery_date) {
                                foreach ($possible_keys as $key) {
                                    $delivery_date = get_post_meta($order_id, $key, true);
                                    if ($delivery_date) break;
                                }
                            }
                            
                            // Format delivery date
                            $formatted_delivery_date = '';
                            if ($delivery_date) {
                                $date_obj = null;
                                $formats = ['Y-m-d', 'Y-m-d H:i:s', 'd/m/Y', 'm/d/Y', 'Y/m/d'];
                                foreach ($formats as $format) {
                                    $date_obj = DateTime::createFromFormat($format, $delivery_date);
                                    if ($date_obj) break;
                                }
                                
                                if (!$date_obj) {
                                    $timestamp = strtotime($delivery_date);
                                    if ($timestamp) {
                                        $date_obj = new DateTime();
                                        $date_obj->setTimestamp($timestamp);
                                    }
                                }
                                
                                if ($date_obj) {
                                    $formatted_delivery_date = $date_obj->format('j M Y');
                                } else {
                                    $formatted_delivery_date = $delivery_date;
                                }
                            }
                            
                            // Map statuses
                            $status_class_map = [
                                'pending' => 'pending-payment',
                                'processing' => 'processing', 
                                'ship-today' => 'ship-today',
                                'completed' => 'completed',
                                'cancelled' => 'cancelled',
                                'on-hold' => 'pending-payment',
                                'refunded' => 'cancelled',
                                'failed' => 'cancelled'
                            ];
                            $status_class = isset($status_class_map[$status_slug]) ? $status_class_map[$status_slug] : 'processing';
                        ?>
                        <div class="greenangel-order-card" data-status="<?php echo esc_attr($status_class); ?>" data-index="<?php echo $global_index; ?>" style="<?php echo $global_index >= 10 ? 'display:none;' : ''; ?>">
                            <div class="order-meta">
                                <span class="order-number">#<?php echo esc_html($order_id); ?></span>
                                <span class="order-date"><?php echo esc_html($order_date); ?></span>
                            </div>
                            
                            <div class="order-status-center">
                                <div class="order-status <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status); ?></div>
                                
                                <?php if ($formatted_delivery_date) : ?>
                                    <div class="delivery-date-pill">
                                        Delivery: <?php echo esc_html($formatted_delivery_date); ?>
                                    </div>
                                <?php else : ?>
                                    <div class="delivery-placeholder-pill">
                                        No delivery date
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <a href="<?php echo $view_url; ?>" class="view-button">View Order</a>
                        </div>
                        <?php $global_index++; ?>
                    <?php endforeach; ?>
                    <?php endforeach; ?>
                    </div>
                    
                    <div class="load-more-container">
                        <button id="load-more-orders" class="load-more-orders-button" style="<?php echo count($orders) <= 10 ? 'display:none;' : ''; ?>">SHOW MORE</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const loadMoreBtn = document.getElementById('load-more-orders');
        const cards = document.querySelectorAll('.greenangel-order-card, .greenangel-year-separator');
        let visibleCount = 10;
        
        // Force-hide cards 11+ 
        cards.forEach((card,i) => {
            if (i >= visibleCount) {
                card.style.setProperty('display','none','important');
            }
        });
        
        // If ≤10 items total, hide the button
        if (cards.length <= visibleCount) {
            loadMoreBtn.style.setProperty('display','none','important');
        }
        
        loadMoreBtn.addEventListener('click', () => {
            let revealed = 0;
            cards.forEach(card => {
                if (card.style.getPropertyValue('display') === 'none' && revealed < 10) {
                    card.style.setProperty('display','block','important');
                    revealed++;
                }
            });
            
            visibleCount += revealed;
            
            if (visibleCount >= cards.length) {
                loadMoreBtn.style.setProperty('display','none','important');
            }
        });
    });
    </script>
    <?php
    
    // Prevent any further Woo output
    return false;
}