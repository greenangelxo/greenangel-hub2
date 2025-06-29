<?php
defined( 'ABSPATH' ) || exit;
// 🌿 Enqueue orders preview styles
add_action('wp_enqueue_scripts', 'greenangel_enqueue_orders_preview_styles');
function greenangel_enqueue_orders_preview_styles() {
    if ( ! is_singular() ) {
        return; // Only load on pages/posts
    }

    global $post;

    // Debug helper: inspect page context if needed.

    if ( has_shortcode( $post->post_content, 'greenangel_orders_preview' ) ) {
        wp_enqueue_style(
            'greenangel-orders-preview',
            plugin_dir_url( __FILE__ ) . 'orders-preview.css',
            [],
            '1.0'
        );
        // Debug: check CSS URL when troubleshooting
    }
}

// 🌿 Optional debug loader
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    add_action( 'wp_enqueue_scripts', 'greenangel_force_enqueue_orders_styles', 20 );
    function greenangel_force_enqueue_orders_styles() {
        wp_enqueue_style(
            'greenangel-orders-preview-force',
            plugin_dir_url( __FILE__ ) . 'orders-preview.css',
            [],
            time() // Cache busting
        );
    }
}

// 🌿 Green Angel Hub – Orders Preview

function greenangel_render_orders_list() {
    if (!is_user_logged_in()) return '<p style="color: #fff;">Please log in to view your orders.</p>';
    
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
    
    ob_start();
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
                        <!-- 🌟 YEAR SEPARATOR PILL -->
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
                            
                            // 🔍 DELIVERY DATE DETECTION - Handles both storage patterns!
                            $delivery_date = null;
                            
                            // Method 1: Try newer format (_delivery_date)
                            $delivery_meta = $order->get_meta('_delivery_date');
                            if ($delivery_meta) {
                                $delivery_date = $delivery_meta;
                            }
                            
                            // Method 2: Try older format (delivery_date)
                            if (!$delivery_date) {
                                $delivery_no_underscore = $order->get_meta('delivery_date');
                                if ($delivery_no_underscore) {
                                    $delivery_date = $delivery_no_underscore;
                                }
                            }
                            
                            // Method 3: Fallback to post meta
                            if (!$delivery_date) {
                                $delivery_post_meta = get_post_meta($order_id, '_delivery_date', true);
                                if ($delivery_post_meta) {
                                    $delivery_date = $delivery_post_meta;
                                }
                            }
                            
                            // Format the delivery date if we found one
                            $formatted_delivery_date = '';
                            if ($delivery_date) {
                                // Try to parse and format the date
                                $date_obj = null;
                                
                                // Try different date formats
                                $formats = ['Y-m-d', 'Y-m-d H:i:s', 'd/m/Y', 'm/d/Y', 'Y/m/d'];
                                foreach ($formats as $format) {
                                    $date_obj = DateTime::createFromFormat($format, $delivery_date);
                                    if ($date_obj) break;
                                }
                                
                                // If still no luck, try strtotime
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
                                    $formatted_delivery_date = $delivery_date; // Use as-is if can't format
                                }
                            }
                            
                            // Map WooCommerce statuses to our guide classes
                            $status_class_map = [
                                'pending' => 'pending-payment',
                                'processing' => 'processing', 
                                'ship-today' => 'ship-today',
                                'completed' => 'completed',
                                'cancelled' => 'cancelled',
                                'on-hold' => 'pending-payment'
                            ];
                            $status_class = isset($status_class_map[$status_slug]) ? $status_class_map[$status_slug] : 'processing';
                        ?>
                        <div class="greenangel-order-card" data-status="<?php echo esc_attr($status_class); ?>" data-index="<?php echo $global_index; ?>" style="<?php echo $global_index >= 10 ? 'display:none;' : ''; ?>">
                            <div class="order-meta">
                                <span class="order-number">#<?php echo esc_html($order_id); ?></span>
                                <span class="order-date"><?php echo esc_html($order_date); ?></span>
                            </div>
                            
                            <!-- 🌿 Debug removed - delivery dates working perfectly! -->
                            
                            <div class="order-status-center">
                                <div class="order-status <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status); ?></div>
                                
                                <!-- 🚀 DELIVERY DATE PILL OR PLACEHOLDER -->
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
        
        // DEBUG: log computed style of card #0
        console.log('DEBUG: card #0 computed display =', getComputedStyle(cards[0]).display);
        
        // Force-hide cards 11+ with !important
        cards.forEach((card,i) => {
            if (i >= visibleCount) {
                card.style.setProperty('display','none','important');
            }
        });
        
        // If ≤10 items total, hide the button too
        if (cards.length <= visibleCount) {
            loadMoreBtn.style.setProperty('display','none','important');
        }
        
        loadMoreBtn.addEventListener('click', () => {
            let revealed = 0;
            cards.forEach(card => {
                // Only unhide up to 10 at a time
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
    return ob_get_clean();
}

add_shortcode('greenangel_orders', 'greenangel_render_orders_list');
?>