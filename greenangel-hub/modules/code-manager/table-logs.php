<?php
defined( 'ABSPATH' ) || exit;
// 🌿 Green Angel – Angel Code Usage Log
function greenangel_render_code_log_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'greenangel_code_logs';
    $logs  = $wpdb->get_results("SELECT * FROM $table ORDER BY timestamp DESC LIMIT 50");

    echo '<div class="log-section">';
    ?>
    <style>
        .log-section {
            margin-top: 30px;
        }
        .log-controls {
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
            align-items: center;
        }
        .log-bubble-button {
            background: #222;
            color: #aed604;
            border: none;
            padding: 8px 18px;
            font-weight: 500;
            cursor: pointer;
            border-radius: 20px;
            transition: all 0.2s ease-in-out;
            font-family: "Poppins", sans-serif !important;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 0 0 rgba(174,214,4,0);
        }
        .log-bubble-button:hover {
            box-shadow: 0 0 8px rgba(174,214,4,0.7);
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
        .log-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Poppins', sans-serif;
        }
        .log-table th {
            background: rgba(174, 214, 4, 0.1);
            color: #aed604;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid rgba(174, 214, 4, 0.2);
        }
        .log-table td {
            padding: 12px;
            color: #fff;
            border-bottom: 1px solid #333;
        }
        .log-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }
        .log-table .code-column {
            font-weight: 600;
            color: #aed604;
            font-family: monospace;
        }
        .log-table .time-column {
            color: #888;
            font-size: 13px;
        }
        @media (max-width: 768px) {
            .log-table {
                font-size: 13px;
            }
            .log-table th,
            .log-table td {
                padding: 8px;
            }
        }
    </style>
    <?php
    echo '<div class="title-bubble" style="margin-top:30px; margin-bottom:12px;">📊 Usage Log</div>';
    echo '<div class="log-controls">';
    echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
    echo wp_nonce_field('greenangel_clear_usage_log', '_wpnonce', true, false);
    echo '<input type="hidden" name="action" value="greenangel_clear_usage_log">';
    echo '<button type="submit" class="log-bubble-button">🗑 Clear Log</button>';
    echo '</form>';
    echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
    echo wp_nonce_field('greenangel_download_usage_log', '_wpnonce', true, false);
    echo '<input type="hidden" name="action" value="greenangel_download_usage_log">';
    echo '<button type="submit" class="log-bubble-button">📥 Download CSV</button>';
    echo '</form>';
    echo '</div>';

    if (!$logs) {
        echo '<div class="empty-log">';
        echo '<p style="font-size:16px;">No registrations logged yet 🌙</p>';
        echo '</div></div>';
        return;
    }

    echo '<table class="log-table">';
    echo '<thead><tr><th>Email</th><th>Code Used</th><th>Timestamp</th></tr></thead><tbody>';
    foreach ($logs as $log) {
        echo '<tr>';
        echo '<td>' . esc_html($log->email ?: '—') . '</td>';
        echo '<td class="code-column">' . esc_html($log->code_used) . '</td>';
        echo '<td class="time-column">' . esc_html(date('Y-m-d H:i', strtotime($log->timestamp))) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}
