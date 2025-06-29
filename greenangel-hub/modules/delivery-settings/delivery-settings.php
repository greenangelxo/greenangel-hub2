<?php

// 🌿 Green Angel – Delivery Settings Module

// ─────────────────────────────────────────────────────────────
// Enqueue Flatpickr JS + CSS
add_action('admin_enqueue_scripts', function($hook) {
    if ($hook !== 'toplevel_page_greenangel-hub') return;
    wp_enqueue_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', [], null, true);
    wp_enqueue_style('flatpickr-style', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
});

// ─────────────────────────────────────────────────────────────
// Save Logic
add_action('admin_init', function() {
    if ( ! current_user_can('manage_woocommerce') ) {
        return;
    }

    if ( isset($_POST['greenangel_delivery_settings_nonce']) && wp_verify_nonce($_POST['greenangel_delivery_settings_nonce'], 'greenangel_save_delivery_days') ) {
        $valid_days = ['tue', 'wed', 'thu', 'fri', 'sat'];
        $selected = isset($_POST['delivery_days']) ? array_intersect($valid_days, (array) $_POST['delivery_days']) : [];
        update_option('greenangel_delivery_days', $selected);

        // Blackout Dates
        if (isset($_POST['blackout_dates'])) {
            $raw_input = wp_unslash($_POST['blackout_dates']);
            $raw_dates = explode(',', $raw_input);
            $valid_dates = [];
            foreach ($raw_dates as $date) {
                $trimmed = trim($date);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $trimmed)) {
                    $valid_dates[] = $trimmed;
                }
            }
            update_option('greenangel_blackout_dates', $valid_dates);
        }

        // Max Advance Days
        if (isset($_POST['max_advance_days'])) {
            $max_days = max(1, min(60, intval($_POST['max_advance_days'])));
            update_option('greenangel_max_advance_days', $max_days);
        }

        // Cutoff Time
        if (isset($_POST['cutoff_time'])) {
            $time = preg_match('/^\d{2}:\d{2}$/', $_POST['cutoff_time']) ? $_POST['cutoff_time'] : '09:00';
            update_option('greenangel_cutoff_time', $time);
        }

        // Daily Delivery Limit
        if (isset($_POST['daily_delivery_limit'])) {
            $limit = max(1, min(200, intval($_POST['daily_delivery_limit'])));
            update_option('greenangel_daily_delivery_limit', $limit);
        }
    }
});

// ─────────────────────────────────────────────────────────────
// Delivery Settings Tab Renderer
function greenangel_render_delivery_settings_tab() {
    $option_key = 'greenangel_delivery_days';
    $saved_days = get_option($option_key, ['tue', 'wed', 'thu', 'fri', 'sat']);

    $all_days = [
        'tue' => 'Tuesday',
        'wed' => 'Wednesday',
        'thu' => 'Thursday',
        'fri' => 'Friday',
        'sat' => 'Saturday',
    ];
    ?>
    
    <style>
        /* Delivery Settings Styles - Angel Hub Dark Theme */
        .delivery-settings-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .delivery-settings-title {
            font-size: 24px;
            font-weight: 600;
            color: #aed604;
            margin: 0 0 8px 0;
        }
        
        .delivery-settings-subtitle {
            font-size: 14px;
            color: #888;
            margin: 0;
        }
        
        .delivery-form {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .setting-group {
            background: #1a1a1a;
            border: 2px solid #333;
            border-radius: 14px;
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .setting-label {
            display: block;
            font-size: 16px;
            font-weight: 600;
            color: #aed604;
            margin-bottom: 15px;
        }
        
        .setting-description {
            font-size: 13px;
            color: #888;
            margin-top: 10px;
            line-height: 1.5;
        }
        
        /* Day checkboxes */
        .days-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .day-checkbox {
            background: #222;
            border: 2px solid #333;
            border-radius: 10px;
            padding: 12px;
            text-align: center;
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
        }
        
        .day-checkbox:hover {
            border-color: #444;
            transform: translateY(-1px);
        }
        
        .day-checkbox input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }
        
        .day-checkbox input[type="checkbox"]:checked ~ .day-label {
            color: #222;
        }
        
        .day-checkbox input[type="checkbox"]:checked ~ .day-label:before {
            content: '✓ ';
            font-weight: 700;
        }
        
        .day-checkbox input[type="checkbox"]:checked {
            background: #aed604;
        }
        
        .day-checkbox input[type="checkbox"]:checked + .day-label {
            background: #aed604;
            color: #222;
        }
        
        .day-label {
            display: block;
            color: #aed604;
            font-weight: 500;
            font-size: 14px;
            padding: 5px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        /* Input fields */
        .angel-input {
            background: #0a0a0a;
            border: 2px solid #333;
            color: #fff;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            width: 100%;
            transition: all 0.2s ease;
            outline: none;
        }
        
        .angel-input:focus {
            border-color: #aed604;
        }
        
        .angel-input[type="number"] {
            width: 100px;
        }
        
        .angel-input[type="time"] {
            width: 150px;
        }
        
        /* Flatpickr overrides */
        .flatpickr-calendar {
            background: #222 !important;
            border: 2px solid #333 !important;
            border-radius: 10px !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5) !important;
        }
        
        .flatpickr-day {
            color: #aed604 !important;
            border-radius: 8px !important;
        }
        
        .flatpickr-day:hover {
            background: #333 !important;
            border-color: #aed604 !important;
        }
        
        .flatpickr-day.selected {
            background: #aed604 !important;
            color: #222 !important;
        }
        
        .flatpickr-months {
            background: #1a1a1a !important;
        }
        
        .flatpickr-current-month {
            color: #aed604 !important;
        }
        
        /* Submit button */
        .save-button-wrapper {
            text-align: center;
            margin-top: 30px;
        }
        
        .angel-submit {
            background: #aed604;
            color: #222;
            border: none;
            padding: 14px 32px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .angel-submit:hover {
            background: #9bc603;
            transform: translateY(-2px);
        }
        
        .angel-submit:active {
            transform: translateY(0);
        }
        
        /* Mobile responsive */
        @media (max-width: 600px) {
            .delivery-form {
                padding: 0 10px;
            }
            
            .setting-group {
                padding: 20px 15px;
            }
            
            .days-grid {
                grid-template-columns: 1fr;
            }
            
            .angel-input[type="number"],
            .angel-input[type="time"] {
                width: 100%;
            }
            
            .delivery-settings-title {
                font-size: 20px;
            }
        }
        
        /* Hide default WordPress styles */
        .delivery-form .form-table {
            display: none;
        }
    </style>
    
    <div class="delivery-settings-header">
        <h2 class="delivery-settings-title">🚚 Delivery Settings</h2>
        <p class="delivery-settings-subtitle">Configure your delivery days, limits, and blackout dates</p>
    </div>
    
    <form method="post" class="delivery-form">
        <input type="hidden" name="greenangel_delivery_settings_nonce" value="<?php echo wp_create_nonce('greenangel_save_delivery_days'); ?>">
        
        <!-- Delivery Days -->
        <div class="setting-group">
            <label class="setting-label">📅 Eligible Delivery Days</label>
            <div class="days-grid">
                <?php foreach ($all_days as $slug => $label): ?>
                    <label class="day-checkbox">
                        <input type="checkbox" name="delivery_days[]" value="<?php echo esc_attr($slug); ?>" <?php echo in_array($slug, $saved_days) ? 'checked' : ''; ?>>
                        <span class="day-label"><?php echo esc_html($label); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <p class="setting-description">Select which days of the week you offer delivery service</p>
        </div>
        
        <!-- Blackout Dates -->
        <div class="setting-group">
            <label class="setting-label">🚫 Blackout Dates</label>
            <input type="text" 
                   id="blackout_dates" 
                   name="blackout_dates" 
                   value="<?php echo esc_attr(implode(',', get_option('greenangel_blackout_dates', []))); ?>" 
                   class="angel-input" 
                   placeholder="Click to select dates">
            <p class="setting-description">Choose specific dates where deliveries shouldn't be available (e.g. holidays, breaks)</p>
        </div>
        
        <!-- Max Advance Days -->
        <div class="setting-group">
            <label class="setting-label">📆 Max Advance Days</label>
            <input type="number" 
                   name="max_advance_days" 
                   value="<?php echo esc_attr(get_option('greenangel_max_advance_days', 14)); ?>" 
                   min="1" 
                   max="60" 
                   class="angel-input">
            <p class="setting-description">How many days in advance a delivery can be booked (e.g. 14 = 2 weeks from today)</p>
        </div>
        
        <!-- Daily Delivery Limit -->
        <div class="setting-group">
            <label class="setting-label">📦 Daily Delivery Limit</label>
            <input type="number" 
                   name="daily_delivery_limit" 
                   value="<?php echo esc_attr(get_option('greenangel_daily_delivery_limit', 20)); ?>" 
                   min="1" 
                   max="200" 
                   class="angel-input">
            <p class="setting-description">Maximum number of parcels that can be delivered per day. When reached, that date becomes unavailable</p>
        </div>
        
        <!-- Cutoff Time -->
        <div class="setting-group">
            <label class="setting-label">⏰ Cutoff Time</label>
            <input type="time" 
                   name="cutoff_time" 
                   value="<?php echo esc_attr(get_option('greenangel_cutoff_time', '09:00')); ?>" 
                   class="angel-input">
            <p class="setting-description">Orders placed after this time will shift the earliest available delivery day forward by 1</p>
        </div>
        
        <div class="save-button-wrapper">
            <button type="submit" class="angel-submit">Save Delivery Settings</button>
        </div>
    </form>
    
    <!-- Flatpickr Initialization -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        flatpickr("#blackout_dates", {
            mode: "multiple",
            dateFormat: "Y-m-d",
            allowInput: true,
            defaultDate: document.getElementById("blackout_dates").value.split(",").filter(Boolean),
            theme: "dark"
        });
    });
    </script>
    
    <?php
}

// ✅ Don't remove these — they're vital
require_once __DIR__ . '/checkout-hook.php';