<?php
// 🌿 Green Angel – Angel Code Manager Tab
require_once plugin_dir_path(__FILE__) . 'table-codes.php';
require_once plugin_dir_path(__FILE__) . 'form-add-code.php';
require_once plugin_dir_path(__FILE__) . 'table-logs.php';
require_once plugin_dir_path(__FILE__) . 'table-fails.php';
require_once plugin_dir_path(__FILE__) . 'handle-registration.php';
require_once plugin_dir_path(__FILE__) . 'registration-hooks.php';
require_once plugin_dir_path(__FILE__) . 'manage-logs.php';

function greenangel_render_angel_codes_tab() {
    echo '<div class="angel-codes-wrapper">';
    echo '<h2 style="margin-top:0; font-size:24px;">🪽 Angel Code Manager</h2>';
    echo '<p>Here you can manage your invite-only access system, view all active codes, and track usage logs.</p>';
    greenangel_render_code_table();
    greenangel_render_add_code_form();
    greenangel_render_code_log_table(); // 👈 new log viewer
    greenangel_render_failed_code_log(); // 👈 failed attempts log
    echo '</div>';
}

// ✅ Handle Angel Code form submission
add_action('admin_post_greenangel_add_angel_code', function () {
    if (!current_user_can('manage_woocommerce')) return;
    global $wpdb;
    $table = $wpdb->prefix . 'greenangel_codes';
    $code       = sanitize_text_field($_POST['code'] ?? '');
    $label      = sanitize_text_field($_POST['label'] ?? '');
    $type       = sanitize_text_field($_POST['type'] ?? 'promo');
    $expires_at = sanitize_text_field($_POST['expires_at'] ?? '');
    $active     = intval($_POST['active'] ?? 1);
    if (!$code) {
        wp_redirect(admin_url('admin.php?page=greenangel-hub&tab=angel-codes&error=missing_code'));
        exit;
    }
    $wpdb->insert($table, [
        'code'       => $code,
        'label'      => $label,
        'type'       => $type,
        'expires_at' => $expires_at ?: null,
        'active'     => $active,
        'created_at' => current_time('mysql'),
        'updated_at' => current_time('mysql'),
    ]);
    wp_redirect(admin_url('admin.php?page=greenangel-hub&tab=angel-codes&added=1'));
    exit;
});