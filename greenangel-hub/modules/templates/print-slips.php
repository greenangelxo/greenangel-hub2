<?php
/**
 * Green Angel Packing Slip Template
 * Matches the Photoshop design with FLY HIGH ANGEL header - CLEAN PRINT VERSION
 */
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Packing Slips</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* Super aggressive print CSS to remove ALL headers and footers */
    @page {
      size: A4;
      margin: 0;
    }
    
    @media print {
      /* Hide all browser headers/footers */
      head, header, footer {
        display: none !important;
      }
      
      /* Reset body margins for print */
      body {
        margin: 2cm !important;
        padding: 0 !important;
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
      }
      
      /* Hide absolutely everything that might be added by WordPress */
      #adminmenumain, #wpadminbar, #wpfooter, .update-nag, .updated, 
      .error, .is-dismissible, header, footer, .no-print {
        display: none !important;
      }
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: "Poppins", sans-serif;
      background: #fff;
      color: #222;
    }
    .slip {
      page-break-after: always;
      max-width: 100%;
      overflow: hidden;
      padding-top: 1cm; /* Add padding at top to push content down */
    }
    
    /* Logo and header styles */
    .logo-section {
      display: flex;
      justify-content: space-between;
      margin-bottom: 30px;
    }
    .logo {
      width: 180px;
    }
    .ship-to {
      text-align: right;
      font-size: 13px;
    }
    .ship-to h3 {
      font-weight: 600;
      margin-bottom: 4px;
      text-transform: uppercase;
    }
    .ship-to p {
      margin: 0;
      line-height: 1.4;
    }
    
    /* Header title */
    .header-title {
      font-size: 20px;
      font-weight: 700;
      margin-bottom: 25px;
      text-transform: uppercase;
      border-bottom: 1px solid #eee;
      padding-bottom: 10px;
    }
    
    /* Order details */
    .order-details {
      float: right;
      text-align: right;
      font-size: 13px;
      margin-top: -50px;
    }
    
    /* Rounded table styles */
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
    
    /* Add rounded corners to the table */
    .items tr:first-child th:first-child {
      border-top-left-radius: 8px;
    }
    .items tr:first-child th:last-child {
      border-top-right-radius: 8px;
    }
    .items tr:last-child td:first-child {
      border-bottom-left-radius: 8px;
    }
    .items tr:last-child td:last-child {
      border-bottom-right-radius: 8px;
    }
    
    /* Fix borders for rounded corners */
    .items tr:first-child th {
      border-top: 1px solid #ddd;
    }
    .items tr th:first-child, .items tr td:first-child {
      border-left: 1px solid #ddd;
    }
    .items tr th:last-child, .items tr td:last-child {
      border-right: 1px solid #ddd;
    }
    .items tr:last-child td {
      border-bottom: 1px solid #ddd;
    }
    
    /* Check column */
    .check-col {
      width: 40px;
      text-align: center;
    }
    
    /* Card styles - bold instead of green */
    .card-item { 
      font-weight: 700; 
    }
    .card-desc {
      display: block;
      font-size: 11px;
      color: #555;
      font-weight: normal;
      margin-top: 3px;
      font-style: italic;
    }
    
    /* Footer */
    .footer {
      text-align: center;
      font-size: 14px;
      font-weight: 600;
      margin-top: 60px;
      margin-bottom: 60px; /* Add space at bottom to ensure footer isn't cut off */
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

<?php if (empty($ids) || !is_array($ids)): ?>
  <p><strong>No orders selected.</strong></p>
<?php else: foreach ($ids as $id):
    $order = wc_get_order($id);
    if (! $order) continue;

    // Pull this order's card meta
    $card_type = get_post_meta($id, '_greenangel_card_issued', true);

    // (We no longer act on $card_needed here; only explicit assignments)
    
    // Get shipping address components
    $shipping_first_name = $order->get_shipping_first_name();
    $shipping_last_name  = $order->get_shipping_last_name();
    $shipping_address_1  = $order->get_shipping_address_1();
    $shipping_address_2  = $order->get_shipping_address_2();
    $shipping_city       = $order->get_shipping_city();
    $shipping_state      = $order->get_shipping_state();
    $shipping_postcode   = $order->get_shipping_postcode();
?>
  <div class="slip">    
    <div class="logo-section">
      <div class="logo">
        <img src="<?php echo esc_url( plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'modules/assets/logo-packing-slip.png' ); ?>" alt="Green Angel" width="180">
      </div>
      <div class="ship-to">
        <h3>SHIP TO:</h3>
        <p>
          <?php echo esc_html("$shipping_first_name $shipping_last_name"); ?><br>
          <?php echo esc_html($shipping_address_1); ?><br>
          <?php if ($shipping_address_2) echo esc_html("$shipping_address_2<br>"); ?>
          <?php echo esc_html($shipping_city); ?><br>
          <?php if ($shipping_state) echo esc_html("$shipping_state<br>"); ?>
          <?php echo esc_html($shipping_postcode); ?>
        </p>
      </div>
    </div>

    <div class="header-title">
      FLY HIGH ANGEL - YOUR PACKING SLIP
    </div>

    <div class="order-details">
      <p><strong>ORDER NUMBER:</strong> #<?php echo $order->get_id(); ?></p>
      <p><strong>ORDER DATE:</strong> <?php echo $order->get_date_created()->date('d/m/y'); ?></p>
    </div>

    <table class="items">
      <thead>
        <tr>
          <th>#</th>
          <th>Product</th>
          <th>Qty</th>
          <th class="check-col">✓</th>
        </tr>
      </thead>
      <tbody>
        <?php $i = 1; foreach ($order->get_items() as $item): ?>
        <tr>
          <td><?php echo $i++; ?></td>
          <td><?php echo esc_html($item->get_name()); ?></td>
          <td><?php echo esc_html($item->get_quantity()); ?></td>
          <td class="check-col"></td>
        </tr>
        <?php endforeach; ?>

        <!-- ONLY show a card row if one was explicitly assigned -->
        <?php if ($card_type === 'angel'): ?>
        <tr>
          <td><?php echo $i++; ?></td>
          <td>
            <span class="card-item">Angel Card</span>
            <span class="card-desc">NFC loyalty card - tap on friends phone to load our store with your personal referral code. Earns ££ off future orders.</span>
          </td>
          <td>1</td>
          <td class="check-col"></td>
        </tr>
        <?php elseif ($card_type === 'affiliate'): ?>
        <tr>
          <td><?php echo $i++; ?></td>
          <td>
            <span class="card-item">Angel Affiliate Card</span>
            <span class="card-desc">NFC referral card - tap on phone to load our store with your unique referral code. Earns commission!</span>
          </td>
          <td>1</td>
          <td class="check-col"></td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <div class="footer">
      GREENANGELSHOP.COM
      <span class="thank-you">THANK YOU FOR YOUR ORDER.</span>
    </div>
  </div>
<?php endforeach; endif; ?>

<script>
// Enhanced print script with additional cleanup
window.onload = function() {
  // Remove any default headers/footers Chrome might add
  var style = document.createElement('style');
  style.innerHTML = `
    @media print {
      @page { 
        margin: 0; 
        size: A4;
      }
      body { margin: 2cm !important; }
      
      /* Hide all headers and footers */
      head, header, footer, .header-date, .header-packing-slips {
        display: none !important;
      }
    }
  `;
  document.head.appendChild(style);
  
  // Short timeout to ensure everything is loaded
  setTimeout(function() {
    window.print();
  }, 500);
};
</script>
</body>
</html>