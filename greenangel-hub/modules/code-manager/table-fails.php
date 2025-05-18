<?php
// 🌿 Green Angel – Failed Code Attempt Log

function greenangel_render_failed_code_log() {
    global $wpdb;
    $table = $wpdb->prefix . 'greenangel_failed_code_attempts';
    $logs = $wpdb->get_results("SELECT * FROM $table ORDER BY timestamp DESC LIMIT 50");

    echo '<div class="log-section">
    <style>
      .fail-log-table {
        background-color: #222;
        border-radius: 14px;
        overflow: hidden;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 40px;
        width: 100%;
        font-family: "Poppins", sans-serif !important;
      }
      .fail-log-table th, .fail-log-table td {
        border: none;
        padding: 12px;
        text-align: center;
        vertical-align: middle;
      }
      .fail-log-table td {
        background: #222;
        color: #fff;
        padding: 14px 16px;
      }
      .fail-log-table thead {
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

    echo '<div class="title-bubble" style="margin-top:40px;">🚫 Failed Angel Code Attempts</div>';
    echo '<div class="log-controls">';
    echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
    echo '<input type="hidden" name="action" value="greenangel_clear_failed_log">';
    echo '<button type="submit" class="log-bubble-button">🗑 Clear Log</button>';
    echo '</form>';
    echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
    echo '<input type="hidden" name="action" value="greenangel_download_failed_log">';
    echo '<button type="submit" class="log-bubble-button">📥 Download CSV</button>';
    echo '</form>';
    echo '</div>';

    if (!$logs) {
        echo '<div class="empty-log">';
        echo '<p style="font-size:16px;">No failed attempts yet 💤<br><span style="opacity:0.6;">This will log whenever someone tries an invalid or inactive Angel Code.</span></p>';
        echo '</div>';
        return;
    }

    echo '<table class="fail-log-table">';
    echo '<thead><tr>';
    echo '<th><span class="header-bubble">Email</span></th>';
    echo '<th><span class="header-bubble">Code Tried</span></th>';
    echo '<th><span class="header-bubble">IP Address</span></th>';
    echo '<th><span class="header-bubble">Time</span></th>';
    echo '</tr></thead><tbody>';

    foreach ($logs as $log) {
        echo '<tr>';
        echo '<td>' . esc_html($log->email ?: '—') . '</td>';
        echo '<td>' . esc_html($log->code_tried) . '</td>';
        echo '<td>' . esc_html($log->ip_address ?: '—') . '</td>';
        echo '<td>' . esc_html(date('Y-m-d H:i', strtotime($log->timestamp))) . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
    echo '</div>';
}