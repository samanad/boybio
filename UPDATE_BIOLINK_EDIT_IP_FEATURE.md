# Update Guide: Biolink Edit IP Feature

## Overview

This feature allows a specific IP address to see an "Edit" link in the footer of biolink pages. The IP address can be configured from the Admin Settings → Security section. This is useful for administrators who want quick access to edit biolink pages when viewing them from a specific IP address.

**Important**: The site is behind Cloudflare proxy, so the IP detection has been updated to prioritize Cloudflare's `CF-Connecting-IP` header to correctly identify the real client IP address.

---

## Feature Details

### What It Does

1. **Admin Configuration**: Admins can set an allowed IP address in Admin Settings → Security
2. **IP Detection**: The system detects the visitor's IP address, prioritizing Cloudflare's header when behind a proxy
3. **Edit Link Display**: When a visitor with the allowed IP views a biolink page, a fixed footer with an "Edit" link appears
4. **Direct Edit Access**: Clicking the "Edit" link takes the admin directly to the biolink edit page for that specific biolink

### When the Edit Link Appears

- ✅ The page is a biolink type (`type == 'biolink'`)
- ✅ The page is not in preview mode
- ✅ The visitor's IP address matches the configured allowed IP
- ✅ An IP address has been configured in Admin Settings → Security

---

## Files Modified

### 1. `product/themes/altum/views/admin/settings/partials/security.php`

**Location**: After the CSRF strict validation switch

**Changes**: Added a new form field for the allowed IP address:

```php
<div class="form-group">
    <label for="biolink_edit_allowed_ip"><i class="fas fa-fw fa-sm fa-edit text-muted mr-1"></i> <?= l('admin_settings.security.biolink_edit_allowed_ip') ?></label>
    <input type="text" id="biolink_edit_allowed_ip" name="biolink_edit_allowed_ip" class="form-control" value="<?= isset(settings()->security) && isset(settings()->security->biolink_edit_allowed_ip) ? settings()->security->biolink_edit_allowed_ip : '' ?>" placeholder="165.22.58.120" />
    <small class="form-text text-muted"><?= l('admin_settings.security.biolink_edit_allowed_ip_help') ?></small>
</div>
```

---

### 2. `product/app/controllers/admin/AdminSettings.php`

**Location**: In the `security()` method

**Changes**: Added IP validation and saving logic:

```php
$_POST['biolink_edit_allowed_ip'] = input_clean($_POST['biolink_edit_allowed_ip'] ?? '');

/* Validate IP if provided */
if(!empty($_POST['biolink_edit_allowed_ip']) && !filter_var($_POST['biolink_edit_allowed_ip'], FILTER_VALIDATE_IP)) {
    Alerts::add_field_error('biolink_edit_allowed_ip', l('admin_settings.security.biolink_edit_allowed_ip_error'));
}

if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
    $value = json_encode([
        'csrf_strict_validation_is_enabled' => isset($_POST['csrf_strict_validation_is_enabled']),
        'biolink_edit_allowed_ip' => $_POST['biolink_edit_allowed_ip'],
    ]);

    $this->update_settings('security', $value);
}
```

---

### 3. `product/themes/altum/views/l/biolink_wrapper.php`

**Location**: Before the closing `</html>` tag

**Changes**: Added footer with edit link that appears conditionally:

```php
<?php
/* Show edit link in footer if IP matches and link is biolink type */
if($this->link->type == 'biolink' && !$this->is_preview) {
    $allowed_ip = isset(settings()->security) && isset(settings()->security->biolink_edit_allowed_ip) ? settings()->security->biolink_edit_allowed_ip : '';
    $current_ip = get_ip();
    
    if(!empty($allowed_ip) && $current_ip && $current_ip === $allowed_ip) {
?>
    <footer class="biolink-edit-footer" style="position: fixed; bottom: 0; left: 0; right: 0; background: rgba(0, 0, 0, 0.8); padding: 10px; text-align: center; z-index: 9999;">
        <a href="<?= url('link/' . $this->link->link_id) ?>" style="color: #fff; text-decoration: none; font-weight: bold;">
            <i class="fas fa-fw fa-edit"></i> Edit
        </a>
    </footer>
<?php
    }
}
?>
```

---

### 4. `product/app/helpers/others.php`

**Location**: In the `get_ip()` function

**Changes**: Added Cloudflare IP header support (prioritized first):

```php
/* list of server keys to check for IP */
/* Cloudflare CF-Connecting-IP is checked first as it's the most reliable when behind Cloudflare proxy */
$ip_sources = [
    'HTTP_CF_CONNECTING_IP',
    'HTTP_CLIENT_IP',
    'HTTP_X_FORWARDED_FOR',
    'REMOTE_ADDR'
];
```

**Why This Change**: When the site is behind Cloudflare proxy, the real client IP is passed in the `CF-Connecting-IP` header. By checking this header first, we ensure accurate IP detection for the edit link feature.

---

### 5. `product/app/languages/admin/english#en.php`

**Location**: After `admin_settings.security.csrf_strict_validation_is_enabled_help`

**Changes**: Added language strings:

```php
'admin_settings.security.biolink_edit_allowed_ip' => 'Biolink Edit Allowed IP',
'admin_settings.security.biolink_edit_allowed_ip_help' => 'The IP address that will see an "Edit" link in the footer of biolink pages. Leave empty to disable this feature.',
'admin_settings.security.biolink_edit_allowed_ip_error' => 'Please provide a valid IP address.',
```

---

## Configuration

### How to Set Up

1. **Navigate to Admin Panel**: Go to Admin → Settings → Security
2. **Enter IP Address**: In the "Biolink Edit Allowed IP" field, enter your IP address (e.g., `165.22.58.120`)
3. **Save Settings**: Click the "Update" button
4. **Verify**: Visit any biolink page from the configured IP address - you should see an "Edit" link in the footer

### Default IP

The default IP address used in the example is `165.22.58.120`, but this can be changed to any valid IP address through the admin settings.

---

## Technical Details

### IP Detection Priority

When detecting the visitor's IP address, the system checks in this order:

1. **`HTTP_CF_CONNECTING_IP`** - Cloudflare's header (most reliable when behind Cloudflare)
2. **`HTTP_CLIENT_IP`** - Standard client IP header
3. **`HTTP_X_FORWARDED_FOR`** - Forwarded for header (first IP if multiple)
4. **`REMOTE_ADDR`** - Server's remote address (fallback)

### Security Considerations

- The IP address is validated using PHP's `filter_var()` with `FILTER_VALIDATE_IP`
- Only exact IP matches are allowed (no wildcards or ranges)
- The edit link only appears on biolink pages, not on other link types
- The edit link does not appear in preview mode
- The feature can be disabled by leaving the IP field empty

---

## Testing Checklist

- ✅ Configure IP address in Admin Settings → Security
- ✅ Visit a biolink page from the configured IP address
- ✅ Verify "Edit" link appears in the footer
- ✅ Click the "Edit" link and verify it goes to the correct edit page
- ✅ Visit a biolink page from a different IP address
- ✅ Verify "Edit" link does NOT appear
- ✅ Visit a non-biolink page from the configured IP
- ✅ Verify "Edit" link does NOT appear
- ✅ Test with Cloudflare proxy enabled
- ✅ Verify IP detection works correctly behind Cloudflare

---

## Summary

**Files Modified** (5 files):
1. `product/themes/altum/views/admin/settings/partials/security.php`
2. `product/app/controllers/admin/AdminSettings.php`
3. `product/themes/altum/views/l/biolink_wrapper.php`
4. `product/app/helpers/others.php`
5. `product/app/languages/admin/english#en.php`

**Key Features**:
- IP-based edit link access for biolink pages
- Configurable from Admin Settings → Security
- Cloudflare proxy support for accurate IP detection
- Secure IP validation
- Only appears on biolink pages (not preview mode)

**Last Updated**: 2025
**Feature Version**: 1.0



