# Admin Bridge SSO (hub side)

Endpoint: `GET /admin-bridge/authorize`

Requires Altum **admin** session (`Authentication::guard('admin')`).

## Env (cloub.io)

```
ADMIN_BRIDGE_SECRET=<same as peer CLOUB_ADMIN_SSO_SECRET, >=16 chars>
ADMIN_BRIDGE_PEERS=https://www.shazdeha.com,https://shazdeha.com
```

Files: `app/controllers/AdminBridge.php`, route in `app/core/Router.php`.

See peer docs: `sa/ADMIN_SSO.md`.
