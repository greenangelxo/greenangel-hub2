<?php
// Green Angel — Toggle & Delete Angel Codes

add_action('admin_post_greenangel_toggle_code', function () {
    if (!current_user_can('manage_woocommerce') || empty($_GET['id'])) return;

    global $wpdb;
    $id = intval($_GET['id']);
    $table = $wpdb->prefix . 'greenangel_codes';

    $current = $wpdb->get_var("SELECT active FROM $table WHERE id = $id");
    $new = $current ? 0 : 1;

    $wpdb->update($table, ['active' => $new], ['id' => $id]);
    wp_redirect(admin_url('admin.php?page=greenangel-hub&tab=angel-codes'));
    exit;
});

add_action('admin_post_greenangel_delete_code', function () {
    if (!current_user_can('manage_woocommerce') || empty($_GET['id'])) return;

    global $wpdb;
    $id = intval($_GET['id']);
    $table = $wpdb->prefix . 'greenangel_codes';

    $wpdb->delete($table, ['id' => $id]);
    wp_redirect(admin_url('admin.php?page=greenangel-hub&tab=angel-codes'));
    exit;
});