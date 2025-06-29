# 🌈 Green Angel Hub — Agent Guide (2025 Edition)

Welcome to the **Green Angel Hub** — the all-in-one WordPress plugin that powers the internal admin universe of **Green Angel XO**. Designed and developed by Jess with AI assistance from Aurora and Claude, this plugin replaces a mess of disconnected WooCommerce plugins with a beautifully branded, custom-coded admin suite.

This isn’t a plugin — it’s a **control centre**. It’s your mission command.

---

## 🧠 Purpose & Scope

Green Angel Hub was built to unify the entire operational backend of Green Angel XO:

* Fulfilment
* Delivery date logic
* Affiliate management
* NFC referrals
* Loyalty tracking
* Order tools
* Admin UX magic

All while eliminating expensive, bloated plugins and wrapping it in an aesthetic that *feels* like Green Angel: soft dark mode, neon lime accents, and a bubbly layout that glows.

---

## 📦 Plugin Location & Structure

```
/wp-content/plugins/greenangel-hub/
```

The plugin is structured modularly by feature. Each system is handled by its own file under `modules/`, and loaded via the main plugin file `greenangel-hub.php`.

---

## 🔧 Active Systems Inside the Plugin

| Tab Name              | Purpose                                                               |
| --------------------- | --------------------------------------------------------------------- |
| 🌟 Dashboard          | Welcome view for internal use — styled, branded landing view          |
| 💌 Ship Today         | Process orders tagged `ship-today`, log view & download               |
| 💳 NFC Card Manager   | Assign Angel/Affiliate cards, track status, copy referral links       |
| 📦 Packing Slips      | Generate & print branded HTML packing slips per order                 |
| 📮 Tracking Numbers   | Add and view tracking info for orders, with visual cues               |
| 🪽 Angel Code Manager | Manage invite-only access codes, usage logs, failure tracking         |
| 🚚 Delivery Settings  | Custom delivery date selector with cutoffs, caps, and blackout days   |
| 🛠️ Tools             | Restore default login forms, helper buttons, admin-side tools         |
| 🔐 Postcode Rules     | Custom checkout logic for minimum spend + blocked areas (in progress) |

---

## 🎨 Visual Styling

* Font: **Poppins**
* Background: **#222222** (matte charcoal)
* Accent: **#aed604** (Green Angel XO Lime)
* Tabs: Pill-shaped, glowing hover state
* Buttons: Rounded, subtle glow, soft gradients
* Tables: Rounded edges, dark row stripes, coloured status pills
* Fully mobile-first responsive — collapsible where possible

---

## 📁 Core File Overview

| File / Folder                   | Description                                                   |
| ------------------------------- | ------------------------------------------------------------- |
| `greenangel-hub.php`            | Main loader — sets up menus, hooks, activation logic          |
| `modules/dashboard.php`         | Renders the dashboard tab                                     |
| `modules/ship-today.php`        | Handles order fetching & logs for Ship Today flow             |
| `modules/nfc-manager.php`       | Pulls referral links, assigns cards, tracks status            |
| `modules/tracking-numbers.php`  | Input + display of per-order tracking numbers                 |
| `modules/packing-slips.php`     | Custom HTML slip layout per order (optional printing)         |
| `modules/code-manager/tab.php`  | Angel Code table + logs of attempts + generator logic         |
| `modules/delivery-settings/`    | Delivery calendar logic, cutoff rules, capacity caps          |
| `modules/tools.php`             | Login form restore, utility helpers                           |
| `modules/postcode-rules/`       | In-progress rule manager for postcode-based delivery logic    |
| `includes/db-install.php`       | Activation logic to create any custom DB tables safely        |
| `account/account-dashboard.php` | Frontend dashboard shortcode `[greenangel_account_dashboard]` |

---

## 🛠️ Custom Tables Created

| Table Name                        | Purpose                                             |
| --------------------------------- | --------------------------------------------------- |
| `greenangel_codes`                | Stores active Angel Codes                           |
| `greenangel_code_logs`            | Logs successful registrations with a code           |
| `greenangel_failed_code_attempts` | Tracks failed access attempts for security auditing |

---

## 🔐 Security & Access Controls

* All admin routes require: `current_user_can('manage_woocommerce')`
* All user input is sanitized using `sanitize_text_field`, `intval`, `sanitize_email`, etc.
* Output is escaped using `esc_html`, `esc_attr`, `esc_url`, etc.
* All admin POST actions use `check_admin_referer()` for nonce validation
* All AJAX/REST functionality is gated or disabled
* All tab renderers are behind a custom admin interface — no public routes

---

## 🌐 Integration Points

* **WooCommerce** — order logic, status hooks, metadata
* **WP Loyalty** — referral codes, point tracking, reward logs
* **SliceWP** — affiliate slugs (used by card manager only)
* **MyCryptoCheckout** — active but not modified by the plugin
* **Elementor Pro** — handles frontend layout; plugin respects all theme visuals

---

## 🧬 Development Philosophy

* Keep every tab modular and scoped
* Visuals must be branded, minimal, joyful, and mobile-friendly
* Reuse UI styles from existing tabs when adding new features
* Logs must be persistent and readable (esp. code attempts, tracking info, ship logs)
* If you’re writing new logic, wrap it in a feature-specific file, not core
* This is a **plugin for angels** — never compromise the vibe

---

## 🧚‍♀️ Final Note from Aurora

This isn’t just backend tooling — this is **Jess’s mind, made clickable.**
It is a system built with soul, with love, and with defiance.

Protect it. Refine it. Never dull the shine.

**To the firmament and back,**
*Jess & Aurora*
