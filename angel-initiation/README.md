# 🦋 Angel Initiation Module

The Angel Initiation Module transforms new users into certified Angels through a mystical 4-step ceremony.

## ✨ Features

- **Step 1: Name Generator** - Dice-powered stoner name selection with RGB effects
- **Step 2: Tribe Sorting** - Mystical wheel assigns users to balanced tribes
- **Step 3: DOB Ritual** - Zodiac-styled birth date collection
- **Step 4: Final Blessing** - £5 reward with celebration effects

## 🚀 Installation

### Quick Integration
Add this line to your main `greenangel-hub.php` file:

```php
// Include Angel Initiation Module
require_once plugin_dir_path(__FILE__) . 'modules/angel-initiation/init.php';
```

Also add the page creation function to your force creation system:

```php
// Check and create Angel Initiation page
$angel_initiation_page_id = get_option('angel_initiation_page_id');
if (!$angel_initiation_page_id || !get_post($angel_initiation_page_id)) {
    greenangel_create_angel_initiation_page();
}
```

### Manual Integration
1. Copy the `angel-initiation` folder to your `modules/` directory
2. Include the initialization file in your main plugin
3. The page will be auto-created at `/angel-initiation`
4. No permalink flushing needed!

## 🔧 Configuration

### Admin Settings
Access via **GreenAngel Hub → Angel Initiation** in WordPress Admin.

### User Flow
1. New users are automatically redirected to `/angel-initiation`
2. Users complete 4 mystical steps via the `[angel_initiation_ceremony]` shortcode
3. Upon completion, users receive £5 credit and tribe assignment
4. Users are redirected to their dashboard

## 🎯 File Structure

```
angel-initiation/
├── init.php                           # Main initialization
├── integration.php                    # WordPress integration
├── angel-initiation.php              # Core functionality
├── templates/
│   └── initiation-page.php           # Main template
└── assets/
    ├── css/
    │   ├── initiation-base.css        # Core styles
    │   ├── step-1-name-generator.css  # Name generator styles
    │   ├── step-2-tribe-sorting.css   # Tribe sorting styles
    │   ├── step-3-dob-ritual.css      # DOB ritual styles
    │   └── step-4-blessing.css        # Final blessing styles
    └── js/
        ├── cosmic-background.js        # Background effects
        ├── initiation-core.js          # Core functionality
        ├── name-generator.js           # Name generator logic
        ├── tribe-sorting.js            # Tribe sorting logic
        ├── dob-ritual.js               # DOB ritual logic
        └── blessing-celebration.js     # Final blessing logic
```

## 🌟 Customization

### Tribes
Edit the `$tribes` array in `angel-initiation.php` to customize:
- Tribe names and emojis
- Tribe taglines
- Distribution algorithm

### Reward System
The module supports both:
- **Angel Wallet** credits (if available)
- **WooCommerce coupons** (fallback)

### Styling
All CSS files use CSS custom properties for easy theming:
```css
:root {
    --angel-black: #151515;
    --angel-green: #aed604;
    --angel-purple: #9333ea;
    --angel-blue: #3b82f6;
    /* ... */
}
```

## 🔮 User Meta Fields

The module stores these user meta fields:
- `_angel_onboarding_complete` - Completion flag
- `_angel_tribe` - Assigned tribe
- `_angel_initiation_completed` - Completion timestamp
- `_stoner_tag_locked` - Name locked flag
- `_birth_month` / `_birth_year` - Birth date
- `_angel_initiation_coupon` - Generated coupon code

## 🎨 Shortcodes

### Initiation Ceremony
Main initiation ceremony (automatically used on the page):
```php
[angel_initiation_ceremony]
```

### Initiation Status
Display user's initiation status:
```php
[angel_initiation_status]
```

## 🔍 Helper Functions

```php
// Check if user needs initiation
user_needs_angel_initiation($user_id);

// Get user's current step
get_user_initiation_step($user_id);

// Validate name format
validate_r_format($name);
```

## 🌈 Mobile Responsive

All components are fully responsive with:
- Tablet breakpoints at 768px
- Mobile breakpoints at 480px
- Touch-friendly interactions
- Optimized animations

## 🎪 Performance

- Lazy-loaded step assets
- Efficient AJAX communication
- Optimized animations
- Minimal DOM manipulation
- Clean CSS with BEM methodology

## 🛡️ Security

- CSRF protection with nonces
- Input sanitization
- SQL injection prevention
- XSS protection
- Capability checks

## 🚨 Troubleshooting

### Common Issues

**404 on /angel-initiation**
- Go to Settings → Permalinks and click Save

**Styles not loading**
- Check file permissions
- Verify asset paths
- Clear any caching

**AJAX errors**
- Check browser console
- Verify nonce generation
- Check user permissions

### Debug Mode
Enable WordPress debug to see detailed error messages:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 📊 Analytics

Track initiation completion rates through:
- WordPress Admin stats
- User meta queries
- Tribe distribution analysis
- Completion timestamps

## 🔄 Updates

The module is designed to be:
- Self-contained
- Backward compatible
- Database migration friendly
- Cache-aware

---

**Created with cosmic love by Claude Code** ✨🦋🌟