<?php 
// Green Angel Hub – Stock Check Module
// Quick-glance stock viewer for all your products!

function greenangel_render_stock_check_tab() {
    // Security check
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Permission denied');
    }
    ?>
    
    <div class="stock-header">
        <h2 class="stock-title">📦 Stock Check</h2>
        <p class="stock-subtitle">Quick-glance inventory for all products</p>
    </div>

    <style>
        /* Stock Check - Angel Hub Dark Theme */
        .stock-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .stock-title {
            font-size: 24px;
            font-weight: 600;
            color: #aed604;
            margin: 0 0 8px 0;
        }
        
        .stock-subtitle {
            font-size: 14px;
            color: #888;
            margin: 0;
        }
        
        /* Mobile Cards View */
        .stock-cards-container {
            display: grid;
            gap: 20px;
            margin-top: 20px;
        }
        
        .stock-card {
            background: #1a1a1a;
            border: 2px solid #333;
            border-radius: 14px;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        
        .stock-card:hover {
            border-color: #444;
            transform: translateY(-2px);
        }
        
        .stock-card-header {
            background: #222;
            padding: 15px 20px;
            border-bottom: 2px solid #333;
        }
        
        .product-title {
            font-size: 16px;
            font-weight: 600;
            color: #aed604;
            margin: 0;
        }
        
        .stock-card-body {
            padding: 20px;
        }
        
        .stock-rows {
            display: grid;
            gap: 12px;
        }
        
        .stock-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #333;
        }
        
        .stock-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        
        .variation-name {
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            flex: 1;
            padding-right: 10px;
        }
        
        /* Stock Pills */
        .stock-pill {
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            min-width: 100px;
            justify-content: center;
        }
        
        .stock-green {
            background: rgba(174, 214, 4, 0.15);
            color: #aed604;
            border: 1px solid rgba(174, 214, 4, 0.4);
        }
        
        .stock-orange {
            background: rgba(255, 165, 0, 0.15);
            color: #ffa500;
            border: 1px solid rgba(255, 165, 0, 0.4);
        }
        
        .stock-red {
            background: rgba(255, 99, 132, 0.15);
            color: #ff6384;
            border: 1px solid rgba(255, 99, 132, 0.4);
        }
        
        /* Quick Stats Bar */
        .stock-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #1a1a1a;
            border: 2px solid #333;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 28px;
            font-weight: 700;
            color: #aed604;
            display: block;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
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
        
        /* Desktop Styles */
        @media (min-width: 768px) {
            .stock-cards-container {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .stock-stats {
                grid-template-columns: repeat(3, 1fr);
                max-width: 600px;
                margin: 0 auto 30px;
            }
        }
        
        @media (min-width: 1024px) {
            .stock-cards-container {
                grid-template-columns: repeat(3, 1fr);
                max-width: 1200px;
                margin: 20px auto;
            }
            
            .product-title {
                font-size: 18px;
            }
            
            .stock-pill {
                min-width: 120px;
            }
        }
        
        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .stock-card {
            animation: fadeIn 0.3s ease;
        }
    </style>

    <?php
    // Fetch all published products - simplified query
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC'
    );
    
    $products = get_posts($args);
    
    if (empty($products)) {
        ?>
        <div class="empty-state">
            <div class="empty-state-icon">📦</div>
            <h3 class="empty-state-title">No Products Found</h3>
            <p class="empty-state-text">There are no published products to check stock for.</p>
        </div>
        <?php
        return;
    }
    
    // Process products and build data array
    $products_data = array();
    $total_items = 0;
    $low_stock_count = 0;
    $out_of_stock_count = 0;
    
    foreach ($products as $post) {
        $product = wc_get_product($post->ID);
        
        if (!$product) {
            continue;
        }
        
        $product_id = $product->get_id();
        $product_title = $product->get_name();
        $variations = array();
        
        // Check product type
        if ($product->is_type('simple')) {
            // Simple product
            $stock_qty = $product->get_stock_quantity();
            
            // If stock quantity is null, check stock status
            if ($stock_qty === null) {
                $stock_status = $product->get_stock_status();
                if ($stock_status === 'instock') {
                    $stock_qty = 999; // Show as high stock
                } else if ($stock_status === 'outofstock') {
                    $stock_qty = 0;
                } else {
                    $stock_qty = 5; // onbackorder or other
                }
            }
            
            $variations[] = array(
                'name' => 'Simple Product',
                'stock' => intval($stock_qty)
            );
            $total_items++;
            
            if ($stock_qty <= 5) $low_stock_count++;
            if ($stock_qty == 0) $out_of_stock_count++;
            
        } else if ($product->is_type('variable')) {
            // Variable product
            $available_variations = $product->get_available_variations();
            
            if (!empty($available_variations)) {
                foreach ($available_variations as $variation_data) {
                    $variation_id = $variation_data['variation_id'];
                    $variation = wc_get_product($variation_id);
                    
                    if (!$variation) {
                        continue;
                    }
                    
                    // Build variation name from attributes
                    $variation_attributes = $variation_data['attributes'];
                    $name_parts = array();
                    
                    foreach ($variation_attributes as $key => $value) {
                        if ($value) {
                            // Clean up attribute name
                            $clean_key = str_replace('attribute_pa_', '', $key);
                            $clean_key = str_replace('attribute_', '', $clean_key);
                            $clean_key = str_replace('_', ' ', $clean_key);
                            $clean_key = ucwords($clean_key);
                            
                            $name_parts[] = ucfirst($value);
                        }
                    }
                    
                    $variation_name = !empty($name_parts) ? implode(' / ', $name_parts) : 'Variation';
                    
                    // Get stock quantity
                    $stock_qty = $variation->get_stock_quantity();
                    
                    // If stock quantity is null, check stock status
                    if ($stock_qty === null) {
                        $stock_status = $variation->get_stock_status();
                        if ($stock_status === 'instock') {
                            $stock_qty = 999; // Show as high stock
                        } else if ($stock_status === 'outofstock') {
                            $stock_qty = 0;
                        } else {
                            $stock_qty = 5; // onbackorder or other
                        }
                    }
                    
                    $variations[] = array(
                        'name' => $variation_name,
                        'stock' => intval($stock_qty)
                    );
                    $total_items++;
                    
                    if ($stock_qty <= 5) $low_stock_count++;
                    if ($stock_qty == 0) $out_of_stock_count++;
                }
            }
        }
        
        // Only add if we have variations to show
        if (!empty($variations)) {
            $products_data[] = array(
                'id' => $product_id,
                'title' => $product_title,
                'variations' => $variations
            );
        }
    }
    
    // Display stats
    ?>
    <div class="stock-stats">
        <div class="stat-card">
            <span class="stat-number"><?php echo $total_items; ?></span>
            <span class="stat-label">Total Items</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?php echo $low_stock_count; ?></span>
            <span class="stat-label">Low Stock Items</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?php echo $out_of_stock_count; ?></span>
            <span class="stat-label">Out of Stock</span>
        </div>
    </div>
    
    <?php if (empty($products_data)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📊</div>
            <h3 class="empty-state-title">No Stock-Managed Products</h3>
            <p class="empty-state-text">None of your products are currently managing stock.</p>
        </div>
        <?php return; ?>
    <?php endif; ?>
    
    <!-- Products Grid -->
    <div class="stock-cards-container">
        <?php foreach ($products_data as $product_data): ?>
            <div class="stock-card">
                <div class="stock-card-header">
                    <h3 class="product-title"><?php echo esc_html($product_data['title']); ?></h3>
                </div>
                <div class="stock-card-body">
                    <div class="stock-rows">
                        <?php foreach ($product_data['variations'] as $variation): 
                            $stock = $variation['stock'];
                            
                            // Determine badge class and label
                            if ($stock >= 11) {
                                $badge_class = 'stock-green';
                                $badge_icon = '✅';
                            } elseif ($stock >= 6) {
                                $badge_class = 'stock-orange';
                                $badge_icon = '⚠️';
                            } else {
                                $badge_class = 'stock-red';
                                $badge_icon = $stock == 0 ? '❌' : '🚨';
                            }
                            
                            // Handle high stock display
                            $display_stock = $stock;
                            if ($stock >= 999) {
                                $display_stock = 'In Stock';
                            }
                        ?>
                            <div class="stock-row">
                                <span class="variation-name"><?php echo esc_html($variation['name']); ?></span>
                                <span class="stock-pill <?php echo $badge_class; ?>">
                                    <?php echo $badge_icon; ?> <?php echo $display_stock; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php
}
?>