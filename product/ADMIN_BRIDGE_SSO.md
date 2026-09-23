# Admin Bridge SSO (hub side)

Endpoint: `GET /admin-bridge/authorize`

Requires Altum **admin** session (`Authentication::guard('admin')`).

## Config via `.env` (preferred)

Create on the server (do **not** commit this file):

`/var/www/www-root/data/www/boybio.net/.env`

```env
ADMIN_BRIDGE_SECRET=your-long-shared-secret-here
ADMIN_BRIDGE_PEERS=https://www.shazdeha.com,https://shazdeha.com
```

`ADMIN_BRIDGE_SECRET` must be ≥16 characters and **identical** to shazdeha’s `CLOUB_ADMIN_SSO_SECRET`.

Also accepts process env / `$_SERVER` if set, but `.env` is enough — no PHP-FPM `export` / pool env required.

Files: `app/controllers/AdminBridge.php`, route in `app/core/Router.php`.

See peer docs: `sa/ADMIN_SSO.md`.
