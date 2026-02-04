# Version 60 to Version 63 - Official Changelog Summary

## Quick Overview

**Version 60 → 63** represents a significant update with major new features, improvements, and bug fixes across multiple versions (61.0.0, 61.0.1, 62.0.0, 63.0.0).

---

## 🎯 MAJOR NEW FEATURES (v60 → v63)

### Payment & Financial Features
1. **New Payment Gateways**:
   - ✅ **Plisio** (Cryptocurrency) - One-time payments
   - ✅ **Revolut** - One-time payments  
   - ✅ **Klarna** - One-time payments
   - ✅ **Paddle Billing** - One-time & recurring payments
   - ✅ **Offline Payment System** for Payment Blocks plugin

2. **Payment Management**:
   - ✅ Credit notes system for refunds/chargebacks
   - ✅ Mark payments as refunded, partially refunded, or chargeback
   - ✅ Manual payment logging via admin panel
   - ✅ Tax import via CSV
   - ✅ State & County based taxes
   - ✅ Bulk delete taxes

### Biolink Enhancements
1. **New Features**:
   - ✅ 6 new biolink themes
   - ✅ Branded Button & Modal feature
   - ✅ Pre-made shadow controls for blocks
   - ✅ Auto-apply theme settings to all pages with that theme
   - ✅ Set page language for SEO
   - ✅ Video file uploads for header block covers

2. **Improvements**:
   - ✅ Better block categories in create modal (dark mode support)
   - ✅ Fixed form inconsistencies with icons
   - ✅ Improved video block support (.mov files enabled)
   - ✅ Better timeline block (themeable)

### Admin Panel Features
1. **Plan Management**:
   - ✅ Disable/enable & re-order plan features from admin
   - ✅ Suggested plan upgrade feature with discounts
   - ✅ HTML support for plan descriptions
   - ✅ Export plans to JSON
   - ✅ Display base currency prices at a glance
   - ✅ Control text & background color of Plan Tags

2. **Other Admin Features**:
   - ✅ Fully categorized settings page sidebar
   - ✅ Improved codes display with more data & export (JSON, CSV)
   - ✅ IP blacklist from registration
   - ✅ Email domain blacklist

### Technical Infrastructure
1. **Performance & Caching**:
   - ✅ Redis caching support (alternative to file-based)
   - ✅ Improved session handling (better resource usage)
   - ✅ Minified remaining CSS/JS files
   - ✅ Improved cron jobs performance & tracking

2. **Docker & Compatibility**:
   - ✅ Full Docker support
   - ✅ PHP 8.5 support enabled

3. **Protocols & Tools**:
   - ✅ RDAP protocol for domain whois checking
   - ✅ Auto currency detection based on Country/County

### PWA Plugin Enhancements
- ✅ Auto-generate dynamic splash screens for iOS
- ✅ Translucent background header (like native apps)
- ✅ Fixed edge case issues on custom domains

### Pay Page Improvements
- ✅ Display percentage & dollar savings
- ✅ 3 new trust widgets after pay button
- ✅ Applied discounts show over all payment frequencies
- ✅ Show before & after price with discounts
- ✅ Display discount urgency/fomo (time remaining)

---

## 🐛 BUG FIXES (v60 → v63)

### Critical Fixes
- ✅ Fixed TWO FA authentication not being able to be disabled
- ✅ Fixed Spreadsheet Block not working properly
- ✅ Fixed email notifications for payment blocks
- ✅ Fixed biolink templates ordering
- ✅ Fixed PWA system issues on custom domains
- ✅ Fixed Signature Tool on mobile devices
- ✅ Fixed Email Shield plugin activation issues
- ✅ Fixed Stripe HUF currency payments
- ✅ Fixed Admin API Domains GET endpoint bugs
- ✅ Fixed SSO system potential issues

### UI/UX Fixes
- ✅ Fixed font size preview on biolink pages
- ✅ Fixed button sizing with font settings
- ✅ Fixed page width sizing on pages system
- ✅ Fixed horizontal scroll bar on homepage with blog posts
- ✅ Fixed form submission buttons (prevent double-click)
- ✅ Fixed admin panel Invoice page errors with certain taxes
- ✅ Fixed plan renewal price rounding
- ✅ Fixed account preference default (100 results per page issue)

### Other Fixes
- ✅ Fixed image optimizer logging wrong data
- ✅ Fixed Twilio notification handler testing
- ✅ Fixed plan translations fallback
- ✅ Fixed Razorpay payment gateway issues
- ✅ Fixed CSV exporter issues
- ✅ Fixed emailing broadcasts edge-case bugs
- ✅ Fixed cron jobs not properly reporting execution
- ✅ Fixed webhosts caching cron pages

---

## 📊 VERSION BREAKDOWN

### Version 63.0.0 (21 December, 2025)
- Offline payment system
- RDAP protocol
- Auto-apply biolink theme settings
- Page language for SEO
- Payment refund/chargeback marking
- Credit notes system
- Discount code start/end dates
- Full Docker support
- Various improvements and fixes

### Version 62.0.0
- 6 new biolink themes
- Branded Button & Modal
- Plisio & Revolut payment gateways
- Pre-made shadow controls
- Plan features control from admin
- Redis caching
- Auto currency detection
- PWA improvements
- Various improvements and fixes

### Version 61.0.1 (28 October, 2025)
- Appointment block improvements
- Bulk Short URL fix
- Block settings fixes
- Yookassa payment fix
- Free plan editing fix
- Service worker fixes
- Various other fixes

### Version 61.0.0 (22 October, 2025)
- Klarna & Paddle Billing payment gateways
- Suggested plan upgrade feature
- Manual payment logging
- Tax import & management
- HTML plan descriptions
- IP/Email blacklisting
- Various improvements and fixes

---

## ✅ IMPLEMENTATION STATUS

### Already Implemented (Core Files)
- ✅ Payment gateway helpers (Plisio, Revolut)
- ✅ Payment processor controllers
- ✅ Credit notes controllers
- ✅ Webhook handlers (Plisio, Revolut, Klarna, Paddle Billing)
- ✅ Session management helper
- ✅ Router updates with new routes
- ✅ Config.php Redis settings
- ✅ Available plan features include file
- ✅ Plisio cryptocurrencies include file

### Still Needed
- ⚠️ **View Files**: Copy view templates from v63 for:
  - Payment processors management
  - Credit notes
  - New payment gateway settings
  - Plan features admin interface
  - Tax import interface
  
- ⚠️ **Language Files**: Update translations for:
  - Payment processors
  - Credit notes
  - New payment gateways
  - Tax management
  - Plan features control

- ⚠️ **Database Updates**: May need SQL migrations for:
  - Payment processors table
  - Credit notes functionality
  - Tax import features
  - Plan features control
  - Discount code start/end dates

- ⚠️ **Theme Files**: Copy new biolink themes (6 new themes)

- ⚠️ **Asset Files**: 
  - Minified CSS/JS files
  - New icons/assets for payment gateways
  - PWA splash screen assets

---

## 🎯 KEY IMPROVEMENTS SUMMARY

### Performance
- Redis caching option
- Better session handling
- Minified CSS/JS
- Improved cron jobs
- Better resource usage

### User Experience
- Better pay page with savings display
- Trust widgets
- Discount urgency/fomo
- Improved biolink themes
- Better block controls

### Admin Experience
- Categorized settings
- Better plan management
- Tax import/management
- Manual payment logging
- Plan features control

### Developer Experience
- Docker support
- PHP 8.5 support
- Better API performance
- Improved documentation

---

## 📝 RECOMMENDATIONS

1. **Priority 1**: Copy view files and language files from v63
2. **Priority 2**: Run database migrations if SQL files are available
3. **Priority 3**: Copy new theme files (6 new biolink themes)
4. **Priority 4**: Test all new payment gateways thoroughly
5. **Priority 5**: Test credit notes and refund functionality
6. **Priority 6**: Verify Docker setup if planning to use it

---

## 🔒 SECURITY NOTES

- ✅ All new payment gateways have proper validation
- ✅ License checks maintained (Extended License required)
- ✅ Input validation in place
- ✅ Hash/signature validation for webhooks
- ⚠️ Review payment processor API keys configuration
- ⚠️ Monitor webhook logs for suspicious activity

---

**Summary Created**: 2025-01-27
**Based On**: Official AltumCode Changelog (v60 → v63)
**Status**: Core implementation complete, view/language files and database updates may be needed


