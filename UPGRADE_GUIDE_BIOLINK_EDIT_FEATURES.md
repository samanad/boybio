# Upgrade Guide: Biolink Edit Features

This guide covers two related features for biolink page editing:

1. **IP-Based Edit Link Feature** (Future Use) - Reserved for future implementation
2. **Branding Edit Link Feature** (Current Implementation) - Active feature that replaces links in branding HTML

---

## Files to Upload

Upload the following **8 files** to your server:

### Core Files (7 files):
1. `product/app/controllers/admin/AdminSettings.php`
2. `product/app/helpers/others.php`
3. `product/app/languages/admin/english#en.php`
4. `product/themes/altum/views/admin/settings/partials/links.php`
5. `product/themes/altum/views/admin/settings/partials/security.php`
6. `product/themes/altum/views/l/biolink_wrapper.php`
7. `product/themes/altum/views/l/partials/biolink.php`

### Documentation (1 file - optional):
8. `UPGRADE_GUIDE_BIOLINK_EDIT_FEATURES.md` (this file)

---

## Feature 1: IP-Based Edit Link (Reserved for Future Use)

### Overview
This feature allows a specific IP address to see an "Edit" link in the footer of biolink pages. **Currently not active** - kept for future use. The IP address can be configured from Admin Settings → Security section.

**Important**: The site is behind Cloudflare proxy, so the IP detection has been updated to prioritize Cloudflare's `CF-Connecting-IP` header to correctly identify the real client IP address.

### Files Modified for Feature 1:
- `product/themes/altum/views/admin/settings/partials/security.php` - Added IP input field
- `product/app/controllers/admin/AdminSettings.php` - Added `security()` method updates
- `product/app/helpers/others.php` - Updated `get_ip()` function for Cloudflare support
- `product/app/languages/admin/english#en.php` - Added security language strings

### Status: ⚠️ Reserved for Future Use
The IP-based edit link code has been removed from `biolink_wrapper.php` but the settings infrastructure remains in place for future implementation.

---

## Feature 2: Branding Edit Link (Current Implementation) ⭐

### Overview
This feature allows all links in the biolink page branding HTML to point to the biolink edit page. When enabled, any `<a>` tag in the branding HTML will have its `href` attribute replaced with the edit page URL. Regular users will see the login page when clicking, while admins will see the edit page directly.

### How It Works
1. Admin configures branding HTML in **Admin Settings → Links → Biolink page branding**
2. Admin enables "Enable edit link in branding" toggle
3. All links in the branding HTML automatically point to the biolink edit page
4. HTML structure is preserved - only `href` attributes are changed
5. Works for both default admin branding and custom user branding

### Configuration

#### Step 1: Enable the Feature
1. Navigate to **Admin → Settings → Links**
2. Scroll to the **Biolink page branding** section
3. Check the **"Enable edit link in branding"** checkbox
4. Click **Update** to save

#### Step 2: Set Up Branding HTML
In the **"Biolink page branding"** textarea, add your branding HTML with links. For example:

```html
<a href="https://example.com">Edit this page</a>
```

When the feature is enabled, this will automatically become:

```html
<a href="/link/123">Edit this page</a>
```

(Where `123` is the actual biolink link_id)

### Files Modified for Feature 2:
- `product/themes/altum/views/admin/settings/partials/links.php` - Added setting toggle
- `product/app/controllers/admin/AdminSettings.php` - Added `links()` method updates
- `product/themes/altum/views/l/partials/biolink.php` - Updated branding display logic
- `product/themes/altum/views/l/biolink_wrapper.php` - Removed IP-based footer (cleanup)
- `product/app/languages/admin/english#en.php` - Added links language strings

### Technical Details

#### Link Replacement Logic
The system uses regex to find and replace all `href` attributes in the branding HTML:

```php
$branding_html = preg_replace('/href=["\']([^"\']*)["\']/i', 'href="' . htmlspecialchars($edit_url, ENT_QUOTES, 'UTF-8') . '"', $branding_html);
```

This ensures:
- All links are replaced (not just the first one)
- Both single and double quotes are handled
- HTML is properly escaped for security
- Original HTML structure is preserved

#### User Experience
- **Regular Users**: Clicking the link redirects to login page (authentication required)
- **Admin Users**: Clicking the link goes directly to biolink edit page
- **All Users**: See the same branding HTML with edit links

### Testing Checklist

- ✅ Enable "Enable edit link in branding" in Admin Settings → Links
- ✅ Add branding HTML with links (e.g., `<a href="#">Edit</a>`)
- ✅ Visit a biolink page as a regular user
- ✅ Verify branding displays correctly
- ✅ Click the link and verify it redirects to login page
- ✅ Login as admin and visit the same biolink page
- ✅ Click the link and verify it goes to edit page
- ✅ Test with multiple links in branding HTML
- ✅ Verify all links are replaced correctly

---

## Cloudflare IP Detection (Both Features)

### Updated `get_ip()` Function
The `get_ip()` function in `product/app/helpers/others.php` has been updated to prioritize Cloudflare's `CF-Connecting-IP` header:

```php
$ip_sources = [
    'HTTP_CF_CONNECTING_IP',  // Cloudflare header (checked first)
    'HTTP_CLIENT_IP',
    'HTTP_X_FORWARDED_FOR',
    'REMOTE_ADDR'
];
```

**Why This Matters**: When your site is behind Cloudflare proxy, the real client IP is passed in the `CF-Connecting-IP` header. This ensures accurate IP detection for any IP-based features.

---

## File Change Summary

### Complete File List (8 files):

1. **`product/app/controllers/admin/AdminSettings.php`**
   - Updated `security()` method (Feature 1)
   - Updated `links()` method (Feature 2)

2. **`product/app/helpers/others.php`**
   - Updated `get_ip()` function for Cloudflare support (Feature 1)

3. **`product/app/languages/admin/english#en.php`**
   - Added security language strings (Feature 1)
   - Added links language strings (Feature 2)

4. **`product/themes/altum/views/admin/settings/partials/links.php`**
   - Added "Enable edit link in branding" toggle (Feature 2)

5. **`product/themes/altum/views/admin/settings/partials/security.php`**
   - Added "Biolink Edit Allowed IP" field (Feature 1 - future use)

6. **`product/themes/altum/views/l/biolink_wrapper.php`**
   - Removed IP-based footer code (cleanup)

7. **`product/themes/altum/views/l/partials/biolink.php`**
   - Updated branding display to replace link hrefs (Feature 2)

8. **`UPGRADE_GUIDE_BIOLINK_EDIT_FEATURES.md`** (this file)
   - Complete upgrade documentation

---

## Installation Steps

1. **Backup Your Files**: Always backup before updating
   ```bash
   # Backup the files you'll be replacing
   cp product/app/controllers/admin/AdminSettings.php product/app/controllers/admin/AdminSettings.php.backup
   # ... repeat for other files
   ```

2. **Upload Files**: Upload all 7 core files to their respective locations

3. **Clear Cache**: Clear your application cache
   - Visit: `https://yourdomain.com/clear_cache.php`
   - Or delete: `uploads/cache/*` files

4. **Configure Settings**:
   - Go to **Admin → Settings → Links**
   - Enable **"Enable edit link in branding"**
   - Add your branding HTML with links
   - Click **Update**

5. **Test**: Visit a biolink page and verify the edit links work correctly

---

## Troubleshooting

### Links Not Replacing
- ✅ Verify "Enable edit link in branding" is checked
- ✅ Check that branding HTML contains `<a>` tags
- ✅ Clear cache after enabling the feature
- ✅ Verify you're viewing a biolink page (not other link types)

### Login Page Appears for Admins
- ✅ Verify you're logged in as an admin user
- ✅ Check that the user has permission to edit biolinks
- ✅ Verify the link_id in the URL is correct

### IP Detection Not Working (Future Feature)
- ✅ Verify Cloudflare is properly configured
- ✅ Check that `HTTP_CF_CONNECTING_IP` header is being sent
- ✅ Test with `var_dump(get_ip())` to see detected IP

---

## Version Information

- **Feature 1 (IP-Based)**: Version 1.0 - Reserved for future use
- **Feature 2 (Branding Edit Link)**: Version 1.0 - Active
- **Last Updated**: 2025
- **Compatible With**: AltumCode 66biolinks v60.0.0+

---

## Support

If you encounter any issues:
1. Check the troubleshooting section above
2. Verify all files were uploaded correctly
3. Clear cache and test again
4. Check server error logs for PHP errors

---

**Note**: The IP-based edit link feature (Feature 1) is kept in the codebase for future use but is currently not active. The branding edit link feature (Feature 2) is the active implementation that should be used.



