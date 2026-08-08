# Cloub.io / Altum — custom security & admin sessions plan

Saved: 2026-08-08  
Live tree: `altum/product/` (inline customs)  
Plugin path (future): `altum/product0/plugins/linkdoo-custom/`

---

## A) Previously reviewed requests (implement later via few plugins)

### A1. Biolink passwords in admin list
- Show stars equal to **real password length**, click to reveal plaintext.
- **Blocker today:** passwords are **bcrypt only** (`links.settings.password`). Length/plaintext not recoverable.
- **Required:** when password is set/changed, also store `password_length` and/or an **encrypted recoverable** secret for admin-only reveal (keep bcrypt for public verify).

### A2. Secondary admin dashboard gate
- After normal login, require another **admin gate password** configured in Admin → Settings → Security.
- Optional **2FA / TOTP** for that same gate (beyond account login 2FA).

### A3. IP whitelists (finish + extend)
- **Admin whitelist:** multi-IP (UI partly exists; save/auth still single-IP auto-login). Wire `parse_settings_ip_list` / `is_ip_in_settings_list` in `Authentication` + `AdminSettings::security()`.
- **User (all users) whitelist** tied to earlier security section / subdomain whose **A record is an IP**:
  - Configurable what opens without password (or fully open) from those IPs/hosts.
  - Cloudflare: use real visitor IP via `CF-Connecting-IP` only when peer is Cloudflare.

### A4. Plugin consolidation (fewer plugins)
- Prefer **one** custom plugin (`linkdoo-custom`, optionally split security later).
- Live site still has **inline** customs; move to `product0` bridge style before/while building A1–A3.
- Do **not** scatter into many small plugins.

### Decisions still needed before coding A1–A3
1. OK to store encrypted recoverable biolink password (or length + encrypted) for admin reveal?
2. Exact bypass matrix (IP / Host / which actions skip biolink password / login / whole site)?
3. Build in `product0` plugin path vs temporary inline on `product/`?

---

## B) Admin “alive sessions” (implement now)

### Goal
In **Admin → Users** list, in the last info column (logins / IP / country icons):
- Show **how many sessions are currently alive** for that user (including admins).
- Clicking the sessions icon opens a **popup** with the full list and complete info per session.

### Why a new table
PHP file sessions alone are not queryable per user. Need DB tracking:

`users_sessions` (proposed):
- `id`, `user_id`, `session_id` (PHP session id)
- `ip`, `continent_code`, `country_code`, `city_name`
- `device_type`, `os_name`, `browser_name`, `browser_language`
- `user_agent` (truncated)
- `datetime` (created / login)
- `last_activity`
- `is_admin` (user type admin at time of session)
- `admin_impersonation` (optional: admin logged-in-as-user)

**Alive** = `last_activity` within a window (default **30 minutes**).

### Hooks
- Upsert on login (`User::login_aftermath_update`)
- Touch `last_activity` on authenticated requests (`App.php` activity path)
- Delete/mark ended on logout (`Authentication::logout`)
- Periodic prune of stale rows (cron or on read)

### UI
- Icon + badge count in users table meta column
- Modal loads sessions via `admin/users/sessions/{user_id}` (JSON or HTML partial)

### Cloudflare
- Session IP must use `get_ip()` (already CF-aware where configured).

---

## C) Related existing code map

| Area | Path |
|------|------|
| Admin users list | `product/themes/altum/views/admin/users/index.php` |
| Admin users controller | `product/app/controllers/admin/AdminUsers.php` |
| Login aftermath | `product/app/models/User.php` → `login_aftermath_update` |
| Auth / logout | `product/app/core/Authentication.php` |
| Activity tick | `product/app/core/App.php` |
| Security settings | `AdminSettings::security()`, `themes/.../admin/settings/partials/security.php` |
| Mother password | `controllers/l/Link.php` |
| IP list helpers | `app/helpers/others.php` (`parse_settings_ip_list`, `is_ip_in_settings_list`) |
| Plugin upgrade doc | `V63_TO_V69_PLUGIN_UPGRADE.md` |

---

## D) Private biolinks / anti-discovery (“windows only, no doors”)

**Goal:** Biolink pages are only reachable by exact URL. No public directory, sitemap listing, claim-URL oracle, or search indexing. Unknown slugs use Admin → Main → custom 404 URL (`main.not_found_url`).

**Admin toggle:** Links → *Prevent public discovery of biolink pages* (`links.prevent_biolinks_discovery`). Defaults **ON** if unset.

| Surface | Behavior when ON |
|---------|------------------|
| `sitemap/links` | Removed / not-found |
| Directory | Admin-only; hidden from guest/user nav |
| Claim URL check API | Disabled (no used/available leak) |
| Homepage claim + example URL | Hidden |
| Public biolink/splash pages | `X-Robots-Tag` + meta `noindex,nofollow,noarchive` |
| Missing slug | Unchanged → `NotFound` → `not_found_url` |

Helper: `biolinks_discovery_is_prevented()` in `app/helpers/links.php`.

## E) Status

| Item | Status |
|------|--------|
| A1–A4 security package | Documented for future (plugin-first) |
| B alive sessions in admin users | **Implemented in `product/`** (model + hooks + admin UI) |
| D private biolinks / anti-discovery | **Implemented in `product/`** |
