# Admin Bridge SSO — `.env` in boybio.net (not product/)

Keep:

`/var/www/www-root/data/www/boybio.net/.env`

```env
ADMIN_BRIDGE_SECRET=your-long-shared-secret-here
ADMIN_BRIDGE_PEERS=https://www.shazdeha.com,https://shazdeha.com
```

Do **not** put `.env` inside `product/`.

## Why another domain alone is not enough

A second domain rooted on `boybio.net` does **not** let the cloub/`product` PHP process read that file by itself. Each domain has its own `open_basedir`. Serving `.env` over HTTP from that domain would expose secrets — we do **not** do that.

## Correct fix: allow reading the parent folder

For the **cloub.io / product** domain in Plesk → PHP Settings → `open_basedir`, include the parent directory, for example:

```text
{DOCROOT}:/tmp:/var/www/www-root/data/www/boybio.net
```

(or whatever your panel shows now, **plus** `/var/www/www-root/data/www/boybio.net`)

Then PHP in `product/` can read `../.env` safely. No secrets in `product/`, no public env URL.

Reload PHP after changing open_basedir:

```bash
sudo systemctl reload plesk-php82-fpm
# or your php*-fpm service
```

## Permissions

```bash
chmod 600 /var/www/www-root/data/www/boybio.net/.env
chown www-data:www-data /var/www/www-root/data/www/boybio.net/.env
```
