<?php
/**
 * 🌿 Green Angel Hub - Account Pages Loader
 * This file stays in: plugins/greenangel-hub/account/view-order.php
 * 
 * It loads all the account functionality from organized includes
 */

// Safety first - ensure we have the includes directory
$includes_dir = plugin_dir_path(__FILE__) . 'includes/';
if (!file_exists($includes_dir)) {
    wp_mkdir_p($includes_dir);
}

// Load all our organized account files
require_once $includes_dir . 'styles.php';          // All CSS styling
require_once $includes_dir . 'order-details.php';   // Single order view
require_once $includes_dir . 'orders-list.php';     // Orders listing page
require_once $includes_dir . 'address-edit.php';    // Address editing
require_once $includes_dir . 'account-edit.php';    // Account details + dice game

// That's it! All functionality is now loaded from organized files 🎉