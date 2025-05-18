<?php
// 🌿 Green Angel Hub – NFC Card Manager Module

use Wlr\App\Models\Users;

// 🔁 Save Card Assignment
add_action('admin_post_greenangel_save_card_status', 'greenangel_save_card_status');
function greenangel_save_card_status() {
    if (
        isset($_POST['order_id']) &&
        isset($_POST['card_issued']) &&
        current_user_can('manage_woocommerce')
    ) {
        $order_id    = intval($_POST['order_id']);
        $card_issued = sanitize_text_field($_POST['card_issued']);

        if (in_array($card_issued, ['angel', 'affiliate'])) {
            update_post_meta($order_id, '_greenangel_card_issued', $card_issued);
            update_post_meta($order_id, '_greenangel_card_status', 'issued');
        } else {
            delete_post_meta($order_id, '_greenangel_card_issued');
            delete_post_meta($order_id, '_greenangel_card_status');
        }
    }

    wp_redirect(admin_url('admin.php?page=greenangel-hub&tab=nfc-manager'));
    exit;
}

// 🧠 Get Referral Code
function greenangel_get_loyalty_referral_code($email) {
    if (!class_exists('Wlr\App\Models\Users')) return null;
    $user_model = new Users();
    $where      = sanitize_email($email);
    $user       = $user_model->getWhere("user_email = '{$where}'", '*', true);
    return (!empty($user) && isset($user->refer_code))
         ? $user->refer_code
         : null;
}

// 💳 Main Renderer
function greenangel_render_nfc_card_manager() {
    echo '<div class="title-bubble">💚 NFC Card Manager</div>';

    // ─── Styles ───────────────────────────────────────────────────────
    echo '<style>
      /* Table & Fonts */
      .nfc-table {
        background-color: #222;
        border-radius: 14px;
        overflow: hidden;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 20px;
        width: 100%;
        font-family: "Poppins",sans-serif!important;
        table-layout: fixed;
      }
      .nfc-table th, .nfc-table td {
        border: none;
        padding: 12px;
        text-align: center;
        vertical-align: middle;
      }
      .nfc-table td {
        background: #222;
        color: #fff;
        padding: 14px 16px;
      }
      .nfc-table thead { background: #222; height: 50px; }
      .header-bubble {
        display: inline-block;
        background: #aed604;
        color: #222;
        padding: 6px 12px;
        border-radius: 16px;
        font-weight: 500;
        font-size: 12px;
        white-space: nowrap;
      }

      /* Column Widths */
      .nfc-table th:nth-child(1), .nfc-table td:nth-child(1) { width: 8%; }
      .nfc-table th:nth-child(2), .nfc-table td:nth-child(2) { width: 15%; }
      .nfc-table th:nth-child(3), .nfc-table td:nth-child(3) { width: 8%; }
      .nfc-table th:nth-child(4), .nfc-table td:nth-child(4) { width: 8%; }
      .nfc-table th:nth-child(5), .nfc-table td:nth-child(5) { width: 30%; }
      .nfc-table th:nth-child(6), .nfc-table td:nth-child(6) { width: 20%; }
      .nfc-table th:nth-child(7), .nfc-table td:nth-child(7) { width: 11%; }

      /* Bold customer */
      .nfc-table td:nth-child(2) { font-weight: 600; }

      /* Hover effect */
      .nfc-table tbody tr:hover td { background: #2a2a2a; }

      /* Order & Price */
      .order-id {
        background: rgba(174,214,4,0.2);
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 500;
        color: #aed604;
        display: inline-block;
      }
      .price-tag { font-weight: 500; }
      .total-spend { font-weight: 500; color: #aed604; }

      /* Referral link */
      .referral-link {
        display: flex;
        align-items: center;
        gap: 10px;
        justify-content: center;
      }
      .referral-link code {
        background: rgba(255,255,255,0.1);
        padding: 8px 12px;
        border-radius: 8px;
        color: #aed604;
        border: 1px solid rgba(174,214,4,0.3);
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 13px;
      }
      .referral-link code:hover { max-width: 100%; transition: max-width 0.3s ease; }
      .copy-btn {
        background: #aed604;
        color: #222;
        border: none;
        padding: 8px 14px;
        font-weight: 500;
        border-radius: 20px;
        cursor: pointer;
        transition: 0.2s ease-in-out;
        font-size: 13px;
      }
      .copy-btn:hover { opacity: 0.9; transform: scale(1.05); }

      /* Card select */
      .angel-select { position: relative; display: inline-block; margin-right: 10px; }
      .angel-select select {
        padding: 10px 16px;
        border-radius: 20px;
        border: 1px solid rgba(174,214,4,0.3);
        background: #222;
        color: #fff;
        min-width: 100%;
        appearance: none;
        padding-right: 35px;
        cursor: pointer;
        font-size: 14px;
      }
      .angel-select select:hover,
      .angel-select select:focus {
        border-color: #aed604;
        color: #ffffff;           /* <- force white text on hover/focus */
      }
      .angel-select:after {
        content: "▼";
        font-size: 10px;
        color: #aed604;
        position: absolute;
        right: 15px; top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
      }

      /* Status bubbles */
      .status-bubble {
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 500;
        display: inline-block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 13px;
      }
      .status-first  { background: #e74c3c; color: #fff; }
      .status-needed { background: #3498db; color: #fff; }
      .status-issued { background: #27ae60; color: #fff; }

      /* Empty state */
      .empty-state {
        text-align: center;
        padding: 40px;
        background: #222;
        border-radius: 14px;
        margin-top: 20px;
        color: #fff;
      }

      /* Webkit fix */
      @media screen and (-webkit-min-device-pixel-ratio:0) {
        select { -webkit-appearance: none!important; }
      }
    </style>';

    // ─── Scripts ───────────────────────────────────────────────────────
    echo '<script>
      function copyToClipboard(url) {
        navigator.clipboard.writeText(url).then(
          ()=>alert("Referral link copied! 💖"),
          e=>alert("Copy failed: "+e)
        );
      }
      function confirmAndSubmit(sel) {
        const val = sel.value;
        if (val==="angel"||val==="affiliate") {
          const lab = val==="angel"?"Angel Card":"Angel Affiliate Card";
          if (!confirm(`💎 Are you sure you\\\'ve written the referral link to the NFC ${lab}?`)) {
            sel.value="";
            return;
          }
        }
        sel.closest("form").submit();
      }
    </script>';

    // ─── Fetch orders ──────────────────────────────────────────────────
    $orders = wc_get_orders([
      'status'  => 'ship-today',
      'limit'   => -1,
      'orderby' => 'date',
      'order'   => 'DESC',
    ]);

    if (empty($orders)) {
      echo '<div class="empty-state">';
      echo '<p style="font-size:18px;">No orders marked <strong style="color:#aed604;">Ship Today</strong>. ✨</p>';
      echo '</div>';
      return;
    }

    // ─── Table ─────────────────────────────────────────────────────────
    echo '<table class="nfc-table">';
      echo '<thead><tr>';
        echo '<th><span class="header-bubble">Order #</span></th>';
        echo '<th><span class="header-bubble">Customer</span></th>';
        echo '<th><span class="header-bubble">Order Total</span></th>';
        echo '<th><span class="header-bubble">Total Spend</span></th>';
        echo '<th><span class="header-bubble">Referral Link</span></th>';
        echo '<th><span class="header-bubble">Card Assigned</span></th>';
        echo '<th><span class="header-bubble">Status</span></th>';
      echo '</tr></thead>';
      echo '<tbody>';
      foreach ($orders as $order) {
        $id        = $order->get_id();
        $name      = $order->get_billing_first_name().' '.$order->get_billing_last_name();
        $email     = $order->get_billing_email();
        $total     = floatval($order->get_total());
        $spend     = 0;
        $ref_html  = '—';

        // gather customer history
        $cust_orders = wc_get_orders([
          'billing_email'=> $email,
          'status'       => ['completed','processing','on-hold','ship-today'],
          'limit'        => -1,
        ]);
        foreach ($cust_orders as $co) {
          $spend += floatval($co->get_total());
        }

        // referral link
        $ref  = greenangel_get_loyalty_referral_code($email);
        if ($ref) {
          $url = esc_url("https://greenangelshop.com?wlr_ref={$ref}");
          $ref_html = "<div class='referral-link'>
                        <code>{$url}</code>
                        <button class='copy-btn' onclick=\"copyToClipboard('{$url}')\">Copy</button>
                       </div>";
        }

        // card status detection
        $cur = get_post_meta($id, '_greenangel_card_issued', true);
        $just = !empty($cur);
        $prev_id = null; $prev_type = null;
        if (empty($cur)) {
          foreach ($cust_orders as $co) {
            $st  = get_post_meta($co->get_id(), '_greenangel_card_status', true);
            $tp  = get_post_meta($co->get_id(), '_greenangel_card_issued', true);
            if ($st==='issued' && $tp && $co->get_id()!==$id) {
              $cur = $tp;
              $prev_id   = $co->get_id();
              $prev_type = $tp;
              break;
            }
          }
        }

        // row markup
        echo '<tr>';
          echo '<td><span class="order-id">#'.$id.'</span></td>';
          echo '<td>'.esc_html($name).'</td>';
          echo '<td><span class="price-tag">£'.number_format($total,2).'</span></td>';
          echo '<td><span class="total-spend">£'.number_format($spend,2).'</span></td>';
          echo '<td>'.$ref_html.'</td>';

          // Card Assigned dropdown
          echo '<td>
                  <form method="POST" action="'.admin_url('admin-post.php').'" class="card-form">
                    <input type="hidden" name="action"   value="greenangel_save_card_status">
                    <input type="hidden" name="order_id" value="'.esc_attr($id).'">
                    <div class="angel-select">
                      <select name="card_issued" onchange="confirmAndSubmit(this)">
                        <option value="">Select card type…</option>
                        <option value="angel"    '.selected($cur,'angel',false).'>Angel Card</option>
                        <option value="affiliate"'.selected($cur,'affiliate',false).'>Angel Affiliate Card</option>
                      </select>
                    </div>
                  </form>
                </td>';

          // Status bubble
          $cnt = count($cust_orders);
          if ($prev_id && $prev_type) {
            echo '<td><span class="status-bubble status-issued">✅ Already issued on #'.$prev_id.'</span></td>';
          }
          elseif ($just) {
            echo '<td><span class="status-bubble status-issued">✅ Card assigned just now</span></td>';
          }
          else {
            if ($cnt === 1) {
              echo '<td><span class="status-bubble status-first">1st Order No Card</span></td>';
            } else {
              echo '<td><span class="status-bubble status-needed">Card Needed</span></td>';
            }
          }
        echo '</tr>';
      }
      echo '</tbody>';
    echo '</table>';
}