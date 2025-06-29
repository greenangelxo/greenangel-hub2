<?php
/**
 * Green Angel Packing Slip Template
 * Matches the Photoshop design with FLY HIGH ANGEL header - CLEAN PRINT VERSION
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Only allow shop managers/admins
if ( ! current_user_can( 'manage_woocommerce' ) ) {
    wp_die( esc_html__( 'Unauthorized access', 'green-angel' ) );
}

// Sanitize and validate IDs
$ids = isset( $ids ) && is_array( $ids ) ? array_map( 'intval', $ids ) : [];
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php echo esc_html__( 'Packing Slips', 'green-angel' ); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* Super aggressive print CSS to remove ALL headers and footers */
    @page {
      size: A4;
      margin: 0;
    }
    @media print {
      head, header, footer { display: none !important; }
      body {
        margin: 2cm !important;
        padding: 0 !important;
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
      }
      #adminmenumain, #wpadminbar, #wpfooter, .update-nag, .updated,
      .error, .is-dismissible, header, footer, .no-print {
        display: none !important;
      }
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: "Poppins", sans-serif;
      background: #fff;
      color: #222;
    }
    .slip {
      page-break-after: always;
      max-width: 100%;
      overflow: hidden;
      padding-top: 1cm;
    }
    .logo-section {
      display: flex;
      justify-content: space-between;
      margin-bottom: 30px;
    }
    .logo { width: 180px; }
    .ship-to { text-align: right; font-size: 13px; }
    .ship-to h3 {
      font-weight: 600;
      margin-bottom: 4px;
      text-transform: uppercase;
    }
    .ship-to p { margin: 0; line-height: 1.4; }
    .header-title {
      font-size: 20px;
      font-weight: 700;
      margin-bottom: 25px;
      text-transform: uppercase;
      border-bottom: 1px solid #eee;
      padding-bottom: 10px;
    }
    .order-details {
      float: right;
      text-align: right;
      font-size: 13px;
      margin-top: -50px;
    }
    .items {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      margin: 12px 0;
    }
    .items th, .items td {
      border: 1px solid #ddd;
      padding: 10px 12px;
      font-size: 13px;
    }
    .items th {
      background: #f8f8f8;
      font-weight: 600;
      text-align: left;
    }
    .items tr:first-child th:first-child { border-top-left-radius: 8px; }
    .items tr:first-child th:last-child  { border-top-right-radius: 8px; }
    .items tr:last-child td:first-child  { border-bottom-left-radius: 8px; }
    .items tr:last-child td:last-child   { border-bottom-right-radius: 8px; }
    .check-col { width: 40px; text-align: center; }
    .card-item {
      font-weight: 700;
    }
    .card-desc {
      display: block;
      font-size: 11px;
      color: #555;
      font-style: italic;
      margin-top: 3px;
    }
    .footer {
      text-align: center;
      font-size: 14px;
      font-weight: 600;
      margin: 60px 0;
      text-transform: uppercase;
    }
    .thank-you {
      display: block;
      font-size: 13px;
      margin-top: 3px;
    }
    .clearfix::after { content:""; display:table; clear:both; }
  </style>
</head>
<body>

<?php if ( empty( $ids ) ) : ?>
  <p><strong><?php echo esc_html__( 'No orders selected.', 'green-angel' ); ?></strong></p>
<?php else : foreach ( $ids as $order_id ) :

    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        continue;
    }

    // Ensure meta is safe
    $card_type = sanitize_text_field( get_post_meta( $order_id, '_greenangel_card_issued', true ) );

    // Shipping address
    $first   = esc_html( $order->get_shipping_first_name() );
    $last    = esc_html( $order->get_shipping_last_name() );
    $addr1   = esc_html( $order->get_shipping_address_1() );
    $addr2   = esc_html( $order->get_shipping_address_2() );
    $city    = esc_html( $order->get_shipping_city() );
    $state   = esc_html( $order->get_shipping_state() );
    $postcode= esc_html( $order->get_shipping_postcode() );
?>
  <div class="slip">
    <div class="logo-section">
      <div class="logo">
        <img src="<?php echo esc_url( plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'modules/assets/logo-packing-slip.png' ); ?>"
             alt="<?php echo esc_attr__( 'Green Angel Logo', 'green-angel' ); ?>" width="180">
      </div>
      <div class="ship-to">
        <h3><?php echo esc_html__( 'SHIP TO:', 'green-angel' ); ?></h3>
        <p>
          <?php echo $first . ' ' . $last; ?><br>
          <?php echo $addr1; ?><br>
          <?php if ( $addr2 ) echo $addr2 . '<br>'; ?>
          <?php echo $city; ?><br>
          <?php if ( $state ) echo $state . '<br>'; ?>
          <?php echo $postcode; ?>
        </p>
      </div>
    </div>

    <div class="header-title">
      <?php echo esc_html__( 'FLY HIGH ANGEL - YOUR PACKING SLIP', 'green-angel' ); ?>
    </div>

    <div class="order-details">
      <p><strong><?php echo esc_html__( 'ORDER NUMBER:', 'green-angel' ); ?></strong> #<?php echo esc_html( $order->get_id() ); ?></p>
      <p><strong><?php echo esc_html__( 'ORDER DATE:', 'green-angel' ); ?></strong>
         <?php
         $created = $order->get_date_created();
         if ( $created ) {
             echo esc_html( date_i18n( 'd/m/y', $created->getTimestamp() ) );
         }
         ?>
      </p>
      <?php if ( $order->get_meta( '_delivery_date' ) ) : 
          $dd = sanitize_text_field( $order->get_meta( '_delivery_date' ) );
      ?>
      <p><strong><?php echo esc_html__( 'DELIVERY DATE:', 'green-angel' ); ?></strong>
         <?php echo esc_html( date_i18n( 'd/m/y', strtotime( $dd ) ) ); ?>
      </p>
      <?php endif; ?>
    </div>

    <table class="items">
      <thead>
        <tr>
          <th>#</th>
          <th><?php echo esc_html__( 'Product', 'green-angel' ); ?></th>
          <th><?php echo esc_html__( 'Qty', 'green-angel' ); ?></th>
          <th class="check-col">✓</th>
        </tr>
      </thead>
      <tbody>
        <?php $i = 1; foreach ( $order->get_items() as $item ) : ?>
        <tr>
          <td><?php echo esc_html( $i++ ); ?></td>
          <td><?php echo esc_html( $item->get_name() ); ?></td>
          <td><?php echo esc_html( $item->get_quantity() ); ?></td>
          <td class="check-col"></td>
        </tr>
        <?php endforeach; ?>

        <?php if ( 'angel' === $card_type ) : ?>
        <tr>
          <td><?php echo esc_html( $i++ ); ?></td>
          <td>
            <span class="card-item"><?php echo esc_html__( 'Angel Card', 'green-angel' ); ?></span>
            <span class="card-desc"><?php echo esc_html__( 'NFC loyalty card - tap on friends phone to load our store with your personal referral code. Earns ££ off future orders.', 'green-angel' ); ?></span>
          </td>
          <td>1</td>
          <td class="check-col"></td>
        </tr>
        <?php elseif ( 'affiliate' === $card_type ) : ?>
        <tr>
          <td><?php echo esc_html( $i++ ); ?></td>
          <td>
            <span class="card-item"><?php echo esc_html__( 'Angel Affiliate Card', 'green-angel' ); ?></span>
            <span class="card-desc"><?php echo esc_html__( 'NFC referral card - tap on phone to load our store with your unique referral code. Earns commission!', 'green-angel' ); ?></span>
          </td>
          <td>1</td>
          <td class="check-col"></td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <div class="footer">
      <?php echo esc_html__( 'GREENANGELSHOP.COM', 'green-angel' ); ?>
      <span class="thank-you"><?php echo esc_html__( 'THANK YOU FOR YOUR ORDER.', 'green-angel' ); ?></span>
    </div>
  </div>
<?php endforeach; endif; ?>

<script>
// Enhanced print script with additional cleanup
window.onload = function() {
  var style = document.createElement('style');
  style.innerHTML = `
    @media print {
      @page { margin: 0; size: A4; }
      body { margin: 2cm !important; }
      head, header, footer, .header-date, .header-packing-slips { display: none !important; }
    }
  `;
  document.head.appendChild(style);
  setTimeout(function() {
    window.print();
  }, 500);
};
</script>
</body>
</html>