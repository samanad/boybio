# Custom Changes Extracted from product1 (v60 Customized)

## Summary

This document lists all custom changes found in `product1` folder (your customized v60) that need to be preserved when upgrading to v63.

---

## 1. CUSTOM CONTROLLERS (New Files)

### 1.1 CheckUrlAvailability.php
**Location**: `app/controllers/CheckUrlAvailability.php`
**Purpose**: Checks if a URL is available for claiming during registration
**Features**:
- Validates URL availability
- Checks for blacklisted keywords
- Supports custom domains
- Returns JSON response with status (available/used/banned)

### 1.2 Manifest.php
**Location**: `app/controllers/Manifest.php`
**Purpose**: Serves PWA manifest.json file from local storage
**Features**:
- Serves manifest file for PWA plugin
- Always uses local file (never cloud)
- Proper headers for manifest

### 1.3 ServiceWorker.php
**Location**: `app/controllers/ServiceWorker.php`
**Purpose**: Serves PWA service worker file
**Features**:
- Serves service worker for PWA plugin
- Falls back to empty service worker if file doesn't exist
- Proper headers for service worker

---

## 2. CUSTOM CORE FILES

### 2.1 CustomHooks.php
**Location**: `app/core/CustomHooks.php`
**Purpose**: Custom hook system for user registration, deletion, and language prefix generation
**Features**:
- `user_initiate_registration()` - Handles claim URL during registration
- `user_finished_registration()` - Saves claim URL preference
- `user_delete()` - Enhanced user deletion with file cleanup
- `user_payment_finished()` - Resets AIX plugin monthly limits
- `generate_language_prefixes_to_skip()` - Dynamic language prefix generation

---

## 3. CUSTOM HELPERS

### 3.1 offload_helpers.php
**Location**: `app/helpers/offload_helpers.php`
**Purpose**: Helper functions for offload storage (S3) integration
**Features**:
- `get_file_from_offload_or_local()` - Gets file from S3 or local
- Downloads from S3 to temp cache if needed
- Cache management (24-hour cache)

---

## 4. DATABASE CHANGES

### 4.1 Links Table - is_explore_things Column
**SQL**: 
```sql
ALTER TABLE `links` 
ADD COLUMN `is_explore_things` tinyint DEFAULT 0 NULL 
AFTER `directory_is_enabled`;
```
**Purpose**: Flag for explore/directory feature
**Source**: Found in `product1/update/sql/6100.sql`

### 4.2 Footer Pages
**SQL File**: `add_footer_pages.sql`
**Purpose**: Adds default footer links (AltumCode and 66biolinks) to pages table
**Note**: This is a one-time setup script, not a migration

---

## 5. ROUTER CHANGES

### 5.1 New Routes in Router.php
**Location**: `app/core/Router.php`

**Routes to add:**
```php
'check-url-availability' => [
    'controller' => 'CheckUrlAvailability',
    'settings' => [
        'no_authentication_check' => true,
        'has_view' => false,
    ]
],

'sw.js' => [
    'controller' => 'ServiceWorker',
    'settings' => [
        'no_authentication_check' => true,
        'has_view' => false,
        'no_browser_language_detection' => true,
    ]
],

'manifest.json' => [
    'controller' => 'Manifest',
    'settings' => [
        'no_authentication_check' => true,
        'has_view' => false,
        'no_browser_language_detection' => true,
    ]
],
```

**Special Router Logic:**
- Check for `sw.js` in params and route to ServiceWorker
- Check for `manifest.json` in params and route to Manifest

---

## 6. CODE MODIFICATIONS

### 6.1 Files That May Have Been Modified
Based on grep results, these files may have custom modifications:
- `app/core/Controller.php` - May have custom hooks integration
- `app/core/Router.php` - Has custom routes
- `app/core/App.php` - May have CustomHooks integration
- `app/core/Authentication.php` - May have claim URL logic
- `app/controllers/Register.php` - May have claim URL handling
- `app/controllers/l/Link.php` - May have footer/pages modifications
- `app/controllers/Directory.php` - May have explore_things modifications
- `app/languages/english#en.php` - May have custom translations
- `app/languages/admin/english#en.php` - May have admin translations

**Action Required**: Compare these files between product1 and v60 to extract exact changes.

---

## 7. IMPLEMENTATION PLAN

### Step 1: Copy Custom Files
1. ✅ Copy `CheckUrlAvailability.php` to product
2. ✅ Copy `Manifest.php` to product
3. ✅ Copy `ServiceWorker.php` to product
4. ✅ Copy `CustomHooks.php` to product
5. ✅ Copy `offload_helpers.php` to product

### Step 2: Update Database Migration
1. ✅ Add `is_explore_things` column to migration SQL
2. ✅ Ensure migration preserves existing data

### Step 3: Update Router
1. ✅ Add new routes to Router.php
2. ✅ Add special routing logic for sw.js and manifest.json

### Step 4: Compare Modified Files
1. ⚠️ Compare Controller.php, App.php, Authentication.php, Register.php
2. ⚠️ Extract and merge custom modifications
3. ⚠️ Compare language files

### Step 5: Test
1. Test claim URL functionality
2. Test PWA manifest and service worker
3. Test explore_things feature
4. Test custom hooks

---

## 8. FILES TO COPY

### New Files (Copy as-is):
- `app/controllers/CheckUrlAvailability.php`
- `app/controllers/Manifest.php`
- `app/controllers/ServiceWorker.php`
- `app/core/CustomHooks.php`
- `app/helpers/offload_helpers.php`

### Files to Compare & Merge:
- `app/core/Controller.php`
- `app/core/App.php`
- `app/core/Router.php`
- `app/core/Authentication.php`
- `app/controllers/Register.php`
- `app/controllers/l/Link.php`
- `app/controllers/Directory.php`
- `app/languages/english#en.php`
- `app/languages/admin/english#en.php`

---

## 9. DATABASE MIGRATION UPDATES

### Update 6100.sql to include:
```sql
-- X -- ALTER TABLE `links` ADD COLUMN `is_explore_things` tinyint DEFAULT 0 NULL AFTER `directory_is_enabled` IF NOT EXISTS;
```

The `-- X --` prefix makes it optional (won't fail if column exists).

---

**Status**: Analysis complete. Ready to implement custom changes into v63.


