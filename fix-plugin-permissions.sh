#!/bin/bash

# Fix Plugin and Upload Directory Permissions Script
# This script fixes permissions for plugin directories and upload directories

cd /var/www/www-root/data/www/boybio.net

echo "=== Fixing Plugin and Upload Directory Permissions ==="
echo ""

# Base paths
UPLOADS_DIR="product/uploads"
PLUGINS_DIR="product/plugins"

# List of upload subdirectories that need write permissions
UPLOAD_SUBDIRS=(
    "main"
    "users"
    "cache"
    "cookie_consent"
    "logs"
    "offline_payment_proofs"
    "blog"
    "pwa"
    "dynamic_og_images"
    "products_files"
    "avatars"
    "backgrounds"
    "block_thumbnail_images"
    "block_images"
    "files"
    "favicons"
    "qr_code"
    "qr_code_logo"
    "qr_code_background"
    "qr_code_foreground"
    "chats_assistants"
    "chats_images"
    "syntheses"
    "static"
    "splash_pages"
    "service_workers"
)

# Fix ownership first
echo "Fixing ownership..."
if [ -d "$UPLOADS_DIR" ]; then
    chown -R www-data:www-data "$UPLOADS_DIR" 2>/dev/null || chown -R apache:apache "$UPLOADS_DIR" 2>/dev/null || echo "Warning: Could not change ownership of $UPLOADS_DIR"
    echo "✓ Ownership fixed for $UPLOADS_DIR"
fi

if [ -d "$PLUGINS_DIR" ]; then
    chown -R www-data:www-data "$PLUGINS_DIR" 2>/dev/null || chown -R apache:apache "$PLUGINS_DIR" 2>/dev/null || echo "Warning: Could not change ownership of $PLUGINS_DIR"
    echo "✓ Ownership fixed for $PLUGINS_DIR"
fi

# Create and fix permissions for upload subdirectories
echo ""
echo "Creating and fixing permissions for upload subdirectories..."
for dir in "${UPLOAD_SUBDIRS[@]}"; do
    full_path="$UPLOADS_DIR/$dir"
    if [ ! -d "$full_path" ]; then
        echo "Creating directory: $full_path"
        mkdir -p "$full_path" 2>/dev/null || true
    fi
    
    if [ -d "$full_path" ]; then
        chmod -R 755 "$full_path" 2>/dev/null || true
        chown -R www-data:www-data "$full_path" 2>/dev/null || chown -R apache:apache "$full_path" 2>/dev/null || true
        echo "✓ Fixed permissions for $full_path"
    fi
done

# Fix permissions for main uploads directory
if [ -d "$UPLOADS_DIR" ]; then
    chmod -R 755 "$UPLOADS_DIR" 2>/dev/null || true
    echo "✓ Fixed permissions for $UPLOADS_DIR"
fi

# Fix permissions for plugins directory
if [ -d "$PLUGINS_DIR" ]; then
    chmod -R 755 "$PLUGINS_DIR" 2>/dev/null || true
    echo "✓ Fixed permissions for $PLUGINS_DIR"
    
    # Fix permissions for each plugin subdirectory
    echo ""
    echo "Fixing permissions for individual plugin directories..."
    for plugin_dir in "$PLUGINS_DIR"/*; do
        if [ -d "$plugin_dir" ]; then
            plugin_name=$(basename "$plugin_dir")
            chmod -R 755 "$plugin_dir" 2>/dev/null || true
            chown -R www-data:www-data "$plugin_dir" 2>/dev/null || chown -R apache:apache "$plugin_dir" 2>/dev/null || true
            echo "✓ Fixed permissions for plugin: $plugin_name"
        fi
    done
fi

# Special handling for cache directory (needs 777 for phpFastCache)
if [ -d "$UPLOADS_DIR/cache" ]; then
    chmod -R 777 "$UPLOADS_DIR/cache" 2>/dev/null || true
    chown -R www-data:www-data "$UPLOADS_DIR/cache" 2>/dev/null || chown -R apache:apache "$UPLOADS_DIR/cache" 2>/dev/null || true
    echo "✓ Fixed cache directory permissions (777)"
fi

# Verify permissions
echo ""
echo "Verifying permissions..."
if [ -w "$UPLOADS_DIR" ]; then
    echo "✓ Uploads directory is writable"
else
    echo "✗ WARNING: Uploads directory is NOT writable"
    echo "  You may need to run this script with sudo or as root"
fi

if [ -w "$PLUGINS_DIR" ]; then
    echo "✓ Plugins directory is writable"
else
    echo "✗ WARNING: Plugins directory is NOT writable"
    echo "  You may need to run this script with sudo or as root"
fi

echo ""
echo "=== Complete ==="
echo ""
echo "If you still see permission errors, try running as root:"
echo "  sudo ./fix-plugin-permissions.sh"
echo ""
echo "Or use this one-liner:"
echo "  cd /var/www/www-root/data/www/boybio.net && sudo chown -R www-data:www-data product/uploads product/plugins && sudo chmod -R 755 product/uploads product/plugins && sudo chmod -R 777 product/uploads/cache"

