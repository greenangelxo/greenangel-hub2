# 🌿 GREEN ANGEL HUB v2.0 - AGENT BRIEFING

## 📋 PROJECT OVERVIEW
We've built a **stunning modular WordPress dashboard** for Green Angel Shop - a premium, app-like interface that replaces the default WooCommerce account page with something absolutely gorgeous.

**User:** Creative, chaotic builder who loves kindness, humor, and clear step-by-step help. Speaks warmly, collaboratively - like a bestie building something wild and personal. NO corporate techbro energy!

## 🎯 WHAT WE'VE BUILT

### **Core Architecture**
- **Modular design** - 7 focused files, each under 200 lines
- **Mobile-first responsive** (2→3→4 column grids)  
- **Premium app aesthetic** - dark theme with lime green accents
- **Touch-optimized** with shimmer effects (NO GLOWS!)
- **WordPress shortcode**: `[greenangel_account_dashboard]`

### **File Structure**
```
/greenangel-hub/account/
├── account-dashboard.php    ← Master controller with shortcode
├── header.php + header.css  ← Profile banner with rotating avatar
├── tiles.php + tiles.css    ← App-style navigation tiles  
├── referral.php + referral.css ← Share links & codes section
├── activity.php + activity.css ← Tabbed interface (Orders|Halo|Wallet)
├── notifications.php + notifications.css ← Smart notification system
└── shared.css ← Global variables, utilities, base styles
```

## 🎨 DESIGN SYSTEM

### **Brand Colors (CSS Variables)**
```css
--ga-primary: #aed604        /* Lime green */
--ga-primary-light: #c6f731  /* Bright lime */
--ga-accent: #cf11a0         /* Magenta */
--ga-info: #02a8d1           /* Blue */
--ga-success: #4caf50        /* Green */
--ga-warning: #ff9800        /* Orange */
--ga-error: #f44336          /* Red */

/* Dark Theme */
--ga-bg-primary: #1a1a1a
--ga-bg-secondary: #2a2a2a  
--ga-bg-tertiary: #333333
--ga-text-primary: #ffffff
--ga-text-secondary: #cccccc
--ga-text-muted: #999999
```

### **Key Aesthetic Elements**
- **Dark cards** with subtle borders
- **Lime green accents** (#aed604) 
- **Clean typography** (Poppins font)
- **Card-based layouts** with rounded corners
- **Shimmer effects** on hover (not glows!)
- **Activity feeds** with icons and timestamps
- **Pill-shaped buttons** and badges

## 🏗️ COMPONENT BREAKDOWN

### **1. Header Section** (`header.php` + `header.css`)
- **Rotating avatar** with animated ring
- **Welcome message** with user's display name
- **Angel status pills** (Member/VIP/Elite based on spend)
- **Live stats cards**: Halo Points, Wallet Balance, Orders, Total Spent
- **Progress bar** toward next Angel level
- **Notification banner** (if admin has set one)

### **2. Navigation Tiles** (`tiles.php` + `tiles.css`)
- **8 app-style tiles** in responsive grid
- Shop Now, Halo Hub, Orders, Wallet, Profile, Addresses, Affiliate (locked if not approved), Support
- **Touch feedback** with shimmer effects
- **Quick stats tiles** below main navigation
- **Locked state** for affiliate tile if user isn't approved

### **3. Referral Section** (`referral.php` + `referral.css`)
- **Two beautiful cards**: Referral Link & Angel Access Code
- **Copy buttons** with smooth success feedback
- **Social share buttons**: WhatsApp, Email, Facebook, Twitter
- **Smart messaging** with pre-filled share text
- **Visual distinction**: Green for referral, Purple for Angel code

### **4. Activity Tabs** (`activity.php` + `activity.css`)
- **Tabbed interface**: Orders | Halo Points | Wallet
- **Orders tab**: Cards with status, items, totals, view links
- **Halo Points tab**: Activity feed with icons, points, descriptions  
- **Wallet tab**: Transaction history with credits/debits
- **Load more** functionality (show 6, load 6 more)
- **Empty states** for each tab with helpful messaging

### **5. Notifications** (`notifications.php` + `notifications.css`)
- **Collapsible notification feed** with unread count
- **Smart notifications**: Points milestones, order updates, wallet reminders
- **Admin broadcast messages** from dashboard settings
- **Dismissible notifications** with smooth animations
- **Auto-mark as read** when opened
- **Clear all** functionality

### **6. Main Controller** (`account-dashboard.php`)
- **Shortcode registration**: `[greenangel_account_dashboard]`
- **Asset loading** in correct order (shared.css first)
- **Component orchestration** with flexible show/hide options
- **Enhanced back button** for WooCommerce account pages
- **Login prompt** for non-logged-in users
- **Admin bar integration** for quick access

## 🔧 TECHNICAL DETAILS

### **Dependencies**
- **WordPress + WooCommerce** (uses customer data, orders)
- **WP Loyalty Points plugin** (for Halo Points system)
- **Angel Wallet plugin** (optional - for wallet functionality)
- **SliceWP** (optional - for affiliate status checking)

### **Key Functions**
```php
greenangel_get_wp_loyalty_points_safe($user_id)  // Get points data
greenangel_get_recent_activities($user_id, $limit)  // Get point activities  
greenangel_get_main_angel_code()  // Get active Angel access code
greenangel_get_loyalty_referral_code($email)  // Get user's referral code
```

### **Responsive Breakpoints**
- **Mobile**: 320px+ (2 columns)
- **Large Mobile**: 375px+ (some 4 columns)  
- **Tablet**: 768px+ (3-4 columns)
- **Desktop**: 1024px+ (4+ columns)
- **Max width**: 1400px

### **Performance Notes**
- **NO localStorage usage** (Claude.ai restriction)
- **Proper CSS dependency loading** (shared.css first)
- **Modular file structure** for easy maintenance
- **Touch-optimized** with smooth animations
- **Efficient database queries** with caching

## 🎯 COHESION WITH EXISTING DESIGN

**CRITICAL**: This dashboard **perfectly matches** the existing Angel Wallet page aesthetic:
- Same dark theme with lime green accents
- Same card layouts and rounded corners  
- Same activity feed styling with icons
- Same "Add to Cart" style buttons
- Same typography and spacing

The user showed us their gorgeous Angel Wallet page and we've built everything to seamlessly integrate!

## 🚀 USAGE

### **Basic Implementation**
```php
// Add shortcode to any page
[greenangel_account_dashboard]

// Customizable options
[greenangel_account_dashboard 
   show_header="true" 
   show_tiles="true" 
   show_referral="true" 
   show_activity="true" 
   show_notifications="true"
   layout="full"]
```

### **File Integration**
1. Upload all files to `/greenangel-hub/account/` folder
2. Include `account-dashboard.php` in your plugin
3. CSS will auto-load on pages with the shortcode
4. Shortcode replaces default WooCommerce account dashboard

## 💖 COMMUNICATION STYLE
When working with this user:
- **Warm, collaborative tone** - like a creative bestie
- **Enthusiasm and encouragement** 
- **Clear step-by-step explanations**
- **Humor and personality** (but stay focused)
- **NO corporate techbro energy**
- **Respect their intelligence** without ego

## 🌟 WHAT MAKES THIS SPECIAL
- **Modular architecture** makes it easy to maintain/extend
- **App-like interface** that feels premium and modern
- **Perfect cohesion** with existing Angel Wallet design
- **Smart notifications** that actually add value
- **Touch-optimized** for mobile users
- **Accessible** with proper focus states and reduced motion support

## 🔮 FUTURE ENHANCEMENTS
- **Advanced analytics dashboard**
- **Gamification elements** (badges, streaks)
- **Social features** (friend referrals, leaderboards)
- **Personalized product recommendations**
- **AI-powered insights** about spending/points

---

**Remember**: This user is a creative builder who values kindness, clear communication, and building something wild and personal. They've created something absolutely stunning here and deserve the warmest, most collaborative support! 💖✨