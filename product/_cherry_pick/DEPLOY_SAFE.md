# Safe deploy (after 2026-08-07 outage)

## What broke last time
1. **`app/init.php` lost `spl_autoload_register`** — Altum classes never loaded → HTTP 500.
2. Mixed restore of `Cache.php` / `sessions.php` / `Authentication.php`.
3. PHP **mbstring** missing on some PHP binaries (install `php8.2-mbstring` for the FPM that serves cloub.io).

## What this push fixes
- Restored Altum autoloader in `app/init.php` (keeps `sessions.php`, `NotFoundException`, `66text`).
- Replaced v69 Redis `Cache.php` with the known-good Files cache from `product63.1` (matches live `vendor/`).
- Added `themes/altum/assets/fonts/Inter-Bold.ttf` required by v69 captcha.
- Removed `gsa.json` from the selective list (secret; stays gitignored).

## Server deploy (selective, keeps config/uploads)

```bash
cd /var/www/www-root/data/www/boybio.net
git fetch origin backup

# backup files that will be replaced
git show origin/backup:product/_cherry_pick/SELECTIVE_FINAL.txt > /tmp/selective_final.txt
mkdir -p /tmp/boybio_file_bak
while IFS= read -r rel; do
  [ -z "$rel" ] && continue
  if [ -f "product/$rel" ]; then
    mkdir -p "/tmp/boybio_file_bak/$(dirname "$rel")"
    cp -a "product/$rel" "/tmp/boybio_file_bak/$rel"
  fi
done < /tmp/selective_final.txt
cp -a product/config.php /tmp/boybio_config.php.bak
cp -a product/uploads/pwa/manifest.json /tmp/boybio_manifest.json.bak 2>/dev/null || true

# pull only selective paths
while IFS= read -r rel; do
  [ -z "$rel" ] && continue
  git checkout origin/backup -- "product/$rel"
done < /tmp/selective_final.txt

# keep server secrets/assets
cp -a /tmp/boybio_config.php.bak product/config.php
cp -a /tmp/boybio_manifest.json.bak product/uploads/pwa/manifest.json 2>/dev/null || true
rm -f product/app/languages/cache/*.php

# must have autoloader
grep -n "spl_autoload_register" product/app/init.php
php -l product/app/init.php
php -m | grep -i mbstring

# restart the FPM that serves the site (usually 8.2)
systemctl restart php8.2-fpm
```

## Quick verify
```bash
curl -sI https://cloub.io/ | head -5
# expect 200/302, NOT 500
tail -30 product/uploads/logs/$(date +%Y-%m-%d).log
```

## Rollback
```bash
rsync -a /tmp/boybio_file_bak/ product/
cp -a /tmp/boybio_config.php.bak product/config.php
rm -f product/app/languages/cache/*.php
systemctl restart php8.2-fpm
```

## After site is healthy
- Digital Wallets / new tables: only run `/update/` after a DB backup (see `V55_DB_NOTE.md`).
- Do **not** change the site PHP version in the same window as this deploy.
