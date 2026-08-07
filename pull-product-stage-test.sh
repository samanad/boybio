#!/bin/bash
# Keep product/ intact. Build/update product_stage/ for domain test root.
# Usage (on server, from site root e.g. /var/www/www-root/data/www/boybio.net):
#   bash pull-product-stage-test.sh
set -euo pipefail
cd "$(dirname "$0")"

echo "=== Fetch origin/backup ==="
git fetch origin backup

if [ ! -d product ]; then
  echo "ERROR: product/ not found"
  exit 1
fi

if [ ! -d product_stage ]; then
  echo "=== One-time: copy product/ -> product_stage/ (keeps vendor, config, uploads) ==="
  rsync -a product/ product_stage/
else
  echo "product_stage/ exists — not wiping it; overlaying selected files only"
fi

echo "=== Pull selective product_stage files + this script ==="
git checkout origin/backup -- pull-product-stage-test.sh product_stage/

echo ""
echo "DONE."
echo "1) Point domain document root to: $(pwd)/product_stage"
echo "2) Check product_stage/config.php SITE_URL matches the test domain"
echo "3) Open /update/ on that domain OR run FINAL_READY.sql"
echo "4) Live fallback remains: $(pwd)/product"
