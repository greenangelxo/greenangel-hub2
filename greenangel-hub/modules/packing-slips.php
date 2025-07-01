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
      .pack-container { 
        max-width: 1200px; 
        margin: 20px auto; 
        padding: 0;
        background: #1a1a1a;
        border-radius: 24px;
        overflow: hidden;
        border: 2px solid rgba(174,214,4,0.2);
      }
      
      /* Header section */
      .pack-header {
        background: linear-gradient(135deg, #222 0%, #2a2a2a 100%);
        padding: 24px 32px;
        border-bottom: 2px solid rgba(174,214,4,0.2);
      }
      .pack-header h2 {
        margin: 0;
        color: #aed604;
        font-size: 24px;
        font-weight: 600;
      }
      
      /* Summary section */
      .pack-summary {
        background: #1e1e1e;
        padding: 24px 32px;
        border-bottom: 2px solid rgba(174,214,4,0.1);
      }
      
      /* Table section */
      .pack-table-section {
        background: #1a1a1a;
        padding: 0;
      }
      .pack-table { 
        width: 100%; 
        border-collapse: separate; 
        border-spacing: 0; 
        margin: 0;
        background: transparent;
      }
      .pack-table th, .pack-table td { 
        background: #222; 
        color: #fff; 
        padding: 16px 18px; 
        text-align: center; 
        vertical-align: middle; 
        font-size: 14px;
        border-bottom: 1px solid rgba(174,214,4,0.1);
      }
      .pack-table thead th { 
        background: #2a2a2a; 
        font-weight: 600;
        border-bottom: 2px solid rgba(174,214,4,0.3);
      }
      .pack-table tbody tr:hover td { 
        background: #2a2a2a; 
      }
      .pack-table tbody tr:last-child td {
        border-bottom: none;
      }
      .pack-table th:nth-child(1), .pack-table td:nth-child(1) { width:5%; }
      .pack-table th:nth-child(2), .pack-table td:nth-child(2) { width:12%; }
      .pack-table th:nth-child(3), .pack-table td:nth-child(3) { width:25%; }
      .pack-table th:nth-child(4), .pack-table td:nth-child(4) { width:20%; }
      .pack-table th:nth-child(5), .pack-table td:nth-child(5),
      .pack-table th:nth-child(6), .pack-table td:nth-child(6) { width:19%; }
      
      /* Controls section */
      .pack-controls {
        background: linear-gradient(135deg, #1e1e1e 0%, #222 100%);
        padding: 24px 32px;
        border-top: 2px solid rgba(174,214,4,0.2);
      }
      
      .order-id { 
        background: #aed604; 
        color: #222; 
        padding: 8px 14px; 
        border-radius: 20px; 
        font-weight: 600; 
        display: inline-block; 
        font-size: 13px;
        border: none;
      }
      .select-all { cursor:pointer; }
      .action-btn { background:transparent; color:#fff; border:none; padding:8px; border-radius:8px; font-size:18px; cursor:pointer; transition:0.2s; }
      .action-btn:hover { transform:scale(1.1); }
      .card-bubble { display:inline-block; padding:8px 14px; border-radius:20px; font-size:13px; font-weight:500; }
      .card-none  { background:#7f8c8d; color:#fff; }
      .card-angel { background:#27ae60; color:#fff; }
      .card-affil { background:#2980b9; color:#fff; }
      .bulk-wrapper { display:inline-flex; align-items:center; background:rgba(174,214,4,0.1); padding:14px 20px; border-radius:16px; border: 1px solid rgba(174,214,4,0.2); }
      .bulk-wrapper .action-btn { background:#aed604; color:#222; padding:8px 12px; border-radius:14px; font-size:14px; margin-left:10px; }
      
      /* Style picker in controls */
      .style-picker-bubble {
        display: inline-flex;
        align-items: center;
        background: rgba(174,214,4,0.1);
        padding: 12px 20px;
        border-radius: 16px;
        margin-bottom: 0;
        border: 1px solid rgba(174,214,4,0.2);
      }
      .style-picker-bubble label {
        color: #aed604;
        margin-right: 12px;
        font-weight: 500;
        font-size: 14px;
      }
      .style-picker-bubble select {
        background: #333;
        color: #aed604;
        border: 2px solid rgba(174,214,4,0.3);
        padding: 8px 12px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 500;
        outline: none;
        cursor: pointer;
        transition: border-color 0.2s ease;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
      }
      .style-picker-bubble select:hover {
        border-color: #aed604;
        color: #aed604;
      }
      .style-picker-bubble select:focus {
        border-color: #aed604;
        color: #aed604;
      }
      .style-picker-bubble select option {
        background: #333;
        color: #aed604;
        padding: 8px;
      }
      .style-picker-bubble select option:hover {
        background: #444;
        color: #aed604;
      }
      .style-picker-bubble select option:checked {
        background: #444;
        color: #aed604;
      }
      
      /* Actions section styling */
      .actions-section {
        background: transparent;
        padding: 0;
        border-radius: 0;
        border: none;
      }
      .actions-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
      }
      
      /* ─── Prep Summary Styles ───────────────────────────────────────── */
      .prep-summary-wrapper { display:flex; gap:20px; flex-wrap:wrap; margin:0; }
      @media (max-width:800px) { .prep-summary-wrapper { flex-direction:column; } }
      .prep-summary-bubble {
        background: linear-gradient(135deg, #2a2a2a 0%, #333 100%); 
        color: #fff; 
        border-left: 4px solid #aed604;
        padding: 20px 24px; 
        border-radius: 16px; 
        font-family: Poppins,sans-serif;
        border: 1px solid rgba(174,214,4,0.2);
        flex: 1;
        min-width: 300px;
      }
      .prep-summary-bubble h3 { margin-top:0; font-size:1.4em; color:#aed604; }
      .prep-summary-bubble p { margin:6px 0; font-size:1.1em; }
      .breakdown {
        max-height:280px; overflow-y:auto; margin-top:15px; padding:12px;
        background: rgba(0,0,0,0.3); border-radius:8px; font-size:12px;
        scrollbar-width:thin; scrollbar-color:#aed604 #222;
        border: 1px solid rgba(174,214,4,0.1);
      }
      .breakdown::-webkit-scrollbar { width:6px; }
      .breakdown::-webkit-scrollbar-track { background:#222; }
      .breakdown::-webkit-scrollbar-thumb {
        background:#aed604; border-radius:4px;
      }
      .empty-state {
        padding: 40px;
        text-align: center;
        color: #aed604;
      }
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

    function generateBulkLabels() {
      var f = document.getElementById("pack-form");
      f.target = "_blank";

      // collect checked order IDs
      var selected = Array.from(
        document.querySelectorAll('.pack-row input[name="orders[]"]:checked')
      ).map(cb=>cb.value);

      // check card status
      var hasNone=false, hasCard=false;
      selected.forEach(function(id){
        var row = document.querySelector('.pack-row input[value="'+id+'"]').closest("tr");
        if (row.querySelector('.card-none')) hasNone=true; else hasCard=true;
      });

      // decide copies
      var copies = !hasCard ? 1
                 : !hasNone ? 2
                 : (confirm("Print TWO labels per order?\nOK = double, Cancel = single")?2:1);

      f.labels_copies.value = copies;
      f.print_type.value   = 'label_bulk';
      f.action             = "<?php echo $print_labels_url; ?>";
      
      // 🔥 Add this (inject style choice from dropdown)
      const styleInput = document.createElement("input");
      styleInput.type = "hidden";
      styleInput.name = "label_style";
      styleInput.value = document.getElementById("label_style").value;
      f.appendChild(styleInput);
      
      f.submit();
      f.removeChild(styleInput); // cleanup
    }
    </script>

    <?php
    // ─── Fetch orders ─────────────────────────────────────────────────
    $orders_today = wc_get_orders([
      'status'  => 'ship-today',
      'limit'   => -1,
      'orderby' => 'date',
      'order'   => 'DESC',
    ]);
    
    // 🌟 FIXED: Fetch tomorrow's orders BEFORE checking if anything exists
    $target = date('Y-m-d', strtotime('+2 days'));
    
    // 🎯 Check BOTH old and new delivery date meta keys during transition
    $orders_tomorrow = wc_get_orders([
      'limit' => -1,
      'orderby' => 'date',
      'order' => 'ASC',
      'meta_query' => [
        'relation' => 'OR',  // ✨ This means it'll match EITHER condition
        [
          'key' => '_delivery_date',     // Your new custom system
          'value' => $target,
          'compare' => '='
        ],
        [
          'key' => 'delivery_date',      // Old CoderRockz system
          'value' => $target,
          'compare' => '='
        ]
      ]
    ]);
    
    // 🌟 NEW: Check if we have ANY orders to show (today OR tomorrow)
    if ( empty($orders_today) && empty($orders_tomorrow) ) {
      echo '<div class="empty-state"><p style="font-size:18px;">'
         . 'No orders to pack today or tomorrow. Time for a cuppa! ☕✨</p></div>';
      return;
    }
    ?>

    <!-- ─── Prep Summaries + Table & Form ───────────────────────────────── -->
    <div class="pack-container">
      
      <!-- Header Section -->
      <div class="pack-header">
        <h2>📦 Packing & Labels</h2>
      </div>

      <!-- Summary Section -->
      <div class="pack-summary">
        <div class="prep-summary-wrapper">
          <?php
            // 🌟 Show summaries even if one is empty
            greenangel_render_prep_summary($orders_today,   "🧁 Today's Prep Summary");
            greenangel_render_prep_summary($orders_tomorrow,"🕒 Tomorrow's Prep Summary");
          ?>
        </div>
      </div>

      <form method="POST" id="pack-form">
        <?php wp_nonce_field('greenangel_pack_action','greenangel_pack_nonce'); ?>
        <input type="hidden" name="print_type" value="">
        <input type="hidden" name="labels_copies" value="1">

        <!-- Table Section -->
        <?php if (!empty($orders_today)): // 🌟 Only show table if we have ship-today orders ?>
        <div class="pack-table-section">
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
            <?php foreach ($orders_today as $order):
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
                          class="action-btn">📄</button>
                </td>
                <td>
                  <button type="button"
                          onclick="printSingleOrder(<?php echo $id; ?>,'labels')"
                          class="action-btn">📮</button>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Controls Section -->
        <div class="pack-controls">
          <div class="actions-section">
            <div class="actions-row">
              <!-- Style Picker -->
              <div class="style-picker-bubble">
                <label for="label_style">🏷️ Label Style:</label>
                <select name="label_style" id="label_style">
                  <option value="1">Classic</option>
                  <option value="2">Modern Caps</option>
                  <option value="3">Handwritten</option>
                  <option value="4">Elegant Serif</option>
                  <option value="5">Big + Bold</option>
                </select>
              </div>
              
              <!-- Action Buttons -->
              <div class="bulk-wrapper">
                <!-- Bulk Packing Slips (always single) -->
                <button type="button" class="action-btn"
                        onclick="
                          var f=document.getElementById('pack-form');
                          f.target='_blank';
                          f.labels_copies.value=1;
                          f.print_type.value='slip_bulk';
                          f.action='<?php echo $print_slips_url; ?>';
                          f.submit();
                        ">
                  📄 Generate Packing Slips
                </button>

                <!-- Bulk Address Labels with mixed logic -->
                <button type="button" class="action-btn" onclick="generateBulkLabels()">
                  📮 Generate Address Labels
                </button>
              </div>
            </div>
          </div>
        </div>
        <?php else: // 🌟 Show message if no ship-today orders but we have tomorrow's orders ?>
        <div class="pack-controls">
          <p style="text-align: center; color: #aed604; font-size: 16px; padding: 20px;">
            No orders marked "Ship Today" yet, but you've got orders to prep for tomorrow! 🌟
          </p>
        </div>
        <?php endif; ?>
      </form>
    </div>
    <?php
}

function greenangel_print_slips() {
  if (! current_user_can('manage_woocommerce')) wp_die('Permission denied');
  check_admin_referer('greenangel_pack_action','greenangel_pack_nonce');
  $ids = isset($_POST['orders']) ? array_map('intval',(array)$_POST['orders']) : [];
  // Filter out any zero/empty values
  $ids = array_filter($ids, function($id) { return $id > 0; });
  if (empty($ids)) wp_die('No orders selected.');
  require plugin_dir_path(__FILE__).'templates/print-slips.php';
  exit;
}

function greenangel_print_labels() {
  if (! current_user_can('manage_woocommerce')) wp_die('Permission denied');
  check_admin_referer('greenangel_pack_action','greenangel_pack_nonce');

  $original = isset($_POST['orders']) ? array_map('intval',(array)$_POST['orders']) : [];
  // FIXED: Filter out any zero/empty values to prevent Order #0
  $original = array_filter($original, function($id) { return $id > 0; });
  
  $copies = isset($_POST['labels_copies']) ? intval($_POST['labels_copies']) : 1;
  if (empty($original)) wp_die('No orders selected.');

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

// ─── Prep‐Summary Renderer ─────────────────────────────────────────────────
function greenangel_render_prep_summary($orders, $title) {
    $pre_roll_sku_map  = [ 'GA-PRL-25'=>2, 'PR-BUNDLE-10'=>10, 'PR-BUNDLE-20'=>20, 'PR-BUNDLE-30'=>30 ];
    $cookie_dough_skus = ['GA-KKD-25'];
    $pre_roll_count    = 0;
    $cookie_count      = 0;

    foreach ($orders as $order) {
      foreach ($order->get_items() as $item) {
        $prod = $item->get_product(); if (!$prod) continue;
        $sku  = trim($prod->get_sku());
        $qty  = $item->get_quantity();
        if (isset($pre_roll_sku_map[$sku])) $pre_roll_count += $qty * $pre_roll_sku_map[$sku];
        if (in_array($sku, $cookie_dough_skus)) $cookie_count += $qty;
      }
    }

    echo '<div class="prep-summary-bubble">';
      echo "<h3>{$title}</h3>";
      echo '<p>🍪 Kookie Dough: <strong>' . esc_html($cookie_count)    . '</strong></p>';
      echo '<p>🌿 Pre-Rolls:     <strong>' . esc_html($pre_roll_count) . '</strong></p>';

      echo '<div class="breakdown">';
        echo '<strong>📋 Order Breakdown:</strong><br>';
        foreach ($orders as $order) {
          echo '<strong>Order #'.$order->get_id().':</strong><br>';
          foreach ($order->get_items() as $item) {
            $prod = $item->get_product(); if (!$prod) continue;
            $sku  = trim($prod->get_sku());
            $qty  = $item->get_quantity();
            $name = esc_html($prod->get_name());

            $line   = '&nbsp;&nbsp;- '.$name.' (SKU:"'.esc_html($sku).'") x'.$qty;
            $suffix = '';
            if (isset($pre_roll_sku_map[$sku])) {
              $suffix .= ' → '.($qty * $pre_roll_sku_map[$sku]).' pre-rolls';
            }
            if (in_array($sku, $cookie_dough_skus)) {
              $suffix .= ' → '.$qty.' kookie dough';
            }
            $line .= $suffix;

            if (isset($pre_roll_sku_map[$sku]) || in_array($sku, $cookie_dough_skus)) {
              echo '<strong>'.$line.'</strong><br>';
            } else {
              echo $line.'<br>';
            }
          }
          echo '<br>';
        }
      echo '</div>';
    echo '</div>';
}