#!/bin/bash
# Keep product/ intact. Build/update product_stage/ for domain test root.
# Usage (on server, from site root e.g. /var/www/www-root/data/www/boybio.net):
#   bash pull-product-stage-test.sh
#
# Also resets product_info to 60.0.0/6000 via PHP mysqli (same as the app),
# so /update/ only applies 6100 and reaches 60.1.0.
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

echo "=== Clear language cache ==="
rm -f product_stage/app/languages/cache/*.php 2>/dev/null || true

CFG="product_stage/config.php"
if [ ! -f "$CFG" ]; then
  echo "ERROR: $CFG missing"
  exit 1
fi

echo "=== Reset product_info to 6000 (so /update/ does not restart from 801) ==="
php -r '
require $argv[1];
$mysqli = @new mysqli(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if ($mysqli->connect_errno) {
  fwrite(STDERR, "DB connect failed: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . PHP_EOL);
  exit(1);
}
$mysqli->set_charset("utf8mb4");
$sql = "UPDATE `settings` SET `value` = '\''{\"version\":\"60.0.0\",\"code\":\"6000\"}'\'' WHERE `key` = '\''product_info'\''";
if (!$mysqli->query($sql)) {
  fwrite(STDERR, "UPDATE failed: " . $mysqli->error . PHP_EOL);
  exit(1);
}
$res = $mysqli->query("SELECT `value` FROM `settings` WHERE `key` = '\''product_info'\''");
$row = $res ? $res->fetch_assoc() : null;
echo ($row["value"] ?? "(no product_info row)") . PHP_EOL;
$mysqli->close();
' "$CFG"

echo ""
echo "DONE."
echo "1) Point domain document root to: $(pwd)/product_stage"
echo "2) Check product_stage/config.php SITE_URL matches the test domain"
echo "3) Open https://YOUR-TEST-DOMAIN/update/  (should finish at 60.1.0)"
echo "4) Live fallback remains: $(pwd)/product"
