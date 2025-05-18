<?php
// 🌱 Green Angel Hub – Ship Today Module

define('GREENANGEL_SHIP_TODAY_LOG', plugin_dir_path(__FILE__) . '/../logs/ship-today-log.txt');

// Set the correct timezone for your location
date_default_timezone_set('Europe/London'); // Adjust this to your timezone if needed!

// 🧠 UI Callback
function greenangel_render_ship_today_tab() {
    ?>
    <div class="ship-today-container">
        <div class="title-bubble">💌 Ship Today Manager</div>
        
        <div class="ship-today-description">
            Press the button below to update orders to <span class="highlight-text">'Ship Today'</span> based on their delivery dates. 
            Orders with delivery dates for tomorrow will be automatically moved to Ship Today status.
        </div>
        
        <div class="angel-card">
            <div class="card-header">
                <span class="header-bubble">Process Orders</span>
            </div>
            
            <div class="card-content">
                <button id="ship-today-process" class="angel-button">
                    <span class="button-icon">🚚</span>
                    Run Ship Today Process
                </button>
                <span id="process-success" class="success-indicator">✅ Process completed successfully!</span>
            </div>
        </div>
        
        <!-- Log management buttons moved below the log textarea -->
        
        <div class="angel-card">
            <div class="card-header">
                <span class="header-bubble">Log Preview</span>
            </div>
            
            <div class="card-content">
                <textarea id="log-preview" class="ship-today-log" readonly></textarea>
                
                <!-- Log management buttons as bubbles -->
                <div class="log-controls">
                    <button id="clear-log" class="log-bubble-button">
                        <span class="button-icon">🗑️</span>
                        Clear Log
                    </button>
                    <button id="download-log" class="log-bubble-button">
                        <span class="button-icon">📥</span>
                        Download Log
                    </button>
                    <span id="clear-success" class="success-indicator">✅ Log cleared successfully!</span>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        /* Ship Today Container */
        .ship-today-container {
            margin-top: 10px;
            font-family: "Poppins", sans-serif !important;
        }
        
        /* Title Styling */
        .ship-today-description {
            margin-bottom: 30px;
            font-size: 15px;
            line-height: 1.5;
            color: #222222;
            font-weight: 400;
        }
        
        .highlight-text {
            background: rgba(174, 214, 4, 0.2);
            color: #222222;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 10px;
        }
        
        /* Card Styling */
        .angel-card {
            background: #222222;
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 25px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            padding: 16px 20px;
            background: #222222;
        }
        
        .card-content {
            padding: 25px;
            background: #222222;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        /* Button Group */
        .button-group {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        /* Button Styling */
        .angel-button {
            background: #aed604;
            color: #222222;
            border: none;
            padding: 12px 20px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 20px;
            transition: all 0.2s ease-in-out;
            font-family: "Poppins", sans-serif !important;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            min-width: 220px;
            justify-content: center;
        }
        
        .angel-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        
        .angel-button:active {
            transform: translateY(1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }
        
        .angel-button.secondary {
            background: rgba(174, 214, 4, 0.15);
            color: #aed604;
            min-width: auto;
        }
        
        .angel-button.secondary:hover {
            background: rgba(174, 214, 4, 0.25);
        }
        
        .button-icon {
            font-size: 18px;
        }
        
        /* Success Indicator */
        .success-indicator {
            display: inline-block;
            background: #aed604;
            color: #222222;
            padding: 8px 18px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 14px;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
            font-family: "Poppins", sans-serif !important;
            animation: none;
        }
        
        .success-indicator.show {
            opacity: 1;
            transform: translateY(0);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(174, 214, 4, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(174, 214, 4, 0); }
            100% { box-shadow: 0 0 0 0 rgba(174, 214, 4, 0); }
        }
        
        /* Log Preview */
        .ship-today-log {
            width: 100%;
            height: 300px;
            background: #f5f5f5;
            color: #222222;
            border: 1px solid rgba(174, 214, 4, 0.3);
            border-radius: 10px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            resize: vertical;
            line-height: 1.6;
        }
        
        /* Log Bubble Buttons */
        .log-controls {
            display: flex;
            gap: 12px;
            margin-top: 15px;
            align-items: center;
        }
        
        .log-bubble-button {
            background: #222222;
            color: #aed604;
            border: none;
            padding: 8px 16px;
            font-weight: 500;
            cursor: pointer;
            border-radius: 20px;
            transition: all 0.2s ease-in-out;
            font-family: "Poppins", sans-serif !important;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .log-bubble-button:hover {
            background: #333333;
            transform: translateY(-2px);
        }
        
        .ship-today-log:focus {
            outline: none;
            border-color: #aed604;
        }
        
        /* Header bubbles */
        .header-bubble {
            display: inline-block;
            background-color: #aed604;
            color: #222222;
            padding: 6px 12px;
            border-radius: 16px;
            font-weight: 500;
            font-size: 12px;
            white-space: nowrap;
        }
    </style>

    <script>
        function showSuccess(id) {
            const el = document.getElementById(id);
            el.classList.add('show');
            setTimeout(() => el.classList.remove('show'), 3000);
        }

        document.getElementById('ship-today-process').onclick = () => {
            const btn = document.getElementById('ship-today-process');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="button-icon">🔄</span> Processing...';
            btn.disabled = true;
            btn.style.opacity = '0.7';

            fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=greenangel_ship_today_run')
                .then(res => res.text()).then(data => {
                    document.getElementById('log-preview').value = data;
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    showSuccess('process-success');
                });
        };

        document.getElementById('clear-log').onclick = () => {
            fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=greenangel_ship_today_clear')
                .then(() => {
                    document.getElementById('log-preview').value = '';
                    showSuccess('clear-success');
                });
        };

        document.getElementById('download-log').onclick = () => {
            window.location.href = '<?php echo admin_url('admin-ajax.php'); ?>?action=greenangel_ship_today_download';
        };

        window.addEventListener('DOMContentLoaded', () => {
            fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=greenangel_ship_today_log')
                .then(res => res.text()).then(data => {
                    document.getElementById('log-preview').value = data;
                });
        });
    </script>
    <?php
}

// 🚚 AJAX: Run Ship Today
add_action('wp_ajax_greenangel_ship_today_run', function() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Permission denied');
    }
    $log = "[" . date('Y-m-d H:i:s') . "] 💚 Manual 'Ship Today' process started.\n";
    $updated = 0; $skipped = 0;

    $orders = wc_get_orders(['status' => 'processing', 'limit' => -1]);
    $tomorrow = date('Y-m-d', strtotime('+1 day'));

    foreach ($orders as $order) {
        $delivery = $order->get_meta('delivery_date');
        if (!$delivery) {
            $log .= "[" . date('Y-m-d H:i:s') . "] Order #{$order->get_id()} skipped (no delivery date).\n";
            $skipped++; continue;
        }

        // Format delivery date to Y-m-d for comparison
        $delivery_date = date('Y-m-d', strtotime($delivery));
        
        if ($delivery_date == $tomorrow) {
            $order->update_status('ship-today');
            $log .= "[" . date('Y-m-d H:i:s') . "] Order #{$order->get_id()} updated to 'Ship Today'.\n";
            $updated++;
        } else {
            $log .= "[" . date('Y-m-d H:i:s') . "] Order #{$order->get_id()} remains 'Processing'. (Future Delivery: $delivery)\n";
            $skipped++;
        }
    }

    $log .= "[" . date('Y-m-d H:i:s') . "] ✨ Process completed. Updated: $updated, Skipped: $skipped.\n";
    file_put_contents(GREENANGEL_SHIP_TODAY_LOG, $log, FILE_APPEND);
    echo $log; wp_die();
});

// 🔍 AJAX: Load Log
add_action('wp_ajax_greenangel_ship_today_log', function() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Permission denied');
    }
    echo file_exists(GREENANGEL_SHIP_TODAY_LOG) ? file_get_contents(GREENANGEL_SHIP_TODAY_LOG) : '';
    wp_die();
});

// 🧼 AJAX: Clear Log
add_action('wp_ajax_greenangel_ship_today_clear', function() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Permission denied');
    }
    file_put_contents(GREENANGEL_SHIP_TODAY_LOG, '');
    wp_die();
});

// 📥 AJAX: Download Log
add_action('wp_ajax_greenangel_ship_today_download', function() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Permission denied');
    }
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="ship-today-log.txt"');
    readfile(GREENANGEL_SHIP_TODAY_LOG);
    exit;
});