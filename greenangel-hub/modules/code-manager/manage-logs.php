<?php
// 🌿 Green Angel — Log Management for Angel Codes

function esc_csv($value) {
    $value = str_replace('"', '""', $value);
    return str_replace(["\n", "\r"], '', $value);
}
// Clear usage log
add_action('admin_post_greenangel_clear_usage_log', function () {
    if (!current_user_can('manage_woocommerce')) wp_die('Permission denied');
    check_admin_referer('greenangel_clear_usage_log');
    global $wpdb;
    $wpdb->query('TRUNCATE TABLE ' . $wpdb->prefix . 'greenangel_code_logs');
    wp_redirect(admin_url('admin.php?page=greenangel-hub&tab=angel-codes&log_cleared=1'));
    exit;
});

// Download usage log as CSV
add_action('admin_post_greenangel_download_usage_log', function () {
    if (!current_user_can('manage_woocommerce')) wp_die('Permission denied');
    check_admin_referer('greenangel_download_usage_log');
    global $wpdb;
    $table = $wpdb->prefix . 'greenangel_code_logs';
    $logs  = $wpdb->get_results("SELECT * FROM $table ORDER BY timestamp DESC");
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="angel-code-usage-log.csv"');
    echo "email,code_used,timestamp\n";
    foreach ($logs as $log) {
        echo esc_csv($log->email) . ',' . esc_csv($log->code_used) . ',' . esc_csv($log->timestamp) . "\n";
    }
    exit;
});

// Clear failed attempts log
add_action('admin_post_greenangel_clear_failed_log', function () {
    if (!current_user_can('manage_woocommerce')) wp_die('Permission denied');
    check_admin_referer('greenangel_clear_failed_log');
    global $wpdb;
    $wpdb->query('TRUNCATE TABLE ' . $wpdb->prefix . 'greenangel_failed_code_attempts');
    wp_redirect(admin_url('admin.php?page=greenangel-hub&tab=angel-codes&failed_cleared=1'));
    exit;
});

// Download failed attempts log as CSV
add_action('admin_post_greenangel_download_failed_log', function () {
    if (!current_user_can('manage_woocommerce')) wp_die('Permission denied');
    check_admin_referer('greenangel_download_failed_log');
    global $wpdb;
    $table = $wpdb->prefix . 'greenangel_failed_code_attempts';
    $logs  = $wpdb->get_results("SELECT * FROM $table ORDER BY timestamp DESC");
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="angel-code-failed-log.csv"');
    echo "email,code_tried,ip_address,timestamp\n";
    foreach ($logs as $log) {
        echo esc_csv($log->email) . ',' . esc_csv($log->code_tried) . ',' . esc_csv($log->ip_address) . ',' . esc_csv($log->timestamp) . "\n";
    }
    exit;
});
