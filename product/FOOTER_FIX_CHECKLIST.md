# Footer Pages Fix Checklist

## ✅ Step 1: Upload Fixed Page.php File
**CRITICAL:** You must upload the fixed `Page.php` file to your server!

**File to upload:**
- `product/app/models/Page.php`

**Upload to:**
- `/var/www/www-root/data/www/boybio.net/product/app/models/Page.php`

**Why:** The original file has a query that fails when the `order` column doesn't exist. The fixed version handles this gracefully.

---

## ✅ Step 2: Run SQL Script
Run `fix_footer_complete_final.sql` in phpMyAdmin:

1. Open phpMyAdmin
2. Select your database
3. Go to SQL tab
4. Copy entire contents of `fix_footer_complete_final.sql`
5. Paste and click "Go"

This will:
- Enable pages feature
- Create footer pages
- Ensure they're published

---

## ✅ Step 3: Clear Cache
Visit: `https://boybio.net/clear_cache.php`

Or manually delete cache files via SSH:
```bash
rm -rf /var/www/www-root/data/www/boybio.net/product/uploads/cache/*
```

---

## ✅ Step 4: Test
Visit: `https://boybio.net/test_pages_direct.php`

This will show you:
- If pages feature is enabled
- If pages exist in database
- If queries work

---

## Common Issues:

1. **Page.php not uploaded** → Footer pages won't load (query fails)
2. **SQL not run** → Pages don't exist or feature is disabled
3. **Cache not cleared** → Old empty cache is still being used
4. **Pages feature disabled** → SQL script enables it, but cache must be cleared

---

## Quick Verification:

After completing all steps, your footer should show:
- Blog | Affiliate | Contact | **built with love** | **from saman**

If you only see Blog | Affiliate | Contact, then:
- Check if Page.php was uploaded
- Check if SQL was run
- Check if cache was cleared
- Run test_pages_direct.php to see what's wrong











