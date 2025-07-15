<?php
/**
 * 🎛️ MAINTENANCE DASHBOARD - LED Admin Interface
 * Beautiful admin controls for our ICONIC LED maintenance system
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 🌈 Render the ICONIC LED maintenance dashboard
 */
function greenangel_render_maintenance_dashboard() {
    $is_enabled = greenangel_is_maintenance_enabled();
    $custom_message = get_option('greenangel_maintenance_message', '');
    $eta = get_option('greenangel_maintenance_eta', '');
    $stats = greenangel_get_maintenance_stats();
    ?>
    
    <div id="maintenance-dashboard">
        <h2>🌈 Iconic LED Maintenance Mode</h2>
        <p class="dashboard-subtitle">Transform your site into an electric paradise while you craft magic behind the scenes ✨</p>
        
        <!-- 📊 Status Overview -->
        <div class="status-overview">
            <div class="status-card main-status">
                <div class="status-header">
                    <div class="status-indicator" id="status-indicator">
                        <div class="status-dot <?php echo $is_enabled ? 'enabled' : 'disabled'; ?>"></div>
                        <span class="status-text" id="status-text">
                            <?php echo $is_enabled ? 'LED MODE ACTIVE' : 'SITE IS LIVE'; ?>
                        </span>
                    </div>
                    <button id="toggle-maintenance" class="toggle-button <?php echo $is_enabled ? 'active' : ''; ?>">
                        <span class="button-icon"><?php echo $is_enabled ? '🌞' : '🌙'; ?></span>
                        <span class="button-text"><?php echo $is_enabled ? 'Wake Up Site' : 'Activate Magic'; ?></span>
                    </button>
                </div>
                
                <div class="status-description">
                    <?php if ($is_enabled): ?>
                        <p class="enabled-text">✨ Your site is showing the most ICONIC LED maintenance console! Only admins can access the real site.</p>
                    <?php else: ?>
                        <p class="disabled-text">🌍 Site is accessible to all visitors. Ready to activate your electric LED magic?</p>
                    <?php endif; ?>
                </div>
                
                <!-- 🎮 Quick Actions -->
                <div class="quick-actions">
                    <a href="<?php echo home_url('?preview_maintenance=true'); ?>" target="_blank" class="quick-action preview">
                        <span class="action-icon">👁️</span>
                        <span class="action-text">Preview LED Magic</span>
                    </a>
                    <button id="emergency-test" class="quick-action emergency">
                        <span class="action-icon">🚪</span>
                        <span class="action-text">Test Emergency</span>
                    </button>
                </div>
            </div>
            
            <!-- 📈 Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">⏱️</div>
                    <div class="stat-content">
                        <div class="stat-value" id="current-session">
                            <?php echo $stats['current_session'] > 0 ? gmdate('H:i:s', $stats['current_session']) : '00:00:00'; ?>
                        </div>
                        <div class="stat-label">Current Magic Session</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo gmdate('H:i', $stats['total_downtime']); ?></div>
                        <div class="stat-label">Total Magic Time</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🎭</div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo count(get_option('greenangel_maintenance_logs', array())); ?></div>
                        <div class="stat-label">Magic Events</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 🎨 Settings Panel -->
        <div class="settings-panel">
            <div class="panel-grid">
                <!-- 💬 Message Settings -->
                <div class="settings-card">
                    <h3>💬 Custom Magic Message</h3>
                    <div class="form-group">
                        <label for="maintenance-message">Your Special Message for Visitors:</label>
                        <textarea id="maintenance-message" placeholder="Leave empty for rotating LED messages..."><?php echo esc_textarea($custom_message); ?></textarea>
                        <div class="character-count">
                            <span id="char-count"><?php echo strlen($custom_message); ?></span>/500 characters
                        </div>
                        <div class="message-preview">
                            <strong>Live Preview:</strong> 
                            <span id="message-preview-text"><?php echo !empty($custom_message) ? esc_html($custom_message) : 'Using electric rotating messages'; ?></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="maintenance-eta">Expected Return Time:</label>
                        <input type="text" id="maintenance-eta" value="<?php echo esc_attr($eta); ?>" 
                               placeholder="e.g. 2 hours, tomorrow morning, 3pm GMT...">
                        <div class="eta-examples">
                            <strong>Examples:</strong> "30 minutes", "2 hours", "tomorrow at 3pm", "back soon!"
                        </div>
                    </div>
                    
                    <button id="save-settings" class="save-button">
                        <span class="button-icon">💾</span>
                        Save Magic Settings
                    </button>
                </div>
                
                <!-- 🎪 Features & Tools -->
                <div class="settings-card">
                    <h3>🎪 Epic LED Features</h3>
                    
                    <div class="feature-list">
                        <div class="feature-item">
                            <div class="feature-icon">🎰</div>
                            <div class="feature-content">
                                <strong>Electric Emoji Roulette</strong>
                                <p>Interactive spinning wheel with LED-themed emojis</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">⚡</div>
                            <div class="feature-content">
                                <strong>RGB Particle Effects</strong>
                                <p>Electric floating particles with neon glow</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">🌈</div>
                            <div class="feature-content">
                                <strong>Dynamic LED Messages</strong>
                                <p>Rotating status messages with electric vibes</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">💎</div>
                            <div class="feature-content">
                                <strong>Crystal Progress Bar</strong>
                                <p>RGB flowing progress with shimmer effects</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="emergency-info">
                        <h4>🚪 Emergency Backdoor Access</h4>
                        <p>If you ever get locked out, add this to any URL:</p>
                        <div class="emergency-code" onclick="copyToClipboard(this)">
                            ?iamjess=true
                            <span class="copy-hint">Click to copy</span>
                        </div>
                        <p class="emergency-note">⏰ Gives you 1 hour of emergency access. Save this somewhere safe!</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ℹ️ Information Panel -->
        <div class="info-panel">
            <h3>ℹ️ How Your Iconic LED Maintenance Mode Works</h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-icon">💼</div>
                    <div class="info-content">
                        <strong>Business Never Stops</strong>
                        <p>WooCommerce orders, payments, webhooks, and all business operations continue seamlessly</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">🎨</div>
                    <div class="info-content">
                        <strong>Electric LED Design</strong>
                        <p>RGB borders, floating particles, and smooth animations for maximum impact</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">🔐</div>
                    <div class="info-content">
                        <strong>Admin-Only Access</strong>
                        <p>Only users with admin privileges can access the real site during maintenance</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">📱</div>
                    <div class="info-content">
                        <strong>Mobile-First Design</strong>
                        <p>Perfectly optimized for all devices with responsive animations</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">🚪</div>
                    <div class="info-content">
                        <strong>Emergency Exit Always Available</strong>
                        <p>Secret backdoor URL provides instant emergency access if needed</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">⚡</div>
                    <div class="info-content">
                        <strong>Performance Optimized</strong>
                        <p>Lightning-fast loading with optimized animations</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 🔔 Notification System -->
        <div id="notification-container"></div>
    </div>
    
    <script>
        // Pass PHP data to JavaScript
        window.maintenanceData = {
            isEnabled: <?php echo $is_enabled ? 'true' : 'false'; ?>,
            homeUrl: '<?php echo home_url(); ?>',
            currentSessionStart: <?php echo $stats['enabled_time']; ?>,
            previewUrl: '<?php echo home_url('?preview_maintenance=true'); ?>',
            emergencyUrl: '<?php echo home_url('?iamjess=true'); ?>',
            currentEmoji: '<?php echo greenangel_get_random_emoji(); ?>'
        };
        
        // Live message preview functionality
        document.getElementById('maintenance-message').addEventListener('input', function() {
            const preview = document.getElementById('message-preview-text');
            const value = this.value.trim();
            preview.textContent = value || 'Using electric rotating messages';
            
            // Update character count
            document.getElementById('char-count').textContent = this.value.length;
        });
    </script>
    
    <?php
}

/**
 * 🎨 Critical CSS with LED theme
 */
add_action('admin_head', function() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'greenangel-hub' || 
        !isset($_GET['tab']) || $_GET['tab'] !== 'maintenance') {
        return;
    }
    ?>
    <style>
        /* 🌈 Critical CSS for LED dashboard */
        #maintenance-dashboard {
            max-width: 1200px;
            margin: 0 auto;
            font-family: 'Poppins', sans-serif;
        }
        
        .dashboard-subtitle {
            color: #aaa;
            margin-bottom: 30px;
            font-size: 15px;
            line-height: 1.6;
        }
        
        .status-overview {
            margin-bottom: 30px;
        }
        
        .main-status {
            background: linear-gradient(145deg, #3a3a3a 0%, #4d4d4d 50%, #3a3a3a 100%);
            border: 2px solid rgba(174, 214, 4, 0.3);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }
        
        /* Enhanced quick actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
            margin-top: 20px;
        }
        
        .quick-action {
            padding: 10px 15px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(174, 214, 4, 0.1), rgba(0, 255, 255, 0.05));
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
            font-size: 12px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .quick-action:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, rgba(174, 214, 4, 0.2), rgba(0, 255, 255, 0.1));
            box-shadow: 0 5px 15px rgba(174, 214, 4, 0.2);
            color: #fff;
            text-decoration: none;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <?php
});
?>