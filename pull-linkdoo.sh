#!/bin/bash

# Quick script to pull only the linkdoo folder from backup branch

set -e

cd /var/www/www-root/data/www/boybio.net

echo "=== Pulling linkdoo folder ==="
echo ""

# Safety check
if [ ! -d "product" ]; then
    echo "ERROR: product directory not found! Are you in the right directory?"
    exit 1
fi

# Fetch latest changes
echo "Fetching latest changes from remote..."
git fetch origin backup
echo "✓ Fetch complete"
echo ""

# Checkout only the linkdoo folder
echo "Updating linkdoo folder..."
if git checkout origin/backup -- linkdoo/ 2>/dev/null; then
    echo "✓ linkdoo folder updated successfully"
else
    echo "✗ Error: Could not update linkdoo folder"
    exit 1
fi

# Fix permissions
echo ""
echo "Fixing permissions..."
chown -R www-data:www-data linkdoo/ 2>/dev/null || true
chmod -R 755 linkdoo/ 2>/dev/null || true

echo ""
echo "=== Complete ==="
echo "linkdoo folder is now up to date!"




