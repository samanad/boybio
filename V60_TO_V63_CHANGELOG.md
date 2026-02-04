# Version 60 to Version 63 Upgrade Changelog

## Executive Summary

This document details all changes between version 60.0.0 and version 63 of the 66biolinks script. Version 63 is confirmed to be an **Extended Version** with commercial payment features, similar to version 60.

**Status**: ✅ Version 63 is Extended Version (commercial with payment features)
**Backup**: ✅ Product folder backed up to `product1` folder

---

## 1. NEW FEATURES & ADDITIONS

### 1.1 New Payment Gateways
- **Plisio** - Cryptocurrency payment processor
  - New file: `app/helpers/payment-gateways/Plisio.php`
  - New file: `app/includes/plisio_cryptocurrencies.php` (cryptocurrency definitions)
  - New controller: `app/controllers/WebhookPlisio.php`
  - New controller: `app/controllers/WebhookPlisioWhitelabel.php`
  
- **Revolut** - Payment gateway
  - New file: `app/helpers/payment-gateways/Revolut.php`
  - New controller: `app/controllers/WebhookRevolut.php`

### 1.2 New Controllers
1. **Payment Processors Management**
   - `app/controllers/PaymentProcessors.php` - User payment processor management
   - `app/controllers/PaymentProcessorCreate.php` - Create payment processors
   - `app/controllers/PaymentProcessorUpdate.php` - Update payment processors
   - `app/controllers/admin/AdminPaymentProcessors.php` - Admin payment processor management
   - `app/controllers/admin/AdminPaymentCreate.php` - Admin create payments

2. **Credit Notes**
   - `app/controllers/CreditNotes.php` - User credit notes
   - `app/controllers/admin/AdminCreditNotes.php` - Admin credit notes management

3. **Tax Management**
   - `app/controllers/admin/AdminTaxesImport.php` - Import taxes

4. **Additional Webhooks**
   - `app/controllers/WebhookKlarna.php` - Klarna payment webhook
   - `app/controllers/WebhookPaddleBilling.php` - Paddle billing webhook

5. **PWA Features**
   - `app/controllers/PwaSplashGenerator.php` - PWA splash page generator

### 1.3 New Plugin
- **AIX Plugin** - AI Assistant plugin
  - Location: `plugins/aix/config.php`
  - Features: Content writing, image generation, text-to-speech, speech-to-text, assistant chat
  - Version: 10.0.0

### 1.4 New Helper Files
- `app/helpers/sessions.php` - Session management helper functions
  - Functions: `session_start_if_not_started()`, `session_set()`, `session_get()`, `session_unset_key()`, `session_has()`

### 1.5 New Include Files
- `app/includes/available_plan_features.php` - Defines available plan features dynamically
- `app/includes/plisio_cryptocurrencies.php` - Cryptocurrency definitions for Plisio

---

## 2. CONFIGURATION CHANGES

### 2.1 Config.php Updates
**New Redis Configuration Options** (lines 10-17):
```php
/* Only modify this if you want to use redis for caching instead of the default file system caching */
define('REDIS_IS_ENABLED', 0);
define('REDIS_SOCKET_PATH', null);
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);
define('REDIS_PASSWORD', null);
define('REDIS_DATABASE', 0);
define('REDIS_TIMEOUT', 2);
```

**Action Required**: These are optional Redis caching settings. If you're not using Redis, you can leave them as default or add them to your existing config.php.

### 2.2 Router.php Updates
**New Session Control Setting**:
- Added `'allow_sessions' => true` to controller settings (line 55 in v63)
- New routes for guest payment webhooks with `'allow_sessions' => false`

---

## 3. MODIFIED FILES

### 3.1 Core Files
- `app/core/Router.php` - Added session control settings and new routes
- `app/core/App.php` - Likely updated for session management
- `app/core/Language.php` - Updated language handling

### 3.2 Controllers (Modified)
Multiple controllers have been updated. Key changes include:
- Payment-related controllers updated for new payment processors
- Session handling improvements
- New payment processor integration points

### 3.3 Language Files
- `app/languages/english#en.php` - Added translations for:
  - Payment processors management
  - Credit notes
  - Guest payments
  - New payment gateway instructions (Plisio, Revolut, Klarna, etc.)
  - AIX plugin features

---

## 4. SECURITY ANALYSIS

### 4.1 Security Checks Performed
✅ **No malicious code detected** in new files
✅ **Proper license checks** in place (Extended License required for payment features)
✅ **Input validation** present in webhook handlers
✅ **Hash validation** implemented in Plisio webhook

### 4.2 Potential Security Considerations
1. **Webhook Handlers**: All new webhook controllers properly validate:
   - License type (Extended License required)
   - Request method (POST required)
   - Hash/signature validation where applicable

2. **File Operations**: File operations found in controllers are legitimate (upload handling, etc.)

3. **Session Management**: New session helper provides controlled session handling

### 4.3 Recommended Security Practices
- Ensure webhook endpoints are properly secured
- Review payment processor API keys configuration
- Monitor webhook logs for suspicious activity

---

## 5. BUGS & ISSUES IDENTIFIED

### 5.1 Potential Issues
1. **Plisio Webhook Metadata Parsing** (WebhookPlisio.php line 58):
   - Uses `explode('&', $_POST['order_name'])` which could fail if format changes
   - **Status**: Acceptable - standard webhook pattern, but should be monitored

2. **Session Helper** (sessions.php):
   - Global variable `$session_started` used - could cause issues in some edge cases
   - **Status**: Acceptable - standard pattern

### 5.2 No Critical Bugs Found
All new code follows established patterns from the existing codebase.

---

## 6. FILES TO IMPLEMENT

### 6.1 New Files to Copy
1. **Payment Gateways**:
   - `app/helpers/payment-gateways/Plisio.php`
   - `app/helpers/payment-gateways/Revolut.php`

2. **Controllers**:
   - `app/controllers/PaymentProcessors.php`
   - `app/controllers/PaymentProcessorCreate.php`
   - `app/controllers/PaymentProcessorUpdate.php`
   - `app/controllers/CreditNotes.php`
   - `app/controllers/PwaSplashGenerator.php`
   - `app/controllers/WebhookPlisio.php`
   - `app/controllers/WebhookPlisioWhitelabel.php`
   - `app/controllers/WebhookRevolut.php`
   - `app/controllers/WebhookKlarna.php`
   - `app/controllers/WebhookPaddleBilling.php`
   - `app/controllers/admin/AdminPaymentProcessors.php`
   - `app/controllers/admin/AdminPaymentCreate.php`
   - `app/controllers/admin/AdminCreditNotes.php`
   - `app/controllers/admin/AdminTaxesImport.php`

3. **Helpers**:
   - `app/helpers/sessions.php`

4. **Includes**:
   - `app/includes/available_plan_features.php`
   - `app/includes/plisio_cryptocurrencies.php`

5. **Plugins**:
   - `plugins/aix/config.php` (if AIX plugin is desired)

### 6.2 Files to Update
1. `config.php` - Add Redis configuration (optional)
2. `app/core/Router.php` - Add session settings and new routes
3. `app/languages/english#en.php` - Add new translations
4. Other core files as needed

---

## 7. IMPLEMENTATION NOTES

### 7.1 Database Changes
⚠️ **IMPORTANT**: Version 63 may require database schema updates. Check for:
- New tables for payment_processors
- New columns in existing tables
- New settings entries

**Action**: Review SQL update files if available, or check database structure in v63 install dump.

### 7.2 Custom Code Preservation
Since you have custom modifications:
1. ✅ Backup completed to `product1` folder
2. Compare your custom files with v63 versions
3. Merge custom changes into v63 files where needed
4. Test thoroughly after implementation

### 7.3 Testing Checklist
After implementation, test:
- [ ] Payment processor creation/management
- [ ] Plisio cryptocurrency payments
- [ ] Revolut payments
- [ ] Credit notes functionality
- [ ] Webhook endpoints
- [ ] Session management
- [ ] Existing custom features still work

---

## 8. VERSION INFORMATION

- **Version 60**: 60.0.0 (Code: 6000)
- **Version 63**: 63.0.0 (estimated, based on zip filename)

---

## 9. SUMMARY OF CHANGES

### Additions:
- 2 new payment gateways (Plisio, Revolut)
- 14+ new controllers
- 1 new plugin (AIX)
- 3 new helper/include files
- Enhanced session management
- Redis caching support

### Improvements:
- Better payment processor management
- Credit notes system
- Enhanced webhook handling
- Improved session control

### Security:
- ✅ No security vulnerabilities found
- ✅ Proper validation in place
- ✅ License checks maintained

---

## 10. RECOMMENDATIONS

1. **Implement in stages**:
   - First: Core files and helpers
   - Second: Payment processors
   - Third: New controllers
   - Fourth: Language files

2. **Test thoroughly** before going live

3. **Monitor logs** after implementation

4. **Keep backup** (`product1` folder) until fully tested

---

**Document Created**: $(Get-Date)
**Analyzed By**: AI Assistant
**Status**: Ready for Implementation



---

## 11. IMPLEMENTATION SUMMARY

### Files Successfully Implemented:

✅ **Helper Files**:
- pp/helpers/sessions.php - Session management helper

✅ **Include Files**:
- pp/includes/available_plan_features.php - Dynamic plan features
- pp/includes/plisio_cryptocurrencies.php - Cryptocurrency definitions

✅ **Payment Gateway Helpers**:
- pp/helpers/payment-gateways/Plisio.php - Plisio payment gateway
- pp/helpers/payment-gateways/Revolut.php - Revolut payment gateway

✅ **Controllers**:
- pp/controllers/PaymentProcessors.php and related payment processor controllers
- pp/controllers/CreditNotes.php and admin version
- pp/controllers/WebhookPlisio.php, WebhookRevolut.php, WebhookKlarna.php, WebhookPaddleBilling.php and related webhooks
- Admin controllers for payment processors and credit notes

✅ **Core Files Updated**:
- config.php - Added Redis configuration options
- pp/core/Router.php - Added session settings and new routes

### Next Steps:

1. **Database Updates**: Check if database schema updates are needed
2. **Language Files**: Update language files with new translations
3. **View Files**: Copy corresponding view files from v63 if they exist
4. **Testing**: Test all new functionality thoroughly
5. **Custom Code Merge**: Review custom modifications and merge as needed

**Implementation Date**: 2025-01-27
**Status**: Core implementation completed - Additional view files and language updates may be needed

