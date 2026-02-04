#!/bin/bash

# Pull only the PWA fix files
cd /var/www/www-root/data/www/boybio.net

echo "=== Pulling PWA Fix Files ==="
echo ""

# Fetch latest changes
git fetch origin backup

# Pull only the required files
echo "Pulling files..."
git checkout origin/backup -- product/themes/altum/views/l/biolink_wrapper.php
git checkout origin/backup -- product/plugins/pwa/views/partials/pwa_custom.php

echo ""
echo "✓ Files pulled successfully"
echo ""

# Fix permissions
echo "Fixing permissions..."
chown -R www-data:www-data product/plugins/pwa/views/ 2>/dev/null || true
chmod -R 755 product/plugins/pwa/views/ 2>/dev/null || true
chmod -R 755 product/themes/altum/views/l/biolink_wrapper.php 2>/dev/null || true

echo "✓ Permissions fixed"
echo ""
echo "=== Complete ==="


