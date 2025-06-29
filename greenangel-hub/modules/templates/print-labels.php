<?php
defined( 'ABSPATH' ) || exit;
/**
 * Green Angel Shipping Labels - FIXED with Style Options
 * 63.5×38.1mm labels, 21 per sheet (3×7)
 * FIXED: Eliminates phantom pages by controlling page breaks properly
 * NEW: Dynamic font styles for variety and stealth
 */


// Only allow admins / shop managers
if ( ! current_user_can( 'manage_woocommerce' ) ) {
    wp_die( esc_html__( 'Unauthorized access', 'green-angel' ) );
}

// Sanitize incoming IDs array
$ids = isset( $ids ) && is_array( $ids )
    ? array_map( 'intval', $ids )
    : [];

// 🔥 NEW: Get the style choice from the form
if (!isset($style)) {
    $style = isset($_POST['label_style']) ? sanitize_text_field($_POST['label_style']) : '1';
}

?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php echo esc_html__( 'Address Labels', 'green-angel' ); ?></title>
  <style>
    @page { 
      size: A4 portrait; 
      margin: 5mm;
    }
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body { 
      font-family: sans-serif; 
      line-height: 1;
    }
    /* CRITICAL: Force exact page control */
    .page {
      width: 200mm; /* Slightly less than A4 to prevent overflow */
      height: 287mm; /* Slightly less than A4 to prevent overflow */
      position: relative;
      overflow: hidden; /* Prevent any content spillage */
    }
    /* Only add page break if NOT the last page */
    .page:not(:last-child) {
      page-break-after: always;
    }
    .labels-container {
      display: grid;
      grid-template-columns: repeat(3, 63.5mm);
      grid-template-rows: repeat(7, 38.1mm);
      column-gap: 2mm;
      row-gap: 0;
      position: absolute;
      top: 7mm;   /* Was 17mm, moved up 10mm (1cm) */
      left: 0mm;  /* Was 5mm, moved left 5mm (0.5cm) */
      width: calc(3 * 63.5mm + 2 * 2mm);
      height: calc(7 * 38.1mm);
    }
    .label {
      width: 63.5mm;
      height: 38.1mm;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      
      /* 🔥 NEW: Dynamic font styles based on Jack's choice */
      <?php if ($style === '1'): ?>
        /* Classic - Clean & Professional */
        font-family: 'Arial', sans-serif;
        text-transform: none;
      <?php elseif ($style === '2'): ?>
        /* Modern Caps - Bold & Contemporary */
        font-family: 'Arial Black', sans-serif;
        text-transform: uppercase;
        font-weight: 900;
      <?php elseif ($style === '3'): ?>
        /* Handwritten - Personal & Friendly */
        font-family: 'Comic Sans MS', cursive;
        text-transform: none;
        font-style: italic;
      <?php elseif ($style === '4'): ?>
        /* Elegant Serif - Sophisticated */
        font-family: 'Times New Roman', serif;
        text-transform: capitalize;
      <?php elseif ($style === '5'): ?>
        /* Big + Bold - Can't Miss Energy */
        font-family: 'Impact', sans-serif;
        text-transform: uppercase;
        font-weight: 900;
        letter-spacing: 0.5px;
      <?php endif; ?>
    }
    .label .content {
      text-align: center;
      padding: 2mm;
      width: 100%;
    }
    .label h4 {
      <?php if ($style === '1'): ?>
        /* Classic - Everything same size, no bold hierarchy */
        font-size: 18px;
        font-weight: 400;
      <?php elseif ($style === '2'): ?>
        /* Modern Caps - Name slightly bigger */
        font-size: 20px;
        font-weight: 700;
      <?php elseif ($style === '3'): ?>
        /* Handwritten - All same weight, friendly */
        font-size: 19px;
        font-weight: 400;
      <?php elseif ($style === '4'): ?>
        /* Elegant - Name and postcode bold pattern */
        font-size: 19px;
        font-weight: 700;
      <?php elseif ($style === '5'): ?>
        /* Big Bold - Everything same weight, just HUGE */
        font-size: 18px;
        font-weight: 600;
      <?php endif; ?>
      color: #222;
      margin-bottom: 3px;
      line-height: 1.1;
    }
    .label p {
      <?php if ($style === '1'): ?>
        /* Classic - Everything uniform */
        font-size: 18px;
        font-weight: 400;
      <?php elseif ($style === '2'): ?>
        /* Modern Caps - Address smaller but still readable */
        font-size: 16px;
        font-weight: 500;
      <?php elseif ($style === '3'): ?>
        /* Handwritten - All same size, casual */
        font-size: 19px;
        font-weight: 400;
      <?php elseif ($style === '4'): ?>
        /* Elegant - Normal address, bold postcode */
        font-size: 17px;
        font-weight: 400;
      <?php elseif ($style === '5'): ?>
        /* Big Bold - Everything chunky */
        font-size: 18px;
        font-weight: 600;
      <?php endif; ?>
      color: #333;
      line-height: 1.3;
    }
    
    /* Special postcode styling for Style 4 */
    <?php if ($style === '4'): ?>
    .label .postcode {
      font-weight: 700;
      font-size: 19px;
    }
    <?php endif; ?>
    /* Debug borders - uncomment to see grid */
    /* .label { border: 0.5pt dotted rgba(0,200,0,0.5); } */
  </style>
</head>
<body>
<?php
// No IDs? Bail early
if ( empty( $ids ) ) {
    echo '<p><strong>' . esc_html__( 'No orders selected.', 'green-angel' ) . '</strong></p>';
    exit;
}

// Pagination logic
$total_labels     = count( $ids );
$labels_per_page  = 21;
$total_pages      = ceil( $total_labels / $labels_per_page );

// Loop through pages
for ( $page = 0; $page < $total_pages; $page++ ) {
    $start_index = $page * $labels_per_page;
    $end_index   = min( $start_index + $labels_per_page, $total_labels );

    echo '<div class="page">';
    echo   '<div class="labels-container">';

    $printed = 0;

    // Print each label
    for ( $i = $start_index; $i < $end_index; $i++ ) {
        $order_id = $ids[ $i ];
        $order    = wc_get_order( $order_id );

        if ( ! $order ) {
            // Error placeholder
            echo '<div class="label"><div class="content">';
            echo '<h4 style="color:red;">' . esc_html__( 'ERROR', 'green-angel' ) . '</h4>';
            echo '<p style="color:red;">' . esc_html( sprintf( __( 'Order #%d', 'green-angel' ), $order_id ) ) . '</p>';
            echo '</div></div>';
            $printed++;
            continue;
        }

        // Build recipient name
        $first = sanitize_text_field( $order->get_shipping_first_name() );
        $last  = sanitize_text_field( $order->get_shipping_last_name() );
        $name  = trim( $first . ' ' . $last );
        if ( empty( $name ) ) {
            $first = sanitize_text_field( $order->get_billing_first_name() );
            $last  = sanitize_text_field( $order->get_billing_last_name() );
            $name  = trim( $first . ' ' . $last );
        }

        // Build address lines
        $address_1 = $order->get_shipping_address_1() ?: $order->get_billing_address_1();
        $address_2 = $order->get_shipping_address_2() ?: $order->get_billing_address_2();
        $city      = $order->get_shipping_city()      ?: $order->get_billing_city();
        $state     = $order->get_shipping_state()     ?: $order->get_billing_state();
        $postcode  = $order->get_shipping_postcode()  ?: $order->get_billing_postcode();
        
        $lines = array_filter( [$address_1, $address_2, $city, $state] );
        $lines = array_map( 'sanitize_text_field', $lines );
        
        // Special handling for Style 4 (bold postcode)
        if ($style === '4') {
            $address_html = implode( '<br>', array_map( 'esc_html', $lines ) );
            if ($postcode) {
                $address_html .= '<br><span class="postcode">' . esc_html( sanitize_text_field($postcode) ) . '</span>';
            }
        } else {
            // Normal handling for other styles
            if ($postcode) $lines[] = sanitize_text_field($postcode);
            $address_html = implode( '<br>', array_map( 'esc_html', $lines ) );
        }

        // Render label with dynamic styling
        echo '<div class="label"><div class="content">';
        echo '<h4>' . esc_html( $name ) . '</h4>';
        echo '<p>' . $address_html . '</p>';
        echo '</div></div>';

        $printed++;
    }

    // Empty slots for alignment
    $empty = $labels_per_page - $printed;
    for ( $e = 0; $e < $empty; $e++ ) {
        echo '<div class="label"></div>';
    }

    echo   '</div>';
    echo '</div>';
}
?>

<script>
window.onload = function() {
  setTimeout(function() {
    window.print();
  }, 300);
};
</script>
</body>
</html>