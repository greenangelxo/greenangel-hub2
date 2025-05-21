<?php
// 🌿 Green Angel Hub – Affiliates Subpage

// Register submenu page so it's accessible from the WP menu
add_action('admin_menu', function () {
    add_submenu_page(
        'greenangel-hub',
        'Affiliates',
        'Affiliates',
        'manage_woocommerce',
        'greenangel-affiliates',
        'greenangel_render_affiliates_page'
    );
});

function greenangel_fetch_affiliates() {
    if (function_exists('slicewp_get_affiliates')) {
        return slicewp_get_affiliates(['number' => -1, 'status' => 'active']);
    }
    global $wpdb;
    $table = $wpdb->prefix . 'slicewp_affiliates';
    return $wpdb->get_results("SELECT * FROM $table WHERE status = 'active'");
}

function greenangel_render_affiliates_page() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Permission denied');
    }

    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $affiliates = greenangel_fetch_affiliates();

    if ($search) {
        $search_lc = strtolower($search);
        $affiliates = array_filter($affiliates, function($aff) use ($search_lc) {
            $user = get_user_by('ID', $aff->user_id);
            if (!$user) return false;
            $name = $user->display_name;
            $email = $user->user_email;
            return (stripos($name, $search_lc) !== false) || (stripos($email, $search_lc) !== false);
        });
    }

    echo '<div class="affiliate-wrap">';
    echo '<div class="title-bubble">💚 Affiliates</div>';

    $is_tab = (isset($_GET['page']) && $_GET['page'] === 'greenangel-hub');
    echo '<form method="get" class="search-bar">';
    if ($is_tab) {
        echo '<input type="hidden" name="page" value="greenangel-hub">';
        echo '<input type="hidden" name="tab" value="affiliates">';
    } else {
        echo '<input type="hidden" name="page" value="greenangel-affiliates">';
    }
    echo '<input type="text" name="s" value="' . esc_attr($search) . '" placeholder="Search affiliates...">';
    echo '<button type="submit">Search</button>';
    echo '</form>';

    echo '<table class="aff-table">';
    echo '<thead><tr>';
    echo '<th><span class="header-bubble">Affiliate</span></th>';
    echo '<th><span class="header-bubble">Email</span></th>';
    echo '<th><span class="header-bubble">Referral Link</span></th>';
    echo '<th><span class="header-bubble">Letter</span></th>';
    echo '</tr></thead><tbody>';

    foreach ($affiliates as $aff) {
        $user = get_user_by('ID', $aff->user_id);
        if (!$user) continue;
        $name = $user->display_name ?: $user->user_email;
        $email = $user->user_email;
        $slug = get_user_meta($user->ID, 'slice_referral_slug', true);
        if (empty($slug) && isset($aff->slug)) $slug = $aff->slug;
        $ref_url = home_url('/ref/' . $slug);
        $print_url = esc_url(plugins_url('../generate-affiliate-letter.php', __FILE__) . '?affiliate_id=' . intval($aff->affiliate_id));
        echo '<tr>';
        echo '<td>' . esc_html($name) . '</td>';
        echo '<td>' . esc_html($email) . '</td>';
        echo '<td><code>' . esc_html($ref_url) . '</code></td>';
        echo '<td><a class="letter-btn" href="' . $print_url . '" target="_blank">Print</a></td>';
        echo '</tr>';
    }

    echo '</tbody></table></div>';

    echo '<style>
        .affiliate-wrap {font-family:"Poppins",sans-serif!important;}
        .search-bar {margin-bottom:20px;}
        .search-bar input[type=text] {padding:8px 12px;border-radius:20px;border:1px solid rgba(174,214,4,0.3);background:#222;color:#fff;}
        .search-bar button {background:#aed604;color:#222;border:none;padding:8px 16px;border-radius:20px;font-weight:500;cursor:pointer;margin-left:6px;}
        .aff-table {width:100%;border-collapse:separate;border-spacing:0;border-radius:14px;overflow:hidden;margin-top:10px;font-family:"Poppins",sans-serif!important;}
        .aff-table th,.aff-table td {padding:12px 14px;text-align:center;}
        .aff-table th {background:#222;color:#aed604;}
        .aff-table td {background:#222;color:#fff;}
        .aff-table tbody tr:hover td {background:#2a2a2a;}
        .header-bubble {background:#aed604;color:#222;padding:6px 12px;border-radius:16px;font-weight:500;font-size:12px;white-space:nowrap;}
        .letter-btn {background:#aed604;color:#222;border-radius:20px;padding:6px 12px;text-decoration:none;display:inline-block;font-size:13px;transition:.2s;}
        .letter-btn:hover {transform:scale(1.05);color:#222;}
        .aff-table code {background:rgba(255,255,255,0.1);padding:6px 8px;border-radius:8px;display:inline-block;color:#aed604;}
    </style>';
}
