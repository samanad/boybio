#!/bin/bash

# Fix Cache Permissions Script
# This script fixes permissions for the cache directory

cd /var/www/www-root/data/www/boybio.net

echo "=== Fixing Cache Permissions ==="
echo ""

# Ensure cache directory exists
if [ ! -d "product/uploads/cache" ]; then
    echo "Creating cache directory..."
    mkdir -p product/uploads/cache
    echo "✓ Cache directory created"
else
    echo "✓ Cache directory exists"
fi

# Fix ownership
echo "Fixing ownership..."
chown -R www-data:www-data product/uploads/cache/ 2>/dev/null || chown -R apache:apache product/uploads/cache/ 2>/dev/null || echo "Warning: Could not change ownership"
echo "✓ Ownership fixed"

# Fix permissions - make it writable
echo "Fixing permissions..."
chmod -R 777 product/uploads/cache/ 2>/dev/null || echo "Warning: Could not change permissions"
echo "✓ Permissions fixed"

# Create subdirectories that might be needed
echo "Ensuring subdirectories exist..."
mkdir -p product/uploads/cache/66biolinks 2>/dev/null || true
mkdir -p product/uploads/cache/offload 2>/dev/null || true
chmod -R 777 product/uploads/cache/ 2>/dev/null || true
chown -R www-data:www-data product/uploads/cache/ 2>/dev/null || chown -R apache:apache product/uploads/cache/ 2>/dev/null || true
echo "✓ Subdirectories created"

# Verify permissions
echo ""
echo "Verifying permissions..."
if [ -w "product/uploads/cache" ]; then
    echo "✓ Cache directory is writable"
else
    echo "✗ WARNING: Cache directory is NOT writable"
    echo "  You may need to run this script with sudo or as root"
fi

echo ""
echo "=== Complete ==="
echo ""
echo "If you still see permission errors, try running as root:"
echo "  sudo ./fix_cache_permissions.sh"

