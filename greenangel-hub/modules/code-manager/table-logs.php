<?php
// Green Angel – Angel Code Log Viewer (Clean Version)
function greenangel_render_code_log_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'greenangel_code_logs';
    $logs = $wpdb->get_results("SELECT * FROM $table ORDER BY timestamp DESC LIMIT 50");
    
    if (!$logs) {
        ?>
        <div class="empty-state">
            <div class="empty-state-icon">🌙</div>
            <p>No registrations logged yet</p>
            <p style="opacity: 0.6; font-size: 13px;">This will populate once someone registers using a valid code.</p>
        </div>
        <?php
        return;
    }
    ?>
    
    <style>
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
    
    <table class="log-table">
        <thead>
            <tr>
                <th>Email</th>
                <th>Code Used</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?php echo esc_html($log->email ?: '—'); ?></td>
                    <td class="code-column"><?php echo esc_html($log->code_used); ?></td>
                    <td class="time-column"><?php echo esc_html(date('Y-m-d H:i', strtotime($log->timestamp))); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}