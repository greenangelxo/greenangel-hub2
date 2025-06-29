<?php
function greenangel_render_dashboard_tab() {
    ?>
    <style>
        /* Dashboard module styles - works within the dark wrapper */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }

        .dashboard-module {
            background: #1a1a1a;
            border: 2px solid #333;
            padding: 25px 20px;
            border-radius: 14px;
            text-align: center;
            color: white;
            text-decoration: none;
            transition: all 0.2s ease;
            display: block;
            -webkit-tap-highlight-color: transparent;
        }

        .dashboard-module:hover {
            transform: translateY(-2px);
            border-color: #aed604;
            text-decoration: none;
            color: white;
        }

        .dashboard-module:active {
            transform: translateY(0);
        }

        .dashboard-module .icon {
            font-size: 28px;
            margin-bottom: 10px;
            display: block;
        }

        .dashboard-module .title {
            font-weight: 600;
            font-size: 16px;
            color: #aed604;
            margin-bottom: 5px;
            display: block;
        }

        .dashboard-module .desc {
            font-size: 12px;
            color: #aaa;
            line-height: 1.4;
            display: block;
        }

        .version-badge {
            text-align: center;
            margin-top: 30px;
        }

        .version-pill {
            display: inline-block;
            background: #aed604;
            color: #000;
            font-size: 14px;
            font-weight: 700;
            padding: 10px 20px;
            border-radius: 50px;
        }

        /* Tablet styles (600px+) */
        @media (min-width: 600px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 18px;
            }
        }

        /* Desktop (768px+) */
        @media (min-width: 768px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }

            .dashboard-module {
                padding: 28px 20px;
            }

            .dashboard-module .icon {
                font-size: 30px;
            }

            .dashboard-module .title {
                font-size: 17px;
            }

            .dashboard-module .desc {
                font-size: 13px;
            }
        }

        /* Large desktop (1024px+) */
        @media (min-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: repeat(3, 1fr);
                max-width: 1000px;
                margin: 0 auto 40px;
            }

            .dashboard-module {
                padding: 30px 20px;
            }

            .dashboard-module:hover {
                transform: translateY(-4px);
            }

            .dashboard-module .icon {
                font-size: 32px;
                margin-bottom: 12px;
            }

            .dashboard-module .title {
                font-size: 18px;
            }

            .version-pill {
                font-size: 16px;
                padding: 12px 24px;
            }
        }
    </style>

    <div class="dashboard-grid">
        <a href="<?php echo admin_url('admin.php?page=greenangel-hub&tab=ship-today'); ?>" class="dashboard-module">
            <span class="icon">💌</span>
            <span class="title">Ship Today</span>
            <span class="desc">Process today's priority orders</span>
        </a>
        <a href="<?php echo admin_url('admin.php?page=greenangel-hub&tab=nfc-manager'); ?>" class="dashboard-module">
            <span class="icon">💳</span>
            <span class="title">NFC Manager</span>
            <span class="desc">Manage referral cards and links</span>
        </a>
        <a href="<?php echo admin_url('admin.php?page=greenangel-hub&tab=packing-slips'); ?>" class="dashboard-module">
            <span class="icon">📦</span>
            <span class="title">Packing Slips</span>
            <span class="desc">Print your beautiful packing slips</span>
        </a>
        <a href="<?php echo admin_url('admin.php?page=greenangel-hub&tab=tracking-numbers'); ?>" class="dashboard-module">
            <span class="icon">📮</span>
            <span class="title">Tracking Numbers</span>
            <span class="desc">Add Royal Mail tracking to orders</span>
        </a>
        <a href="<?php echo admin_url('admin.php?page=greenangel-hub&tab=angel-codes'); ?>" class="dashboard-module">
            <span class="icon">🪽</span>
            <span class="title">Angel Codes</span>
            <span class="desc">Manage access codes and logs</span>
        </a>
        <a href="<?php echo admin_url('admin.php?page=greenangel-hub&tab=delivery-settings'); ?>" class="dashboard-module">
            <span class="icon">🚚</span>
            <span class="title">Delivery Settings</span>
            <span class="desc">Set cutoffs and blackout dates</span>
        </a>
        <a href="<?php echo admin_url('admin.php?page=greenangel-hub&tab=tools'); ?>" class="dashboard-module">
            <span class="icon">🛠️</span>
            <span class="title">Tools</span>
            <span class="desc">Dev tools, resets and utilities</span>
        </a>
    </div>

    <div class="version-badge">
        <div class="version-pill">Angel Hub v1.0</div>
    </div>
    <?php
}