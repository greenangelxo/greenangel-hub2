<?php
// 🌿 Green Angel – Angel Code Log Viewer

function greenangel_render_code_log_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'greenangel_code_logs';
    $logs = $wpdb->get_results("SELECT * FROM $table ORDER BY timestamp DESC LIMIT 50");

    // ─── Styles ────────────────────────────────
    echo '<style>
      .log-table {
        background-color: #222;
        border-radius: 14px;
        overflow: hidden;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 40px;
        width: 100%;
        font-family: "Poppins", sans-serif !important;
      }
      .log-table th, .log-table td {
        border: none;
        padding: 12px;
        text-align: center;
        vertical-align: middle;
      }
      .log-table td {
        background: #222;
        color: #fff;
        padding: 14px 16px;
      }
      .log-table thead {
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
      .log-controls {
        display: flex;
        gap: 12px;
        margin-top: 15px;
        align-items: center;
      }
      .log-bubble-button {
        background: #aed604;
        color: #222;
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
        background: #c5e915;
        transform: translateY(-2px);
      }
      .empty-log {
        text-align: center;
        padding: 30px;
        background: #222;
        border-radius: 14px;
        margin-top: 20px;
        color: #fff;
      }
    </style>';

    // ─── Output ────────────────────────────────
    echo '<h3 style="font-size:20px; margin-top:60px;">📜 Angel Code Usage Log</h3>';
    echo '<div class="log-controls">';
    echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
    echo '<input type="hidden" name="action" value="greenangel_clear_usage_log">';
    echo '<button type="submit" class="log-bubble-button">🗑 Clear Log</button>';
    echo '</form>';
    echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
    echo '<input type="hidden" name="action" value="greenangel_download_usage_log">';
    echo '<button type="submit" class="log-bubble-button">📥 Download CSV</button>';
    echo '</form>';
    echo '</div>';
    if (!$logs) {
        echo '<div class="empty-log">';
        echo '<p style="font-size:16px;">No registrations logged yet 🌙<br><span style="opacity:0.6;">This will populate once someone registers using a valid code.</span></p>';
        echo '</div>';
        return;
    }

    echo '<table class="log-table">';
    echo '<thead><tr>';
    echo '<th><span class="header-bubble">Email</span></th>';
    echo '<th><span class="header-bubble">Code Used</span></th>';
    echo '<th><span class="header-bubble">Timestamp</span></th>';
    echo '</tr></thead><tbody>';

    foreach ($logs as $log) {
        echo '<tr>';
        echo '<td>' . esc_html($log->email ?: '—') . '</td>';
        echo '<td style="font-weight:600;">' . esc_html($log->code_used) . '</td>';
        echo '<td>' . esc_html(date('Y-m-d H:i', strtotime($log->timestamp))) . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
}
