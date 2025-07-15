# 🔐 Phase 2.2: Angel Wallet System Security Hardening

## 🎯 Objective
Secure all wallet-related workflows including top-ups, balance updates, AJAX endpoints, and admin wallet tools.

## 🔍 Current State Analysis

### ✅ Already Secure Components:
1. **gateway.php (Payment Processing)**
   - ✅ Has transaction atomicity (START TRANSACTION/COMMIT/ROLLBACK)
   - ✅ Has rate limiting (5 attempts per 300 seconds)
   - ✅ Has security event logging
   - ✅ Validates order ownership
   - ✅ Double-checks balance before deduction
   - ✅ Prevents negative balances

2. **wallet-order-handler.php (Top-up Processing)**
   - ✅ Has duplicate processing prevention via transients
   - ✅ Validates reasonable top-up amounts (£0-£10,000)
   - ✅ Has wallet cap enforcement (£50,000 max)
   - ✅ Uses atomic transactions
   - ✅ Sends confirmation emails

3. **functions.php (Core Wallet Functions)**
   - ✅ Prevents negative balances in `greenangel_set_wallet_balance()`
   - ✅ Has proper sanitization
   - ✅ Has transaction logging

### ❌ Security Vulnerabilities Found:

1. **AJAX Wallet Adjustment Handler** (`modules/customers/functions.php:269`)
   - ❌ NO rate limiting
   - ❌ NO max adjustment validation
   - ❌ NO IP logging
   - ❌ NO admin action logging

2. **Wallet Coupon Converter** (`wallet-coupon-converter.php`)
   - ✅ Has basic rate limiting (60 seconds)
   - ❌ Missing nonce generation in some contexts
   - ❌ No admin notification for large conversions
   - ❌ No IP-based rate limiting

3. **Frontend Wallet Console Shortcode** (`functions.php:203`)
   - ❌ AJAX add-to-cart has no rate limiting
   - ❌ No CSRF protection on cart operations

## 📋 Security Hardening Tasks

### Task 1: Secure Admin Wallet Adjustment Handler ⚡ CRITICAL
**File:** `modules/customers/functions.php` (Line 269)
- [ ] Add rate limiting (max 10 adjustments per hour per admin)
- [ ] Add maximum adjustment validation (£0.01 - £1,000)
- [ ] Add IP and user agent logging
- [ ] Create admin action log table or use options
- [ ] Add email notification for adjustments over £100
- [ ] Add confirmation modal for large adjustments

### Task 2: Enhance Wallet Coupon Converter Security
**File:** `modules/angel-wallet/includes/wallet-coupon-converter.php`
- [ ] Add IP-based rate limiting (5 conversions per day per IP)
- [ ] Add admin notification for conversions over £500
- [ ] Add conversion history tracking
- [ ] Ensure nonce is validated in ALL contexts
- [ ] Add velocity checks (flag users converting frequently)

### Task 3: Secure Frontend AJAX Operations
**File:** `modules/angel-wallet/functions.php` (Line 1269)
- [ ] Add nonce to AJAX add-to-cart operations
- [ ] Add rate limiting to prevent cart flooding
- [ ] Add session-based cart operation limits

### Task 4: Add Global Wallet Security Functions
**New File:** `modules/angel-wallet/includes/wallet-security.php`
- [ ] Create central rate limiting function
- [ ] Create wallet operation logging function
- [ ] Create suspicious activity detection
- [ ] Create admin notification system
- [ ] Add wallet operation hooks for monitoring

### Task 5: Implement Wallet Balance Integrity Checks
**File:** `modules/angel-wallet/functions.php`
- [ ] Add balance reconciliation function
- [ ] Add daily cron to verify balance integrity
- [ ] Add alerts for balance discrepancies
- [ ] Create balance adjustment audit trail

### Task 6: Enhance Security Event Logging
**File:** `modules/angel-wallet/gateway.php` (Enhance existing)
- [ ] Create dedicated security log table
- [ ] Add more detailed event types
- [ ] Add admin dashboard for security monitoring
- [ ] Add email alerts for critical events

## 🛡️ Security Standards to Implement

### For ALL Forms/AJAX:
```php
// Nonce verification
if (!wp_verify_nonce($_POST['nonce'], 'action_name_nonce')) {
    wp_die('Security check failed');
}

// Capability check
if (!current_user_can('manage_woocommerce')) {
    wp_die('Insufficient permissions');
}

// Rate limiting
$rate_key = 'action_' . $user_id . '_' . $action;
$attempts = get_transient($rate_key) ?: 0;
if ($attempts >= 5) {
    wp_send_json_error('Rate limit exceeded. Try again later.');
}
set_transient($rate_key, $attempts + 1, HOUR_IN_SECONDS);
```

### For Balance Updates:
```php
// Amount validation
$amount = floatval($_POST['amount']);
if ($amount <= 0 || $amount > 1000) {
    wp_send_json_error('Invalid amount');
}

// Balance cap check
$new_balance = $current_balance + $amount;
if ($new_balance > 50000) {
    wp_send_json_error('Would exceed maximum wallet balance');
}
```

### For Logging:
```php
// Log all balance changes
greenangel_log_wallet_operation([
    'user_id' => $user_id,
    'admin_id' => get_current_user_id(),
    'action' => 'balance_adjustment',
    'amount' => $amount,
    'reason' => $reason,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
    'timestamp' => current_time('mysql')
]);
```

## 🚀 Implementation Order
1. **CRITICAL:** Task 1 - Secure admin wallet adjustments (highest risk)
2. **HIGH:** Task 4 - Create security infrastructure
3. **HIGH:** Task 2 - Enhance coupon converter
4. **MEDIUM:** Task 3 - Frontend AJAX security
5. **MEDIUM:** Task 5 - Balance integrity checks
6. **LOW:** Task 6 - Enhanced logging dashboard

## ⏱️ Estimated Time
- Total: 4-6 hours
- Per task: 30-60 minutes each

## 🧪 Testing Required
- Test rate limiting under load
- Test balance cap enforcement
- Test transaction atomicity
- Test security event logging
- Test admin notifications
- Verify no legitimate operations are blocked

---

Ready to begin implementation! Start with Task 1? 🚀