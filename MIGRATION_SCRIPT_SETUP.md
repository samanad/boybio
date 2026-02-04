# Migration Script Setup - Server Requirements

## What Was Done

Before running the migration script, the following was set up on the server:

### 1. PHP mysqli Extension for CLI

The migration script requires the `mysqli` extension for PHP CLI. This was installed:

```bash
sudo apt-get update
sudo apt-get install php8.3-mysqli
```

**Verification:**
```bash
php -m | grep -i mysqli
# Should output: mysqli
```

**Location:**
- Extension file: `/usr/lib/php/20230831/mysqli.so`
- Config file: `/etc/php/8.3/cli/conf.d/20-mysqli.ini`

### 2. Script Location

The migration script is located at:
```
product/migrate-to-offload-minimal.php
```

Make sure it's executable:
```bash
chmod +x product/migrate-to-offload-minimal.php
```

## Prerequisites

Before running the migration script, ensure:

1. **Offload plugin is enabled** in Admin Panel → Settings → Plugins
2. **Offload settings are configured** in Admin Panel → Settings → Offload:
   - Provider (AWS S3, DigitalOcean Spaces, etc.)
   - Access Key
   - Secret Access Key
   - Storage Name (bucket name)
   - Region
   - Endpoint URL (if using non-AWS provider)
3. **All files exist locally** (plugins, languages, uploads) - restore from backup if needed
4. **PHP mysqli extension is installed** for CLI (see above)

## Running the Script

### Step 1: Test Upload (DO NOT DELETE LOCAL FILES)

```bash
cd /var/www/www-root/data/www/boybio.net
php product/migrate-to-offload-minimal.php
```

This will:
- Upload files to offload storage
- Show progress
- Keep local files intact

### Step 2: Verify Upload

1. Check your S3/cloud storage bucket to verify files were uploaded
2. Test that the site still works normally
3. Verify files are accessible from offload URLs

### Step 3: Delete Local Files (OPTIONAL - Only After Verification)

⚠️ **WARNING**: Only do this after thorough testing!

```bash
php product/migrate-to-offload-minimal.php --delete-local
```

This will delete:
- `product/uploads/main/`
- `product/uploads/pwa/`
- `product/uploads/favicons/`
- `product/plugins/`
- `product/app/languages/`

## Troubleshooting

### "mysqli extension is not available"

Make sure PHP mysqli extension is installed:
```bash
sudo apt-get install php8.3-mysqli
php -m | grep -i mysqli
```

### "Offload plugin is not active"

Enable the offload plugin in Admin Panel → Settings → Plugins

### "Offload is not configured"

Configure offload settings in Admin Panel → Settings → Offload

### "Database connection failed"

Check your `product/config.php` file has correct database credentials

### Files not uploading

- Check S3 bucket permissions
- Verify AWS credentials are correct
- Check network connectivity
- Review error messages in the script output

## Notes

- The script uses the application's database connection method (mysqli)
- Files are uploaded with `public-read` ACL
- The script preserves directory structure in S3
- Local files are kept by default (only deleted with `--delete-local` flag)




