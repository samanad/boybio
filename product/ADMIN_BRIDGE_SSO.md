# Admin Bridge SSO (hub side)

Endpoint: `GET /admin-bridge/authorize`

Bad/missing access → plain text: `you can't access this page` (no secrets leaked).

## `.env` (important)

PHP document root is **`product/`**. Plesk `open_basedir` usually **cannot** read the parent folder, so put the file here:

`/var/www/www-root/data/www/boybio.net/product/.env`

```env
ADMIN_BRIDGE_SECRET=your-long-shared-secret-here
ADMIN_BRIDGE_PEERS=https://www.shazdeha.com,https://shazdeha.com
```

If you already created it in `boybio.net/.env`, copy it:

```bash
cp /var/www/www-root/data/www/boybio.net/.env /var/www/www-root/data/www/boybio.net/product/.env
chmod 600 /var/www/www-root/data/www/boybio.net/product/.env
chown www-data:www-data /var/www/www-root/data/www/boybio.net/product/.env
```

`ADMIN_BRIDGE_SECRET` must match shazdeha `CLOUB_ADMIN_SSO_SECRET` (≥16 chars).

You must also be **logged in as admin** on cloub.io when authorize runs (otherwise login redirect, then continue).
