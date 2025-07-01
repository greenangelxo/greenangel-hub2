<?php
// 🌱 Green Angel Hub – Ship Today Module (Updated for Format Crossover)

define('GREENANGEL_SHIP_TODAY_LOG', plugin_dir_path(__FILE__) . '/../logs/ship-today-log.txt');

// 🧠 UI Callback
function greenangel_render_ship_today_tab() {
    ?>
    <style>
        /* Module-specific styles that work WITH the dark wrapper */
        .ship-today-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .ship-today-title {
            font-size: 24px;
            font-weight: 600;
            color: #aed604;
            margin: 0 0 8px 0;
        }
        
        .ship-today-subtitle {
            font-size: 14px;
            color: #888;
            margin: 0;
        }

        .log-card {
            background: #1a1a1a;
            border: 2px solid #333;
            border-radius: 14px;
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .log-title {
            color: #aed604;
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }
        
        .log-actions {
            display: flex;
            gap: 10px;
        }
        
        .log-button {
            background: #333;
            color: #aed604;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        
        .log-button:hover {
            background: #444;
            transform: translateY(-1px);
            color: #aed604;
        }

        .log-viewer {
            width: 100%;
            height: 300px;
            background: #0a0a0a;
            border: 2px solid #333;
            border-radius: 10px;
            padding: 15px;
            color: #aed604;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
            resize: vertical;
            outline: none;
            overflow-y: auto;
        }
        
        .log-viewer:focus {
            border-color: #444;
        }
        
        .log-viewer::-webkit-scrollbar {
            width: 10px;
        }
        
        .log-viewer::-webkit-scrollbar-track {
            background: #1a1a1a;
            border-radius: 5px;
        }
        
        .log-viewer::-webkit-scrollbar-thumb {
            background: #333;
            border-radius: 5px;
        }
        
        .log-viewer::-webkit-scrollbar-thumb:hover {
            background: #444;
        }

        .process-wrapper {
            text-align: center;
            margin-top: 25px;
        }
        
        .process-button {
            background: #aed604;
            color: #000;
            border: none;
            padding: 12px 28px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 25px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }
        
        .process-button:hover {
            background: #9bc603;
            transform: translateY(-2px);
        }
        
        .process-button:active {
            transform: translateY(0);
        }
        
        .process-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .success-message {
            background: #222;
            border: 2px solid #aed604;
            color: #aed604;
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            margin-top: 15px;
            display: inline-block;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }
        
        .success-message.show {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Mobile responsive */
        @media (max-width: 600px) {
            .log-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .log-actions {
                width: 100%;
                justify-content: center;
            }
            
            .log-viewer {
                height: 250px;
                font-size: 12px;
            }
            
            .process-button {
                padding: 10px 20px;
                font-size: 14px;
            }
        }
    </style>

    <div class="ship-today-header">
        <h2 class="ship-today-title">💌 Ship Today</h2>
        <p class="ship-today-subtitle">Process orders with tomorrow's delivery date and move them to Ship Today status</p>
    </div>

    <div class="log-card">
        <div class="log-header">
            <h3 class="log-title">📋 Process Log</h3>
            <div class="log-actions">
                <button id="clear-log" class="log-button">
                    <span>🗑️</span> Clear
                </button>
                <button id="download-log" class="log-button">
                    <span>📥</span> Download
                </button>
            </div>
        </div>
        
        <textarea id="log-preview" class="log-viewer" readonly placeholder="No logs yet..."></textarea>

        <div class="process-wrapper">
            <button id="ship-today-process" class="process-button">
                <span>🚚</span> Run Ship Today Process
            </button>
            <div id="process-success" class="success-message">✅ Process completed successfully!</div>
        </div>

        <div class="process-wrapper">
            <div id="clear-success" class="success-message">✅ Log cleared!</div>
        </div>
    </div>

    <script>
        // Load log on page load
        window.addEventListener('DOMContentLoaded', () => {
            fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=greenangel_ship_today_log')
                .then(res => res.text())
                .then(data => document.getElementById('log-preview').value = data);
        });

        function showSuccess(id) {
            const el = document.getElementById(id);
            el.classList.add('show');
            setTimeout(() => el.classList.remove('show'), 3000);
        }

        document.getElementById('ship-today-process').onclick = () => {
            const btn = document.getElementById('ship-today-process');
            const original = btn.innerHTML;
            btn.innerHTML = '<span>⏳</span> Processing...';
            btn.disabled = true;

            fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=greenangel_ship_today_run')
                .then(res => res.text())
                .then(data => {
                    document.getElementById('log-preview').value = data;
                    btn.innerHTML = original;
                    btn.disabled = false;
                    showSuccess('process-success');
                    document.getElementById('log-preview').scrollTop = document.getElementById('log-preview').scrollHeight;
                });
        };

        document.getElementById('clear-log').onclick = () => {
            if (!confirm('Are you sure you want to clear the log?')) return;
            fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=greenangel_ship_today_clear')
                .then(() => {
                    document.getElementById('log-preview').value = '';
                    showSuccess('clear-success');
                });
        };

        document.getElementById('download-log').onclick = () => {
            window.location.href = '<?php echo admin_url('admin-ajax.php'); ?>?action=greenangel_ship_today_download';
        };
    </script>
    <?php
}

// 🚚 AJAX: Run Ship Today
add_action('wp_ajax_greenangel_ship_today_run', function() {
    if (!current_user_can('manage_woocommerce')) wp_die('Permission denied');
    
    $log = "[" . current_time('mysql') . "] 💚 Manual 'Ship Today' process started.\n";
    $updated = 0; 
    $skipped = 0;
    $orders = wc_get_orders(['status' => 'processing', 'limit' => -1]);
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    
    foreach ($orders as $o) {
        $order_id = $o->get_id();
        
        // Check BOTH old and new delivery date formats
        $delivery = $o->get_meta('_delivery_date'); // NEW format with underscore
        if (!$delivery) {
            $delivery = $o->get_meta('delivery_date'); // OLD format without underscore
        }
        
        if (!$delivery) {
            $log .= "[" . current_time('mysql') . "] Order #{$order_id} skipped (no delivery date found).\n";
            $skipped++;
            continue;
        }
        
        // Parse the date - handle different possible formats
        $d = date('Y-m-d', strtotime($delivery));
        
        // Debug: Show what we're comparing
        $log .= "[DEBUG] Order #{$order_id} - Raw delivery: '$delivery', Parsed: '$d', Tomorrow: '$tomorrow'\n";
        
        if ($d == $tomorrow) {
            $o->update_status('ship-today');
            $log .= "[" . current_time('mysql') . "] Order #{$order_id} updated to 'Ship Today' (Delivery: $d)\n";
            $updated++;
        } else {
            $log .= "[" . current_time('mysql') . "] Order #{$order_id} remains 'Processing' (Delivery: $d)\n";
            $skipped++;
        }
    }
    
    $log .= "[" . current_time('mysql') . "] ✨ Process completed. Updated: $updated, Skipped: $skipped.\n";
    file_put_contents(GREENANGEL_SHIP_TODAY_LOG, $log, FILE_APPEND);
    echo $log;
    wp_die();
});

// 🔍 AJAX: Load Log
add_action('wp_ajax_greenangel_ship_today_log', function() {
    if (!current_user_can('manage_woocommerce')) wp_die('Permission denied');
    echo file_exists(GREENANGEL_SHIP_TODAY_LOG) ? file_get_contents(GREENANGEL_SHIP_TODAY_LOG) : '';
    wp_die();
});

// 🧼 AJAX: Clear Log
add_action('wp_ajax_greenangel_ship_today_clear', function() {
    if (!current_user_can('manage_woocommerce')) wp_die('Permission denied');
    file_put_contents(GREENANGEL_SHIP_TODAY_LOG, '');
    wp_die();
});

// 📥 AJAX: Download Log
add_action('wp_ajax_greenangel_ship_today_download', function() {
    if (!current_user_can('manage_woocommerce')) wp_die('Permission denied');
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="ship-today-log.txt"');
    readfile(GREENANGEL_SHIP_TODAY_LOG);
    exit;
});