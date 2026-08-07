#!/bin/bash
# Keep product/ intact. Build/update product_stage/ for domain test root.
# Usage (on server, from site root e.g. /var/www/www-root/data/www/boybio.net):
#   bash pull-product-stage-test.sh
#
# Also resets product_info to 60.0.0/6000 via mysql (no phpMyAdmin),
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

CFG="product_stage/config.php"
if [ ! -f "$CFG" ]; then
  echo "ERROR: $CFG missing"
  exit 1
fi

echo "=== Reset product_info to 6000 (so /update/ does not restart from 801) ==="
DB_LINE="$(php -r '
$c = file_get_contents($argv[1]);
foreach (["DATABASE_SERVER","DATABASE_USERNAME","DATABASE_PASSWORD","DATABASE_NAME"] as $k) {
  if (!preg_match("/define\(\s*'\''".$k."'\''\s*,\s*'\''((?:\\\\'|[^'\''])*)'\''/s", $c, $m)) {
    fwrite(STDERR, "Missing $k\n");
    exit(1);
  }
  echo str_replace(["\\'", "\\\\"], ["'", "\\"], $m[1]), "\t";
}
' "$CFG")"

IFS=$'\t' read -r DB_HOST DB_USER DB_PASS DB_NAME <<<"$DB_LINE"

if [ -z "${DB_NAME:-}" ]; then
  echo "ERROR: Could not read DATABASE_* from $CFG"
  exit 1
fi

export MYSQL_PWD="$DB_PASS"
mysql -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" --protocol=TCP -e \
  "UPDATE \`settings\` SET \`value\` = '{\"version\":\"60.0.0\",\"code\":\"6000\"}' WHERE \`key\` = 'product_info'; SELECT \`value\` FROM \`settings\` WHERE \`key\` = 'product_info';"
unset MYSQL_PWD

echo ""
echo "DONE."
echo "1) Point domain document root to: $(pwd)/product_stage"
echo "2) Check product_stage/config.php SITE_URL matches the test domain"
echo "3) Open https://YOUR-TEST-DOMAIN/update/  (should finish at 60.1.0)"
echo "4) Live fallback remains: $(pwd)/product"
