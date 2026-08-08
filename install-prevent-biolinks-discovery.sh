#!/bin/bash
# Selective install: private biolinks / anti-discovery only.
# Run on the server from the boybio.net repo root (directory that contains product/).
set -euo pipefail

cd "$(cd "$(dirname "$0")" && pwd)"

if [ ! -f product/index.php ]; then
  echo "Run from boybio.net root (directory that contains product/)"
  exit 1
fi

echo "=== Fetch origin/backup ==="
git fetch origin backup

BK="/tmp/boybio_prevent_discovery_bak_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BK"

FILES=(
  product/app/helpers/links.php
  product/app/controllers/Sitemap.php
  product/app/controllers/Directory.php
  product/app/controllers/CheckUrlAvailability.php
  product/app/controllers/Spotlight.php
  product/app/controllers/admin/AdminSettings.php
  product/app/controllers/l/Link.php
  'product/app/languages/admin/english#en.php'
  product/themes/altum/views/sitemap/sitemap_index.php
  product/themes/altum/views/admin/settings/partials/links.php
  product/themes/altum/views/index/index.php
  product/themes/altum/views/l/biolink_wrapper.php
  product/themes/altum/views/l/splash_wrapper.php
  product/themes/altum/views/partials/app_sidebar.php
  product/themes/altum/views/partials/menu.php
)

for rel in "${FILES[@]}"; do
  if [ -f "$rel" ]; then
    mkdir -p "$BK/$(dirname "$rel")"
    cp -a "$rel" "$BK/$rel"
  fi
done
echo "Backup at: $BK"

echo "=== Checkout selected files from origin/backup ==="
for rel in "${FILES[@]}"; do
  git checkout origin/backup -- "$rel"
  echo "updated $rel"
done

echo
echo "Done. Mode defaults ON (prevent discovery)."
echo "Optional: Admin → Settings → Links → confirm checkbox, then set Main → custom 404 URL."
echo "Rollback: cp -a $BK/product/. product/"
