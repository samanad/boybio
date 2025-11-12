# Migration Guide: Version 60 to Version 61

This document contains all changes made during Version 60. It is organized into two sections:
1. **Feature Additions/Upgrades** - Custom features and enhancements that should be reapplied
2. **Bug Fixes/Error Resolutions** - Fixes for errors that may already be resolved in the new version

**Important**: Before applying bug fixes, check if the new version already includes them. Only apply feature additions and any bug fixes that are still needed.

---

# PART 1: FEATURE ADDITIONS & UPGRADES

These are custom features and enhancements that should be reapplied to the new version.

---

## Feature: Subdirectory Redirect System

### Overview

The subdirectory redirect feature allows:
1. **404 Redirect on Custom Domains**: When a user visits a non-existent link on a custom domain (e.g., `boy.bio/x2`), they are redirected to the base URL with the same subdirectory (e.g., `linkdooni.com/x2`) after being informed the link is not available.
2. **Home Page Search Redirect**: When users search for available subdirectories on the home page, if the subdirectory is available (not registered), they are prompted to redirect to the base URL with that subdirectory.

The feature can be enabled from either:
- **Admin Settings → Main → Other Settings**
- **Admin Settings → Links** (after Claim URL settings)

---

### Files Modified for Subdirectory Redirect Feature

#### 1. `product/themes/altum/views/admin/settings/partials/main.php`

**Location**: After the "Custom 404 page URL" field, inside the `#other_settings_container` div

**Changes**: Add the following code after line 241 (after the `not_found_url` field):

```php
<div class="form-group custom-control custom-switch">
    <input id="subdirectory_redirect_is_enabled" name="subdirectory_redirect_is_enabled" type="checkbox" class="custom-control-input" <?= (isset(settings()->main->subdirectory_redirect_is_enabled) && settings()->main->subdirectory_redirect_is_enabled) ? 'checked="checked"' : null?>>
    <label class="custom-control-label" for="subdirectory_redirect_is_enabled"><i class="fas fa-fw fa-sm fa-redo text-muted mr-1"></i> <?= l('admin_settings.main.subdirectory_redirect_is_enabled') ?></label>
    <small class="form-text text-muted"><?= l('admin_settings.main.subdirectory_redirect_is_enabled_help') ?></small>
</div>

<div class="form-group">
    <label for="subdirectory_redirect_base_url"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('admin_settings.main.subdirectory_redirect_base_url') ?></label>
    <input id="subdirectory_redirect_base_url" type="url" name="subdirectory_redirect_base_url" class="form-control" value="<?= settings()->main->subdirectory_redirect_base_url ?? '' ?>" placeholder="https://linkdooni.com" />
    <small class="form-text text-muted"><?= l('admin_settings.main.subdirectory_redirect_base_url_help') ?></small>
</div>
```

---

#### 2. `product/themes/altum/views/admin/settings/partials/links.php`

**Location**: After the "Claim URL type" field (around line 223)

**Changes**: Add the following code after the `claim_url_type` select field:

```php
<div class="form-group custom-control custom-switch">
    <input id="subdirectory_redirect_is_enabled" name="subdirectory_redirect_is_enabled" type="checkbox" class="custom-control-input" <?= (isset(settings()->links->subdirectory_redirect_is_enabled) && settings()->links->subdirectory_redirect_is_enabled) ? 'checked="checked"' : null?>>
    <label class="custom-control-label" for="subdirectory_redirect_is_enabled"><i class="fas fa-fw fa-sm fa-redo text-muted mr-1"></i> <?= l('admin_settings.links.subdirectory_redirect_is_enabled') ?></label>
    <small class="form-text text-muted"><?= l('admin_settings.links.subdirectory_redirect_is_enabled_help') ?></small>
</div>

<div class="form-group">
    <label for="subdirectory_redirect_base_url"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('admin_settings.links.subdirectory_redirect_base_url') ?></label>
    <input id="subdirectory_redirect_base_url" type="url" name="subdirectory_redirect_base_url" class="form-control" value="<?= settings()->links->subdirectory_redirect_base_url ?? '' ?>" placeholder="https://linkdooni.com" />
    <small class="form-text text-muted"><?= l('admin_settings.links.subdirectory_redirect_base_url_help') ?></small>
</div>
```

---

#### 3. `product/app/controllers/admin/AdminSettings.php`

**In the `main()` method:**

**Location**: After the `not_found_url` validation (around line 106)

**Changes**: Add the following code:

```php
/* Process subdirectory redirect settings */
$_POST['subdirectory_redirect_is_enabled'] = (int) isset($_POST['subdirectory_redirect_is_enabled']);
$_POST['subdirectory_redirect_base_url'] = trim($_POST['subdirectory_redirect_base_url'] ?? '');

/* Validate base URL if enabled */
if($_POST['subdirectory_redirect_is_enabled'] && !empty($_POST['subdirectory_redirect_base_url'])) {
    $base_url_parsed = parse_url($_POST['subdirectory_redirect_base_url']);
    if(!isset($base_url_parsed['scheme']) || !isset($base_url_parsed['host'])) {
        Alerts::add_field_error('subdirectory_redirect_base_url', l('global.error_message.invalid_url'));
    }
}
```

**Location**: In the JSON encode array (around line 176)

**Changes**: Add these two lines after `'not_found_url'`:

```php
'subdirectory_redirect_is_enabled' => $_POST['subdirectory_redirect_is_enabled'],
'subdirectory_redirect_base_url' => $_POST['subdirectory_redirect_base_url'],
```

**In the `links()` method:**

**Location**: After `claim_url_type` processing (around line 1960)

**Changes**: Add the following code:

```php
/* Process subdirectory redirect settings */
$_POST['subdirectory_redirect_is_enabled'] = (int) isset($_POST['subdirectory_redirect_is_enabled']);
$_POST['subdirectory_redirect_base_url'] = trim($_POST['subdirectory_redirect_base_url'] ?? '');

/* Validate base URL if enabled */
if($_POST['subdirectory_redirect_is_enabled'] && !empty($_POST['subdirectory_redirect_base_url'])) {
    $base_url_parsed = parse_url($_POST['subdirectory_redirect_base_url']);
    if(!isset($base_url_parsed['scheme']) || !isset($base_url_parsed['host'])) {
        Alerts::add_field_error('subdirectory_redirect_base_url', l('global.error_message.invalid_url'));
    }
}
```

**Location**: In the JSON encode array (around line 2043)

**Changes**: Add these two lines after `'claim_url_type'`:

```php
'subdirectory_redirect_is_enabled' => $_POST['subdirectory_redirect_is_enabled'],
'subdirectory_redirect_base_url' => $_POST['subdirectory_redirect_base_url'],
```

---

#### 4. `product/app/core/Router.php`

**Location**: In the section where 404 is handled for custom domains (around line 1778)

**Changes**: Replace the existing 404 redirect logic with:

```php
/* Check for subdirectory redirect feature */
$subdirectory_redirect_enabled = (isset(settings()->main->subdirectory_redirect_is_enabled) && settings()->main->subdirectory_redirect_is_enabled) ||
                                 (isset(settings()->links->subdirectory_redirect_is_enabled) && settings()->links->subdirectory_redirect_is_enabled);
$subdirectory_redirect_base_url = !empty(settings()->main->subdirectory_redirect_base_url) ? settings()->main->subdirectory_redirect_base_url : 
                                   (!empty(settings()->links->subdirectory_redirect_base_url) ? settings()->links->subdirectory_redirect_base_url : '');

if(isset(self::$data['domain']) && 
   $subdirectory_redirect_enabled &&
   !empty($subdirectory_redirect_base_url) &&
   !empty(self::$params[0])) {
    
    /* Build redirect URL with subdirectory */
    $base_url = rtrim($subdirectory_redirect_base_url, '/');
    $subdirectory = self::$params[0];
    $redirect_url = $base_url . '/' . $subdirectory;
    
    /* Store message in session and redirect */
    $_SESSION['subdirectory_redirect_message'] = sprintf(l('not_found.subdirectory_redirect_message'), $subdirectory);
    $_SESSION['subdirectory_redirect_url'] = $redirect_url;
    
    header('Location: ' . url('not-found?subdirectory_redirect=1'));
    die();
}
```

**Location**: Add route for `check-url-availability` (around line 843, before 'spotlight')

**Changes**: Add the following route:

```php
'check-url-availability' => [
    'controller' => 'CheckUrlAvailability',
    'settings' => [
        'no_authentication_check' => true,
        'has_view' => false,
    ]
],
```

---

#### 5. `product/app/controllers/NotFound.php`

**Location**: At the beginning of the `index()` method (around line 23)

**Changes**: Add the following code before the "Custom 404 redirect if set" check:

```php
/* Handle subdirectory redirect */
if(isset($_GET['subdirectory_redirect']) && isset($_SESSION['subdirectory_redirect_url']) && isset($_SESSION['subdirectory_redirect_message'])) {
    $redirect_url = $_SESSION['subdirectory_redirect_url'];
    $message = $_SESSION['subdirectory_redirect_message'];
    
    /* Clear session variables */
    unset($_SESSION['subdirectory_redirect_url']);
    unset($_SESSION['subdirectory_redirect_message']);
    
    /* Show message and redirect */
    \Altum\Alerts::add_info($message);
    
    /* Redirect after a short delay to show message */
    header('Refresh: 2; url=' . $redirect_url);
}
```

---

#### 6. `product/themes/altum/views/index/index.php`

**Location**: In the JavaScript section for claim URL (around line 123-147)

**Changes**: Replace the existing JavaScript with:

```php
<?php ob_start() ?>
    <script>
'use strict';

    let claim_button_default_href = document.querySelector('#claim_button').href;
    let checkUrlTimeout = null;
    
    ['change', 'paste', 'keyup', 'keypress'].forEach(event_type => document.querySelector('#claim_url').addEventListener(event_type, event => {
        let url = get_slug(document.querySelector('#claim_url').value);
        let domain_id_element = document.querySelector('#domain_id');
        let domain_id = domain_id_element ? domain_id_element.value : null;

        let query_params = new URLSearchParams();
        if(url) query_params.set('claim-url', url);
        if(domain_id) query_params.set('domain-id', domain_id);

        document.querySelector('#claim_button').href = query_params.toString()
            ? `${claim_button_default_href}?${query_params}`
            : claim_button_default_href;

        /* Check URL availability if subdirectory redirect is enabled */
        <?php 
        $subdirectory_redirect_enabled = (isset(settings()->main->subdirectory_redirect_is_enabled) && settings()->main->subdirectory_redirect_is_enabled) ||
                                         (isset(settings()->links->subdirectory_redirect_is_enabled) && settings()->links->subdirectory_redirect_is_enabled);
        $subdirectory_redirect_base_url = !empty(settings()->main->subdirectory_redirect_base_url) ? settings()->main->subdirectory_redirect_base_url : 
                                           (!empty(settings()->links->subdirectory_redirect_base_url) ? settings()->links->subdirectory_redirect_base_url : '');
        if($subdirectory_redirect_enabled && !empty($subdirectory_redirect_base_url)): ?>
        if(url && url.length >= 3) {
            /* Debounce the check */
            clearTimeout(checkUrlTimeout);
            checkUrlTimeout = setTimeout(() => {
                $.ajax({
                    type: 'POST',
                    url: <?= json_encode(url('check-url-availability')) ?>,
                    data: {
                        url: url,
                        domain_id: domain_id || '',
                        token: <?= json_encode(\Altum\Csrf::get('global_token')) ?>
                    },
                    success: (response) => {
                        if(response.status === 'success' && response.details && response.details.available) {
                            /* URL is available - redirect */
                            if(confirm(response.details.message + ' ' + <?= json_encode(l('index.subdirectory_redirect_confirm')) ?>)) {
                                window.location.href = response.details.redirect_url;
                            }
                        }
                    },
                    error: () => {
                        /* Silently fail - don't interrupt user flow */
                    }
                });
            }, 500);
        }
        <?php endif ?>

        if(event.key === 'Enter') {
            event.preventDefault();
            document.querySelector('#claim_button').click();
        }
    }));
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
```

---

#### 7. `product/app/controllers/CheckUrlAvailability.php` (NEW FILE)

**Create this new file** with the following content:

```php
<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
 *
 * This software is licensed exclusively by AltumCode and is sold only via https://altumcode.com/.
 * Unauthorized distribution, modification, or use of this software without a valid license is not permitted and may be subject to applicable legal actions.
 *
 * 🌍 View all other existing AltumCode projects via https://altumcode.com/
 * 📧 Get in touch for support or general queries via https://altumcode.com/contact
 * 📤 Download the latest version via https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 * 📘 Facebook: https://facebook.com/altumcode
 * 📸 Instagram: https://instagram.com/altumcode
 */

namespace Altum\Controllers;

use Altum\Response;

defined('ALTUMCODE') || die();

class CheckUrlAvailability extends Controller {

    public function index() {

        if(!empty($_POST) && isset($_POST['url'])) {
            
            /* Check CSRF */
            if(!\Altum\Csrf::check('global_token')) {
                Response::json(l('global.error_message.invalid_csrf_token'), 'error');
            }
            
            $url = get_slug($_POST['url'], '-', false);
            $domain_id = isset($_POST['domain_id']) && $_POST['domain_id'] ? (int) $_POST['domain_id'] : null;
            
            if(empty($url)) {
                Response::json(l('global.error_message.empty_field'), 'error');
            }
            
            /* Check if URL is available */
            $domain_id_where = $domain_id ? "AND `domain_id` = {$domain_id}" : "AND (`domain_id` IS NULL OR `domain_id` = 0)";
            $is_existing_link = database()->query("SELECT `link_id` FROM `links` WHERE `url` = '{$url}' {$domain_id_where}")->num_rows;
            
            /* Check if URL is blacklisted */
            $is_blacklisted = false;
            if(array_key_exists($url, \Altum\Router::$routes['']) || in_array($url, \Altum\Language::$active_languages) || file_exists(ROOT_PATH . $url)) {
                $is_blacklisted = true;
            }
            if(in_array(mb_strtolower($url), settings()->links->blacklisted_keywords ?? [])) {
                $is_blacklisted = true;
            }
            
            /* If URL is available and not blacklisted, and subdirectory redirect is enabled */
            $subdirectory_redirect_enabled = (isset(settings()->main->subdirectory_redirect_is_enabled) && settings()->main->subdirectory_redirect_is_enabled) ||
                                             (isset(settings()->links->subdirectory_redirect_is_enabled) && settings()->links->subdirectory_redirect_is_enabled);
            $subdirectory_redirect_base_url = !empty(settings()->main->subdirectory_redirect_base_url) ? settings()->main->subdirectory_redirect_base_url : 
                                               (!empty(settings()->links->subdirectory_redirect_base_url) ? settings()->links->subdirectory_redirect_base_url : '');
            
            if(!$is_existing_link && !$is_blacklisted && 
               $subdirectory_redirect_enabled &&
               !empty($subdirectory_redirect_base_url)) {
                
                /* Build redirect URL */
                $base_url = rtrim($subdirectory_redirect_base_url, '/');
                $redirect_url = $base_url . '/' . $url;
                
                Response::json('', 'success', [
                    'available' => true,
                    'redirect_url' => $redirect_url,
                    'message' => sprintf(l('index.subdirectory_available_message'), $url)
                ]);
            }
            
            /* URL is not available or feature is disabled */
            Response::json('', 'success', [
                'available' => false
            ]);
        }
        
        Response::json(l('global.error_message.basic'), 'error');
    }

}
```

---

#### 8. `product/app/languages/admin/english#en.php`

**Location**: After `admin_settings.main.not_found_url_help` (around line 687)

**Changes**: Add the following language strings:

```php
'admin_settings.main.subdirectory_redirect_is_enabled' => 'Enable subdirectory redirect',
'admin_settings.main.subdirectory_redirect_is_enabled_help' => 'When enabled, 404 errors on custom domains will redirect to the base URL with the same subdirectory. Users will be informed that the link is not available before redirecting.',
'admin_settings.main.subdirectory_redirect_base_url' => 'Subdirectory redirect base URL',
'admin_settings.main.subdirectory_redirect_base_url_help' => 'The base URL where users will be redirected (e.g., https://linkdooni.com). The subdirectory from the original request will be appended to this URL.',
```

**Location**: After `admin_settings.links.claim_url_type_help` (around line 1417)

**Changes**: Add the following language strings:

```php
'admin_settings.links.subdirectory_redirect_is_enabled' => 'Enable subdirectory redirect',
'admin_settings.links.subdirectory_redirect_is_enabled_help' => 'When enabled, 404 errors on custom domains will redirect to the base URL with the same subdirectory. Users will be informed that the link is not available before redirecting.',
'admin_settings.links.subdirectory_redirect_base_url' => 'Subdirectory redirect base URL',
'admin_settings.links.subdirectory_redirect_base_url_help' => 'The base URL where users will be redirected (e.g., https://linkdooni.com). The subdirectory from the original request will be appended to this URL.',
```

---

#### 9. `product/app/languages/english#en.php`

**Location**: After `not_found.button` (around line 1444)

**Changes**: Add the following language strings:

```php
'not_found.subdirectory_redirect_message' => 'The link "%s" is not available on this domain. Redirecting you...',
'index.subdirectory_available_message' => 'The username "%s" is available!',
'index.subdirectory_redirect_confirm' => 'Would you like to be redirected?',
```

---

### Subdirectory Redirect Feature Summary

**Files Modified** (9 files):
1. `product/themes/altum/views/admin/settings/partials/main.php`
2. `product/themes/altum/views/admin/settings/partials/links.php`
3. `product/app/controllers/admin/AdminSettings.php` (2 methods: `main()` and `links()`)
4. `product/app/core/Router.php` (2 locations: route definition and 404 handling)
5. `product/app/controllers/NotFound.php`
6. `product/themes/altum/views/index/index.php`
7. `product/app/languages/admin/english#en.php`
8. `product/app/languages/english#en.php`
9. `product/app/controllers/CheckUrlAvailability.php` (NEW FILE)

**Key Features**:
- Subdirectory redirect can be enabled from either Main or Links settings
- System checks both locations and uses whichever is enabled
- Priority: `settings()->main` takes precedence over `settings()->links` for base URL
- 404 redirects on custom domains show a message before redirecting
- Home page search checks URL availability and prompts for redirect if available

**Testing Checklist**:
1. ✅ Enable feature from Admin Settings → Main → Other Settings
2. ✅ Enable feature from Admin Settings → Links
3. ✅ Set base URL (e.g., `https://linkdooni.com`)
4. ✅ Visit non-existent link on custom domain (e.g., `boy.bio/x2`)
5. ✅ Verify redirect message appears
6. ✅ Verify redirect to `linkdooni.com/x2` after 2 seconds
7. ✅ Search for available subdirectory on home page
8. ✅ Verify prompt appears when subdirectory is available
9. ✅ Verify redirect works when user confirms

---

# PART 2: BUG FIXES & ERROR RESOLUTIONS

**⚠️ IMPORTANT**: Before applying these fixes, check if the new version (61) already includes them. These are fixes for errors that occurred in version 60, and may have already been resolved by the developers.

---

## Bug Fix 1: Null Preferences Warning in Dashboard and Links Controllers

**Issue**: `Warning: Attempt to read property "links_default_order_by" on null` when `$this->user->preferences` is null.

**Check First**: Test if this error still occurs in the new version. If not, skip this fix.

**Files Modified** (5 files):
1. `product/app/controllers/Dashboard.php`
2. `product/app/controllers/Links.php`
3. `product/app/controllers/LinksStatistics.php`
4. `product/app/controllers/admin/AdminLinks.php`
5. `product/app/controllers/api/ApiLinks.php`

**Fix**: Add null checks before accessing `preferences` properties.

**Changes**: In each file, replace the filtering system initialization code:

**Before:**
```php
$filters->set_default_order_by($this->user->preferences->links_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
$filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
```

**After:**
```php
$preferences = isset($this->user->preferences) ? $this->user->preferences : null;
$filters->set_default_order_by(($preferences && isset($preferences->links_default_order_by)) ? $preferences->links_default_order_by : 'link_id', ($preferences && isset($preferences->default_order_type)) ? $preferences->default_order_type : settings()->main->default_order_type);
$filters->set_default_results_per_page(($preferences && isset($preferences->default_results_per_page)) ? $preferences->default_results_per_page : settings()->main->default_results_per_page);
```

**Note**: For `ApiLinks.php`, use `$this->api_user->preferences` instead of `$this->user->preferences`.

---

## Bug Fix 2: Service Worker Registration Error (404/Redirect Issue)

**Issue**: `SecurityError: Failed to register a ServiceWorker... A bad HTTP response code (404) was received when fetching the script.`

**Check First**: Test if `sw.js` is accessible and service workers register correctly in the new version. If yes, skip this fix.

**Root Cause**: When the browser requests `sw.js`, it's being caught by the `.htaccess` rewrite rule and going through the router, but the route wasn't being matched correctly, resulting in a 404 error.

**Files Modified** (2 files):
1. `product/app/core/Router.php`
2. `product/app/controllers/ServiceWorker.php` (NEW FILE)

**Fix**: 
1. Add early check in router to handle `sw.js` before normal route matching
2. Add route handler for `sw.js` to serve it properly
3. Create ServiceWorker controller to serve the service worker file

**Changes**:

#### 1. `product/app/core/Router.php`

**Location 1**: In the `parse_controller()` method, at the very beginning (around line 1689)

**Changes**: Add early check for service worker before any other route logic:

```php
public static function parse_controller() {

    self::$original_request = input_clean(implode('/', self::$params));
    self::$original_request_query = http_build_query(array_diff_key($_GET, array_flip(['altum'])));

    /* Check for service worker early */
    if(!empty(self::$params[0]) && self::$params[0] === 'sw.js' && array_key_exists('sw.js', self::$routes['']) && file_exists(APP_PATH . 'controllers/ServiceWorker.php')) {
        self::$controller_key = 'sw.js';
        self::$controller = 'ServiceWorker';
        self::$path = '';
        unset(self::$params[0]);
        self::$params = array_values(self::$params);
        return;
    }

    /* Rest of the method continues... */
```

**Location 2**: In the routes array, after `check-url-availability` route (around line 851)

**Changes**: Add the following route:

```php
'sw.js' => [
    'controller' => 'ServiceWorker',
    'settings' => [
        'no_authentication_check' => true,
        'has_view' => false,
        'no_browser_language_detection' => true,
        'allow_indexing' => false,
    ]
],
```

#### 2. `product/app/controllers/ServiceWorker.php` (NEW FILE)

**Create this new file** with the following content:

```php
<?php
namespace Altum\Controllers;

defined('ALTUMCODE') || die();

class ServiceWorker extends Controller {

    public function index() {

        /* Set proper headers for service worker */
        header('Content-Type: application/javascript');
        header('Service-Worker-Allowed: /');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        /* Check if PWA is enabled and service worker file exists */
        if(\Altum\Plugin::is_active('pwa') && settings()->pwa->is_enabled) {
            $service_worker_file = UPLOADS_PATH . \Altum\Uploads::get_path('pwa') . 'sw.js';
            
            if(file_exists($service_worker_file)) {
                readfile($service_worker_file);
                die();
            }
        }

        /* Return empty service worker if no file exists (prevents redirect errors) */
        echo "// Service Worker\n";
        echo "// No service worker file configured\n";
        echo "self.addEventListener('install', function(event) {\n";
        echo "    self.skipWaiting();\n";
        echo "});\n";
        echo "self.addEventListener('activate', function(event) {\n";
        echo "    event.waitUntil(self.clients.claim());\n";
        echo "});\n";
        
        die();
    }

}
```

**Note**: This controller serves the PWA service worker file if it exists, or returns a minimal service worker to prevent redirect errors.

---

## Bug Fix 3: Undefined Property Warnings in Login (Social Login Providers)

**Issue**: `Warning: Undefined property: stdClass::$is_enabled` for LinkedIn and Microsoft social login providers in the login view and controller.

**Check First**: Test if this error still occurs in the new version. If not, skip this fix.

**Root Cause**: The code was accessing `settings()->linkedin->is_enabled` and `settings()->microsoft->is_enabled` (and other social login providers) without checking if these properties exist first.

**Files Modified** (2 files):
1. `product/themes/altum/views/login/index.php`
2. `product/app/controllers/Login.php`

**Fix**: Add `isset()` checks before accessing `is_enabled` property for all social login providers.

**Changes**: 

#### 1. `product/themes/altum/views/login/index.php`

**Location**: Lines 74, 78, 86, 94, 102, 110, and 118

**Before:**
```php
<?php if(settings()->facebook->is_enabled || settings()->google->is_enabled || ...): ?>
    ...
    <?php if(settings()->linkedin->is_enabled): ?>
    ...
    <?php if(settings()->microsoft->is_enabled): ?>
```

**After:**
```php
<?php if((isset(settings()->facebook->is_enabled) && settings()->facebook->is_enabled) || (isset(settings()->google->is_enabled) && settings()->google->is_enabled) || ...): ?>
    ...
    <?php if(isset(settings()->linkedin->is_enabled) && settings()->linkedin->is_enabled): ?>
    ...
    <?php if(isset(settings()->microsoft->is_enabled) && settings()->microsoft->is_enabled): ?>
```

#### 2. `product/app/controllers/Login.php`

**Location**: Lines 93, 138, 183, 228, 273, and 318

**Before:**
```php
if(settings()->facebook->is_enabled && in_array($method, ['facebook-initiate', 'facebook'])) {
    ...
}
if(settings()->linkedin->is_enabled && in_array($method, ['linkedin-initiate', 'linkedin'])) {
    ...
}
if(settings()->microsoft->is_enabled && in_array($method, ['microsoft-initiate', 'microsoft'])) {
    ...
}
```

**After:**
```php
if(isset(settings()->facebook->is_enabled) && settings()->facebook->is_enabled && in_array($method, ['facebook-initiate', 'facebook'])) {
    ...
}
if(isset(settings()->linkedin->is_enabled) && settings()->linkedin->is_enabled && in_array($method, ['linkedin-initiate', 'linkedin'])) {
    ...
}
if(isset(settings()->microsoft->is_enabled) && settings()->microsoft->is_enabled && in_array($method, ['microsoft-initiate', 'microsoft'])) {
    ...
}
```

**Note**: Apply this pattern to all social login provider checks (Facebook, Google, Twitter, Discord, LinkedIn, Microsoft) in both files for consistency and to prevent future issues.

---

## Summary

### Feature Additions (Always Apply)
- ✅ Subdirectory Redirect Feature (9 files modified, 1 new file)

### Bug Fixes (Check First, Then Apply if Needed)
- ⚠️ Null Preferences Warning (5 files) - **Check if error still exists**
- ⚠️ Service Worker 404 Error (2 files) - **Check if error still exists**
- ⚠️ Undefined Property Warnings in Login (2 files) - **Check if error still exists**

---

**Last Updated**: Version 60 → Version 61 Migration
**Date**: 2025
