# cloub app (served via boybios.com)

The APK only talks to hardcoded Cloudflare anycast IPs (no DNS, not 1.1.1.1). TLS SNI is still `boybios.com` so the Worker on that domain answers.

## After you click Create Worker on Cloudflare

1. Add `boybios.com` as a **zone** in Cloudflare (if it is not there yet). Nameservers must be Cloudflare. Orange-cloud proxy stays on.

2. Open **Workers & Pages** → the worker you just created (rename it `boybios` if you want).

3. Replace the default Hello World code with the contents of `boybios/cloudflare-worker.js`. Click **Deploy**.

4. **Settings → Variables and Secrets**
   - Variable `CLOUB_ORIGIN` = `https://cloub.io`
   - Variable `APP_NAME` = `cloub`
   - Secret `BOYBIOS_WORKER_SECRET` = the same long random string as in cloub.io `config.php`

5. **Settings → Domains & Routes → Add Custom Domain** → `boybios.com` (and `www.boybios.com` if you want). Cloudflare will create the DNS records. Do not point the domain at your origin server IP.

6. Open `https://boybios.com/health` in a browser. You should see `{"ok":true,...}`.

7. On cloub.io, set:

```php
define('BOYBIOS_WORKER_SECRET', 'the-same-secret');
```

and deploy `BoybiosApi.php` plus the router change.

## APK on saman.host (Plesk, no Android Studio)

This uses the **Android SDK command-line tools + Gradle** on the Debian server.

1. In Plesk, create a subdomain (for example `apk.saman.host`) and set its document root to the `boybios/site` folder. The parent `boybios/android` folder must sit next to `site` (upload the whole `boybios` directory).

2. SSH as **root** first (4GB RAM will freeze without swap):

```bash
apt install -y openjdk-17-jdk-headless unzip wget
fallocate -l 2G /swapfile-boybio
chmod 600 /swapfile-boybio
mkswap /swapfile-boybio
swapon /swapfile-boybio
echo '/swapfile-boybio none swap sw 0 0' >> /etc/fstab
chmod +x /path/to/boybios/site/build-apk.sh
```

After a reboot the unfinished build is gone. Upload the latest `boybios` files, then build again. Leave other heavy panels idle during the first compile (it is slow on 2 CPUs).

3. Edit `boybios/site/config.php` and change `BUILD_PASSWORD`.

4. In Plesk **PHP Settings**, do **not** disable `shell_exec`. If Plesk blocks it, build over SSH instead:

```bash
bash /path/to/boybios/site/build-apk.sh
```

5. Open `https://apk.saman.host`, log in, click **Build APK**, wait (first run downloads the SDK), then **Download cloub.apk**.

The phone still connects to `boybios.com` on Cloudflare, not to saman.host.
