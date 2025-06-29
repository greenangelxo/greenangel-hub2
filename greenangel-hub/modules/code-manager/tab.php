<?php
defined( 'ABSPATH' ) || exit;
// 🌿 Green Angel – Angel Code Manager Tab
require_once plugin_dir_path(__FILE__) . 'table-codes.php';
require_once plugin_dir_path(__FILE__) . 'form-add-code.php';
require_once plugin_dir_path(__FILE__) . 'table-logs.php';
require_once plugin_dir_path(__FILE__) . 'table-fails.php';
require_once plugin_dir_path(__FILE__) . 'handle-registration.php';
require_once plugin_dir_path(__FILE__) . 'registration-hooks.php';
require_once plugin_dir_path(__FILE__) . 'manage-logs.php';

function greenangel_render_angel_codes_tab() {
    ?>
    <div class="angel-codes-wrapper">
        <div class="codes-header">
            <h2 class="codes-title">🪽 Angel Code Manager</h2>
            <p class="codes-subtitle">Manage your invite-only access system, view all active codes, and track usage logs</p>
        </div>
        
        <style>
            /* Angel Codes Manager - Dark Theme */
            .angel-codes-wrapper {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 15px;
            }
            
            .codes-header {
                text-align: center;
                margin-bottom: 30px;
            }
            
            .codes-title {
                font-size: 24px;
                font-weight: 600;
                color: #aed604;
                margin: 0 0 8px 0;
                font-family: 'Poppins', sans-serif;
            }
            
            .codes-subtitle {
                font-size: 14px;
                color: #888;
                margin: 0;
                font-family: 'Poppins', sans-serif;
            }
            
            /* Navigation Pills */
            .codes-nav {
                display: flex;
                gap: 10px;
                justify-content: center;
                margin-bottom: 30px;
                flex-wrap: wrap;
            }
            
            .nav-pill {
                background: transparent;
                border: 2px solid #333;
                color: #aed604;
                padding: 10px 20px;
                border-radius: 25px;
                text-decoration: none;
                font-size: 14px;
                font-weight: 500;
                transition: all 0.2s ease;
                font-family: 'Poppins', sans-serif;
            }
            
            .nav-pill:hover {
                background: rgba(174, 214, 4, 0.1);
                border-color: #aed604;
                color: #aed604;
            }
            
            .nav-pill.active {
                background: #aed604;
                color: #222;
                border-color: #aed604;
            }
            
            /* Main Content Grid */
            .codes-main-content {
                margin-bottom: 30px;
            }
            
            /* Secondary Content Grid */
            .codes-secondary-grid {
                display: grid;
                gap: 20px;
                margin-top: 30px;
            }
            
            @media (min-width: 1024px) {
                .codes-secondary-grid {
                    grid-template-columns: 1fr 1fr;
                }
            }
            
            /* Card Styles */
            .codes-card {
                background: #1a1a1a;
                border: 2px solid #333;
                border-radius: 14px;
                overflow: hidden;
                transition: all 0.2s ease;
            }
            
            .codes-card:hover {
                border-color: #444;
            }
            
            .codes-card-header {
                background: #222;
                padding: 20px;
                border-bottom: 2px solid #333;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            
            .codes-card-header.expandable-header {
                cursor: pointer;
            }
            
            .card-header-actions {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-left: auto;
            }
            
            .angel-button.small {
                padding: 6px 16px;
                font-size: 12px;
            }
            
            .header-buttons {
                display: flex;
                gap: 8px;
                margin-right: 15px;
            }
            
            .codes-card-title {
                font-size: 18px;
                font-weight: 600;
                color: #aed604;
                margin: 0;
                display: flex;
                align-items: center;
                gap: 10px;
                font-family: 'Poppins', sans-serif;
            }
            
            .codes-card-content {
                padding: 25px;
            }
            
            /* Logs Container with Scroll */
            .logs-container {
                max-height: 400px;
                overflow-y: auto;
                background: #0a0a0a;
                border: 1px solid #333;
                border-radius: 10px;
                padding: 15px;
            }
            
            /* Custom Scrollbar */
            .logs-container::-webkit-scrollbar {
                width: 8px;
            }
            
            .logs-container::-webkit-scrollbar-track {
                background: #0a0a0a;
                border-radius: 4px;
            }
            
            .logs-container::-webkit-scrollbar-thumb {
                background: #333;
                border-radius: 4px;
            }
            
            .logs-container::-webkit-scrollbar-thumb:hover {
                background: #444;
            }
            
            /* Form Styles */
            .angel-form-group {
                margin-bottom: 20px;
            }
            
            .angel-form-group label {
                display: block;
                color: #aed604;
                font-size: 13px;
                font-weight: 600;
                margin-bottom: 8px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            
            .angel-input {
                width: 100%;
                background: #0a0a0a;
                color: #fff;
                border: 2px solid #333;
                border-radius: 10px;
                padding: 12px 16px;
                font-family: 'Poppins', sans-serif;
                font-size: 14px;
                transition: all 0.3s ease;
            }
            
            .angel-input:focus {
                outline: none;
                border-color: #aed604;
            }
            
            .angel-select {
                position: relative;
            }
            
            .angel-select select {
                width: 100%;
                background: #0a0a0a;
                color: #fff;
                border: 2px solid #333;
                border-radius: 10px;
                padding: 12px 40px 12px 16px;
                font-family: 'Poppins', sans-serif;
                font-size: 14px;
                appearance: none;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .angel-select select:hover,
            .angel-select select:focus {
                border-color: #aed604;
                outline: none;
            }
            
            .angel-select::after {
                content: '▼';
                position: absolute;
                right: 16px;
                top: 50%;
                transform: translateY(-50%);
                color: #aed604;
                font-size: 12px;
                pointer-events: none;
            }
            
            .angel-button {
                background: #aed604;
                color: #222;
                border: none;
                padding: 12px 24px;
                font-size: 14px;
                font-weight: 600;
                border-radius: 25px;
                cursor: pointer;
                transition: all 0.2s ease;
                font-family: 'Poppins', sans-serif;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }
            
            .angel-button:hover {
                background: #9bc603;
                transform: translateY(-2px);
            }
            
            .angel-button.secondary {
                background: transparent;
                border: 2px solid #aed604;
                color: #aed604;
            }
            
            .angel-button.secondary:hover {
                background: rgba(174, 214, 4, 0.1);
            }
            
            .angel-button.small {
                padding: 6px 16px;
                font-size: 12px;
            }
            
            .angel-button.secondary.small {
                padding: 6px 14px;
                font-size: 12px;
                border-width: 1px;
            }
            
            /* Success/Error Messages */
            .angel-message {
                padding: 12px 20px;
                border-radius: 10px;
                margin-bottom: 20px;
                font-size: 14px;
                font-weight: 500;
                animation: slideIn 0.3s ease;
            }
            
            .angel-message.success {
                background: rgba(39, 174, 96, 0.1);
                border: 1px solid rgba(39, 174, 96, 0.3);
                color: #27ae60;
            }
            
            .angel-message.error {
                background: rgba(231, 76, 60, 0.1);
                border: 1px solid rgba(231, 76, 60, 0.3);
                color: #e74c3c;
            }
            
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            /* Expandable Sections */
            .expandable-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                cursor: pointer;
                user-select: none;
            }
            
            .expand-icon {
                color: #aed604;
                transition: transform 0.3s ease;
                font-size: 20px;
            }
            
            .expandable-header.expanded .expand-icon {
                transform: rotate(180deg);
            }
            
            .expandable-content {
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.3s ease;
            }
            
            .expandable-content.expanded {
                max-height: 500px;
            }
            
            /* Active Codes Mobile Cards */
            .codes-mobile-view {
                display: block;
            }
            
            .codes-desktop-view {
                display: none;
            }
            
            @media (min-width: 1024px) {
                .codes-mobile-view {
                    display: none;
                }
                
                .codes-desktop-view {
                    display: block;
                }
            }
            
            .code-item-card {
                background: #0a0a0a;
                border: 1px solid #333;
                border-radius: 12px;
                padding: 15px;
                margin-bottom: 12px;
                transition: all 0.2s ease;
            }
            
            .code-item-card:hover {
                border-color: #444;
            }
            
            .code-item-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
            }
            
            .code-name {
                font-weight: 700;
                color: #aed604;
                font-family: monospace;
                font-size: 16px;
            }
            
            .code-type-badge {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 15px;
                font-size: 12px;
                font-weight: 600;
                background: rgba(174, 214, 4, 0.2);
                color: #aed604;
            }
            
            .code-item-body {
                display: grid;
                gap: 10px;
                font-size: 13px;
                margin-bottom: 15px;
            }
            
            .code-info-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding-bottom: 8px;
                border-bottom: 1px solid #333;
            }
            
            .code-info-label {
                color: #666;
                font-weight: 500;
            }
            
            .code-info-value {
                color: #fff;
            }
            
            .code-status-active {
                color: #27ae60;
                font-weight: 600;
            }
            
            .code-status-inactive {
                color: #e74c3c;
                font-weight: 600;
            }
            
            .code-item-actions {
                display: flex;
                gap: 8px;
            }
            
            .code-item-actions .angel-button {
                flex: 1;
                text-align: center;
                justify-content: center;
                padding: 8px 16px !important;
                font-size: 13px !important;
                text-decoration: none !important;
            }
            
            .code-item-actions .angel-button:hover {
                text-decoration: none !important;
            }
            .empty-state {
                text-align: center;
                padding: 40px;
                color: #666;
            }
            
            .empty-state-icon {
                font-size: 48px;
                margin-bottom: 15px;
                opacity: 0.5;
            }
            
            /* Mobile Responsive */
            @media (max-width: 768px) {
                .codes-nav {
                    justify-content: flex-start;
                    overflow-x: auto;
                    -webkit-overflow-scrolling: touch;
                    padding-bottom: 10px;
                }
                
                .nav-pill {
                    white-space: nowrap;
                    flex-shrink: 0;
                }
                
                .codes-card-content {
                    padding: 20px 15px;
                }
                
                .logs-container {
                    max-height: 300px;
                }
            }
        </style>
        
        <?php
        // Show success/error messages
        if (isset($_GET['added']) && $_GET['added'] == '1') {
            echo '<div class="angel-message success">✅ Angel code added successfully!</div>';
        }
        if (isset($_GET['error']) && $_GET['error'] == 'missing_code') {
            echo '<div class="angel-message error">❌ Please enter a code!</div>';
        }
        ?>
        
        <!-- Main Codes Table/Cards -->
        <div class="codes-main-content">
            <div class="codes-card">
                <div class="codes-card-header">
                    <h3 class="codes-card-title">
                        <span>🎫</span>
                        Active Codes
                    </h3>
                </div>
                <div class="codes-card-content">
                    <?php
                    // Get the codes data for mobile view
                    global $wpdb;
                    $codes_table = $wpdb->prefix . 'greenangel_codes';
                    $codes = $wpdb->get_results("SELECT * FROM $codes_table ORDER BY created_at DESC");
                    ?>
                    
                    <!-- Mobile Cards View -->
                    <div class="codes-mobile-view">
                        <?php if (empty($codes)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">🎫</div>
                                <p>No angel codes created yet</p>
                                <p style="opacity: 0.6; font-size: 13px;">Add your first code below to get started</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($codes as $code): ?>
                                <div class="code-item-card">
                                    <div class="code-item-header">
                                        <span class="code-name"><?php echo esc_html($code->code); ?></span>
                                        <span class="code-type-badge"><?php echo esc_html(ucfirst($code->type)); ?></span>
                                    </div>
                                    
                                    <div class="code-item-body">
                                        <?php if (!empty($code->label)): ?>
                                        <div class="code-info-row">
                                            <span class="code-info-label">Label</span>
                                            <span class="code-info-value"><?php echo esc_html($code->label); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="code-info-row">
                                            <span class="code-info-label">Status</span>
                                            <span class="code-info-value <?php echo $code->active ? 'code-status-active' : 'code-status-inactive'; ?>">
                                                <?php echo $code->active ? '✅ Active' : '❌ Inactive'; ?>
                                            </span>
                                        </div>
                                        
                                        <div class="code-info-row">
                                            <span class="code-info-label">Created</span>
                                            <span class="code-info-value"><?php echo date('Y-m-d', strtotime($code->created_at)); ?></span>
                                        </div>
                                        
                                        <?php if (!empty($code->expires_at)): ?>
                                        <div class="code-info-row">
                                            <span class="code-info-label">Expires</span>
                                            <span class="code-info-value"><?php echo date('Y-m-d', strtotime($code->expires_at)); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="code-item-actions">
                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="flex: 1;">
                                            <input type="hidden" name="action" value="greenangel_toggle_code_status">
                                            <input type="hidden" name="code_id" value="<?php echo $code->id; ?>">
                                            <?php wp_nonce_field('greenangel_toggle_code_' . $code->id); ?>
                                            <button type="submit" class="angel-button secondary" style="width: 100%; text-decoration: none;">
                                                <?php echo $code->active ? '🔄 Deactivate' : '✅ Activate'; ?>
                                            </button>
                                        </form>
                                        <a href="#" class="angel-button secondary" style="background: rgba(231, 76, 60, 0.1); border-color: #e74c3c; color: #e74c3c;">
                                            🗑️ Delete
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Desktop Table View -->
                    <div class="codes-desktop-view">
                        <?php greenangel_render_code_table(); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Secondary Grid: Add Form + Logs -->
        <div class="codes-secondary-grid">
            <!-- Add New Code Form -->
            <div class="codes-card">
                <div class="codes-card-header">
                    <h3 class="codes-card-title">
                        <span>➕</span>
                        Add New Angel Code
                    </h3>
                </div>
                <div class="codes-card-content">
                    <?php greenangel_render_add_code_form(); ?>
                </div>
            </div>
            
            <!-- Usage Logs (has its own header with buttons) -->
            <div class="codes-card">
                <div class="codes-card-header expandable-header" onclick="toggleExpand(this)">
                    <h3 class="codes-card-title">
                        <span>📊</span>
                        Usage Logs
                    </h3>
                    <span class="expand-icon">▼</span>
                </div>
                <div class="codes-card-content">
                    <div class="expandable-content">
                        <div class="logs-container">
                            <?php greenangel_render_code_log_table(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Failed Attempts Log (has its own header with buttons) -->
        <div class="codes-card" style="margin-top: 20px;">
            <div class="codes-card-header expandable-header" onclick="toggleExpand(this)">
                <h3 class="codes-card-title">
                    <span>🚫</span>
                    Failed Angel Code Attempts
                </h3>
                <span class="expand-icon">▼</span>
            </div>
            <div class="codes-card-content">
                <div class="expandable-content">
                    <div class="logs-container">
                        <?php greenangel_render_failed_code_log(); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            function toggleExpand(header) {
                header.classList.toggle('expanded');
                const content = header.parentElement.querySelector('.expandable-content');
                if (content) {
                    content.classList.toggle('expanded');
                }
            }
            
            // Auto-expand first log section
            document.addEventListener('DOMContentLoaded', function() {
                const firstExpandable = document.querySelector('.expandable-header');
                if (firstExpandable) {
                    toggleExpand(firstExpandable);
                }
            });
        </script>
    </div>
    <?php
}

// ✅ Handle Angel Code form submission
add_action('admin_post_greenangel_add_angel_code', function () {
    if (!current_user_can('manage_woocommerce')) return;
    check_admin_referer('greenangel_add_code');
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

// ✅ Handle Angel Code toggle status
add_action('admin_post_greenangel_toggle_code_status', function () {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Permission denied');
    }
    
    $code_id = intval($_POST['code_id'] ?? 0);
    
    if (!$code_id) {
        wp_redirect(admin_url('admin.php?page=greenangel-hub&tab=angel-codes'));
        exit;
    }
    
    // Verify nonce
    check_admin_referer('greenangel_toggle_code_' . $code_id);
    
    global $wpdb;
    $table = $wpdb->prefix . 'greenangel_codes';
    
    // Get current status
    $current = $wpdb->get_row($wpdb->prepare("SELECT active FROM $table WHERE id = %d", $code_id));
    
    if ($current) {
        $new_status = $current->active ? 0 : 1;
        $wpdb->update(
            $table,
            ['active' => $new_status, 'updated_at' => current_time('mysql')],
            ['id' => $code_id],
            ['%d', '%s'],
            ['%d']
        );
    }
    
    wp_redirect(admin_url('admin.php?page=greenangel-hub&tab=angel-codes'));
    exit;
});