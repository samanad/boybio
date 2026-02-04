# Offload Migration Guide

This guide explains how to migrate static assets, plugins, and translations to offload storage (S3) so you can delete local files and load everything from the cloud.

## Overview

The migration process:
1. **Configure offload settings** in Admin Panel
2. **Run migration script** to upload existing files to S3
3. **System automatically loads from offload** when local files don't exist
4. **Optionally delete local files** after verification

## Step 1: Configure Offload Settings

1. Go to **Admin Panel → Settings → Offload**
2. Configure your S3/cloud storage:
   - Provider (AWS S3, DigitalOcean Spaces, etc.)
   - Access Key
   - Secret Access Key
   - Storage Name (bucket name)
   - Region
   - Endpoint URL (if using non-AWS provider)
3. Enable offload and save settings

## Step 2: Run Migration Script

The migration script uploads these folders to offload storage:
- `product/uploads/main/` - Static assets (logos, favicons)
- `product/uploads/pwa/` - PWA manifest and icons
- `product/uploads/favicons/` - Favicon files
- `product/plugins/` - Entire plugins folder
- `product/app/languages/` - Translation files

### Run the script:

```bash
cd /var/www/www-root/data/www/boybio.net
php product/migrate-to-offload.php
```

The script will:
- Check if offload is configured
- Upload all files from the above folders to S3
- Show progress and summary
- Report any errors

### Expected output:

```
=== Migration to Offload Storage ===

Offload URL: https://your-bucket.s3.amazonaws.com
Storage Name: your-bucket
Region: us-east-1

✓ AWS S3 Client initialized

1. Uploading static assets (uploads/main/)...
  ✓ Uploaded: uploads/main/logo.png
  ✓ Uploaded: uploads/main/favicon.ico
  ...

2. Uploading PWA assets (uploads/pwa/)...
  ✓ Uploaded: uploads/pwa/manifest.json
  ...

3. Uploading favicons (uploads/favicons/)...
  ...

4. Uploading plugins (plugins/)...
  ✓ Uploaded: plugins/offload/config.php
  ...

5. Uploading translations (app/languages/)...
  ✓ Uploaded: app/languages/english#en.php
  ...

=== Migration Summary ===
✓ Successfully uploaded: 150 files
```

## Step 3: Verify Files Are Loading from Offload

After migration, test that files load correctly:

1. **Static assets**: Check if logos/favicons load from offload URLs
2. **Plugins**: Verify plugins still work
3. **Translations**: Check that language files load correctly
4. **PWA**: Verify PWA manifest loads from offload

The system will automatically:
- Load from local files if they exist (priority)
- Download from offload to temp cache if local files don't exist
- Use offload URLs for static assets

## Step 4: Delete Local Files (Optional)

⚠️ **WARNING**: Only delete local files after thorough testing!

After verifying everything works from offload:

```bash
php product/migrate-to-offload.php --delete-local
```

This will delete:
- `product/uploads/main/`
- `product/uploads/pwa/`
- `product/uploads/favicons/`
- `product/plugins/`
- `product/app/languages/`

**Important**: Make sure you have a backup before deleting!

## How It Works

### Static Assets (uploads/main, uploads/pwa, uploads/favicons)

The system automatically uses offload URLs when local files don't exist:
- Local file exists → Use local file
- Local file missing → Use offload URL directly

### Plugins

Plugins are loaded from:
1. Local `product/plugins/` directory (if exists)
2. Offload storage (downloaded to temp cache if local missing)

### Translations

Language files are loaded from:
1. Local `product/app/languages/` directory (if exists)
2. Offload storage (downloaded to temp cache if local missing)

The temp cache is located at: `product/uploads/cache/offload/`

Cache files are refreshed every 24 hours automatically.

## Troubleshooting

### "Offload plugin is not active or not configured"

- Make sure offload plugin is enabled in Admin Panel
- Verify offload settings are configured correctly
- Check that `settings()->offload->uploads_url` is set

### "Failed to initialize AWS S3 client"

- Check your AWS credentials (Access Key, Secret Key)
- Verify the bucket name and region are correct
- For non-AWS providers, check endpoint URL

### "Failed to upload" errors

- Check S3 bucket permissions (needs `PutObject` permission)
- Verify bucket ACL allows public-read (for static assets)
- Check network connectivity to S3

### Files not loading from offload

- Check that files were uploaded successfully (check S3 bucket)
- Verify offload URL is correct in settings
- Check temp cache directory permissions: `chmod -R 777 product/uploads/cache/offload/`

### Cache issues

Clear the offload cache:
```bash
rm -rf product/uploads/cache/offload/*
```

## Files Modified

1. **`product/migrate-to-offload.php`** - Migration script (new)
2. **`product/app/helpers/offload_helpers.php`** - Helper functions (new)
3. **`product/app/core/Language.php`** - Modified to load from offload
4. **`product/app/core/Plugin.php`** - Modified to load from offload

## Benefits

✅ **Reduced server storage** - Files stored in cloud
✅ **Faster deployments** - No need to sync large files
✅ **Scalability** - Cloud storage scales automatically
✅ **Backup** - Files backed up in cloud storage
✅ **CDN ready** - Can use CDN with cloud storage

## Notes

- Local files take priority over offload (if both exist)
- Temp cache is used for PHP files (languages, plugins) that need to be `require`d
- Static assets (images, CSS, JS) are loaded directly from offload URLs
- Cache is automatically refreshed every 24 hours
- You can manually clear cache: `rm -rf product/uploads/cache/offload/*`




