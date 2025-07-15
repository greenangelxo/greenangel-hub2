<?php
/**
 * 🛠️ MAINTENANCE FUNCTIONS - Helper Utilities
 * Clean, reusable functions for our ICONIC LED maintenance system
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 🔍 Check if maintenance mode is enabled
 */
function greenangel_is_maintenance_enabled() {
    return get_option('greenangel_maintenance_enabled', false);
}

/**
 * 🚪 Check emergency backdoor access
 */
function greenangel_check_emergency_access() {
    // Check URL parameter
    if (isset($_GET['iamjess']) && $_GET['iamjess'] === 'true') {
        setcookie('greenangel_emergency_access', wp_hash('jess_emergency_' . date('Y-m-d')), time() + 3600, '/');
        return true;
    }
    
    // Check existing emergency cookie
    if (isset($_COOKIE['greenangel_emergency_access']) && 
        $_COOKIE['greenangel_emergency_access'] === wp_hash('jess_emergency_' . date('Y-m-d'))) {
        return true;
    }
    
    return false;
}

/**
 * 🌐 Detect webhook/callback requests (keep business running!)
 */
function greenangel_is_webhook_request() {
    $webhook_paths = array(
        '/wp-json/',
        '/wc-api/',
        '?wc-api=',
        '/webhook',
        '/callback',
        '/stripe',
        '/paypal',
        '/crypto',
        '/payment'
    );
    
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    
    foreach ($webhook_paths as $path) {
        if (strpos($request_uri, $path) !== false) {
            return true;
        }
    }
    
    // Check for common webhook user agents
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $webhook_agents = array('stripe', 'paypal', 'webhook', 'coinbase', 'crypto');
    
    foreach ($webhook_agents as $agent) {
        if (stripos($user_agent, $agent) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * 💬 Get custom maintenance message with ICONIC LED defaults
 */
function greenangel_get_maintenance_message() {
    $custom = get_option('greenangel_maintenance_message', '');
    
    if (!empty($custom)) {
        return $custom;
    }
    
    // Electric LED magical messages
    $messages = array(
        "We're polishing diamonds and making them extra sparkly. The magic is almost ready! 💎✨",
        "Currently wrapping heartbreakerz in the most beautiful packaging. Your order will be worth the wait! 💚🎁",
        "Breaking diamonds into perfect mini treasures. Each piece crafted with love! 💎⚡",
        "Tending to our magical greenery and making sure everything grows perfectly! 🌱💚",
        "Cooking up some incredible magic in our celestial kitchen. Almost ready to serve! 🔥✨",
        "Sprinkling that signature green angel dust on everything. Pure magic incoming! 🌟💚",
        "Charging our crystal cores with next-level energy. The vibes are immaculate! ⚡💫",
        "Weaving rainbow threads through every diamond. Creating something extraordinary! 🌈💎",
        "Mixing celestial potions that will blow your mind. The alchemy is real! 🧪✨",
        "Calibrating the sparkle matrix to maximum brilliance. Prepare for magic! 💫🔥"
    );
    
    return $messages[array_rand($messages)];
}

/**
 * ⏰ Get estimated return time
 */
function greenangel_get_maintenance_eta() {
    return get_option('greenangel_maintenance_eta', '');
}

/**
 * 📊 Get maintenance statistics
 */
function greenangel_get_maintenance_stats() {
    $enabled_time = get_option('greenangel_maintenance_enabled_time', 0);
    $total_downtime = get_option('greenangel_maintenance_total_downtime', 0);
    
    return array(
        'enabled_time' => $enabled_time,
        'total_downtime' => $total_downtime,
        'current_session' => $enabled_time > 0 ? (time() - $enabled_time) : 0
    );
}

/**
 * 📝 Log maintenance events
 */
function greenangel_log_maintenance_event($event, $details = '') {
    $log_entry = array(
        'timestamp' => current_time('mysql'),
        'event' => $event,
        'details' => $details,
        'user' => get_current_user_id()
    );
    
    $logs = get_option('greenangel_maintenance_logs', array());
    array_unshift($logs, $log_entry);
    
    // Keep only last 50 entries
    $logs = array_slice($logs, 0, 50);
    
    update_option('greenangel_maintenance_logs', $logs);
}

/**
 * 🔄 Toggle maintenance mode with logging
 */
function greenangel_toggle_maintenance_mode() {
    $enabled = greenangel_is_maintenance_enabled();
    $new_state = !$enabled;
    
    update_option('greenangel_maintenance_enabled', $new_state);
    
    if ($new_state) {
        update_option('greenangel_maintenance_enabled_time', time());
        greenangel_log_maintenance_event('enabled', 'Iconic LED maintenance mode activated - prepare for magic! ✨');
    } else {
        $enabled_time = get_option('greenangel_maintenance_enabled_time', 0);
        if ($enabled_time > 0) {
            $session_duration = time() - $enabled_time;
            $total_downtime = get_option('greenangel_maintenance_total_downtime', 0);
            update_option('greenangel_maintenance_total_downtime', $total_downtime + $session_duration);
        }
        delete_option('greenangel_maintenance_enabled_time');
        greenangel_log_maintenance_event('disabled', 'Site is back live with electric LED magic! 🌟');
    }
    
    return $new_state;
}

/**
 * 🧹 Clean up old maintenance data
 */
function greenangel_cleanup_maintenance_data() {
    // Clean up old logs (older than 30 days)
    $logs = get_option('greenangel_maintenance_logs', array());
    $cutoff = strtotime('-30 days');
    
    $logs = array_filter($logs, function($log) use ($cutoff) {
        return strtotime($log['timestamp']) > $cutoff;
    });
    
    update_option('greenangel_maintenance_logs', $logs);
}

// Schedule cleanup weekly
if (!wp_next_scheduled('greenangel_maintenance_cleanup')) {
    wp_schedule_event(time(), 'weekly', 'greenangel_maintenance_cleanup');
}

add_action('greenangel_maintenance_cleanup', 'greenangel_cleanup_maintenance_data');

/**
 * 🎨 Get random emoji for extra magic
 */
function greenangel_get_random_emoji() {
    $emojis = array('💎', '✨', '🔥', '💚', '🌟', '⚡', '🎭', '🦄', '👑', '💫');
    return $emojis[array_rand($emojis)];
}

/**
 * 🌈 Generate dynamic CSS for time-based effects
 */
function greenangel_get_dynamic_maintenance_css() {
    $hour = date('H');
    $css = '';
    
    // Time-based color variations
    if ($hour >= 22 || $hour <= 6) {
        // Night mode - darker and more mysterious
        $css .= '.background-container { filter: brightness(0.8) hue-rotate(15deg); }';
    } elseif ($hour >= 6 && $hour <= 9) {
        // Dawn mode - warmer tones
        $css .= '.background-container { filter: brightness(1.1) hue-rotate(30deg); }';
    }
    
    return $css;
}
?>