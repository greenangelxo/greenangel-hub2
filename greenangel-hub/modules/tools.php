<?php
// 🌿 Green Angel Hub – Tools Module

function greenangel_copy_login_form() {
    $source = plugin_dir_path(__FILE__) . 'assets/form-login.php';
    $dest   = get_theme_root() . '/savoy/woocommerce/myaccount/form-login.php';
    wp_mkdir_p( dirname( $dest ) );
    return copy( $source, $dest );
}

function greenangel_restore_login_form() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_die( 'Permission denied' );
    }
    check_admin_referer( 'greenangel_restore_login_form', 'greenangel_nonce' );
    if ( greenangel_copy_login_form() ) {
        update_option( 'greenangel_last_login_restore', current_time( 'mysql' ) );
        wp_redirect( admin_url( 'admin.php?page=greenangel-hub&tab=tools&restore=success' ) );
    } else {
        wp_redirect( admin_url( 'admin.php?page=greenangel-hub&tab=tools&restore=fail' ) );
    }
    exit;
}
add_action( 'admin_post_greenangel_restore_login_form', 'greenangel_restore_login_form' );

function greenangel_download_login_form() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_die( 'Permission denied' );
    }
    $file = get_theme_root() . '/savoy/woocommerce/myaccount/form-login.php';
    if ( ! file_exists( $file ) ) {
        wp_die( 'File not found' );
    }
    header( 'Content-Type: application/x-php' );
    header( 'Content-Disposition: attachment; filename="form-login.php"' );
    readfile( $file );
    exit;
}
add_action( 'admin_post_greenangel_download_login_form', 'greenangel_download_login_form' );

function greenangel_upload_login_form() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_die( 'Permission denied' );
    }
    check_admin_referer( 'greenangel_upload_login_form', 'greenangel_nonce_upload' );
    if ( empty( $_FILES['login_file']['name'] ) ) {
        wp_redirect( admin_url( 'admin.php?page=greenangel-hub&tab=tools&upload=fail' ) );
        exit;
    }
    $file = $_FILES['login_file'];
    $check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] );
    if ( $check['ext'] !== 'php' ) {
        wp_redirect( admin_url( 'admin.php?page=greenangel-hub&tab=tools&upload=invalid' ) );
        exit;
    }
    $dest = plugin_dir_path( __FILE__ ) . 'assets/form-login.php';
    if ( move_uploaded_file( $file['tmp_name'], $dest ) ) {
        greenangel_copy_login_form();
        update_option( 'greenangel_last_login_restore', current_time( 'mysql' ) );
        wp_redirect( admin_url( 'admin.php?page=greenangel-hub&tab=tools&upload=success' ) );
    } else {
        wp_redirect( admin_url( 'admin.php?page=greenangel-hub&tab=tools&upload=fail' ) );
    }
    exit;
}
add_action( 'admin_post_greenangel_upload_login_form', 'greenangel_upload_login_form' );

function greenangel_render_tools_tab() {
    $theme_file    = get_theme_root() . '/savoy/woocommerce/myaccount/form-login.php';
    $code_contents = file_exists( $theme_file ) ? file_get_contents( $theme_file ) : '';
    $last_restored = get_option( 'greenangel_last_login_restore' );
    $action        = admin_url( 'admin-post.php' );
    ?>
    <div class="tools-wrapper">
        <div class="title-bubble">🛠 Tools</div>

        <div class="angel-card">
            <div class="card-header">
                <span class="header-bubble">🛠 Login Form Restore</span>
            </div>
            <div class="card-content">
                <?php if ( isset( $_GET['restore'] ) && $_GET['restore'] === 'success' ) : ?>
                    <span class="success-indicator show">✅ Login form restored!</span>
                <?php elseif ( isset( $_GET['restore'] ) && $_GET['restore'] === 'fail' ) : ?>
                    <span class="success-indicator show">❌ Restore failed</span>
                <?php endif; ?>
                <form method="post" action="<?php echo esc_url( $action ); ?>">
                    <input type="hidden" name="action" value="greenangel_restore_login_form">
                    <?php wp_nonce_field( 'greenangel_restore_login_form', 'greenangel_nonce' ); ?>
                    <button type="submit" class="angel-button">🔁 Restore Login Form</button>
                </form>
                <?php if ( $last_restored ) : ?>
                    <p style="margin-top:10px;">✅ Last restored: <?php echo esc_html( $last_restored ); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="angel-card">
            <div class="card-header">
                <span class="header-bubble">👁 View Current form-login.php</span>
            </div>
            <div class="card-content">
                <textarea id="ga-login-code" class="code-preview" readonly><?php echo esc_textarea( $code_contents ); ?></textarea>
                <button type="button" class="copy-btn" onclick="navigator.clipboard.writeText(document.getElementById('ga-login-code').value)">Copy</button>
            </div>
        </div>

        <div class="angel-card">
            <div class="card-header">
                <span class="header-bubble">Manage File</span>
            </div>
            <div class="card-content">
                <a class="angel-button secondary" href="<?php echo esc_url( $action . '?action=greenangel_download_login_form' ); ?>">💾 Download Current Version</a>
                <form method="post" action="<?php echo esc_url( $action ); ?>" enctype="multipart/form-data" style="margin-top:15px;">
                    <input type="hidden" name="action" value="greenangel_upload_login_form">
                    <?php wp_nonce_field( 'greenangel_upload_login_form', 'greenangel_nonce_upload' ); ?>
                    <input type="file" name="login_file" accept=".php" required>
                    <button type="submit" class="angel-button" style="margin-top:10px;">📤 Upload New Login Form Version</button>
                </form>
            </div>
        </div>
    </div>

    <style>
        .tools-wrapper { margin-top:20px; font-family:'Poppins',sans-serif!important; }
        .angel-card { background:#222222; border-radius:14px; overflow:hidden; margin-bottom:25px; box-shadow:0 6px 12px rgba(0,0,0,0.1); }
        .card-header { padding:16px 20px; background:#222222; }
        .card-content { padding:25px; background:#222222; display:flex; flex-direction:column; gap:15px; }
        .angel-button { background:#aed604; color:#222222; border:none; padding:12px 20px; font-weight:600; cursor:pointer; border-radius:20px; transition:all 0.2s ease-in-out; font-family:'Poppins',sans-serif!important; font-size:14px; display:flex; align-items:center; gap:8px; box-shadow:0 4px 8px rgba(0,0,0,0.15); justify-content:center; }
        .angel-button.secondary { background:rgba(174,214,4,0.15); color:#aed604; }
        .header-bubble { display:inline-block; background-color:#aed604; color:#222222; padding:6px 12px; border-radius:16px; font-weight:500; font-size:12px; white-space:nowrap; }
        .success-indicator { display:inline-block; background:#aed604; color:#222222; padding:8px 18px; border-radius:20px; font-weight:500; font-size:14px; }
        .code-preview { width:100%; height:300px; background:#f5f5f5; color:#222222; border:1px solid rgba(174,214,4,0.3); border-radius:10px; padding:15px; font-family:'Courier New',monospace; font-size:13px; resize:vertical; line-height:1.6; }
        .copy-btn { background:#222222; color:#aed604; border:none; padding:8px 16px; font-weight:500; cursor:pointer; border-radius:20px; transition:all 0.2s ease-in-out; font-family:'Poppins',sans-serif!important; font-size:13px; display:inline-flex; align-items:center; gap:8px; }
        .copy-btn:hover { background:#333333; }
    </style>
    <?php
}
