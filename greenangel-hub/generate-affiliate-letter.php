<?php
/**
 * Affiliate Welcome Letter generator
 */

if ( ! defined('ABSPATH') ) {
    exit; // no direct access
}

if ( ! current_user_can('manage_woocommerce') ) {
    wp_die('Permission denied');
}

$affiliate_id = isset($_GET['affiliate_id']) ? intval($_GET['affiliate_id']) : 0;
if ( ! $affiliate_id ) {
    wp_die('Missing affiliate_id');
}

function ga_get_affiliate($affiliate_id) {
    if ( function_exists('slicewp_get_affiliate') ) {
        return slicewp_get_affiliate($affiliate_id);
    }
    global $wpdb;
    $table = $wpdb->prefix . 'slicewp_affiliates';
    return $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table WHERE affiliate_id = %d", $affiliate_id) );
}

$affiliate = ga_get_affiliate($affiliate_id);
if ( ! $affiliate ) {
    wp_die('Affiliate not found');
}

$user = get_user_by('ID', $affiliate->user_id);
if ( ! $user ) {
    wp_die('User not found');
}

$name = $user->display_name ? $user->display_name : $user->user_email;
$email = $user->user_email;
$slug  = get_user_meta($user->ID, 'slice_referral_slug', true);
if ( empty($slug) && isset($affiliate->slug) ) {
    $slug = $affiliate->slug;
}
$ref_url = home_url('/ref/' . $slug);

?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Affiliate Welcome Letter</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
body{font-family:'Poppins',sans-serif;background:#fff;color:#222222;padding:40px;}
.h1{color:#aed604;}
.bubble{background:#aed604;color:#222222;border-radius:20px;padding:10px 20px;display:inline-block;margin-bottom:20px;}
</style>
</head>
<body>
<div class="bubble"><strong>Green Angel Affiliate Program</strong></div>
<h1>Hello <?php echo esc_html($name); ?>!</h1>
<p>We're excited to have you as part of the community.</p>
<p>Your referral link:<br><strong><?php echo esc_html($ref_url); ?></strong></p>
<p>Keep flying high with Green Angel. Replace this text with your own welcome message.</p>
<script>window.onload=()=>window.print();</script>
</body>
</html>
