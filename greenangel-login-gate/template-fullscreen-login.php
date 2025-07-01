<?php defined('ABSPATH') || exit; ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Angel Login - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>
<body class="angel-login-body">
    
    <div class="angel-login-container">
        <?php
        while (have_posts()) {
            the_post();
            the_content();
        }
        ?>
    </div>
    
    <?php wp_footer(); ?>
</body>
</html>