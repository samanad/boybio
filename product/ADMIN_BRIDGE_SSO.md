# Admin Bridge SSO (hub side)

Endpoint: `GET /admin-bridge/authorize`

Requires Altum **admin** session. Misconfiguration or bad requests → normal **404** (no public hints).

## `.env` location

Altum app root = `product/`.  
Put secrets in the **parent** folder:

`/var/www/www-root/data/www/boybio.net/.env`

```env
ADMIN_BRIDGE_SECRET=your-long-shared-secret-here
ADMIN_BRIDGE_PEERS=https://www.shazdeha.com,https://shazdeha.com
```

```bash
chmod 600 /var/www/www-root/data/www/boybio.net/.env
chown www-data:www-data /var/www/www-root/data/www/boybio.net/.env
```

### If PHP still cannot read it (`open_basedir`)

Plesk often confines PHP to `product/`. Either:

1. Add the parent to open_basedir for the domain, e.g. include  
   `/var/www/www-root/data/www/boybio.net`  
   or

2. Also place the same file at `product/.env` (allowed by open_basedir).

`ADMIN_BRIDGE_SECRET` must match shazdeha `CLOUB_ADMIN_SSO_SECRET` (≥16 chars).
