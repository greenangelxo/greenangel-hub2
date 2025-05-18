<?php
// 🌿 Green Angel Hub – Packing & Labels Module

// ─── Actions ──────────────────────────────────────────────────────────────
add_action('admin_post_greenangel_print_slips',        'greenangel_print_slips');
add_action('admin_post_nopriv_greenangel_print_slips', 'greenangel_print_slips');
add_action('admin_post_greenangel_print_labels',       'greenangel_print_labels');
add_action('admin_post_nopriv_greenangel_print_labels','greenangel_print_labels');

function greenangel_render_packing_slips_tab() {
    // Pre-compute our URLs for JS
    $print_slips_url  = esc_js( admin_url('admin-post.php?action=greenangel_print_slips') );
    $print_labels_url = esc_js( admin_url('admin-post.php?action=greenangel_print_labels') );
    ?>
    <div class="title-bubble">📦 Packing & Labels</div>

    <!-- ─── Styles ─────────────────────────────────────────────────────── -->
    <style>
      .pack-container { max-width:1200px; margin:20px auto; padding-bottom:40px; }
      .pack-table { width:100%; border-collapse:separate; border-spacing:0; border-radius:14px; overflow:hidden; margin-bottom:0; }
      .pack-table th, .pack-table td { background:#222; color:#fff; padding:16px 18px; text-align:center; vertical-align:middle; font-size:14px; }
      .pack-table thead th { background:#222; font-weight:600; }
      .pack-table tbody tr:hover td { background:#2a2a2a; }
      .pack-table th:nth-child(1), .pack-table td:nth-child(1) { width:5%; }
      .pack-table th:nth-child(2), .pack-table td:nth-child(2) { width:12%; }
      .pack-table th:nth-child(3), .pack-table td:nth-child(3) { width:25%; }
      .pack-table th:nth-child(4), .pack-table td:nth-child(4) { width:20%; }
      .pack-table th:nth-child(5), .pack-table td:nth-child(5),
      .pack-table th:nth-child(6), .pack-table td:nth-child(6) { width:19%; }
      .order-id { background:rgba(174,214,4,0.2); color:#aed604; padding:6px 10px; border-radius:6px; font-weight:500; display:inline-block; }
      .select-all { cursor:pointer; }
      .action-btn { background:#aed604; color:#222; border:none; padding:8px 12px; border-radius:14px; font-size:14px; cursor:pointer; transition:0.2s; }
      .action-btn:hover { transform:scale(1.05); }
      .card-bubble { display:inline-block; padding:8px 14px; border-radius:20px; font-size:13px; font-weight:500; }
      .card-none  { background:#7f8c8d; color:#fff; }
      .card-angel { background:#27ae60; color:#fff; }
      .card-affil { background:#2980b9; color:#fff; }
      .bulk-wrapper { display:inline-flex; align-items:center; background:#222; padding:14px 20px; border-radius:14px; }
      .bulk-wrapper .action-btn { margin-left:10px; }
    </style>

    <!-- ─── Single-order JS ───────────────────────────────────────────────── -->
    <script>
    function printSingleOrder(id, type) {
      var form = document.getElementById("pack-form");
      form.target = "_blank";
      form.print_type.value = type + "_" + id;

      if (type === "labels") {
        // find the row & see if it has .card-none
        var row    = document.querySelector('.pack-row input[name="orders[]"][value="' + id + '"]').closest("tr");
        var noCard = !!row.querySelector(".card-none");
        form.labels_copies.value = noCard
          ? 1
          : (confirm("Print TWO labels for this order?\nOK = double, Cancel = single") ? 2 : 1);
      } else {
        form.labels_copies.value = 1;
      }

      // pick the right endpoint
      form.action = (type === "labels" ? "<?php echo $print_labels_url; ?>" : "<?php echo $print_slips_url; ?>");

      // inject this order & submit
      var tmp = document.createElement("input");
      tmp.type  = "hidden";
      tmp.name  = "orders[]";
      tmp.value = id;
      form.appendChild(tmp);
      form.submit();
      form.removeChild(tmp);
    }
    </script>

    <?php
    // ─── Fetch orders ─────────────────────────────────────────────────
    $orders = wc_get_orders([
      'status'  => 'ship-today',
      'limit'   => -1,
      'orderby' => 'date',
      'order'   => 'DESC',
    ]);

    if ( empty($orders) ) {
      echo '<div class="empty-state"><p style="font-size:18px;">
              No orders marked <strong style="color:#aed604;">Ship Today</strong>. ✨
            </p></div>';
      return;
    }
    ?>

    <!-- ─── Table & Form ─────────────────────────────────────────────────── -->
    <div class="pack-container">
      <form method="POST" id="pack-form">
        <input type="hidden" name="print_type"    value="">
        <input type="hidden" name="labels_copies"  value="1">
        <input type="hidden" name="orders[]"       value="">

        <table class="pack-table">
          <thead>
            <tr>
              <th><input type="checkbox" class="select-all" onchange="
                document.querySelectorAll('.pack-row input[type=checkbox]')
                  .forEach(cb=>cb.checked=this.checked);
              "></th>
              <th>Order #</th><th>Customer</th><th>Angel Card</th>
              <th>Packing Slip</th><th>Address Label</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($orders as $order):
            $id   = $order->get_id();
            $name = esc_html($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
            $raw  = get_post_meta($id, '_greenangel_card_issued', true);

            if      ($raw === 'affiliate') $card_html = '<span class="card-bubble card-affil">Angel Affiliate Card</span>';
            elseif  ($raw === 'angel')     $card_html = '<span class="card-bubble card-angel">Angel Card</span>';
            else                           $card_html = '<span class="card-bubble card-none">None Required</span>';
          ?>
            <tr class="pack-row">
              <td><input type="checkbox" name="orders[]" value="<?php echo esc_attr($id); ?>"></td>
              <td><span class="order-id">#<?php echo $id; ?></span></td>
              <td><?php echo $name; ?></td>
              <td><?php echo $card_html; ?></td>
              <td>
                <button type="button"
                        onclick="printSingleOrder(<?php echo $id; ?>,'slips')"
                        class="action-btn">🖨️</button>
              </td>
              <td>
                <button type="button"
                        onclick="printSingleOrder(<?php echo $id; ?>,'labels')"
                        class="action-btn">🏷️</button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>

        <!-- ─── Bulk Actions ─────────────────────────────────────────── -->
        <div style="height:20px;"></div>
        <div style="text-align:right;"><div class="bulk-wrapper">

          <!-- Bulk Packing Slips (always single) -->
          <button type="button" class="action-btn"
                  onclick="
                    var f = document.getElementById('pack-form');
                    f.target = '_blank';
                    f.labels_copies.value = 1;
                    f.print_type.value   = 'slip_bulk';
                    f.action            = '<?php echo $print_slips_url; ?>';
                    f.submit();
                  ">
            🖨️ Generate Packing Slips
          </button>

          <!-- Bulk Address Labels with all-none / all-cards / mixed logic -->
          <button type="button" class="action-btn"
                  onclick="
                    var f = document.getElementById('pack-form');
                    f.target = '_blank';

                    // 1) collect checked order IDs
                    var selected = Array.from(
                      document.querySelectorAll('.pack-row input[name=\'orders[]\']:checked')
                    ).map(function(cb){ return cb.value; });

                    // 2) see if any have no-card vs any have card
                    var hasNone = false, hasCard = false;
                    selected.forEach(function(id){
                      var row = document.querySelector(
                        '.pack-row input[name=\'orders[]\'][value=\''+id+'\']'
                      ).closest('tr');
                      if ( row.querySelector('.card-none') ) hasNone = true;
                      else                                  hasCard = true;
                    });

                    // 3) decide copies
                    if      (!hasCard)                  f.labels_copies.value = 1;  // none need cards
                    else if (!hasNone)                  f.labels_copies.value = 2;  // all have cards
                    else                                 // mixed → prompt
                      f.labels_copies.value = confirm(
                        'Print TWO labels per order? OK=double, Cancel=single'
                      ) ? 2 : 1;

                    // 4) submit
                    f.print_type.value = 'label_bulk';
                    f.action          = '<?php echo $print_labels_url; ?>';
                    f.submit();
                  ">
            🏷️ Generate Address Labels
          </button>

        </div></div>
      </form>
    </div>
    <?php
}

// ─── Print Handlers ────────────────────────────────────────────────────
function greenangel_print_slips() {
  if (! current_user_can('manage_woocommerce')) wp_die('Permission denied');
  $ids = isset($_POST['orders']) ? array_map('intval',(array)$_POST['orders']) : [];
  if (empty($ids)) wp_die('No orders selected.');
  require plugin_dir_path(__FILE__).'templates/print-slips.php';
  exit;
}

function greenangel_print_labels() {
  if (! current_user_can('manage_woocommerce')) wp_die('Permission denied');

  $original = isset($_POST['orders'])       ? array_map('intval',(array)$_POST['orders'])       : [];
  $copies   = isset($_POST['labels_copies'])? intval($_POST['labels_copies'])                  : 1;
  if (empty($original)) wp_die('No orders selected.');

  // server‐side safety: force single when no card is required
  $ids = [];
  foreach ($original as $id) {
    $raw = get_post_meta($id, '_greenangel_card_issued', true);
    $order_copies = empty($raw) ? 1 : $copies;
    for ($i = 0; $i < $order_copies; $i++) {
      $ids[] = $id;
    }
  }

  require plugin_dir_path(__FILE__).'templates/print-labels.php';
  exit;
}