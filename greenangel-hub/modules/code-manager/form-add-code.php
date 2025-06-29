<?php
// 🌿 Add New Angel Code Form (Clean Version for Card Wrapper)
function greenangel_render_add_code_form() {
    $action = admin_url('admin-post.php');
    ?>
    <form method="POST" action="<?php echo esc_url($action); ?>" class="angel-code-form">
        <input type="hidden" name="action" value="greenangel_add_angel_code">
        <?php wp_nonce_field('greenangel_add_code'); ?>
        
        <div class="angel-form-group">
            <label>Code</label>
            <input type="text" name="code" required class="angel-input" placeholder="Enter unique code">
        </div>
        
        <div class="angel-form-group">
            <label>Label (optional)</label>
            <input type="text" name="label" class="angel-input" placeholder="Description for this code">
        </div>
        
        <div class="angel-form-group">
            <label>Type</label>
            <div class="angel-select">
                <select name="type" class="angel-input">
                    <option value="promo">Promo</option>
                    <option value="affiliate">Affiliate</option>
                    <option value="main">Main</option>
                </select>
            </div>
        </div>
        
        <div class="angel-form-group">
            <label>Expires At (optional)</label>
            <input type="date" name="expires_at" class="angel-input">
        </div>
        
        <div class="angel-form-group">
            <label>Is Active?</label>
            <div class="angel-select">
                <select name="active" class="angel-input">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
        </div>
        
        <button type="submit" class="angel-button">
            <span>➕</span>
            Add Code
        </button>
    </form>
    <?php
}