# Restore from MD Files – Completed

**Date**: 2025-02-04

## Summary

The ongoing update described in the project’s MD files has been restored and verified in `product/`.

---

## What Was Already in Place (v60 → v63)

From **FINAL_UPGRADE_SUMMARY.md** and **CUSTOM_CHANGES_IMPLEMENTATION_COMPLETE.md**, the following were already present in `product/`:

- **Custom controllers**: `CheckUrlAvailability.php`, `Manifest.php`, `ServiceWorker.php`
- **Custom core**: `CustomHooks.php`
- **Custom helper**: `offload_helpers.php`
- **Router**: Routes for `check-url-availability`, `sw.js`, `manifest` and special routing logic
- **CustomHooks usage**: `Register.php`, `User.php`, `Payments.php`, `Language.php`
- **Database**: `product/update/sql/6100.sql` includes the optional `is_explore_things` column (with `-- X --`)
- **Version**: `product/update/info.php` set to 63.0.0

No changes were required for these.

---

## What Was Restored from the MD Files

### 1. IP-based Biolink Edit Footer (from **UPDATE_BIOLINK_EDIT_IP_FEATURE.md**)

- **File**: `product/themes/altum/views/l/biolink_wrapper.php`  
  - **Change**: Added the footer block that shows an “Edit” link when the visitor’s IP matches the configured “Biolink Edit Allowed IP” (for biolink pages, non-preview).  
  - Uses existing `get_ip()` (already using `HTTP_CF_CONNECTING_IP` in `product/app/helpers/others.php`).

### 2. Admin Language Strings (from **UPDATE_BIOLINK_EDIT_IP_FEATURE.md**)

- **File**: `product/app/languages/admin/english#en.php`  
  - **Change**: Set security strings for the biolink edit IP feature:
    - `admin_settings.security.biolink_edit_allowed_ip` → `"Biolink Edit Allowed IP"`
    - `admin_settings.security.biolink_edit_allowed_ip_help` → text about the “Edit” link in the footer
    - `admin_settings.security.biolink_edit_allowed_ip_error` → unchanged (valid IP message)

---

## Already Present (Biolink / Branding)

From **UPGRADE_GUIDE_BIOLINK_EDIT_FEATURES.md**, the following were already in `product/` and were not modified:

- “Enable edit link in branding” in Admin Settings → Links
- Branding link replacement in `product/themes/altum/views/l/partials/biolink.php`
- Security partial with `biolink_edit_allowed_ip` field
- `get_ip()` with Cloudflare `CF-Connecting_IP` support in `others.php`
- AdminSettings handling for security and links

---

## Optional Next Steps (from the MDs)

- **Manual comparison** (if you need to pull more customizations from `product1`):
  - `app/core/Controller.php`, `App.php`, `Authentication.php`
  - `app/controllers/l/Link.php`, `app/controllers/Directory.php`
  - `app/languages/english#en.php`, `app/languages/admin/english#en.php`
- **Database**: Run migrations via `/update/` (or run `6100.sql`, `6200.sql`, `6300.sql` manually) after backing up the database.
- **Testing**: Check payment processors, credit notes, PWA (manifest/service worker), CheckUrlAvailability, and the biolink edit footer for the allowed IP.

---

## Status

- v60 → v63 upgrade and custom changes: **verified in** `product/`
- IP-based biolink edit footer and language strings: **restored from MD**
- Branding edit link and Cloudflare IP support: **already present**

Restore from the MD files is complete.
