<?php
// 🌿 Green Angel — Toggle & Delete Angel Codes

add_action('admin_post_greenangel_toggle_code', function () {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Nope');
    }

    $redirect = admin_url('admin.php?page=greenangel-hub&tab=angel-codes');
    $id = intval($_GET['id'] ?? 0);

    if (!$id) {
        wp_redirect(add_query_arg('code', 'invalid', $redirect));
        exit;
    }

    if (!check_admin_referer('greenangel_toggle_code', '_wpnonce', false)) {
        wp_redirect(add_query_arg('nonce', 'failed', $redirect));
        exit;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'greenangel_codes';

    $current = $wpdb->get_var($wpdb->prepare("SELECT active FROM $table WHERE id = %d", $id));
    $new = $current ? 0 : 1;

    $wpdb->update($table, ['active' => $new], ['id' => $id]);
    wp_redirect($redirect);
    exit;
});

add_action('admin_post_greenangel_delete_code', function () {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Nope');
    }

    $redirect = admin_url('admin.php?page=greenangel-hub&tab=angel-codes');
    $id = intval($_GET['id'] ?? 0);

    if (!$id) {
        wp_redirect(add_query_arg('code', 'invalid', $redirect));
        exit;
    }

    if (!check_admin_referer('greenangel_delete_code', '_wpnonce', false)) {
        wp_redirect(add_query_arg('nonce', 'failed', $redirect));
        exit;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'greenangel_codes';

    $wpdb->delete($table, ['id' => $id]);
    wp_redirect($redirect);
    exit;
});