# What to Do Next – Continue the Update (v60 → v63)

This is the checklist to **resume and finish** the update. Do the steps in order.

---

## Step 1: Backup your database

**Do this first.** If something goes wrong, you can restore.

- Use phpMyAdmin: export your database (full dump), or  
- Use command line: `mysqldump -u USER -p DATABASE_NAME > backup_before_update.sql`  
- Keep the backup file somewhere safe.

---

## Step 2: Run the update (database migrations)

You have two ways. Use **one** of them.

### Option A – Using the update page (easiest)

1. In the browser, go to: **`https://YOUR-SITE-URL/update/`**  
   (Replace YOUR-SITE-URL with your real domain, e.g. `boybio.net`.)
2. The page will:
   - See your current version (e.g. 60.0.0)
   - Run the migration files 6100, 6200, 6300 in order
   - Set the version to 63.0.0
3. Follow any message on the page (e.g. “Update complete” or errors).

### Option B – Using phpMyAdmin (if you prefer or if the page fails)

1. Open phpMyAdmin and select your database.
2. Go to the **SQL** tab.
3. Open these files on your computer (in this order):
   - `product/update/sql/6100.sql`
   - `product/update/sql/6200.sql`
   - `product/update/sql/6300.sql`
4. For each file: copy its contents, paste into the SQL box, run it.  
   Do **6100** first, then **6200**, then **6300**.

---

## Step 3: Check that the update ran

In phpMyAdmin (or any SQL client), run:

```sql
SELECT `value` FROM `settings` WHERE `key` = 'product_info';
```

You should see something like: `{"version":"63.0.0","code":"6300"}`.  
If you see that, the update ran correctly.

---

## Step 4: Test the site

Quick checks:

- Open the main site and a few pages.
- Log in (user and admin if you have both).
- Try one or two important features (e.g. create/edit a link, biolink, payment if you use it).
- If you use PWA, claim URL, or payment processors, test those too.

If anything breaks, you still have the database backup from Step 1.

---

## Step 5: (Optional) Later – compare custom files

Only if you had your own customizations and want to be sure nothing is missing:

- Compare these between `product1` (old backup) and `product`:  
  Controller.php, App.php, Authentication.php, Link.php (in `l/`), Directory.php, and language files.
- Merge any extra customizations you still need into `product`.

You can do this after the update is done and the site works.

---

## Summary

| Step | What to do |
|------|------------|
| 1    | Backup database |
| 2    | Run update: go to `https://YOUR-SITE/update/` **or** run 6100.sql, 6200.sql, 6300.sql in phpMyAdmin |
| 3    | Check version in DB (`product_info` = 63.0.0) |
| 4    | Test the site |
| 5    | (Optional) Compare custom files with product1 later |

**You don’t need to change any code.** The code in `product/` is already prepared for v63; you only need to run the database update (Step 2) and then test.
