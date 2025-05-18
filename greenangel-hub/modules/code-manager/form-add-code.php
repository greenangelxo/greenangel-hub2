<?php
// 🌿 Add New Angel Code Form

function greenangel_render_add_code_form() {
    $action = admin_url('admin-post.php');

    echo '<div class="code-add-block" style="margin-top:40px;">';
    echo '<div class="title-bubble" style="font-size:18px; margin-bottom:15px;">➕ Add New Angel Code</div>';

    echo '<form method="POST" action="'.$action.'" style="max-width:600px; background:#222; padding:30px; border-radius:16px; color:#fff;">';

    echo '<input type="hidden" name="action" value="greenangel_add_angel_code">';

    // Code
    echo '<label style="display:block; margin-bottom:8px;">Code</label>';
    echo '<input type="text" name="code" required style="width:100%; padding:12px; border-radius:10px; border:none; margin-bottom:16px;">';

    // Label
    echo '<label style="display:block; margin-bottom:8px;">Label (optional)</label>';
    echo '<input type="text" name="label" style="width:100%; padding:12px; border-radius:10px; border:none; margin-bottom:16px;">';

    // Type
    echo '<label style="display:block; margin-bottom:8px;">Type</label>';
    echo '<select name="type" style="width:100%; padding:12px; border-radius:10px; border:none; margin-bottom:16px;">
            <option value="promo">Promo</option>
            <option value="affiliate">Affiliate</option>
            <option value="main">Main</option>
          </select>';

    // Expiry
    echo '<label style="display:block; margin-bottom:8px;">Expires At (optional)</label>';
    echo '<input type="date" name="expires_at" style="width:100%; padding:12px; border-radius:10px; border:none; margin-bottom:16px;">';

    // Active toggle
    echo '<label style="display:block; margin-bottom:8px;">Is Active?</label>';
    echo '<select name="active" style="width:100%; padding:12px; border-radius:10px; border:none; margin-bottom:24px;">
            <option value="1">Yes</option>
            <option value="0">No</option>
          </select>';

    // Submit
    echo '<button type="submit" style="background:#aed604; color:#222; font-weight:600; padding:12px 20px; border:none; border-radius:20px; cursor:pointer;">Add Code</button>';

    echo '</form>';
    echo '</div>';
}