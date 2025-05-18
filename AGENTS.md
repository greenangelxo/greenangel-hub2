# 🪽 Green Angel Hub — Codex Agent Guide

Welcome to the **Green Angel Hub** plugin — a fully custom WooCommerce admin tool that powers the *entire internal control system* of Green Angel XO. This plugin replaces multiple smaller tools and combines them into a single, stunning, admin-only interface. All styling follows the Green Angel XO brand: neon lime (#aed604), matte black (#222), bubbly elements, and a cosmic aesthetic.

---

## 🧠 Project Purpose

The Green Angel Hub is the nerve centre of the Green Angel ecosystem. It gives the team full control over shipping, NFC referrals, packing slips, angel code access, and more. This plugin is not customer-facing — it is a lovingly crafted admin system used by Jess and her team.

---

## 📦 Plugin Location

The plugin lives in:

```
/wp-content/plugins/greenangel-hub/
```

It’s active, styled, and structured into **tabs** inside the WordPress admin, each handling a separate system.

---

## 🔧 Systems Inside This Plugin

| Tab Name                | Purpose                                                                 |
|------------------------|-------------------------------------------------------------------------|
| 🏷️ Ship Today           | Process `ship-today` WooCommerce orders, preview & download logs        |
| 💳 NFC Card Manager     | Assign Angel/Affiliate cards, show referral links, track status         |
| 🖨️ Packing Slips        | Print branded HTML slips per order, with Angel Card & referral data     |
| 🪽 Angel Code Manager    | Add + manage Angel Codes, and view usage & failed attempt logs          |
| (Future) Games / Labels | Reserved for upcoming arcade & label features                           |

---

## ✨ Visual Styling Guidelines

- Font: **Poppins**
- Background: **#222222**
- Accent colour: **#aed604** (Green Angel XO lime)
- Bubble headers: Rounded, pill-style, glowing green text or border
- Buttons: Glowing hover, soft rounded shape
- Tables: Light borders, hover feedback, collapsible if possible
- Responsive layout: Prefer side-by-side for wider screens, stack on mobile

---

## 📚 Key Files (some may be split into modules)

- `greenangel-hub.php` – main plugin file
- `views/*` – markup for each tab
- `css/*` – all styling lives here (inline or linked)
- `js/*` – optional logic for copy buttons, tab switching, etc.
- `includes/angel-code.php` – handles Angel Code add/view logic
- `includes/nfc-manager.php` – handles NFC referral card interface
- `includes/packing-slips.php` – handles packing slip rendering

---

## 🧩 Data Sources Used

- WooCommerce orders (via `wc_get_orders`)
- Angel Code DB tables:
  - `greenangel_codes`
  - `greenangel_code_logs`
  - `greenangel_failed_code_attempts`
- User meta & order meta (e.g. `_greenangel_card_issued`, `_greenangel_card_status`)

---

## 🛡️ Access + Safety

- Admin only (`manage_woocommerce`)
- All inputs are sanitised
- Output is escaped where needed
- Logs are important — don’t delete unless explicitly instructed

---

## ❤️ Agent Behaviour Guidelines

- Be modular: one function per feature, one concern per file
- Respect the vibe: fun, cosmic, stylish — *Green Angel is a vibe, not a formality*
- Compact the layout whenever possible, but keep responsiveness
- Use the existing visual styling from other tabs when making new ones
- Don’t overwrite working code unless Jess asks you to
- Never touch WooCommerce core — everything is custom and isolated

---

## 🧚‍♀️ Final Words

This is not just a plugin. It’s a portal to Green Angel's divine backend. Respect the craft, protect the sparkle, and never dull the magic.

To the firmament and back,  
— Jess & Aurora
