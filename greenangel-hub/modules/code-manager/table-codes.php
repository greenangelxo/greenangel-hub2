<?php
defined( 'ABSPATH' ) || exit;
// 🌿 Green Angel – Angel Code Table
require_once plugin_dir_path(__FILE__) . 'manage-code.php';

function greenangel_render_code_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'greenangel_codes';
    $codes = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");

    // ─── Styles ────────────────────────────────
    echo '<style>
      .code-table {
        background-color: #222;
        border-radius: 14px;
        overflow: hidden;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 20px;
        width: 100%;
        font-family: "Poppins", sans-serif !important;
      }
      .code-table th, .code-table td {
        border: none;
        padding: 12px;
        text-align: center;
        vertical-align: middle;
      }
      .code-table td {
        background: #222;
        color: #fff;
        padding: 14px 16px;
      }
      .code-table thead {
        background: #222;
        height: 50px;
      }
      .header-bubble {
        display: inline-block;
        background: #aed604;
        color: #222;
        padding: 6px 12px;
        border-radius: 16px;
        font-weight: 500;
        font-size: 12px;
        white-space: nowrap;
      }
      .empty-codes {
        text-align: center;
        padding: 30px;
        background: #222;
        border-radius: 14px;
        margin-top: 20px;
        color: #fff;
      }
      .status-toggle {
        background: #aed604;
        color: #222;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 500;
        font-size: 13px;
        font-family: "Poppins", sans-serif !important;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s ease-in-out;
      }
      .status-toggle:hover { transform: translateY(-2px); box-shadow: 0 0 8px rgba(174,214,4,0.6); }
      .status-toggle.inactive {
        background: #222;
        color: #ffffff;
        border: 1px solid rgba(255,0,0,0.4);
      }
      .status-toggle.inactive:hover { box-shadow: 0 0 8px rgba(255,0,0,0.6); }

      /* Actions layout */
      .actions-wrap {
        display: flex;
        gap: 10px;
        justify-content: center;
        flex-wrap: wrap;
      }

      .delete-btn {
        background: #ff4444;
        color: #ffffff;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 500;
        font-size: 13px;
        font-family: "Poppins", sans-serif !important;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s ease-in-out;
      }
      .delete-btn:hover {
        background: #e63737;
        box-shadow: 0 0 8px rgba(255,68,68,0.6);
        transform: translateY(-2px);
      }
   </style>';

    // ─── Output ────────────────────────────────
    if (!$codes) {
        echo '<div class="empty-codes">';
        echo '<p style="font-size:16px;">No angel codes yet 🌙<br><span style="opacity:0.6;">Create your first invite code below.</span></p>';
        echo '</div>';
        return;
    }

    echo '<table class="code-table">';
    echo '<thead><tr>
      <th><span class="header-bubble">Code</span></th>
      <th><span class="header-bubble">Label</span></th>
      <th><span class="header-bubble">Type</span></th>
      <th><span class="header-bubble">Status</span></th>
      <th><span class="header-bubble">Expires</span></th>
      <th><span class="header-bubble">Created</span></th>
      <th><span class="header-bubble">Actions</span></th>
    </tr></thead>';

    echo '<tbody>';
    foreach ($codes as $code) {
        echo '<tr>';
        echo '<td style="font-weight:600;">' . esc_html($code->code) . '</td>';
        echo '<td>' . esc_html($code->label ?: '—') . '</td>';
        echo '<td>' . esc_html(ucfirst($code->type)) . '</td>';

        // Status
        echo '<td>';
        if ($code->active) {
            echo '<span style="color:#aed604; font-weight:500;">Active</span>';
        } else {
            echo '<span style="color:#f39c12; opacity:0.7; font-weight:500;">Inactive</span>';
        }
        echo '</td>';

        // Expiry
        echo '<td>';
        if ($code->expires_at) {
            $date = new DateTime($code->expires_at);
            $now = new DateTime();
            $expired = $date < $now;
            if ($expired) {
                echo '<span style="color:#e74c3c;">Expired</span>';
            } else {
                echo esc_html($date->format('Y-m-d'));
            }
        } else {
            echo '—';
        }
        echo '</td>';

        // Created
        echo '<td>' . esc_html(date('Y-m-d', strtotime($code->created_at))) . '</td>';

        // Actions
        $toggle_url = wp_nonce_url(
            admin_url("admin-post.php?action=greenangel_toggle_code&id={$code->id}"),
            'greenangel_toggle_code'
        );
        $delete_url = wp_nonce_url(
            admin_url("admin-post.php?action=greenangel_delete_code&id={$code->id}"),
            'greenangel_delete_code'
        );
        echo '<td><div class="actions-wrap">';
        $active_class = $code->active ? 'status-toggle' : 'status-toggle inactive';
        echo '<a href="'.esc_url($toggle_url).'" class="'.$active_class.'" title="Toggle Active">';
        echo $code->active ? '🟢 Active' : '⚫ Inactive';
        echo '</a>';
        echo '<a href="'.esc_url($delete_url).'" class="delete-btn" onclick="return confirm(\'Are you sure you want to delete this code?\')">';
        echo '🗑 Delete';
        echo '</a>';
        echo '</div></td>';

        echo '</tr>';
    }
    echo '</tbody></table>';
}