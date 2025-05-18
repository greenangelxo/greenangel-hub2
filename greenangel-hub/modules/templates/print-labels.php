<?php
/**
 * Green Angel Shipping Labels
 * 63.5×38.1mm labels, 21 per sheet (3×7)
 */
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Address Labels</title>
  <style>
    /* match your sticker sheet margins */
    @page { size: A4 portrait; margin: 5mm; }
    body { margin:0; padding:0; font-family:sans-serif; text-transform:uppercase; }

    .labels-container {
      display: grid;
      grid-template-columns: repeat(3, 63.5mm);
      grid-template-rows: repeat(7, 38.1mm);
      column-gap: 2mm;
      row-gap: 0;

      justify-items: center;
      align-items: center;

      /* ↓ offset down to where your labels actually start */
      margin-top: 17mm;  /* vertical offset you found worked */
      /* ↓ offset right to center horizontally */
      margin-left: 5mm;  /* tweak this value in mm */
    }

    .label {
      width:100%; height:100%;
      box-sizing: border-box;
      /* guide border commented out */
      /* border: 0.5pt dotted rgba(0,0,0,0.2); */
    }

    .label .content {
      max-width: calc(63.5mm - 6mm);
      text-align: center;
    }

    .label h4 {
      margin:0 0 4px;
      font-size:16px;
      font-weight:700;
      color:#222;
    }
    .label p {
      margin:0;
      line-height:1.2;
      font-size:14px;
      font-weight:600;
      color:#333;
    }
  </style>
</head>
<body>

<?php
if ( empty($ids) || ! is_array($ids) ) {
  echo '<p>No orders selected.</p>';
  exit;
}
echo '<div class="labels-container">';
foreach ( $ids as $id ) {
  $order = wc_get_order($id);
  if ( ! $order ) continue;

  $name = trim($order->get_shipping_first_name().' '.$order->get_shipping_last_name());
  $addr = array_filter([
    $order->get_shipping_address_1(),
    $order->get_shipping_address_2(),
    $order->get_shipping_city(),
    $order->get_shipping_state(),
    $order->get_shipping_postcode(),
  ]);
  $address = implode('<br>', array_map('esc_html', $addr));
  ?>
  <div class="label">
    <div class="content">
      <h4><?php echo esc_html($name); ?></h4>
      <p><?php echo $address; ?></p>
    </div>
  </div>
  <?php
}
for ($i = count($ids); $i < 21; $i++) {
  echo '<div class="label"><div class="content"></div></div>';
}
echo '</div>';
?>

<script>
  window.onload = () => setTimeout(() => window.print(), 300);
</script>
</body>
</html>